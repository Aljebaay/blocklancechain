<?php 
require_once __DIR__ . '/../includes/session_bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';

blc_bootstrap_session();
if(!isset($_SESSION['admin_email'])){
	
echo "<script>window.open('login','_self');</script>";
	
}else{

?>

<?php

if(isset($_GET['delete_all_logs'])){
admin_csrf_require('delete_all_logs', $input->get('csrf_token'), 'index?admin_logs');
	
$delete_logs = $db->delete("admin_logs",""); 
	
if($delete_logs){

echo "<script>alert('All admin logs has been deleted successfully.');</script>";
	
echo "<script>window.open('index?admin_logs','_self')</script>";
	
}

}

?>

<?php } ?>
