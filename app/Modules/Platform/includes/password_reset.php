<?php

if (!function_exists('blc_password_reset_ensure_table')) {
	function blc_password_reset_ensure_table($db) {
		static $initialized = false;
		if ($initialized) {
			return;
		}

		$db->query(
			"CREATE TABLE IF NOT EXISTS password_resets (
				id INT(11) NOT NULL AUTO_INCREMENT,
				seller_id INT(10) NOT NULL,
				email VARCHAR(255) NOT NULL,
				selector VARCHAR(32) NOT NULL,
				token_hash VARCHAR(255) NOT NULL,
				expires_at DATETIME NOT NULL,
				used_at DATETIME DEFAULT NULL,
				created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY uq_password_resets_selector (selector),
				KEY idx_password_resets_seller (seller_id),
				KEY idx_password_resets_expires (expires_at)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
		);

		$initialized = true;
	}
}

if (!function_exists('blc_password_reset_issue')) {
	function blc_password_reset_issue($db, $sellerId, $email, $ttlSeconds = 3600) {
		$sellerId = (int) $sellerId;
		if ($sellerId <= 0 || !is_string($email) || trim($email) === '') {
			return null;
		}

		blc_password_reset_ensure_table($db);
		$db->query(
			"DELETE FROM password_resets WHERE seller_id = :seller_id OR expires_at < :now OR used_at IS NOT NULL",
			array(
				"seller_id" => $sellerId,
				"now" => date('Y-m-d H:i:s'),
			)
		);

		$selector = bin2hex(random_bytes(8));
		$token = bin2hex(random_bytes(32));
		$tokenHash = password_hash($token, PASSWORD_DEFAULT);
		$expiresAt = date('Y-m-d H:i:s', time() + max(300, (int) $ttlSeconds));

		$inserted = $db->insert(
			"password_resets",
			array(
				"seller_id" => $sellerId,
				"email" => trim($email),
				"selector" => $selector,
				"token_hash" => $tokenHash,
				"expires_at" => $expiresAt,
			)
		);

		if (!$inserted) {
			return null;
		}

		return array(
			"selector" => $selector,
			"token" => $token,
			"expires_at" => $expiresAt,
		);
	}
}

if (!function_exists('blc_password_reset_resolve')) {
	function blc_password_reset_resolve($db, $selector, $token) {
		$selector = is_string($selector) ? trim($selector) : '';
		$token = is_string($token) ? trim($token) : '';
		if ($selector === '' || $token === '') {
			return null;
		}
		if (preg_match('/^[a-f0-9]{16}$/', $selector) !== 1 || preg_match('/^[a-f0-9]{64}$/', $token) !== 1) {
			return null;
		}

		blc_password_reset_ensure_table($db);
		$row = $db->query(
			"SELECT * FROM password_resets WHERE selector = :selector AND used_at IS NULL LIMIT 1",
			array("selector" => $selector)
		)->fetch();
		if (!$row) {
			return null;
		}

		$expiresAtTs = strtotime((string) $row->expires_at);
		if ($expiresAtTs === false || $expiresAtTs < time()) {
			return null;
		}

		if (!password_verify($token, (string) $row->token_hash)) {
			return null;
		}

		return $row;
	}
}

if (!function_exists('blc_password_reset_mark_used')) {
	function blc_password_reset_mark_used($db, $resetId, $sellerId = 0) {
		$resetId = (int) $resetId;
		$sellerId = (int) $sellerId;
		blc_password_reset_ensure_table($db);

		if ($resetId > 0) {
			$db->update("password_resets", array("used_at" => date('Y-m-d H:i:s')), array("id" => $resetId));
		}

		if ($sellerId > 0) {
			$db->query("DELETE FROM password_resets WHERE seller_id = :seller_id", array("seller_id" => $sellerId));
		}
	}
}
