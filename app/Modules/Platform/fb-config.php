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
if(!class_exists("\\Facebook\\Facebook")){
   require_once __DIR__ . "/Facebook/autoload.php";
}

$FB = new \Facebook\Facebook([
   'app_id' => $fb_app_id,
   'app_secret' => $fb_app_secret,
   'default_graph_version' => 'v2.10',
]);

$helper = $FB->getRedirectLoginHelper();
