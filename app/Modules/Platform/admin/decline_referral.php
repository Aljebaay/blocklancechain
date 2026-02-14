<?php
require_once __DIR__ . '/../includes/session_bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';

blc_bootstrap_session();
if(!isset($_SESSION['admin_email'])){
	
echo "<script>window.open('login','_self');</script>";
	
}else{

?>

<?php

if(isset($_GET['decline_referral'])){
admin_csrf_require('decline_referral', $input->get('csrf_token'), 'index?view_referrals');
	
$referral_id = (int) $input->get('decline_referral');
if($referral_id <= 0){
	echo "<script>alert('Invalid referral id.');window.open('index?view_referrals','_self');</script>";
	exit;
}

$get_referrals = $db->select("referrals",array("referral_id" => $referral_id));

$row_referrals = $get_referrals->fetch();

$seller_id = $row_referrals->seller_id;


$select_seller = $db->select("sellers",array("seller_id" => $seller_id));

$row_seller = $select_seller->fetch();

$seller_user_name = $row_seller->seller_user_name;
$safeSellerUserName = addslashes((string) $seller_user_name);


$update_referral = $db->update("referrals",array("status" => 'declined'),array("referral_id" => $referral_id));

if($update_referral){

$insert_log = $db->insert_log($admin_id,"referral",$referral_id,"declined");

echo "<script>alert('Referral has been declined successfully. No commision has been rewarded to $safeSellerUserName.');</script>";
	
echo "<script>window.open('index?view_referrals','_self');</script>";
	
}


	
}

?>

<?php } ?>
