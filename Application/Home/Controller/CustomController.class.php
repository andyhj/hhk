<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Controller;
use Common\WxApi\JsApiPay;
use Common\WxApi\class_weixin_adv;
use QRcode;
use Common\Common\Sockets;
use Common\Common\WxH5Login;
use Common\Common\Blend;
use Common\Common\Custom;
class CustomController extends InitController {
    private $user_info;
    
    public function __construct() {
        header("Content-type: text/html; charset=utf-8"); 
        parent::__construct();
        $recommend = delTrim(I("rec", session("rec"))); //推荐人
        $mode_type = session("modeType"); 
        $roomid = session("roomid"); 
        $n = delTrim(I("n"));
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
                if(!$n){
                    if($mode_type==3){
                        $url = $this->http . $_SERVER['HTTP_HOST'] . '/index/custom/code.html?id='.$roomid;
                    }
                    header('Location: ' . $url);
                    die();
                }
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
        $url = $this->http.$_SERVER['HTTP_HOST'].'/index/custom/custom.html';
        header('Location: ' . $url);
    }

    
    /**
     * 自定义比赛列表
     */
    public function custom(){
        $user_info = $this->user_info;
        $custom = D("Custom");
        $custom_id = I("custom_id",0);
        $where["u_id"] = $user_info["id"];
        if($custom_id){
            $where["id"] = $custom_id;
        }else{
            $where["is_del"] = 0;
        }
        $custom_info = $custom->getOne($where);
        $custom_user_info = $custom->getCustomUserOne($user_info["id"]);
        $custom_apply_count = $custom->getApplyCount(["gameid"=>$custom_info["id"]]);
        $custom_ranking_list = $custom->getCustomRanking(["c_id"=>$custom_info["id"]]);
        $is_exchange = 0;
        if($custom_ranking_list){
            foreach ($custom_ranking_list as $crl) {
                if($crl["prizes1_name"]&&$crl["prizes1_exchange"]!=2){
                    $is_exchange = 1;
                }
                if($crl["prizes2_name"]&&$crl["prizes2_exchange"]!=2){
                    $is_exchange = 1;
                }
                if($crl["prizes3_name"]&&$crl["prizes3_exchange"]!=2){
                    $is_exchange = 1;
                }
                if($crl["prizes4_name"]&&$crl["prizes4_exchange"]!=2){
                    $is_exchange = 1;
                }
                if($crl["prizes5_name"]&&$crl["prizes5_exchange"]!=2){
                    $is_exchange = 1;
                }
            }
        }
        if($custom_info["audit_status"]!=1){
            $is_exchange = 0;
        }
        if($custom_info["status"]==1){
            $is_exchange = 1;
        }
        if((time()-$custom_info["end_date"])>604800){
            $is_exchange = 0;
        }
        $wx_share_url = $this->http.$_SERVER['HTTP_HOST'].'/s/'. $user_info["id"] . '-' . $custom_info["id"] . '-0-' . $custom_info["game_id"] . '-3.html';
        $custom_info["game_type"] = $this->getGameType($custom_info["game_id"]);
        $this->assign('custom_info',$custom_info);
        $this->assign('cu_info',$custom_user_info);
        $this->assign('custom_apply_count',$custom_apply_count);
        $this->assign('userInfo',$user_info);
        $this->assign('is_exchange',$is_exchange);
        $this->assign('code',$this->http.$_SERVER['HTTP_HOST'].'/index/custom/code.html?id='.$custom_info["id"]);
        $this->assign('wx_share_url',$wx_share_url);
        $this->display();
    }
    /**
     * 删除比赛
     */
    public function delcustom(){
        $id = I("custom_id",0);
        $url = $this->http.$_SERVER['HTTP_HOST'].'/index/custom/cuslist.html';
        if(!$id){
            echo '<script>alert("参数错误");location="'.$url.'";</script>';die();
        }
        $user_info = $this->user_info;
        if(!$user_info){
             echo '<script>alert("参数错误");location="'.$url.'";</script>';die();
        }
        $custom = D("Custom");
        $where["u_id"] = $user_info["id"];
        $where["id"] = $id;
        $custom_info = $custom->getOne($where);
        if(!$custom_info){
            echo '<script>alert("比赛不存在");location="'.$url.'";</script>';die();
        }
        $customs = new Custom();
        $return_status = $customs->delcustom($id);
        if($return_status===11){
            echo '<script>alert("比赛不存在");location="'.$url.'";</script>';die();
        }
        if($return_status===20){
            echo '<script>alert("删除成功");location="'.$url.'";</script>';die();
        }
        if($return_status===13){
            echo '<script>alert("删除失败");location="'.$url.'";</script>';die();
        }
        if($return_status===14){
            echo '<script>alert("比赛进行中不能删除");location="'.$url.'";</script>';die();
        }
        if($return_status===15){
            echo '<script>alert("奖品发放中不能删除");location="'.$url.'";</script>';die();
        }
    }
    /**
     * 添加比赛
     */
    public function addcustom(){
        $user_info = $this->user_info;
        $custom = D("Custom");
        $host = $_SERVER['HTTP_HOST'];
        $url = $this->http.$host.'/index/custom/cuslist.html';
        $custom_id = I("custom_id",0);
        $data = [
            'name' => '',  //比赛名称
            'number' => '',  //比赛人数
            'type' => '',  //模式
            'inning' => 1,  //场次
            'custom_time' => '',  //比赛时长
            'prizes1_range' => '',
            'prizes1_name' => '',
            'prizes1_value' => '',
            'prizes2_range' => '',
            'prizes2_name' => '',
            'prizes2_value' => '',
            'prizes3_range' => '',
            'prizes3_name' => '',
            'prizes3_value' => '',
            'prizes4_range' => '',
            'prizes4_name' => '',
            'prizes4_value' => '',
            'prizes5_range' => '',
            'prizes5_name' => '',
            'prizes5_value' => '',
            'join_prizes_name' => '',
            'join_prizes_value' => '',
            'max_number' => '',   //最大名次
            'start_date' => '',  //比赛开始时间
            'error' => ''
        ];
        $cu_data = [
            'nickname' => $user_info["nickname"],   //创建者名称
            'mobile' => '',  //手机号码
            'phone' => '',    //固定电话
            'card_id' => '',   //身份证号码
            'bank_card' => '',  //银行卡号
            'card_name' => ''  //持卡人姓名
        ];
        $custom_user_info = $custom->getCustomUserOne($user_info["id"]);
        if($custom_id){
            $where["id"] = $custom_id;
            $where["is_del"] = 0;
            $custom_info = $custom->getOne($where);
            if($custom_info&&$custom_info["audit_status"]!=2){
                echo '<script>alert("非法访问");location="'.$url.'";</script>';die();
            }
            $data = [
                'name' => $custom_info["name"],  //比赛名称
                'game_id' => $custom_info["game_id"],  //游戏类型
                'number' => $custom_info["number"],  //比赛人数
                'type' => 2,  //模式
                'inning' => $custom_info["inning"],  //场次
                'custom_time' => $custom_info["custom_time"],  //比赛时长
                'prizes1_range' => $custom_info["prizes1_range"],
                'prizes1_name' => $custom_info["prizes1_name"],
                'prizes1_value' => $custom_info["prizes1_value"],
                'prizes2_range' => $custom_info["prizes2_range"],
                'prizes2_name' => $custom_info["prizes2_name"],
                'prizes2_value' => $custom_info["prizes2_value"],
                'prizes3_range' => $custom_info["prizes3_range"],
                'prizes3_name' => $custom_info["prizes3_name"],
                'prizes3_value' => $custom_info["prizes3_value"],
                'prizes4_range' => $custom_info["prizes4_range"],
                'prizes4_name' => $custom_info["prizes4_name"],
                'prizes4_value' => $custom_info["prizes4_value"],
                'prizes5_range' => $custom_info["prizes5_range"],
                'prizes5_name' => $custom_info["prizes5_name"],
                'prizes5_value' => $custom_info["prizes5_value"],
                'join_prizes_name' => $custom_info["join_prizes_name"],
                'join_prizes_value' => $custom_info["join_prizes_value"],
                'max_number' => $custom_info["max_number"],   //最大名次
                'start_date' => date("Y-m-d H:i:s",$custom_info["start_date"]),  //比赛开始时间
                'error' => ''
            ];
        }else{
//            $where["u_id"] = $user_info["id"];
//            $where["is_del"] = 0;
//            $custom_info = $custom->getOne($where);
//            if($custom_info){
//                echo '<script>alert("已经存在一个比赛，请先删除后再创建");location="'.$url.'";</script>';die();
//            }
        }
        
        $custom_user_id = 0;
        if($custom_user_info){
            $custom_user_id = $custom_user_info["id"];
            $cu_data = [
                'nickname' => $custom_user_info["nickname"],   //创建者名称
                'mobile' => $custom_user_info["mobile"],  //手机号码
                'phone' => $custom_user_info["phone"],    //固定电话
                'card_id' => $custom_user_info["card_id"],   //身份证号码
                'bank_card' => $custom_user_info["bank_card"],  //银行卡号
                'card_name' => $custom_user_info["card_name"]  //持卡人姓名
            ];
        }
        if(is_post()){
//            $game_id = 1;       //游戏id
            $max_number = 1;    //最大名次
            $data["name"] = I("name");      //名称
            $data["number"] = I("number");  //比赛人数
//            $data["tickets"] = I("tickets");  //门票
//            $data["welfare"] = I("welfare");  //推广福利
            $data["type"] = 2;  //模式
            $data["custom_time"] = I("custom_time");  //比赛时长
            $data["game_id"] = I("game_id");  //游戏类型
            $data["inning"] = I("inning");  //场次
            $data["prizes1_range"] = I("prizes1_name")?"1-1":"";    //奖品1范围
            $data["prizes1_name"] = I("prizes1_name");      //奖品1名称
            $data["prizes1_value"] = I("prizes1_value")?I("prizes1_value"):0.00;        //奖品1价值
            $data["prizes2_range"] = I("prizes2_name")?"2-2":"";    //奖品2范围
            $data["prizes2_name"] = I("prizes2_name");      //奖品2名称
            $data["prizes2_value"] = I("prizes2_value")?I("prizes2_value"):0.00;        //奖品2价值
            $data["prizes3_range"] = I("prizes3_name")?"3-3":"";    //奖品3范围
            $data["prizes3_name"] = I("prizes3_name");      //奖品3名称
            $data["prizes3_value"] = I("prizes3_value")?I("prizes3_value"):0.00;        //奖品3价值
            $data["prizes4_range"] = I("prizes4_name")?"4-4":"";    //奖品4范围
            $data["prizes4_name"] = I("prizes4_name");      //奖品4名称
            $data["prizes4_value"] = I("prizes4_value")?I("prizes4_value"):0.00;        //奖品4价值
            $data["prizes5_range"] = I("prizes5_name")?"5-5":"";    //奖品5范围
            $data["prizes5_name"] = I("prizes5_name");      //奖品5名称
            $data["prizes5_value"] = I("prizes5_value")?I("prizes5_value"):0.00;        //奖品5价值
            $data["join_prizes_name"] = I("join_prizes_name");      //参与奖名称
            $data["join_prizes_value"] = I("join_prizes_value")?I("join_prizes_value"):0.00;        //参与奖价值
            $data["join_prizes_number"] = I("join_prizes_number",0); 
            $data["start_date"] = I("start_date");  //比赛开始时间
            
            $cu_data["nickname"] = I("nickname");  //创建者名称
            $cu_data["mobile"] = I("mobile");  //手机号码
            $cu_data["phone"] = I("phone");  //固定电话
            $cu_data["card_id"] = I("card_id");  //身份证号码
            $cu_data["bank_card"] = I("bank_card");  //银行卡号
            $cu_data["card_name"] = I("card_name");  //持卡人姓名
            
            $word = C("WORD");
            if(!$data["name"]|| _strlen($data["name"])>32){
//                echo '<script>alert("比赛名称不能为空并且不能大于10个字");location="'.$url.'";</script>';
                $data["error"] = "比赛名称不能为空并且不能大于16个字";
            }elseif(in_array($data["name"],$word)){
                $data["error"] = "比赛名字有敏感字存在！请重新输入！";
            }elseif($data["type"]==1&&(!$data["number"]||!is_int(intval($data["number"]))||intval($data["number"])<6)){
                $data["error"] = "比赛人数大于等于6人";
            }elseif(!$data["inning"]||!is_int(intval($data["inning"]))||intval($data["inning"])>10){
                $data["error"] = "场次要大于1小于10次";
            }elseif($data["type"]==2&&(!$data["number"]||!is_int(intval($data["number"]))||intval($data["number"])<1)){
                $data["error"] = "比赛轮数不能小于1";
            }elseif(!$data["custom_time"]||!is_int(intval($data["custom_time"]))||intval($data["custom_time"])>120){
                $data["error"] = "比赛时长大于1小于120分钟";
            }elseif(intval($data["tickets"])>200){
                $data["error"] = "报名费不能大于200";
            }elseif(!$data["tickets"]&&$data["welfare"]){
                $data["error"] = "报名费为0的时候推广福利必须为0";
            }elseif($data["tickets"]&&($data["welfare"]>($data["tickets"]*0.9))){
                $data["error"] = "推广福利不能大于门票的90%价格";
            }elseif(!$data["start_date"]){
                $data["error"] = "开始时间不能为空";
            }elseif(strtotime($data["start_date"])<= time()){
                $data["error"] = "至少提前一日申请";
            }elseif(!$data["prizes1_name"]&&!$data["prizes2_name"]&&!$data["prizes3_name"]&&!$data["prizes4_name"]&&!$data["prizes5_name"]){
                $data["error"] = "必须设置一个奖品";
            }elseif(_strlen($data["prizes1_name"])>32||_strlen($data["prizes2_name"])>32||_strlen($data["prizes3_name"])>32||_strlen($data["prizes4_name"])>32||_strlen($data["prizes5_name"])>32||_strlen($data["join_prizes_name"])>32){
                $data["error"] = "奖品名称不能大于16个字";
            }elseif(in_array($data["prizes1_name"],$word)||in_array($data["prizes2_name"],$word)||in_array($data["prizes3_name"],$word)||in_array($data["prizes4_name"],$word)||in_array($data["prizes5_name"],$word)||in_array($data["join_prizes_name"],$word)){
                $data["error"] = "奖品名称有敏感字存在！请重新输入！";
            }else{
                if(intval($data["prizes2_range2"])>$max_number){
                    $max_number = intval($data["prizes2_range2"]);
                }
                if(intval($data["prizes3_range2"])>$max_number){
                    $max_number = intval($data["prizes3_range2"]);
                }
                if($data["prizes1_name"]){
                    $max_number = 1;
                }
                if($data["prizes2_name"]){
                    $max_number = 2;
                }
                if($data["prizes3_name"]){
                    $max_number = 3;
                }
                if($data["prizes4_name"]){
                    $max_number = 4;
                }
                if($data["prizes5_name"]){
                    $max_number = 5;
                }
                unset($data["error"]);
//                if($data["join_prizes_number"]==0&&$data["join_prizes_name"]){
//                    $max_number = $data["number"];
//                }
//                if($data["join_prizes_number"]>0&&$data["join_prizes_name"]){
//                    $max_number = $max_number+$data["join_prizes_number"];
//                }
                $data["u_id"] = $user_info["id"];
//                $data["game_id"] = $game_id;
                $data["max_number"] = $max_number;
                $data["start_date"] = strtotime($data["start_date"]);
                
                if($custom_id){
                    $data["audit_status"] = 0;
                    $return_status = $custom->updCustom(["id"=>$custom_id],$data);
                }else{
//                    print_r($data);die();
                    $where["u_id"] = $user_info["id"];
                    $where["is_del"] = 0;
                    $where["game_id"] = $data["game_id"];
                    $custom_info = $custom->getOne($where);
                    if($custom_info){
                        echo '<script>alert("已经存在一个同类型比赛，请先删除后再创建");location="'.$url.'";</script>';die();
                    }
                    $data["add_date"] = time();
                    $return_status = $custom->addCustom($data);
                }
                
                if($custom_user_id){
                    $custom->updCustomUser(["id"=>$custom_user_id],$cu_data);
                }else{
                    $cu_data["u_id"] = $user_info["id"];
                    $return_status = $custom->addCustomUser($cu_data);
                }
                if($return_status){
                    echo '<script>alert("提交成功");location="'.$url.'";</script>';die();
                }else{
                    $data["start_date"] = date("Y-m-d H:i:s",$data["start_date"]);
                    $data["error"] = "提交失败";
                }
            }
        }
        $this->assign('custom_id',$custom_id);
        $this->assign('data',$data);
        $this->assign('cu_data',$cu_data);
        $this->assign('userInfo',$user_info);
        $this->display();
    }
    /**
     * 我的奖券列表
     */
    public function vouchers(){
        $user_info = $this->user_info;
        $custom = D("Custom");
        $where["u_id"] = $user_info["id"];
        $custom_ranking = $custom->getCustomRanking($where);
        $custom_ranking1_arr = [];
        $custom_ranking2_arr = [];
        $custom_ranking3_arr = [];
        if($custom_ranking){
            foreach ($custom_ranking as $value) {
                if($value["prizes1_name"]||$value["prizes2_name"]||$value["prizes3_name"]||$value["prizes4_name"]||$value["prizes5_name"]){
                    $title = "";
                    if(($value["prizes1_name"]&&$value["prizes1_exchange"]!=2)||($value["prizes2_name"]&&$value["prizes2_exchange"]!=2)||($value["prizes3_name"]&&$value["prizes3_exchange"]!=2)||($value["prizes4_name"]&&$value["prizes4_exchange"]!=2)||($value["prizes5_name"]&&$value["prizes5_exchange"]!=2)){
                        if(($value["add_time"]+($value["period"]*60*60*24))<= time()){
                            $title = "已过期";
                            $value["title"] = $title;
                            $custom_ranking1_arr[] = $value;
                        }else{
                            $title = "已发放";
                            $value["title"] = $title;
                            $custom_ranking2_arr[] = $value;
                        }
                    }else{
                        $title = "已领取";
                        $value["title"] = $title;
                        $custom_ranking3_arr[] = $value;
                    }
                }
            }
        }
        $this->assign('userInfo',$user_info);
        $this->assign('custom_ranking1',$custom_ranking1_arr);
        $this->assign('custom_ranking2',$custom_ranking2_arr);
        $this->assign('custom_ranking3',$custom_ranking3_arr);
        $this->display();
    }
    /**
     * 奖券信息
     */
    public function rankinginfo(){
        $user_info = $this->user_info;
        $url = $this->http.$_SERVER['HTTP_HOST'].'/index/custom/custom.html';
        $id = I("id");
        if(!$id){
            echo '<script>alert("参数错误");location="'.$url.'";</script>';die();
        }
        $custom = D("Custom");
        $model_user = D("User");
        $where["id"] = $id;
        $where["u_id"] = $user_info["id"];
        $ranking_info = $custom->getCustomRankingOne($where);
        if(!$ranking_info){
            echo '<script>alert("奖品不存在");location="'.$url.'";</script>';die();
        }
        if($ranking_info&&(!$ranking_info["prizes1_name"]&&!$ranking_info["prizes2_name"]&&!$ranking_info["prizes3_name"]&&!$ranking_info["prizes4_name"]&&!$ranking_info["prizes5_name"])){
            echo '<script>alert("奖品不存在");location="'.$url.'";</script>';die();
        }
        if($ranking_info["prizes1_exchange"]==2){
            $ranking_info["prizes1_title"] = "已领取";
        }else{
            if(($ranking_info["add_time"]+($ranking_info["period"]*60*60*24))<= time()){
                $ranking_info["prizes1_title"] = "已过期";
                $ranking_info["prizes1_exchange"] = 3;
            }else{
                if($ranking_info["prizes1_exchange"]==1){
                    $ranking_info["prizes1_title"] = "已发放";
                }else{
                    $ranking_info["prizes1_title"] = "未发放";
                }
            }
        }
        if($ranking_info["prizes2_exchange"]==2){
            $ranking_info["prizes2_title"] = "已领取";
        }else{
            if(($ranking_info["add_time"]+($ranking_info["period"]*60*60*24))<= time()){
                $ranking_info["prizes2_title"] = "已过期";
                $ranking_info["prizes2_exchange"] = 3;
            }else{
                if($ranking_info["prizes2_exchange"]==1){
                    $ranking_info["prizes2_title"] = "已发放";
                }else{
                    $ranking_info["prizes2_title"] = "未发放";
                }
            }
        }
        if($ranking_info["prizes3_exchange"]==2){
            $ranking_info["prizes3_title"] = "已领取";
        }else{
            if(($ranking_info["add_time"]+($ranking_info["period"]*60*60*24))<= time()){
                $ranking_info["prizes3_title"] = "已过期";
                $ranking_info["prizes3_exchange"] = 3;
            }else{
                if($ranking_info["prizes3_exchange"]==1){
                    $ranking_info["prizes3_title"] = "已发放";
                }else{
                    $ranking_info["prizes3_title"] = "未发放";
                }
            }
        }
        if($ranking_info["prizes4_exchange"]==2){
            $ranking_info["prizes4_title"] = "已领取";
        }else{
            if(($ranking_info["add_time"]+($ranking_info["period"]*60*60*24))<= time()){
                $ranking_info["prizes4_title"] = "已过期";
                $ranking_info["prizes4_exchange"] = 3;
            }else{
                if($ranking_info["prizes4_exchange"]==1){
                    $ranking_info["prizes4_title"] = "已发放";
                }else{
                    $ranking_info["prizes4_title"] = "未发放";
                }
            }
        }
        if($ranking_info["prizes5_exchange"]==2){
            $ranking_info["prizes5_title"] = "已领取";
        }else{
            if(($ranking_info["add_time"]+($ranking_info["period"]*60*60*24))<= time()){
                $ranking_info["prizes5_title"] = "已过期";
                $ranking_info["prizes5_exchange"] = 3;
            }else{
                if($ranking_info["prizes5_exchange"]==1){
                    $ranking_info["prizes5_title"] = "已发放";
                }else{
                    $ranking_info["prizes5_title"] = "未发放";
                }
            }
        }
        if($ranking_info["join_prizes_exchange"]==2){
            $ranking_info["join_prizes_title"] = "已领取";
        }else{
            if(($ranking_info["add_time"]+($ranking_info["period"]*60*60*24))<= time()){
                $ranking_info["join_prizes_title"] = "已过期";
                $ranking_info["join_prizes_exchange"] = 3;
            }else{
                if($ranking_info["join_prizes_exchange"]==1){
                    $ranking_info["join_prizes_title"] = "已发放";
                }else{
                    $ranking_info["join_prizes_title"] = "未发放";
                }
            }
        }
        $url = $this->http.$_SERVER['HTTP_HOST'].'/index/custom/exchange.html';
        $c_where["id"] = $ranking_info["c_id"];
        $custom_info = $custom->getOne($c_where);
        $c_user_info = $custom->getCustomUserOne($custom_info["u_id"]);
        if($custom_info["is_del"]){
            $ranking_info["prizes1_title"] = "比赛已结束";
            $ranking_info["prizes1_exchange"] = 4;
            $ranking_info["prizes2_title"] = "比赛已结束";
            $ranking_info["prizes2_exchange"] = 4;
            $ranking_info["prizes3_title"] = "比赛已结束";
            $ranking_info["prizes3_exchange"] = 4;
            $ranking_info["prizes4_title"] = "比赛已结束";
            $ranking_info["prizes4_exchange"] = 4;
            $ranking_info["prizes5_title"] = "比赛已结束";
            $ranking_info["prizes5_exchange"] = 4;
            $ranking_info["join_prizes_title"] = "比赛已结束";
            $ranking_info["join_prizes_exchange"] = 4;
        }
        $user_address = $model_user->getGameUserAddr($user_info["id"]);
        $this->assign('userAddress',$user_address);
        $this->assign('customInfo',$custom_info);
        $this->assign('rankingInfo',$ranking_info);
        $this->assign('url',$url);
        $this->assign('userInfo',$user_info);
        $this->assign('cUserInfo',$c_user_info);
        $this->display("rankinginfo1");
    }
    /**
     * 奖券信息
     */
    public function orankinginfo(){
        $user_info = $this->user_info;
        $url = $this->http.$_SERVER['HTTP_HOST'].'/index/custom/custom.html';
        $id = I("id");
        $ocustom_id = I("ocustom_id",0);
        if(!$id&&!$ocustom_id){
            echo '<script>alert("参数错误");</script>';die();
        }
        $custom = D("SwissOfficialCustom");
        $model_user = D("User");
        if($id){
            $where["id"] = $id;
            $where["u_id"] = $user_info["id"];
            $ranking_info = $custom->getCustomRankingOne($where);
            //print_r($ranking_info);die();
            if(!$ranking_info){
                echo '<script>alert("奖品不存在");</script>';die();
            }
            if($ranking_info&&!$ranking_info["prizes_name"]){
                echo '<script>alert("奖品不存在");</script>';die();
            }
            $c_where["id"] = $ranking_info["c_id"];
            $this->assign('lq_url',$this->http.$_SERVER['HTTP_HOST'].'/index/custom/lq.html?r_id='.$ranking_info["id"]);
            $this->assign('addr_url',$this->http.$_SERVER['HTTP_HOST'].'/index/user/address.html');
        }
        if($ocustom_id){
            $c_where["id"] = $ocustom_id;
            $a_where["gameid"] = $id;
            $a_where["uid"] = $user_info["id"];
            $oapply_info = $custom->getApplyOne($a_where);
            $this->assign('oapply_info',$oapply_info);
        }
        $custom_info = $custom->getOne($c_where);
        $user_address = $model_user->getGameUserAddr($user_info["id"]);
        $this->assign('userAddress',$user_address);
        $this->assign('customInfo',$custom_info);
        $this->assign('rankingInfo',$ranking_info);
        $this->assign('url',$url);
        $this->assign('userInfo',$user_info);
        $this->assign('ocustom_id',$ocustom_id);
        $this->display("orankinginfo");
    }
    public function lq(){
        $user_info = $this->user_info;
        $r_id=I("r_id");
        if(!$r_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $custom = D("OfficialCustom");
        $model_order = D("Order");
        $blend = new Blend();
        $where["id"] = $r_id;
        $where["u_id"] = $user_info["id"];
        $where["status"] = 2;
        $ranking_info = $custom->getCustomRankingOne($where);
        if(!$ranking_info){
            $json["status"] = 306;
            $json["info"] = "奖品不存在";
            $this->ajaxReturn($json);
        }
        if($ranking_info["is_robot"]==0&&$ranking_info["prizes_type"]){
            $prizes = explode("-", $ranking_info["prizes_type"]);
            $cr_user_id = $ranking_info["u_id"];
            if($prizes[0]==3&&$ranking_info["prizes_value"]>0){
                if($prizes[1]==1){
                    $game_address = $model_order->getGameUserAddress($cr_user_id);
                    if($game_address&&$game_address["phone"]){
                        $order_number = $cr_user_id.time();
                        $game_data["order_number"] = $order_number;
                        $game_data["uid"] = $cr_user_id;
                        $game_data["item_id"] = $ranking_info["id"];
                        $game_data["item_name"] = $ranking_info["prizes_name"];
                        $game_data["cost_type"] = 5;
                        $game_data["type"] = 2;
                        $game_data["addr_name"] = $game_address["name"];
                        $game_data["addr_number"] = $game_address["number"];
                        $game_data["addr_phone"] = $game_address["phone"];
                        $game_data["addr_postcode"] = $game_address["postcode"];
                        $game_data["address"] = $game_address["address"];
                        $game_data["add_time"] = time();
                        $game_data["status"] = 400;
                        $game_data["amount"] = $ranking_info["prizes_value"];
                        $game_data["item_type"] = 1;
                        $game_order_status=$model_order->addGameOrder($game_data);
                        if($game_order_status){
                            $custom->updCustomRanking(["id"=>$ranking_info["id"]],["status"=>1]);
                            $blend->moblieRecharge($order_number);
                        }else{
                            $json["status"] = 307;
                            $json["info"] = "领取失败";
                            $this->ajaxReturn($json);
                        }
                    }else{
                        $json["status"] = 308;
                        $json["info"] = "领取失败";
                        $this->ajaxReturn($json);
                    }
                }
            }
        }
    }

    /**
     * 奖券信息
     */
    public function rankinginfo2(){
        $user_info = $this->user_info;
        $url = $this->http.$_SERVER['HTTP_HOST'].'/index/custom/custom.html';
        $id = I("id");
        if(!$id){
            echo '<script>alert("参数错误");location="'.$url.'";</script>';die();
        }
        $custom = D("Custom");
        $model_user = D("User");
        $where["id"] = $id;
        $ranking_info = $custom->getCustomRankingOne($where);
        if(!$ranking_info){
            echo '<script>alert("奖品不存在");location="'.$url.'";</script>';die();
        }
        if($ranking_info&&(!$ranking_info["prizes1_name"]&&!$ranking_info["prizes2_name"]&&!$ranking_info["prizes3_name"]&&!$ranking_info["prizes4_name"]&&!$ranking_info["prizes5_name"])){
            echo '<script>alert("奖品不存在");location="'.$url.'";</script>';die();
        }
        if($ranking_info["prizes1_exchange"]==2){
            $ranking_info["prizes1_title"] = "已领取";
        }else{
            if(($ranking_info["add_time"]+($ranking_info["period"]*60*60*24))<= time()){
                $ranking_info["prizes1_title"] = "已过期";
                $ranking_info["prizes1_exchange"] = 3;
            }else{
                if($ranking_info["prizes1_exchange"]==1){
                    $ranking_info["prizes1_title"] = "已发放";
                }else{
                    $ranking_info["prizes1_title"] = "未发放";
                }
            }
        }
        if($ranking_info["prizes2_exchange"]==2){
            $ranking_info["prizes2_title"] = "已领取";
        }else{
            if(($ranking_info["add_time"]+($ranking_info["period"]*60*60*24))<= time()){
                $ranking_info["prizes2_title"] = "已过期";
                $ranking_info["prizes2_exchange"] = 3;
            }else{
                if($ranking_info["prizes2_exchange"]==1){
                    $ranking_info["prizes2_title"] = "已发放";
                }else{
                    $ranking_info["prizes2_title"] = "未发放";
                }
            }
        }
        if($ranking_info["prizes3_exchange"]==2){
            $ranking_info["prizes3_title"] = "已领取";
        }else{
            if(($ranking_info["add_time"]+($ranking_info["period"]*60*60*24))<= time()){
                $ranking_info["prizes3_title"] = "已过期";
                $ranking_info["prizes3_exchange"] = 3;
            }else{
                if($ranking_info["prizes3_exchange"]==1){
                    $ranking_info["prizes3_title"] = "已发放";
                }else{
                    $ranking_info["prizes3_title"] = "未发放";
                }
            }
        }
        if($ranking_info["prizes4_exchange"]==2){
            $ranking_info["prizes4_title"] = "已领取";
        }else{
            if(($ranking_info["add_time"]+($ranking_info["period"]*60*60*24))<= time()){
                $ranking_info["prizes4_title"] = "已过期";
                $ranking_info["prizes4_exchange"] = 3;
            }else{
                if($ranking_info["prizes4_exchange"]==1){
                    $ranking_info["prizes4_title"] = "已发放";
                }else{
                    $ranking_info["prizes4_title"] = "未发放";
                }
            }
        }
        if($ranking_info["prizes5_exchange"]==2){
            $ranking_info["prizes5_title"] = "已领取";
        }else{
            if(($ranking_info["add_time"]+($ranking_info["period"]*60*60*24))<= time()){
                $ranking_info["prizes5_title"] = "已过期";
                $ranking_info["prizes5_exchange"] = 3;
            }else{
                if($ranking_info["prizes5_exchange"]==1){
                    $ranking_info["prizes5_title"] = "已发放";
                }else{
                    $ranking_info["prizes5_title"] = "未发放";
                }
            }
        }
        if($ranking_info["join_prizes_exchange"]==2){
            $ranking_info["join_prizes_title"] = "已领取";
        }else{
            if(($ranking_info["add_time"]+($ranking_info["period"]*60*60*24))<= time()){
                $ranking_info["join_prizes_title"] = "已过期";
                $ranking_info["join_prizes_exchange"] = 3;
            }else{
                if($ranking_info["join_prizes_exchange"]==1){
                    $ranking_info["join_prizes_title"] = "已发放";
                }else{
                    $ranking_info["join_prizes_title"] = "未发放";
                }
            }
        }
        $url = $this->http.$_SERVER['HTTP_HOST'].'/index/custom/exchange.html';
        $c_where["id"] = $ranking_info["c_id"];
        $custom_info = $custom->getOne($c_where);
        $userInfo = $model_user->getUserOne($ranking_info["u_id"]);
        if($custom_info["is_del"]){
            $ranking_info["prizes1_title"] = "比赛已结束";
            $ranking_info["prizes1_exchange"] = 4;
            $ranking_info["prizes2_title"] = "比赛已结束";
            $ranking_info["prizes2_exchange"] = 4;
            $ranking_info["prizes3_title"] = "比赛已结束";
            $ranking_info["prizes3_exchange"] = 4;
            $ranking_info["prizes4_title"] = "比赛已结束";
            $ranking_info["prizes4_exchange"] = 4;
            $ranking_info["prizes5_title"] = "比赛已结束";
            $ranking_info["prizes5_exchange"] = 4;
            $ranking_info["join_prizes_title"] = "比赛已结束";
            $ranking_info["join_prizes_exchange"] = 4;
        }
        $user_address = $model_user->getGameUserAddr($ranking_info["u_id"]);
        $this->assign('userAddress',$user_address);
        $this->assign('customInfo',$custom_info);
        $this->assign('rankingInfo',$ranking_info);
        $this->assign('url',$url);
        $this->assign('userInfo',$userInfo);
        $this->display("rankinginfo2");
    }
    /**
     * 兑奖地址
     */
    public function exchange(){
        $id = I("id");
        $type = delTrim(I("type",0));
        if(!$id||!$type){
            echo '<script>alert("参数错误");</script>';die();
        }
        $user_info = $this->user_info;
        $custom = D("Custom");
        $where["id"] = $id;
        $where["u_id"] = $user_info["id"];
        $url = $this->http.$_SERVER['HTTP_HOST'].'/index/custom/rankinginfo?id='.$id;
        $ranking_info = $custom->getCustomRankingOne($where);
        if(!$ranking_info){
            echo '<script>alert("奖品不存在");location="'.$url.'";</script>';die();
        }
        
        if($type==6){
            if($ranking_info["join_prizes_exchange"]==2){
                echo '<script>alert("奖品已领取");location="'.$url.'";</script>';die();
            }
        }else{
            if($ranking_info["prizes{$type}_exchange"]==2){
                echo '<script>alert("奖品已领取");location="'.$url.'";</script>';die();
            }
        }
        if($type==6){
            $data["join_prizes_exchange"] = 2;
        }else{
            $data["prizes{$type}_exchange"] = 2;
        }
        
        $ranking_info["prizes_name"] = $ranking_info["prizes{$type}_name"];
        $return_sataus = $custom->updCustomRanking($where,$data);
        if($return_sataus){
            echo '<script>alert("领取成功");location="'.$url.'";</script>';die();
        }else{
            echo '<script>alert("领取失败");location="'.$url.'";</script>';die();
        }
    }
    /**
     * 批量发货
     */
    public function exchangeall(){
        $id = I("c_id");
        $r_id = I("r_id");
        if(!$id){
            echo '<script>alert("参数错误");</script>';die();
        }
        $user_info = $this->user_info;
        $custom = D("Custom");
        $m_user = D("User");
        require_once APP_ROOT . "Application/Common/Concrete/wxapi/example/weixin.api.php";
        $weixin = new class_weixin_adv();
        $where["id"] = $id;
        $where["u_id"] = $user_info["id"];
        $where["is_del"] = 0;
        $where["status"] = 2;
        $where["is_send"] = 1;
        $custom_info = $custom->getOne($where);
        $url = $this->http.$_SERVER['HTTP_HOST'].'/index/custom/rankingdes.html?c_id='.$id;
        if($r_id){
            $url = $this->http.$_SERVER['HTTP_HOST'].'/index/custom/rankinginfo2.html?id='.$r_id;
        }
        if(!$custom_info){
            echo '<script>alert("比赛不存在");location="'.$url.'";</script>';die();
        }
        $custom_user_info = $custom->getCustomUserOne($custom_info["u_id"]);
        if($r_id){
            $crs_where["id"] = $r_id;
            $ranking_info = $custom->getCustomRankingOne($crs_where);
            if(!$ranking_info||empty($ranking_info)){
                echo '<script>alert("奖品不存在");location="'.$url.'";</script>';die();
            }
            $cr_data = [];
            $prizes_name = "";
           if($ranking_info["prizes1_name"]&&$ranking_info["prizes1_exchange"]==0){
               $cr_data["prizes1_exchange"] = 1;
               $prizes_name = $cr_data["prizes1_name"];
           }
           if($ranking_info["prizes2_name"]&&$ranking_info["prizes2_exchange"]==0){
               $cr_data["prizes2_exchange"] = 1;
               $prizes_name = $cr_data["prizes2_name"];
           }
           if($ranking_info["prizes3_name"]&&$ranking_info["prizes3_exchange"]==0){
               $cr_data["prizes3_exchange"] = 1;
               $prizes_name = $cr_data["prizes3_name"];
           }
           if($ranking_info["prizes4_name"]&&$ranking_info["prizes4_exchange"]==0){
               $cr_data["prizes4_exchange"] = 1;
               $prizes_name = $cr_data["prizes4_name"];
           }
           if($ranking_info["prizes5_name"]&&$ranking_info["prizes5_exchange"]==0){
               $cr_data["prizes5_exchange"] = 1;
               $prizes_name = $cr_data["prizes5_name"];
           }
           if($ranking_info["join_prizes_name"]&&$ranking_info["join_prizes_exchange"]==0){
               $cr_data["join_prizes_exchange"] = 1;
               $prizes_name = $cr_data["join_prizes_name"];
           }
           if($cr_data&&!empty($cr_data)){
               $msg_uid = $cr_data["u_id"];
               $r_status = $custom->updCustomRanking($crs_where,$cr_data);
               if($r_status){
                   $title = "您好，您的比赛奖品已发货，请注意查收";
                   $msg_data = $m_user->wxFhMsg($msg_uid,$title,$prizes_name,$custom_user_info["nickname"],HTTP_HOST."/index/custom/rankinginfo.html?id=".$cr_data["id"]);
                    $weixin->send_user_message($msg_data);
               }
           }
           echo '<script>alert("发货成功");location="'.$url.'";</script>';die();
        }
        $crs_where["c_id"] = $id;
        $url = $this->http.$_SERVER['HTTP_HOST'].'/index/custom/rankingdes.html?c_id='.$id;
        $ranking_list = $custom->getCustomRanking($crs_where);
        if(!$ranking_list||empty($ranking_list)){
            echo '<script>alert("奖品不存在");location="'.$url.'";</script>';die();
        }
        foreach ($ranking_list as $value) {
           $cr_where["id"] = $value["id"];
           $cr_data = [];
           $prizes_name = "";
           if($value["prizes1_name"]&&$value["prizes1_exchange"]==0){
               $cr_data["prizes1_exchange"] = 1;
                $prizes_name = $value["prizes1_name"];
           }
           if($value["prizes2_name"]&&$value["prizes2_exchange"]==0){
               $cr_data["prizes2_exchange"] = 1;
                $prizes_name = $value["prizes2_name"];
           }
           if($value["prizes3_name"]&&$value["prizes3_exchange"]==0){
               $cr_data["prizes3_exchange"] = 1;
                $prizes_name = $value["prizes3_name"];
           }
           if($value["prizes4_name"]&&$value["prizes4_exchange"]==0){
               $cr_data["prizes4_exchange"] = 1;
                $prizes_name = $value["prizes4_name"];
           }
           if($value["prizes5_name"]&&$value["prizes5_exchange"]==0){
               $cr_data["prizes5_exchange"] = 1;
                $prizes_name = $value["prizes5_name"];
           }
           if($value["join_prizes_name"]&&$value["join_prizes_exchange"]==0){
               $cr_data["join_prizes_exchange"] = 1;
                $prizes_name = $value["join_prizes_name"];
           }
           if($cr_data&&!empty($cr_data)){
               $msg_uid = $value["u_id"];
               $r_status = $custom->updCustomRanking($cr_where,$cr_data);
               if($r_status){
                   $title = "您好，您的比赛奖品已发货，请注意查收";
                   $msg_data = $m_user->wxFhMsg($msg_uid,$title,$prizes_name,$custom_user_info["nickname"],HTTP_HOST."/index/custom/rankinginfo.html?id=".$value["id"]);
                    $weixin->send_user_message($msg_data);
               }
               
           }
        }
        echo '<script>alert("发货成功");location="'.$url.'";</script>';die();
    }
    /**
     * 比赛二维码
     */
    public function code(){
        require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/WxPay.JsApiPay.php";
        require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
        $weixin = new class_weixin_adv();
        $custom_id = I("id");
        if(!$custom_id){
            die("参数错误");
        }
        $user_info = $this->user_info;
        $custom = D("Custom");
        $where["id"] = $custom_id;
        //$where["u_id"] = $user_info["id"];
        $where["is_del"] = 0;
        $where["audit_status"] = 1;
        $custom_info = $custom->getOne($where);
        if(!$custom_info){
            die("房间不存在");
        }
        if($user_info["id"]==$custom_info["u_id"]){
            $c_where["c_id"] = $custom_id;
            $c_where["u_id"] = $user_info["id"];
            $c_where["game_type"] = $custom_info["game_id"];
            $code_info = $custom->getCodeOne($c_where);
            $code_id = 0;
            if($code_info){
                $code_id = $code_info["id"];
            }else{
                $c_where["c_id"] = 0;
                $c_where["u_id"] = 0;
                $code_info = $custom->getCodeOne($c_where);
                if($code_info){
                    $code_status = $custom->updCustomCode(["id"=>$code_info["id"]],["u_id"=>$user_info["id"],"c_id"=>$custom_id,"game_type"=>$custom_info["game_id"]]);
                    if($code_status){
                        $code_id = $code_info["id"];
                    }
                }else{
                    $code_count = $custom->getCodeCount();
                    if($code_count<100000){
                        $c_data["u_id"] = $user_info["id"];
                        $c_data["c_id"] = $custom_id;
                        $c_data["game_type"] = $custom_info["game_id"];
                        $code_status = $custom->addCustomCode($c_data);
                        if($code_status){
                            $code_id = $code_status;
                        }
                    }
                }
            }
            if(!$code_id){
                die("生成二维码失败");
            }
            $code_date = $custom_info["code_date"];
            $paths = APP_ROOT."Public/file/custom/";
            $filename = $code_id."_qrcode.jpg"; //新文件名字
            if(!file_exists($paths)){
                mkdir ($paths, 0777);//创建二维码存放文件夹
            }else{
                if(!file_exists($paths.$filename)){
                    //if(($custom_info["code_date"]+(60*60*24*30))<= time()){
                        //1,永久二维码，2，临时二维码，有效期30天
                        $image = $weixin->create_qrcode(1,$code_id);
                        $imageinfo = curl_get($image);
                        $local_file = fopen($paths.$filename, 'w');
                        //如果没有打开文件，进行写入操作
                        if(false !==$local_file){
                            if(false !==fwrite($local_file, $imageinfo['body'])){
                                $data["code_date"] = time();
                                $code_date = $data["code_date"];
                                $custom->updCustom($where,$data);
                                fclose($local_file);
                            }
                        }
                    //}

                }else{
                    //已经存在的二维码不执行
                }
            }
        }else{
            $c_where["c_id"] = $custom_id;
            $code_info = $custom->getCodeOne($c_where);
            $code_id = 0;
            if($code_info){
                $code_id = $code_info["id"];
            }
            $filename = $code_id."_qrcode.jpg"; //新文件名字
        }
        
        $src = $this->http.$_SERVER['HTTP_HOST']."/Public/file/custom/".$filename;
        $custom_user_info = $custom->getCustomUserOne($custom_info["u_id"]);
        $wx_share_url = $this->http.$_SERVER['HTTP_HOST'].'/s/'. $user_info["id"] . '-' . $custom_info["id"] . '-0-' . $custom_info["game_id"] . '-3.html';
        $this->assign('image',$src);
        $this->assign('userInfo',$user_info);
        $this->assign('custom_info',$custom_info);
        $this->assign('cu_info',$custom_user_info);
        $this->assign('code_date', date("Y-m-d H:i:s",$code_date));
        $this->assign('custom_url',$this->http.$_SERVER['HTTP_HOST'].'/index/custom/?custom_id='.$custom_id);
        $this->assign('wx_share_url',$wx_share_url);
        
        $this->display();
    }
    /**
     * 获得奖券用户统计
     */
    public function rankingdes(){
        $user_info = $this->user_info;
        $custom = D("Custom");
        $user = D("User");
        $cid = I("c_id");
        $url = $this->http.$_SERVER['HTTP_HOST'].'/index/custom/custom.html';
        if(!$cid){
            echo '<script>alert("参数错误");location="'.$url.'";</script>';die();
        }
        $custom_ranking = [];
        $ran_where["c_id"] = $cid;
        $ran_where["u_id"] = array(array('EGT',100000)) ;
        $custom_ranking_list = $custom->getCustomRanking($ran_where);
        if($custom_ranking_list){
            foreach ($custom_ranking_list as $value) {
                if($value["prizes1_name"]||$value["prizes2_name"]||$value["prizes3_name"]||$value["prizes4_name"]||$value["prizes5_name"]){
                    $title = "";
                    $value["style"] = 0;
                    if(($value["prizes1_name"]&&$value["prizes1_exchange"]!=2)||($value["prizes2_name"]&&$value["prizes2_exchange"]!=2)||($value["prizes3_name"]&&$value["prizes3_exchange"]!=2)||($value["prizes4_name"]&&$value["prizes4_exchange"]!=2)||($value["prizes5_name"]&&$value["prizes5_exchange"]!=2)){
                        if(($value["add_time"]+($value["period"]*60*60*24))<= time()){
                            $title = "已过期";
                            $value["style"] = 1;
                        }else{
                            if(($value["prizes1_name"]&&$value["prizes1_exchange"]==1)||($value["prizes2_name"]&&$value["prizes2_exchange"]==1)||($value["prizes3_name"]&&$value["prizes3_exchange"]==1)||($value["prizes4_name"]&&$value["prizes4_exchange"]==1)||($value["prizes5_name"]&&$value["prizes5_exchange"]==1)){
                                $title = "已发放";
                            }else{
                                $title = "未发放";
                            }
                        }
                    }else{
                        $title = "已领取";
                        $value["style"] = 1;
                    }
                    $user_info = $user->getUserOne($value["u_id"]);
                    $value["user_name"] = $user_info["nickname"];
                    $value["user_headurl"] = $user_info["headurl"];
                    $value["title"] = $title;
                    $custom_ranking[] = $value;
                }
            }
        }
        $this->assign('userInfo',$user_info);
        $this->assign('customRanking',$custom_ranking);
        $this->assign('c_id',$cid);
        $this->display();
    }
    /**
     * 获得奖券用户统计
     */
    public function apply(){
        $custom = D("Custom");
        $user = D("User");
        $cid = I("c_id");
        $url = $this->http.$_SERVER['HTTP_HOST'].'/index/custom/custom.html';
        if(!$cid){
            echo '<script>alert("参数错误");location="'.$url.'";</script>';die();
        }
        $userInfo = $this->user_info;
        $custom_apply = [];
        $ca_where["gameid"] = $cid;
        $custom_apply_list = $custom->getApplyList($ca_where);
        if($custom_apply_list){
            foreach ($custom_apply_list as $value) {
                $user_info = $user->getUserOne($value["uid"]);
                $value["user_name"] = $user_info["nickname"];
                $value["gender"] = $user_info["gender"];
                $value["gender_name"] = "未知";
                if($user_info["gender"]==1){
                    $value["gender_name"] = "男";
                }
                if($user_info["gender"]==2){
                    $value["gender_name"] = "女";
                }
                $value["user_headurl"] = $user_info["headurl"];
                $custom_apply[] = $value;
            }
        }
        $custom_apply_count = $custom->getApplyCount($ca_where);
        $this->assign('export',$this->http.$_SERVER['HTTP_HOST'].'/index/index/applyexport.html?c_id='.$cid.'&u_id='.$userInfo["id"]);
        $this->assign('custom_apply_count',$custom_apply_count);
        $this->assign('customApply',$custom_apply);
        $this->display();
    }
    
    //导出
    public function applyexport(){
        $cid = I("c_id",0);
        $url = $this->http.$_SERVER['HTTP_HOST'].'/index/custom/custom.html';
        if(!$cid){
            echo '<script>alert("参数错误");location="'.$url.'";</script>';die();
        }
        $custom = D("Custom");
        $user = D("User");
        $user_info = $this->user_info;
        $where["u_id"] = $user_info["id"];
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
    /**
     * 比赛说明
     */
    public function instruction(){
        $this->display();
    }
    /**
     * 历史比赛记录
     */
    public function history(){
        $custom = D("Custom");
        $user_info = $this->user_info;
        $where["u_id"] = $user_info["id"];
        $where["is_del"] = 1;
        $where["start_date"] = array('EGT', time()-604800); //显示7天数据
        $custom_list = $custom->getList($where,"start_date DESC");
        $custom_arr = [];
        if($custom_list){
            foreach ($custom_list as $value) {
                $value["game_type"] = $this->getGameType($value["game_id"]);
                $custom_arr[] = $value;
            }
        }
        $this->assign('customList',$custom_arr);
        $this->display();
    }
    /**
     * 比赛列表
     */
    public function cuslist(){
        $custom = D("Custom");
        $user_info = $this->user_info;
        $where["u_id"] = $user_info["id"];
        $where["is_del"] = 0;
        //$where["start_date"] = array('EGT', time()-604800);
        $custom_list = $custom->getList($where,"start_date DESC");
        $custom_arr = [];
        if($custom_list){
            foreach ($custom_list as $value) {
                $value["game_type"] = $this->getGameType($value["game_id"]);
                $custom_arr[] = $value;
            }
        }
        $this->assign('customList',$custom_arr);
        $this->display();
    }
}
