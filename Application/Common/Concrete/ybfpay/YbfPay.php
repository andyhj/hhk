<?php

namespace Common\YbfPay;

/**
 * 2019-10-14
 *
 * @author Andy
 */
class Ybf {

    protected $result = false;                                      //返回参数@array
    private $key = 'f68f04b060218f0fcadb1384dc7c9b02';              //秘钥
    private $merchant_id = '10002';                             //商户号

    function __construct() {
        //构造请求流水号
        $this->requestNo = date("Ymdhis") . rand(10, 99) . rand(10, 99);
    }

   
    /**
     * 银宝付云闪付（代还）
     * @param
     * @return
     * @author Andy Create At 2019-03-25
     */
    function ysfPayment($data = false) {
        $param = array(
            'tenant_id' => $this->merchant_id,
            'channel' => 6, //默认6
            'order_number' => $data['order_number'], //订单号
            'amount' => $data['amount'], //交易金额,0.00必须保留两位
            'fee' => $data['fee'], //用户费率,0.005 就是千5
            'rate' => $data['rate'], //提现手续费（每笔）
            'account_name' => $data['account_name'], //持卡人姓名
            'id_card' => $data['id_card'], //身份证号码
            'tran_account' => $data['account'], //信用卡号  
            'card_cvv' => $data['card_cvv'], //信用卡cvn
            'validity_date' => $data['validity_date'], //信用卡号有限期：：格式0125
            'phone' => $data['phone'], //手机号    
            'bank_code' => $data['bank_code'], //银行编码
            'city' => $data['city'], //落地城市，如：深圳市
            'notify_url' => $data['notify_url'], //订单处理结果通知地址 
            'close_notify_url' => $data['close_notify_url'],   //代付异步通知地址
        );
        //获取签名
        $param['sign'] = $this->getSign($param);
        add_log("ysfPayment.log", "ybfpay", "提交参数：" . var_export($param, true));
        $url = "http://pay.hsqpay.com/api/dhPay/payment";
        $res = $this->httpRequest($url, $param);
        add_log("ysfPayment.log", "ybfpay", "返回数据：" . var_export($res, true));

        return json_decode($res, true);
    }
    /**
     * 进件
     * @param
     * @return
     * @author Andy Create At 2019-03-25
     */
    function registerAndAccess($data = false) {
        $param = array(
            'tenant_id' => $this->merchant_id,
            'channel' => 6, //默认6
            'province' => $data['province'], //省
            'city' => $data['city'], //市
            'area' => $data['area'], //区
            'address' => $data['address'], //详细地址
            'mer_name' => $data['mer_name'], //真实姓名
            'id_card' => $data['id_card'], //身份证号码
            'account' => $data['account'], //结算账号  
            'reserved_phone' => $data['reserved_phone'], //结算卡银行预留手机号 
            'bank_branch' => $data['bank_branch'], //银行行号
            'down_pay_fee' => $data['down_pay_fee'], //费率 
        );
        //获取签名
        $param['sign'] = $this->getSign($param);
        add_log("registerAndAccess.log", "ybfpay", "提交参数：" . var_export($param, true));
        $url = "http://pay.hsqpay.com/api/dhPay/registerAndAccess";
        $res = $this->httpRequest($url, $param);
        add_log("registerAndAccess.log", "ybfpay", "返回数据：" . var_export($res, true));

        return json_decode($res, true);
    }
    /**
     * 银宝付云闪付代付（代还）
     * @param
     * @return
     * @author Andy Create At 2019-03-25
     */
    function ysfWitbindcard($data = false) {
        $param = array(
            'tenant_id' => $this->merchant_id,
            'order_number' => $data['order_number'], //支付订单号
            'df_order_number' => $data['df_order_number'], //代付订单号
        );
        //获取签名
        $param['sign'] = $this->getSign($param);
        add_log("ysfWitbindcard.log", "ybfpay", "提交参数：" . var_export($param, true));
        $url = "http://pay.hsqpay.com/api/dhPay/withdraw";
        $res = $this->httpRequest($url, $param);
        add_log("ysfWitbindcard.log", "ybfpay", "返回数据：" . var_export($res, true));

        return json_decode($res, true);
    }
    /**
     * 银宝付云闪付查询（代还）
     * @param
     * @return
     * @author Andy Create At 2019-03-25
     */
    function ysfQuery($data = false) {
        $param = array(
            'tenant_id' => $this->merchant_id,
            'order_number' => $data['order_number'], //订单号
        );
        //获取签名
        $param['sign'] = $this->getSign($param);
        add_log("ysfQuery.log", "ybfpay", "提交参数：" . var_export($param, true));
        $url = "http://pay.hsqpay.com/api/dhPay/query";
        $res = $this->httpRequest($url, $param);
        add_log("ysfQuery.log", "ybfpay", "返回数据：" . var_export($res, true));

        return json_decode($res, true);
    }
    /**
     * 银宝付云闪付查询银行卡资金（代还）
     * @param
     * @return
     * @author Andy Create At 2019-03-25
     */
    function ysfQueryCard($data = false) {
        $param = array(
            'tenant_id' => $this->merchant_id,
            'channel' => 6, //默认6
            'account' => $data['account'], //卡号
        );
        //获取签名
        $param['sign'] = $this->getSign($param);
        add_log("ysfQueryCard.log", "ybfpay", "提交参数：" . var_export($param, true));
        $url = "http://pay.hsqpay.com/api/dhPay/queryCard";
        $res = $this->httpRequest($url, $param);
        add_log("ysfQueryCard.log", "ybfpay", "返回数据：" . var_export($res, true));

        return json_decode($res, true);
    }

    /**
     * 发送http请求
     * @param string $url
     * @param string $post_data
     * @return string HTTP response
     *         false 失败
     */
    function httpRequest($url, $post_data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($post_data !== null) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        } else {
            curl_setopt($ch, CURLOPT_POST, 0);
        }

        // 测试环境不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        //访问的端口号：
        // curl_setopt($ch, CURLOPT_PORT, $this->port);

        $response = curl_exec($ch);
        $curl_errno = (int) curl_errno($ch);

        // $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
        $data = curl_getinfo($ch);
        // return $data;
        // echo $httpCode;
        curl_close($ch);

        // header('Content-Type:text/xml; charset=utf-8');

        return $response;
    }

    /**
     * 生成签名
     * @param  array $Obj 待签名的数据
     * @return string
     */
    function getSign($Obj) {
        if (!is_array($Obj)) {
            $Obj = (array) $Obj;
        }
        foreach ($Obj as $k => $v) {
            $Parameters [$k] = $v;
        }
        // 签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        // 签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $this->key;
        //echo "【string2】".$String."</br>";die;
        // 签名步骤三：MD5加密

        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        // 签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        $this->sign = $result_;

        return $result_;
    }

    /**
     * 作用：格式化参数，签名过程需要使用
     */
    function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        if (!is_array($paraMap)) {
            $paraMap = (array) $paraMap;
        }
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            // $buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    /**
     * 检查签名是否正确
     * @param  array $data 待检查的数据
     * @return boolean
     */
    function checkSign($data) {
        $sign = $data['sign'];
        unset($data['sign']);
        return $sign == $this->getSign($data);
    }


    /* json2arr */

    function jsontoarr($res = '') {
        $res = strstr($res, '{');
        $res = str_replace('{', '', $res);
        $res = str_replace('}', '', $res);
        $res = str_replace('"', '', $res);
        $res = explode(',', $res);
        $result = array();
        foreach ($res as $key => $value) {
            $arr = explode(':', $value);
            $result[$arr[0]] = $arr[1];
        }
        return $result;
    }

}
