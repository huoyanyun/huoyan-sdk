<?php
require_once "HuoyanOauth2.php";
require_once "config.php";

session_start();

$ho = new HuoyanOauth2(HY_AKEY, HY_SKEY);
$code_url = $ho->getAuthorizeURL(HY_CALLBACK_URL);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>火眼平台SDK</title>
</head>

<body>
<!-- 授权按钮 -->
<div>
    <a href="<?= $code_url ?>">
        <img src="huoyanlogo.png" title="点击进入授权页面" alt="点击进入授权页面" border="0"/>
        <label>点击进入授权页面</label>
    </a>
</div>
</body>
</html>
