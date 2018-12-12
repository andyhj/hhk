<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Model;

/**
 * Description of PayModel
 *
 * @author Administrator
 */
class PayModel {
    //构造函数
    function __construct()
    {

        //设置农商行请求地址
        $this->url = 'http://bjrcb-pay.xianyizu.com/gateway';//生产环境地址
        //$this -> url='http://test.brcb.pufubao.net/gateway';//测试环境地址
        $this->key = '';
        $this->mch_id = '';

        $this->mch_list = null;
    }

    function set_mch_id($str)
    {
        $this->mch_id = $str;
    }

    function set_key($str)
    {
        $this->key = $str;
    }


    //农商行公众号支付
    //add by zhouqi 20170411
    function publicPay($arg)
    {
        $arrHashCode = array(
            "service_type" => "WECHAT_WEBPAY",
            "mch_id" => $this->mch_id,
            "nonce_str" => rand(10000000000, 99999999999999999999),
            "body" => $arg['body'],
            "out_trade_no" => $arg['out_trade_no'],
            "total_fee" => $arg['total_fee'],
            "notify_url" => $arg['notify_url'],
            "callback_url" => $arg['callback_url'],
            "spbill_create_ip" => "112.126.90.134",
            // "attach"=> "",
            // "device_info"=> "SN1234567890098765",
            // "time_start"=> date('Ymd',time()),
            // "time_expire"=> date('Ymd',time()),
            // "goods_tag"=> "",
            // "product_id"=> "",
            // "limit_pay"=> "",

        );
        foreach ($arrHashCode as $k => $v) {
            $arrHashCode[$k] = $v;
        }

        return $this->structData($arrHashCode);
    }

    //公众号支付 获取签名后数据
    function structData($arrHashCode)
    {
        //验签处理字段
        $pData = $this->getSignedData($arrHashCode);
        $rsp = $this->strToArray($pData);
        return $rsp;
    }

    //字符串转数组
    function strToArray($arg)
    {
        $data = preg_split("/&/", $arg);
        $resp = array();
        foreach ($data as $val) {
            $key = preg_split("/=/", $val, 2);
            $resp += array($key[0] => $key[1]);
        }
        return $resp;
    }

    /**
     *
     * 农商行支付宝扫码支付接口
     * add by zhouqi 20170517
     */
    function alipayScanned($arg)
    {
        $arrHashCode = array(
            "service_type" => "ALIPAY_SCANNED",
            "mch_id" => $this->mch_id,
            "out_trade_no" => 'PH' . date('YmdHis', time()),
            "total_fee" => '1',
            "subject" => mb_convert_encoding('支付宝扫码支付', "UTF-8"), //订单标题
            "body" => mb_convert_encoding('支付宝扫码支付', "UTF-8"), //商品描述
            "device_info" => "112.126.90.134",
            "notify_url" => "/",
            "nonce_str" => rand(1000000000, 99999999999999999999),
        );

        foreach ($arg as $k => $v) {
            $arrHashCode[$k] = mb_convert_encoding($v, 'utf-8');
        }

        // return  $arrHashCode;
        return $this->sentData($arrHashCode);
    }

    //农商行刷卡(小额支付)
    function swipePay($arg)
    {
        //	print_r($arg);
        $arrHashCode = array(
            //		"appid"=> "",
            //		"attach"=> "北京分店",
            "auth_code" => "1234567890",
            "body" => "Ipad_mini_16G_白色",
            //		"detail"=> "A detailed description of the goods",
            //		"device_info"=> "SN1234567890098765",
            //		"fee_type"=> "CNY",
            //		"goods_tag"=> "WECHAT",
            "mch_id" => $this->mch_id,
            "nonce_str" => rand(10000000000, 99999999999999999999),
            //		"op_user_id"=> "C148782380925010440",
            "out_trade_no" => 'PH' . date('YmdHis', time()),
            "service_type" => "WECHAT_MICRO",
            "spbill_create_ip" => "112.126.90.134",
            //		"time_expire"=> date('Ymd',time()),
            //		"time_start"=> date('Ymd',time()),
            "total_fee" => "1"
        );
        foreach ($arrHashCode as $k => $v) {
            $arrHashCode[$k] = mb_convert_encoding($v, 'utf-8');
        }

        return $this->sentData($arrHashCode);
    }


    //农商行刷卡(小额支付)
    function microPay($arg)
    {
        foreach ($arg as $k => $v) {
            $arrHashCode[$k] = mb_convert_encoding($v, 'utf-8');
        }
        return $this->sentData($arrHashCode);
    }

    //订单查询请求
    function orderQuery($mch_id, $out_trade_no, $mch_key = '')
    {
        $arrHashCode = array(
            'service_type' => 'WECHAT_ORDERQUERY',
            'mch_id' => $mch_id,
            'out_trade_no' => $out_trade_no,
            'nonce_str' => time(),
        );
        if (isset($mch_key) && !empty($mch_key)) {
            $this->set_key($mch_key);
        }
        foreach ($arrHashCode as $k => $v) {
            $arrHashCode[$k] = mb_convert_encoding($v, 'utf-8');
        }
        return $this->sentData($arrHashCode);
    }




    //农商行扫码支付接口
    function scanCode($arg)
    {
        $arrHashCode = array(
            "body" => mb_convert_encoding('微信扫码支付', "UTF-8"),
            //		"detail"=> "This is the body's description information",
            //		"product_id"=> "03xf6u2b9r",
            //		"op_user_id"=> "C148782380925010440",
            "spbill_create_ip" => "112.126.90.134",
            "service_type" => "WECHAT_SCANNED",
            "notify_url" => "http://www.hsqpay.com/test/callback.php",
            //		"time_start"=> "20161114115410",
            //		"fee_type"=> "CNY",
            "nonce_str" => rand(1000000000, 99999999999999999999),
            "out_trade_no" => 'PH' . date('YmdHis', time()),
            //		"device_info"=> "SN1234567890098765",
            //		"time_expire"=> "20161114120410",
            //		"goods_tag"=> "WECHAT",
            "mch_id" => $this->mch_id,
            //		"attach"=> "北京分店",
            "total_fee" => '1'
        );
        foreach ($arg as $k => $v) {
            $arrHashCode[$k] = mb_convert_encoding($v, 'utf-8');
        }
        //header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);print_r($arrHashCode);die();
        //file_put_contents('./log/logs.txt',print_r($postStr.$this->key,true),FILE_APPEND);

        return $this->sentData($arrHashCode);
    }

    function str2array($arg)
    {
        $data = preg_split('/{|,|}/', preg_filter('/"|{|}/', '', $arg));
        $resp = array();
        foreach ($data as $val) {
            $key = preg_split('/:/', $val, 2);
            $resp += array($key[0] => $key[1]);
        }
        return $resp;
    }

    function post2($url, $postdata)
    {    //file_get_content
        /*        $postdata = http_build_query(
                $data
                );*/
        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded;charset=UTF-8',
                'content' => $postdata  //mb_convert_encoding($postdata,"GBK","utf-8")
            )
        );

        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
        // file_put_contents('./log/nsh_logs.txt',print_r($postdata,true). '|' ,FILE_APPEND);

        return json_decode($result, true);
    }

    //获得签名
    function getSignedData($arg)
    {

        $postStr = '';
        ksort($arg);
        foreach ($arg as $k => $v) {
            if ($v == '') continue;
            $postStr = $postStr . (($postStr == '') ? '' : '&') . $k . '=' . $v;
        }
        $arg['sign'] = strtoupper(md5($postStr . $this->key));

        $postStr = '';
        ksort($arg);
        foreach ($arg as $k => $v) {
            if ($v == '') continue;
            $postStr = $postStr . (($postStr == '') ? '' : '&') . $k . '=' . $v;
        }
        return $postStr;
    }

    function sentData($arrHashCode)
    {
        //print_r($arrHashCode);
        //接口地址区分生产和测试
        $url = "http://bjrcb-pay.xianyizu.com/gateway";//生产环境地址
        //$url="http://test.brcb.pufubao.net/gateway";//测试环境地址
        //验签处理字段
        $post_data = $this->getSignedData($arrHashCode);

        //print_r($post_data);
        $rsp = $this->post2($url, $post_data);
        //数据返回
        //	print_r($rsp);
        file_put_contents('./log/nsh_logs.txt', "\r\n" . print_r($rsp, true), FILE_APPEND);

        return $rsp;
    }

    public static function createPostParam($post = [], $orderId = 0, $mchRecord = [])
    {
        $baseUrl = env('APP_URL');
        $postParam = [
            'mch_id' => $mchRecord['origin_mch_id'],
            'service_type' => 'WEBPAY',
            'body' => '九商品描述',   // 商品描述
            'mch_create_ip' => app('request')->getClientIp(),    // ip
            'nonce_str' => self::randCode('8'),    // 随机字符串
            'customer_out_trade_no' => '',   // 商户订单号
            'total_fee' => 1,  // 金额
            'notify_url' => $baseUrl . self::NOTIFY_URL, // 异步通知地址
            'callback_url' => $baseUrl . self::CALLBACK_URL . '/' . $orderId,   // 同步回调
        ];
        $postParam = array_merge($postParam, $post);
        Log::debug('postData 原始数据：', [$postParam]);
        $postParam['sign'] = self::signMd5($postParam, $mchRecord);
        return $postParam;
    }

    /**
     * 生成签名
     *
     * @param array $param
     * @param array $mchRecord
     * @return string
     */
    public static function signMd5($param = [], $mchRecord = [])
    {
        // 排序
        ksort($param);
        $sign = '';
        foreach ($param as $key => $val) {
            // 去除空值
            if (!empty($key) && !empty($val)) {
                $sign .= $key . '=' . $val . '&';
            }
        }
        $sign = rtrim($sign, '&');
        // 去除末尾 &
        $apiKey = empty($mchRecord['origin_mch_key']) ? self::MODULE_KEY : $mchRecord['origin_mch_key'];
        Log::debug('signMd5 原始字符串：', [$sign]);
        Log::debug('signMd5 apiKey：', [$apiKey]);
        // 加密
        $sign = md5($sign . $apiKey);
        // 转化大写
        $sign = strtoupper($sign);
        Log::debug('signMd5 加密后的字符串：', [$sign]);
        return $sign;
    }

    /**
     * 构造自动提交表单
     *
     * @param $params
     * @param $action
     * @return string
     */
    public static function createHtml($params = [], $action = null)
    {
        $encodeType = isset($params ['encoding']) ? $params ['encoding'] : 'UTF-8';
        if ($action === null)
            $action = env('BNS_PFB_API_URL');

        //\Log::info(__METHOD__, [$action, $params]);

        $html = <<<eot
        <html>
            <head>
                <title>付款页面</title>
                <meta http-equiv="Content-Type" content="text/html; charset={$encodeType}" />
            </head>
            <body  onload="javascript:document.pay_form.submit();">
                <form id="pay_form" name="pay_form" action="{$action}" method="post">
eot;
        foreach ($params as $key => $value) {
            $html .= "    <input type=\"hidden\" name=\"{$key}\" id=\"{$key}\" value=\"{$value}\" />\n";
        }
        $html .= <<<eot
              </form>
            </body>
        </html>
eot;
        return $html;
    }
    /**
     * 构造自动提交表单
     *
     * @param $params
     * @param $action
     * @return string
     */
    public static function createPayHtml($jsApiParameters,$notify_url="")
    {        
        $location = "";
        if($notify_url){
            $location = 'location="'.$notify_url.'"';
        }
        $html = '<html>
                <head>
                    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
                    <meta name="viewport" content="width=device-width, initial-scale=1"/> 
                    <title>开心豆充值</title>
                    <script type="text/javascript">
                        //调用微信JS api 支付
                        function jsApiCall()
                        {
                                WeixinJSBridge.invoke(
                                        "getBrandWCPayRequest",
                                        '.$jsApiParameters.',
                                        function(res){
                                            if(res.err_msg == "get_brand_wcpay_request:ok" ) {
                                                //alert("支付成功");
                                            }
                                            if(res.err_msg == "get_brand_wcpay_request:cancel" ) {
                                            }
                                            if(res.err_msg == "get_brand_wcpay_request:fail" ) {
                                                alert("支付失败");
                                            }
                                            '.$location.';
                                        }
                                );
                        }

                        function callpay()
                        {
                                if (typeof WeixinJSBridge == "undefined"){
                                    if( document.addEventListener ){
                                        document.addEventListener("WeixinJSBridgeReady", jsApiCall, false);
                                    }else if (document.attachEvent){
                                        document.attachEvent("WeixinJSBridgeReady", jsApiCall); 
                                        document.attachEvent("onWeixinJSBridgeReady", jsApiCall);
                                    }
                                }else{
                                    jsApiCall();
                                }
                        }
                        callpay();
                        </script>
                </head>
                <body>
                </body>
                </html>';
        return $html;
    }

    /**
     * 微信-公众号-自行授权.md
     * @return [type] [description]
     */
    function auto_Authorization($data){

        $arg = array(
            "service_type" => "WECHAT_UNIFIEDORDER",
            "appid" => $data['appid'],
            "openid" => $data['openid'],
            "mch_id" => $this->mch_id,
            "body" => mb_convert_encoding($data['body'],'utf-8'),
            "out_trade_no" => $data['out_trade_no'],
            "total_fee" => $data['total_fee'],
            "spbill_create_ip" => '112.126.90.134',
            "notify_url" => $data['notify_url'],
            "trade_type" => 'JSAPI',
            "is_self_pay" => 'N',
            'nonce_str'=> rand(1000000000,99999999999999999999),

        );

        foreach ($arg as $k => $v) {
            $arrHashCode[$k] = mb_convert_encoding($v, 'utf-8');
        }
        

        //验签处理字段
        $post_data=$this->getSignedData($arrHashCode);

        $url="http://bjrcb-pay.291501.com/gateway";//生产环境地址
        $rsp = $this->post_first($url,$post_data);
        //日志
        file_put_contents('./log/nsh_logs.txt',"\r\n".print_r($post_data,true),FILE_APPEND);
        file_put_contents('./log/nsh_logs.txt',"\r\n".print_r(count($rsp),true),FILE_APPEND);

        //数据返回
        return $rsp;
    }

    /**
     * 商户公众号配置
     * @param [type] $data [description]
     */
    function MerchantPublicConfig($data){
        $arg = array(
            "serviceType" => "CUSTOMER_CONFIG",
            "agentNum" => $data['agentNum'],
            "customerNum" => $data['customerNum'],
            "configChannel" => 'WECHAT_OFFLINE',
            'jsapiPath'=>'http://hsq.ysetong.com/',
             "scribeAppid" => $data['scribeAppid'],
        );

        foreach ($arg as $k => $v) {
            // $arrHashCode[$k] = mb_convert_encoding($v, 'utf-8');
            $arrHashCode[$k] = $v;
        }

        $post_data = $this->new_sign($arrHashCode,$data['Akey']);
        $post_data = json_encode($post_data);

        $url="http://bjrcb-pay.xianyizu.com/customer/service";//生产环境地址
        

        $rsp = $this->post_first($url,$post_data);
        return $rsp;
    }

    /**
     * 商户公众号配置
     * @param [type] $data [description]
     */
    function MerchantPublicConfigAppIDAndSub($data){
        $arg = array(
            "serviceType" => "CUSTOMER_CONFIG",
            "agentNum" => $data['agentNum'],
            "customerNum" => $data['customerNum'],
            "configChannel" => 'WECHAT_OFFLINE',
            'jsapiPath'=>'http://hsq.ysetong.com/',
            "scribeAppid" => $data['scribeAppid'],
            'appid'=>$data['appid']
            );

        foreach ($arg as $k => $v) {
            // $arrHashCode[$k] = mb_convert_encoding($v, 'utf-8');
            $arrHashCode[$k] = $v;
        }

        $post_data = $this->new_sign($arrHashCode,$data['Akey']);
        $post_data = json_encode($post_data);

        $url="http://bjrcb-pay.xianyizu.com/gateway";//生产环境地址


        $rsp = $this->post_first($url,$post_data);
        return $rsp;
    }

     /**
     * 商户公众号配置查询
     * @param [type] $data [description]
     */
    function getPublicConfig($data){
        $arg = array(
            "serviceType" => "CUSTOMER_QUERYCONFIG",
            "agentNum" => $data['agentNum'],
            "customerNum" => $data['customerNum'],
            "configChannel" => 'WECHAT_OFFLINE',
        );

        foreach ($arg as $k => $v) {
            // $arrHashCode[$k] = mb_convert_encoding($v, 'utf-8');
            $arrHashCode[$k] = $v;
        }

        $post_data = $this->new_sign($arrHashCode,$data['Akey']);
        $post_data = json_encode($post_data);

        $url="http://bjrcb-pay.xianyizu.com/customer/service";//生产环境地址
        
        print_r($post_data);
        $rsp = $this->post_first($url,$post_data);
        return $rsp;
    }

    /**
     * post请求
     * @param  [type] $url      [description]
     * @param  [type] $postdata [description]
     * @return [type]           [description]
     */
    function post_first($url,$postdata){
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type:application/x-www-form-urlencoded;charset=UTF-8',
                'content' => $postdata  //mb_convert_encoding($postdata,"GBK","utf-8")
            )
        );

        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);

        return $result;
    }

    /**
     * from字符串装数组
     * @param [type] $str [description]
     */
    function FormStrToArr($str){
        $arr = explode('&',$str);
        $data = array();
        foreach ($arr as $key => $value) {
            $t = explode('=',$value);
            $data[$t[0]] = $t[1];
        }

        return $data;
    }

    /**
     * json格式数据加密
     */
    function new_sign($arg,$key){
        $postStr = '';
        ksort($arg);
        foreach ($arg as $k => $v) {
            if ($v == '') continue;
            $postStr = $postStr . (($postStr == '') ? '' : '&') . $k . '=' . $v;
        }

        $arg['sign'] = strtoupper(md5($postStr . $key));

        return $arg;
    }
}
