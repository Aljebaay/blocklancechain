<?php 
require_once __DIR__ . '/../includes/session_bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';

blc_bootstrap_session();
if(!isset($_SESSION['admin_email'])){
	
echo "<script>window.open('login','_self');</script>";
	
}else{

?>

<?php 

if(isset($_GET['delete_log'])){
admin_csrf_require('delete_log', $input->get('csrf_token'), 'index?admin_logs');
	
$id = (int) $input->get('delete_log');
if($id <= 0){
	echo "<script>alert('Invalid log id.');window.open('index?admin_logs','_self')</script>";
	exit;
}

$delete_log = $db->delete("admin_logs",array("id"=>$id)); 
	
if($delete_log){

echo "<script>alert('One admin log has been deleted successfully.');</script>";
	
echo "<script>window.open('index?admin_logs','_self')</script>";
	
}

}

?>

<?php } ?>
