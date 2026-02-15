<?php

require_once __DIR__ . "/../includes/session_bootstrap.php";
blc_bootstrap_session();

$uriPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$uriPath = trim((string)$uriPath, "/");

if ($uriPath === "categories" || $uriPath === "") {
	header("Location: ../index.php");
	exit;
}

$prefix = "categories/";
if (strpos($uriPath, $prefix) === 0) {
	$tail = substr($uriPath, strlen($prefix));
	$parts = array_values(array_filter(explode("/", $tail), function ($v) {
		return $v !== "";
	}));

	if (isset($parts[0])) {
		$_GET["cat_url"] = urldecode($parts[0]);
	}
	if (isset($parts[1])) {
		$_GET["cat_child_url"] = urldecode($parts[1]);
	}
	$_REQUEST = array_merge($_REQUEST, $_GET);
}

require __DIR__ . "/category.php";

