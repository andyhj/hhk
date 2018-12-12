<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Controller;

use Common\Common\Sockets;
use Common\Common\Daifu;
use Common\WxApi\JsApiPay;
use Common\WxApi\class_weixin_adv;
use Common\Common\Redis;
class CallbackController extends InitController {
    /**
     * 收款宝充值回调
     */
    public function jubaopay(){
        include APP_ROOT .'Application/Common/Concrete/jubaopay/jubaopay.php';
        $message=$_POST["message"];
        $signature=$_POST["signature"];

        $jubaopay=new jubaopay(APP_ROOT .'Application/Common/Concrete/jubaopay/jubaopay.ini');
        $jubaopay->decrypt($message);
        // 校验签名，然后进行业务处理
        $result=$jubaopay->verify($signature);
        add_log("callback_jubao.log", "pay", "聚宝数据：". var_export($message, true));
        add_log("callback_jubao.log", "pay", "聚宝数据校验签名返回：". var_export($result, true));
        if($result==1) {
           // 得到解密的结果后，进行业务处理
            header("Content-Type:text/html; charset=utf-8");
            $pay_number = $jubaopay->getEncrypt("payid");
            $merchant_number = $jubaopay->getEncrypt("orderNo");
            $model_order = D("order");
            $pay_order_info = $model_order->getPayOrderOne($pay_number);
            if($pay_order_info){
                add_log("callback_jubao.log", "pay", "订单信息". var_export($pay_order_info, true));
                if($pay_order_info["status"]!=100){
                    echo "success";die();
                }
                $pay_order["merchant_number"] = $merchant_number;
                $pay_order["status"] = 200;
                $pay_order["pay_date"] = time();
                $pay_order_where["pay_number"] = $pay_number;
                $return_status = $model_order->updPayOrder($pay_order_where,$pay_order);
                if($return_status){
                    $order_data["u_id"] = $pay_order_info["u_id"];
                    $order_data["order_number"] = $pay_order_info["u_id"]. time();
                    $order_data["pay_number"] = $pay_number;
                    $order_data["pay_type"] = $pay_order_info["pay_type"];
                    $order_data["amount"] = $pay_order_info["amount"];
                    $order_data["ratio"] = $pay_order_info["ratio"];
                    $order_data["item_id"] = $pay_order_info["item_id"];
                    $order_data["type"] = $pay_order_info["type"];
                    $order_data["channel"] = $pay_order_info["channel"];
                    $order_data["status"] = 200;
                    $order_data["add_date"] = time();
                    $order_id = $model_order->add($order_data);
                    if(!$order_id){
                        add_log("callback_jubao.log", "pay", $pay_number."添加订单失败");
                    }
                    //累计充钱数
                    $model_order->addUserRecharge($pay_order_info["u_id"],$pay_order_info["amount"]);
                    
                    $bank_record = D("BankRecord");
                    $rec_data["uid"] = $pay_order_info["u_id"];
                    $rec_data["coinnum"] = $pay_order_info["ratio"];
                    $rec_data["type"] = 4;
                    $rec_status = $bank_record->addRecord($rec_data);
                    add_log("callback_jubao.log", "pay", $pay_number."充值存银行状态：".$rec_status);
                    
                    echo "success";die();
                }else{
                    add_log("callback_jubao.log", "pay", $pay_number."更新订单状态失败");
                }
            }else{
                add_log("callback_jubao.log", "pay", $pay_number."订单不存在");
                echo "success";die();
            }
        }
    }
    /**
     * 微信充值回调
     */
    public function wxpay(){
        $post_data = file_get_contents("php://input"); 
        libxml_disable_entity_loader(true);
        $postarr = json_decode(json_encode(simplexml_load_string($post_data, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        add_log("callback.log", "pay", "微信数据：". var_export($postarr, true));
        if($postarr["return_code"]==="SUCCESS"){
            header("Content-Type:text/html; charset=utf-8");
            require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/WxPay.JsApiPay.php";
            require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            $pay_number = $postarr["out_trade_no"];
            $merchant_number = $postarr["transaction_id"];
            $model_order = D("order");
            $m_user = D("user");
            $pay_order_info = $model_order->getPayOrderOne($pay_number);
            $return_success = "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
            $return_fail = "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[更新订单状态失败]]></return_msg></xml>";
            if($pay_order_info){
                add_log("callback.log", "pay", "订单信息". var_export($pay_order_info, true));
                if($pay_order_info["status"]!=100){
                    echo $return_success;exit();
                }
                $pay_order["merchant_number"] = $merchant_number;
                $pay_order["status"] = 200;
                $pay_order["pay_date"] = time();
                $pay_order_where["pay_number"] = $pay_number;
                $return_status = $model_order->updPayOrder($pay_order_where,$pay_order);
                $m_redis = new Redis();
                if($return_status){
                    $order_data["u_id"] = $pay_order_info["u_id"];
                    $order_data["order_number"] = $pay_order_info["u_id"]. time();
                    $order_data["pay_number"] = $pay_number;
                    $order_data["pay_type"] = $pay_order_info["pay_type"];
                    $order_data["amount"] = $pay_order_info["amount"];
                    $order_data["ratio"] = $pay_order_info["ratio"];
                    $order_data["item_id"] = $pay_order_info["item_id"];
                    $order_data["type"] = $pay_order_info["type"];
                    $order_data["channel"] = $pay_order_info["channel"];
                    $order_data["status"] = 200;
                    $order_data["add_date"] = time();
                    $order_id = $model_order->add($order_data);
                    if(!$order_id){
                        add_log("callback.log", "pay", $pay_number."添加订单失败");
                    }
                    //累计充钱数
                    $model_order->addUserRecharge($pay_order_info["u_id"],$pay_order_info["amount"]);
                    
                    $bank_record = D("BankRecord");
                    $rec_data["uid"] = $pay_order_info["u_id"];
                    $rec_data["coinnum"] = $pay_order_info["ratio"];
                    $rec_data["type"] = 4;
                    
                    $rec_status = $bank_record->addRecord($rec_data);
                    add_log("callback.log", "pay", $pay_number."充值存银行状态：".$rec_status);
                    //推送微信消息
//                    $user_info = $m_user->getUserOne($pay_order_info["u_id"]);
//                    $msg_data["touser"] = $user_info["other_id"];
//                    $msg_data["template_id"] = "i2BZ0ikUrLXmKUiAsJ9j9dcIX9Qo4pRiM2WeZ4MFxtU";
//                    $msg_data["url"] = 'https://'.$_SERVER['HTTP_HOST'].'/index/user/orderinfo.html?order_type=1&order_number='.$order_data["order_number"];
//                    $msg_data["data"] = array(
//                        "first"=>array(
//                            "value"=>"您好，充值成功",
//                            "color"=>""
//                        ),
//                        "keyword1"=>array(
//                            "value"=> $order_data["order_number"],
//                            "color"=>""
//                        ),
//                        "keyword2"=>array(
//                            "value"=> $order_data["amount"],
//                            "color"=>""
//                        ),
//                        "keyword3"=>array(
//                            "value"=> $order_data["u_id"],
//                            "color"=>""
//                        ),
//                        "keyword4"=>array(
//                            "value"=> date("Y-m-d H:i:s",$order_data["add_date"]),
//                            "color"=>""
//                        ),
//                        "remark"=>array(
//                            "value"=>"祝您游戏愉快。",
//                            "color"=>""
//                        )
//                    );
//                    $return_status = $weixin->send_user_message($msg_data);
//                    add_log("callback.log", "pay", "公众号消息推送状态：". var_export($return_status, true));

                    //累计充钱数
//                    $data = $m_redis->hgetall("user.".$pay_order_info["u_id"]);
//                    $money = $pay_order_info["amount"];
//                    if($data&&!empty($data["money"])){
//                        $money += $data["money"];
//                    }
//                    $data["money"] = $money;
//                    $m_redis->hmset("user.".$pay_order_info["u_id"], $data);
                    
                    echo $return_success;exit();
                }else{
                    add_log("callback.log", "pay", $pay_number."更新订单状态失败");
                    echo $return_fail;exit();
                }
            }else{
                add_log("callback.log", "pay", $pay_number."订单不存在");
                echo $return_success;exit();
            }
        }
    }
    /**
     * 微信退款回调
     */
    public function wrefund(){
        $post_data = file_get_contents("php://input"); 
        libxml_disable_entity_loader(true);
        $postarr = json_decode(json_encode(simplexml_load_string($post_data, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        add_log("callback.log", "pay", "微信退款数据：". var_export($postarr, true));
        if($postarr["return_code"]==="SUCCESS"){
            $return_number = $postarr["out_refund_no"];
            $refund_id = $postarr["refund_id"];
            $model_order = D("Order");
            $order_info = $model_order->getOneByReturnNumber($return_number);
            $return_success = "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
            $return_fail = "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[更新订单状态失败]]></return_msg></xml>";
            if($order_info){
                add_log("callback.log", "pay", "订单信息". var_export($order_info, true));
                if($order_info["status"]!=300){
                    echo $return_success;exit();
                }
                $order_upd_data["refund_id"] = $refund_id;
                $order_upd_data["status"] = 400;
                $order_where["return_number"] = $return_number;
                $return_status = $model_order->updOrder($order_where,$order_upd_data);
                if($return_status){
                    //推送微信消息
                    $m_user = D("User");
                    header("Content-Type:text/html; charset=utf-8");
                    require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/WxPay.JsApiPay.php";
                    require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
                    $user_info = $m_user->getUserOne($order_info["u_id"]);
                    $weixin = new class_weixin_adv();
                    $msg_data["touser"] = $user_info["other_id"];
                    $msg_data["template_id"] = "XEs5AvWT90jTkybiThRYKg4taOONk9zIPkQZGv3h39g";
                    $msg_data["url"] = 'https://'.$_SERVER['HTTP_HOST'].'/index/user/orderinfo.html?order_type=1&order_number='.$order_data["order_number"];
                    $msg_data["data"] = array(
                        "first"=>array(
                            "value"=>"退款成功",
                            "color"=>""
                        ),
                        "keyword1"=>array(
                            "value"=> $return_number,
                            "color"=>""
                        ),
                        "keyword2"=>array(
                            "value"=> $order_info["refund_fee"],
                            "color"=>""
                        ),
                        "remark"=>array(
                            "value"=>"祝您游戏愉快。",
                            "color"=>""
                        )
                    );
                    $return_status = $weixin->send_user_message($msg_data);
                    add_log("callback.log", "pay", "公众号退款消息推送状态：". var_export($return_status, true));

                    echo $return_success;exit();
                }else{
                    add_log("callback.log", "pay", $return_number."更新订单状态失败");
                    echo $return_fail;exit();
                }
            }else{
                add_log("callback.log", "pay", $return_number."订单不存在");
                echo $return_success;exit();
            }
        }
    }
    /**
     * 代付回调
     */
    public function df(){
        $data = $_POST;
        add_log("daifu_callback.log", "commission", "佣金提现回调通知数据：". var_export($data, true));
        if($data&&!empty($data)){
            if($data["MerNo"]==43769&&$data["Succeed"]==11){
                $model_award = D("Award");
                $where["order_number"] = $data["MerBillNo"];
                $where["add_date"] = array('GT', time()-86400);
                $where["status"] = array('in', '200,300');
                $award_info = $model_award->getAwardExtractOne($where);
                if($award_info){
                    $retrun_status = $model_award->returnCommission($award_info["u_id"],$award_info["commission"],$data["MerBillNo"],"银行处理失败，资金退回",400);
                    if(!$retrun_status){
                        add_log("daifu_callback.log", "commission", $data["MerBillNo"]."回滚失败");
                    }else{
                        $m_user = D("user");
                        $user_info = $m_user->getUserOne($award_info["u_id"]);
                        header("Content-Type:text/html; charset=utf-8");
                        require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/WxPay.JsApiPay.php";
                        require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
                        $weixin = new class_weixin_adv();
                        $msg_data["touser"] = $user_info["other_id"];
                        $msg_data["template_id"] = "XEs5AvWT90jTkybiThRYKg4taOONk9zIPkQZGv3h39g";
                        $msg_data["url"] = 'https://'.$_SERVER['HTTP_HOST'].'/index/user/extractinfo.html?id='.$award_info["id"];
                        $msg_data["data"] = array(
                            "first"=>array(
                                "value"=>"您的佣金已退回账户",
                                "color"=>""
                            ),
                            "keyword1"=>array(
                                "value"=> $data["MerBillNo"],
                                "color"=>""
                            ),
                            "keyword2"=>array(
                                "value"=> $award_info["commission"],
                                "color"=>""
                            ),
                            "remark"=>array(
                                "value"=>"银行处理失败，资金退回，点击查看详情",
                                "color"=>""
                            )
                        );
                        $return_status = $weixin->send_user_message($msg_data);
                        add_log("daifu_callback.log", "commission", $data["MerBillNo"]."回滚成功");
                        add_log("daifu_callback.log", "commission", "公众号消息推送状态：". var_export($return_status, true));
                    }
                }
            }
        }
    }
    public function getDaifuOrder(){
        $order_number = I("order_number",'');
        if(!$order_number){
            $json["status"] = 305;
            $json["info"] = "请传入订单号";
            $this->ajaxReturn($json);
        }
        $daifu = new Daifu();
        $url = "https://gwapi.yemadai.com/transfer/transferQueryFixed";
        $data["merchantNumber"] = 43769;
        $data["mertransferID"] = $order_number;
        $data["requestTime"] = date("YmdHis");
        $post_xml = $daifu->getOrder($data);
        //$post_data["requestDomain"] = $post_xml;
        $result = file_get_contents($url."?requestDomain=".$post_xml);
        $result = base64_decode($result);
        $xml = simplexml_load_string($result);
        $xml_arr = json_decode(json_encode($xml),TRUE);
        print_r($xml_arr);
    }
//    public function test(){
//        $reurn_data = $this->awardArr(100188, 638888.00/14285, 0);
//        print_r($reurn_data);
//    }

}
