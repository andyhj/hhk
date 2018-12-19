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
            $url = HTTP_HOST. '/mobile/perfect_info/registered';
            if ($return_status === 113) {
                header('Location: ' . $url);
                die();
            }
        }
        $this->assign('userInfo', $this->user_info);
        $this->assign('wx_share_url', $this->http . $_SERVER['HTTP_HOST'] . '/s/' . $this->user_info["id"] . '-0-0-0-0.html');
    }
    public function index(){
        
    }

    //添加计划
    public function planSubmit(){
        $c_id = I("c_id",1);  //通道id
        $b_id = I("b_id"); //银行卡id
        $u_id = $this->user_info["id"];
        $amount = I("amount"); //金额
        $periods = I("periods"); //期数(6,12,24)
        $session_name = "plan_submit_".$u_id;
        if(session($session_name)){
            $json["status"] = 305;
            $json["info"] = "正在提交...";
            $this->returnJson($json);
        }
        session($session_name,1);
        if(!$c_id||!$b_id||!$u_id||!$amount||!$periods){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->returnJson($json,$session_name);
        }
        $channel_model = M("channel");
        $user_vip_model = M("user_vip");
        $plan_model = M("plan");
        $plan_des_model = M("plan_des");
        $p_amount = round($amount/$periods, 2); //每期扣款额度
        if($p_amount<200){
            $json["status"] = 306;
            $json["info"] = "每期扣款额度不能低于200";
            $this->returnJson($json,$session_name);
        }
        //查询通道
        $channel_info = $channel_model->where(["id"=>$c_id])->find(); 
        if(!$channel_info){
            $json["status"] = 306;
            $json["info"] = "通道不存在";
            $this->returnJson($json,$session_name);
        }
        $bank_card_hlb_model = M("bank_card_".$channel_info["code"]);
        //查询银行卡
        $bank_card_hlb_info = $bank_card_hlb_model->where(["id"=>$b_id])->find();
        if(!$bank_card_hlb_info||!$bank_card_hlb_info["success"]){
            $json["status"] = 307;
            $json["info"] = "银行卡不存在";
            $this->returnJson($json,$session_name);
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
            $this->returnJson($json,$session_name);
        }
        if($d>=$$repayment){
            $json["status"] = 308;
            $json["info"] = "请在还款日前制定计划";
            $this->returnJson($json,$session_name);
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
            $this->returnJson($json,$session_name);
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
        $p_fee = round($p_amount*$fee+$close_rate); //每期手续费
        
        $plan_data = array(
            "u_id" => $u_id,
            "c_id" => $c_id,
            "c_code" => $channel_info["code"],
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
            $this->returnJson($json,$session_name);
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
                $this->returnJson($json,$session_name);
            }
            $json["status"] = 400;
            $json["info"] = "插入计划详情失败";
            $this->returnJson($json,$session_name);
        }else{
            $json["status"] = 401;
            $json["info"] = "制定计划详情失败";
            $this->returnJson($json,$session_name);
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
                    "s_time" => strtotime($k_time),
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
                    "s_time" => strtotime($h_time),
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
                    "s_time" => strtotime($k1_time),
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
                    "s_time" => strtotime($h1_time),
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
                    "s_time" => strtotime($k2_time),
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
                    "s_time" => strtotime($h2_time),
                    "type" => 2,
                    "days" => $date,
                );
                $a ++;
                $date = date("Y-m-d",strtotime("+1 day",strtotime($date)));
            }
        }
        return $plan_des_arr;
    }
    
    //补单
    public function repOrder(){
        $pd_id = I("post.id");  //计划详情id
        $session_name = "plan_rep_".$pd_id;
        if(session($session_name)){
            $json["status"] = 305;
            $json["info"] = "正在提交...";
            $this->returnJson($json);
        }
        session($session_name,1);
        $h = (int)date("H");
        if($h>20||$h<8){
            $json["status"] = 305;
            $json["info"] = "请在8-20点时间段补单";
            $this->returnJson($json,$session_name);
        }
        if(!$pd_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->returnJson($json,$session_name);
        }
        $plan_model = M("plan");
        $plan_des_model = M("plan_des");
        $plan_des_info = $plan_des_model->where(["id"=>$pd_id])->find();  //计划详情
        if(!$plan_des_info||empty($plan_des_info)){
            $json["status"] = 306;
            $json["info"] = "计划不存在";
            $this->returnJson($json,$session_name);
        }
        $plan_info = $plan_model->where(["id"=>$plan_des_info["p_id"]])->find(); //查询计划信息
        if(!$plan_info||empty($plan_info)){
            $json["status"] = 307;
            $json["info"] = "计划不存在";
            $this->returnJson($json,$session_name);
        }
        $bank_card_hlb_model = M("bank_card_".$plan_info["c_code"]);
        $bank_card_hlb_info = $bank_card_hlb_model->where(["id"=>$plan_info["bc_id"]])->find(); //查询银行卡信息
        if(!$bank_card_hlb_info||empty($bank_card_hlb_info)){
            $json["status"] = 308;
            $json["info"] = "银行卡不存在";
            $this->returnJson($json,$session_name);
        }
        $t_time = date("Y-m-d");
        $periods = $plan_info["periods"]; //总期数
        $p_time = date("Y-m", strtotime($plan_info["add_time"])); //获取计划月份
        $repayment = $p_time."-".$bank_card_hlb_info["repayment"]; //还款日
        if($bank_card_hlb_info["bill"]>$bank_card_hlb_info["repayment"]){
            $repayment = date("Y-m",strtotime("+1 month",strtotime($p_time)))."-".$bank_card_hlb_info["repayment"]; //还款日
        }
        //判断是否已过还款期
        if(strtotime($t_time)>= strtotime($repayment)){
            $json["status"] = 309;
            $json["info"] = "已过还款期";
            $this->returnJson($json,$session_name);
        }
        
        //判断是否最后一期，如果是 直接补单
        if($periods*2==$plan_des_info["num"]){
            if($plan_des_info["type"]==1){
                $json["status"] = 310;
                $json["info"] = "计划异常";
                $this->returnJson($json,$session_name);
            }
            $this->replacementOrder($plan_info, $plan_des_info,$session_name); //通道补单
        }
        
        $plan_des_next_info = $plan_des_model->where(["num"=>$plan_des_info["num"]+1,"u_id"=>$plan_des_info["u_id"],"p_id"=>$plan_des_info["p_id"]])->find();  //查询下一期计划
        if(!$plan_des_next_info||empty($plan_des_next_info)){
            $json["status"] = 312;
            $json["info"] = "计划异常";
            $this->returnJson($json,$session_name);
        }
        //判断当前补单时间是否小于下一期，如果是 直接补单
        if(time()<$plan_des_next_info["s_time"]){
            if($plan_des_next_info["s_time"]- time()<1800){ //当前时间距离下一期任务小于半小时，则下一期任务延期半小时
                $plan_des_model->where(["id"=>$plan_des_next_info["id"]])->save(["s_time"=>$plan_des_next_info["s_time"]+1800]);
            }
            $this->replacementOrder($plan_info, $plan_des_info,$session_name); //通道补单
        }
        
        //如果当前期数补单时间大于下一期时间，则要修改之后期数时间
        $residue_periods = $periods*2-$plan_des_info["num"]; //查询剩余期数
        $date_2 = $repayment;
	$d1 = strtotime($t_time);
	$d2 = strtotime($date_2);
	$days = round(($d2-$d1)/3600/24); //计算距离还款日天数
        $d_periods = ceil($residue_periods/4); //每天最多执行（代扣+代还）4次，计算出要几天
        if($days<$d_periods){
            $json["status"] = 313;
            $json["info"] = "距离还款日较近，请重新制定计划";
            $this->returnJson($json,$session_name);
        }
        $p_des_where["num"] = array('GT',$plan_des_info["num"]);
        $p_des_where["u_id"] = $plan_des_info["u_id"];
        $p_des_where["p_id"] = $plan_des_info["p_id"];
        $plan_des_list = $plan_des_model->where($p_des_where)->order('num asc')->select();
        if($plan_des_list&&!empty($plan_des_list)){
            $d = 0;
            $rt_s = 0;
            foreach ($plan_des_list as $k=>$pdl) {
                //查询下一期距离现在天数
                if($k==0){
                    $pd1 = strtotime($pdl["days"]);
                    $pd2 = strtotime($t_time);
                    $d = round(($pd2-$pd1)/3600/24)+1; 
                }
                $pds_data["s_time"] = strtotime("+$d day",$pdl["s_time"]);
                $pds_data["days"] = date("Y-m-d",strtotime("+$d day",$pdl["s_time"]));
                $rt_s = $plan_des_model->where(["id"=>$pdl["id"]])->save($pds_data); //修改之后计划执行时间
            }
            if($rt_s){
                $this->replacementOrder($plan_info, $plan_des_info,$session_name); //通道补单
            }
        }
        $json["status"] = 318;
        $json["info"] = "补单重新制定计划失败";
        $this->returnJson($json,$session_name);
    }
    /**
     * 通道补单，如果新增通道，修改此方法即可
     * @param type $plan_info    //计划
     * @param type $plan_des_info //计划详情
     */
    private function replacementOrder($plan_info,$plan_des_info,$session_name=''){
        $json["status"] = 311;
        $json["info"] = "补单失败";
        if(!$plan_info||empty($plan_info)||!$plan_des_info||empty($plan_des_info)){
            $this->returnJson($json,$session_name);
        }
        $plan_model = M("plan");
        $plan_des_model = M("plan_des");
        $letters = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9','Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', 'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'Z', 'X', 'C', 'V', 'B', 'N', 'M'];
        $uid = $plan_des_info["u_id"];
        $pd_id = $plan_des_info["id"];
        $remedy_id = "BD".get_rand_str(6,$letters).$uid. time(); //补单订单号
        $upd_plan_des_data["remedy_id"] = $remedy_id;
        $upd_plan_des_data["remedy_time"] = time();
        if($plan_des_info["type"]==1){
            //执行代扣
            switch ($plan_info["c_code"]) {
                case "hlb":
                    $hlb_dh = [];//执行代扣
                    if(!$hlb_dh){
                        $upd_plan_des_data["message"] = "补单失败";
                        $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                    }else{
                        if ($hlb_dh['rt2_retCode'] == '0000') {
                            $upd_plan_des_data["message"] = "提交成功,等待回调通知";
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
//                                $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>1]);
                            $json["status"] = 200;
                            $json["info"] = "提交成功,等待回调通知";
                        }elseif($hlb_dh['rt2_retCode'] == '0001'){
                            $upd_plan_des_data["message"] = $hlb_dh['rt3_retMsg'];
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            $json["status"] = 311;
                            $json["info"] = $hlb_dh['rt3_retMsg'];
                        }else{
                            $upd_plan_des_data["message"] = $hlb_dh['rt3_retMsg'];
                            $upd_plan_des_data["order_state"] = 4;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            $json["status"] = 311;
                            $json["info"] = $hlb_dh['rt3_retMsg'];
                        }
                    }
                    break;

                default:
                    break;
            }
        }elseif($plan_des_info["type"]==2){
            //执行代还
            switch ($plan_info["c_code"]) {
                case "hlb":
                    $hlb_dh = [];//执行代还
                    if(!$hlb_dh){
                        $upd_plan_des_data["message"] = "补单失败";
                        $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                    }else{
                        if ($hlb_dh['rt2_retCode'] == '0000') {
                            $upd_plan_des_data["message"] = "补单成功";
                            $upd_plan_des_data["order_state"] = 1;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>1]);
                            $json["status"] = 200;
                            $json["info"] = "补单成功";
                        }elseif($hlb_dh['rt2_retCode'] == '0001'){
                            $upd_plan_des_data["message"] = $hlb_dh['rt3_retMsg'];
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            $json["status"] = 311;
                            $json["info"] = $hlb_dh['rt3_retMsg'];
                        }else{
                            $upd_plan_des_data["message"] = $hlb_dh['rt3_retMsg'];
                            $upd_plan_des_data["order_state"] = 4;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            $json["status"] = 311;
                            $json["info"] = $hlb_dh['rt3_retMsg'];
                        }
                    }
                    break;

                default:
                    break;
            }
        }else{
            $json["status"] = 311;
            $json["info"] = "类型错误";
        }
        $this->returnJson($json,$session_name);
    }

    private function returnJson($data,$session_name=""){
        if($session_name){
            session($session_name, null);
        }
        $this->ajaxReturn($data);
    }
}
