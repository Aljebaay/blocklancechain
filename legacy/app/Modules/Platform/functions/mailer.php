<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/// Send Mail Function Starts ///

function send_mail($data,$temp_name=''){

   global $db;
   global $dir;
   global $site_name;
   global $site_email_address;
   global $site_logo;
   global $s_currency;

   global $mail_library;
   global $enable_smtp;
   global $s_host;
   global $s_port;
   global $s_secure;
   global $s_username;
   global $s_password;

   $site_email = $site_email_address;

   if(!function_exists("blc_require_vendor_autoload")){
      function blc_require_vendor_autoload($dir){
         $candidates = array(
            rtrim($dir, "\\/") . '/vendor/autoload.php',
            dirname(rtrim($dir, "\\/")) . '/vendor/autoload.php',
         );
         foreach($candidates as $candidate){
            if(is_file($candidate)){
               require_once $candidate;
               return true;
            }
         }
         return false;
      }
   }

   blc_require_vendor_autoload($dir);

   // echo load_view($data['template'],$data);
   // exit();
   
   $mail = new PHPMailer();
   // $mail->SMTPDebug = 2;
   try {
      // Swift Mailer is deprecated; keep legacy config values but send through PHPMailer.
      if($enable_smtp == "yes"){
         $mail->isSMTP();
         $mail->Host = $s_host;
         $mail->Port = $s_port;
         $mail->SMTPAuth = true;
         $mail->SMTPSecure = $s_secure;
         $mail->Username = $s_username;
         $mail->Password = $s_password;
      }

      $mail->CharSet = 'UTF-8';
      $mail->setFrom($site_email,$site_name);
      $mail->addAddress($data['to']);
      $mail->addReplyTo($site_email,$site_name);
      $mail->isHTML(true);

      $mail->Subject = $data['subject'];
      $mail->Body = load_view($data['template'],$data);

      if($mail->send()){ 
         return true;
      }

   }catch(Exception $e){
      print_r($e->getMessage());
   }

}

/// Send Mail Function Ends ///


function load_view($file,$data=''){
   global $db;
   global $dir;
   global $site_url;
   global $site_name;
   global $site_email_address;
   global $site_logo;
   global $s_currency;
   global $template_folder;
   
   if($data['template'] != "email_confirmation"){ 
      $file = "main"; 
   }else{
      $file = "main2";
   }

   $lang = ( isset($data['lang']) && !empty($data['lang']) ) ? $data['lang'] : $template_folder;

   ob_start();
   require("$dir"."emails/templates/{$lang}/$file.php");
   return ob_get_clean();
}


function img_url($url){
   global $site_url;
   // $site_url = "https://www.gig-zone.com";
   echo $site_url."/images/email/".$url;
}


/// Send Admin Mail Function Starts ///

function send_admin_mail($data,$temp_name){

   global $con;
   global $dir;
    
   // where admin_role='administrator'
   $get_admins = "select * from admins where admin_role='administrator'";
   $run_admins = mysqli_query($con,$get_admins);
   while($row_admins = mysqli_fetch_array($run_admins)){
   $admin_id = $row_admins['admin_id'];
   $admin_name = $row_admins['admin_name'];
   $admin_email = $row_admins['admin_email'];

   $data['admin_name'] = $admin_name;
   $data['to'] = $admin_email;
   
   send_mail($data,$temp_name);

   }

}

/// Send Admin Mail Function Ends ///


?>
