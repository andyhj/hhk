<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cli\Controller;
use Common\WxApi\class_weixin_adv;
use Common\Common\Custom;
use Common\Common\Sockets;
use Common\Common\Blend;
class CustomController extends InitController {
    /**
     * 排名发奖
     */
    public function ranking(){
        $custom = D("Custom");
        $user = D("User");
        $where["is_del"] = 0;
        $where["audit_status"] = 1;
        $custom_list = $custom->getList($where);
        //add_log("custom.log", "cli", "进入排名发奖");
        if($custom_list){
            require_once APP_ROOT ."Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            add_log("custom.log", "cli", "比赛数据：". var_export($custom_list, true));
            foreach ($custom_list as $value) {
                $custom_id = $value["id"];
                $custom_name = $value["name"];
                $minute = $value["start_date"]-time();
                if($value["status"]==0&&($minute<=330&&$minute>270)){  //距离开赛还有5分钟提醒
                    add_log("custom.log", "cli", $custom_id." 进入开赛提醒");
                    $custom_apply = $custom->getApplyList(["gameid"=>$custom_id]); //比赛报名人列表
                    if($custom_apply){
                        foreach ($custom_apply as $ca) {
                            $ca_user_id = $ca["uid"];
                            $ca_user_info = $user->getUserOne($ca_user_id);
                            if($ca_user_info["other_id"]){
                                $msg_data["touser"] = $ca_user_info["other_id"];
                                $msg_data["template_id"] = "aKVigfLGpniMofqvIbkkP65BU7JoHleHmoWDQwr89sw";
                                $msg_data["url"] = HTTP_HOST.'/index/index/custom/?custom_id='.$custom_id;
                                $keyword1 = $this->getGameType($value["game_id"]);
                                $msg_data["data"] = array(
                                    "first"=>array(
                                        "value"=>"距离 {$custom_name} 比赛还剩5分钟",
                                        "color"=>""
                                    ),
                                    "keyword1"=>array(
                                        "value"=> $keyword1,
                                        "color"=>""
                                    ),
                                    "keyword2"=>array(
                                        "value"=>$custom_name,
                                        "color"=>""
                                    ),
                                    "keyword3"=>array(
                                        "value"=> date("Y-m-d H:i:s",$value["start_date"]),
                                        "color"=>""
                                    ),
                                    "remark"=>array(
                                        "value"=>"点击进入游戏",
                                        "color"=>""
                                    )
                                );
                                $return_status = $weixin->send_user_message($msg_data);
                                add_log("custom.log", "cli", "开赛推送微信消息状态：". var_export($return_status, true));
                            }else{
                                add_log("custom.log", "cli", "用户openid不存在，不推送微信消息：". var_export($ca_user_info, true));
                            }
                        }
                    }
                }
                if(($value["status"]==0&&($minute<=0))||$value["status"]==3){ //比赛人数不足
                    $custom_apply_count = $custom->getApplyCount(["gameid"=>$custom_id]);
                    if($value["type"]==1&&$custom_apply_count<$value["number"]){
                        add_log("custom.log", "cli", $custom_id." 进入比赛人数不足");
                        $obj_custom = new Custom();
                        $return_status = $obj_custom->delcustom($custom_id);
                        add_log("custom.log", "cli", $custom_id." 删除比赛状态:".$return_status);
                        if($return_status===11){
                            add_log("custom.log", "cli", $custom_id." 比赛不存在");
                        }
                        if($return_status===20){
                            add_log("custom.log", "cli", $custom_id." 删除成功");
                        }
                        if($return_status===13){
                            add_log("custom.log", "cli", $custom_id." 删除失败");
                        }
                        if($return_status===14){
                            add_log("custom.log", "cli", $custom_id." 比赛进行中不能删除");
                        }
                        if($return_status===15){
                            add_log("custom.log", "cli", $custom_id." 奖品发放中不能删除");
                        }
                    }
                }
                
                if($value["status"]==2&&$value["is_send"]==0){  //比赛结束发送奖品
                    add_log("custom.log", "cli", $custom_id." 进入比赛结束发奖");
                    if($value["join_prizes_name"]){
                        $del_ranking["c_id"] = $value["id"];
                        $d_r = $custom->delCustomRanking($del_ranking);
                        if($d_r){
                            $ranking_all = $custom->getCustomRankingAll(["c_id"=>$value["id"]]);
                            if($ranking_all){
                                $jpn = 0;
                                if($value["join_prizes_number"]>0){
                                    $jpn = $value["number"]+$value["join_prizes_number"];
                                }
                                $j = 0;
                                foreach ($ranking_all as $ra) {
                                    $j++;
                                    if($jpn>0){
                                        if($j>$jpn){
                                            break;
                                        }
                                    }
                                    $add_ranking["c_id"] = $ra["c_id"];
                                    $add_ranking["ci_id"] = $ra["ci_id"];
                                    $add_ranking["u_id"] = $ra["u_id"];
                                    $add_ranking["ranking"] = $ra["ranking"];
                                    $add_ranking["gamevalue"] = $ra["gamevalue"];
                                    $custom->addCustomRanking($add_ranking);
                                }
                            }
                        }
                    }
                    $user_info = $user->getUserOne($value["u_id"]);
                    $rn_where["c_id"] = $value["id"];
                    $custom_ranking_arr = $custom->getCustomRanking($rn_where);
                    if($custom_ranking_arr){
                        foreach ($custom_ranking_arr as $cra) {
                            $cr_user_id = $cra["u_id"];
                            $custom_apply_info = $custom->getApplyOne(["uid"=>$cr_user_id]);
                            $cr_mobile = $custom_apply_info["mobile"];
                            $r_where = [];
                            $r_data = [];
                            $r_where["id"] = $cra["id"];
                            $is_send=0;
                            //发送奖品1
                            if($value["prizes1_range"]){
                                $range = explode("-", $value["prizes1_range"]);
                                if($cra["ranking"]>=$range[0]&&$cra["ranking"]<=$range[1]){
                                    $is_send=1;
                                    $r_data["prizes1_name"] = $value["prizes1_name"];
                                    $r_data["prizes1_value"] = $value["prizes1_value"];
                                }
                            }
                            //发送奖品2
                            if($value["prizes2_range"]){
                                $range = explode("-", $value["prizes2_range"]);
                                if($cra["ranking"]>=$range[0]&&$cra["ranking"]<=$range[1]){
                                    $is_send=1;
                                    $r_data["prizes2_name"] = $value["prizes2_name"];
                                    $r_data["prizes2_value"] = $value["prizes2_value"];
                                }
                            }
                            //发送奖品3
                            if($value["prizes3_range"]){
                                $range = explode("-", $value["prizes3_range"]);
                                if($cra["ranking"]>=$range[0]&&$cra["ranking"]<=$range[1]){
                                    $is_send=1;
                                    $r_data["prizes3_name"] = $value["prizes3_name"];
                                    $r_data["prizes3_value"] = $value["prizes3_value"];
                                }
                            }
                            //发送奖品4
                            if($value["prizes4_range"]){
                                $range = explode("-", $value["prizes4_range"]);
                                if($cra["ranking"]>=$range[0]&&$cra["ranking"]<=$range[1]){
                                    $is_send=1;
                                    $r_data["prizes4_name"] = $value["prizes4_name"];
                                    $r_data["prizes4_value"] = $value["prizes4_value"];
                                }
                            }
                            //发送奖品5
                            if($value["prizes5_range"]){
                                $range = explode("-", $value["prizes5_range"]);
                                if($cra["ranking"]>=$range[0]&&$cra["ranking"]<=$range[1]){
                                    $is_send=1;
                                    $r_data["prizes5_name"] = $value["prizes5_name"];
                                    $r_data["prizes5_value"] = $value["prizes5_value"];
                                }
                            }
                            //发送参与奖
                            if($value["join_prizes_name"]&&!$is_send){
                                $r_data["join_prizes_name"] = $value["join_prizes_name"];
                                $r_data["join_prizes_value"] = $value["join_prizes_value"];
                            }
                            $r_data["name"] = $value["name"];
                            $r_data["period"] = $value["period"];
                            $r_data["headurl"] = $user_info["headurl"];
                            $r_data["mobile"] = $cr_mobile;
                            $r_data["add_time"] = time();
                            $return_status = $custom->updCustomRanking($r_where,$r_data);
                            add_log("custom.log", "cli", $cra["id"]."奖品数据：". var_export($r_data, true));
                            if($return_status){
                                $kan_user_info = $user->getUserOne($cr_user_id);
                                if($kan_user_info["other_id"]){
                                    $msg_data["touser"] = $kan_user_info["other_id"];
                                    $msg_data["template_id"] = "-IH8WVyI2Jk70Z5Je0CjO0li3A_8XjzgZLpW_qAggII";
                                    $msg_data["url"] = HTTP_HOST.'/index/custom/rankinginfo.html?id='.$cra["id"];
                                    $msg_data["data"] = array(
                                        "first"=>array(
                                            "value"=>"恭喜您得了第{$cra["ranking"]}名",
                                            "color"=>""
                                        ),
                                        "keyword1"=>array(
                                            "value"=> date("Y-m-d H:i:s",$value["end_date"]),
                                            "color"=>""
                                        ),
                                        "keyword2"=>array(
                                            "value"=> $value["name"],
                                            "color"=>""
                                        ),
                                        "keyword3"=>array(
                                            "value"=> "第{$cra["ranking"]}名",
                                            "color"=>""
                                        ),
                                        "remark"=>array(
                                            "value"=>"点击查看奖券详情",
                                            "color"=>""
                                        )
                                    );
                                    $return_status = $weixin->send_user_message($msg_data);
                                    add_log("custom.log", "cli", "推送比赛排名微信消息状态：". var_export($return_status, true));
                                }else{
                                    add_log("custom.log", "cli", "用户openid不存在，不推送微信消息：". var_export($kan_user_info, true));
                                }
                            }else{
                                add_log("custom.log", "cli", $cra["id"]."发送奖品失败");
                            }
                        }
                    }else{
                        add_log("custom.log", "cli", $value["id"]."排名不存在");
                    }
                    $cm_where["id"] = $custom_id;
                    $cm_data["is_send"] = 1;
                    $custom->updCustom($cm_where,$cm_data);
                    $bsjg_msg = $user->wxBsJgMsg($value["u_id"],"您发起的比赛已结束，请及时发放奖品","有效", date("Y-m-d H:i:s",$value["end_date"]?$value["end_date"]: time()),HTTP_HOST.'/index/custom/rankingdes.html?c_id='.$value["id"]);
                    $weixin->send_user_message($bsjg_msg);
                }
            }
        }
    }
    /**
     * 官方赛事排名发奖
     */
    public function oranking(){
        $custom = D("OfficialCustom");
        $user = D("User");
        $blend = new Blend();
        $model_order = D("Order");
        $where["is_del"] = 0;
        $custom_list = $custom->getList($where);
        //add_log("ocustom.log", "cli", "进入官方赛事排名发奖");
        if($custom_list){
            require_once APP_ROOT ."Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            add_log("ocustom.log", "cli", "比赛数据：". var_export($custom_list, true));
            foreach ($custom_list as $value) {
                $custom_id = $value["id"];
                $custom_name = $value["name"];
                $minute = $value["start_date"]-time();
                if($value["status"]==0&&($minute<=330&&$minute>270)){  //距离开赛还有5分钟提醒
                    add_log("ocustom.log", "cli", $custom_id." 进入开赛提醒");
                    $custom_apply = $custom->getApplyList(["gameid"=>$custom_id]); //比赛报名人列表
                    if($custom_apply){
                        foreach ($custom_apply as $ca) {
                            $ca_user_id = $ca["uid"];
                            $ca_user_info = $user->getUserOne($ca_user_id);
                            if($ca_user_info["other_id"]){
                                $msg_data["touser"] = $ca_user_info["other_id"];
                                $msg_data["template_id"] = "aKVigfLGpniMofqvIbkkP65BU7JoHleHmoWDQwr89sw";
                                $msg_data["url"] = HTTP_HOST. '/0-' . $value["id"] . '-0-'.$value["game_id"].'-4.html';
                                $keyword1 = $this->getGameType($value["game_id"]);
                                $msg_data["data"] = array(
                                    "first"=>array(
                                        "value"=>"距离 {$custom_name} 比赛还剩5分钟",
                                        "color"=>""
                                    ),
                                    "keyword1"=>array(
                                        "value"=> $keyword1,
                                        "color"=>""
                                    ),
                                    "keyword2"=>array(
                                        "value"=>$custom_name,
                                        "color"=>""
                                    ),
                                    "keyword3"=>array(
                                        "value"=> date("Y-m-d H:i:s",$value["start_date"]),
                                        "color"=>""
                                    ),
                                    "remark"=>array(
                                        "value"=>"点击进入游戏",
                                        "color"=>""
                                    )
                                );
                                $return_status = $weixin->send_user_message($msg_data);
                                add_log("ocustom.log", "cli", "开赛推送微信消息状态：". var_export($return_status, true));
                            }else{
                                add_log("ocustom.log", "cli", "用户openid不存在，不推送微信消息：". var_export($ca_user_info, true));
                            }
                        }
                    }
                }                
                if($value["status"]==2&&$value["is_send"]==0&&(time()-$value["end_date"])>300){  //比赛结束发送奖品
                    add_log("ocustom.log", "cli", $custom_id." 进入比赛结束发奖");
                    $rn_where["c_id"] = $value["id"];
                    $custom_ranking_arr = $custom->getCustomRanking($rn_where);
                    if($custom_ranking_arr){
                        foreach ($custom_ranking_arr as $cra) {
                            $cr_user_id = $cra["u_id"];
                            $r_where = [];
                            $r_data = [];
                            $r_where["id"] = $cra["id"];
                            //发送奖品1
                            if($value["prizes1_name"]){
                                if($cra["ranking"]==1){
                                    $r_data["prizes_type"] = $value["prizes1_type"];
                                    $r_data["prizes_name"] = $value["prizes1_name"];
                                    $r_data["prizes_value"] = $value["prizes1_value"];
                                    $r_data["prizes_image"] = $value["prizes1_image"];
                                }
                            }
                            //发送奖品2
                            if($value["prizes2_name"]){
                                if($cra["ranking"]==2){
                                    $r_data["prizes_type"] = $value["prizes2_type"];
                                    $r_data["prizes_name"] = $value["prizes2_name"];
                                    $r_data["prizes_value"] = $value["prizes2_value"];
                                    $r_data["prizes_image"] = $value["prizes2_image"];
                                }
                            }
                            //发送奖品3
                            if($value["prizes3_name"]){
                                if($cra["ranking"]==3){
                                    $r_data["prizes_type"] = $value["prizes3_type"];
                                    $r_data["prizes_name"] = $value["prizes3_name"];
                                    $r_data["prizes_value"] = $value["prizes3_value"];
                                    $r_data["prizes_image"] = $value["prizes3_image"];
                                }
                            }
                            //发送奖品4
                            if($value["prizes4_name"]){
                                if($cra["ranking"]==4){
                                    $r_data["prizes_type"] = $value["prizes4_type"];
                                    $r_data["prizes_name"] = $value["prizes4_name"];
                                    $r_data["prizes_value"] = $value["prizes4_value"];
                                    $r_data["prizes_image"] = $value["prizes4_image"];
                                }
                            }
                            //发送奖品5
                            if($value["prizes5_name"]){
                                if($cra["ranking"]==5){
                                    $r_data["prizes_type"] = $value["prizes5_type"];
                                    $r_data["prizes_name"] = $value["prizes5_name"];
                                    $r_data["prizes_value"] = $value["prizes5_value"];
                                    $r_data["prizes_image"] = $value["prizes5_image"];
                                }
                            }
                            $r_data["name"] = $value["name"];
                            $r_data["add_time"] = time();
                            $return_status = $custom->updCustomRanking($r_where,$r_data);
                            add_log("ocustom.log", "cli", $cra["id"]."奖品数据：". var_export($r_data, true));
                            if($return_status&&$cra["is_robot"]==0){
                                $kan_user_info = $user->getUserOne($cr_user_id);
                                if($kan_user_info["other_id"]){
                                    $msg_data["touser"] = $kan_user_info["other_id"];
                                    $msg_data["template_id"] = "-IH8WVyI2Jk70Z5Je0CjO0li3A_8XjzgZLpW_qAggII";
                                    $msg_data["url"] = HTTP_HOST.'/index/custom/orankinginfo.html?id='.$cra["id"];
                                    $msg_data["data"] = array(
                                        "first"=>array(
                                            "value"=>"恭喜您得了第{$cra["ranking"]}名",
                                            "color"=>""
                                        ),
                                        "keyword1"=>array(
                                            "value"=> date("Y-m-d H:i:s",$value["end_date"]),
                                            "color"=>""
                                        ),
                                        "keyword2"=>array(
                                            "value"=> $value["name"],
                                            "color"=>""
                                        ),
                                        "keyword3"=>array(
                                            "value"=> "第{$cra["ranking"]}名",
                                            "color"=>""
                                        ),
                                        "remark"=>array(
                                            "value"=>"点击查看奖券详情",
                                            "color"=>""
                                        )
                                    );
                                    $return_status = $weixin->send_user_message($msg_data);
                                    add_log("ocustom.log", "cli", "推送比赛排名微信消息状态：". var_export($return_status, true));
                                }else{
                                    add_log("ocustom.log", "cli", "用户openid不存在，不推送微信消息：". var_export($kan_user_info, true));
                                }
                                /**
                                 * 发送奖励
                                 */
                                if($r_data["prizes_type"]){
                                    $prizes = explode("-", $r_data["prizes_type"]);
                                    /**
                                     * 发送虚拟货币
                                     */
                                    if($prizes[0]==2&&$r_data["prizes_value"]>0){
                                        if($prizes[1]==1){
                                            $extra = array(
                                            'add' => array('type' => 'int','size' => 2,'value' => 1),
                                            'coin' => array ('type' => 'int','size' => 4,'value' => $r_data["prizes_value"]),
                                            'type' => array('type' => 'int','size' => 2,'value' => 100),
                                            'cointype' => array('type' => 'int','size' => 2,'value' => 1)
                                            );
                                            $custom_rsp = Sockets :: call('call_back', 10, 20, $cr_user_id, $extra);
                                            add_log("ocustom.log", "cli", "官方赛事送开心豆Socket返回数据：". var_export($custom_rsp, true));
                                        }
                                        if($prizes[1]==2){
                                            $extra = array(
                                            'add' => array('type' => 'int','size' => 2,'value' => 1),
                                            'coin' => array ('type' => 'int','size' => 4,'value' => $r_data["prizes_value"]),
                                            'type' => array('type' => 'int','size' => 2,'value' => 101),
                                            'cointype' => array('type' => 'int','size' => 2,'value' => 3)
                                            );
                                            $custom_rsp = Sockets :: call('call_back', 10, 20, $cr_user_id, $extra);
                                            add_log("ocustom.log", "cli", "官方赛事送时光豆Socket返回数据：". var_export($custom_rsp, true));
                                        }
                                        if($prizes[1]==3){
                                            $extra = array(
                                            'add' => array('type' => 'int','size' => 2,'value' => 1),
                                            'coin' => array ('type' => 'int','size' => 4,'value' => $r_data["prizes_value"]),
                                            'type' => array('type' => 'int','size' => 2,'value' => 102),
                                            'cointype' => array('type' => 'int','size' => 2,'value' => 4)
                                            );
                                            $custom_rsp = Sockets :: call('call_back', 10, 20, $cr_user_id, $extra);
                                            add_log("ocustom.log", "cli", "官方赛事送钻石Socket返回数据：". var_export($custom_rsp, true));
                                        }
                                    }
                                    /**
                                     * 发送虚拟物品
                                     */
                                    if($prizes[0]==3&&$r_data["prizes_value"]>0){
                                        if($prizes[1]==1){
                                            $game_address = $model_order->getGameUserAddress($cr_user_id);
                                            if($game_address&&$game_address["phone"]){
                                                $order_number = $cr_user_id.time();
                                                $game_data["order_number"] = $order_number;
                                                $game_data["uid"] = $cr_user_id;
                                                $game_data["item_id"] = $cra["id"];
                                                $game_data["item_name"] = $r_data["prizes_name"];
                                                $game_data["cost_type"] = 5;
                                                $game_data["type"] = 2;
                                                $game_data["addr_name"] = $game_address["name"];
                                                $game_data["addr_number"] = $game_address["number"];
                                                $game_data["addr_phone"] = $game_address["phone"];
                                                $game_data["addr_postcode"] = $game_address["postcode"];
                                                $game_data["address"] = $game_address["address"];
                                                $game_data["add_time"] = time();
                                                $game_data["status"] = 400;
                                                $game_data["amount"] = $r_data["prizes_value"];
                                                $game_data["item_type"] = 1;
                                                $game_order_status=$model_order->addGameOrder($game_data);
                                                if($game_order_status){
                                                    $blend->moblieRecharge($order_number);
                                                }else{
                                                    add_log("ocustom.log", "cli", "添加订单失败，订单数据：". var_export($game_data, true));
                                                    $custom->updCustomRanking($r_where,["status"=>2]);
                                                    $msg_data = $user->wxExchangeFallMsg($cr_user_id,"您好，您的话费奖品兑换失败","系统错误","点击链接领取",HTTP_HOST.'/index/custom/orankinginfo.html?id='.$cra["id"]);
                                                    $return_status = $weixin->send_user_message($msg_data);
                                                    add_log("ocustom.log", "cli", "微信消息状态：". var_export($return_status,true));
                                                }
                                            }else{
                                                add_log("ocustom.log", "cli", "用户收货地址不存在：". var_export($custom_rsp, true));
                                                $custom->updCustomRanking($r_where,["status"=>2]);
                                                $msg_data = $user->wxExchangeFallMsg($cr_user_id,"您好，您的话费奖品兑换失败","收货地址未填写或填写错误","点击链接领取",HTTP_HOST.'/index/custom/orankinginfo.html?id='.$cra["id"]);
                                                $return_status = $weixin->send_user_message($msg_data);
                                                add_log("ocustom.log", "cli", "微信消息状态：". var_export($return_status,true));
                                            }
                                        }
                                        
                                    }
                                }
                            }else{
                                add_log("ocustom.log", "cli", $cra["id"]."发送奖品失败");
                            }
                        }
                    }else{
                        add_log("ocustom.log", "cli", $value["id"]."排名不存在");
                    }
                    if($value["join_prizes_number"]>0){
                        $apply_sql = "SELECT * FROM `tb_official_custom` where gameid=".$value["id"]." order by ranking limit ".$value["max_number"].",".$value["join_prizes_number"];
                        $oapply_list = $custom->getApplyListBySql($apply_sql);
                        if($oapply_list){
                            foreach ($oapply_list as $oal) {
                                $o_user_info = $user->getUserOne($oal["uid"]);
                                if($o_user_info["other_id"]){
                                    $msg_data["touser"] = $o_user_info["other_id"];
                                    $msg_data["template_id"] = "-IH8WVyI2Jk70Z5Je0CjO0li3A_8XjzgZLpW_qAggII";
                                    $msg_data["url"] = HTTP_HOST.'/index/custom/orankinginfo.html?ocustom_id='.$oal["gameid"];
                                    $msg_data["data"] = array(
                                        "first"=>array(
                                            "value"=>"恭喜您得了第{$oal["ranking"]}名",
                                            "color"=>""
                                        ),
                                        "keyword1"=>array(
                                            "value"=> date("Y-m-d H:i:s",$value["end_date"]),
                                            "color"=>""
                                        ),
                                        "keyword2"=>array(
                                            "value"=> $value["name"],
                                            "color"=>""
                                        ),
                                        "keyword3"=>array(
                                            "value"=> "第{$oal["ranking"]}名",
                                            "color"=>""
                                        ),
                                        "remark"=>array(
                                            "value"=>"点击查看奖券详情",
                                            "color"=>""
                                        )
                                    );
                                    $return_status = $weixin->send_user_message($msg_data);
                                    add_log("ocustom.log", "cli", "推送比赛参与奖排名微信消息状态：". var_export($return_status, true));
                                }else{
                                    add_log("ocustom.log", "cli", "用户openid不存在，不推送微信消息：". var_export($kan_user_info, true));
                                }
                            }
                        }
                    }
                    $cm_where["id"] = $custom_id;
                    $cm_data["is_send"] = 1;
                    $custom->updCustom($cm_where,$cm_data);
                }
            }
        }
    }
    
    /**
     * 官方赛事人数不足
     * @return boolean
     */
    public function gfocustom(){
        add_log("fhocustom.log", "cli", "进入");
        $custom = D("OfficialCustom");
        $where["is_del"] = 0;
        $where["status"] = 3;
        $where["cost_is_return"] = 0;
        $custom_list = $custom->getList($where);
        if($custom_list){
            add_log("fhocustom.log", "cli", "比赛数据：". var_export($custom_list, true));
            foreach ($custom_list as $value) {
                $custom->updCustom(["id"=>$value["id"]],["cost_is_return"=>1]);
                if($value["tickets"]>0){
                    $custom_apply_list = $custom->getApplyList(["gameid"=>$value["id"]]);
                    if($custom_apply_list){
                        foreach ($custom_apply_list as $ca) {
                            $uid = $ca["uid"];
                            if($value["t_type"]==1){
                                $extra = array(
                                'add' => array('type' => 'int','size' => 2,'value' => 1),
                                'coin' => array ('type' => 'int','size' => 4,'value' => (int)$value["tickets"]),
                                'type' => array('type' => 'int','size' => 2,'value' => 103),
                                'cointype' => array('type' => 'int','size' => 2,'value' => 1)
                                );
                                $custom_rsp = Sockets :: call('call_back', 10, 20, $uid, $extra);
                                add_log("fhocustom.log", "cli", "官方赛事返还开心豆Socket返回数据：". var_export($custom_rsp, true));
                            }
                            if($value["t_type"]==2){
                                $extra = array(
                                'add' => array('type' => 'int','size' => 2,'value' => 1),
                                'coin' => array ('type' => 'int','size' => 4,'value' => (int)$value["tickets"]),
                                'type' => array('type' => 'int','size' => 2,'value' => 104),
                                'cointype' => array('type' => 'int','size' => 2,'value' => 4)
                                );
                                $custom_rsp = Sockets :: call('call_back', 10, 20, $uid, $extra);
                                add_log("fhocustom.log", "cli", "官方赛事返还钻石Socket返回数据：". var_export($custom_rsp, true));
                            }
                        }
                    }
                }
            }
        }
    }
    
    /**
     * 官方赛事排名发奖
     */
    public function soranking(){
        $custom = D("SwissOfficialCustom");
        $user = D("User");
        $blend = new Blend();
        $model_order = D("Order");
        $where["is_del"] = 0;
        $custom_list = $custom->getList($where);
        add_log("socustom.log", "cli", "进入官方赛事排名发奖");
        if($custom_list){
            require_once APP_ROOT ."Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            add_log("socustom.log", "cli", "比赛数据：". var_export($custom_list, true));
            foreach ($custom_list as $value) {
                $custom_id = $value["id"];         
                if($value["status"]==2&&$value["is_send"]==0&&(time()-$value["end_date"])>300){  //比赛结束发送奖品
                    add_log("socustom.log", "cli", $custom_id." 进入比赛结束发奖");
                    $rn_where["c_id"] = $value["id"];
                    $custom_ranking_arr = $custom->getCustomRanking($rn_where);
                    if($custom_ranking_arr){
                        foreach ($custom_ranking_arr as $cra) {
                            $cr_user_id = $cra["u_id"];
                            $r_where = [];
                            $r_data = [];
                            $r_where["id"] = $cra["id"];
                            //发送奖品1
                            if($value["prizes1_name"]){
                                if($cra["ranking"]==1){
                                    $r_data["prizes_type"] = $value["prizes1_type"];
                                    $r_data["prizes_name"] = $value["prizes1_name"];
                                    $r_data["prizes_value"] = $value["prizes1_value"];
                                    $r_data["prizes_image"] = $value["prizes1_image"];
                                }
                            }
                            //发送奖品2
                            if($value["prizes2_name"]){
                                if($cra["ranking"]==2){
                                    $r_data["prizes_type"] = $value["prizes2_type"];
                                    $r_data["prizes_name"] = $value["prizes2_name"];
                                    $r_data["prizes_value"] = $value["prizes2_value"];
                                    $r_data["prizes_image"] = $value["prizes2_image"];
                                }
                            }
                            //发送奖品3
                            if($value["prizes3_name"]){
                                if($cra["ranking"]==3){
                                    $r_data["prizes_type"] = $value["prizes3_type"];
                                    $r_data["prizes_name"] = $value["prizes3_name"];
                                    $r_data["prizes_value"] = $value["prizes3_value"];
                                    $r_data["prizes_image"] = $value["prizes3_image"];
                                }
                            }
                            //发送奖品4
                            if($value["prizes4_name"]){
                                if($cra["ranking"]==4){
                                    $r_data["prizes_type"] = $value["prizes4_type"];
                                    $r_data["prizes_name"] = $value["prizes4_name"];
                                    $r_data["prizes_value"] = $value["prizes4_value"];
                                    $r_data["prizes_image"] = $value["prizes4_image"];
                                }
                            }
                            //发送奖品5
                            if($value["prizes5_name"]){
                                if($cra["ranking"]==5){
                                    $r_data["prizes_type"] = $value["prizes5_type"];
                                    $r_data["prizes_name"] = $value["prizes5_name"];
                                    $r_data["prizes_value"] = $value["prizes5_value"];
                                    $r_data["prizes_image"] = $value["prizes5_image"];
                                }
                            }
                            $r_data["name"] = $value["name"];
                            $r_data["add_time"] = time();
                            $return_status = $custom->updCustomRanking($r_where,$r_data);
                            add_log("socustom.log", "cli", $cra["id"]."奖品数据：". var_export($r_data, true));
                            if($return_status&&$cra["is_robot"]==0){
                                $kan_user_info = $user->getUserOne($cr_user_id);
                                if($kan_user_info["other_id"]){
                                    $msg_data["touser"] = $kan_user_info["other_id"];
                                    $msg_data["template_id"] = "-IH8WVyI2Jk70Z5Je0CjO0li3A_8XjzgZLpW_qAggII";
                                    $msg_data["url"] = HTTP_HOST.'/index/custom/orankinginfo.html?id='.$cra["id"];
                                    $msg_data["data"] = array(
                                        "first"=>array(
                                            "value"=>"恭喜您得了第{$cra["ranking"]}名",
                                            "color"=>""
                                        ),
                                        "keyword1"=>array(
                                            "value"=> date("Y-m-d H:i:s",$value["end_date"]),
                                            "color"=>""
                                        ),
                                        "keyword2"=>array(
                                            "value"=> $value["name"],
                                            "color"=>""
                                        ),
                                        "keyword3"=>array(
                                            "value"=> "第{$cra["ranking"]}名",
                                            "color"=>""
                                        ),
                                        "remark"=>array(
                                            "value"=>"点击查看奖券详情",
                                            "color"=>""
                                        )
                                    );
                                    $return_status = $weixin->send_user_message($msg_data);
                                    add_log("socustom.log", "cli", "推送比赛排名微信消息状态：". var_export($return_status, true));
                                }else{
                                    add_log("socustom.log", "cli", "用户openid不存在，不推送微信消息：". var_export($kan_user_info, true));
                                }
                                /**
                                 * 发送奖励
                                 */
                                if($r_data["prizes_type"]){
                                    $prizes = explode("-", $r_data["prizes_type"]);
                                    /**
                                     * 发送虚拟货币
                                     */
                                    if($prizes[0]==2&&$r_data["prizes_value"]>0){
                                        if($prizes[1]==1){
                                            $extra = array(
                                            'add' => array('type' => 'int','size' => 2,'value' => 1),
                                            'coin' => array ('type' => 'int','size' => 4,'value' => $r_data["prizes_value"]),
                                            'type' => array('type' => 'int','size' => 2,'value' => 100),
                                            'cointype' => array('type' => 'int','size' => 2,'value' => 1)
                                            );
                                            $custom_rsp = Sockets :: call('call_back', 10, 20, $cr_user_id, $extra);
                                            add_log("socustom.log", "cli", "官方赛事送开心豆Socket返回数据：". var_export($custom_rsp, true));
                                        }
                                        if($prizes[1]==2){
                                            $extra = array(
                                            'add' => array('type' => 'int','size' => 2,'value' => 1),
                                            'coin' => array ('type' => 'int','size' => 4,'value' => $r_data["prizes_value"]),
                                            'type' => array('type' => 'int','size' => 2,'value' => 101),
                                            'cointype' => array('type' => 'int','size' => 2,'value' => 3)
                                            );
                                            $custom_rsp = Sockets :: call('call_back', 10, 20, $cr_user_id, $extra);
                                            add_log("socustom.log", "cli", "官方赛事送时光豆Socket返回数据：". var_export($custom_rsp, true));
                                        }
                                        if($prizes[1]==3){
                                            $extra = array(
                                            'add' => array('type' => 'int','size' => 2,'value' => 1),
                                            'coin' => array ('type' => 'int','size' => 4,'value' => $r_data["prizes_value"]),
                                            'type' => array('type' => 'int','size' => 2,'value' => 102),
                                            'cointype' => array('type' => 'int','size' => 2,'value' => 4)
                                            );
                                            $custom_rsp = Sockets :: call('call_back', 10, 20, $cr_user_id, $extra);
                                            add_log("socustom.log", "cli", "官方赛事送钻石Socket返回数据：". var_export($custom_rsp, true));
                                        }
                                    }
                                    /**
                                     * 发送虚拟物品
                                     */
                                    if($prizes[0]==3&&$r_data["prizes_value"]>0){
                                        if($prizes[1]==1){
                                            $game_address = $model_order->getGameUserAddress($cr_user_id);
                                            if($game_address&&$game_address["phone"]){
                                                $order_number = $cr_user_id.time();
                                                $game_data["order_number"] = $order_number;
                                                $game_data["uid"] = $cr_user_id;
                                                $game_data["item_id"] = $cra["id"];
                                                $game_data["item_name"] = $r_data["prizes_name"];
                                                $game_data["cost_type"] = 5;
                                                $game_data["type"] = 2;
                                                $game_data["addr_name"] = $game_address["name"];
                                                $game_data["addr_number"] = $game_address["number"];
                                                $game_data["addr_phone"] = $game_address["phone"];
                                                $game_data["addr_postcode"] = $game_address["postcode"];
                                                $game_data["address"] = $game_address["address"];
                                                $game_data["add_time"] = time();
                                                $game_data["status"] = 400;
                                                $game_data["amount"] = $r_data["prizes_value"];
                                                $game_data["item_type"] = 1;
                                                $game_order_status=$model_order->addGameOrder($game_data);
                                                if($game_order_status){
                                                    $blend->moblieRecharge($order_number);
                                                }else{
                                                    add_log("socustom.log", "cli", "添加订单失败，订单数据：". var_export($game_data, true));
                                                    $custom->updCustomRanking($r_where,["status"=>2]);
                                                    $msg_data = $user->wxExchangeFallMsg($cr_user_id,"您好，您的话费奖品兑换失败","系统错误","点击链接领取",HTTP_HOST.'/index/custom/orankinginfo.html?id='.$cra["id"]);
                                                    $return_status = $weixin->send_user_message($msg_data);
                                                    add_log("socustom.log", "cli", "微信消息状态：". var_export($return_status,true));
                                                }
                                            }else{
                                                add_log("socustom.log", "cli", "用户收货地址不存在：". var_export($custom_rsp, true));
                                                $custom->updCustomRanking($r_where,["status"=>2]);
                                                $msg_data = $user->wxExchangeFallMsg($cr_user_id,"您好，您的话费奖品兑换失败","收货地址未填写或填写错误","点击链接领取",HTTP_HOST.'/index/custom/orankinginfo.html?id='.$cra["id"]);
                                                $return_status = $weixin->send_user_message($msg_data);
                                                add_log("socustom.log", "cli", "微信消息状态：". var_export($return_status,true));
                                            }
                                        }
                                        
                                    }
                                }
                            }else{
                                add_log("socustom.log", "cli", $cra["id"]."发送奖品失败");
                            }
                        }
                    }else{
                        add_log("socustom.log", "cli", $value["id"]."排名不存在");
                    }
                    if($value["join_prizes_number"]>0){
                        $apply_sql = "SELECT * FROM `tb_official_custom_apply` where gameid=".$value["id"]." GROUP BY uid order by ranking limit ".$value["max_number"].",".$value["join_prizes_number"];
                        $oapply_list = $custom->getApplyListBySql($apply_sql);
                        if($oapply_list){
                            foreach ($oapply_list as $oal) {
                                $o_user_info = $user->getUserOne($oal["uid"]);
                                if($o_user_info["other_id"]){
                                    $msg_data["touser"] = $o_user_info["other_id"];
                                    $msg_data["template_id"] = "-IH8WVyI2Jk70Z5Je0CjO0li3A_8XjzgZLpW_qAggII";
                                    $msg_data["url"] = HTTP_HOST.'/index/custom/orankinginfo.html?ocustom_id='.$oal["gameid"];
                                    $msg_data["data"] = array(
                                        "first"=>array(
                                            "value"=>"恭喜您得了第{$oal["ranking"]}名",
                                            "color"=>""
                                        ),
                                        "keyword1"=>array(
                                            "value"=> date("Y-m-d H:i:s",$value["end_date"]),
                                            "color"=>""
                                        ),
                                        "keyword2"=>array(
                                            "value"=> $value["name"],
                                            "color"=>""
                                        ),
                                        "keyword3"=>array(
                                            "value"=> "第{$oal["ranking"]}名",
                                            "color"=>""
                                        ),
                                        "remark"=>array(
                                            "value"=>"点击查看奖券详情",
                                            "color"=>""
                                        )
                                    );
                                    $return_status = $weixin->send_user_message($msg_data);
                                    add_log("ocustom.log", "cli", "推送比赛参与奖排名微信消息状态：". var_export($return_status, true));
                                }else{
                                    add_log("ocustom.log", "cli", "用户openid不存在，不推送微信消息：". var_export($kan_user_info, true));
                                }
                            }
                        }
                    }
                    $cm_where["id"] = $custom_id;
                    $cm_data["is_send"] = 1;
                    $custom->updCustom($cm_where,$cm_data);
                }
            }
        }
    }
    
    /**
     * 官方赛事复制
     * @return boolean
     */
    public function gfcustom(){
        add_log("addocustom.log", "cli", "进入");
        //每天早上1:00执行，其它时间跳过
        $time_now = date('H:i:s');
        if($time_now < '01:00:00' || $time_now >= '01:30:00'){
            return false;
        }
        $custom = D("SwissOfficialCustom");
        $where["is_del"] = 0;
        $custom_list = $custom->getList($where);
        if($custom_list){
            add_log("addocustom.log", "cli", "比赛数据：". var_export($custom_list, true));
            foreach ($custom_list as $value) {
                if($value["start_date"]< time()){
                    $add_custom_data = $value;
                    $add_custom_data["status"] = 0;
                    $add_custom_data["is_send"] = 0;
                    $add_custom_data["cost_is_return"] = 0;
                    $his = date("H:i:s",$value["start_date"]);
                    $hjs = date("H:i:s",$value["end_date"]);
                    $ymd = date("Y-m-d",time());
                    $add_custom_data["start_date"] = strtotime($ymd." ".$his);
                    $add_custom_data["end_date"] = strtotime($ymd." ".$hjs);
                    unset($add_custom_data["id"]);
                    $custom_id=$custom->addCustom($add_custom_data);
                    add_log("addocustom.log", "cli", "复制比赛数据：". var_export($add_custom_data, true));
                    if($custom_id){
                        add_log("addocustom.log", "cli", "复制比赛数据成功：". $custom_id);
                        $extra = array(
                            'gametype' => array ('type' => 'int','size' => 4,'value' => $add_custom_data["game_id"]),
                            'gameid' => array ('type' => 'int','size' => 4,'value' => $custom_id),
                            'optype' => array ('type' => 'int','size' => 4,'value' => 1),
                            'roomid' => array ('type' => 'int','size' => 2,'value' => 197)
                        );
                        $response = Sockets :: call('call_back', 10, 501, $custom_id, $extra);
                        add_log("addocustom.log", "cli", "Socket返回数据". var_export($response, true));
                        $custom->updCustom(["id"=>$value["id"]],["is_del"=>1]);
                    }
                }
            }
        }
    }
}
