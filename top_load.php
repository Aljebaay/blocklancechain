<?php
require_once __DIR__ . '/includes/session_bootstrap.php';

blc_bootstrap_session();
require_once("includes/db.php");
require_once("functions/functions.php");

switch($_REQUEST['zAction']){
	
	default:
		get_top_proposals();
	break;

	case "get_top_pagination":
		get_top_pagination();
	break;

}