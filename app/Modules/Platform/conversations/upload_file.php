<?php

require_once("../includes/db.php");

if(!isset($_SESSION['seller_user_name'])){
	http_response_code(401);
	echo "Unauthorized";
	exit;
}

if(isset($_FILES["file"]["name"])){
	if(!isset($_FILES["file"]["error"]) || (int) $_FILES["file"]["error"] !== UPLOAD_ERR_OK){
		http_response_code(400);
		echo "Upload failed";
		exit;
	}

	$file = $_FILES["file"]["name"];
	$file_tmp = $_FILES["file"]["tmp_name"];
	$file_size = isset($_FILES["file"]["size"]) ? (int) $_FILES["file"]["size"] : 0;

	if(!is_uploaded_file($file_tmp)){
		http_response_code(400);
		echo "Invalid upload";
		exit;
	}

    $allowed = array('jpeg','jpg','gif','png','tif','avi','mpeg','mpg','mov','rm','3gp','flv','mp4','zip','rar','mp3','wav','docx','csv','xls','xlsx','pptx','pdf','txt','psd','xd','txt');
	$maxUploadBytes = 10 * 1024 * 1024;

	$file_extension = strtolower((string) pathinfo($file, PATHINFO_EXTENSION));
	if($file_extension === '' || !in_array($file_extension,$allowed,true)){
		echo $lang['alert']['extension_not_supported'];
	}elseif($file_size > $maxUploadBytes){
		echo isset($lang['alert']['file_too_large']) ? $lang['alert']['file_too_large'] : 'File is too large';
	}else{
		$file = pathinfo($file, PATHINFO_FILENAME);
		$file = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $file);
		if($file === null || $file === ''){
			$file = 'upload';
		}
		$file = $file . "_" . time() . "." . $file_extension;
      uploadToS3("conversations_files/$file",$file_tmp);
		echo $file;	
	}
}
