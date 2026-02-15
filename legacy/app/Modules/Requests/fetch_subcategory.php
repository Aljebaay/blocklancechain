<?php
$root = dirname(__DIR__, 3);
$platformRoot = defined('BLC_PLATFORM_PATH') ? BLC_PLATFORM_PATH : ($root . '/app/Modules/Platform');
require_once $platformRoot . '/includes/session_bootstrap.php';

blc_bootstrap_session();
require_once $platformRoot . "/includes/db.php";

if(!isset($_SESSION['seller_user_name'])){
	
echo "<script>window.open('../login','_self')</script>";
exit;
	
}


echo "<option value=''> {$lang['placeholder']['select_sub_category']} </option>";

$category_id = $input->post('category_id');

$get_c_cats = $db->select("categories_children",array("child_parent_id" => $category_id));

while($row_c_cats = $get_c_cats->fetch()){
	
$child_id = $row_c_cats->child_id;


$get_meta = $db->select("child_cats_meta",array("child_id" => $child_id,"language_id" => $siteLanguage));

$row_meta = $get_meta->fetch();

$child_title = $row_meta->child_title;


echo "<option value='$child_id'> $child_title </option>";
	
}

?>
