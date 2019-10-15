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
use Common\Common\WxH5Login;
use Common\HeliPay\Heli;
use Common\WxApi\class_weixin_adv;
use Common\GyfPay\gyf;
class PlanController extends InitController {
    private $user_info;
    private $user_wx_info;
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
        //    $url = HSQ_HOST. '/mobile/perfect_info/registered';
            $url = HSQ_HOST. '/mobile/binding/new_binding';
            if ($return_status === 113) {
                header('Location: ' . $url);
                die();
            }
        }
        $db_config = C("DB_CONFIG2");
        $customer_wx_m = M("cunstomer_wx_binding",$db_config["DB_PREFIX"],$db_config);
        $wx = $customer_wx_m->where(["user_id"=>$this->user_info["id"],"state"=>1])->find();
        if(!$wx){
            add_log("wxlogin.log", "plan", "微信登陆数据：". var_export($wx, true));
            echo '<script>alert("请先关注会收钱公众号");</script>';
            die();
        }
        $this->user_wx_info = $wx;
        $this->assign('userInfo', $this->user_info);
        $this->assign('wx_share_url', $this->http . $_SERVER['HTTP_HOST'] . '/' . $this->user_info["id"] . '-0-0-0-0.html');
    }
    public function index(){
        $u_id = $this->user_info["id"];
        $plan_model = M("plan");
        $current_page = 1;
        $per_page = 10;
        $plan_list1 = $plan_model->where(["u_id"=>$u_id,"status"=>3])->order("add_time desc")->page($current_page.','.$per_page)->select(); //正在执行
        $plan_list2 = $plan_model->where(["u_id"=>$u_id,"status"=>1])->order("add_time desc")->page($current_page.','.$per_page)->select(); //已完成
        $plan_list3 = $plan_model->where(["u_id"=>$u_id,"status"=>array('in','0,2,4,5')])->order("add_time desc,status desc")->page($current_page.','.$per_page)->select(); //未执行
        $plan_arr1 = [];
        $plan_arr2 = [];
        $plan_arr3 = [];
        if($plan_list1){
            foreach ($plan_list1 as $p1) {
                $card_info = M("bank_card_".$p1["c_code"])->where(["id"=>$p1["bc_id"]])->find();
                $p1["bank_name"] = $card_info["bank_name"];
                $p1["user_name"] = $card_info["user_name"];
                $p1["card_no"] = substr($card_info["card_no"],-4);
                $plan_arr1[] = $p1;
            }
        }
        if($plan_list2){
            foreach ($plan_list2 as $p2) {
                $card_info = M("bank_card_".$p2["c_code"])->where(["id"=>$p2["bc_id"]])->find();
                $p2["bank_name"] = $card_info["bank_name"];
                $p2["user_name"] = $card_info["user_name"];
                $p2["card_no"] = substr($card_info["card_no"],-4);
                $plan_arr2[] = $p2;
            }
        }
        if($plan_list3){
            foreach ($plan_list3 as $p3) {
                $card_info = M("bank_card_".$p3["c_code"])->where(["id"=>$p3["bc_id"]])->find();
                $p3["bank_name"] = $card_info["bank_name"];
                $p3["user_name"] = $card_info["user_name"];
                $p3["card_no"] = substr($card_info["card_no"],-4);
                $plan_arr3[] = $p3;
            }
        }
        $this->assign('is_jh',1);
        $this->assign('plan_arr1', $plan_arr1);
        $this->assign('plan_arr2', $plan_arr2);
        $this->assign('plan_arr3', $plan_arr3);
        $this->display();
    }
    /**
     *计划详情
     */
    public function plandes(){
        $u_id = $this->user_info["id"];
        $p_id = I("id");
        $url = U("index/plan/index");
        if(!$p_id){
            $this->error("参数错误",$url);die();
        }
        $plan_des_list = M("plan_des")->where(["u_id"=>$u_id,"p_id"=>$p_id])->order('id ASC')->select();
        if(!$plan_des_list||empty($plan_des_list)){
            $this->error("计划不存在",$url);die();
        }
        $plan_des_arr = [];
        if($plan_des_list&&!empty($plan_des_list)){
            foreach ($plan_des_list as $val) {
                $val["type_name"] = "";
                if($val["type"]==1){
                    $val["type_name"] = "消费";
                }
                if($val["type"]==2){
                    $val["type_name"] = "还款";
                }
                switch ($val["order_state"]) {
                    case 1:
                        $val["status_name"] = "成功";
                        break;
                    case 2:
                        $val["status_name"] = "待执行";
                        break;
                    case 3:
                        $val["status_name"] = "执行中";
                        break;
                    case 4:
                        $val["status_name"] = "失败";
                        break;
                    default:
                        $val["status_name"] = "";
                        break;
                }
                $plan_des_arr[] = $val;
            }
        }
        $plan_info = M("plan")->where(["u_id"=>$u_id,"id"=>$p_id])->find();
        $this->assign("plan_info",$plan_info);
        $this->assign("plan_des_list",$plan_des_arr);
        $this->assign('cancel_url', U("index/plan/cancel"));
        $this->display("des");

    }
    public function orderdes(){
        $u_id = $this->user_info["id"];
        $p_id = I("id");
        $type = I("type",1);
        $this->assign('p_id', $p_id);
        $this->assign('type', $type);
        $this->assign('home', HTTP_HOST);
        $this->assign('plandes', U("index/plan/plandes",['id'=>$p_id]));
        $this->assign('ad_url', AD_HOST.'?uid='.$u_id);
        $this->display();
    }
    public function cancel(){
        $u_id = $this->user_info["id"];
        $p_id = I("post.p_id");
        if(!$p_id){
            $json["status"] = 306;
            $json["info"] = "参数错误";
            $this->returnJson($json);
        }
        $plan_info = M("plan")->where(["u_id"=>$u_id,"id"=>$p_id])->find();
        if(!$plan_info||empty($plan_info)){
            $json["status"] = 306;
            $json["info"] = "计划不存在";
            $this->returnJson($json);
        }
        $s = M("plan")->where(["u_id"=>$u_id,"id"=>$p_id])->save(["status"=>2]);
        if($s){
            $json["status"] = 200;
            $json["info"] = "计划终止成功";
            $this->returnJson($json);
        }
        $json["status"] = 307;
        $json["info"] = "计划终止失败";
        $this->returnJson($json);
    }

    /**
     * 通道列表
     */
    public function channel(){
        $u_id = $this->user_info["id"];
        $channel_moblie_m = M("channel_moblie");
        $where = [];
        if($u_id!=464885){
            $where["state"] = 1;
        } 
        $num = $channel_moblie_m->where($where)->count();
        $channel_moblie_list = $channel_moblie_m->where($where)->order("sort ASC")->select();
        // if($num==1&&$channel_moblie_list){
        //     $url = U("index/plan/planadd",["c_id"=>$channel_moblie_list[0]["c_id"]]);
        //     header('Location: ' . $url);
        //     die();
        // }
        $channels_arr = [];
        if($channel_moblie_list){
            foreach ($channel_moblie_list as $value) {
                $channel_info = M("channel")->where(["id"=>$value["c_id"]])->find();
                $value["channel_info"] = $channel_info;
                $channels_arr[] = $value;
            }
        }
        $this->assign('channels', $channels_arr);
        $this->display();
    }
    /**
     * 添加计划
     */
    public function planadd(){
        $c_id = I("c_id"); //通道id
        $u_id = $this->user_info["id"];
        if(!$c_id||!$u_id){
            echo '<script>alert("参数错误");</script>';
            die();
        }
        $channel_model = M("channel");
        $channel_moblie_m = M("channel_moblie");
        $channel_info = $channel_model->where(["id"=>$c_id])->find();
        if(!$channel_info){
            echo '<script>alert("通道不存在");</script>';
            die();
        }
        $fee = $channel_info["user_fee"]; //普通用户交易费率
        $close_rate = $channel_info["user_close_rate"];   //普通用户结算费用（每笔）
        $is_plus = 0;
        $user_m = M("user");
        $user_des = $user_m->where(["u_id"=>$u_id])->find();
        //判断是否plus会员
        if($user_des && $user_des['is_vip']){
            $is_plus = 1;
            $fee = $channel_info["plus_user_fee"]; //plus用户交费率
            $close_rate = $channel_info["plus_user_close_rate"];   //plus用户结算费用（每笔）
        }
        $bank_card_model = M("bank_card_".$channel_info["code"]);
        $bank_card_list = $bank_card_model->where(["uid"=>$u_id,"success"=>1])->select();
        $this->assign('bank_card_list', $bank_card_list);
        $this->assign('channel_moblie_info', $channel_moblie_m->where(["c_id"=>$c_id])->find());
        $this->assign('is_plus', $is_plus);
        $this->assign('fee', $fee);
        $this->assign('close_rate', (int)$close_rate);
        $this->assign('channel_info', $channel_info);
        $this->assign('add_plan_url', U("index/plan/planSubmit"));
        $this->assign('add_card_url', U("index/card/addCard",["c_code"=>$channel_info["code"]]));
        $this->assign('cart_url', U("index/card/index",["c_code"=>$channel_info["code"]]));
        $this->assign('getcard_url', U("index/plan/getCard"));
        $this->assign('c_code', $channel_info["code"]);
        $this->assign('c_id', $c_id);
        $this->display();
    }
    public function getCard(){
        $c_code = I("c_code");
        $id = I("id");
        $u_id = $this->user_info["id"];
        if(!$c_code||!$id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->returnJson($json);
        }
        $bank_card_model = M("bank_card_".$c_code);
        $bank_card_info = $bank_card_model->where(["uid"=>$u_id,"success"=>1,"id"=>$id])->find();
        if($bank_card_info){
            $bank_card_arr["bill"] = $bank_card_info["bill"];
            $bank_card_arr["repayment"] = $bank_card_info["repayment"];
            $json["status"] = 200;
            $json["info"] = "成功";
            $json["data"] = $bank_card_arr;
            $this->returnJson($json);
        }
        $json["status"] = 306;
        $json["info"] = "没有数据";
        $this->returnJson($json);
    }

    //添加计划
    public function planSubmit(){
        $c_id = I("c_id",1);  //通道id
        $b_id = I("b_id"); //银行卡id
        $u_id = $this->user_info["id"];
        $amount = I("amount"); //金额
        $periods = I("periods"); //期数(6,12,24)
        $nums = (int)I("nums",0);//每天执行次数
        $session_name = "plan_submit_".$u_id;
        if(session($session_name)){
            $json["status"] = 305;
            $json["info"] = "正在提交...";
            $this->returnJson($json);
        }
        session($session_name,1);
        if(!$c_id||!$b_id||!$u_id||!$amount||!$periods||$b_id<1){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->returnJson($json,$session_name);
        }
        $channel_model = M("channel");
        $plan_model = M("plan");
        $plan_des_model = M("plan_des");
        if($periods==6&&$amount<1500){
            $json["status"] = 306;
            $json["info"] = "选择6期，还款总额不能小于1500";
            $this->returnJson($json,$session_name);
        }
        if($periods==12&&$amount<3000){
            $json["status"] = 306;
            $json["info"] = "选择12期，还款总额不能小于3000";
            $this->returnJson($json,$session_name);
        }
        if($periods==18&&$amount<4500){
            $json["status"] = 306;
            $json["info"] = "选择18期，还款总额不能小于4500";
            $this->returnJson($json,$session_name);
        }
        if($periods==24&&$amount<6000){
            $json["status"] = 306;
            $json["info"] = "选择24期，还款总额不能小于6000";
            $this->returnJson($json,$session_name);
        }
        $p_amount =roundResolve($amount,$periods); //每期扣款额度
        // $p_amount = round($amount/$periods, 2); //每期扣款额度
        // if($p_amount<200){
        //     $json["status"] = 306;
        //     $json["info"] = "每期扣款额度不能低于200";
        //     $this->returnJson($json,$session_name);
        // }
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
        $plan_info = $plan_model->where("`bc_id`={$b_id} AND c_code='".$channel_info["code"]."' AND (`status`=3 OR `status`=4 OR `status`=5)")->find();
        if($plan_info){
            $json["status"] = 323;
            $json["info"] = "此银行卡有正在执行计划或待执行计划";
            $this->returnJson($json,$session_name);
        }
        $bill = date("Y-m")."-".$bank_card_hlb_info["bill"];  //账单日
        $repayment = date("Y-m")."-".$bank_card_hlb_info["repayment"]; //还款日
        $d = date("Y-m-d", time());
        if(strtotime($d)< strtotime($bill)&&strtotime($d)<strtotime($repayment)&&$bank_card_hlb_info["bill"]>$bank_card_hlb_info["repayment"]){
            $bill = date("Y-m",strtotime("-1 month"))."-".$bank_card_hlb_info["bill"]; //账单日
        }
        if(strtotime($bill)>strtotime($repayment)){
            $repayment = date("Y-m",strtotime("+1 month"))."-".$bank_card_hlb_info["repayment"]; //还款日
        }
        if(strtotime($d)<= strtotime($bill)){
            $json["status"] = 308;
            $json["info"] = "请在账单日后制定计划";
            $this->returnJson($json,$session_name);
        }
        if(strtotime($d)>= strtotime($repayment)){
            $json["status"] = 308;
            $json["info"] = "请在还款日前制定计划";
            $this->returnJson($json,$session_name);
        }
        $nums=2;
        $reserved_days = 3; //预留天数
        $p_d = $periods/$nums+$reserved_days;
        $date_1 = date("Y-m-d");
        $date_2 = $repayment;
        $d1 = strtotime($date_1);
        $d2 = strtotime($date_2);
        $days = round(($d2-$d1)/3600/24); //计算距离还款日天数
        if($days<=$p_d){
            $nums=4;
            $p_d = $periods/$nums+$reserved_days;
        }
        if($days<=$p_d){
            $json["status"] = 308;
            $json["info"] = "选择{$periods}期，距离还款日必须大于{$p_d}天";
            $this->returnJson($json,$session_name);
        }
        $fee = $channel_info["user_fee"]; //普通用户交易费率
        $close_rate = $channel_info["user_close_rate"];   //普通用户结算费用（每笔）
        $is_plus = 0;
        $user_m = M("user");
        $user_des = $user_m->where(["u_id"=>$u_id])->find();
        //判断是否plus会员
        if($user_des && $user_des['is_vip']){
            $is_plus = 1;
            $fee = $channel_info["plus_user_fee"]; //plus用户交费率
            $close_rate = $channel_info["plus_user_close_rate"];   //plus用户结算费用（每笔）
        }
        // $p_fee = round($p_amount*$fee+$close_rate,2); //每期手续费

        $plan_data = array(
            "u_id" => $u_id,
            "c_id" => $c_id,
            "c_code" => $channel_info["code"],
            "bc_id" => $b_id,
            "amount" => $amount,
            "periods" => $periods,
            "p_amount" => 0,
            "p_fee" => 0,
            "fee" => $fee,
            "close_rate" => $close_rate,
            "add_time" => date("Y-m-d H:i:s")
        );
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
        if($nums){
            if($nums<$num){
                $json["status"] = 321;
                $json["info"] = "距离还款日较短，不能选择每天执行一次";
                $this->returnJson($json,$session_name);
            }
            $num = $nums;
        }
        M()->startTrans();
        $plan_id = $plan_model->add($plan_data);
        if(!$plan_id){
            $json["status"] = 309;
            $json["info"] = "生成计划失败";
            $this->returnJson($json,$session_name);
        }
        $plan_des_arr = $this->getPlanDes($plan_id, $u_id, $p_amount, $fee, $close_rate, $is_include, $periods, $num);
        if($plan_des_arr&&!empty($plan_des_arr)){
            $return_status = $plan_des_model->addAll($plan_des_arr);
            if($return_status){
                M()->commit();
                //赠送上级会员
                $user_m = D('User');
                $db_config = C("DB_CONFIG2");
                $cunstomer_wx_binding_m = M("cunstomer_wx_binding",$db_config["DB_PREFIX"],$db_config);
                $user_wx_binding = $cunstomer_wx_binding_m->where(["user_id"=>$this->user_info["agentsid"],"state"=>1])->find();
                add_log("plan.log", "plan", "上级用户微信绑定数据：". var_export($user_wx_binding, true));
                if($user_wx_binding&&!empty($user_wx_binding)){
                    $user_vip_log_m = M("user_vip_log");
                    $user_vip_log_info = $user_vip_log_m->where(["u_id"=>$user_wx_binding["user_id"],"type"=>3,"friend_uid"=>$u_id])->find();
                    add_log("plan.log", "plan", "赠送vip信息：". var_export($user_vip_log_info, true));
                    if(!$user_vip_log_info&&empty($user_vip_log_info)){
                        $user_vip_log_data["u_id"] = $user_wx_binding["user_id"];
                        $user_vip_log_data["type"] = 3;
                        $user_vip_log_data["friend_uid"] = $u_id;
                        $user_vip_log_data["add_time"] = time();
                        $user_vip_log_data["end_time"] = strtotime("+1 month");
                        add_log("plan.log", "plan", "赠送vip信息数据：". var_export($user_vip_log_data, true));
                        $s=$user_vip_log_m->add($user_vip_log_data);
                        add_log("plan.log", "plan", "赠送vip信息状态：". var_export($s, true));
                        if($s){
                            $user_m->wxMessagewxYwlcMsg($user_wx_binding["user_id"],'恭喜您获得《会还款》一个月PLUS会员','下级用户制定《会还款》计划成功赠送',date("Y-m-d H:i:s"),'请尽快领取','点击领取','',HTTP_HOST.'/index/user/plusdes.html',$user_wx_binding["open_id"]);
                        }
                    }

                    //520活动
                    // $created = strtotime($this->user_info["created"]);
                    // $s_time = strtotime('2019-05-18');
                    // $e_time = strtotime('2019-05-23');
                    // $this_time = time();
                    // if($this_time>=$s_time&&$this_time<$e_time&&$created>=$s_time&&$created<$e_time){
                    //     $plan_des_info = $plan_des_model->where(["p_id"=>$plan_id])->order('s_time desc')->find();
                    //     $user_m->wxMessagewxYwlcMsg($user_wx_binding["user_id"],'恭喜您获得520活动奖励资格','下级用户制定《会还款》计划成功',date("Y-m-d H:i:s"),'累计计划额度达标即可获得奖励','请在'.date('Y-m-d H:i:s',$plan_des_info["s_time"]).'计划完成后联系客服领取','点击查看活动介绍','https://mp.weixin.qq.com/s/NN5sX_NjVeFmUKzMMFGUAQ',$user_wx_binding["open_id"]);
                    // }
                }

                $json["status"] = 200;
                $json["info"] = "生成计划成功";
                $this->returnJson($json,$session_name);
            }
            add_log("plan.log", "plan", "计划详情：". var_export($plan_des_arr, true));
            M()->rollback();
            $json["status"] = 400;
            $json["info"] = "插入计划详情失败";
            $this->returnJson($json,$session_name);
        }else{
            M()->rollback();
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
    private function getPlanDes($p_id,$uid,$p_amount, $fee, $close_rate,$type,$periods,$num){
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
                if(strtotime($begintime)<time()){
                    $begintime = date("Y-m-d H:i:s");
                }
                $endtime = $date." 11:30:00";
                $k_time = randomDate($begintime,$endtime); //随机生成执行时间
                //代扣
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "K".get_rand_str(6,$letters).$uid. time(),
                    "amount" => round(($p_amount[$i+1]+$close_rate)/($close_rate-$fee),2),
                    "s_time" => strtotime($k_time),
                    "type" => 1,
                    "days" => $date,
                    "fee" => $fee,
                    "close_rate" => $close_rate,
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
                    "amount" => $p_amount[$i+1],
                    "s_time" => strtotime($h_time),
                    "type" => 2,
                    "days" => $date,
                    "fee" => $fee,
                    "close_rate" => $close_rate,
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
            for($i=0;$i<($periods/$num);$i++){
                $begintime = $date." 08:10:00";
                if(strtotime($begintime)<time()){
                    $begintime = date("Y-m-d H:i:s");
                }
                $endtime = $date." 10:00:00";
                $k1_time = randomDate($begintime,$endtime); //随机生成执行时间
                $j = $i*2+1;
                //代扣
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "K".get_rand_str(6,$letters).$uid. time(),
                    "amount" => round(($p_amount[$j]+$close_rate)/($close_rate-$fee),2),
                    "s_time" => strtotime($k1_time),
                    "type" => 1,
                    "days" => $date,
                    "fee" => $fee,
                    "close_rate" => $close_rate,
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
                    "amount" => $p_amount[$j],
                    "s_time" => strtotime($h1_time),
                    "type" => 2,
                    "days" => $date,
                    "fee" => $fee,
                    "close_rate" => $close_rate,
                );

                $begintime = $date." 14:00:00";
                $endtime = $date." 16:00:00";
                $k2_time = randomDate($begintime,$endtime); //随机生成执行时间
                $a ++;
                $p_fee = round($p_amount[$j+1]*$fee+$close_rate,2); //每期手续费
                //代扣
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "K".get_rand_str(6,$letters).$uid. time(),
                    "amount" => round(($p_amount[$j+1]+$close_rate)/($close_rate-$fee),2),
                    "s_time" => strtotime($k2_time),
                    "type" => 1,
                    "days" => $date,
                    "fee" => $fee,
                    "close_rate" => $close_rate,
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
                    "amount" => $p_amount[$j+1],
                    "s_time" => strtotime($h2_time),
                    "type" => 2,
                    "days" => $date,
                    "fee" => $fee,
                    "close_rate" => $close_rate,
                );
                $a ++;
                $date = date("Y-m-d",strtotime("+1 day",strtotime($date)));
            }
        }
        //每天执行两次
        if($num==4){
            if($type==2){
                $date = date("Y-m-d",strtotime("+1 day"));
            }
            $a = 1;
            for($i=0;$i<($periods/$num);$i++){
                $begintime = $date." 07:10:00";
                if(strtotime($begintime)<time()){
                    $begintime = date("Y-m-d H:i:s");
                }
                $endtime = $date." 08:00:00";
                $k1_time = randomDate($begintime,$endtime); //随机生成执行时间
                $j = $i*$num+1;
                //代扣
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "K".get_rand_str(6,$letters).$uid. time(),
                    "amount" => round(($p_amount[$j]+$close_rate)/($close_rate-$fee),2),
                    "s_time" => strtotime($k1_time),
                    "type" => 1,
                    "days" => $date,
                    "fee" => $fee,
                    "close_rate" => $close_rate,
                );

                $begintime = $date." 08:30:00";
                $endtime = $date." 09:30:00";
                $h1_time = randomDate($begintime,$endtime); //随机生成执行时间
                $a ++;
                //代还
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "H".get_rand_str(6,$letters).$uid. time(),
                    "amount" => $p_amount[$j],
                    "s_time" => strtotime($h1_time),
                    "type" => 2,
                    "days" => $date,
                    "fee" => $fee,
                    "close_rate" => $close_rate,
                );

                $begintime = $date." 10:00:00";
                $endtime = $date." 11:00:00";
                $k2_time = randomDate($begintime,$endtime); //随机生成执行时间
                $a ++;
                $p_fee = round($p_amount[$j+1]*$fee+$close_rate,2); //每期手续费
                //代扣
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "K".get_rand_str(6,$letters).$uid. time(),
                    "amount" => round(($p_amount[$j+1]+$close_rate)/($close_rate-$fee),2),
                    "s_time" => strtotime($k2_time),
                    "type" => 1,
                    "days" => $date,
                    "fee" => $fee,
                    "close_rate" => $close_rate,
                );

                $begintime = $date." 11:30:00";
                $endtime = $date." 12:30:00";
                $h2_time = randomDate($begintime,$endtime); //随机生成执行时间
                $a ++;
                //代还
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "H".get_rand_str(6,$letters).$uid. time(),
                    "amount" => $p_amount[$j+1],
                    "s_time" => strtotime($h2_time),
                    "type" => 2,
                    "days" => $date,
                    "fee" => $fee,
                    "close_rate" => $close_rate,
                );

                $begintime = $date." 13:00:00";
                $endtime = $date." 14:00:00";
                $k2_time = randomDate($begintime,$endtime); //随机生成执行时间
                $a ++;
                $p_fee = round($p_amount[$j+1]*$fee+$close_rate,2); //每期手续费
                //代扣
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "K".get_rand_str(6,$letters).$uid. time(),
                    "amount" => round(($p_amount[$j+2]+$close_rate)/($close_rate-$fee),2),
                    "s_time" => strtotime($k2_time),
                    "type" => 1,
                    "days" => $date,
                    "fee" => $fee,
                    "close_rate" => $close_rate,
                );

                $begintime = $date." 14:30:00";
                $endtime = $date." 15:30:00";
                $h2_time = randomDate($begintime,$endtime); //随机生成执行时间
                $a ++;
                //代还
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "H".get_rand_str(6,$letters).$uid. time(),
                    "amount" => $p_amount[$j+2],
                    "s_time" => strtotime($h2_time),
                    "type" => 2,
                    "days" => $date,
                    "fee" => $fee,
                    "close_rate" => $close_rate,
                );

                $begintime = $date." 16:00:00";
                $endtime = $date." 17:00:00";
                $k2_time = randomDate($begintime,$endtime); //随机生成执行时间
                $a ++;
                $p_fee = round($p_amount[$j+1]*$fee+$close_rate,2); //每期手续费
                //代扣
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "K".get_rand_str(6,$letters).$uid. time(),
                    "amount" => round(($p_amount[$j+3]+$close_rate)/($close_rate-$fee),2),
                    "s_time" => strtotime($k2_time),
                    "type" => 1,
                    "days" => $date,
                    "fee" => $fee,
                    "close_rate" => $close_rate,
                );

                $begintime = $date." 17:30:00";
                $endtime = $date." 19:00:00";
                $h2_time = randomDate($begintime,$endtime); //随机生成执行时间
                $a ++;
                //代还
                $plan_des_arr [] = array(
                    "num" => $a,
                    "u_id" => $uid,
                    "p_id" => $p_id,
                    "order_id" => "H".get_rand_str(6,$letters).$uid. time(),
                    "amount" => $p_amount[$j+3],
                    "s_time" => strtotime($h2_time),
                    "type" => 2,
                    "days" => $date,
                    "fee" => $fee,
                    "close_rate" => $close_rate,
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
        $bank_card_hlb_info = $bank_card_hlb_model->where(["id"=>$plan_info["bc_id"],"success"=>1])->find(); //查询银行卡信息
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
            $this->replacementOrder($plan_info, $plan_des_info,$bank_card_hlb_info,$session_name); //通道补单
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
            $this->replacementOrder($plan_info, $plan_des_info,$bank_card_hlb_info,$session_name); //通道补单
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
                $this->replacementOrder($plan_info, $plan_des_info,$bank_card_hlb_info,$session_name); //通道补单
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
     * @param type $bank_card_hlb_info //银行卡信息
     */
    private function replacementOrder($plan_info,$plan_des_info,$bank_card_hlb_info,$session_name=''){
        $json["status"] = 311;
        $json["info"] = "补单失败";
        if(!$plan_info||empty($plan_info)||!$plan_des_info||empty($plan_des_info)||!$bank_card_hlb_info||empty($bank_card_hlb_info)){
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
            $user_m = D("User");
            $user_m->updPlanFee($plan_info,$plan_des_info);  //判断会员是否到期，更新未执行的计划
            $plan_des_info = $plan_des_model->where(["id"=>$plan_des_info["id"]])->find();
            //执行代扣
            switch ($plan_info["c_code"]) {
                case "hlb":
                    require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/helipay/HeliPay.php";
                    $heli_pay = new Heli();
                    $arg = array(
                        'bindId'=>$bank_card_hlb_info['bind_id'],
                        'userId'=>$plan_info['u_id'],
                        'orderId'=>$remedy_id,
                        'orderAmount'=>$plan_des_info["amount"],
                        'terminalType'=>'IMEI',
                        'terminalId'=>'122121212121',
                        'queryUrl'=>HTTP_HOST."/index/callback/hlbPay",
                        'Code'=>'',
                    );
                    $hlb_dh = $heli_pay->bindingCardPay($arg);//执行代扣
                    if(!$hlb_dh){
                        $upd_plan_des_data["message"] = "补单失败";
                        $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                        $this->sendWxErrorMessage($plan_info, "消费补单失败", "消费");
                    }else{
                        if ($hlb_dh['rt2_retCode'] == '0000') {
                            $upd_plan_des_data["message"] = "提交成功,等待回调通知";
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            // $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>1]);
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
                            $this->sendWxErrorMessage($plan_info, $hlb_dh['rt3_retMsg'], "消费");
                            $json["status"] = 311;
                            $json["info"] = $hlb_dh['rt3_retMsg'];
                        }
                    }
                    break;
                case "gyf":
                    require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/gyfpay/gyfpay.php";
                    $param=[
                        'merch_id' => $bank_card_hlb_info['merch_id'],//子商户号
                        'order_id' => $remedy_id,//订单号
                        'name'=> $bank_card_hlb_info['user_name'],//法人姓名
                        'phone'=> $bank_card_hlb_info['phone'],//法人电话
                        'id_card'=> $bank_card_hlb_info['id_card'],//身份证号
                        'card_id'=> $bank_card_hlb_info['card_no'],//交易卡号
                        'notify_url'=> HTTP_HOST."/index/gyfCallback/receive",//异步通知地址
                        'amount'=> $plan_des_info["amount"]*100,//交易金额
                        'cvv'=> $bank_card_hlb_info['card_cvv'],//安全码
                        'exp_date'=> $bank_card_hlb_info['validity_date'],//有效期
                        'device_id'=> create_guid(),//设备id
                        'ip_addr'=>getIP(),//公网IP地址（若不填大额交易限额会被风控）（付款客户端IP）
                    ];
                    $gyf_dh = gyf::pay($param);//执行代扣
                    if(isset($gyf_dh['status']) && $gyf_dh['status'] == 1){
                        if($gyf_dh['ret_data']['data']['orderStatus']=='02'){
                            $upd_plan_des_data["message"] = "提交成功,等待回调通知";
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            // $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>1]);
                            $json["status"] = 200;
                            $json["info"] = "提交成功,等待回调通知";
                        }elseif($gyf_dh['ret_data']['data']['orderStatus']=='03'){
                            $upd_plan_des_data["message"] = "补单失败,".$gyf_dh['ret_data']['data']['respDesc'];
                            $json["info"] = $upd_plan_des_data["message"];
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            $this->sendWxErrorMessage($plan_info, "消费补单失败", "消费");
                        }else{                            
                            $upd_plan_des_data["message"] = "订单处理中";
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            // $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>1]);
                            $json["status"] = 200;
                            $json["info"] = "订单处理中";
                        }
                    }elseif(isset($gyf_dh['status']) && $gyf_dh['status'] == 0){
                        if($gyf_dh['ret_data']['code']=='0100'){
                            $upd_plan_des_data["message"] = "订单处理中";
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            // $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>1]);
                            $json["status"] = 200;
                            $json["info"] = "订单处理中";
                        }else{
                            $upd_plan_des_data["message"] = "补单失败,".$gyf_dh['msg'];
                            $json["info"] = $upd_plan_des_data["message"];
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            $this->sendWxErrorMessage($plan_info, "消费补单失败", "消费");
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
                    require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/helipay/HeliPay.php";
                    $heli_pay = new Heli();
                    $arg = array(
                        'userId' => $plan_info['u_id'],
                        'bindId' => $bank_card_hlb_info['bind_id'],
                        'order_id' => $remedy_id,
                        'amount' => $plan_des_info["amount"],
                    );
                    $hlb_dh = $heli_pay->creditWithdraw($arg);//执行代还
                    if(!$hlb_dh){
                        $upd_plan_des_data["message"] = "补单失败";
                        $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                        $this->sendWxErrorMessage($plan_info, "还款补单失败", "还款");
                    }else{
                        if ($hlb_dh['rt2_retCode'] == '0000') {
                            $upd_plan_des_data["message"] = "补单成功";
                            $upd_plan_des_data["order_state"] = 1;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            $plan_status = 3;
                            if((int)($plan_info["periods"]*2)==$upd_plan_des_data["num"]){
                                $plan_status = 1;
                            }
                            $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>$plan_status]);
                            $this->sendWxMessage($plan_info, $plan_des_info);
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
                            $this->sendWxErrorMessage($plan_info, $hlb_dh['rt3_retMsg'], "还款");
                            $json["status"] = 311;
                            $json["info"] = $hlb_dh['rt3_retMsg'];
                        }
                    }
                    break;
                case "gyf":
                    require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/gyfpay/gyfpay.php";
                    $param=[
                        'merch_id' => $bank_card_hlb_info['merch_id'],//子商户号
                        'order_id' => $remedy_id,//订单号
                        'name'=> $bank_card_hlb_info['user_name'],//法人姓名
                        'phone'=> $bank_card_hlb_info['phone'],//法人电话
                        'id_card'=> $bank_card_hlb_info['id_card'],//身份证号
                        'card_id'=> $bank_card_hlb_info['card_no'],//结算卡号
                        'notify_url'=> HTTP_HOST."/index/gyfCallback/close",//异步通知地址
                        'amount'=> $plan_des_info["amount"]*100,//交易金额
                    ];
                    $gyf_dh = gyf::withdraw($param);//执行代还
                    if(isset($gyf_dh['status']) && $gyf_dh['status'] == 1){
                        if($gyf_dh['ret_data']['data']['orderStatus']=='02'){
                            $upd_plan_des_data["message"] = "提交成功,等待回调通知";
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            // $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>1]);
                            $json["status"] = 200;
                            $json["info"] = "提交成功,等待回调通知";
                        }elseif($gyf_dh['ret_data']['data']['orderStatus']=='03'){
                            $upd_plan_des_data["message"] = "补单失败,".$gyf_dh['ret_data']['data']['respDesc'];
                            $json["info"] = $upd_plan_des_data["message"];
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            $this->sendWxErrorMessage($plan_info, "还款补单失败", "还款");
                        }else{                            
                            $upd_plan_des_data["message"] = "订单处理中";
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            // $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>1]);
                            $json["status"] = 200;
                            $json["info"] = "订单处理中";
                        }
                    }elseif(isset($gyf_dh['status']) && $gyf_dh['status'] == 0){
                        if($gyf_dh['ret_data']['code']=='0100'){
                            $upd_plan_des_data["message"] = "订单处理中";
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            // $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>1]);
                            $json["status"] = 200;
                            $json["info"] = "订单处理中";
                        }else{
                            $upd_plan_des_data["message"] = "补单失败,".$gyf_dh['msg'];
                            $json["info"] = $upd_plan_des_data["message"];
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            $this->sendWxErrorMessage($plan_info, "还款补单失败", "还款");
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
            require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            $msg_data["touser"] = $cunstomer_wx_binding_info["open_id"];
            $msg_data["template_id"] = "_nQ9Iqu1cT6z2aiHV2vvL366b3Qr4nFpfsU7GQ1cg4U";
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
            add_log("callback_helipay.log", "callback", "计划失败公众号消息推送状态：". var_export($return_status, true));
        }
    }
}
