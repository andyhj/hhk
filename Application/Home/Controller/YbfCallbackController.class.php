<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Controller;

use Common\WxApi\class_weixin_adv;
use Common\ybfPay\Ybf;
class YbfCallbackController extends InitController {
    public function __construct() {
        parent::__construct();
        header("Content-Type:text/html; charset=utf-8");
    }
    function receive(){
    	//接受请求的数据
        $data = I('post.');
		add_log("receive.log", "ybfpay", "订单异步返回信息：" . var_export($data, true));		
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/ybfpay/YbfPay.php";
        $ybf = new Ybf();

        if ($ybf->checkSign($data)) {//验签成功
            if ($data['status'] == '00000') {
                $order_id = $data['tenant_order_number'];
                $plan_model = M("plan");
                $plan_des_model = M("plan_des");
                $plan_des_info = $plan_des_model->where("order_id='{$order_id}' OR remedy_id='{$order_id}'")->find();
                if(!$plan_des_info&&empty($plan_des_info)){
                    add_log("receive.log", "ybfpay", "订单不存在：". var_export($order_id, true));
                    die('SUCCESS');
                }
                // 如果订单状态成功
                if($plan_des_info&&!empty($plan_des_info)&&$plan_des_info["order_state"]==1){
                    add_log("receive.log", "ybfpay", "已付款成功：". var_export($plan_des_info, true));
                    die('SUCCESS');
                }
                if($plan_des_info&&!empty($plan_des_info)&&$plan_des_info["order_state"]==2){
                    add_log("receive.log", "ybfpay", "待执行：". var_export($plan_des_info, true));
                    die();
                }
                $plan_info = $plan_model->where(["id"=>$plan_des_info["p_id"]])->find();
                //订单支付成功
                $plan_status = 3;
                if((int)($plan_info["periods"]*2)==$plan_des_info["num"]){
                    $plan_status = 1;
                }
                $time = time();
                $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>$plan_status]);
                $r_s = $plan_des_model->where(["id"=>$plan_des_info["id"]])->save(["order_state"=>1,"d_time"=> $time,"message"=>"消费成功"]);
                if($r_s){
                    $this->sendWxMessage($plan_info, $plan_des_info,"消费");
                    die('SUCCESS');
                }
            }else{
                $message = $data['msg'];
                $plan_des_model->where(["id"=>$plan_des_info["id"]])->save(["order_state"=>4,"message"=>$message]);
                $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>5,"message"=>$message]);
                $this->sendWxErrorMessage($plan_info, $message, "消费");
				die("SUCCESS");
            }
        }else{
            add_log("receive.log", "ybfpay", "验签失败" );		
			die();
        }
		die();
	}
	/**
	 * 代付回调方法
	*/
    function close(){
		//接受请求的数据
        $data = I('post.');
		add_log("close.log", "ybfpay", "订单异步返回信息：" . var_export($data, true));			
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/ybfpay/YbfPay.php";
        $ybf = new Ybf();

        if ($ybf->checkSign($data)) {//验签成功
            if ($data['status'] == '00000') {
                $order_id = $data['tenant_order_number'];
                $plan_model = M("plan");
                $plan_des_model = M("plan_des");
                $plan_des_info = $plan_des_model->where("order_id='{$order_id}' OR remedy_id='{$order_id}'")->find();
                add_log("receive.log", "ybfpay", "订单：". var_export($plan_des_info, true));
                if(!$plan_des_info&&empty($plan_des_info)){
                    add_log("receive.log", "ybfpay", "订单不存在：". var_export($order_id, true));
                    die('SUCCESS');
                }
                // 如果订单状态成功
                if($plan_des_info&&!empty($plan_des_info)&&$plan_des_info["order_state"]==1){
                    add_log("receive.log", "ybfpay", "已付款成功：". var_export($plan_des_info, true));
                    die('SUCCESS');
                }
                if($plan_des_info&&!empty($plan_des_info)&&$plan_des_info["order_state"]==2){
                    add_log("receive.log", "ybfpay", "待执行：". var_export($plan_des_info, true));
                    die();
                }
                $plan_info = $plan_model->where(["id"=>$plan_des_info["p_id"]])->find();
                //订单支付成功
                $plan_status = 3;
                if((int)($plan_info["periods"]*2)==$plan_des_info["num"]){
                    $plan_status = 1;
                }
                $time = time();
                $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>$plan_status]);
                $r_s = $plan_des_model->where(["id"=>$plan_des_info["id"]])->save(["order_state"=>1,"d_time"=> $time,"message"=>"还款成功"]);
                if($r_s){
                    $this->sendWxMessage($plan_info, $plan_des_info);
                    die('SUCCESS');
                }
            }else{
                $message = $data['msg'];
                $plan_des_model->where(["id"=>$plan_des_info["id"]])->save(["order_state"=>4,"message"=>$message]);
                $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>5,"message"=>$message]);
                $this->sendWxErrorMessage($plan_info, $message, "还款");
				die("SUCCESS");
            }
        }else{
            add_log("receive.log", "ybfpay", "验签失败" );		
			die();
        }
		die();
    }
    /**
     * 公众号推送信息
     * @param type $plan_info
     * @param type $plan_des_info
     */
    private function sendWxMessage($plan_info,$plan_des_info,$trade_type="还款"){
        $db_config = C("DB_CONFIG2");
        $customer_m = M("cunstomer_wx_binding",$db_config["DB_PREFIX"],$db_config);
        $cunstomer_wx_binding_info = $customer_m->where(["user_id"=>$plan_info["u_id"],"state"=>1])->find();
        if($cunstomer_wx_binding_info&&!empty($cunstomer_wx_binding_info)){
            $type=2;
            if($trade_type=="消费"){
                $type=1;
            }
            require_once APP_ROOT ."Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            $msg_data["touser"] = $cunstomer_wx_binding_info["open_id"];
            $msg_data["template_id"] = "_nQ9Iqu1cT6z2aiHV2vvL366b3Qr4nFpfsU7GQ1cg4U";
            $msg_data["url"] = HTTP_HOST.'/index/plan/orderdes.html?id='.$plan_info["id"].'&type='.$type;
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
                    "value"=> $trade_type,
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
            add_log("sendWxMessage.log", "cli", "计划成功公众号消息推送数据：". var_export($msg_data, true));
            add_log("sendWxMessage.log", "cli", "计划成功公众号消息推送状态：". var_export($return_status, true));
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
            $msg_data["template_id"] = "0rAKRWnyzyiW9ICydVIJj4W4NZAFR_PGNoM4XsUr92A";
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
            add_log("ybfpay_helipay.log", "ybfpay", "计划失败公众号消息推送状态：". var_export($return_status, true));
        }
    }

}
