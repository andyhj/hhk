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

        $db_config = C("DB_CONFIG2");
        $customer_m = M("customer_info",$db_config["DB_PREFIX"],$db_config);
        $cunstomer_wx_binding_m = M("cunstomer_wx_binding",$db_config["DB_PREFIX"],$db_config);
        $openid_bind_m = M("openid_bind",$db_config["DB_PREFIX"],$db_config);
        $customer_bankaccount_m = M("customer_bankaccount",$db_config["DB_PREFIX"],$db_config);
        $rc_user_info=[]; //推荐用户信息
        if ($recommend) {
            $rc_user_info = $customer_m->where(["id"=>$recommend])->find(); 
            if (!$rc_user_info) {
                return 111;
            }
        }
        $tools = new JsApiPay();
        $weixin = new class_weixin_adv();
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
//            $tmpStr = json_encode($data['nickname']);
//            $tmpStr = preg_replace_callback("#(\\\ud[0-9a-f]{3})|(\\\ue[0-9a-f]{3})#ie", "", $tmpStr); //将emoji的unicode置为空，其他不动  
//            $nickname = delTrim(json_decode($tmpStr, true));
//            $nickname=str_replace("'","",$nickname);
            $nickname=$this->filterEmoji($data["nickname"]);
        }
        $openid = delTrim($data["openid"]); //第三方ID
        $other_type = 1; //第三方类型(1微信，2QQ，3支付宝)
        $gender = isset($data["sex"]) ? $data["sex"] : 0;                  //性别
        $headurl = isset($data["headimgurl"]) ? $data["headimgurl"] : '';  //头像
        $province = isset($data["province"]) ? $data["province"] : '';     //地区
        $city = isset($data["city"]) ? $data["city"] : '';                 //城市
        $unionid = isset($data["unionid"]) ? $data["unionid"] : '';         
        $cunstomer_wx_binding_info = $cunstomer_wx_binding_m->where(["open_id"=>$openid])->find(); //查询有没微信登陆用户
        if($cunstomer_wx_binding_info&&!empty($cunstomer_wx_binding_info)&&$cunstomer_wx_binding_info["user_id"]){
            $hsq_user_info = $customer_m->where(["id"=>$cunstomer_wx_binding_info["user_id"]])->find(); 
            if (!$hsq_user_info||!$hsq_user_info['auditstate']) {
                return 113;
            }
            $state = 0;
            if ($data["subscribe"]) { //是否关注
                $state = 1;
            }
            $user_info = M('user')->where(['u_id'=>$cunstomer_wx_binding_info["user_id"]])->find();
            if(!$user_info){
                $customer_bankaccount = $customer_bankaccount_m->where(['userId'=>$cunstomer_wx_binding_info["user_id"]])->find();
                $user_data['u_id'] = $cunstomer_wx_binding_info["user_id"];
                $user_data['superior_id'] = $hsq_user_info["agentsid"];
                $user_data['login_id'] = $hsq_user_info["loginid"];
                $user_data['password'] = $hsq_user_info["password"];
                $user_data['u_name'] = $hsq_user_info["name"];
                $user_data['id_card'] = $hsq_user_info["idcard"];
                $user_data['name'] = $customer_bankaccount["accountname"];
                $user_data['open_id'] = $openid;
                $user_data['wx_tx'] = $headurl;
                $user_data['wx_name'] = $nickname;
                M('user')->add($user_data);
            }else{
                $user_data['open_id'] = $openid;
                $user_data['wx_tx'] = $headurl;
                $user_data['wx_name'] = $nickname;
                $user_data['lasttime'] = date('Y-m-d H:i:s');
                M('user')->where(['u_id'=>$cunstomer_wx_binding_info["user_id"]])->save($user_data);
            }
            $wx_bd_data["state"] = $state;
            $wx_bd_data["wx_tx"] = $headurl;
            $wx_bd_data["wx_name"] = $nickname;
            $wx_bd_data["wx_dz"] = $city;
            $cunstomer_wx_binding_m->where(["open_id"=>$openid])->save($wx_bd_data);
            $session['u_id'] = $cunstomer_wx_binding_info['user_id'];
            $session['portrait'] = $headurl;
            $session['nickname'] = $nickname;
            $session['open_id'] = $openid;
            session('userInfo', json_encode($session));
            return 200;
        }else{
            $openid_bind_info = $openid_bind_m->where(["openid"=>$openid])->find(); //查询绑定上级状态
            $scene_value = C("default_scene_value");
            if(empty($openid_bind_info)){
                if($recommend){
                    $scene_value = $recommend;
                }
                if($scene_value){
                    $obj = array(
                        'openid'=>$_SESSION['openid'],
                        'scene_value'=>$scene_value,
                    );
                    $openid_bind_info = $obj;
                    $obj['created'] = $obj['updated'] = date('Y-m-d H:i:s');
                    $openid_bind_m->add($obj);
                }
            }
            session('openidBind', json_encode($openid_bind_info));
            return 113;
        }
    }
    private function filterEmoji($str)
    {
      $str = preg_replace_callback( '/./u',
          function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
          },
          $str);
       return $str;
    }
}
