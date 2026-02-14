<?php

if (!function_exists('blc_session_regenerate_id_safe')) {
	function blc_session_regenerate_id_safe($deleteOldSession = true) {
		if (session_status() !== PHP_SESSION_ACTIVE) {
			return false;
		}

		if (headers_sent()) {
			$_SESSION['__blc_regenerate_pending'] = 1;
			return false;
		}

		return session_regenerate_id((bool) $deleteOldSession);
	}
}

if (!function_exists('blc_bootstrap_session')) {
	function blc_bootstrap_session() {
		if (session_status() === PHP_SESSION_ACTIVE) {
			return;
		}

		$baseDir = dirname(__DIR__);
		$projectBaseDir = $baseDir;
		if (defined('BLC_BASE_PATH') && is_string(BLC_BASE_PATH) && BLC_BASE_PATH !== '') {
			$projectBaseDir = BLC_BASE_PATH;
		} else {
			$rootCandidate = dirname(__DIR__, 4);
			if (is_dir($rootCandidate . DIRECTORY_SEPARATOR . 'app') && is_dir($rootCandidate . DIRECTORY_SEPARATOR . 'public')) {
				$projectBaseDir = $rootCandidate;
			}
		}
		$sessionDirs = array(
			$projectBaseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions',
			$baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions',
			$baseDir . DIRECTORY_SEPARATOR . '.sessions',
			rtrim(sys_get_temp_dir(), "\\/") . DIRECTORY_SEPARATOR . 'blocklancechain_sessions',
		);

		foreach ($sessionDirs as $sessionDir) {
			if (!is_dir($sessionDir)) {
				@mkdir($sessionDir, 0777, true);
			}

			if (is_dir($sessionDir) && is_writable($sessionDir)) {
				session_save_path($sessionDir);
				break;
			}
		}

		$secureCookie = false;
		if (
			(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && strtolower((string) $_SERVER['HTTPS']) !== 'off')
			|| (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
			|| (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && stripos((string) $_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false)
		) {
			$secureCookie = true;
		}

		@ini_set('session.use_strict_mode', '1');
		@ini_set('session.use_only_cookies', '1');

		$cookieParams = session_get_cookie_params();
		$lifetime = isset($cookieParams['lifetime']) ? (int) $cookieParams['lifetime'] : 0;
		$path = isset($cookieParams['path']) ? (string) $cookieParams['path'] : '/';
		$domain = isset($cookieParams['domain']) ? (string) $cookieParams['domain'] : '';

		if (PHP_VERSION_ID >= 70300) {
			session_set_cookie_params(array(
				'lifetime' => $lifetime,
				'path' => $path,
				'domain' => $domain,
				'secure' => $secureCookie,
				'httponly' => true,
				'samesite' => 'Lax',
			));
		} else {
			session_set_cookie_params($lifetime, $path . '; samesite=Lax', $domain, $secureCookie, true);
		}

		session_start();

		if (isset($_SESSION['__blc_regenerate_pending']) && (int) $_SESSION['__blc_regenerate_pending'] === 1) {
			if (!headers_sent()) {
				unset($_SESSION['__blc_regenerate_pending']);
				@session_regenerate_id(true);
			}
		}
	}
}
