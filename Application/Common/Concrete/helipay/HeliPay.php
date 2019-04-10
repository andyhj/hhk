<?php
namespace Common\HeliPay;
use Common\HeliPay\Rsa;
use Common\HeliPay\CryptAES;
require_once APP_ROOT .'Application/Common/Concrete/helipay/CryptAES.php';
require_once APP_ROOT .'Application/Common/Concrete/helipay/RSA.php';
class Heli{
    const TENANT = 'C1800372628';
//    const OFFICIALURL = 'http://pay.trx.helipay.com/trx/quickPayApi/interface.action';   //正式请求地址
    const OFFICIALURL = 'http://test.trx.helipay.com/trx/quickPayApi/interface.action';   //测试请求地址

    // 发起提现地址
//    const WIT_API = 'http://transfer.trx.helipay.com/trx/transfer/interface.action';
    const WIT_API = 'http://test.trx.helipay.com/trx/transfer/interface.action'; //测试环境
   
    /**
     * 银行卡支付下单
     * @return boolean
     */
    public function anotherPay($data) {
        $keyStr = get_rand_str(16);
        $aes = new CryptAES();
        $aes->set_key($keyStr);
        $aes->require_pkcs5();
        $arr = array(
            'P1_bizType' => 'QuickPayBankCardPay', //银行卡支付接口 *
            'P2_customerNumber' => self::TENANT, //商户号由合利宝分配 *
            'P3_userId' => $data['userId'], //唯一ID *
            'P4_orderId' => $data['orderId'], //订单号 *
            'P5_timestamp' => date("YmdHis"), //时间：yyyyMMddHHmmss *
            'P6_payerName' => $data['payerName'], //姓名 *
            'P7_idCardType' => 'IDCARD', //IDCARD：身份证 *
            'P8_idCardNo' => $aes->encrypt($data['idCardNo']), //身份证号码  * AES加密 
            'P9_cardNo' => $aes->encrypt($data['cardNo']), //银行卡号  * AES加密 
            'P10_year' => $aes->encrypt($data['year']), //信用卡时必输:信用卡有效期年份 17  AES加密 
            'P11_month' => $aes->encrypt($data['month']), //信用卡时必输:信用卡有效期月份 09 AES加密 
            'P12_cvv2' => $aes->encrypt($data['cvv2']), //信用卡时必输:信用卡安全码 052 AES加密 
            'P13_phone' => $aes->encrypt($data['phone']), //手机号码 * AES加密 
            'P14_currency' => "CNY", //暂只支持人民币：CNY *
            'P15_orderAmount' => $data['orderAmount'], //订单金额，以元为单位，最小金额为0.01 * 
            'P16_goodsName' => $this->goods(), //商品名称 *
            'P17_goodsDesc' => $this->goods(), //商品描述 
            'P18_terminalType' => array_key_exists("terminalType", $data)?$data["terminalType"]:'IMEI', //终端类型IMEI,MAC,UUID（针对 IOS 系统）,OTHER * 
            'P19_terminalId' => array_key_exists("terminalId", $data)?$data["terminalId"]:'122121212121', //终端标识 *
            'P20_orderIp' => $_SERVER['REMOTE_ADDR'], //用户支付时使用的网络终端 IP *
            'P21_period' => '', //过了订单有效时间的订单会被设置为取消状态不能再重新进行支付。
            'P22_periodUnit' => '', //Day：天,Hour：时,Minute：分
            'P23_serverCallbackUrl' => $data['queryUrl'] //回调地址 *
        );
        $sign_str_trim = $this->sinParamsToString($arr);

        $rsa = new Rsa();
        $sign =  $rsa->genSign($sign_str_trim);
        add_log("anotherPay.log", "helipay", "私钥生成的数字签名为：". $sign);
        $encryptionKey = $rsa->rsaEnc($keyStr);
        add_log("anotherPay.log", "helipay", "私钥生成的加密密钥为：". $encryptionKey);
        $arr['userAccount'] = $aes->encrypt($data['phone']); //用户注册账号
        $arr['appType'] = 'WX'; //应用类型
        $arr['appName'] = '微信公众号'; //应用名
        $arr['dealSceneType'] = 'QUICKPAY'; //业务场景
        $arr['encryptionKey'] = $encryptionKey; //加密密钥
        $arr['signatureType'] = 'MD5WITHRSA'; //签名方式
        $arr['sign'] = $sign;
        //$http_client = new HttpClient();
        add_log("anotherPay.log", "helipay", "提交参数：". var_export($arr, true));
        $pageContents = $rsa->curlPost(self::OFFICIALURL, $arr);
        $result_arr = json_decode($pageContents, true);
        add_log("anotherPay.log", "helipay", "返回参数：". var_export($result_arr, true));
        $verify = array(
            'rt1_bizType' => $result_arr['rt1_bizType'],
            'rt2_retCode' => $result_arr['rt2_retCode'],
            'rt4_customerNumber' => $result_arr['rt4_customerNumber'],
            'rt5_orderId' => $result_arr['rt5_orderId']
        );
        if ($rsa->verSign($this->SinParamsToString($verify),$result_arr['sign'])) {
            add_log("anotherPay.log", "helipay", "返回参数验签成功");
            return $result_arr;
        } else {
            add_log("anotherPay.log", "helipay", "返回参数验签失败");
            return false;
        }
    }
    
    /**
     * 发送支付短信验证码
     */
    public function phoneCode($data) {
        $keyStr = get_rand_str(16);
        $aes = new CryptAES();
        $aes->set_key($keyStr);
        $aes->require_pkcs5();
        $arr = array(
            'P1_bizType' => 'QuickPaySendValidateCode', //银行卡支付接口 *
            'P2_customerNumber' => self::TENANT, //商户号由合利宝分配 *
            'P3_orderId' => $data['orderId'], //唯一订单号 *
            'P4_timestamp' => date("YmdHis"), //时间：yyyyMMddHHmmss *
            'P5_phone' => $aes->encrypt($data['phone']) //手机号码 * AES加密
        );
        $sign_str_trim = $this->sinParamsToString($arr);

        $rsa = new Rsa();
        $sign =  $rsa->genSign($sign_str_trim);
        add_log("phoneCode.log", "helipay", "私钥生成的数字签名为：". $sign);
        $encryptionKey = $rsa->rsaEnc($keyStr);
        add_log("phoneCode.log", "helipay", "私钥生成的加密密钥为：". $encryptionKey);
        $arr['encryptionKey'] = $encryptionKey; //加密密钥
        $arr['signatureType'] = 'MD5WITHRSA'; //签名方式
        $arr['sign'] = $sign;

        add_log("anotherPay.log", "helipay", "提交参数：". var_export($arr, true));
        //$http_client = new HttpClient();
        $pageContents = $rsa->curlPost(self::OFFICIALURL, $arr);
        $result_arr = json_decode($pageContents, true);
        add_log("phoneCode.log", "helipay", "返回参数：". var_export($result_arr, true));
        return $result_arr;
    }
    
    /**
     * 确认支付
     */
    public function affirmPay($data) {
        $keyStr = get_rand_str(16);
        $aes = new CryptAES();
        $aes->set_key($keyStr);
        $aes->require_pkcs5();
        
        $arr = array(
            'P1_bizType' => 'QuickPayConfirmPay', //银行卡支付接口 * 
            'P2_customerNumber' => self::TENANT, //商户号由合利宝分配 *
            'P3_orderId' => $data['orderId'], //唯一订单号,同一商户号下订单号唯一 *
            'P4_timestamp' => date("YmdHis"), //时间：yyyyMMddHHmmss *
            'P5_validateCode' => $aes->encrypt($data['validateC']), //验证码 * AES加密
            'P6_orderIp' => $_SERVER['REMOTE_ADDR'] //用户支付时使用的网络终端 IP *
        );
        $sign_str_trim = $this->sinParamsToString($arr);

        $rsa = new Rsa();
        $sign =  $rsa->genSign($sign_str_trim);
        add_log("affirmPay.log", "helipay", "私钥生成的数字签名为：". $sign);
        $encryptionKey = $rsa->rsaEnc($keyStr);
        add_log("affirmPay.log", "helipay", "私钥生成的加密密钥为：". $encryptionKey);
        $arr['encryptionKey'] = $encryptionKey; //加密密钥
        $arr['signatureType'] = 'MD5WITHRSA'; //签名方式
        $arr['sign'] = $sign;

        add_log("anotherPay.log", "helipay", "提交参数：". var_export($arr, true));
        //$http_client = new HttpClient();
        $pageContents = $rsa->curlPost(self::OFFICIALURL, $arr);
        $result_arr = json_decode($pageContents, true);
        add_log("affirmPay.log", "helipay", "返回参数：". var_export($result_arr, true));
        return $result_arr;
    }
    
    /**
     * 绑卡支付短信
     */
    public function bindingPayCode($data) {
        $keyStr = get_rand_str(16);
        $aes = new CryptAES();
        $aes->set_key($keyStr);
        $aes->require_pkcs5();
        $arr = array(
            'P1_bizType' => 'QuickPayBindPayValidateCode', //银行卡支付接口
            'P2_customerNumber' => self::TENANT, //商户号由合利宝分配
            'P3_bindId' => $data['bindId'], //合利宝生成的唯一绑卡ID
            'P4_userId' => $data['userId'], //用户ID
            'P5_orderId' => $data['orderId'], //订单号
            'P6_timestamp' => date("YmdHis"), //时间：yyyyMMddHHmmss
            'P7_currency' => 'CNY', //人民币：CNY
            'P8_orderAmount' => $data['orderAmount'], //交易金额
            'P9_phone' => $aes->encrypt($data['phone']) //手机号码 * AES加密 
        );
        $sign_str_trim = $this->sinParamsToString($arr);

        $rsa = new Rsa();
        $sign =  $rsa->genSign($sign_str_trim);
        add_log("bindingPayCode.log", "helipay", "私钥生成的数字签名为：". $sign);
        $encryptionKey = $rsa->rsaEnc($keyStr);
        add_log("bindingPayCode.log", "helipay", "私钥生成的加密密钥为：". $encryptionKey);
        $arr['encryptionKey'] = $encryptionKey; //加密密钥
        $arr['signatureType'] = 'MD5WITHRSA'; //签名方式
        $arr['sign'] = $sign;

        add_log("bindingPayCode.log", "helipay", "提交参数：". var_export($arr, true));
        //$http_client = new HttpClient();
        $pageContents = $rsa->curlPost(self::OFFICIALURL, $arr);
        $result_arr = json_decode($pageContents, true);
        add_log("bindingPayCode.log", "helipay", "返回参数：". var_export($result_arr, true));
        $verify = array(
            'rt1_bizType' => $result_arr['rt1_bizType'],
            'rt2_retCode' => $result_arr['rt2_retCode'],
            'rt4_customerNumber' => $result_arr['rt4_customerNumber'],
            'rt5_orderId' => $result_arr['rt5_orderId'],
            'rt6_phone' => $result_arr['rt6_phone']
        );
        if ($rsa->verSign($this->SinParamsToString($verify),$result_arr['sign'])) {
            add_log("bindingPayCode.log", "helipay", "返回参数验签成功");
            return $result_arr;
        } else {
            add_log("bindingPayCode.log", "helipay", "返回参数验签失败");
            return false;
        }
    }
    
    /**
     * 绑卡支付
     */
    public function bindingCardPay($data) {
        $keyStr = get_rand_str(16);
        $aes = new CryptAES();
        $aes->set_key($keyStr);
        $aes->require_pkcs5();
        $arr = array(
            'P1_bizType' => 'QuickPayBindPay', //银行卡支付接口
            'P2_customerNumber' => self::TENANT, //商户号由合利宝分配
            'P3_bindId' => $data['bindId'], //合利宝生成的唯一绑卡ID
            'P4_userId' => $data['userId'], //用户ID
            'P5_orderId' => $data['orderId'], //订单号
            'P6_timestamp' => date("YmdHis"), //时间：yyyyMMddHHmmss
            'P7_currency' => 'CNY', //人民币：CNY
            'P8_orderAmount' => $data['orderAmount'], //交易金额
            'P9_goodsName' => $this->goods(), //人民币：CNY
            'P10_goodsDesc' => $this->goods(), //人民币：CNY
            'P11_terminalType' => $data['terminalType'], //终端类型：IMEI MAC UUID（针对 IOS 系统） OTHER
            'P12_terminalId' => $data['terminalId'], //终端标识 122121212121
            'P13_orderIp' => $_SERVER['REMOTE_ADDR'], //网络终端 IP
            'P14_period' => '', //订单有效时间
            'P15_periodUnit' => '', //订单有效时间 Day：天  Hour：时  Minute：分
            'P16_serverCallbackUrl' => $data['queryUrl'], //回调地址
        );
        $sign_str_trim = $this->sinParamsToString($arr);

        $rsa = new Rsa();
        $sign =  $rsa->genSign($sign_str_trim);
        add_log("bindingCardPay.log", "helipay", "私钥生成的数字签名为：". $sign);
        $encryptionKey = $rsa->rsaEnc($keyStr);
        add_log("bindingCardPay.log", "helipay", "私钥生成的加密密钥为：". $encryptionKey);
        if(array_key_exists("Code", $data)&&$data["Code"]){
            $arr['P17_validateCode'] = $aes->encrypt($data['Code']);   //商户设置需要验证支付短信码时，必填，AES加密
        }
        $arr['userAccount'] = $aes->encrypt($data['phone']); //用户注册账号
        $arr['appType'] = 'WX'; //应用类型
        $arr['appName'] = '微信公众号'; //应用名
        $arr['dealSceneType'] = 'QUICKPAY'; //业务场景
        $arr['encryptionKey'] = $encryptionKey; //加密密钥
        $arr['signatureType'] = 'MD5WITHRSA'; //签名方式
        $arr['sign'] = $sign;

        add_log("bindingCardPay.log", "helipay", "提交参数：". var_export($arr, true));
        //$http_client = new HttpClient();
        $pageContents = $rsa->curlPost(self::OFFICIALURL, $arr);
        $result_arr = json_decode($pageContents, true);
        add_log("bindingCardPay.log", "helipay", "返回参数：". var_export($result_arr, true));
        return $result_arr;
    }
    
    /**
     * 鉴权绑卡
     */
    public function bindingCard($data) {
        $keyStr = get_rand_str(16);
        $aes = new CryptAES();
        $aes->set_key($keyStr);
        $aes->require_pkcs5();
        $arr=array(
            'P1_bizType'=>'QuickPayBindCard',    //银行卡支付接口
            'P2_customerNumber'=> self::TENANT,      //商户号由合利宝分配
            'P3_userId'=>$data['userId'],       //用户Id
            'P4_orderId'=>$data['orderId'],     //订单号
            'P5_timestamp'=>date("YmdHis"),     //时间 yyyyMMddHHmmss
            'P6_payerName'=>$data['payerName'],       //姓名
            'P7_idCardType'=>"IDCARD",  //身份证：IDCARD
            'P8_idCardNo'=>$aes->encrypt($data['idCardNo']),      //身份证号码
            'P9_cardNo'=>$aes->encrypt($data['cardNo']),    //银行卡号
            'P10_year'=>$aes->encrypt($data['year']),     //信用卡时必填：信用卡有效期年
            'P11_month'=>$aes->encrypt($data['month']),     //信用卡时必填：信用卡有效期月
            'P12_cvv2'=>$aes->encrypt($data['cvv2']),     //信用卡时必填：安全码
            'P13_phone'=>$aes->encrypt($data['phone']), //手机号
            'P14_validateCode'=>$aes->encrypt($data['code']), //信息验证码
        );
        $sign_str_trim = $this->sinParamsToString($arr);

        $rsa = new Rsa();
        $sign =  $rsa->genSign($sign_str_trim);
        add_log("bindingCard.log", "helipay", "私钥生成的数字签名为：". $sign);
        $encryptionKey = $rsa->rsaEnc($keyStr);
        add_log("bindingCard.log", "helipay", "私钥生成的加密密钥为：". $encryptionKey);
        $arr['userAccount'] = $data['phone']; //用户注册账号
        $arr['encryptionKey'] = $encryptionKey; //加密密钥
        $arr['signatureType'] = 'MD5WITHRSA'; //签名方式
        $arr['sign'] = $sign;

        add_log("bindingCard.log", "helipay", "提交参数：". var_export($arr, true));
        //$http_client = new HttpClient();
        $pageContents = $rsa->curlPost(self::OFFICIALURL, $arr);
        $result_arr = json_decode($pageContents, true);
        add_log("bindingCard.log", "helipay", "返回参数：". var_export($result_arr, true));
        $verify = array(
            'rt1_bizType' => $result_arr['rt1_bizType'],
            'rt2_retCode' => $result_arr['rt2_retCode'],
            'rt4_customerNumber' => $result_arr['rt4_customerNumber'],
            'rt5_userId' => $result_arr['rt5_userId'],
            'rt6_orderId' => $result_arr['rt6_orderId'],
            'rt7_bindStatus' => $result_arr['rt7_bindStatus'],
            'rt8_bankId' => $result_arr['rt8_bankId'],
            'rt9_cardAfterFour' => $result_arr['rt9_cardAfterFour'],
            'rt10_bindId' => $result_arr['rt10_bindId'],
            'rt11_serialNumber' => $result_arr['rt11_serialNumber']
        );
        if ($rsa->verSign($this->SinParamsToString($verify),$result_arr['sign'])) {
            add_log("bindingCard.log", "helipay", "返回参数验签成功");
            return $result_arr;
        } else {
            add_log("bindingCard.log", "helipay", "返回参数验签失败");
            return false;
        }
    }
    
    /**
     * 鉴权绑卡短信
     */
    public function bindingCardCode($data) {
        $keyStr = get_rand_str(16);
        $aes = new CryptAES();
        $aes->set_key($keyStr);
        $aes->require_pkcs5();
        $arr=array(
            'P1_bizType'=>'AgreementPayBindCardValidateCode',    //银行卡支付接口
            'P2_customerNumber'=> self::TENANT,      //商户号由合利宝分配
            'P3_userId'=>$data['userId'],       //用户ID
            'P4_orderId'=>$data['orderId'],     //订单号
            'P5_timestamp'=>date("YmdHis"),     //时间：yyyyMMddHHmmss
            'P6_cardNo'=>$aes->encrypt($data['cardNo']),       //银行卡号
            'P7_phone'=>$aes->encrypt($data['phone']),       //手机号
            'P8_idCardNo'=>$aes->encrypt($data['idCardNo']),     //证件号
            'P9_idCardType'=>"IDCARD",  //身份证：IDCARD
            'P10_payerName'=>$data['payerName'],       //姓名
        );
        $sign_str_trim = $this->sinParamsToString($arr);
        
        add_log("bindingCardCode.log", "helipay", "签名字符串：". $sign_str_trim);
        $rsa = new Rsa();
        $sign =  $rsa->genSign($sign_str_trim);
        add_log("bindingCardCode.log", "helipay", "私钥生成的数字签名为：". $sign);
        $encryptionKey = $rsa->rsaEnc($keyStr);
        add_log("bindingCardCode.log", "helipay", "私钥生成的加密密钥为：". $encryptionKey);
        $arr['P11_isEncrypt'] = 1; 
        if(array_key_exists("year", $data)&&$data["year"]){
            $arr['P12_year'] = $aes->encrypt($data['year']);   //信用卡时必填：信用卡有效期年
        }
        if(array_key_exists("month", $data)&&$data["month"]){
            $arr['P13_month'] = $aes->encrypt($data['month']);   //信用卡时必填：信用卡有效期月
        }
        if(array_key_exists("cvv2", $data)&&$data["cvv2"]){
            $arr['P14_cvv2'] = $aes->encrypt($data['cvv2']);   //信用卡时必填：安全码
        }
        $arr['signatureType'] = 'MD5WITHRSA'; //签名方式
        $arr['sign'] = $sign;

        add_log("bindingCardCode.log", "helipay", "提交参数：". var_export($arr, true));
        //$http_client = new HttpClient();
        $pageContents = $rsa->curlPost(self::OFFICIALURL, $arr);
        $result_arr = json_decode($pageContents, true);
        add_log("bindingCardCode.log", "helipay", "返回参数：". var_export($result_arr, true));
        $verify = array(
            'rt1_bizType' => $result_arr['rt1_bizType'],
            'rt2_retCode' => $result_arr['rt2_retCode'],
            'rt4_customerNumber' => $result_arr['rt4_customerNumber'],
            'rt5_orderId' => $result_arr['rt6_orderId']
        );
        if ($rsa->verSign($this->SinParamsToString($verify),$result_arr['sign'])) {
            add_log("bindingCardCode.log", "helipay", "返回参数验签成功");
            return $result_arr;
        } else {
            add_log("bindingCardCode.log", "helipay", "返回参数验签失败");
            return false;
        }
    }
    /**
     * 银行卡解绑
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function card_unbind($data) {
        $keyStr = get_rand_str(16);
        $aes = new CryptAES();
        $aes->set_key($keyStr);
        $aes->require_pkcs5();
        $arr = array(
            'P1_bizType' => 'BankCardUnbind',
            'P2_customerNumber' => self::TENANT,
            'P3_userId' => $data['hash'],
            'P4_bindId' => $data['bind_id'], //绑定Id 
            'P5_orderId' => $data['order_id'],
            'P6_timestamp' => date("YmdHis") //时间：yyyyMMddHHmmss *
        );
        $sign_str_trim = $this->sinParamsToString($arr);

        $rsa = new Rsa();
        $sign =  $rsa->genSign($sign_str_trim);
        add_log("card_unbind.log", "helipay", "私钥生成的数字签名为：". $sign);
        $arr['signatureType'] = 'MD5WITHRSA'; //签名方式
        $arr['sign'] = $sign;

        add_log("card_unbind.log", "helipay", "提交参数：". var_export($arr, true));
        //$http_client = new HttpClient();
        $pageContents = $rsa->curlPost(self::OFFICIALURL, $arr);
        $result_arr = json_decode($pageContents, true);
        add_log("card_unbind.log", "helipay", "返回参数：". var_export($result_arr, true));
        return $result_arr;
    }
    
    /**
     * 提现账户绑定
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function wit_bind_card($data, $type) {
        $keyStr = get_rand_str(16);
        $aes = new CryptAES();
        $aes->set_key($keyStr);
        $aes->require_pkcs5();
        $arr = array(
            'P1_bizType' => 'SettlementCardBind',
            'P2_customerNumber' => self::TENANT,
            'P3_userId' => $data['hash'],
            'P4_orderId' => $data['order_id'],
            'P5_payerName' => $data['name'],
            'P6_idCardType' => 'IDCARD',
            'P7_idCardNo' => $aes->encrypt($data['id_card']), //身份证号码  * AES加密 
            'P8_cardNo' => $aes->encrypt($data['cardNo']), //银行卡号  * AES加密 
            'P9_phone' => $aes->encrypt($data['phone']), //手机号码 * AES加密 
            'P10_bankUnionCode' => $data['bankUnionCode']
        );
        $sign_str_trim = $this->sinParamsToString($arr);

        $rsa = new Rsa();
        $sign =  $rsa->genSign($sign_str_trim);
        add_log("witbindcard.log", "helipay", "私钥生成的数字签名为：". $sign);
        $encryptionKey = $rsa->rsaEnc($keyStr);
        add_log("witbindcard.log", "helipay", "私钥生成的加密密钥为：". $encryptionKey);
        // 操作类型，绑定，修改，不参与签名
        $arr['P11_operateType'] = $type;
        if(array_key_exists("bind_id", $data)&&$data["bind_id"]){
            $arr['P13_bindId'] = $data["bind_id"];   //结算卡绑定ID,修改结算卡时使用
        }
        $arr['encryptionKey'] = $encryptionKey; //加密密钥
        $arr['signatureType'] = 'MD5WITHRSA'; //签名方式
        $arr['sign'] = $sign;
        //$http_client = new HttpClient();
        add_log("witbindcard.log", "helipay", "提交参数：". var_export($arr, true));
        $pageContents = $rsa->curlPost(self::OFFICIALURL, $arr);
        $result_arr = json_decode($pageContents, true);
        add_log("witbindcard.log", "helipay", "返回参数：". var_export($result_arr, true));
        $verify = array(
            'rt1_bizType' => $result_arr['rt1_bizType'],
            'rt2_retCode' => $result_arr['rt2_retCode'],
            'rt4_customerNumber' => $result_arr['rt4_customerNumber'],
            'rt5_userId' => $result_arr['rt5_userId'],
            'rt6_orderId' => $result_arr['rt6_orderId'],
            'rt7_bindStatus' => $result_arr['rt7_bindStatus'],
            'rt8_bankId' => $result_arr['rt8_bankId'],
            'rt9_cardAfterFour' => $result_arr['rt9_cardAfterFour']
        );
        if ($rsa->verSign($this->SinParamsToString($verify),$result_arr['sign'])) {
            add_log("witbindcard.log", "helipay", "返回参数验签成功");
            return $result_arr;
        } else {
            add_log("witbindcard.log", "helipay", "返回参数验签失败");
            return false;
        }
    }
    /**
     * 发起提现
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function withdraw($data) {
        $keyStr = get_rand_str(16);
        $aes = new CryptAES();
        $aes->set_key($keyStr);
        $aes->require_pkcs5();
        $arr = array(
            'P1_bizType' => 'SettlementCardWithdraw',
            'P2_customerNumber' => self::TENANT,
            'P3_userId' => $data['hash'],
            'P4_orderId' => $data['order_id'],
            'P5_amount' => $data['amount'],
            'P6_feeType' => 'PAYER',
            'P7_summary' => '提现备注',
        );
        $sign_str_trim = $this->sinParamsToString($arr);

        $rsa = new Rsa();
        $sign =  $rsa->genSign($sign_str_trim);
        add_log("withdraw.log", "helipay", "私钥生成的数字签名为：". $sign);
        // 绑定id不参与加密
        $arr['P8_bindId'] = $data['bind_id'];
        $arr['signatureType'] = 'MD5WITHRSA'; //签名方式
        $arr['sign'] = $sign;

        add_log("withdraw.log", "helipay", "提交参数：". var_export($arr, true));
        //$http_client = new HttpClient();
        $pageContents = $rsa->curlPost(self::WIT_API, $arr);
        $result_arr = json_decode($pageContents, true);
        add_log("withdraw.log", "helipay", "返回参数：". var_export($result_arr, true));
        $verify = array(
            'rt1_bizType' => $result_arr['rt1_bizType'],
            'rt2_retCode' => $result_arr['rt2_retCode'],
            'rt4_customerNumber' => $result_arr['rt4_customerNumber'],
            'rt5_userId' => $result_arr['rt5_userId'],
            'rt6_orderId' => $result_arr['rt6_orderId'],
            'rt7_serialNumber' => $result_arr['rt7_serialNumber']
        );
        if ($rsa->verSign($this->SinParamsToString($verify),$result_arr['sign'])) {
            add_log("withdraw.log", "helipay", "返回参数验签成功");
            return $result_arr;
        } else {
            add_log("withdraw.log", "helipay", "返回参数验签失败");
            return false;
        }
    }
    
    /**
     * 信用卡还款
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function creditWithdraw($data) {
        $keyStr = get_rand_str(16);
        $aes = new CryptAES();
        $aes->set_key($keyStr);
        $aes->require_pkcs5();
        $arr = array(
            'P1_bizType'=>'CreditCardRepayment',
            'P2_customerNumber'=>self::TENANT,
            'P3_userId'=>$data['userId'],
            'P4_bindId'=>$data['bindId'],
            'P5_orderId'=>$data['order_id'],
            'P6_timestamp'=>date("YmdHis"),
            'P7_currency'=>'CNY',
            'P8_orderAmount'=>$data['amount'],
            'P9_feeType'=>'PAYER',
            'P10_summary'=>'还款备注'
        );
        $sign_str_trim = $this->sinParamsToString($arr);

        $rsa = new Rsa();
        $sign =  $rsa->genSign($sign_str_trim);
        add_log("creditWithdraw.log", "helipay", "私钥生成的数字签名为：". $sign);
        $arr['signatureType'] = 'MD5WITHRSA'; //签名方式
        $arr['sign'] = $sign;

        add_log("creditWithdraw.log", "helipay", "提交参数：". var_export($arr, true));
        //$http_client = new HttpClient();
        $pageContents = $rsa->curlPost(self::WIT_API, $arr);
        $result_arr = json_decode($pageContents, true);
        add_log("creditWithdraw.log", "helipay", "返回参数：". var_export($result_arr, true));
        $verify = array(
            'rt1_bizType' => $result_arr['rt1_bizType'],
            'rt2_retCode' => $result_arr['rt2_retCode'],
            'rt4_customerNumber' => $result_arr['rt4_customerNumber'],
            'rt5_userId' => $result_arr['rt5_userId'],
            'rt6_orderId' => $result_arr['rt6_orderId'],
            'rt7_serialNumber' => $result_arr['rt7_serialNumber'],
            'rt8_bindId' => $result_arr['rt8_bindId']
        );
        if ($rsa->verSign($this->SinParamsToString($verify),$result_arr['sign'])) {
            add_log("creditWithdraw.log", "helipay", "返回参数验签成功");
            return $result_arr;
        } else {
            add_log("creditWithdraw.log", "helipay", "返回参数验签失败");
            return false;
        }
    }
    /**
     * 结算卡提现、信用卡还款查询
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function getWithdraw($data) {
        $keyStr = get_rand_str(16);
        $aes = new CryptAES();
        $aes->set_key($keyStr);
        $aes->require_pkcs5();
        $arr = array(
            'P1_bizType' => 'TransferQuery',
            'P2_orderId' => $data['order_id'],
            'P3_customerNumber' => self::TENANT
        );
        $rsa = new Rsa();
        $sign_str_trim = $this->sinParamsToString($arr);

        $sign =  $rsa->genSign($sign_str_trim);
        add_log("getWithdraw.log", "helipay", "私钥生成的数字签名为：". $sign);
        $arr['signatureType'] = 'MD5WITHRSA'; //签名方式
        $arr['sign'] = $sign;

        add_log("getWithdraw.log", "helipay", "提交参数：". var_export($arr, true));
        //$http_client = new HttpClient();
        $pageContents = $rsa->curlPost(self::WIT_API, $arr);
        $result_arr = json_decode($pageContents, true);
        add_log("getWithdraw.log", "helipay", "返回参数：". var_export($result_arr, true));
        $verify = array(
            'rt1_bizType' => $result_arr['rt1_bizType'],
            'rt2_retCode' => $result_arr['rt2_retCode'],
            'rt4_customerNumber' => $result_arr['rt4_customerNumber'],
            'rt5_orderId' => $result_arr['rt5_orderId'],
            'rt6_serialNumber' => $result_arr['rt6_serialNumber'],
            'rt7_orderStatus' => $result_arr['rt7_orderStatus']
        );
        if ($rsa->verSign($this->SinParamsToString($verify),$result_arr['sign'])) {
            add_log("getWithdraw.log", "helipay", "返回参数验签成功");
            return $result_arr;
        } else {
            add_log("getWithdraw.log", "helipay", "返回参数验签失败");
            return false;
        }
    }
    /**
     * 支付回调验签
     */
    public function back_checked($result_arr){
        // 验签
        $verify=array(
            'rt1_bizType'=>$result_arr['rt1_bizType'],
            'rt2_retCode'=>$result_arr['rt2_retCode'],
            'rt3_retMsg'=>$result_arr['rt3_retMsg'],
            'rt4_customerNumber'=>$result_arr['rt4_customerNumber'],
            'rt5_orderId'=>$result_arr['rt5_orderId'],
            'rt6_serialNumber'=>$result_arr['rt6_serialNumber'],
            'rt7_completeDate'=>$result_arr['rt7_completeDate'],
            'rt8_orderAmount'=>$result_arr['rt8_orderAmount'],
            'rt9_orderStatus'=>$result_arr['rt9_orderStatus'],
            'rt10_bindId'=>$result_arr['rt10_bindId'],
            'rt11_bankId'=>$result_arr['rt11_bankId'],
            'rt12_onlineCardType'=>$result_arr['rt12_onlineCardType'],
            'rt13_cardAfterFour'=>$result_arr['rt13_cardAfterFour'],
            'rt14_userId'=>$result_arr['rt14_userId'],
        );
        $rsa = new Rsa();
        if ($rsa->verSign($this->SinParamsToString($verify),$result_arr['sign'])) {
            add_log("backchecked.log", "helipay", "返回参数验签成功");
            return true;
        } else {
            add_log("backchecked.log", "helipay", "返回参数验签失败");
            return false;
        }
    }
    
    private function goods() {
        $data = array(
            '煮蛋器', '电热壶电热杯', '咖啡机', '豆浆机', '勺子', '打蛋器', '水果刀', '厨用刀', '开瓶器', '刨子', '厨房用铲', '砧板', '榨汁机', '蛋糕模', '家用净水器', '家用搅拌机', '其他厨具', '餐具', '洗衣粉', '洗衣皂', '香皂', '药皂', '洗衣液', '衣物柔顺剂', '洗洁精', '厨房清洁剂', '卫生间清洁剂', '消毒用品', '其他洗涤用品', '驱虫灭害用品'
        );

        return $data[rand(0, count($data) - 1)];
    }
    private function sinParamsToString($params) {
        $sign_str = "";
        foreach ($params as $key => $val) {
            $sign_str .=  '&'.$val;
        }
        //$sign_str_trim = rtrim($sign_str, '&');
        return $sign_str;
    }
}
