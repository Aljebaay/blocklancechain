<?php
require 'vendor/autoload.php';
use App\Support\LegacyScriptRunner;
use Illuminate\Http\Request;

$req = Request::create('/install.php', 'GET');
$router = base_path('..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'router.php');
$res = LegacyScriptRunner::run($req, $router, '/install.php');
var_export($res);
