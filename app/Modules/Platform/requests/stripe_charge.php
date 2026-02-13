<?php
$root = dirname(__DIR__);
require_once $root . '/bootstrap/app.php';

$bridge = \App\Runtime\EndpointBridge::fromConfig();
$bridge->dispatch('requests.stripe_charge');
