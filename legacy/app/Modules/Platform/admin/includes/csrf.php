<?php

if (!function_exists('admin_csrf_token')) {
	function admin_csrf_token($scope = 'global') {
		if (!isset($_SESSION['admin_csrf_tokens']) || !is_array($_SESSION['admin_csrf_tokens'])) {
			$_SESSION['admin_csrf_tokens'] = array();
		}

		if (!isset($_SESSION['admin_csrf_tokens'][$scope]) || !is_string($_SESSION['admin_csrf_tokens'][$scope]) || $_SESSION['admin_csrf_tokens'][$scope] === '') {
			$_SESSION['admin_csrf_tokens'][$scope] = bin2hex(random_bytes(32));
		}

		return $_SESSION['admin_csrf_tokens'][$scope];
	}
}

if (!function_exists('admin_csrf_is_valid')) {
	function admin_csrf_is_valid($scope, $token) {
		if (!is_string($token) || $token === '') {
			return false;
		}

		if (!isset($_SESSION['admin_csrf_tokens']) || !is_array($_SESSION['admin_csrf_tokens'])) {
			return false;
		}

		if (!isset($_SESSION['admin_csrf_tokens'][$scope]) || !is_string($_SESSION['admin_csrf_tokens'][$scope])) {
			return false;
		}

		return hash_equals($_SESSION['admin_csrf_tokens'][$scope], $token);
	}
}

if (!function_exists('admin_csrf_require')) {
	function admin_csrf_require($scope, $token, $redirectUrl = 'index') {
		if (admin_csrf_is_valid($scope, $token)) {
			return;
		}

		echo "<script>alert('Invalid security token. Please try again.');window.open('" . $redirectUrl . "','_self');</script>";
		exit;
	}
}
