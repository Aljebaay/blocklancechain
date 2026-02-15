<?php
require_once __DIR__ . '/../../includes/session_bootstrap.php';

blc_bootstrap_session();
require_once("../../includes/db.php");

$amount = $input->post("amount");

echo showPrice($amount,'','no');