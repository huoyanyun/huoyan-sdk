<?php
require_once "config.php";

check_params();
$order_array = generate_data();
header('Content-Type:application/json; charset=utf-8');
echo json_encode(array('code' => 0, 'msg' => '', 'data' => $order_array));
exit;

/**
 *请求参数检查
 */
function check_params()
{
    $app_key = isset($_POST['app_key']) ? $_POST['app_key'] : '';
    $timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : '';
    $sign = isset($_POST['sign']) ? $_POST['sign'] : '';
    if (empty($app_key) or empty($timestamp) or empty($sign)) {
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode(array('code' => 1, 'msg' => '参数不完整', 'data' => ''));
        exit;
    }
    $params_string = 'app_key=' . $app_key . '&timestamp=' . $timestamp;
    $new_sign = md5($params_string . '&app_secret=' . HY_SKEY);
    if ($new_sign != $sign) {
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode(array('code' => 2, 'msg' => '签名不正确', 'data' => ''));
        exit;
    }
}

/**
 * 生成测试数据
 * @return array
 */
function generate_data()
{
    $order_array = array();
    for ($i = 0; $i < 5; $i++) {
        $data = array(
            'order_no' => date("YmdHi"),
            'user_id' => 379,
            'prod' => '测试',
            'amount' => '100.0',
            'create_time' => time(),
            'update_time' => time(),
            'remark' => '备注',
            'status' => 2
        );
        $order_array[] = $data;
    }
    return $order_array;
}