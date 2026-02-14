<?php

if(!function_exists('blc_parse_mysql_host')){
	function blc_parse_mysql_host(string $host): array {
		$normalizedHost = trim($host);
		$port = null;

		if(preg_match('/^\[(.+)\]:(\d+)$/', $normalizedHost, $matches) === 1){
			$normalizedHost = $matches[1];
			$port = (int) $matches[2];
		}elseif(substr_count($normalizedHost, ':') === 1){
			list($possibleHost, $possiblePort) = explode(':', $normalizedHost, 2);
			if($possibleHost !== '' && ctype_digit($possiblePort)){
				$normalizedHost = $possibleHost;
				$port = (int) $possiblePort;
			}
		}

		return array(
			'host' => $normalizedHost,
			'port' => $port,
		);
	}
}

if(!function_exists('blc_build_mysql_dsn')){
	function blc_build_mysql_dsn(string $host, string $databaseName = ''): string {
		$hostParts = blc_parse_mysql_host($host);
		$dsn = "mysql:host={$hostParts['host']}";

		$port = $hostParts['port'];
		if($port === null){
			$envPort = getenv('DB_PORT');
			if($envPort !== false && ctype_digit((string) $envPort)){
				$port = (int) $envPort;
			}
		}

		if($port !== null){
			$dsn .= ";port={$port}";
		}

		if($databaseName !== ''){
			$dsn .= ";dbname={$databaseName}";
		}

		$dsn .= ";charset=utf8mb4";
		return $dsn;
	}
}

if(!function_exists('blc_create_installer_pdo')){
	function blc_create_installer_pdo(string $host, string $user, string $pass, string $databaseName = ''): PDO {
		return new PDO(
			blc_build_mysql_dsn($host, $databaseName),
			$user,
			$pass,
			array(
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
			)
		);
	}
}

if(!function_exists('blc_schema_has_table')){
	function blc_schema_has_table(PDO $pdo, string $tableName): bool {
		$query = $pdo->prepare(
			"SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table_name"
		);
		$query->execute(array(':table_name' => $tableName));
		return ((int) $query->fetchColumn()) > 0;
	}
}

if(!function_exists('blc_has_required_schema')){
	function blc_has_required_schema(string $host, string $user, string $pass, string $databaseName): bool {
		if(trim($host) === '' || trim($user) === '' || trim($databaseName) === ''){
			return false;
		}

		try{
			$pdo = blc_create_installer_pdo($host, $user, $pass, $databaseName);
			$requiredTables = array('general_settings', 'admins', 'api_settings', 'languages');
			foreach($requiredTables as $tableName){
				if(!blc_schema_has_table($pdo, $tableName)){
					return false;
				}
			}
			return true;
		}catch(Throwable $ex){
			return false;
		}
	}
}

if(!function_exists('blc_is_installation_complete')){
	function blc_is_installation_complete(string $host, string $user, string $pass, string $databaseName): bool {
		if(!blc_has_required_schema($host, $user, $pass, $databaseName)){
			return false;
		}

		try{
			$pdo = blc_create_installer_pdo($host, $user, $pass, $databaseName);
			$generalSettingsReady = (int) $pdo->query(
				"SELECT COUNT(*) FROM general_settings WHERE TRIM(COALESCE(site_url,'')) <> '' AND TRIM(COALESCE(site_name,'')) <> ''"
			)->fetchColumn();
			$adminReady = (int) $pdo->query(
				"SELECT COUNT(*) FROM admins WHERE TRIM(COALESCE(admin_email,'')) <> '' AND TRIM(COALESCE(admin_pass,'')) <> ''"
			)->fetchColumn();

			return $generalSettingsReady > 0 && $adminReady > 0;
		}catch(Throwable $ex){
			return false;
		}
	}
}

if(!function_exists('blc_split_sql_statements')){
	function blc_split_sql_statements(string $sql): array {
		$statements = array();
		$buffer = '';
		$inSingleQuote = false;
		$inDoubleQuote = false;
		$inBacktick = false;
		$lineStart = true;
		$length = strlen($sql);

		for($index = 0; $index < $length; $index++){
			$char = $sql[$index];
			$nextChar = $index + 1 < $length ? $sql[$index + 1] : '';

			if($lineStart && !$inSingleQuote && !$inDoubleQuote && !$inBacktick){
				if($char === '-' && $nextChar === '-'){
					while($index < $length && $sql[$index] !== "\n"){
						$index++;
					}
					$lineStart = true;
					continue;
				}

				if($char === '#'){
					while($index < $length && $sql[$index] !== "\n"){
						$index++;
					}
					$lineStart = true;
					continue;
				}
			}

			if(!$inSingleQuote && !$inDoubleQuote && !$inBacktick && $char === '/' && $nextChar === '*'){
				$blockCommentEnd = strpos($sql, '*/', $index + 2);
				if($blockCommentEnd === false){
					break;
				}
				$index = $blockCommentEnd + 1;
				$lineStart = false;
				continue;
			}

			$isEscaped = $index > 0 && $sql[$index - 1] === '\\';
			if($char === "'" && !$inDoubleQuote && !$inBacktick && !$isEscaped){
				$inSingleQuote = !$inSingleQuote;
			}elseif($char === '"' && !$inSingleQuote && !$inBacktick && !$isEscaped){
				$inDoubleQuote = !$inDoubleQuote;
			}elseif($char === '`' && !$inSingleQuote && !$inDoubleQuote){
				$inBacktick = !$inBacktick;
			}

			if($char === ';' && !$inSingleQuote && !$inDoubleQuote && !$inBacktick){
				$statement = trim($buffer);
				if($statement !== ''){
					$statements[] = $statement;
				}
				$buffer = '';
				$lineStart = true;
				continue;
			}

			$buffer .= $char;
			$lineStart = ($char === "\n" || $char === "\r");
		}

		$tailStatement = trim($buffer);
		if($tailStatement !== ''){
			$statements[] = $tailStatement;
		}

		return $statements;
	}
}

if(!function_exists('blc_execute_sql_file')){
	function blc_execute_sql_file(PDO $pdo, string $sqlPath): void {
		$sql = file_get_contents($sqlPath);
		if($sql === false){
			throw new RuntimeException("Unable to read SQL file: " . basename($sqlPath));
		}

		$statements = blc_split_sql_statements($sql);
		foreach($statements as $statement){
			$trimmed = trim($statement);
			if($trimmed === ''){
				continue;
			}
			$pdo->exec($trimmed);
		}
	}
}

if(!function_exists('blc_execute_sql_file_with_mysqli')){
	function blc_execute_sql_file_with_mysqli(
		string $host,
		string $user,
		string $pass,
		string $databaseName,
		string $sqlPath
	): void {
		if(!function_exists('mysqli_init')){
			throw new RuntimeException("MySQLi extension is required to run installer SQL files.");
		}

		$sql = file_get_contents($sqlPath);
		if($sql === false){
			throw new RuntimeException("Unable to read SQL file: " . basename($sqlPath));
		}

		$hostParts = blc_parse_mysql_host($host);
		$mysqli = mysqli_init();
		if($mysqli === false){
			throw new RuntimeException("Unable to initialize MySQLi.");
		}

		$port = $hostParts['port'] !== null ? (int) $hostParts['port'] : null;
		if($port === null){
			$envPort = getenv('DB_PORT');
			if($envPort !== false && ctype_digit((string) $envPort)){
				$port = (int) $envPort;
			}
		}
		if($port === null){
			$port = 3306;
		}
		if(!@$mysqli->real_connect($hostParts['host'], $user, $pass, $databaseName, $port)){
			throw new RuntimeException("Database connection failed: " . mysqli_connect_error());
		}

		if(!$mysqli->multi_query($sql)){
			$errorMessage = $mysqli->error;
			$mysqli->close();
			throw new RuntimeException("Failed executing SQL file " . basename($sqlPath) . ": " . $errorMessage);
		}

		do{
			$result = $mysqli->store_result();
			if($result instanceof mysqli_result){
				$result->free();
			}
			if($mysqli->errno){
				$errorMessage = $mysqli->error;
				$mysqli->close();
				throw new RuntimeException("Failed executing SQL file " . basename($sqlPath) . ": " . $errorMessage);
			}
		}while($mysqli->more_results() && $mysqli->next_result());

		if($mysqli->errno){
			$errorMessage = $mysqli->error;
			$mysqli->close();
			throw new RuntimeException("Failed executing SQL file " . basename($sqlPath) . ": " . $errorMessage);
		}

		$mysqli->close();
	}
}
