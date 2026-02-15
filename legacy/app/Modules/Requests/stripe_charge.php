<?php
$root = dirname(__DIR__, 3);
$platformRoot = defined('BLC_PLATFORM_PATH') ? BLC_PLATFORM_PATH : ($root . '/app/Modules/Platform');
require_once $platformRoot . '/includes/session_bootstrap.php';
blc_bootstrap_session();
include $platformRoot . "/includes/db.php";
include $platformRoot . "/functions/payment.php";
include $platformRoot . "/functions/processing_fee.php";
if(!isset($_SESSION['seller_user_name'])){
echo "<script>window.open('../login.php','_self');</script>";
exit;
}

$offerId = isset($_SESSION['c_offer_id']) ? (int) $_SESSION['c_offer_id'] : 0;
if ($offerId <= 0) {
    echo "<script>window.open('manage_requests','_self');</script>";
    exit;
}

$select_offers = $db->select("send_offers",array("offer_id" => $offerId));
$row_offers = $select_offers->fetch();
if (!$row_offers) {
    echo "<script>window.open('manage_requests','_self');</script>";
    exit;
}
$request_id = $row_offers->request_id;
$proposal_id = $row_offers->proposal_id;
$amount = $row_offers->amount;
$processing_fee = processing_fee($amount);

$select_proposals = $db->select("proposals",array("proposal_id" => $proposal_id));
$row_proposals = $select_proposals->fetch();
$proposal_title = $row_proposals->proposal_title;

$reference_no = mt_rand();

$data = [];
$data['type'] = "request_offer";
$data['content_id'] = $offerId;
$data['name'] = $proposal_title;
$data['desc'] = 'Request Offer Payment';
$data['qty'] = 1;
$data['price'] = $amount;
$data['sub_total'] = $amount;
$data['processing_fee'] = $processing_fee;
$data['total'] = $amount + $processing_fee;

$data['cancel_url'] = "$site_url/cancel_payment";

$payment = new Payment();
$payment->stripe($data);
