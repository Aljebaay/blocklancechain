<?php

function base_url($url=''){
	global $site_url;
	echo rtrim($site_url, "/")."/".ltrim($url, "/");
}

function dynamicUrl($url, $forceInternal = false){
   global $site_url;

   $base = rtrim($site_url, "/");
   $url = trim((string)$url);

   if($url === ""){
      return $base;
   }

   if(preg_match('/^(#|mailto:|tel:|javascript:)/i', $url)){
      return $url;
   }

   $parts = parse_url($url);
   if($parts === false){
      return $url;
   }

   $hasScheme = isset($parts['scheme']);
   $hasHost = isset($parts['host']);

   if($hasScheme || $hasHost){
      if(!$forceInternal){
         return $url;
      }

      $path = isset($parts['path']) ? $parts['path'] : "/";
      $normalized = $base . (strpos($path, "/") === 0 ? $path : "/" . $path);

      if(isset($parts['query']) && $parts['query'] !== ""){
         $normalized .= "?" . $parts['query'];
      }
      if(isset($parts['fragment']) && $parts['fragment'] !== ""){
         $normalized .= "#" . $parts['fragment'];
      }

      return $normalized;
   }

   if(strpos($url, "/") === 0){
      return $base . $url;
   }

   return $base . "/" . $url;
}

function showPrice($price,$class='',$show_symbol=''){
	
	global $db;
   global $s_currency;
	global $currency_position;
	global $currency_format;

   if($price === null || $price === ''){
      $price = 0;
   }

   if($show_symbol == ''){
      $show_symbol = 'yes';
   }
   
   if(isset($_SESSION['siteCurrency'])){

      $id = $_SESSION['siteCurrency'];
      $get_currency = $db->select("site_currencies",array('id' => $id));
      $row = $get_currency->fetch();
      $currency_id = $row->currency_id;
      $currency_position = $row->position;
      $currency_format = $row->format;
      $rate = isset($_SESSION['conversionRate']) ? $_SESSION['conversionRate'] : 1;

      $get_currencies = $db->select("currencies",array("id" => $currency_id));
      $row_currencies = $get_currencies->fetch();
      $site_currency = $row_currencies->symbol;
      $price = $price*$rate;

   }else{
      $site_currency = $s_currency;
   }

	// $price = $price / 100;

   $dec_point = '.';
   $thousands_sep = ',';
   if($currency_format == 'european'){
      $dec_point = ',';
      $thousands_sep = '.';
   }
   if(is_int($price)){
      $price = number_format($price, 2, $dec_point, $thousands_sep);
   }else{
      $price = number_format($price, 2, $dec_point, $thousands_sep);
   }

   if(!empty($class)){
      $price = "<span class='$class'>$price</span>";
   }

   if($show_symbol == 'yes'){

      if($currency_position == "left"){
         return $site_currency.$price;
      }else{
         return $price.$site_currency;
      }

   }else{
      return $price;
   }

}

function redirect($url){
	echo "<script>window.open('$url','_self');</script>";
}

function successRedirect($text,$url){
	echo "<script>alert_success('$text','$url');</script>";
}

function messageRedirect($text,$url){
	echo "<script>
	alert('$text');
	window.open('$url','_self');
	</script>";
}

function showMessage($text){
	echo "<script>alert('$text')</script>";
}

function sendPushMessage($notification_id){

   global $site_url;
   
   /// Added by Pixinal Studio For Push Notification
   $curl = curl_init();
   curl_setopt_array($curl, array(
      CURLOPT_URL => "$site_url/api/v1/single-notification/".$notification_id,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET"
   ));
   $response = curl_exec($curl);
   $err = curl_error($curl);
   curl_close($curl);
   /// End For Push Notification

}

// Check if we are in a local environment
function is_localhost(){
  
   // set the array for testing the local environment
   $whitelist = array('127.0.0.1','::1');

   // check if the server is in the array
   if (in_array( $_SERVER['REMOTE_ADDR'],$whitelist)){
      // this is a local environment
      return true;
   }
  
}


function check_purchase(){
   // Open-source mode: licensing checks are intentionally disabled.
   return 1;
}

