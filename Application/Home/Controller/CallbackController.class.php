<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Controller;

use Common\WxApi\class_weixin_adv;
use Common\HeliPay\Heli;
class CallbackController extends InitController {
    public function __construct() {
        parent::__construct();
        header("Content-Type:text/html; charset=utf-8");
    }
    /**
     * 合利宝扣款回调
     */
    public function hlbPay(){
         //接受请求的数据
        $result_arr = I('post.');
        add_log("callback_helipay.log", "callback", "收款异步post回调参数：". var_export($result_arr, true));
        // 验签
        $this->checked($result_arr);
        $order_id =  $result_arr['rt5_orderId'];
        $plan_model = M("plan");
        $plan_des_model = M("plan_des");
        $plan_des_info = $plan_des_model->where("order_id='{$order_id}' OR remedy_id='{$order_id}'")->find();
        if(!$plan_des_info&&empty($plan_des_info)){
            add_log("callback_helipay.log", "callback", "订单不存在：". var_export($order_id, true));
            die('success');
        }
        // 如果订单状态成功
        if($plan_des_info&&!empty($plan_des_info)&&$plan_des_info["order_state"]==1){
            add_log("callback_helipay.log", "callback", "已付款成功：". var_export($plan_des_info, true));
            die('success');
        }
        $plan_info = $plan_model->where(["id"=>$plan_des_info["p_id"]])->find();
        // 如果订单状态没有成功
        if($result_arr['rt2_retCode'] != '0000' || $result_arr['rt9_orderStatus'] != 'SUCCESS'){
            $message = $result_arr['rt3_retMsg'];
            $plan_des_model->where(["id"=>$plan_des_info["id"]])->save(["order_state"=>4,"message"=>$message]);
            $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>5,"message"=>$message]);
            $this->sendWxErrorMessage($plan_info, $message, "消费");
            die('success');
        }
        $plan_status = 3;
        if((int)($plan_info["periods"]*2)==$plan_des_info["num"]){
            $plan_status = 1;
        }
        $time = time();
        $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>$plan_status]);
        $r_s = $plan_des_model->where(["id"=>$plan_des_info["id"]])->save(["order_state"=>1,'message'=>'成功',"d_time"=> $time]);
        if($r_s){
            $this->sendWxMessage($plan_info, $plan_des_info);
            die('success');
        }
    }
    /**
     * 验签
     * @param  [type] $result_arr [description]
     * @return [type]             [description]
     */
    private function checked($result_arr){
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/helipay/HeliPay.php";
        $heli_pay = new Heli();
        $sign = $heli_pay->back_checked($result_arr);
        // 对比签名
        if(!$sign){
            add_log("callback_helipay.log", "callback", "验签失败". var_export($sign, true));
            die('success');
        }
    }
    /**
     * 公众号推送信息
     * @param type $plan_info
     * @param type $plan_des_info
     */
    private function sendWxMessage($plan_info,$plan_des_info){
        $db_config = C("DB_CONFIG2");
        $customer_m = M("cunstomer_wx_binding",$db_config["DB_PREFIX"],$db_config);
        $cunstomer_wx_binding_info = $customer_m->where(["user_id"=>$plan_info["u_id"],"state"=>1])->find();
        if($cunstomer_wx_binding_info&&!empty($cunstomer_wx_binding_info)){
            require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            $msg_data["touser"] = $cunstomer_wx_binding_info["open_id"];
            $msg_data["template_id"] = "_laSDHK5TjAugpGCDoDNJ0C0OVQYI9NkISqfJtPr1q8";
            $msg_data["url"] = HTTP_HOST.'/index/plan/plandes.html?id='.$plan_info["id"];
            $bank_card_model = M("bank_card_".$plan_info["c_code"]);
            $card_info = $bank_card_model->where(["id"=>$plan_info["bc_id"]])->find(); //查询银行卡信息
            $msg_data["data"] = array(
                "first"=>array(
                    "value"=>"《会还款》尊敬的用户您好，您尾号".substr($card_info["card_no"],-4)."的信用卡发生一笔交易。",
                    "color"=>""
                ),
                "tradeDateTime"=>array(
                    "value"=> date("Y-m-d H:i:s"),
                    "color"=>""
                ),
                "tradeType"=>array(
                    "value"=> "消费",
                    "color"=>""
                ),
                "curAmount"=>array(
                    "value"=> $plan_des_info["amount"],
                    "color"=>""
                ),
                "remark"=>array(
                    "value"=>"点击查看详情。",
                    "color"=>""
                )
            );
            $return_status = $weixin->send_user_message($msg_data);
            add_log("callback_helipay.log", "callback", "公众号消息推送状态：". var_export($return_status, true));
        }
    }
    
    /**
     * 计划失败公众号推送信息
     * @param type $plan_info
     * @param type $plan_des_info
     */
    private function sendWxErrorMessage($plan_info,$title,$des){
        $db_config = C("DB_CONFIG2");
        $customer_m = M("cunstomer_wx_binding",$db_config["DB_PREFIX"],$db_config);
        $cunstomer_wx_binding_info = $customer_m->where(["user_id"=>$plan_info["u_id"],"state"=>1])->find();
        if($cunstomer_wx_binding_info&&!empty($cunstomer_wx_binding_info)){
            require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            $msg_data["touser"] = $cunstomer_wx_binding_info["open_id"];
            $msg_data["template_id"] = "DVlfjMCemVIaf6RbPAqARRYMVckz76r_LJXZ_IS566Y";
            $msg_data["url"] = HTTP_HOST.'/index/plan/plandes.html?id='.$plan_info["id"];
            $msg_data["data"] = array(
                "first"=>array(
                    "value"=> "《会还款》计划失败提醒，请关注",
                    "color"=>""
                ),
                "keyword1"=>array(
                    "value"=> $des,
                    "color"=>""
                ),
                "keyword2"=>array(
                    "value"=> date("Y-m-d H:i:s"),
                    "color"=>""
                ),
                "keyword3"=>array(
                    "value"=> $title,
                    "color"=>""
                ),
                "keyword4"=>array(
                    "value"=>"请根据提醒内容处理计划",
                    "color"=>""
                ),
                "remark"=>array(
                    "value"=>"点击查看详情。",
                    "color"=>""
                )
            );
            $return_status = $weixin->send_user_message($msg_data);
            add_log("callback_helipay.log", "callback", "计划失败公众号消息推送状态：". var_export($return_status, true));
        }
    }

}
