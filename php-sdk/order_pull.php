<?php
require_once "config.php";

check_params();
$order_array = generate_data();
response($order_array);

/**
 *请求参数检查
 */
function check_params()
{
    $app_key = isset($_POST['app_key']) ? $_POST['app_key'] : '';
    $timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : '';
    $sign = isset($_POST['sign']) ? $_POST['sign'] : '';
    $start_time = isset($_POST['start_time']) ? $_POST['start_time'] : 0;
    $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : 0;
    if (empty($app_key) or empty($timestamp) or empty($sign)) {
        error(1, '参数不完整');
    } else if (empty($start_time) and empty($end_time)) {
        error(2, '时间不正确');
    }
    //将所有有效参数(不包括sign参数)按顺序拼接起来,键值对$key=$value的模式中间使用&连接
    $params_array = array();
    $params_array['app_key'] = $app_key;
    if (!empty($end_time))
        $params_array['end_time'] = $end_time;
    if (!empty($start_time))
        $params_array['start_time'] = $start_time;
    $params_array['timestamp'] = $timestamp;
    ksort($params_array);
    $params_string = '';
    foreach ($params_array as $k => $v) {
        $params_string .= $k . '=' . $v . '&';
    }
    //最后和分配的app_secret连接,将最后结果md5后,就是计算出的sign值,将计算出的sign值和请求参数中的sign值进行比较,判断请求是否有效
    $params_string .= 'app_secret=' . HY_SKEY;
    $new_sign = md5($params_string);
    if ($new_sign != $sign) {
        error(3, '签名不正确');
    }
}

/**
 * @param string $data
 * @param string $msg
 */
function response($data = '', $msg = '')
{
    header('Content-Type:application/json; charset=utf-8');
    echo json_encode(array('code' => 0, 'msg' => $msg, 'data' => $data));
    exit;
}

/**
 * @param int $code
 * @param string $msg
 * @param string $data
 */
function error($code = 1, $msg = '', $data = '')
{
    header('Content-Type:application/json; charset=utf-8');
    echo json_encode(array('code' => $code, 'msg' => $msg, 'data' => $data));
    exit;
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