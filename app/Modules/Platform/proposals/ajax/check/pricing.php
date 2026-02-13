<?php
$root = dirname(__DIR__, 3);
require_once $root . '/bootstrap/app.php';

$bridge = \App\Runtime\EndpointBridge::fromConfig();
$bridge->dispatch('proposals.ajax.check.pricing');
