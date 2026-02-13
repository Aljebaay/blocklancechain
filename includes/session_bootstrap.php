<?php

if (!function_exists('blc_bootstrap_session')) {
	function blc_bootstrap_session() {
		if (session_status() === PHP_SESSION_ACTIVE) {
			return;
		}

		$sessionDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.sessions';
		if (!is_dir($sessionDir)) {
			@mkdir($sessionDir, 0777, true);
		}

		if (is_dir($sessionDir) && is_writable($sessionDir)) {
			session_save_path($sessionDir);
		}

		session_start();
	}
}

