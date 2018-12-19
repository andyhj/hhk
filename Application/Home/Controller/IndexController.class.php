<?php
namespace Home\Controller;
use Think\Controller;
use Common\Common\Sockets;
use Common\Common\WechatMsg;
use Common\WxApi\class_weixin_adv;
use Common\Common\Redis;
use Common\Common\WxH5Login;
class IndexController extends InitController {
    public function __construct() {
        header("Content-type: text/html; charset=utf-8"); 
        parent::__construct();
//        $user_info = session("userInfo");
//        if(!$user_info){
//            $this->error("请登录!", U("/index/user/index"));
//        }
    }
    
    public function index(){
        if(MT){
            $this->redirect('index/index/maintain',[], 1, '页面跳转中...');
        }
        $plan_model = M("plan");
        $plan_des_model = M("plan_des");
        $u_id = 464885;
        $c_id = 1;
        $b_id = 1;
        $amount = 20000;
        $periods = 12;
        $p_amount = 1666.67;
        $p_fee = 11.83;
        $fee = 0.65;
        $close_rate = 1;
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
        $d1 = strtotime("2018-12-28");
	$d2 = strtotime("2018-12-29");
	$days = round(($d2-$d1)/3600/24); //计算距离还款日天数
        $d = 3;
        echo strtotime("+$d day",1545204455);
//        $plan_id = $plan_model->add($plan_data);
//        $a = $this->getPlanDes($plan_id, $u_id, $p_amount, $p_fee, 2, $periods, 2);
//        $return_status = $plan_des_model->addAll($a);
//        print_r("<pre>");
//        print_r($a);
//        print_r("<pre>");
//        print_r($return_status);
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
    
    public function logout(){
        session(null);
        $this->redirect("/index/index");
    }
    /**
     * 代付回调
     */
    public function daifuCallback(){
        $post_data = file_get_contents("php://input"); 
        $postarr = json_decode($post_data, true);
        add_log("daifu_callback.log", "commission", "佣金提现回调通知数据". var_export($postarr, true));
        $data = $_POST;
        add_log("daifu_callback.log", "commission", "佣金提现回调通知数据". var_export($data, true));
    }
    
    public function wxmsg(){
        define("TOKEN", "TGdfxHKsdavfWSgesDdc");
        $wechatObj = new WechatMsg();
             add_log("wx_msg.log", "wxmsg", "进入：". var_export($_GET,true));
        if (isset($_GET['echostr'])) {
            $wechatObj->valid();
        }else{
            $wechatObj->responseMsg();
        }
    }
    /**
     * 系统维护
     */
    public function maintain(){
        $this->display();
    }
    public function app(){
        $type="Android";
        $url=HTTP_HOST."/wd/download/kxdqp_v1.1.8.apk?v=".get_rand_str(10);
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
            $type="IOS";
            $url="";
        }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
            
        }else{
            
        }
        $this->assign('type', $type);
        $this->assign('url', $url);
        $this->display();
    }


    public function test(){
//        $m_redis = new Redis();
//        $data["robotmaxlosecoin"] = '-2000000';
//        $data["robotmaxwincoin"] = '1000000';
//        print_r($m_redis->hgetall("global.data"));
//        $m_redis->hmset("global.data", $data);
//        print_r($m_redis->hgetall("global.data"));
//        print_r($this->draw(2, 3.5));die();
        $d1 = $d2 = $d3 = $d4 = $d5 = $d6 = 0;
        $j = 0;
        $am = 0;
        for($i=0;$i<100;$i++){
            $j +=3.5;
            $data = $this->draw(2, $j);
//            print_r($data);
            $am += $data["amount"];
            $j -= $data["amount"];
            if($data["id"]==1){
                $d1 ++;
            }
            if($data["id"]==2){
                $d2 ++;
            }
            if($data["id"]==3){
                $d3 ++;
            }
            if($data["id"]==4){
                $d4 ++;
            }
            if($data["id"]==5){
                $d5 ++;
            }
            if($data["id"]==6){
                $d6 ++;
            }
        }
        echo "一等奖：".$d1."<br>";
        echo "二等奖：".$d2."<br>";
        echo "三等奖：".$d3."<br>";
        echo "四等奖：".$d4."<br>";
        echo "五等奖：".$d5."<br>";
        echo "不中奖：".$d6."<br>";
        echo "奖池金额：".$j."<br>";
        echo "中奖总金额：".$am."<br>";
    }
    /**
     * 抽奖
     * @param type $type  类型 1，分享；2，注册
     * @param type $amount  奖金池剩余金额
     */
    public function draw($type,$amount){
        if(!$type||$amount<=0){
            return false;
        }
        if($type==1){
            $prize_arr = array( 
                '0' => array('id' => 1, 'title' => '一等奖','amount'=>18, 'v' => 1), 
                '1' => array('id' => 2, 'title' => '二等奖','amount'=>8, 'v' => 1), 
                '2' => array('id' => 3, 'title' => '三等奖','amount'=>5, 'v' => 15), 
                '3' => array('id' => 4, 'title' => '四等奖','amount'=>3, 'v' => 30), 
                '4' => array('id' => 5, 'title' => '五等奖','amount'=>1, 'v' => 47), 
                '5' => array('id' => 6, 'title' => '不中奖','amount'=>0, 'v' => 6)
            ); 
        }elseif($type==2){
            if($amount<1){
                return false;
            }
            $prize_arr = array( 
                '0' => array('id' => 1, 'title' => '一等奖','amount'=>18, 'v' => 1), 
                '1' => array('id' => 2, 'title' => '二等奖','amount'=>8, 'v' => 1), 
                '2' => array('id' => 3, 'title' => '三等奖','amount'=>5, 'v' => 48), 
                '3' => array('id' => 4, 'title' => '四等奖','amount'=>3, 'v' => 40), 
                '4' => array('id' => 5, 'title' => '五等奖','amount'=>1, 'v' => 10)
            ); 
        }else{
            return false;
        }
        //计算动态概率 start
        if($amount<$prize_arr[0]["amount"]&&$amount>=$prize_arr[1]["amount"]){
            $z_dw = $prize_arr[1]["v"]+$prize_arr[2]["v"]+$prize_arr[3]["v"]+$prize_arr[4]["v"];
            if($type==1){
                $z_dw += $prize_arr[5]["v"];
            }
            $prize_arr[0]["v"] = 0;
            $v1 = floor($prize_arr[1]["v"]/$z_dw*10000); 
            $v2 = floor($prize_arr[2]["v"]/$z_dw*10000); 
            $v3 = floor($prize_arr[3]["v"]/$z_dw*10000); 
            $v4 = floor($prize_arr[4]["v"]/$z_dw*10000); 
            if($type==1){
                $v5 = floor($prize_arr[5]["v"]/$z_dw*10000); 
                $prize_arr[5]["v"] = $v5; 
            }
            $prize_arr[1]["v"] = $v1;
            $prize_arr[2]["v"] = $v2; 
            $prize_arr[3]["v"] = $v3; 
            $prize_arr[4]["v"] = $v4; 
            
        }
        if($amount<$prize_arr[1]["amount"]&&$amount>=$prize_arr[2]["amount"]){
            $z_dw = $prize_arr[2]["v"]+$prize_arr[3]["v"]+$prize_arr[4]["v"];
            if($type==1){
                $z_dw += $prize_arr[5]["v"];
            }
            $prize_arr[0]["v"] = 0;
            $prize_arr[1]["v"] = 0; 
            $v2 = floor($prize_arr[2]["v"]/$z_dw*10000); 
            $v3 = floor($prize_arr[3]["v"]/$z_dw*10000); 
            $v4 = floor($prize_arr[4]["v"]/$z_dw*10000); 
            if($type==1){
                $v5 = floor($prize_arr[5]["v"]/$z_dw*10000); 
                $prize_arr[5]["v"] = $v5; 
            }
            $prize_arr[2]["v"] = $v2; 
            $prize_arr[3]["v"] = $v3; 
            $prize_arr[4]["v"] = $v4; 
        }
        if($amount<$prize_arr[2]["amount"]&&$amount>=$prize_arr[3]["amount"]){
            $z_dw = $prize_arr[3]["v"]+$prize_arr[4]["v"];
            if($type==1){
                $z_dw += $prize_arr[5]["v"];
            }
            $prize_arr[0]["v"] = 0;
            $prize_arr[1]["v"] = 0;
            $prize_arr[2]["v"] = 0; 
            $v3 = floor($prize_arr[3]["v"]/$z_dw*10000); 
            $v4 = floor($prize_arr[4]["v"]/$z_dw*10000); 
            if($type==1){
                $v5 = floor($prize_arr[5]["v"]/$z_dw*10000); 
                $prize_arr[5]["v"] = $v5; 
            }
            $prize_arr[3]["v"] = $v3; 
            $prize_arr[4]["v"] = $v4; 
        }
        if($amount<$prize_arr[3]["amount"]&&$amount>=$prize_arr[4]["amount"]){
            $z_dw = $prize_arr[4]["v"];
            if($type==1){
                $z_dw += $prize_arr[5]["v"];
            }
            $prize_arr[0]["v"] = 0; 
            $prize_arr[1]["v"] = 0;
            $prize_arr[2]["v"] = 0; 
            $prize_arr[3]["v"] = 0; 
            $v4 = floor($prize_arr[4]["v"]/$z_dw*10000); 
            if($type==1){
                $v5 = floor($prize_arr[5]["v"]/$z_dw*10000);
                $prize_arr[5]["v"] = $v5; 
            }
            $prize_arr[4]["v"] = $v4; 
        }
        if($type==1){
            if($amount<$prize_arr[4]["amount"]){
                $prize_arr[0]["v"] = 0;
                $prize_arr[1]["v"] = 0;
                $prize_arr[2]["v"] = 0; 
                $prize_arr[3]["v"] = 0; 
                $prize_arr[4]["v"] = 0; 
                $v5 = floor($prize_arr[5]["v"]/$prize_arr[5]["v"]*10000); 
                $prize_arr[5]["v"] = $v5; 
            }
        }
        //计算动态概率 end
        
        foreach ($prize_arr as $val) { 
            $arr[$val['id']] = $val['v']; 
        } 

        $prize_id = $this->getRand($arr); //根据概率获取奖品id 
        $dw = $prize_arr[$prize_id - 1];//返回中奖结果
        $drw['title'] = $dw['title'];
        $drw['amount'] = $dw['amount'];
        return $dw;
    }
    private function getRand($proArr) { //计算中奖概率 
        $rs = ''; //中奖结果 
        $proSum = array_sum($proArr);
        foreach ($proArr as $key => $proCur) { 
            $randNum = mt_rand(1, $proSum); 
            if ($randNum <= $proCur) { 
                $rs = $key; 
                break; 
            } else { 
                $proSum -= $proCur; 
            } 
        } 
        unset($proArr); 
        return $rs; 
    }

        //导出
    public function applyexport(){
        $cid = I("c_id",0);
        $uid = I("u_id",0);
        $url = $this->http.$_SERVER['HTTP_HOST'].'/index/custom/custom.html';
        if(!$cid||!$uid){
            echo '<script>alert("参数错误");location="'.$url.'";</script>';die();
        }
        $custom = D("Custom");
        $user = D("User");
        $where["u_id"] = $uid;
        $where["id"] = $cid;
        $custom_info = $custom->getOne($where);
        if(!$custom_info){
            echo '<script>alert("比赛不存在");location="'.$url.'";</script>';die();
        }
    	import("Org.Excel.Excel");
        $custom_apply = [];
        $ca_where["gameid"] = $cid;
        $custom_apply_list = $custom->getApplyList($ca_where);
        if($custom_apply_list){
            foreach ($custom_apply_list as $value) {
                $user_info = $user->getUserOne($value["uid"]);
                $mobile = $value["mobile"]?$value["mobile"]:$user_info["phone"];
                $app["nickname"] = $user_info["nickname"];
                $app["gender_name"] = "未知";
                if($user_info["gender"]==1){
                    $app["gender_name"] = "男";
                }
                if($user_info["gender"]==2){
                    $app["gender_name"] = "女";
                }
                $app["mobile"] = $mobile;
                $custom_apply[] = $app;
            }
        }else{
            die("没有数据");
        }
    	$row=array();
    	$row[0]=array('序号','用户昵称','用户性别','用户手机号');
    	$i=1;
    	foreach($custom_apply as $v){
            $row[$i]['i'] = $i;
            $row[$i]['nickname'] = $v['nickname'];
            $row[$i]['gender_name'] = $v['gender_name'];
            $row[$i]['mobile'] = $v['mobile'];
            $i++;
    	}
    	
    	$xls = new \Excel_XML('UTF-8', false, 'datalist');
    	$xls->addArray($row);
    	$xls->generateXML("user". date("Ymd"));
    }
}