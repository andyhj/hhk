<?php

//----------------------------------
// 聚合数据-手机话费充值API调用类
//----------------------------------

namespace Common\Common;

/**
 * Description of JuheRecharge
 *
 * @author Administrator
 */
class JuheRecharge {

    private $appkey;
    private $openid;
    private $telCheckUrl = 'http://op.juhe.cn/ofpay/mobile/telcheck';
    private $telQueryUrl = 'http://op.juhe.cn/ofpay/mobile/telquery';
    private $submitUrl = 'http://op.juhe.cn/ofpay/mobile/onlineorder';  //话费充值接口
    private $staUrl = 'http://op.juhe.cn/ofpay/mobile/ordersta';  //话费订单查询接口
    private $cardUrl = 'http://v.juhe.cn/giftCard/buy';  //礼品卡发货接口
    private $cardOrderUrl = 'http://v.juhe.cn/giftCard/detail';  //礼品卡订单查询接口
    private $smsUrl = 'http://v.juhe.cn/sms/send';  //发短信接口

    public function __construct($appkey, $openid) {
        $this->appkey = $appkey;
        $this->openid = $openid;
    }

    /**
     * 根据手机号码及面额查询是否支持充值
     * @param  string $mobile   [手机号码]
     * @param  int $pervalue [充值金额]
     * @return  boolean
     */
    public function telcheck($mobile, $pervalue) {
        $params = 'key=' . $this->appkey . '&phoneno=' . $mobile . '&cardnum=' . $pervalue;
        $content = $this->juhecurl($this->telCheckUrl, $params);
        $result = $this->_returnArray($content);
        if ($result['error_code'] == '0') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 根据手机号码和面额获取商品信息
     * @param  string $mobile   [手机号码]
     * @param  int $pervalue [充值金额]
     * @return  array
     */
    public function telquery($mobile, $pervalue) {
        $params = 'key=' . $this->appkey . '&phoneno=' . $mobile . '&cardnum=' . $pervalue;
        $content = $this->juhecurl($this->telQueryUrl, $params);
        return $this->_returnArray($content);
    }

    /**
     * 提交话费充值
     * @param  [string] $mobile   [手机号码]
     * @param  [int] $pervalue [充值面额]
     * @param  [string] $orderid  [自定义单号]
     * @return  [array]
     */
    public function telcz($mobile, $pervalue, $orderid) {
        $sign = md5($this->openid . $this->appkey . $mobile . $pervalue . $orderid); //校验值计算
        $params = array(
            'key' => $this->appkey,
            'phoneno' => $mobile,
            'cardnum' => $pervalue,
            'orderid' => $orderid,
            'sign' => $sign
        );
        $content = $this->juhecurl($this->submitUrl, $params, 1);
        return $this->_returnArray($content);
    }
    /**
     * 通用礼品卡购买
     * @param type $product_id 商品id
     * @param type $orderid  订单号
     * @param type $num  数据，默认1
     * @param type $dtype  返回数据格式，默认json
     */
    public function cartBuy($product_id,$orderid,$num=1,$dtype="json"){
        $sign = md5($this->openid . $this->appkey . $num . $orderid); //校验值计算
        $params = array(
            'dtype' => $dtype,
            'key' => $this->appkey,
            'num' => $num,
            'productId' => $product_id,
            'userOrderId' => $orderid,
            'sign' => $sign
        );
        $content = $this->juhecurl($this->cardUrl, $params, 1);
        return $this->_returnArray($content);
    }
    /**
     * 查询通用礼品卡订单状态
     * @param  [string] $orderid [自定义单号]
     * @return  [array]
     */
    public function cardOrder($orderid) {
        $params = 'key=' . $this->appkey . '&orderid=' . $orderid;
        $content = $this->juhecurl($this->cardOrderUrl, $params);
        return $this->_returnArray($content);
    }
    /**
     * 发送短信
     * @param type $mobile 接收短信的手机号码
     * @param type $tpl_id  短信模板ID
     * @param type $tpl_value  模板变量值
     * @return type
     */
    public function sendSms($mobile,$tpl_id,$tpl_value="") {
        $code = urlencode("#code#=$tpl_value");
        $params = 'key=' . $this->appkey . '&mobile=' . $mobile. '&tpl_id=' . $tpl_id. '&tpl_value=' . $code;
        $content = $this->juhecurl($this->smsUrl, $params);
        return $this->_returnArray($content);
    }

    /**
     * 查询订单的充值状态
     * @param  [string] $orderid [自定义单号]
     * @return  [array]
     */
    public function sta($orderid) {
        $params = 'key=' . $this->appkey . '&orderid=' . $orderid;
        $content = $this->juhecurl($this->staUrl, $params);
        return $this->_returnArray($content);
    }

    /**
     * 将JSON内容转为数据，并返回
     * @param string $content [内容]
     * @return array
     */
    public function _returnArray($content) {
        return json_decode($content, true);
    }

    /**
     * 请求接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int $ipost [是否采用POST形式]
     * @return  string
     */
    public function juhecurl($url, $params = false, $ispost = 0) {
        $httpInfo = array();
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'JuheData');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

    /**
     * 加密
     * @param  string $str 待加密的字符串
     * @param  string $key 密码 加密解密时使用的$key为：substr(str_pad(您的用户名, 8, '0'), 0, 8)，即您的用户名（注意是用户名，不是openid）的前8位（不足8位则以0补齐）
     * @return string
     */
    public function encode($str, $key) {
        $key = substr($key, 0, 8);
        $iv = $key;
        $size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);
        $str = $this->pkcs5Pad($str, $size);
        $s = mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB, $iv);
        return base64_encode($s);
    }

    /**
     * 解密
     * @param  string $str 待解密的字符串
     * @param  string $key 密码 加密解密时使用的$key为：substr(str_pad(您的用户名, 8, '0'), 0, 8)，即您的用户名（注意是用户名，不是openid）的前8位（不足8位则以0补齐）
     * @return string
     */
    public function decode($str, $key) {
        $iv = $key;
        $str = base64_decode($str);
        $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB, $iv);
        $str = $this->pkcs5Unpad($str);
        return $str;
    }

    public function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public function pkcs5Unpad($text) {
        $pad = ord($text {strlen($text) - 1});
        if ($pad > strlen($text))
            return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
            return false;
        return substr($text, 0, - 1 * $pad);
    }

}
