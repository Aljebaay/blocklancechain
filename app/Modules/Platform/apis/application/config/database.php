<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['dsn']      The full DSN string describe a connection to the database.
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database driver. e.g.: mysqli.
|			Currently supported:
|				 cubrid, ibase, mssql, mysql, mysqli, oci8,
|				 odbc, pdo, postgre, sqlite, sqlite3, sqlsrv
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Query Builder class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['encrypt']  Whether or not to use an encrypted connection.
|
|			'mysql' (deprecated), 'sqlsrv' and 'pdo/sqlsrv' drivers accept TRUE/FALSE
|			'mysqli' and 'pdo/mysql' drivers accept an array with the following options:
|
|				'ssl_key'    - Path to the private key file
|				'ssl_cert'   - Path to the public key certificate file
|				'ssl_ca'     - Path to the certificate authority file
|				'ssl_capath' - Path to a directory containing trusted CA certificates in PEM format
|				'ssl_cipher' - List of *allowed* ciphers to be used for the encryption, separated by colons (':')
|				'ssl_verify' - TRUE/FALSE; Whether verify the server certificate or not
|
|	['compress'] Whether or not to use client compression (MySQL only)
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|	['ssl_options']	Used to set various SSL options that can be used when making SSL connections.
|	['failover'] array - A array with 0 or more data for connections if the main should fail.
|	['save_queries'] TRUE/FALSE - Whether to "save" all executed queries.
| 				NOTE: Disabling this will also effectively disable both
| 				$this->db->last_query() and profiling of DB queries.
| 				When you run a query, with this setting set to TRUE (default),
| 				CodeIgniter will store the SQL statement for debugging purposes.
| 				However, this may cause high memory usage, especially if you run
| 				a lot of SQL queries ... disable this to avoid that problem.
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $query_builder variables lets you determine whether or not to load
| the query builder class.
*/
$active_group = 'default';
$query_builder = TRUE;

$apiDbHost = getenv('API_DB_HOST');
$apiDbUser = getenv('API_DB_USER');
$apiDbPass = getenv('API_DB_PASS');
$apiDbName = getenv('API_DB_NAME');
$apiDbCharset = getenv('API_DB_CHARSET');
$apiDbCollation = getenv('API_DB_COLLATION');
$appDebug = getenv('APP_DEBUG');

if ($apiDbHost === false || $apiDbHost === '') {
	$apiDbHost = getenv('DB_HOST');
}
if ($apiDbUser === false || $apiDbUser === '') {
	$apiDbUser = getenv('DB_USER');
}
if ($apiDbPass === false || $apiDbPass === '') {
	$apiDbPass = getenv('DB_PASS');
}
if ($apiDbName === false || $apiDbName === '') {
	$apiDbName = getenv('DB_NAME');
}
if ($apiDbCharset === false || $apiDbCharset === '') {
	$apiDbCharset = getenv('DB_CHARSET');
}
if ($apiDbCollation === false || $apiDbCollation === '') {
	$apiDbCollation = getenv('DB_COLLATION');
}

$apiDbDebug = TRUE;
if ($appDebug !== false && $appDebug !== '') {
	$apiDbDebug = in_array(strtolower((string) $appDebug), array('1', 'true', 'yes', 'on'), true);
}

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => $apiDbHost !== false && $apiDbHost !== '' ? (string) $apiDbHost : 'localhost',
	'username' => $apiDbUser !== false ? (string) $apiDbUser : '',
	'password' => $apiDbPass !== false ? (string) $apiDbPass : '',
	'database' => $apiDbName !== false ? (string) $apiDbName : '',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => $apiDbDebug,
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => $apiDbCharset !== false && $apiDbCharset !== '' ? (string) $apiDbCharset : 'utf8',
	'dbcollat' => $apiDbCollation !== false && $apiDbCollation !== '' ? (string) $apiDbCollation : 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
