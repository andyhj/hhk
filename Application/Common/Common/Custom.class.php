<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Common;

use Common\WxApi\JsApiPay;
use Common\WxApi\class_weixin_adv;
use Common\Common\Sockets;
class Custom {
    public function delcustom($custom_id){
        $custom = D("Custom");
        $m_user = D("User");
        $model_order = D("Order");
        $c_where["is_del"] = 0;
        $c_where["id"] = $custom_id;
        $custom_info = $custom->getOne($c_where);  //比赛信息
        if(!$custom_info){
            return 11;  //比赛不存在
        }
        $user_id = 10001;
        $g_user_info = $m_user->getUserOne($user_id);
        $authkey = $g_user_info["authkey"];
        header("Content-Type:text/html; charset=utf-8");
        require_once APP_ROOT . "Application/Common/Concrete/wxapi/example/weixin.api.php";
        $weixin = new class_weixin_adv();
        $custom_ranking_list = $custom->getCustomRanking(["c_id"=>$custom_id]);  //获奖然元
        $custom_apply_list = $custom->getApplyList(["gameid"=>$custom_id]);  //参数人员
        $is_exchange = 0;
        if(!$custom_apply_list){  //如果没人参赛，直接删除比赛
            $custom_status = $custom->updCustom(["id"=>$custom_id],["is_del"=>1]);
            if($custom_status){
                if($custom_info["status"]!=2&&$custom_info["audit_status"]==1){
                    $sk_url = HTTP_HOST . '/api/game/updCustomSocket?uid='.$user_id."&authkey=".$authkey."&gametype=".$custom_info['game_id']."&gameid=".$custom_info['id']."&optype=2";
                    $r_status = file_get_contents($sk_url);
                    $r_status = json_decode($r_status, true);
                    add_log("delcustom.log", "api", "删除成功通知Socket服务：". var_export($r_status, true));
                }
                $code_where["u_id"] = $custom_info["u_id"];
                $code_where["c_id"] = $custom_info["id"];
                $code_data["u_id"] = 0;
                $code_data["c_id"] = 0;
                $custom->updCustomCode($code_where,$code_data);
                add_log("delcustom.log", "api", $custom_info["name"]." 没人参赛删除比赛");
                return 20;  //删除成功
            }else{
                return 13;  //删除失败
            }
        }else{
            $custom_apply_count = $custom->getApplyCount(["gameid"=>$custom_id]);
            if($custom_apply_count<$custom_info["number"]){  //如果参数人数不足预先设定人数，删除比赛，返还欢乐豆
                $custom_status = $custom->updCustom(["id"=>$custom_id],["is_del"=>1]);
                if($custom_status){
                    if($custom_info["status"]!=2&&$custom_info["audit_status"]==1){
                        $sk_url = HTTP_HOST . '/api/game/updCustomSocket?uid='.$user_id."&authkey=".$authkey."&gametype=".$custom_info['game_id']."&gameid=".$custom_info['id']."&optype=2";
                        $r_status = file_get_contents($sk_url);
                        $r_status = json_decode($r_status, true);
                        add_log("delcustom.log", "api", "删除成功通知Socket服务：". var_export($r_status, true));
                    }
                    $code_where["u_id"] = $custom_info["u_id"];
                    $code_where["c_id"] = $custom_info["id"];
                    $code_data["u_id"] = 0;
                    $code_data["c_id"] = 0;
                    $custom->updCustomCode($code_where,$code_data);
                    $tickets = $custom_info["tickets"]; //门票价格
                    if($tickets>0){
                        foreach ($custom_apply_list as $cal) {
                            $uid = $cal["uid"];
                            $bz_user_info = $m_user->getUserOne($uid);
                            $order_where["u_id"] = $uid;
                            $order_where["item_id"] = $custom_info["id"];
                            $order_where["status"] = 200;
                            $order_info = $model_order->getOneByWhere($order_where);
                            $url = HTTP_HOST . '/api/pay/wrefund?uid='.$uid."&authkey=".$bz_user_info["authkey"]."&order_number=".$order_info["order_number"]."&amount=".$order_info["amount"];
                            $r_data = file_get_contents($url);
                            $r_data = json_decode($r_data, true);
                            add_log("delcustom.log", "api", "删除比赛金豆返还{$uid}用户状态：". var_export($r_data, true));
                            if($r_data["status"]==200){
                                $title = $custom_info["name"]."比赛因人数不足，比赛报名费已全额退还到您的账户，请注意查收";
                                $keyword1 = $uid.time();
                                $keyword2 = $tickets;
                                $url = HTTP_HOST;
                                $msg_data = $m_user->wxTkMsg($uid,$title,$keyword1,$keyword2,$url);
                                $return_status = $weixin->send_user_message($msg_data);
                            }
                        }
                    }
                    add_log("delcustom.log", "api", $custom_info["name"]." 人数不足删除比赛");
                    return 20;  //删除成功
                }else{
                    return 13;  //删除失败
                }
            }else{
                if($custom_info["status"]==1){
                    return 14;  //比赛进行中不能删除
                }
                if($custom_info["status"]==2&&!$custom_info["is_send"]){
                    return 15;  //奖品发放中不能删除
                }
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
                if($is_exchange){ //用户奖品未领取完，删除比赛，返还欢乐豆
                    $custom_status = $custom->updCustom(["id"=>$custom_id],["is_del"=>1]);
                    if($custom_status){
                        if($custom_info["status"]!=2&&$custom_info["audit_status"]==1){
                            $sk_url = HTTP_HOST . '/api/game/updCustomSocket?uid='.$user_id."&authkey=".$authkey."&gametype=".$custom_info['game_id']."&gameid=".$custom_info['id']."&optype=2";
                            $r_status = file_get_contents($sk_url);
                            $r_status = json_decode($r_status, true);
                            add_log("delcustom.log", "api", "删除成功通知Socket服务：". var_export($r_status, true));
                        }
                        $code_where["u_id"] = $custom_info["u_id"];
                        $code_where["c_id"] = $custom_info["id"];
                        $code_data["u_id"] = 0;
                        $code_data["c_id"] = 0;
                        $custom->updCustomCode($code_where,$code_data);
                        $tickets = $custom_info["tickets"]; //门票价格
                        if($tickets>0){
                            foreach ($custom_apply_list as $cal) {
                                $uid = $cal["uid"];                            
                                $bz_user_info = $m_user->getUserOne($uid);
                                $order_where["u_id"] = $uid;
                                $order_where["item_id"] = $custom_info["id"];
                                $order_where["status"] = 200;
                                $order_info = $model_order->getOneByWhere($order_where);
                                $url = HTTP_HOST . '/api/pay/wrefund?uid='.$uid."&authkey=".$bz_user_info["authkey"]."&order_number=".$order_info["order_number"]."&amount=".$order_info["amount"];
                                $r_data = file_get_contents($url);
                                $r_data = json_decode($r_data, true);
                                add_log("delcustom.log", "api", "删除比赛金豆返还{$uid}用户状态：". var_export($r_data, true));
                                if($r_data["status"]==200){
                                     $title = $custom_info["name"]."比赛因奖品未发放完，比赛报名费已全额退还到您的账户，请注意查收";
                                    $keyword1 = $uid.time();
                                    $keyword2 = $tickets;
                                    $url = HTTP_HOST;
                                    $msg_data = $m_user->wxTkMsg($uid,$title,$keyword1,$keyword2,$url);
                                    $return_status = $weixin->send_user_message($msg_data);
                                }
                            }
                        }                        
                        add_log("delcustom.log", "api", $custom_info["name"]."用户奖品未领取完删除比赛");
                        return 20;  //删除成功
                    }else{
                        return 13;  //删除失败
                    }
                } else { //用户奖品已领取完，删除比赛，发放推广福利
                    $custom_status = $custom->updCustom(["id"=>$custom_id],["is_del"=>1]);
                    if($custom_status){
                        if($custom_info["status"]!=2&&$custom_info["audit_status"]==1){
                            $sk_url = HTTP_HOST . '/api/game/updCustomSocket?uid='.$user_id."&authkey=".$authkey."&gametype=".$custom_info['game_id']."&gameid=".$custom_info['id']."&optype=2";
                            $r_status = file_get_contents($sk_url);
                            $r_status = json_decode($r_status, true);
                            add_log("delcustom.log", "api", "删除成功通知Socket服务：". var_export($r_status, true));
                        }
                        $code_where["u_id"] = $custom_info["u_id"];
                        $code_where["c_id"] = $custom_info["id"];
                        $code_data["u_id"] = 0;
                        $code_data["c_id"] = 0;
                        $custom->updCustomCode($code_where,$code_data);
                        $welfare = $custom_info["welfare"]; //推广福利
                        if($welfare>0){
                            $model_award_earn = M("award_earn");
                            $model_award = M("award");
                            foreach ($custom_apply_list as $cal){
                                $uid = $cal["uid"];
                                $user_agency = $m_user->getUserAgencyByUserId($uid);
                                if($user_agency){
                                    $superior_id = $user_agency["superior_id"];
                                    if($superior_id){
                                        $ae_where["u_id"] = $superior_id;
                                        $ae_where["source"] = 4;
                                        $ae_where["add_date"] = strtotime(date("Ymd"));
                                        $award_earn_info = $model_award_earn->where($ae_where)->find();
                                        $wamount = 0;
                                        $ae_status = 0;
                                        if($award_earn_info){
                                            $wamount = $welfare+$award_earn_info["amount"];
                                            $ae_status = $model_award_earn->where($ae_where)->save(["amount"=>$wamount]);
                                        }else{
                                            $ae_data["u_id"] = $superior_id;
                                            $ae_data["amount"] = $welfare;
                                            $ae_data["source"] = 4;
                                            $ae_data["add_date"] = strtotime(date("Ymd"));
                                            $ae_status = $model_award_earn->add($ae_data);
                                        }
                                        if($ae_status){
                                            $title = "恭喜您获得".$welfare."元佣金收入";
                                            $msg_data = $m_user->wxFhMsg($superior_id,$title,"代理佣金","开心逗棋牌",HTTP_HOST."/index/user/withdrawal.html");
                                            $weixin->send_user_message($msg_data);
                                        }
                                    }
                                }
                            }
                            $a_sql = "SELECT u_id,SUM(amount) AS total FROM l_award_earn GROUP BY u_id"; //计算总收益
                            $award_earn_data = $model_award_earn->query($a_sql);
                            add_log("delcustom.log", "api", "计算总收益：". var_export($award_earn_data, true));
                            if(!empty($award_earn_data)){
                                foreach ($award_earn_data as $value) {
                                    $exist = $model_award->where(['u_id' => $value["u_id"]])->find();
                                    if($exist)
                                    {
                                        $upd_award_data["earn"] = $value["total"];
                                        $upd_award_data["amount"] = round(floatval($value["total"]-$exist["extract"]), 2);
                                        $model_award->where(['u_id' => $value["u_id"]])->save($upd_award_data);
                                    }else{
                                        $award_data["u_id"] = $value["u_id"];
                                        $award_data["earn"] = $value["total"];
                                        $award_data["extract"] = 0.00;
                                        $award_data["amount"] = $value["total"];
                                        $model_award->add($award_data);
                                    }
                                }
                            }
                        }
                        add_log("delcustom.log", "api", $custom_info["name"]." 用户奖品已领取完删除比赛");
                        return 20;  //删除成功
                    }else{
                        return 13;  //删除失败
                    }
                }
            }
        }
        
        return 13;  //删除失败
    }
}
