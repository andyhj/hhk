<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Controller;

/**
 * Description of PlanController
 *
 * @author Administrator
 */
class PlanController extends InitController {
    private $user_info;
    
    public function __construct() {
        header("Content-type: text/html; charset=utf-8"); 
        parent::__construct();
        $recommend = delTrim(I("rec", session("rec"))); //推荐人
        $this->user_info = $this->getUserInfo();
        $wxh5login = new WxH5Login();
        if (!$this->user_info) {
            $return_status = $wxh5login->wxLogin($recommend);
            if ($return_status === 200) {
                $this->user_info = $this->getUserInfo();
            }
            if ($return_status === 111) {
                echo '<script>alert("推荐用户不存在");</script>';
                die();
            }
            if ($return_status === 112) {
                echo '<script>alert("登陆失败");</script>';
                die();
            }
            if ($return_status === 130) {
                echo '<script>alert("账号被封号，请联系客服");</script>';
                die();
            }
            $url = $this->http . $_SERVER['HTTP_HOST'] . '/index/user/qrcode.html';
            if ($return_status === 113) {
//                $model_user = D("user");
//                if($recommend){
//                    $recommend_info = $model_user->getUserOne($recommend);
//                    if(!$recommend_info||$recommend_info["type"]==1){
//                        header('Location: ' . $url);
//                        die();
//                    }
//                }else{
//                    header('Location: ' . $url);
//                    die();
//                }
            }
        } else {
            if ($this->user_info["status"]) {
                echo '<script>alert("账号被封号，请联系客服");</script>';
                die();
            }
        }
        $this->assign('userInfo', $this->user_info);
        $this->assign('wx_share_url', $this->http . $_SERVER['HTTP_HOST'] . '/s/' . $this->user_info["id"] . '-0-0-0-0.html');
    }
    public function index(){
        
    }

    public function planSubmit(){
        $c_id = 1;  //通道id
        $b_id = I("b_id"); //银行卡id
        $u_id = $this->user_info["id"];
        $amount = I("amount"); //金额
        $periods = I("periods"); //期数(6,12,24)
        if(!$c_id||!$b_id||!$u_id||!$amount||!$periods){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $channel_model = M("channel");
        $bank_card_hlb_model = M("bank_card_hlb");
        $user_vip_model = M("user_vip");
        $plan_model = M("plan");
        $plan_des_model = M("plan_des");
        //查询通道
        $channel_info = $channel_model->where(["id"=>$c_id])->find(); 
        if(!$channel_info){
            $json["status"] = 306;
            $json["info"] = "通道不存在";
            $this->ajaxReturn($json);
        }
        //查询银行卡
        $bank_card_hlb_info = $bank_card_hlb_model->where(["id"=>$b_id])->find();
        if(!$bank_card_hlb_info||!$bank_card_hlb_info["success"]){
            $json["status"] = 307;
            $json["info"] = "银行卡不存在";
            $this->ajaxReturn($json);
        }
        $bill = date("Y-m")."-".$bank_card_hlb_info["bill"];  //账单日
        $repayment = date("Y-m")."-".$bank_card_hlb_info["repayment"]; //还款日
        if($bank_card_hlb_info["bill"]>$bank_card_hlb_info["repayment"]){
            $repayment = date("Y-m",strtotime("+1 month"))."-".$bank_card_hlb_info["repayment"]; //还款日
        }
        $d = date("Y-m-d", time());
        if($d<=$bill){
            $json["status"] = 308;
            $json["info"] = "请在账单日后制定计划";
            $this->ajaxReturn($json);
        }
        if($d>=$$repayment){
            $json["status"] = 308;
            $json["info"] = "请在还款日前制定计划";
            $this->ajaxReturn($json);
        }
        $reserved_days = 3; //预留天数
        $p_d = $periods/2+$reserved_days;
        $date_1 = date("Y-m-d");
	$date_2 = $repayment;
	$d1 = strtotime($date_1);
	$d2 = strtotime($date_2);
	$days = round(($d2-$d1)/3600/24); //计算距离还款日天数
        if($days<=$p_d){
            $json["status"] = 308;
            $json["info"] = "选择{$periods}期距离还款日必须大于{$p_d}天";
            $this->ajaxReturn($json);
        }
        $fee = $channel_info["user_fee"]; //普通用户交易费率
        $close_rate = $channel_info["user_close_rate"];   //普通用户结算费用（每笔）
        $user_vip_info = $user_vip_model->where(["u_id"=>$u_id])->find();
        $is_plus = 0;
        //判断是否plus会员
        if($user_vip_info && strtotime($user_vip_info["end_time"])> time()){
            $is_plus = 1;
            $fee = $channel_info["plus_user_fee"]; //plus用户交费率
            $close_rate = $channel_info["plus_user_close_rate"];   //plus用户结算费用（每笔）
        }
        $p_amount = round($amount/$periods, 2); //每期扣款额度
        $p_fee = round($p_amount*$fee+$close_rate); //每期手续费
        
        $plan_data = array(
            "u_id" => $u_id,
            "c_id" => $c_id,
            "bc_id" => $b_id,
            "amount" => $amount,
            "periods" => $periods,
            "p_amount" => $p_amount,
            "p_fee" => $p_fee,
            "fee" => $fee,
            "close_rate" => $close_rate
        );
        $plan_id = $plan_model->add($plan_data);
        if(!$plan_id){
            $json["status"] = 309;
            $json["info"] = "生成计划失败";
            $this->ajaxReturn($json);
        }
        $is_include = 2; //是否包含今天
        //如果用户在8点前制定计划，加上这一天
        if(date("H")<8){
            $days+=1;
            $is_include = 1;
        }
        $num = 2;  //每天执行次数
        //如果时间大于期数加预留时间，则每天执行一次代扣和代还
        if($periods+$reserved_days<$days){
            $num = 1;
        }
        $plan_des_arr = $this->getPlanDes($plan_id, $u_id, $p_amount, $p_fee, $is_include, $periods, $num);
        if($plan_des_arr&&empty($plan_des_arr)){
            $return_status = $plan_des_model->addAll($plan_des_arr);
            if($return_status){
                $json["status"] = 200;
                $json["info"] = "生成计划成功";
                $this->ajaxReturn($json);
            }
            $json["status"] = 400;
            $json["info"] = "插入计划详情失败";
            $this->ajaxReturn($json);
        }else{
            $json["status"] = 401;
            $json["info"] = "制定计划详情失败";
            $this->ajaxReturn($json);
        }
    }
    /**
     * 生成计划详情列表
     * @param type $p_id 计划id
     * @param type $uid 用户id
     * @param type $p_amount 扣款金额
     * @param type $p_fee 扣款手续金额
     * @param type $type 类型（1，包含今天一整天；2，第二天开始）
     * @param type $periods 期数
     * @param type $num 每天执行次数（1，执行一次；2，执行两次；最多两次）
     */
    private function getPlanDes($p_id,$uid,$p_amount,$p_fee,$type,$periods,$num){
        $date = date("Y-m-d");
        $plan_des_arr = [];
        $letters = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9','Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', 'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'Z', 'X', 'C', 'V', 'B', 'N', 'M'];
        //每天执行一次
        if($num==1){
            if($type==2){
                $date = date("Y-m-d",strtotime("+1 day"));
            }
            $a = 1;
            for($i=0;$i<$periods;$i++){
                $begintime = $date." 08:10:00";
                $endtime = $date." 11:30:00";
                $k_time = randomDate($begintime,$endtime); //随机生成执行时间
                //代扣
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "K".get_rand_str(6,$letters).$uid. time(),
                    "amount" => round($p_amount+$p_fee, 2),
                    "s_time" => $k_time,
                    "type" => 1,
                    "days" => $date,
                );

                $begintime = $date." 13:00:00";
                $endtime = $date." 16:30:00";
                $h_time = randomDate($begintime,$endtime); //随机生成执行时间
                $a ++;
                //代还
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "H".get_rand_str(6,$letters).$uid. time(),
                    "amount" => $p_amount,
                    "s_time" => $h_time,
                    "type" => 2,
                    "days" => $date,
                );
                $a ++;
                $date = date("Y-m-d",strtotime("+1 day",strtotime($date)));
            }
        }
        //每天执行两次
        if($num==2){
            if($type==2){
                $date = date("Y-m-d",strtotime("+1 day"));
            }
            $a = 1;
            for($i=0;$i<($periods/2);$i++){
                $begintime = $date." 08:10:00";
                $endtime = $date." 10:00:00";
                $k1_time = randomDate($begintime,$endtime); //随机生成执行时间
                //代扣
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "K".get_rand_str(6,$letters).$uid. time(),
                    "amount" => round($p_amount+$p_fee, 2),
                    "s_time" => $k1_time,
                    "type" => 1,
                    "days" => $date,
                );

                $begintime = $date." 11:00:00";
                $endtime = $date." 13:00:00";
                $h1_time = randomDate($begintime,$endtime); //随机生成执行时间
                $a ++;
                //代还
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "H".get_rand_str(6,$letters).$uid. time(),
                    "amount" => $p_amount,
                    "s_time" => $h1_time,
                    "type" => 2,
                    "days" => $date,
                );
                
                $begintime = $date." 14:00:00";
                $endtime = $date." 16:00:00";
                $k2_time = randomDate($begintime,$endtime); //随机生成执行时间
                $a ++;
                //代扣
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "K".get_rand_str(6,$letters).$uid. time(),
                    "amount" => round($p_amount+$p_fee, 2),
                    "s_time" => $k2_time,
                    "type" => 1,
                    "days" => $date,
                );

                $begintime = $date." 17:00:00";
                $endtime = $date." 19:00:00";
                $h2_time = randomDate($begintime,$endtime); //随机生成执行时间
                $a ++;
                //代还
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "H".get_rand_str(6,$letters).$uid. time(),
                    "amount" => $p_amount,
                    "s_time" => $h2_time,
                    "type" => 2,
                    "days" => $date,
                );
                $a ++;
                $date = date("Y-m-d",strtotime("+1 day",strtotime($date)));
            }
        }
        return $plan_des_arr;
    }
}
