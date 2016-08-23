<?php
require 'vendor/autoload.php';

class HuoyanClient
{
    /**
     * @access public
     * @param mixed $akey 火眼平台APP KEY
     * @param mixed $skey 火眼平台APP SECRET
     * @param mixed $access_token OAuth认证返回的token
     */
    function __construct($akey, $skey, $access_token)
    {
        $this->oauth = new HuoyanOauth2($akey, $skey, $access_token);
    }

    /**
     * 开启调试信息
     *
     * 开启调试信息后，SDK会将每次请求火眼API所发送的POST Data、Headers以及请求信息、返回内容输出出来。
     *
     * @access public
     * @param bool $enable 是否开启调试信息
     * @return void
     */
    function set_debug($enable)
    {
        $this->oauth->debug = $enable;
    }


    /**
     * 获取用户的基本信息
     *
     * 对应API：{@link https://github.com/huoyanyun/huoyan-sdk/wiki/%E8%8E%B7%E5%8F%96%E5%9F%BA%E6%9C%AC%E4%BF%A1%E6%81%AF}
     * @return array
     */
    function user_base_info()
    {
        $params = array();
        return $this->oauth->get('user/base', $params);
    }
}