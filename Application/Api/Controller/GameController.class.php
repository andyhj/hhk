<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Api\Controller;

/**
 * Description of GameController
 *
 * @author Administrator
 */
use Common\Common\Sockets;
use Common\Common\Redis;
use Common\WxApi\JsApiPay;
use Common\WxApi\class_weixin_adv;
class GameController extends InitController {
    public function index(){
        echo "this is index";
    }
    public function activity(){
        $user_info = $this->user_info;
        $page = I("page",1);
        $num = I("num",30);
        $model_activity = D("activity");
        $where["start_date"] = array('elt', time());  
        $where["end_date"] = array('gt', time());  
        $where["status"] = 1;
        $activity_list = $model_activity->getList($where,$page,$num,"order_id ASC");
        if(!$activity_list){
            $json["status"] = 305;
            $json["info"] = "没有数据";
            $this->ajaxReturn($json);
        }
        $activity_arr = [];
        foreach ($activity_list as $value) {
            $value["image"] = ADMIN_HOST.$value["image"];
            $value["ex_url"] = 'https://'.$_SERVER['HTTP_HOST']."/api/game/exavty.html?uid=".$user_info["id"]."&authkey=".$user_info["authkey"]."&activity_id=".$value["id"];
            $activity_arr[] = $value;
        }
        $json["status"] = 200;
        $json["info"] = "请求成功";
        $json["data"] = $activity_arr;
        $this->ajaxReturn($json);
    }
    public function exavty(){
        $user_info = $this->user_info;
        $activity_id = I("activity_id",0);
        if(!$activity_id){
            echo '<script>alert("参数错误");location="'.$this->http . $_SERVER['HTTP_HOST'].'";</script>';
            die();
        }
        $model_activity = D("activity");
        $m_user = D("User");
        $where["id"] = $activity_id;
        $activity_info = $model_activity->getOne($where);
        if(!$activity_info){
            echo '<script>alert("活动不存在");location="'.$this->http . $_SERVER['HTTP_HOST'].'";</script>';
            die();
        }
        $game_user_info = $m_user->getGameUserOne(["uid"=>$user_info["id"]]);
        $pay_url = 'https://'.$_SERVER['HTTP_HOST']."/api/pay/wpay.html?uid=".$user_info["id"]."&authkey=".$user_info["authkey"]."&item_id=".$activity_info["id"]."&type=2";
        if($activity_info["is_exchange"]){
            if($activity_info["ex_amount"]){
                if($game_user_info["cashpoint"]>=$activity_info["ex_amount"]){
                    $extra = array(
                            'add' => array('type' => 'int','size' => 2,'value' => 2),
                            'coin' => array ('type' => 'int','size' => 4,'value' => $activity_info["ex_amount"]),
                            'type' => array('type' => 'int','size' => 2,'value' => 97),
                            'cointype' => array('type' => 'int','size' => 2,'value' => 4)
                    );
                    $custom_rsp = Sockets :: call('call_back', 10, 20, $user_info["id"], $extra);
                    add_log("exavty.log", "game", "兑换扣除砖石Socket返回数据". var_export($custom_rsp, true));
                    //升级代理
                    $ug_info = $m_user->getUserAgencyByUserId($user_info["id"]);
                    if($ug_info){
                        if($activity_info["ex_amount"]==300&&$ug_info["grade"]==0){
                            $m_user->updAgency($user_info["id"],1);
                        }
                        if($activity_info["ex_amount"]==500&&$ug_info["grade"]<2){
                            $m_user->updAgency($user_info["id"],2);
                        }
                    }
                    if($activity_info["gold_bean"]){ //开心豆
                        $extra = array(
                                'add' => array('type' => 'int','size' => 2,'value' => 1),
                                'coin' => array ('type' => 'int','size' => 4,'value' => $activity_info["gold_bean"]),
                                'type' => array('type' => 'int','size' => 2,'value' => 94),
                                'cointype' => array('type' => 'int','size' => 2,'value' => 1)
                        );
                        $custom_rsp = Sockets :: call('call_back', 10, 20, $user_info["id"], $extra);
                        add_log("exavty.log", "game", "兑换开心豆Socket返回数据：". var_export($custom_rsp, true));
                    }
                    if($activity_info["silver_bean"]){  //时光豆
                        $extra = array(
                                'add' => array('type' => 'int','size' => 2,'value' => 1),
                                'coin' => array ('type' => 'int','size' => 4,'value' => $activity_info["silver_bean"]),
                                'type' => array('type' => 'int','size' => 2,'value' => 95),
                                'cointype' => array('type' => 'int','size' => 2,'value' => 3)
                        );
                        $custom_rsp = Sockets :: call('call_back', 10, 20, $user_info["id"], $extra);
                        add_log("exavty.log", "game", "兑换时光豆Socket返回数据：". var_export($custom_rsp, true));
                    }
                    if($activity_info["ticket"]){   //兑换券
                        $game_awardnum = $activity_info["ticket"]+$game_user_info["awardnum"];
                        $w_data["awardnum"] = $game_awardnum;
                        $r_status = $m_user->updGameUser($w_data,["uid"=>$user_info["id"]]);
                        add_log("exavty.log", "game", "兑换兑换券状态：". var_export($r_status, true));
                        if($r_status){
                            $lt_data["uid"] = $user_info["id"];
                            $lt_data["oper"] = 1;
                            $lt_data["changevalue"] = $activity_info["ticket"];
                            $lt_data["logtype"] = 3;
                            $lt_data["logtime"] = time();
                            $m_user->addLogTicket($lt_data);
                        }
                    }
                }else{
                    if($activity_info["is_pay"]){
                        echo '<script>alert("钻石不足");location="'.$pay_url.'";</script>';
                        die();
                    }
                }
            }
            echo '<script>alert("兑换成功");location="'.$this->http . $_SERVER['HTTP_HOST'].'";</script>';
            die();
        }else{
            if($activity_info["is_pay"]){
                echo '<script>location="'.$pay_url.'";</script>';
                die();
            }
        }
        
    }

    /**
     * 修改房间概率通知游戏服
     */
    public function roomnotice(){
        $room_id = I("room_id",0);
        $extra = array(
                'roomid' => array ('type' => 'int','size' => 4,'value' => $room_id)
        );
        $response = Sockets :: call('call_back', 10, 500, $room_id, $extra);
        add_log("game.log", "api", "Socket返回数据". var_export($response, true));
        if(isset($response["retcode"])&&$response["retcode"]==0){
            $json["status"] = 200;
            $json["info"] = "成功";
            $this->ajaxReturn($json);
        }else{
            $json["status"] = 306;
            $json["info"] = "失败";
            $this->ajaxReturn($json);
        }
    }
    /**
     * 获取机械人输赢
     */
    public function getRobot(){
        $m_redis = new Redis();
        $data = $m_redis->hgetall("global.data");
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $data;
        $this->ajaxReturn($json);
    }
    /**
     * 修改机械人输赢
     */
    public function updRobot(){
        $m_redis = new Redis();
        $robotmaxlosecoin = I("robotmaxlosecoin");
        $robotmaxwincoin = I("robotmaxwincoin");
        $winrate = I("winrate");
        $data["robotmaxlosecoin"] = $robotmaxlosecoin;
        $data["robotmaxwincoin"] = $robotmaxwincoin;
        $data["winrate"] = $winrate;
        $m_redis->hmset("global.data", $data);
        $json["status"] = 200;
        $json["info"] = "成功";
        $this->ajaxReturn($json);
    }
    /**
     * 获取玩家赢机器人的金币数量
     */
    public function getUserRobot(){
        $m_redis = new Redis();
        $uid = I("user_id");
        if(!$uid){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $data = $m_redis->hgetall("user.".$uid);
        if(!$data||empty($data)||empty($data["robotwincoin"])){
            $json["status"] = 306;
            $json["info"] = "没有数据";
            $this->ajaxReturn($json);
        }
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $data;
        $this->ajaxReturn($json);
    }
    /**
     * 修改玩家赢机器人的金币数量
     */
    public function updUserRobot(){
        $uid = I("user_id");
        $robotwincoin = I("robotwincoin",0);
        if(!$uid){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $m_redis = new Redis();
        $data = $m_redis->hgetall("user.".$uid);
        if(!$data||empty($data)||empty($data["robotwincoin"])){
            $json["status"] = 306;
            $json["info"] = "没有数据";
            $this->ajaxReturn($json);
        }
        $data["robotwincoin"] = $robotwincoin;
        $m_redis->hmset("user.".$uid, $data);
        $json["status"] = 200;
        $json["info"] = "成功";
        $this->ajaxReturn($json);
    }
    /**
     * 获取玩家充值系数概率值
     */
    public function getWinrate(){
        $m_redis = new Redis();
        $a = $m_redis->get("winratea");
        $b = $m_redis->get("winrateb");
        $r_data["a"] = $a;
        $r_data["b"] = $b;
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $r_data;
        $this->ajaxReturn($json);
    }
    /**
     * 修改玩家充值系数概率值
     */
    public function updWinrate(){
        $m_redis = new Redis();
        $a = I("gla",0);
        $b = I("glb",0);
        $m_redis->set("winratea", $a);
        $m_redis->set("winrateb", $b);
        $json["status"] = 200;
        $json["info"] = "成功";
        $this->ajaxReturn($json);
    }
    public function sendDlMsg(){
        header("Content-Type:text/html; charset=utf-8");
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/WxPay.JsApiPay.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/weixin.api.php";
        $user_id = I("user_id");
        $title = I("title");
        $remark = I("remark");
        $url = I("url");
        if(!$user_id||!$title||!$remark||!$url){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $m_user = D("user");
        $weixin = new class_weixin_adv();
        $msg_data = $m_user->wxDlSucceedMsg($user_id,$title, urldecode($remark),urldecode($url));
        $weixin->send_user_message($msg_data);
        $json["status"] = 200;
        $json["info"] = "成功";
        $this->ajaxReturn($json);
    }
    /**
     * 发货提醒
     */
    public function sendFhMsg(){
        header("Content-Type:text/html; charset=utf-8");
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/WxPay.JsApiPay.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/weixin.api.php";
        $user_id = I("user_id");
        $title = I("title");
        $keyword1 = I("keyword1");
        $keyword2 = I("keyword2","开心逗棋牌");
        $url = I("url");
        if(!$user_id||!$title||!$keyword1||!$keyword2||!$url){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $m_user = D("user");
        $weixin = new class_weixin_adv();
        $msg_data = $m_user->wxFhMsg($user_id,$title,$keyword1,$keyword2,urldecode($url));
        $weixin->send_user_message($msg_data);
        $json["status"] = 200;
        $json["info"] = "成功";
        $this->ajaxReturn($json);
    }
    /**
     * 创建比赛通知游戏服
     */
    public function updCustomSocket(){
        $gametype = I("gametype",0); //游戏类型，1斗地主
        $gameid = I("gameid",0); //比赛id
        $optype = I("optype",0); //操作类型1 创建 2 删除 3 修改
        if(!$gametype||!$gameid||!$optype){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $custom = D("Custom");
        $custom_info = $custom->getOne(["id"=>$gameid]);
        if(!$custom_info){
            $json["status"] = 307;
            $json["info"] = "比赛不存在";
            $this->ajaxReturn($json);
        }
        $roomid = ["1"=>["1"=>190,"2"=>196],"5"=>["1"=>553,"2"=>553]];
        $extra = array(
                'gametype' => array ('type' => 'int','size' => 4,'value' => $gametype),
                'gameid' => array ('type' => 'int','size' => 4,'value' => $gameid),
                'optype' => array ('type' => 'int','size' => 4,'value' => $optype),
                'roomid' => array ('type' => 'int','size' => 2,'value' => $roomid[$custom_info["game_id"]][$custom_info["type"]])
        );
        $response = Sockets :: call('call_back', 10, 501, $gameid, $extra);
        add_log("game.log", "api", "Socket返回数据". var_export($response, true));
        if(isset($response["retcode"])&&$response["retcode"]==0){
            $json["status"] = 200;
            $json["info"] = "成功";
            $this->ajaxReturn($json);
        }else{
            $json["status"] = 306;
            $json["info"] = "失败";
            $this->ajaxReturn($json);
        }
    }
    
     /**
     * 创建比赛通知游戏服
     */
    public function updOCustomSocket(){
        $gametype = I("gametype",0); //游戏类型，1斗地主
        $gameid = I("gameid",0); //比赛id
        $optype = I("optype",0); //操作类型1 创建 2 删除 3 修改
        if(!$gametype||!$gameid||!$optype){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $custom = D("SwissOfficialCustom");
        $custom_info = $custom->getOne(["id"=>$gameid]);
        if(!$custom_info){
            $json["status"] = 307;
            $json["info"] = "比赛不存在";
            $this->ajaxReturn($json);
        }
        $extra = array(
                'gametype' => array ('type' => 'int','size' => 4,'value' => $gametype),
                'gameid' => array ('type' => 'int','size' => 4,'value' => $gameid),
                'optype' => array ('type' => 'int','size' => 4,'value' => $optype),
                'roomid' => array ('type' => 'int','size' => 2,'value' => 197)
        );
        $response = Sockets :: call('call_back', 10, 501, $gameid, $extra);
        add_log("game.log", "api", "Socket返回数据". var_export($response, true));
        if(isset($response["retcode"])&&$response["retcode"]==0){
            $json["status"] = 200;
            $json["info"] = "成功";
            $this->ajaxReturn($json);
        }else{
            $json["status"] = 306;
            $json["info"] = "失败";
            $this->ajaxReturn($json);
        }
    }
    /**
     * 查询签到
     */
    public function signin(){
        $user_id = $this->user_id;
        $model_sigin = D("Signin");
        $user_signin_info = $model_sigin->getUserSigninOne(["uid"=>$user_id]);
        $num = 0;
        $is_sigin = 0;
        if($user_signin_info){
            $num = $user_signin_info["num"];
            if($num>=7){
                $is_sigin = 1;
                $json["status"] = 200;
                $json["info"] = "成功";
                $json["data"] = ["num"=>$num,"is_sigin"=>$is_sigin];
                $this->ajaxReturn($json);
            }
            $d_date = date("Ymd");
            $s_date = date("Ymd",$user_signin_info["end_date"]);
            if($d_date==$s_date){
                $is_sigin = 1;
            }
        }
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = ["num"=>$num,"is_sigin"=>$is_sigin];
        $this->ajaxReturn($json);
    }
    /**
     * 签到
     */
    public function addsignin(){
        $user_id = $this->user_id;
        $model_sigin = D("Signin");
        $model_user = D("User");
        $user_signin_info = $model_sigin->getUserSigninOne(["uid"=>$user_id]);
        $user_info = $model_user->getUserOne($user_id);
        if($user_signin_info){
            $num = $user_signin_info["num"];
            if($num>=7){
                $json["status"] = 307;
                $json["info"] = "已签到7天";
                $this->ajaxReturn($json);
            }
            $d_date = date("Ymd");
            $s_date = date("Ymd",$user_signin_info["end_date"]);
            if($d_date==$s_date){
                $json["status"] = 305;
                $json["info"] = "今天已签到";
                $this->ajaxReturn($json);
            }
            $where["id"] = $user_signin_info["id"];
            $up_data["num"] = $num+1;
            $up_data["end_date"] = time(); 
            $r_status = $model_sigin->updUserSigninOne($where,$up_data);
            if($r_status){
                $sigin_info = $model_sigin->getSigninOne(["id"=>$up_data["num"]]);
                if($sigin_info){
                    $cointype = 1;
                    if($sigin_info["type"]==2){
                        $cointype = 3;
                    }
                    if($sigin_info["type"]==4){
                        $cointype = 4;
                    }
                    $extra = array(
                            'add' => array('type' => 'int','size' => 2,'value' => 1),
                            'coin' => array ('type' => 'int','size' => 4,'value' => $sigin_info["num"]),
                            'type' => array('type' => 'int','size' => 2,'value' => 91),
                            'cointype' => array('type' => 'int','size' => 2,'value' => $cointype)
                    );
                    $response = Sockets :: call('call_back', 10, 20, $user_id, $extra);
                }
                
                $json["status"] = 200;
                $json["info"] = "签到成功";
                $this->ajaxReturn($json);
            }else{
                $json["status"] = 306;
                $json["info"] = "签到失败";
                $this->ajaxReturn($json);
            }
        }else{
            $add_data["uid"] = $user_id;
            $add_data["num"] = 1;
            $add_data["end_date"] = time(); 
            $r_status = $model_sigin->addUserSigninOne($add_data);
            if($r_status){
                $sigin_info = $model_sigin->getSigninOne(["id"=>1]);
                if($sigin_info){
                    $cointype = 1;
                    if($sigin_info["type"]==2){
                        $cointype = 3;
                    }
                    if($sigin_info["type"]==4){
                        $cointype = 4;
                    }
                    $extra = array(
                            'add' => array('type' => 'int','size' => 2,'value' => 1),
                            'coin' => array ('type' => 'int','size' => 4,'value' => $sigin_info["num"]),
                            'type' => array('type' => 'int','size' => 2,'value' => 91),
                            'cointype' => array('type' => 'int','size' => 2,'value' => $cointype)
                    );
                    $response = Sockets :: call('call_back', 10, 20, $user_id, $extra);
                }
                //上级增加一个有效用户
                $user_agency_info = $model_user->getUserAgencyByUserId($user_id);
                if($user_agency_info){
                    require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/weixin.api.php";
                    $weixin = new class_weixin_adv();
                    $parent_id = $user_agency_info["parent_id"];
                    if($parent_id&&$parent_id!=10001){
                        $user_agency_parent_info = $model_user->getUserAgencyByUserId($parent_id);
                        if($user_agency_parent_info){
                            $upd_data["num"] = $user_agency_parent_info["num"]+1;
                            $ug_status = $model_user->updateUserAgency(["u_id"=>$parent_id],$upd_data);
                            
                            
                            $msg_data = $model_user->wxRegMessage($parent_id,$user_id,$user_info["nickname"]);
                            $return_status = $weixin->send_user_message($msg_data);
                            //赠送2000开心豆
                            $extra = array(
                                    'add' => array('type' => 'int','size' => 2,'value' => 1),
                                    'coin' => array ('type' => 'int','size' => 4,'value' => 2000),
                                    'type' => array('type' => 'int','size' => 2,'value' => 91),
                                    'cointype' => array('type' => 'int','size' => 2,'value' => 1)
                            );
                            $response = Sockets :: call('call_back', 10, 20, $parent_id, $extra);
                        }
                    }
                }
                
                           
                
                $json["status"] = 200;
                $json["info"] = "签到成功";
                $this->ajaxReturn($json);
            }else{
                $json["status"] = 306;
                $json["info"] = "签到失败";
                $this->ajaxReturn($json);
            }
        }
    }
    public function sendBean(){
        $uid = I("user_id",0);
        $gold_bean = I("gold_bean",0);//开心豆
        $silver_bean = I("silver_bean",0);//时光豆
        $diamond = I("diamond",0);//砖石
        $model_user = D("User");
        if(!$uid){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $user_info = $model_user->getGameUserOne($uid);
        if(!$user_info){
            $json["status"] = 306;
            $json["info"] = "用户不存在";
            $this->ajaxReturn($json);
        }
        if($gold_bean>0){ //开心豆
            $extra = array(
                    'add' => array('type' => 'int','size' => 2,'value' => 1),
                    'coin' => array ('type' => 'int','size' => 4,'value' => $gold_bean),
                    'type' => array('type' => 'int','size' => 2,'value' => 91),
                    'cointype' => array('type' => 'int','size' => 2,'value' => 1)
            );
            $custom_rsp = Sockets :: call('call_back', 10, 20, $uid, $extra);
            add_log("bean.log", "game", "发送开心豆Socket返回数据：". var_export($custom_rsp, true));
        }
        if($silver_bean>0){  //时光豆
            $extra = array(
                    'add' => array('type' => 'int','size' => 2,'value' => 1),
                    'coin' => array ('type' => 'int','size' => 4,'value' => $silver_bean),
                    'type' => array('type' => 'int','size' => 2,'value' => 91),
                    'cointype' => array('type' => 'int','size' => 2,'value' => 3)
            );
            $custom_rsp = Sockets :: call('call_back', 10, 20, $uid, $extra);
            add_log("bean.log", "game", "发送时光豆Socket返回数据：". var_export($custom_rsp, true));
        }
        if($diamond>0){  //钻石
            $extra = array(
                    'add' => array('type' => 'int','size' => 2,'value' => 1),
                    'coin' => array ('type' => 'int','size' => 4,'value' => $diamond),
                    'type' => array('type' => 'int','size' => 2,'value' => 91),
                    'cointype' => array('type' => 'int','size' => 2,'value' => 4)
            );
            $custom_rsp = Sockets :: call('call_back', 10, 20, $uid, $extra);
            add_log("bean.log", "game", "发送钻石Socket返回数据：". var_export($custom_rsp, true));
        }
        $json["status"] = 200;
        $json["info"] = "发送成功";
        $this->ajaxReturn($json);
    }
    public function sendNews(){
        $m_redis = new Redis();
        $news_id = I("news_id",0);
        if(!$news_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $m_user = D("User");
        $news_info = $m_user->getGameUserNews($news_id);
        if(!$news_info){
            $json["status"] = 305;
            $json["info"] = "公告不存在";
            $this->ajaxReturn($json);
        }
        $news_info["id"] = (int)$news_info["id"];
        $news_info["sendtime"] = (int)$news_info["sendtime"];
        $news_info["start_date"] = (int)$news_info["start_date"];
        $news_info["expire_time"] = (int)$news_info["expire_time"];
        $news_info["interval"] = (int)$news_info["interval"];
        $m_redis->publish("gamenews", $news_info);
        $json["status"] = 200;
        $json["info"] = "成功";
        $this->ajaxReturn($json);
    }
    public function getNews(){
        $m_user = D("User");
        $where["start_date"] = array('ELT', time());
        $where["expire_time"] = array('GT', time());
        $news_list = $m_user->getGameUserNewsList($where);
        if($news_list){
            $json["status"] = 200;
            $json["info"] = "成功";
            $json["data"] = $news_list;
            $this->ajaxReturn($json);
        }
        $json["status"] = 305;
        $json["info"] = "没有数据";
        $this->ajaxReturn($json);
    }
    public function sendStartMsg(){
        $user_info = $this->user_info;
        $custom_id = delTrim(I("custom_id",0));
        if (!$custom_id) {
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $m_user = D("User");
        $custom = D("Custom");
        $where["id"] = $custom_id;
        $custom_info = $custom->getOne($where);
        if(!$custom_info){
            $json["status"] = 306;
            $json["info"] = "比赛不存在";
            $this->ajaxReturn($json);
        }
        require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
        $weixin = new class_weixin_adv();
        $msg_title = "该比赛类型为瑞士移位赛，需要报名人数为3的倍数，因为您报名较晚，所以被系统请出比赛。";
        $msg_url = HTTP_HOST.'/index/index/custom/?custom_id='.$custom_info["id"];
        $custom_wx_msg = $m_user->wxCustomMsg($user_info["id"],$msg_title,$custom_info["name"],$custom_info["start_date"],$msg_url,"开心斗地主","点击进入观战");
        $custom_wx_msg_return = $weixin->send_user_message($custom_wx_msg);
        add_log("game.log", "game", "开赛提醒公众号消息推送状态：". var_export($custom_wx_msg_return, true));
        $json["status"] = 200;
        $json["info"] = "成功";
        $this->ajaxReturn($json);
    }
    public function getItemList(){
        $order = D("Order");
        $item_list = $order->getItemCostList(["status"=>1]);
        if($item_list){
            $json["status"] = 200;
            $json["info"] = "成功";
            $json["dara"] = $item_list;
            $this->ajaxReturn($json);
        }
        $json["status"] = 305;
        $json["info"] = "没有数据";
        $this->ajaxReturn($json);
    }
    /**
     * 银行记录
     */
    public function bankRecord(){
        $bank_record = D("BankRecord");
        $page = I("page",1);
        $user_info = $this->user_info;
        $per_page = 20;
        $where["uid"] = $user_info["id"];
        $rec_list = $bank_record->getList($where,$page,$per_page);
        $rec_arr = [];
        if($rec_list){
            foreach ($rec_list as $value) {
                $value["type_name"] = $bank_record->getTypeText($value["type"]);
                $rec_arr[] = $value;
            }
        }
        $count = $bank_record->getCount($where);
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $rec_arr;
        $json["pages"] = ceil($count/$per_page);
        $this->ajaxReturn($json);
    }
    /**
     * 修改银行密码
     */
    public function bankPass(){
        $m_user = D("User");
        $user_info = $this->user_info;
        $where["uid"] = $user_info["id"];
        $opassword = I("opassword");
        $password = I("password");
        $apassword = I("apassword");
        if(!$opassword||!$password||!$apassword){
            $json["status"] = 305;
            $json["info"] = "参数不能为空";
            $this->ajaxReturn($json);
        }
        $where["bankpwd"] = $opassword;
        $game_user_info = $m_user->getGameUserOne($where);
        if (!$game_user_info) {
            $json["status"] = 306;
            $json["info"] = "旧密码错误";
            $this->ajaxReturn($json);
        }
        if (trim($password) != trim($apassword)) {
            $json["status"] = 307;
            $json["info"] = "两次密码不一致";
            $this->ajaxReturn($json);
        }
        $n_preg = "/^\d{6}$/";
        if (!preg_match($n_preg, $password)) {
            $json["status"] = 306;
            $json["info"] = "密码为6位的数字";
            $this->ajaxReturn($json);
        }
        if ($m_user->updGameUser(["bankpwd"=>$password],$where)) {
            $json["status"] = 200;
            $json["info"] = "修改成功";
            $this->ajaxReturn($json);
        }
        $json["status"] = 307;
        $json["info"] = "修改失败";
        $this->ajaxReturn($json);
    }
    /**
     * 查询返利
     */
    public function userAward(){
        $user_award = D("UserAward");
        $user_info = $this->user_info;
        $award_info = $user_award->getOne(["uid"=>$user_info["id"]]);
        $amount = 0;
        if($award_info){
            $amount = $award_info["award_coin"];
        }
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $amount;
        $this->ajaxReturn($json);
    }
    /**
     * 提取返利
     */
    public function extAward(){
        $user_award = D("UserAward");
        $user_info = $this->user_info;
        $award_info = $user_award->getOne(["uid"=>$user_info["id"]]);
        $amount = 0;
        if($award_info){
            $amount = $award_info["award_coin"];
        }
        if($amount<=0){
            $json["status"] = 306;
            $json["info"] = "额度为0";
            $this->ajaxReturn($json);
        }
        $bank_record = D("BankRecord");
        $rec_data["uid"] = $user_info["id"];
        $rec_data["coinnum"] = $amount;
        $rec_data["type"] = 5;
       
        $user_award->upd(["uid"=>$user_info["id"]],["award_coin"=>0,"extract_time"=>time()]);
        $rec_status = $bank_record->addRecord($rec_data);
        add_log("game.log", "api", $user_info["id"]."充值存银行状态：".$rec_status);
        if($rec_status){
            $json["status"] = 200;
            $json["info"] = "成功";
            $this->ajaxReturn($json);
        }
        $json["status"] = 308;
        $json["info"] = "失败";
        $this->ajaxReturn($json);
    }
    /**
     *转账
     */
    public function transfer(){
        $num = I("num");
        $user_id = I("user_id");
        $uid = $this->user_id;
        if(!$user_id||!$num){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        if($user_id==$uid){
            $json["status"] = 309;
            $json["info"] = "不能转给自己";
            $this->ajaxReturn($json);
        }
        $model_user = D("User");
        $zz_user_info = $model_user->getUserOne($uid);
        $user_info = $model_user->getUserOne($user_id);
        if(!$zz_user_info["is_proxy"]&&!$user_info["is_proxy"]){
            $json["status"] = 306;
            $json["info"] = "转账必须有一方是商家";
            $this->ajaxReturn($json);
        }
        $bank_record = D("BankRecord");
        $bank_statistic = D("BankStatistic");
        $bank_statistic_info = $bank_statistic->getOne(["uid"=>$uid]);
        $amount = 0;
        if($bank_statistic_info){
            $amount = $bank_statistic_info["remain_coin"];
        }
        if($amount<$num){
            $json["status"] = 307;
            $json["info"] = "额度不足";
            $this->ajaxReturn($json);
        }
        $rec_data["uid"] = $uid;
        $rec_data["from_or_to_uid"] = $user_id;
        $rec_data["coinnum"] = $num;
        $rec_data["type"] = 2;
        $rec_status = $bank_record->addRecord($rec_data,1);
        add_log("game.log", "api", $uid."充值存银行状态：".$rec_status);
        
        $rec_data["uid"] = $user_id;
        $rec_data["from_or_to_uid"] = $uid;
        $rec_data["coinnum"] = $num;
        $rec_data["type"] = 3;
        $rec_status = $bank_record->addRecord($rec_data,2);
        add_log("game.log", "api",$user_id."充值存银行状态：".$rec_status);
        if($rec_status){
            $json["status"] = 200;
            $json["info"] = "成功";
            $this->ajaxReturn($json);
        }
        $json["status"] = 308;
        $json["info"] = "失败";
        $this->ajaxReturn($json);
    }
    /**
     * 排行榜
     */
    public function ranking(){
        $db_config = C("DB_CONFIG2");
        $M = M("user",$db_config["DB_PREFIX"],$db_config);
        $where["uid"] = array("EGT","100000");
        $user_list = $M->where($where)->order("coinnum DESC")->page('1,30')->select();
        $d_user_arr = [];
        if($user_list){
            foreach ($user_list as $key=>$value) {
                $d["id"] = $key+1;
                $d["uid"] = $value["uid"];
                $d["nickname"] = $value["nickname"];
                $d["num"] = $value["coinnum"];
                $d_user_arr[] = $d;
            }
        }
        $where["is_proxy"] =1; 
        $cuser_list = $M->where($where)->order("bankcoin DESC")->select();
        $c_user_arr = [];
        if($cuser_list){
            foreach ($cuser_list as $key=>$value) {
                $c["id"] = $key+1;
                $c["uid"] = $value["uid"];
                $c["nickname"] = $value["nickname"];
                $c["signature"] = $value["signature"];
                $c["num"] = $value["bankcoin"];
                $c_user_arr[] = $c;
            }
        }
        $data["d_user_arr"] = $d_user_arr;
        $data["c_user_arr"] = $c_user_arr;
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $data;
        $this->ajaxReturn($json);
    }
    /**
     * 客服
     */
    public function service(){
        $service_m = M("service");
        $service_info = $service_m->find();
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $service_info;
        $this->ajaxReturn($json);
    }
    /**
     * 充值档次
     */
    public function getShop(){
        $shop_m = D("Shop");
        $shop_list = $shop_m->getList();
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $shop_list;
        $this->ajaxReturn($json);
    }
    
     /**
     * 获取游戏网关
     */
    public function getConfigInfo(){
        $db_config = C("DB_CONFIG2");
        $M = M("gw_config",$db_config["DB_PREFIX"],$db_config);
        $gw_list = $M->select();
        
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $gw_list;
        $this->ajaxReturn($json);
    }
    /**
     * 房间列表
     */
    public function getRoomList(){
        $gametype = I("gametype");
        $where = '';
        if ($gametype && is_numeric($gametype)) {
                $where .= "AND r.`game_type`=".$gametype;
        }
        $db_config = C("DB_CONFIG2");
        $m = M("mm_room",$db_config["DB_PREFIX"],$db_config);
        $sql = "SELECT r.`room_id`, r.`game_type`,r.coin_type,r.`room_type`,r.`room_level`,r.basefee, r.`user_limit` , r.`enter_limit`,r.high_limit, r.`name`, r.`summary`, r.`bak`, r.`commission`, r.`base_limit`,r.base_times " .
				   "FROM `mm_room` r " .
				   "WHERE 1 {$where} AND r.`status`='R' ";
        $query  = $m->query($sql);
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $query;
        $this->ajaxReturn($json);
    }
    /**
     * 方言自定义房
     */
    public function customRoom(){
        $db_config = C("DB_CONFIG2");
        $m = M("diy_tripods",$db_config["DB_PREFIX"],$db_config);
        $user_info = $this->user_info;
        $diy_tripods_list = $m->where(["createuid"=>$user_info["id"],"status"=>array('LT', 2),"endtime"=>array('GT', time())])->select();
        $arr = [];
        if($diy_tripods_list&&!empty($diy_tripods_list)){
            foreach ($diy_tripods_list as $d) {
                $dt["entercode"] = $d["entercode"];
                $dt["game_name"] = "三脚鸡";
                $dt["status_name"] = $d["status"]==1?"进行中":"等待中";
                $dt["createtime"] = date("Y/m/d",$d["createtime"]);
                $dt["endtime"] = floor((($d["endtime"]- time())%86400/60)).'分钟';
                $dt["desktype"] = $d["desktype"]==1?"普通房":"代开房";
                $qz = $d["havebanker"]?"明牌抢庄":"不明牌抢庄";
                
                $dt["playing"] = "三脚鸡:".$d["playernum"]."人-底分:".$d["basecoin"]."分-牌数:39张-".$qz."-".$d["gamenum"]."局";
                $arr[] = $dt;
            }
            $json["status"] = 200;
            $json["info"] = "成功";
            $json["data"] = $arr;
            $this->ajaxReturn($json);
        }
        $json["status"] = 306;
        $json["info"] = "没有数据";
        $this->ajaxReturn($json);
    }
    
    
    /**
     * 方言战绩回放列表
     */
    public function dtrr(){
        $db_config = C("DB_CONFIG2");
        $m = M("diy_tripods_result_record",$db_config["DB_PREFIX"],$db_config);
        $user_info = $this->user_info;
        $dtrr_list = $m->where(["uid"=>$user_info["id"],"play_time"=>array('EGT', strtotime(date("Y-m-d", time()-86400)))])->select();
        $arr = [];
        if($dtrr_list&&!empty($dtrr_list)){
            foreach ($dtrr_list as $d) {
                $d["game_name"] = "三脚鸡";
                $d["play_time"] =  date("Y-m-d",$d["play_time"]);
                $strtus_name = "平局";
                if($d["win_result"]==1){
                    $strtus_name = "赢";
                }
                if($d["win_result"]==2){
                    $strtus_name = "输";
                }
                $d["win_result"] = $strtus_name;
                $arr[] = $d;
            }
            $json["status"] = 200;
            $json["info"] = "成功";
            $json["data"] = $arr;
            $this->ajaxReturn($json);
        }
        $json["status"] = 306;
        $json["info"] = "没有数据";
        $this->ajaxReturn($json);
    }
    /**
     * 方言战绩回放房间列表
     */
    public function dtr(){
        $db_config = C("DB_CONFIG2");
        $m = M("diy_tripods_record",$db_config["DB_PREFIX"],$db_config);
        $serial_num = I("serial_num");
        $room_id = I("room_id");
        if(!$serial_num||!$room_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $mdtrr = M("diy_tripods_result_record",$db_config["DB_PREFIX"],$db_config);
        $dtrr_list = $mdtrr->where(["serial_num"=>$serial_num,'room_id'=>$room_id])->select();
        $dtr_list = $m->where(["serial_num"=>$serial_num,'room_id'=>$room_id])->group('small_serial_num')->select();
        $arr = [];
        $dtrr_arr = [];
        $m_user = D("User");
        if($dtrr_list&&!empty($dtrr_list)){
            foreach ($dtrr_list as $dl) {
                $user_infos = $m_user->getUserOne($dl["uid"]);
                $dl["nickname"] = $user_infos["nickname"];
                $dl["headurl"] = $user_infos["headurl"];
                $dtrr_arr[] = $dl;
            }
        }
        if($dtr_list&&!empty($dtr_list)){
            foreach ($dtr_list as $d) {
                $dt["create_time"] =  date("Y-m-d", strtotime($d["create_time"]));
                $dt["player1"] = $dtrr_list[0]["uid"];
                $dt["player2"] = $dtrr_list[1]["uid"];
                $dt["player3"] = $dtrr_list[2]["uid"];
                $dt["serial_num"] = $d["serial_num"];
                $dt["room_id"] = $d["room_id"];
                $dt["dtres"] = $dtrr_arr;
                $arr[] = $dt;
            }
            $json["status"] = 200;
            $json["info"] = "成功";
            $json["data"] = $arr;
            $this->ajaxReturn($json);
        }
        $json["status"] = 306;
        $json["info"] = "没有数据";
        $this->ajaxReturn($json);
    }
    
}
