<?php

if (!function_exists('blc_bootstrap_session')) {
	function blc_bootstrap_session() {
		if (session_status() === PHP_SESSION_ACTIVE) {
			return;
		}

		$baseDir = dirname(__DIR__);
		$sessionDirs = array(
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

		session_start();
	}
}
