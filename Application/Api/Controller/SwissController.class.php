<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Api\Controller;
use Common\Common\Sockets;
use Common\WxApi\JsApiPay;
use Common\WxApi\class_weixin_adv;
use QRcode;
class SwissController extends InitController {
    /**
     * 官方赛事列表
     */
    public function ocustom(){
        $user_info = $this->user_info;
        $ocustom = D("SwissOfficialCustom");
        $game_id = I("game_id",1);
        $where["game_id"] = $game_id;
        $where["is_del"] = 0;
        $ocustom_list = $ocustom->getList($where);
        $ocustom_arr = [];
        if($ocustom_list){
            foreach ($ocustom_list as $value) {
                $id = $value["id"];
                $ocustom_apply = $ocustom->getApplyOne(["gameid"=>$id,"uid"=>$user_info["id"],"status"=>array('NEQ',2)]);
                $ocustom_apply_count = $ocustom->getApplyCount(["gameid"=>$id]);
                $value["is_apply"] = 0;
                $value["apply_count"] = 0;
                if($ocustom_apply){
                    $value["is_apply"] = 1;
                }
                if($ocustom_apply_count){
                    $value["apply_count"] = $ocustom_apply_count;
                }
                $ocustom_arr[] = $value;
            }
            $json["status"] = 200;
            $json["info"] = "成功";
            $json["data"] = $ocustom_arr;
            $this->ajaxReturn($json);
        }
        $json["status"] = 305;
        $json["info"] = "没有数据";
        $this->ajaxReturn($json);
    }
    /**
     * 官方赛事用户获奖列表
     */
    public function userRankingList(){
        $user_info = $this->user_info;
        $ocustom = D("SwissOfficialCustom");
        $where["u_id"] = $user_info["id"];
        $ocustom_ranking_list = $ocustom->getCustomRanking($where,"add_time DESC");
        $ocustom_ranking_arr = [];
        if($ocustom_ranking_list){
            foreach ($ocustom_ranking_list as $value) {
                if($value["prizes_name"]&&$value["prizes_value"]){
                    $c_id = $value["c_id"];
                    $ocustom_info = $ocustom->getOne(["id"=>$c_id]);
                    $value["custom_date"] = $ocustom_info["start_date"];
                    $ocustom_ranking_arr[] = $value;
                }
            }
            $json["status"] = 200;
            $json["info"] = "成功";
            $json["data"] = $ocustom_ranking_arr;
            $this->ajaxReturn($json);
        }
        $json["status"] = 305;
        $json["info"] = "没有数据";
        $this->ajaxReturn($json);
    }
    /**
     * 官方赛事详情
     */
    public function ocustomdes(){
        $custom_id = I("custom_id");
        if(!$custom_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $ocustom = D("SwissOfficialCustom");
        $ocustom_info = $ocustom->getOne(["id"=>$custom_id,"is_del"=>0]);
        if(!$ocustom_info){
            $json["status"] = 306;
            $json["info"] = "比赛不存在";
            $this->ajaxReturn($json);
        }
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $ocustom_info;
        $this->ajaxReturn($json);
    }
    /**
     * 官方赛事报名人数
     */
    public function numoapply(){
        $custom_id = I("custom_id");
        if(!$custom_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $ocustom = D("SwissOfficialCustom");
        $ocustom_apply_count = $ocustom->getApplyCount($custom_id);
        
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $ocustom_apply_count[0]["num"];
        $this->ajaxReturn($json);
    }
    /**
     * 官方赛事报名
     */
    public function oapply(){
//        $arr = [100170,100172,100173,100208,100175,100176,100174,100325];
//        if(!in_array($this->user_id, $arr)){
//            $json["status"] = 311;
//            $json["info"] = "暂未开放";
//            $this->ajaxReturn($json);
//        }
        $custom_id = I("custom_id");
        if(!$custom_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $user_info = $this->user_info;
        $ocustom = D("SwissOfficialCustom");
        $user = D("User");
        $ocustom_info = $ocustom->getOne(["id"=>$custom_id,"is_del"=>0]);
        if(!$ocustom_info){
            $json["status"] = 306;
            $json["info"] = "比赛不存在";
            $this->ajaxReturn($json);
        }
        if($ocustom_info["status"]==2||$ocustom_info["status"]==3){
            $json["status"] = 307;
            $json["info"] = "比赛已结束";
            $this->ajaxReturn($json);
        }
        if($ocustom_info["status"]==1){
            $json["status"] = 308;
            $json["info"] = "比赛进行中";
            $this->ajaxReturn($json);
        }
        $ocustom_apply = $ocustom->getApplyOne(["gameid"=>$custom_id,"uid"=>$user_info["id"],"status"=>array('NEQ',2)]);
        if($ocustom_apply){
            $json["status"] = 313;
            $json["info"] = "您已报名比赛";
            $this->ajaxReturn($json);
        }
//        require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
//        $weixin = new class_weixin_adv();
        $game_user = $user->getGameUserOne(["uid"=>$this->user_id]);
        if($ocustom_info["t_type"]==1){
            if($ocustom_info["tickets"]>$game_user["coinnum"]){
                $json["status"] = 309;
                $json["info"] = "开心豆不足";
                $this->ajaxReturn($json);
            }else{
                if($ocustom_info["tickets"]>0){
                    $extra = array(
                            'add' => array('type' => 'int','size' => 2,'value' => 2),
                            'coin' => array ('type' => 'int','size' => 4,'value' => $ocustom_info["tickets"]),
                            'type' => array('type' => 'int','size' => 2,'value' => 98),
                            'cointype' => array('type' => 'int','size' => 2,'value' => 1)
                    );
                    $custom_rsp = Sockets :: call('call_back', 10, 20, $this->user_id, $extra);
                    add_log("ocustom.log", "game", "报名扣除开心豆Socket返回数据". var_export($custom_rsp, true));
                }
                $apply_data["gameid"] = $custom_id;
                $apply_data["uid"] = $this->user_id;
                $apply_data["applytime"] = time();
                $apply_id = $ocustom->addCustomApply($apply_data);
                if($apply_id){
//                    $dtime = "";
//                    $fdate=formatDate($ocustom_info["start_date"],time());
//                    if($fdate["d"]>0){
//                        $dtime = $fdate["d"]."天";
//                    }
//                    if($fdate["h"]>0){
//                        $dtime .= $fdate["h"]."小时";
//                    }
//                    if($fdate["i"]>0){
//                        $dtime .= $fdate["i"]."分钟";
//                    }
//                    if($fdate["s"]>0){
//                        $dtime .= $fdate["s"]."秒";
//                    }
//                    $msg_title = "您报名的".$ocustom_info["name"]."还有".$dtime."开始";
//                    $msg_url = 'https://'.$_SERVER['HTTP_HOST'] . '/0-' . $ocustom_info["id"] . '-0-'.$ocustom_info["game_id"].'-4.html';
//                    $custom_wx_msg = $user->wxCustomMsg($user_info["id"],$msg_title,$ocustom_info["name"],$ocustom_info["start_date"],$msg_url);
//                    $custom_wx_msg_return = $weixin->send_user_message($custom_wx_msg);
//                    add_log("ocustom.log", "home", "开赛提醒公众号消息推送状态：". var_export($custom_wx_msg_return, true));
                    $json["status"] = 200;
                    $json["info"] = "报名成功";
                    $json["data"] = $custom_id;
                    $this->ajaxReturn($json);
                }else{
                    $json["status"] = 310;
                    $json["info"] = "报名失败";
                    $this->ajaxReturn($json);
                }
            }
        }
        
        if($ocustom_info["t_type"]==2){
            if($ocustom_info["tickets"]>$game_user["cashpoint"]){
                $json["status"] = 309;
                $json["info"] = "钻石不足";
                $this->ajaxReturn($json);
            }else{
                if($ocustom_info["tickets"]>0){
                    $extra = array(
                            'add' => array('type' => 'int','size' => 2,'value' => 2),
                            'coin' => array ('type' => 'int','size' => 4,'value' => $ocustom_info["tickets"]),
                            'type' => array('type' => 'int','size' => 2,'value' => 99),
                            'cointype' => array('type' => 'int','size' => 2,'value' => 4)
                    );
                    $custom_rsp = Sockets :: call('call_back', 10, 20, $this->user_id, $extra);
                    add_log("ocustom.log", "game", "报名扣除钻石Socket返回数据". var_export($custom_rsp, true));
                }
                $apply_data["gameid"] = $custom_id;
                $apply_data["uid"] = $this->user_id;
                $apply_data["applytime"] = time();
                $apply_id = $ocustom->addCustomApply($apply_data);
                if($apply_id){
//                    $dtime = "";
//                    $fdate=formatDate($ocustom_info["start_date"],time());
//                    if($fdate["d"]>0){
//                        $dtime = $fdate["d"]."天";
//                    }
//                    if($fdate["h"]>0){
//                        $dtime .= $fdate["h"]."小时";
//                    }
//                    if($fdate["i"]>0){
//                        $dtime .= $fdate["i"]."分钟";
//                    }
//                    if($fdate["s"]>0){
//                        $dtime .= $fdate["s"]."秒";
//                    }
//                    $msg_title = "您报名的".$ocustom_info["name"]."还有".$dtime."开始";
//                    $msg_url = 'https://'.$_SERVER['HTTP_HOST'];
//                    $custom_wx_msg = $user->wxCustomMsg($user_info["id"],$msg_title,$ocustom_info["name"],$ocustom_info["start_date"],$msg_url);
//                    $custom_wx_msg_return = $weixin->send_user_message($custom_wx_msg);
//                    add_log("ocustom.log", "home", "开赛提醒公众号消息推送状态：". var_export($custom_wx_msg_return, true));
                    $json["status"] = 200;
                    $json["info"] = "报名成功";
                    $json["data"] = $custom_id;
                    $this->ajaxReturn($json);
                }else{
                    $json["status"] = 310;
                    $json["info"] = "报名失败";
                    $this->ajaxReturn($json);
                }
            }
        }
    }
    public function integral(){
        $custom_id = I("custom_id");
        if(!$custom_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $ocustom = D("SwissOfficialCustom");
        $user = D("User");
        $sql = "select u_id, max(map_gamevalue) as maxval from tb_swiss_official_custom_ranking_all  where c_id = ".$custom_id." group by u_id order by maxval desc ";
        $ocustom_all = $ocustom->getRankingAllBySql($sql);
        $ocustom_list = [];
        if($ocustom_all){
            foreach ($ocustom_all as $oal) {
                $o_user_info = $user->getGameUserOne(["uid"=>$oal["u_id"]]);
                $oal["nickname"] = $o_user_info["nickname"];
                $oal["headurl"] = $o_user_info["headurl"];
                $ocustom_list[] = $oal;
            }
        }
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $ocustom_list;
        $this->ajaxReturn($json);
    }
}
