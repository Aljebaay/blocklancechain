<?php
require_once __DIR__ . '/includes/session_bootstrap.php';

blc_bootstrap_session();
include("includes/db.php");

include("functions/functions.php");

switch($_REQUEST['zAction']){
	
	default:
	
	get_tag_proposals();
	
	break;
	
	case "get_tag_pagination":
	
	get_tag_pagination();
	
	break;
	
}


?>