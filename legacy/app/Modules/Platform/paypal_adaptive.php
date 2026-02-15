<?php
require_once __DIR__ . '/includes/session_bootstrap.php';

blc_bootstrap_session();
require_once("includes/db.php");

if(!isset($_SESSION['seller_user_name'])){
	echo "<script>window.open('login','_self')</script>";
	exit;
}

if(!function_exists('blc_paypal_base_url')){
	function blc_paypal_base_url(string $paypalSandbox): string {
		return ($paypalSandbox === "on")
			? "https://api-m.sandbox.paypal.com"
			: "https://api-m.paypal.com";
	}
}

if(!function_exists('blc_paypal_decode_json')){
	function blc_paypal_decode_json(string $body): array {
		$decoded = json_decode($body, true);
		return is_array($decoded) ? $decoded : array();
	}
}

if(!function_exists('blc_paypal_get_access_token')){
	function blc_paypal_get_access_token(string $clientId, string $clientSecret, string $baseUrl): string {
		if($clientId === "" || $clientSecret === ""){
			throw new RuntimeException("PayPal credentials are missing.");
		}

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => rtrim($baseUrl, "/") . "/v1/oauth2/token",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_USERPWD => $clientId . ":" . $clientSecret,
			CURLOPT_HTTPHEADER => array(
				"Accept: application/json",
				"Accept-Language: en_US"
			),
			CURLOPT_POSTFIELDS => "grant_type=client_credentials",
			CURLOPT_TIMEOUT => 30
		));

		$responseBody = curl_exec($ch);
		$curlError = curl_error($ch);
		$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if($responseBody === false){
			throw new RuntimeException("PayPal token request failed: " . $curlError);
		}

		$decoded = blc_paypal_decode_json($responseBody);
		if($httpCode >= 400 || empty($decoded["access_token"])){
			throw new RuntimeException("PayPal token response was invalid.");
		}

		return (string)$decoded["access_token"];
	}
}

if(!function_exists('blc_paypal_create_payout')){
	function blc_paypal_create_payout(
		string $baseUrl,
		string $accessToken,
		string $receiverEmail,
		float $amount,
		string $currencyCode,
		string $siteName
	): array {
		$payload = array(
			"sender_batch_header" => array(
				"sender_batch_id" => "batch_" . uniqid("", true),
				"email_subject" => "You Have Paypal Payout Payment From " . $siteName
			),
			"items" => array(
				array(
					"recipient_type" => "EMAIL",
					"receiver" => $receiverEmail,
					"sender_item_id" => "item_" . uniqid("", true),
					"note" => "Payout from " . $siteName,
					"amount" => array(
						"value" => number_format($amount, 2, ".", ""),
						"currency" => $currencyCode
					)
				)
			)
		);

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => rtrim($baseUrl, "/") . "/v1/payments/payouts",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json",
				"Authorization: Bearer " . $accessToken
			),
			CURLOPT_POSTFIELDS => json_encode($payload),
			CURLOPT_TIMEOUT => 40
		));

		$responseBody = curl_exec($ch);
		$curlError = curl_error($ch);
		$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if($responseBody === false){
			throw new RuntimeException("PayPal payout request failed: " . $curlError);
		}

		$decoded = blc_paypal_decode_json($responseBody);
		if($httpCode >= 400){
			throw new RuntimeException("PayPal payout API returned an error.");
		}

		return $decoded;
	}
}


$get_payment_settings = $db->select("payment_settings");

$row_payment_settings = $get_payment_settings->fetch();

$withdrawal_limit = $row_payment_settings->withdrawal_limit;

$paypal_email = $row_payment_settings->paypal_email;

$paypal_currency_code = $row_payment_settings->paypal_currency_code;

$paypal_app_client_id = $row_payment_settings->paypal_app_client_id;

$paypal_app_client_secret = $row_payment_settings->paypal_app_client_secret;

$paypal_sandbox = $row_payment_settings->paypal_sandbox;

$login_seller_user_name = $_SESSION['seller_user_name'];
$select_login_seller = $db->select("sellers",array("seller_user_name" => $login_seller_user_name));
$row_login_seller = $select_login_seller->fetch();
$login_seller_id = $row_login_seller->seller_id;
$login_seller_paypal_email = $row_login_seller->seller_paypal_email;


$select_seller_accounts = $db->select("seller_accounts",array("seller_id" => $login_seller_id));
$row_seller_accounts = $select_seller_accounts->fetch();
$current_balance = $row_seller_accounts->current_balance;

if(isset($_POST['withdraw'])){

$amount = (float)$input->post('amount');


if($amount > $withdrawal_limit or $amount == $withdrawal_limit){

if($amount < $current_balance or $amount == $current_balance){

try{
	if(empty($login_seller_paypal_email)){
		throw new RuntimeException("Missing seller paypal email.");
	}

	$apiBaseUrl = blc_paypal_base_url((string)$paypal_sandbox);
	$accessToken = blc_paypal_get_access_token(
		trim((string)$paypal_app_client_id),
		trim((string)$paypal_app_client_secret),
		$apiBaseUrl
	);

	$payoutResponse = blc_paypal_create_payout(
		$apiBaseUrl,
		$accessToken,
		trim((string)$login_seller_paypal_email),
		(float)$amount,
		trim((string)$paypal_currency_code),
		(string)$site_name
	);

	if(!empty($payoutResponse)){

//// $update_seller_account = $db->update("seller_accounts",array("current_balance"=>"current_balance-$amount","withdrawn" => "withdrawn+$amount"),array("seller_id" => $login_seller_id));

$update_seller_account = $db->query(
	"update seller_accounts set current_balance=current_balance-:minus,withdrawn=withdrawn+:plus where seller_id=:seller_id",
	array("minus"=>$amount,"plus"=>$amount,"seller_id"=>$login_seller_id)
);

if($update_seller_account){

$update_seller = $db->query(
	"update sellers set seller_payouts=seller_payouts+1 where seller_id=:seller_id",
	array("seller_id"=>$login_seller_id)
);

$date = date("F d, Y");

$ref = "P-".mt_rand(100000,999999).strtoupper(substr(md5((string)microtime(true)), 0, 2));
if(!empty($payoutResponse['batch_header']['payout_batch_id'])){
	$ref = (string)$payoutResponse['batch_header']['payout_batch_id'];
}

$insert_withdrawal = $db->insert("payouts",array("seller_id"=>$login_seller_id,"ref"=>$ref,"method"=>"paypal","amount"=>$amount,"date"=>$date,"status"=>'completed'));

echo "<script>alert('Your funds ($$amount) has been transferred to your paypal account successfully.');</script>";
	
echo "<script>window.open('$site_url/revenue','_self')</script>";

}


}
	
}catch(Throwable $ex){

// echo "<pre>";
// 	print_r($ex);
// echo "</pre>";

echo "<script>
	alert('Sorry An error occurred During Sending Your Money To Your Paypal Account.');
	window.open('revenue','_self')
</script>";
	
}


}else{
	
echo "<script>alert('Opps! the amount you entered is higher than your current balance.');</script>";
	
echo "<script>window.open('revenue','_self')</script>";
	
}

	
}else{
	
echo "<script>alert('Minimum withdrawal amount is $$withdrawal_limit Dollars.');</script>";
	
echo "<script>window.open('revenue','_self')</script>";
	
}

	
}

?>
