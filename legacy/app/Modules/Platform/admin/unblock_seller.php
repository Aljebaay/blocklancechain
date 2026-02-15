<?php
require_once __DIR__ . '/../includes/session_bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';

blc_bootstrap_session();
if(!isset($_SESSION['admin_email'])){
	
echo "<script>window.open('login','_self');</script>";
	
}else{

?>

<?php

if(isset($_GET['unblock_seller'])){
admin_csrf_require('unblock_seller', $input->get('csrf_token'), 'index?view_sellers');
	
$seller_id = (int) $input->get('unblock_seller');
if($seller_id <= 0){
	echo "<script>alert('Invalid seller id.');window.open('index?view_sellers','_self');</script>";
	exit;
}
	
$update_seller = $db->update("sellers",array("seller_status" => 'away'),array("seller_id" => $seller_id));
	
if($update_seller){

$update_proposals = $db->update("proposals",array("proposal_status"=>'active'),array("proposal_seller_id"=>$seller_id,"proposal_status" => 'active'));

$insert_log = $db->insert_log($admin_id,"seller",$seller_id,"unblocked");

echo "<script>alert('This Seller Has Been Unblocked ,He Is Able To Login Into His Account.');</script>";
	
echo "<script>window.open('index?view_sellers','_self');</script>";

	
}
	
}

?>

<?php } ?>
