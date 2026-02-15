<?php

require_once("../includes/db.php");

if(isset($_FILES["file"]["name"])){

	if(!isset($_FILES["file"]["error"]) || (int) $_FILES["file"]["error"] !== UPLOAD_ERR_OK){
		echo json_encode(array('message' => 'Upload failed'));
		exit;
	}

	$file = $_FILES["file"]["name"];
	$file_tmp = $_FILES["file"]["tmp_name"];
	$file_size = isset($_FILES["file"]["size"]) ? (int) $_FILES["file"]["size"] : 0;
	$maxUploadBytes = 10 * 1024 * 1024; // 10MB

	if(!is_uploaded_file($file_tmp)){
		echo json_encode(array('message' => 'Invalid upload'));
		exit;
	}

	$allowed = array('jpeg','jpg','gif','png','tif','avi','mpeg','mpg','mov','rm','3gp','flv','mp4', 'zip','rar','mp3','wav','docx','csv','xls','pptx','pdf','psd','xd','txt');
	$file_extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
	
	if($file_extension === '' || !in_array($file_extension,$allowed,true)){
	
		$data = array();
		$data['message'] = $lang['alert']['extension_not_supported'];
	
		echo json_encode($data);

	}elseif($file_size > $maxUploadBytes){

		echo json_encode(array('message' => 'File is too large. Maximum size is 10MB.'));

	}else{
	
		$file = pathinfo($file, PATHINFO_FILENAME);
		$file = preg_replace('/[^A-Za-z0-9_-]/', '_', $file);
		if($file === null || $file === ''){
			$file = 'upload';
		}
		$file = $file."_".time().".$file_extension";
   
    	uploadToS3("proposal_files/$file",$file_tmp);
	
		$data = array();
		$data['name'] = $file;
		$data['message'] = "";

		if($enable_s3 == 1){
			$data['url'] = "$s3_domain/proposal_files/$file";
		}else{
			$data['url'] = "$site_url/proposals/proposal_files/$file";
		}
		
		echo json_encode($data);

	}

}