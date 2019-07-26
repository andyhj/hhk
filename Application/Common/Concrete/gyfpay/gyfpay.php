<?php
namespace Common\GyfPay;
class gyf
{
    public static $merno = "MCH103303";
    public static $key = "db7770445fdde3a663f6b93d7a08382a";
    public static $orgno = "PT1033";
    public static $version  = "1.0";
    public static $api_url = "http://pay.weiyifu123.com/gateway/action";
    public static $public_key = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDnpRd6Rp23Gu6tUzdZ0eSEgmJ+qySzg75b3A7y+63IogoPJu6R0Sh/vne9yn+8rrOmQuPLNdMWo4M+ntGpf1HAdARUbqjNjWKJDt6d3tia+xTPlBuzrja6RjbpM1OJ86/qoHOZFaOi7zJgeRG8/WGJIEv3u3lwjCP8QM5GvrDnWwIDAQAB";
    public static $private_key  = "MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBALUy+MNSXWGOgZnHl8V9rye1z4nRs4HWFDmvWWTj3VISgLeVLls+xpL4MajDzK+z0LH9nqf8vuoIKwUwhifSsCqypm5/APHF8XI1KH7K2+l+IYy6SVaoUCiJyrUKBcUcyOgJvJYF1SVw7xJsJo63X/6dk6clSebsWZBfR69Eyw5FAgMBAAECgYBnunc38VWtvFOqwdy7XLjBZc4aGmbFg9TuNNha7irLifYPoiH4cBZjGhvrfbMWPjzRN9v0VLbB6M0f2mhiMbVQtLLgKXrJbXpVnBKxzutEiZ34f/md30fL1GtWavZj0PuY2Y/8F6y13CcLPkLLvk6j7o9h0xMhYso5D08kmPPkIQJBAOltpO2bFIUKeDqHaNrbCjbanlQbWZQ+gPGyOzDcbS4fWWWCQd6o7yeylADk4852xO3opAHQm/31peRKUy10THkCQQDGuG4aND5u3FMEXUmYN7XKuRF//g+T6u4dQaoHCaZFa9bQOHYfgtxoKxBPzFYUFMdQns1hHIennK6pToUbZUUtAkA4Vxh5qPaY7d/68Hfkav3aI4YXcsp6N2PT8lrK/kjz2uku0POpFEk04atLU/OP/6akbYQ4U+tyrnmt0iqlS+6xAkEApFqHHX8WH+RzeMmbA50X6smz0pMS2TjVpTbY5Ccz8HinWuFHuPonRrRPMmCC1Or2ihQ9MtNA0vzAbGD3r9fLJQJAWvbu22DlThJuy1D8/NxwWdBrIqntU4jjBM5Ad9fM8bIXb8bOjU4ENHlInbUP6XmOPXRa08JK8KS67pdSBrRWng==";

    /**
     * 订单查询
     *
     * @return void
     */
    public static function queryOrder($data){
        $senddata = [
            'subMerchId' => $data['merch_id'],//子商户号
            'orderId' => $data['order_id'],//订单号
        ];

        $url = self::$api_url;
        add_log("queryOrder.log", "gyfpay", "提交参数：" . var_export($senddata, true));
        // 加密
        $ret = self::api_post($url, $senddata, 'SdkQueryOrder');
        add_log("queryOrder.log", "gyfpay", "返回参数：" . var_export($ret, true));         
        return $ret;
    }
    //订单提现
    public static function withdraw($data){

        $senddata=[
            'subMerchId' => $data['merch_id'],//子商户号
            'orderId' => $data['order_id'],//订单号
            'name'=> $data['name'],//法人姓名
            'phone'=> $data['phone'],//法人电话
            'idNo'=> $data['id_card'],//身份证号
            'cardId'=> $data['card_id'],//交易卡号
            'notifyUrl'=> $data['notify_url'],//异步通知地址
            'amount'=> $data['amount'],//交易金额
            'remark'=>'代付',//订单名称
        ];

        $url = self::$api_url;         
        add_log("withdraw.log", "gyfpay", "提交参数：" . var_export($senddata, true));
        // 加密
        $ret=self::api_post($url,$senddata,'SdkSettleSubMerch');

        add_log("withdraw.log", "gyfpay", "返回参数：" . var_export($ret, true));         
        return $ret;
    }
    //商户余额
    public static function getBalance($data){

        $senddata=[
            'subMerchId' => $data['merch_id'],//子商户号
        ];

        $url = self::$api_url;         
        add_log("getBalance.log", "gyfpay", "提交参数：" . var_export($senddata, true));
        // 加密
        $ret=self::api_post($url,$senddata,'SdkQuerySubMerchBalance');
        add_log("getBalance.log", "gyfpay", "返回参数：" . var_export($ret, true));         
        return $ret;
    }
    //落地云闪付
    public static function pay($data){
        $senddata=[
            'subMerchId' => $data['merch_id'],//子商户号
            'orderId' => $data['order_id'],//订单号
            'name'=> $data['name'],//法人姓名
            'phone'=> $data['phone'],//法人电话
            'idNo'=> $data['id_card'],//身份证号
            'cardId'=> $data['card_id'],//交易卡号
            'notifyUrl'=> $data['notify_url'],//异步通知地址
            'amount'=> $data['amount'],//交易金额
            'goodsName'=>'落地云闪付',//订单名称
            'cardType'=>'02',//01 借记卡 02 贷记卡
            'cvv'=> $data['cvv'],//安全码
            'expDate'=> $data['exp_date'],//有效期
            'ipAddress'=> $data['ip_addr'],//公网IP地址（若不填大额交易限额会被风控）（付款客户端IP）
        ];

        $url = self::$api_url;         
        add_log("pay.log", "gyfpay", "提交参数：" . var_export($senddata, true));
        // 加密
        $ret=self::api_post($url,$senddata,'SdkUnionQuickPay');
        add_log("pay.log", "gyfpay", "返回参数：" . var_export($ret, true));         
        return $ret;
    }
    //绑定信用卡-页面
    public static function bindCardHtml($data){
        $senddata=[
            'subMerchId' => $data['merch_id'],//子商户号
            'name'=> $data['name'],//法人姓名
            'phone'=> $data['phone'],//法人电话
            'idNo'=> $data['id_card'],//身份证号
            'cardId'=> $data['card_id'],//交易卡号
            'notifyUrl'=> $data['notify_url'],//异步通知地址
            'frontUrl'=> $data['front_url'],//页面通知地址,绑卡结束调回支付页
            'orderId' => $data['order_id'],//请求流水号
        ];

        $url = self::$api_url;         
        add_log("bindCardHtml.log", "gyfpay", "提交参数：" . var_export($senddata, true));
        // 加密
        $ret=self::api_post($url,$senddata,'SdkBindCardH5');
        add_log("bindCardHtml.log", "gyfpay", "返回参数：" . var_export($ret, true));         
        return $ret;

    }
    //绑定信用卡查询
    public static function queryBindCard($data){
        $senddata=[
            'subMerchId' => $data['merch_id'],//子商户号
            'cardId'=> $data['card_id'],//交易卡号
        ];

        $url = self::$api_url;         
        add_log("queryBindCard.log", "gyfpay", "提交参数：" . var_export($senddata, true));
        // 加密
        $ret=self::api_post($url,$senddata,'SdkQueryBindCard');
        add_log("queryBindCard.log", "gyfpay", "返回参数：" . var_export($ret, true));         
        return $ret;

    }
    //修改费率
    public static function updateRate($data){

        $senddata=[
            'subMerchId' => $data['merch_id'],//子商户号
            'feeRate'=> $data['fee_rate'],//交易费率0.68% 传  68. 费率值乘于10000
            'externFee'=> $data['extern_fee'],//附加手续费(结算手续费)，单位分：（1.00元，传 100）
        ];

        // print_r($senddata);echo '<hr>';

        $url = self::$api_url;         
        add_log("updateRate.log", "gyfpay", "提交参数：" . var_export($senddata, true));
        // 加密
        $ret=self::api_post($url,$senddata,'SdkMerchRateModify');
        add_log("updateRate.log", "gyfpay", "返回参数：" . var_export($ret, true));         
        return $ret;
    }
    //修改结算卡
    public static function updateBankcard($data){
        $senddata=[
            'subMerchId' => $data['merch_id'],//子商户号
            'phone'=> $data['phone'],//手机号
            'cardId'=> $data['card_id'],//绑定结算卡号
        ];

        $url = self::$api_url;         
        add_log("updateBankcard.log", "gyfpay", "提交参数：" . var_export($senddata, true));
        //加密
        $ret=self::api_post($url,$senddata,'SdkMerchSettleModify');
        add_log("updateBankcard.log", "gyfpay", "返回参数：" . var_export($ret, true));         
        return $ret;
    }
    //注册商户
    public static function regMchInfo($data){
        $senddata=[
            'merchName'=>'个体户'.$data['name'],//商户名称
            'name'=> $data['name'],//真实姓名
            'phone'=> $data['phone'],//手机号
            'idNo'=> $data['id_card'],//身份证号码
            'merchAddress'=> $data['merch_addr'],//商户详细地址
            'cardId'=> $data['card_id'],//结算账号
            'feeRate'=> $data['fee_rate'],//交易费率0.68% 传  68. 费率值乘于10000
            'externFee'=> $data['extern_fee'],//附加手续费(结算手续费)，单位分：（1.00元，传 100）
            'remark'=>'',//备注
        ];

        $url = self::$api_url;         
        add_log("regMchInfo.log", "gyfpay", "提交参数：" . var_export($senddata, true));
        // 加密
        $ret=self::api_post($url,$senddata,'SdkMerchRegister');
        add_log("regMchInfo.log", "gyfpay", "返回参数：" . var_export($ret, true));         
        return $ret;
    }
    public static function api_post($url,$senddata,$requestName){
        $send_json=json_encode($senddata,JSON_UNESCAPED_UNICODE); //1.组成请求报文明文（Json格式）
        $aes_key = mt_rand(10000000, 99999999) . mt_rand(10000000, 99999999);//2.生成请求随机密码AesKey(由数字和组成的16位定长字符串)
        $data=self::encode($send_json,$aes_key);//3.用生成的随机密码AesKey对明文数据做AES加密（BCD编码）之后得到加密data（data就是报文传输中data的值）
        $encryptkey=self::rsa_encode($aes_key);//4.用RSA私钥文件对AesKey进行RSA加密(Base64格式编码输出)之后得到encryptkey。
        $sign=md5($data.self::$key);//5.用MD5算法对签名串（data+商户蜜钥KEY）进行签名得到sign
        $s_data=[
            'version'=>self::$version,//版本号
            'partnerId'=>self::$orgno,//接入机构编号
            'merchId'=>self::$merno,//接入商户编号
            'requestName'=>$requestName,//接口服务名称
            'data'=>$data,//请求报文体（AES加密)
            'encryptkey'=>$encryptkey,//数据体加密KEY
            'sign'=>$sign//数据签名
        ];
        $json=self::curlpost($url,$s_data);
        //返回数据
        $ret=self::jiemi_data($json); 
        return $ret;
    }
    public static function curlpost($URL, $params)
    {
        $ch = curl_init();
        $timeout = 30;
        curl_setopt($ch, CURLOPT_URL, $URL);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        $file_contents = curl_exec($ch);//获得返回值

        if (curl_error($ch)) {
            var_dump('错误：'.$URL.'=>'.curl_error($ch));exit;
        }
        return $file_contents;
    }
    public static function jiemi_data($json){
        $jm_ret=json_decode($json,true);            
        $msg='请求失败';
        if(isset($jm_ret['data'])&&isset($jm_ret['encryptkey'])) {
            $data = $jm_ret['data'];
            $jm_encryptKey = $jm_ret['encryptkey'];
            $aes_key = self::rsa_decode($jm_encryptKey);
            $data = pack("H*", $data);
            $b_json = self::decode($data, $aes_key);
            $b_ret=json_decode($b_json,true);
            if(isset($b_ret['code'])){
                if(isset($b_ret['msg'])){
                    $msg=$b_ret['msg'];
                }
                if($b_ret['code']=='0000'){
                    return ['status'=>1,'msg'=>$msg,'ret_data'=>$b_ret,'json'=>$b_json];
                }
                return ['status'=>0,'msg'=>$msg,'ret_data'=>$b_ret,'json'=>$b_json];
            }
            return ['status'=>0,'msg'=>'解密失败2','json'=>$b_json];
        }
        return ['status'=>0,'msg'=>'解密失败','json'=>$json];
    }
    //rsa私钥加密
    public static function rsa_encode($data){
        $priPem = chunk_split(self::$private_key,64,"\n");
        $private_key="-----BEGIN RSA PRIVATE KEY-----\n".$priPem."-----END RSA PRIVATE KEY-----";
        $pi_key = openssl_pkey_get_private($private_key);
        if (!$pi_key) die('$pi_key 格式不正确1');
        openssl_private_encrypt($data, $encrypted, $private_key,OPENSSL_PKCS1_PADDING);//公钥加密
        $encrypted = base64_encode($encrypted);// base64传输
        return $encrypted;
    }
    public static function rsa_decode($encrypted){
        $priPem = chunk_split(self::$public_key,64,"\n");
        $pub_key="-----BEGIN PUBLIC KEY-----\n".$priPem."-----END PUBLIC KEY-----";
        $pi_key = openssl_pkey_get_public($pub_key);
        if (!$pi_key) die('$pi_key 格式不正确2');
        openssl_public_decrypt(base64_decode($encrypted), $decrypted, $pub_key,OPENSSL_PKCS1_PADDING);//公钥解密
        return $decrypted;
    }
    //aes加密
    public static function encode($data, $key)
    {
        //AES 128 CBC pkcs5padding
        $raw = openssl_encrypt(
            $data,
            'AES-128-ECB',
            $key,
            OPENSSL_RAW_DATA
        );
        $result =  strtoupper(bin2hex($raw));
        return $result;
    }
    //aes解密
    public static function decode($encrypt, $key)
    {
        $decoded = openssl_decrypt(
            $encrypt,
            'AES-128-ECB',
            $key,
            OPENSSL_RAW_DATA
        );

        return $decoded;
    }
}