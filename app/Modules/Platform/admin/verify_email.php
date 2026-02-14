<?php 
require_once __DIR__ . '/../includes/session_bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';

blc_bootstrap_session();
if(!isset($_SESSION['admin_email'])){
	
echo "<script>window.open('login','_self');</script>";
	
}else{

if(isset($_GET['verify_email'])){
admin_csrf_require('verify_email', $input->get('csrf_token'), 'index?view_sellers');

$seller_id = (int) $input->get('verify_email');
if($seller_id <= 0){
	echo "<script>alert_error('Invalid seller id.','index?view_sellers');</script>";
	exit;
}

$update_seller = $db->update("sellers",array("seller_verification" => 'ok'),array("seller_id" => $seller_id));

if($update_seller){

echo "<script>alert_success('Seller email has been verified successfully.','index?view_sellers');</script>";
	
}
	
}

?>

<?php } ?>
