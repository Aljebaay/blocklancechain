<?php
require_once("../includes/db.php");

if(!isset($_SESSION['seller_user_name'])){
	http_response_code(401);
	echo "Unauthorized";
	exit;
}

$loginSellerUserName = $_SESSION['seller_user_name'];
$loginSeller = $db->select("sellers",array("seller_user_name" => $loginSellerUserName))->fetch();
if(!$loginSeller){
	http_response_code(401);
	echo "Unauthorized";
	exit;
}

$status = isset($_POST['status']) ? $input->post('status') : '';
if($status !== "typing" && $status !== "untyping"){
	http_response_code(400);
	echo "Invalid status";
	exit;
}

$message_group_id = $input->post('message_group_id');
if(!is_numeric($message_group_id) || (int) $message_group_id <= 0){
	http_response_code(400);
	echo "Invalid message group";
	exit;
}

$seller_id = (int) $loginSeller->seller_id;
$message_group_id = (int) $message_group_id;

if($status === "typing"){
	$time = date("Y-m-d H:i:s");
	$select = $db->select("seller_type_status",array("seller_id"=>$seller_id,"message_group_id"=>$message_group_id));
	$count = $select->rowCount();
	if($count == 0){
		$db->insert("seller_type_status",array("seller_id"=>$seller_id,"message_group_id"=>$message_group_id,"time"=>$time,"status"=>$status));
	}else{
		$db->update("seller_type_status",array("time"=>$time,"status"=>$status),array("seller_id"=>$seller_id,"message_group_id"=>$message_group_id));
	}
}else{
	$db->update("seller_type_status",array("status"=>$status),array("seller_id"=>$seller_id,"message_group_id"=>$message_group_id));
}
