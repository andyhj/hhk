<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Common;

use Common\WxApi\class_weixin_adv;
use Common\Common\JuheRecharge;
class Blend {
    /**
     * 话费充值
     * @param type $order_number 订单号
     */
    public function moblieRecharge($order_number){
        if(!$order_number){
            add_log("juhe_pay.log", "pay", "参数错误：".$order_number);
            return 111;
        }
        $model_order = D("Order");
        $game_order = $model_order->getGameOrderOne($order_number);
        add_log("juhe_pay.log", "pay", "订单数据：". var_export($game_order,true));
        if(!$game_order){
            return 115;
        }
        $phone = $game_order["addr_phone"];
        $amount = $game_order["amount"];
        $user_id = $game_order["uid"];
        require_once APP_ROOT . "Application/Common/Concrete/wxapi/example/weixin.api.php";
        $weixin = new class_weixin_adv();
        $game_user = D("User");
        $appkey = '7baf94b5d3250af823d88bb3fe1081e1'; //从聚合申请的话费充值appkey
        $openid = C('JH_CONFIG.OPENID'); //注册聚合账号就会分配的openid，在个人中心可以查看
        $juhe_recharge = new JuheRecharge($appkey,$openid);
        $telCheckRes = $juhe_recharge->telcheck($phone,$amount);
        add_log("juhe_pay.log", "pay", "根据手机号码及面额查询是否支持充值：". var_export($telCheckRes,true));
        if($telCheckRes){
            $telQueryRes =$juhe_recharge->telquery($phone,$amount); #可以选择的面额5、10、20、30、50、100、300
            add_log("juhe_pay.log", "pay", "根据手机号码和面额获取商品信息：". var_export($telQueryRes,true));
            if($telQueryRes['error_code'] == '0'){
                //正常获取到话费商品信息
                $telRechargeRes = $juhe_recharge->telcz($phone,$amount,$order_number); #可以选择的面额5、10、20、30、50、100、300
                add_log("juhe_pay.log", "pay", "提交话费充值：". var_export($telRechargeRes,true));
                if($telRechargeRes['error_code'] =='0'){
                    $msg_data = $game_user->wxExchangeSucceedMsg($user_id,"您好，您的话费已充值成功，请注意查收",$amount."元话费",$phone);
                    $return_status = $weixin->send_user_message($msg_data);
                    add_log("juhe_pay.log", "pay", "微信消息状态：". var_export($return_status,true));
                    $r_data["ordercash"] = $telRechargeRes['result']['ordercash'];
                    $r_data["status"] = 200;
                    $model_order->updGameOrder(["id"=>$game_order["id"]],$r_data);
                    return 200;
                }else{
                    $msg_data = $game_user->wxExchangeFallMsg($user_id,"您好，您的话费兑换失败",$telRechargeRes["reason"]);
                    $return_status = $weixin->send_user_message($msg_data);
                    add_log("juhe_pay.log", "pay", "微信消息状态：". var_export($return_status,true));
                    $model_order->updGameOrder(["id"=>$game_order["id"]],["status"=>500]);
                    return 112;
                }
            }else{
                $msg_data = $game_user->wxExchangeFallMsg($user_id,"您好，您的话费兑换失败",$telQueryRes["reason"]);
                $return_status = $weixin->send_user_message($msg_data);
                add_log("juhe_pay.log", "pay", "微信消息状态：". var_export($return_status,true));
                $model_order->updGameOrder(["id"=>$game_order["id"]],["status"=>500]);
                return 113;
            }
        }else{
            $msg_data = $game_user->wxExchangeFallMsg($user_id,"您好，您的话费兑换失败","该面额暂不支持充值");
            $return_status = $weixin->send_user_message($msg_data);
            add_log("juhe_pay.log", "pay", "微信消息状态：". var_export($return_status,true));
            add_log("juhe_pay.log", "pay", "检测手机号码以及面额不可以充值，手机：". $phone."，面额：".$amount);
            $model_order->updGameOrder(["id"=>$game_order["id"]],["status"=>500]);
            return 114;
        }
    }
}
