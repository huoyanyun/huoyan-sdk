<?php
require_once "OAuth2Exception.php";

/**
 * 火眼平台 OAuth2 认证类
 *
 * 授权机制说明请参考Wiki
 */
class HuoyanOauth2
{

    public $client_id;

    public $client_secret;

    public $access_token;
    /**
     * Contains the last HTTP status code returned.
     */
    public $http_code;
    /**
     * Contains the last API call.
     */
    public $url;
    /**
     * Set up the API root URL.
     */
    public $host = "http://api.huoyanio.com/v1/";
    /**
     * Set timeout default.
     */
    public $timeout = 30;
    /**
     * Set connect timeout.
     */
    public $connecttimeout = 30;
    /**
     * Verify SSL Cert.
     */
    public $ssl_verifypeer = FALSE;
    /**
     * Respons format.
     */
    public $format = 'json';
    /**
     * Decode returned json data.
     */
    public $decode_json = TRUE;
    /**
     * Contains the last HTTP headers returned.
     */
    public $http_info;
    /**
     * print the debug info
     */
    public $debug = True;

    /**
     * boundary of multipart
     */
    public static $boundary = '';

    public $useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36';


    function accessTokenURL()
    {
        return 'http://api.huoyanio.com/oauth2/access_token';
    }

    function authorizeURL()
    {
        return 'http://api.huoyanio.com/oauth2/authorize';
    }

    /**
     * construct WeiboOAuth object
     * @param $client_id
     * @param $client_secret
     * @param null $access_token
     */
    function __construct($client_id, $client_secret, $access_token = NULL)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->access_token = $access_token;
    }

    /**
     * authorize接口
     *
     * 对应API：{@link https://github.com/huoyanyun/huoyan-sdk/wiki/%E8%AF%B7%E6%B1%82%E6%8E%88%E6%9D%83}
     *
     * @param string $url 授权后的回调地址,需与填写的回调地址一致,
     * @param string $response_type 默认值为code
     * @param string $state 用于保持请求和回调的状态。在回调时,会在Query Parameter中回传该参数
     * @return array
     */
    function getAuthorizeURL($url, $response_type = 'code', $state = NULL)
    {
        $params = array();
        $params['client_id'] = $this->client_id;
        $params['redirect_uri'] = $url;
        $params['response_type'] = $response_type;
        $params['state'] = $state;
        return $this->authorizeURL() . "?" . http_build_query($params);
    }

    /**
     * access_token接口
     *
     * 对应API：{@link https://github.com/huoyanyun/huoyan-sdk/wiki/%E8%8E%B7%E5%8F%96%E6%8E%88%E6%9D%83}
     *
     * @param string $type 请求的类型,固定值code
     * @param array $keys 其他参数：
     *  array('code'=>..., 'redirect_uri'=>...)
     * @return array
     * @throws OAuth2Exception
     */
    function getAccessToken($type = 'code', $keys)
    {
        $params = array();
        $params['client_id'] = $this->client_id;
        $params['client_secret'] = $this->client_secret;
        if ($type === 'code') {
            $params['grant_type'] = 'authorization_code';
            $params['code'] = $keys['code'];
            $params['redirect_uri'] = $keys['redirect_uri'];
        } else {
            throw new OAuth2Exception("wrong auth type");
        }
        $response = $this->oAuthRequest($this->accessTokenURL(), 'POST', $params);
        $token = json_decode($response, true);
        if (is_array($token) && !isset($token['error'])) {
            $this->access_token = $token['access_token'];
        } else {
            throw new OAuth2Exception("get access token failed." . $token['error']);
        }
        return $token;
    }


    /**
     * 从数组中读取access_token
     * 常用于从Session或Cookie中读取token，或通过Session/Cookie中是否存有token判断登录状态。
     *
     * @param array $arr 存有access_token和secret_token的数组
     * @return array 成功返回array('access_token'=>'value'); 失败返回false
     */
    function getTokenFromArray($arr)
    {
        if (isset($arr['access_token']) && $arr['access_token']) {
            $token = array();
            $this->access_token = $token['access_token'] = $arr['access_token'];
            return $token;
        } else {
            return false;
        }
    }

    /**
     * GET wrappwer for oAuthRequest.
     *
     * @param $url
     * @param array $parameters
     * @return mixed
     */
    function get($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'GET', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * POST wreapper for oAuthRequest.
     *
     * @param $url
     * @param array $parameters
     * @param bool $multi
     * @return mixed
     */
    function post($url, $parameters = array(), $multi = false)
    {
        $response = $this->oAuthRequest($url, 'POST', $parameters, $multi);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * DELTE wrapper for oAuthReqeust.
     *
     * @param $url
     * @param array $parameters
     * @return mixed
     */
    function delete($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'DELETE', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response, true);
        }
        return $response;
    }

    /**
     * Format and sign an OAuth / API request
     *
     * @param $url
     * @param $method
     * @param $parameters
     * @param bool $multi
     * @return string
     * @ignore
     */
    function oAuthRequest($url, $method, $parameters, $multi = false)
    {
        if (strrpos($url, 'http://') !== 0 && strrpos($url, 'https://') !== 0) {
            $url = "{$this->host}{$url}";
        }
        switch ($method) {
            case 'GET':
                $url = $url . '?' . http_build_query($parameters);
                return $this->http($url, 'GET');
            default:
                $headers = array();
                if (!$multi && (is_array($parameters) || is_object($parameters))) {
                    $body = http_build_query($parameters);
                } else {
                    $body = self::build_http_query_multi($parameters);
                    $headers[] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
                }
                $res = $this->http($url, $method, $body, $headers);
                return $res;
        }
    }

    /**
     * Make an HTTP request
     *
     * @param $url
     * @param $method
     * @param null $postfields
     * @param array $headers
     * @return string API results
     * @ignore
     */
    function http($url, $method, $postfields = NULL, $headers = array())
    {
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        if (version_compare(phpversion(), '5.4.0', '<')) {
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 1);
        } else {
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
        }
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }
        if (isset($this->access_token) && $this->access_token)
            $url = "{$url}access_token={$this->access_token}";
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;

        if ($this->debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo "=====headers======\r\n";
            var_dump($headers);

            echo '=====request info=====' . "\r\n";
            var_dump(curl_getinfo($ci));

            echo '=====response=====' . "\r\n";
            var_dump($response);
        }
        curl_close($ci);
        return $response;
    }

    /**
     * Get the header info to store.
     *
     * @param $ch
     * @param $header
     * @return int
     */
    function getHeader($ch, $header)
    {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        return strlen($header);
    }

    /**
     * @param $params
     * @return string
     */
    public
    static function build_http_query_multi($params)
    {
        if (!$params) return '';

        uksort($params, 'strcmp');

        $pairs = array();

        self::$boundary = $boundary = uniqid('------------------');
        $MPboundary = '--' . $boundary;
        $endMPboundary = $MPboundary . '--';
        $multipartbody = '';

        foreach ($params as $parameter => $value) {

            if (in_array($parameter, array('pic', 'image')) && $value{0} == '@') {
                $url = ltrim($value, '@');
                $content = file_get_contents($url);
                $array = explode('?', basename($url));
                $filename = $array[0];

                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'Content-Disposition: form-data; name="' . $parameter . '"; filename="' . $filename . '"' . "\r\n";
                $multipartbody .= "Content-Type: image/unknown\r\n\r\n";
                $multipartbody .= $content . "\r\n";
            } else {
                $multipartbody .= $MPboundary . "\r\n";
                $multipartbody .= 'content-disposition: form-data; name="' . $parameter . "\"\r\n\r\n";
                $multipartbody .= $value . "\r\n";
            }

        }

        $multipartbody .= $endMPboundary;
        return $multipartbody;
    }
}
