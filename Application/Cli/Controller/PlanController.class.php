<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cli\Controller;

/**
 * Description of PlanController
 *
 * @author Administrator
 */
use Common\HeliPay\Heli;
use Common\WxApi\class_weixin_adv;
class PlanController extends InitController {
    /**
     * 定时执行计划
     */
    public function executePlan(){
        $plan_model = M("plan");
        $plan_des_model = M("plan_des");
        $plan_list = $plan_model->where(["status"=>array('in','3,4')])->select(); 
            add_log("execute_plan.log", "cli", "计划:". var_export($plan_list, true));
        if($plan_list){
            foreach ($plan_list as $pl) {
                $p_id = $pl["id"];
                $plan_des_info = $plan_des_model->where(["p_id"=>$p_id,"s_time"=>array('ELT', time()),"order_state"=>2])->order("s_time ASC")->find();
                add_log("execute_plan.log", "cli", "计划详情:".var_export($plan_des_info, true));
                if($plan_des_info){
                    $this->channelPay($pl, $plan_des_info);
                }
            }
        }else{
            
        }
    }
    /**
     * 通道消费,还款
     * @param type $plan_info
     * @param type $plan_des_info
     */
    private function channelPay($plan_info,$plan_des_info){
        if(!$plan_info||empty($plan_info)||!$plan_des_info||empty($plan_des_info)){
            return false;
        }
        $plan_model = M("plan");
        $plan_des_model = M("plan_des");
        $bank_card_model = M("bank_card_".$plan_info["c_code"]);
        $bank_card_hlb_info = $bank_card_model->where(["id"=>$plan_info["bc_id"]])->find(); //查询银行卡信息
        if(!$bank_card_hlb_info){
            add_log("execute_plan.log", "cli", "计划".$plan_info["id"]."银行卡信息不存在");
            return false;
        }
        $status = false;
        if($plan_des_info["type"]==1){
            //执行代扣
            switch ($plan_info["c_code"]) {
                case "hlb":
                    require_once APP_ROOT . "Application/Common/Concrete/helipay/HeliPay.php";
                    $heli_pay = new Heli();
                    $arg = array(
                        'bindId'=>$bank_card_hlb_info['bind_id'],
                        'userId'=>$plan_des_info['u_id'],
                        'orderId'=>$plan_des_info["order_id"],
                        'orderAmount'=>$plan_des_info["amount"],
                        'terminalType'=>'IMEI',
                        'terminalId'=>'122121212121',
                        'queryUrl'=>HTTP_HOST."/index/callback/hlbPay",
                        'Code'=>'',
                    );
                    $hlb_dh = $heli_pay->bindingCardPay($arg);//执行代扣
                    add_log("execute_plan.log", "cli", "合利宝扣款返回信息：".var_export($hlb_dh, true));
                    if(!$hlb_dh){
                        $upd_plan_des_data["message"] = "消费失败";
                        $upd_plan_des_data["order_state"] = 4;
                        $plan_des_model->where(["id"=>$plan_des_info["id"]])->save($upd_plan_des_data);
                        $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>5]);
                        $this->sendWxErrorMessage($plan_info, "消费失败", "消费");
                    }else{
                        if ($hlb_dh['rt2_retCode'] == '0000') {
                            $upd_plan_des_data["message"] = "提交成功,等待回调通知";
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$plan_des_info["id"]])->save($upd_plan_des_data);
//                                $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>1]);
                            $status = true;
                        }elseif($hlb_dh['rt2_retCode'] == '0001'){
                            $upd_plan_des_data["message"] = $hlb_dh['rt3_retMsg'];
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$plan_des_info["id"]])->save($upd_plan_des_data);
                        }else{
                            $upd_plan_des_data["message"] = $hlb_dh['rt3_retMsg'];
                            $upd_plan_des_data["order_state"] = 4;
                            $plan_des_model->where(["id"=>$plan_des_info["id"]])->save($upd_plan_des_data);
                            $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>5]);
                            $this->sendWxErrorMessage($plan_info, $hlb_dh['rt3_retMsg'], "消费");
                        }
                    }
                    break;

                default:
                    break;
            }
            return $status;
        }elseif($plan_des_info["type"]==2){
            //执行代还
            switch ($plan_info["c_code"]) {
                case "hlb":
                    require_once APP_ROOT . "Application/Common/Concrete/helipay/HeliPay.php";
                    $heli_pay = new Heli();
                    $arg = array(
                        'userId' => $plan_des_info['u_id'],
                        'bindId' => $bank_card_hlb_info['bind_id'],
                        'order_id' => $plan_des_info["order_id"],
                        'amount' => $plan_des_info["amount"],
                    );
                    $hlb_dh = $heli_pay->creditWithdraw($arg);//执行代还
                    add_log("execute_plan.log", "cli", "合利宝还款返回信息：".var_export($hlb_dh, true));
                    if(!$hlb_dh){
                        $upd_plan_des_data["message"] = "还款失败";
                        $plan_des_model->where(["id"=>$plan_des_info["id"]])->save($upd_plan_des_data);
                        $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>5]);
                        $this->sendWxErrorMessage($plan_info, "还款失败", "还款");
                    }else{
                        if ($hlb_dh['rt2_retCode'] == '0000') {
                            $upd_plan_des_data["message"] = "还款成功";
                            $upd_plan_des_data["order_state"] = 1;
                            $upd_plan_des_data["d_time"] = time();
                            $plan_des_model->where(["id"=>$plan_des_info["id"]])->save($upd_plan_des_data);
                            if((int)($plan_info["periods"]*2)==$plan_des_info["num"]){
                                $plan_status = 1;
                                $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>$plan_status]);
                            }
                            $this->sendWxMessage($plan_info, $plan_des_info);
                             $status = true;
                        }elseif($hlb_dh['rt2_retCode'] == '0001'){
                            $upd_plan_des_data["message"] = $hlb_dh['rt3_retMsg'];
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$plan_des_info["id"]])->save($upd_plan_des_data);
                        }else{
                            $upd_plan_des_data["message"] = $hlb_dh['rt3_retMsg'];
                            $upd_plan_des_data["order_state"] = 4;
                            $plan_des_model->where(["id"=>$plan_des_info["id"]])->save($upd_plan_des_data);
                            $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>5]);
                            $this->sendWxErrorMessage($plan_info, $hlb_dh['rt3_retMsg'], "还款");
                        }
                    }
                    break;

                default:
                    break;
            }
            return $status;
        }else{
            return $status;
        }
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
            require_once APP_ROOT ."Application/Common/Concrete/wxapi/example/weixin.api.php";
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
            add_log("callback_helipay.log", "callback", "计划成功公众号消息推送数据：". var_export($msg_data, true));
            add_log("callback_helipay.log", "callback", "计划成功公众号消息推送状态：". var_export($return_status, true));
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
            require_once APP_ROOT ."Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            $msg_data["touser"] = $cunstomer_wx_binding_info["open_id"];
            $msg_data["template_id"] = "qq5apA1Ku6rbm0IWkD_QMHRjAaSOuCu9Fv62SjPpmrE";
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
            add_log("callback_helipay.log", "callback", "计划失败公众号消息推送数据：". var_export($msg_data, true));
            add_log("callback_helipay.log", "callback", "计划失败公众号消息推送状态：". var_export($return_status, true));
        }
    }
    
    
    public function timereport(){
        $date_yes = date("Y-m-d",strtotime("-1 day"));
        $date_tod = date('Y-m-d');
        $db_config = C("DB_CONFIG2");
        $pay_records_m = M("pay_records",$db_config["DB_PREFIX"],$db_config);
        $plan_des_m = M("plan_des");
        
        $pay_records_info = $pay_records_m->where("state = 1 and created > '".$date_yes."' and created < '".$date_tod."' ")->sum('pay');
        $sum_yes = $pay_records_info?$pay_records_info:0; //会收钱昨日交易额
        $pay_records_info1 = $pay_records_m->where("state = 1 and created > '".$date_tod."' ")->sum('pay');
        $sum_today = $pay_records_info1?$pay_records_info1:0; //会收钱今日交易额
        
        $plan_des_info = $plan_des_m->where("s_time > '".strtotime($date_yes)."' and s_time < '".strtotime($date_tod)."' and order_state=1 and type=1 ")->sum('amount');
        $hhk_sum_yes = $plan_des_info?$plan_des_info:0; //会还款昨日交易额
        $plan_des_info1 = $plan_des_m->where("s_time > '".strtotime($date_tod)."' and order_state=1 and type=1 ")->sum('amount');
        $hhk_sum_today = $plan_des_info1?$plan_des_info1:0; //会还款今日交易额
        
        
        $msg = '《会收钱》昨日交易额:' . $sum_yes . ',今日交易额:' . $sum_today.'；《会还款》昨日交易额:' . $hhk_sum_yes . ',今日交易额:' . $hhk_sum_today;
        $this->preparereport($msg);
    }
    private function preparereport($msg)
    {
        $db_config = C("DB_CONFIG2");
        $account_status_m = M("account_status",$db_config["DB_PREFIX"],$db_config);
        $type = 4;
	$sqllist = "SELECT c.open_id,c.wx_name FROM l_account_status a INNER JOIN l_cunstomer_wx_binding c ON a.list = c.user_id WHERE a.type = '".$type."' AND a.isToPush = '1'";
        $open_ids = $account_status_m->query($sqllist);
        if ($open_ids) {
            $user_m = D("User");
            foreach ($open_ids as $value) {
                $user_m->wxMessagewxYwlcMsg('','您有1条业务消息提醒，请关注','会收钱通知',date("Y-m-d H:i:s"),$msg,'请关注','','',$value['open_id']);
            }
        }
    }
}
