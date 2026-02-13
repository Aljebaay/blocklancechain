<?php

require_once __DIR__ . "/includes/session_bootstrap.php";
blc_bootstrap_session();

if (PHP_SAPI === "cli-server" && isset($_SERVER["SCRIPT_NAME"]) && $_SERVER["SCRIPT_NAME"] === "/index.php") {
	$uriPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
	$uriPath = $uriPath ?: "/";
	$docRoot = __DIR__;
	$fullPath = $docRoot . $uriPath;

	if ($uriPath !== "/" && $uriPath !== "/index.php") {
		if (
			preg_match('/\.(?:js|css|map|png|jpe?g|gif|svg|ico|woff2?|ttf|eot|webp|mp4|webm|pdf|json)$/i', $uriPath) &&
			!is_file($fullPath)
		) {
			http_response_code(404);
			echo "Not Found";
			exit;
		}

		$phpPath = $docRoot . rtrim($uriPath, "/") . ".php";
		if (is_file($phpPath)) {
			require $phpPath;
			exit;
		}

		$segments = explode("/", trim($uriPath, "/"));

		if (!empty($segments) && $segments[0] === "categories" && isset($segments[1])) {
			$_GET["cat_url"] = urldecode($segments[1]);
			if (isset($segments[2])) {
				$_GET["cat_child_url"] = urldecode($segments[2]);
			}
			$_REQUEST = array_merge($_REQUEST, $_GET);
			require $docRoot . "/categories/category.php";
			exit;
		}

		if (count($segments) === 1 && preg_match('/^[0-9a-zA-Z_-]+$/', $segments[0])) {
			$_GET["slug"] = $segments[0];
			$_REQUEST = array_merge($_REQUEST, $_GET);
			require $docRoot . "/handler.php";
			exit;
		}
	}
}

require_once("includes/db.php");
require_once("functions/functions.php");
if(strpos($_SERVER["REQUEST_URI"], 'index') !== false){
	header("location: $site_url");
}
require_once("social-config.php");
$site_title = $row_general_settings->site_title;

?>
<!DOCTYPE html>
<html lang="en" class="ui-toolkit">
<head>
	<title><?= $site_title; ?></title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="<?= $site_desc; ?>">
	<meta name="keywords" content="<?= $site_keywords; ?>">
	<meta name="author" content="<?= $site_author; ?>">
  	<link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700,300,100" rel="stylesheet">
	<link href="<?= $site_url; ?>/styles/bootstrap.css" rel="stylesheet">
  	<link href="<?= $site_url; ?>/styles/custom.css" rel="stylesheet"> <!-- Custom css code from modified in admin panel --->
	<link href="<?= $site_url; ?>/styles/styles.css" rel="stylesheet">
	<link href="<?= $site_url; ?>/styles/categories_nav_styles.css" rel="stylesheet">
	<link href="<?= $site_url; ?>/font_awesome/css/font-awesome.css" rel="stylesheet">
	<link href="<?= $site_url; ?>/styles/owl.carousel.css" rel="stylesheet">
	<link href="<?= $site_url; ?>/styles/owl.theme.default.css" rel="stylesheet">
	<link href="<?= $site_url; ?>/styles/sweat_alert.css" rel="stylesheet">
	<link href="<?= $site_url; ?>/styles/animate.css" rel="stylesheet">
  	<?php if($row_general_settings->knowledge_bank == 'yes'): ?>
		<link href="<?= $site_url; ?>/styles/knowledge_bank.css" rel="stylesheet">
	<?php endif ?>
	<?php if(!empty($site_favicon)){ ?>
		<link rel="shortcut icon" href="<?= $site_favicon; ?>" type="image/x-icon" />
	<?php } ?>
	<!-- Optional: include a polyfill for ES6 Promises for IE11 and Android browser -->
	<script src="<?= $site_url; ?>/js/ie.js"></script>
	<script type="text/javascript" src="<?= $site_url; ?>/js/sweat_alert.js"></script>
	<script type="text/javascript" src="<?= $site_url; ?>/js/jquery.min.js"></script>
	<style>.swal2-popup .swal2-styled.swal2-confirm{background-color: #28a745;}</style>
</head>
<body class="is-responsive">
<?php

	require_once("includes/header.php");

	if(!isset($_SESSION['seller_user_name'])){
		require_once("home.php");
	}else{
		require_once("user_home.php");
	}

	require_once("includes/footer.php"); 

?>
<?php if($row_general_settings->knowledge_bank == 'yes'): ?>
<div class="sm popup-support-wrap">
	<div class="popup-support">
		<header class="hero-container" style="background-color: rgb(29,191,115); color: rgb(255,255,255);">
			<div class="hero">
				<h1 class="main-title">
					<a href="#" class="sm-back">
						<i class="pull-left fa fa-angle-left"></i>
					</a> 
					<?= $lang["popup"]['knowledge_bank']['title']; ?>
				</h1>
				<a class="support-nav" href="#"><?= $lang["popup"]['knowledge_bank']['heading']; ?></a>
				<h2 class="sub-title"></h2>
				<div class="search-box">
					<div class="search-placeholder">
						<span class="svg-icon search-magnifier"><i class="fa fa-search"></i></span>
					</div>
			    	<input type="text" id="sm-search" value="">
		  		</div>
	  		</div>
  		</header>
		<div class="search-results">
			<div class="pull-left search-articles">
				<h3></h3>
				<ul></ul>
			</div>
			<div class="pull-left search-single">
				<div class="breadcrumbs">
					<a href="#" class="home-link" data-id="">
						<i class="fa fa-home"></i> <i class="fa fa-angle-right"></i> 
						&nbsp;<span class="sm-category"></span>
					</a>
				</div>
				<div class="sm-title"></div>
				<div class="img imgtop"></div>
				<div class="sm-content"></div>
				<div class="img imgright"></div>
				<div class="img imgbottom"></div>
			</div>
		</div>
	</div>
</div>
<a class="support-que close pull-right">
	<i class="open-popup fa fa-question"></i>
	<i class="close-popup fa fa-remove"></i>
</a>
<script>var site_url='<?= $site_url; ?>';</script>
<script type="text/javascript" src="<?= $site_url; ?>/js/knowledge-bank.js"></script>
<?php endif; ?>

</body>
</html>
