<?php
$rootVendor = dirname(__DIR__, 4) . '/vendor/autoload.php';
if(!is_file($rootVendor)){
    throw new RuntimeException('Root vendor autoload not found: ' . $rootVendor);
}
require_once $rootVendor;
