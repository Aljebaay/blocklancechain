<?php 
require_once __DIR__ . '/../includes/session_bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';
blc_bootstrap_session();
if(!isset($_SESSION['admin_email'])){
	echo "<script>window.open('login','_self');</script>";
}else{

	function delete_files($target) {
	    if(is_dir($target)){
	        $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
	        foreach( $files as $file ){
	            delete_files( $file );      
	        }
	        @rmdir( $target );
	    } elseif(is_file($target)) {
	        unlink( $target );  
	    }
	}

	if(isset($_GET['delete_plugin'])){
		admin_csrf_require('delete_plugin', $input->get('csrf_token'), 'index?plugins');
		$id = (int) $input->get('delete_plugin');
		if($id <= 0){
			echo "<script>alert('Invalid plugin id.');window.open('index?plugins','_self');</script>";
			exit;
		}
		$folder = (string) $input->get('folder');
		if($folder === '' || preg_match('/^[A-Za-z0-9_-]+$/', $folder) !== 1){
			echo "<script>alert('Invalid plugin folder.');window.open('index?plugins','_self');</script>";
			exit;
		}

		$pluginsRoot = realpath(__DIR__ . "/../plugins");
		if($pluginsRoot === false){
			echo "<script>alert('Plugins directory not found.');window.open('index?plugins','_self');</script>";
			exit;
		}

		$pluginPath = realpath($pluginsRoot . DIRECTORY_SEPARATOR . $folder);
		$pluginsPrefix = rtrim($pluginsRoot, "\\/") . DIRECTORY_SEPARATOR;
		if($pluginPath === false || !is_dir($pluginPath) || strpos($pluginPath, $pluginsPrefix) !== 0){
			echo "<script>alert('Plugin directory not found.');window.open('index?plugins','_self');</script>";
			exit;
		}

		$delete_plugin = $db->delete("plugins",array('id' => $id));
		if($delete_plugin){
			$uninstallFile = $pluginPath . DIRECTORY_SEPARATOR . "uninstall.php";
			if(file_exists($uninstallFile)) {
				include($uninstallFile);
			}
			delete_files($pluginPath . DIRECTORY_SEPARATOR);
			echo "<script>alert('One Plugin has been Deleted.');</script>";
			echo "<script>window.open('index?plugins','_self');</script>";
		}
	}

}
