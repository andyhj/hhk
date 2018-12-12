<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cli\Controller;

use Common\Common\JuheRecharge;
use Common\WxApi\class_weixin_adv;
class PayController extends InitController {
    /**
     * 聚合数据话费充值
     */
    public function phonereg(){
        $model_order = D("Order");
        $game_user = D("User");
        $where["status"] = 400;
        $where["item_type"] = 1;
        $game_order = $model_order->getGameOrder($where);
        if($game_order){
            add_log("juhe_pay_callback.log", "pay", "订单数据：". var_export($game_order,true));
            $appkey = '7baf94b5d3250af823d88bb3fe1081e1'; //从聚合申请的话费充值appkey
            $openid = C('JH_CONFIG.OPENID'); //注册聚合账号就会分配的openid，在个人中心可以查看
            $juhe_recharge = new JuheRecharge($appkey,$openid);
            foreach ($game_order as $value) {
                $order_number = $value["order_number"];
                $orderStatusRes = $juhe_recharge->sta($order_number);
                add_log("juhe_pay_callback.log", "pay", $value["order_number"]."聚合手机充值订单数据:". var_export($orderStatusRes, true));
                if($orderStatusRes['error_code'] == '0'){
                    $where["id"] = $value["id"];
                    //查询成功
                    if($orderStatusRes['result']['game_state'] =='1'){
                        $data["status"] = 200;
                        $return_status = $model_order->updGameOrder($where,$data);
                        if($return_status){
                            add_log("juhe_pay_callback.log", "pay", $value["order_number"]."充值成功，更改订单状态成功");
                        }else{
                            add_log("juhe_pay_callback.log", "pay", $value["order_number"]."充值成功，更改订单状态失败");
                        }
                    }elseif($orderStatusRes['result']['game_state'] =='9'){
                        $data["status"] = 500;
                        $return_status = $model_order->updGameOrder($where,$data);
                        if($return_status){
                            $user_info = $game_user->getGameUserOne(["uid"=>$value["uid"]]);
                            $user_data["awardnum"] = $user_info["awardnum"]+$value["cost"];
                            $game_user->updGameUser($user_data,["uid"=>$value["uid"]]);
                            add_log("juhe_pay_callback.log", "pay", $value["order_number"]."充值失败，更改订单状态成功");
                        }else{
                            add_log("juhe_pay_callback.log", "pay", $value["order_number"]."充值失败，更改订单状态失败");
                        }
                    }
                }
            }
        }
    }
    
    /**
     * 聚合数据通用礼品卡兑换
     */
    public function cardbuy(){
        $model_order = D("Order");
        $game_user = D("User");
        $user = D("User");
        $where["status"] = 400;
        $where["item_type"] = 2;
        $game_order = $model_order->getGameOrder($where);
        if($game_order){
            require_once APP_ROOT ."Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            add_log("juhe_pay_callback.log", "pay", "订单数据：". var_export($game_order,true));
            $appkey = '4ec21cc5eab22e4a7dc6d64275bc5126'; //从聚合申请的通用礼品卡appkey
            $openid = C('JH_CONFIG.OPENID'); //注册聚合账号就会分配的openid，在个人中心可以查看
            $juhe_recharge = new JuheRecharge($appkey,$openid);
            foreach ($game_order as $value) {
                $order_number = $value["order_number"];
                $orderStatusRes = $juhe_recharge->cardOrder($order_number);
                add_log("juhe_pay_callback.log", "pay", $value["order_number"]."聚合订单数据:". var_export($orderStatusRes, true));
                $where["id"] = $value["id"];
                if($orderStatusRes['error_code'] == '0'){
                    $key = substr(str_pad("kxyl123456", 8, '0'), 0, 8);
                    $cardNo = $juhe_recharge->decode($orderStatusRes["result"]["cards"]["cardNo"], $key);
                    $cardPws = $juhe_recharge->decode($orderStatusRes["result"]["cards"]["cardPws"], $key);
                    $data["status"] = 200;
                    $data["cards"] = json_encode($orderStatusRes["result"]["cards"]);
                    $return_status = $model_order->updGameOrder($where,$data);
                    if($return_status){
                        $kan_user_info = $user->getUserOne($value["uid"]);
                        if($kan_user_info["other_id"]){
                            $msg_data["touser"] = $kan_user_info["other_id"];
                            $msg_data["template_id"] = "UujjVK8XewVqDnT78RbeCVP_7q99-umJDtbEeqiPxBs";
                            $msg_data["url"] = HTTP_HOST.'/index/user/orderinfo.html?order_type=2&order_number='.$order_number;
                            $msg_data["data"] = array(
                                "first"=>array(
                                    "value"=>"恭喜您得了".$value["item_name"],
                                    "color"=>""
                                ),
                                "keyword1"=>array(
                                    "value"=> $cardNo,
                                    "color"=>""
                                ),
                                "keyword2"=>array(
                                    "value"=>$cardPws,
                                    "color"=>""
                                ),
                                "keyword3"=>array(
                                    "value"=> date("Y-m-d H:i:s",$value["add_time"]),
                                    "color"=>""
                                ),
                                "remark"=>array(
                                    "value"=>"点击查看订单详情",
                                    "color"=>""
                                )
                            );
                            $return_status = $weixin->send_user_message($msg_data);
                            add_log("juhe_pay_callback.log", "cli", "推送微信消息状态：". var_export($return_status, true));
                        }else{
                            add_log("juhe_pay_callback.log", "cli", "用户openid不存在，不推送微信消息：". var_export($kan_user_info, true));
                        }
                        add_log("juhe_pay_callback.log", "pay", $value["order_number"]."兑换成功，更改订单状态成功");
                    }else{
                        add_log("juhe_pay_callback.log", "pay", $value["order_number"]."兑换成功，更改订单状态失败");
                    }
                }else{
                    $data["status"] = 500;
                    $return_status = $model_order->updGameOrder($where,$data);
                    if($return_status){
                        $user_info = $game_user->getGameUserOne(["uid"=>$value["uid"]]);
                        $user_data["awardnum"] = $user_info["awardnum"]+$value["cost"];
                        $game_user->updGameUser($user_data,["uid"=>$value["uid"]]);
                        add_log("juhe_pay_callback.log", "pay", $value["order_number"]."兑换失败，更改订单状态成功");
                    }else{
                        add_log("juhe_pay_callback.log", "pay", $value["order_number"]."兑换失败，更改订单状态失败");
                    }
                }
            }
        }
    }
}
