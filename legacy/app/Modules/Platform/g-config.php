<?php
require_once __DIR__ . '/includes/session_bootstrap.php';
	blc_bootstrap_session();
	require_once "includes/db.php";
	$composerAutoloadCandidates = [
		__DIR__ . "/vendor/autoload.php",
		dirname(__DIR__) . "/vendor/autoload.php",
	];
	foreach($composerAutoloadCandidates as $composerAutoload){
		if(is_file($composerAutoload)){
			require_once $composerAutoload;
			break;
		}
	}
	if(!class_exists("Google_Client")){
		require_once __DIR__ . "/GoogleAPI/vendor/autoload.php";
	}
	$gClient = new Google_Client();
	$gClient->setClientId($g_client_id);
	$gClient->setClientSecret($g_client_secret);
	$gClient->setApplicationName("");
	$gClient->setRedirectUri("$site_url/g-callback");
	$gClient->addScope("https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email");
