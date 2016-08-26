<?php
require_once "HuoyanClient.php";
require_once "config.php";

session_start();

$hc = new HuoyanClient(HY_AKEY, HY_SKEY, $_SESSION['token']['access_token']);
$bi = $hc->user_base_info();
var_dump($bi);