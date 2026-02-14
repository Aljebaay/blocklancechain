<?php
require_once __DIR__ . '/../includes/session_bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';

blc_bootstrap_session();
if(!isset($_SESSION['admin_email'])){
	
echo "<script>window.open('login','_self');</script>";
	
}else{

?>

<?php

if(isset($_GET['delete_user'])){
	admin_csrf_require('delete_user', $input->get('csrf_token'), 'index?view_users');
	
$delete_id = (int) $input->get('delete_user');

	if($delete_id <= 0){
		echo "<script>alert('Invalid admin id.');</script>";
		echo "<script>window.open('index?view_users','_self');</script>";
		exit;
	}

	$currentAdminId = isset($login_admin_id) ? (int) $login_admin_id : 0;
	if($currentAdminId > 0 && $delete_id === $currentAdminId){
		echo "<script>alert('You cannot delete your own admin account.');</script>";
		echo "<script>window.open('index?view_users','_self');</script>";
		exit;
	}
	
$delete_admin = $db->delete("admins",array('admin_id' => $delete_id));
		
if($delete_admin){
	
echo "<script>alert('Admin has been deleted successfully.');</script>";

echo "<script>window.open('index?view_users','_self');</script>";

}

	
}

?>

<?php } ?>
