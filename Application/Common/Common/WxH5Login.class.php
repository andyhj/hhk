<?php
namespace Common\Common;

use Common\WxApi\JsApiPay;
use Common\WxApi\class_weixin_adv;
use Common\Common\Sockets;
class WxH5Login {
    /**
     * 微信登陆
     * 返回状态码描述
     * 111 推荐用户不存在
     * 112 登陆失败
     * 113 用户未关注公众号
     * 130 账号被封号
     * 200 成功
     */
    public function wxLogin($recommend = "") {
        header("Content-Type:text/html; charset=utf-8");
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/WxPay.JsApiPay.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/weixin.api.php";

        $m_user = D("user");
        $rc_user_info=[]; //推荐用户信息
        if ($recommend) {
            $rc_user_info = $m_user->getUserOne($recommend);
            if (!$rc_user_info) {
                return 111;
            }
        }
        $tools = new JsApiPay();
        $weixin = new class_weixin_adv();
        //$openId = session("userOpenId");
        $wx_data = [];
//        if (!$openId || $openId == "") {
//            $openId = $tools->GetOpenid();
//            //\Think\Log::write('获取OpenId：'.$openId,'WARN');
//            if ($openId) {
//                session('userOpenId', $openId);
//                $wx_data = $tools->getUserInfo();
//                add_log("wxlogin.log", "home", "微信用户数据1：". var_export($wx_data, true));
//            }
//        } else {
//            // \Think\Log::write('已存在OpenId：'.$openId,'WARN');
//            // file_put_contents('aa.log','已存在OpenId：'.$openId,FILE_APPEND);
//        }
        $openId = $tools->GetOpenid();
        if ($openId) {
            $wx_data = $tools->getUserInfo();
            add_log("wxlogin.log", "home", "微信用户数据1：". var_export($wx_data, true));
        }else{
            return 112;
        }
        
        if(empty($wx_data)){
            $data = $weixin->get_user_info($openId);
        }else{
            $wx_info_data = $weixin->get_user_info($openId);
                add_log("wxlogin.log", "home", "微信用户数据2：". var_export($wx_info_data, true));
            $wx_data["subscribe"] = $wx_info_data["subscribe"];
            $wx_data["subscribe_time"] = $wx_info_data["subscribe_time"];
            $wx_data["remark"] = $wx_info_data["remark"];
            $wx_data["groupid"] = $wx_info_data["groupid"];
            $wx_data["tagid_list"] = $wx_info_data["tagid_list"];
            $data = $wx_data;
        }
        //add_log("wxlogin.log", "home", "微信用户数据2：". var_export($data, true));
        if (!$data || empty($data)) {
            return 112;
        }
        $nickname = '';     //用户昵称
        if (isset($data["nickname"])) {
            $tmpStr = json_encode($data['nickname']);
            $tmpStr = preg_replace_callback("#(\\\ud[0-9a-f]{3})|(\\\ue[0-9a-f]{3})#ie", "", $tmpStr); //将emoji的unicode置为空，其他不动  
            $nickname = delTrim(json_decode($tmpStr, true));
            $nickname=str_replace("'","",$nickname);
        }
        $other_id = delTrim($data["openid"]); //第三方ID
        $other_type = 1; //第三方类型(1微信，2QQ，3支付宝)
        $gender = isset($data["sex"]) ? $data["sex"] : 0;                  //性别
        $headurl = isset($data["headimgurl"]) ? $data["headimgurl"] : '';  //头像
        $province = isset($data["province"]) ? $data["province"] : '';     //地区
        $city = isset($data["city"]) ? $data["city"] : '';                 //城市
        $unionid = isset($data["unionid"]) ? $data["unionid"] : '';         
        $where["unionid"] = $unionid;
        $where["other_type"] = $other_type;
        $user_info = $m_user->getUserOneByWhere($where);
        $auth_key = md5($other_id . time());
        $user_data["username"] = "";
        $user_data["nickname"] = $nickname;
        $user_data["password"] = "";
        $user_data["email"] = "";
        $user_data["gender"] = $gender;
        $user_data["other_id"] = $other_id;
        $user_data["other_type"] = $other_type;
        $user_data["regip"] = getIP();
        $user_data["authkey"] = $auth_key;
        $user_data["recommend"] = $recommend;
        $user_data["headurl"] = $headurl;
        $user_data["province"] = $province;
        $user_data["city"] = $city;
        $user_data["lastip"] = getIP();
        $user_data["lasttime"] = time();
        $user_data["unionid"] = $unionid;
        $user_data["is_msg"] = $data["subscribe"]?1:0;
        if ($user_info) {
            if ($user_info["status"]) {
                return 130;
            }
            $user_data["plat_uid"] = $user_info["id"];
            $user_data["regtime"] = $user_info["regtime"];
            $return_status = $m_user->updUser($user_data, $where);
            if ($return_status) {
                $l_data["u_id"] = $user_info["id"];
                $l_data["intro"] = "用户登陆成功";
                $l_data["add_date"] = time();
                $l_data["reg_date"] = $user_info["regtime"];
                $m_user->addUserLoginLog($l_data);

                $game_where["plat_uid"] = $user_info["id"];
                //$game_user_info = $m_user->getGameUserOne($game_where);
                $session['game_uid'] = $user_info["id"];
                $session['u_id'] = $user_info['id'];
                $session['u_name'] = $user_info['username'];
                $session['u_email'] = $user_info['email'];
                $session['auth_key'] = $auth_key;
                $session['other_id'] = $user_info['other_id'];
                $session['other_type'] = $user_info['other_type'];
                session('userInfo', json_encode($session));
                if (!$data["subscribe"]) {
                    return 113;
                } else {
                    return 200;
                }
            } else {
                return 112;
            }
        }

        $user_id = $m_user->addUser($user_data);
        if ($user_id) {
            //新用户并关注了推送消息
            if($data["subscribe"]){
                $msg_data = $m_user->subscribeMsg($user_id);
                $weixin->send_user_message($msg_data);
            }
            $l_data["u_id"] = $user_id;
            $l_data["intro"] = "用户登陆成功";
            $l_data["add_date"] = time();
            $l_data["reg_date"] = time();
            $m_user->addUserLoginLog($l_data);

            $game_where["plat_uid"] = $user_id;
            //$game_user_info = $m_user->getGameUserOne($game_where);
            $session['game_uid'] = $user_id;
            $session['u_id'] = $user_id;
            $session['u_name'] = "";
            $session['u_email'] = "";
            $session['auth_key'] = $auth_key;
            $session['other_id'] = $other_id;
            $session['other_type'] = $other_type;
            session('userInfo', json_encode($session));
            if (!$data["subscribe"]) {
                return 113;
            } else {
                return 200;
            }
        }
        return 112;
    }
    private function updAgency($user_id) {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/weixin.api.php";
        $m_user = D("user");
        $weixin = new class_weixin_adv();
        $user_agency_info = $m_user->getUserAgencyByUserId($user_id);
        $grade = $user_agency_info["grade"];
        $return_status = $m_user->updUserAgency($user_id);
        if ($return_status === 2) {
            $state = "您的代理等级从".$m_user->getLevelText($grade)."升级为".$m_user->getLevelText($grade+1);
            $msg_data = $m_user->agencyMsg($user_id,$state);
            $return_status = $weixin->send_user_message($msg_data);
            $this->updAgency($user_agency_info["superior_id"]);
        }
    }
}
