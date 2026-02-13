<?php
require_once __DIR__ . '/../includes/session_bootstrap.php';

blc_bootstrap_session();
unset($_SESSION['admin_email']);

if(isset($_GET['session_expired'])){
	
echo "<script>window.open('login.php?session_expired','_self');</script>";
	
}else{

echo "<script>window.open('login','_self');</script>";

}

?>