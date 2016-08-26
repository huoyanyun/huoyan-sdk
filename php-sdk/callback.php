<?php
require_once 'HuoyanOauth2.php';
require_once 'OAuth2Exception.php';

session_start();

$o = new HuoyanOauth2(HY_AKEY, HY_SKEY);

if (isset($_REQUEST['code'])) {
    $keys = array();
    $keys['code'] = $_REQUEST['code'];
    $keys['redirect_uri'] = HY_CALLBACK_URL;
    try {
        $token = $o->getAccessToken('code', $keys);
    } catch (OAuth2Exception $e) {
    }
}
if (!empty($token)) {
    $_SESSION['token'] = $token;
    ?>
    授权完成,<a href="info.php">进入你的信息页面</a><br/>
    <?php
} else {
    ?>
    授权失败。
    <?php
}
?>
