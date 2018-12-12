<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Api\Controller;

/**
 * Description of UserController
 *
 * @author Andy
 */
use Common\WxApi\JsApiPay;
use Common\WxApi\class_weixin_adv;
use Common\Common\WxAppLogin;
use Common\Common\Redis;

class UserController extends InitController {

    /**
     * 账号密码登陆
     */
    public function atlogin() {
        session(null);
        if (is_post) {  //判断是否post提交
            $username = delTrim(I("post.username")); //用户名
            $password = delTrim(I("post.password")); //密码
            if (!$username) {
                $json["status"] = 305;
                $json["info"] = "用户名不能为空";
                $this->ajaxReturn($json);
            }
            if (!$password) {
                $json["status"] = 306;
                $json["info"] = "密码不能为空";
                $this->ajaxReturn($json);
            }
            $m_user = D("user");
            $where["username"] = $username;
            $where["password"] = md5($username . $password);
            $user_info = $m_user->getUserOneByWhere($where);
            $auth_key = md5($username . time());
            if ($user_info) {
                if ($user_info["status"]) {
                    $json["status"] = 309;
                    $json["info"] = "用户被封";
                    $this->ajaxReturn($json);
                }
                $u_data["lastip"] = getIP();
                $u_data["lasttime"] = time();
                $u_data["authkey"] = $auth_key;
                $return_status = $m_user->where($where)->save($u_data);
                $return_status = $m_user->updGameUser($u_data, $where);
                if ($return_status) {
                    $l_data["u_id"] = $user_info["id"];
                    $l_data["intro"] = "用户登陆成功";
                    $l_data["add_date"] = time();
                    $l_data["reg_date"] = $user_info["regtime"];
                    $m_user->addUserLoginLog($l_data);

                    $return_data['u_id'] = $user_info['id'];
                    $return_data['auth_key'] = $auth_key;
                    $return_data['nickname'] = $user_info['nickname'];
                    $return_data['headurl'] = $user_info['headurl'];

                    $json["status"] = 200;
                    $json["info"] = "登陆成功";
                    $json["data"] = $return_data;
                    $this->ajaxReturn($json);
                } else {
                    $json["status"] = 310;
                    $json["info"] = "登陆失败";
                    $this->ajaxReturn($json);
                }
            }
        }
        $json["status"] = 309;
        $json["info"] = "非法提交";
        $this->ajaxReturn($json);
    }

    /**
     * 注册
     */
    public function register() {
        if (is_post()) {
            $m_user = D("user");
            $username = delTrim(I("post.username")); //用户名
            $nickname = delTrim(I("post.nickname")); //昵称
            $password = delTrim(I("post.password")); //密码
            $repassword = delTrim(I("post.repassword")); //再次输入密码
//            $email = delTrim(I("post.email")); //邮箱
            $recommend = delTrim(I("post.rec", session("rec"))); //推荐人
            if (!$username || !$password) {
                $json["status"] = 305;
                $json["info"] = "用户名、密码不能空";
                $this->ajaxReturn($json);
            }
            $n_preg = '/^[a-zA-Z0-9]{3,12}$/';
            if (!preg_match($n_preg, $username)) {
                $json["status"] = 306;
                $json["info"] = "用户名为3~12位的英文或数字";
                $this->ajaxReturn($json);
            }
//            if (!preg_match("/^13[0-9]{1}[0-9]{8}$|14[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$|177[0-9]{8}$/", $username)) {
//                $json["status"] = 306;
//                $json["info"] = "请输入正确的手机号";
//                $this->ajaxReturn($json);
//            }
            $user = $m_user->getUserOneByName($username);
            if ($user) {
                $json["status"] = 307;
                $json["info"] = "用户名已存在";
                $this->ajaxReturn($json);
            }
            $p_preg = '/[\x{4e00}-\x{9fa5}]/u';
            if (preg_match($p_preg, $password) || strlen($password) < 6 || strlen($password) > 12) {
                $json["status"] = 308;
                $json["info"] = "密码长度为6位至12位字符";
                $this->ajaxReturn($json);
            }
            if ($password != $repassword) {
                $json["status"] = 309;
                $json["info"] = "两次密码不一致";
                $this->ajaxReturn($json);
            }
//            if ($email) {
//                $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
//                if (!preg_match($pattern, $email)) {
//                    $json["status"] = 310;
//                    $json["info"] = "邮箱格式不符，请重新输入";
//                    $this->ajaxReturn($json);
//                }
//                $user_email = $m_user->getUserOneByEmail($email);
//                if ($user_email) {
//                    $json["status"] = 311;
//                    $json["info"] = "该邮箱已被绑定";
//                    $this->ajaxReturn($json);
//                }
//            }

            if ($recommend) {
                $user_info = $m_user->getUserOne($recommend);
                if (!$user_info) {
                    $json["status"] = 312;
                    $json["info"] = "推荐用户不存在";
                    $this->ajaxReturn($json);
                }
            }
            $word = C("WORD");
            if (isset($nickname)&&$nickname) {
                $tmpStr = json_encode($nickname);
                $tmpStr = preg_replace("#(\\\ud[0-9a-f]{3})|(\\\ue[0-9a-f]{3})#ie", "", $tmpStr); //将emoji的unicode置为空，其他不动  
                $nickname = delTrim(json_decode($tmpStr, true));
                $nickname=str_replace("'","",$nickname);
                
                if(in_array($nickname,$word)){
                    $json["status"] = 315;
                    $json["info"] = "昵称有敏感字存在！请重新输入";
                    $this->ajaxReturn($json);
                }
            }
            $auth_key = md5($username . time());
            $u_data["username"] = $username;
            $u_data["nickname"] = $nickname ? $nickname : $username;
            $u_data["password"] = md5($username . $password);
            //$u_data["email"] = $email;
            $u_data["regip"] = getIP();
            $u_data["recommend"] = $recommend;
            $u_data["authkey"] = $auth_key;
            $u_data["channel"] = 2;
            $user_id = $m_user->addUser($u_data);
            if ($user_id) {
                $json["status"] = 200;
                $json["info"] = "注册成功";
                $this->ajaxReturn($json);
            }
            $json["status"] = 313;
            $json["info"] = "注册失败";
            $this->ajaxReturn($json);
        }

        $json["status"] = 314;
        $json["info"] = "非法提交";
        $this->ajaxReturn($json);
    }

    /**
     * 找回密码
     */
    function findPassword() {
        if (is_post) {  //判断是否post提交
            $username = delTrim(I("post.username")); //用户名
            $email = delTrim(I("post.email")); //邮箱
            if (!$username || !$email) {
                $json["status"] = 305;
                $json["info"] = "用户名和邮箱不能为空";
                $this->ajaxReturn($json);
            }
            $m_user = D("user");
            $where["username"] = $username;
            $where["email"] = $email;
            $user_info = $m_user->getUserOneByWhere($where);
            if (!$user_info) {
                $json["status"] = 306;
                $json["info"] = "该帐号绑定的邮箱有误，请重新输入";
                $this->ajaxReturn($json);
            }
            $chars = array(
                "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
                "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
                "w", "x", "y", "z", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"
            );
            $password = get_rand_str(6, $chars);
            $title = "开心逗棋牌密码找回";
            $msg = "您在开心逗棋牌的帐号是{$username} 密码是：{$password}，请牢记!祝您游戏愉快!";
            $pass_word = md5($user_info["username"] . $password);
            $return_status = $m_user->updPassWord($pass_word, $where);
            if ($return_status) {
                sendMail($email, $title, $msg);
                $json["status"] = 307;
                $json["info"] = "找回密码成功";
                $this->ajaxReturn($json);
            } else {
                $json["status"] = 308;
                $json["info"] = "找回密码失败";
                $this->ajaxReturn($json);
            }
        }
        $json["status"] = 309;
        $json["info"] = "非法提交";
        $this->ajaxReturn($json);
    }

    /**
     * 更新结算账户
     */
    public function addBank() {
        $name = I("name", '');
        $card = I("card", '');
        $bank_str = I("bank", '');
        $province_str = I("province", '');
        $city_str = I("city", '');
        $branch_name = I("branch_name", '');
        if (!$name) {
            $json["status"] = 306;
            $json["info"] = "姓名不能为空";
            $this->ajaxReturn($json);
        }
        if (!$card) {
            $json["status"] = 307;
            $json["info"] = "卡号不能为空";
            $this->ajaxReturn($json);
        }
        if (!$bank_str) {
            $json["status"] = 308;
            $json["info"] = "开户行不能为空";
            $this->ajaxReturn($json);
        }
        if (!$province_str) {
            $json["status"] = 309;
            $json["info"] = "开户省份不能为空";
            $this->ajaxReturn($json);
        }
        if (!$city_str) {
            $json["status"] = 310;
            $json["info"] = "开户城市不能为空";
            $this->ajaxReturn($json);
        }
        if (!$branch_name) {
            $json["status"] = 311;
            $json["info"] = "开户行支行不能为空";
            $this->ajaxReturn($json);
        }
        $bank_arr = explode('-', $bank_str);
        $province_arr = explode('-', $province_str);
        $city_arr = explode('-', $city_str);
        $data["u_id"] = $this->user_info["id"];
        $data["name"] = $name;
        $data["card"] = $card;
        $data["bank_code"] = $bank_arr[0];
        $data["bank"] = $bank_arr[1];
        $data["province_code"] = $province_arr[0];
        $data["province"] = $province_arr[1];
        $data["city_code"] = $city_arr[0];
        $data["city"] = $city_arr[1];
        $data["branch_name"] = $branch_name;
        $user_model = D("User");
        $return_id = $user_model->addUserBank($data);
        if ($return_id) {
            $json["status"] = 200;
            $json["info"] = "更新成功";
            $this->ajaxReturn($json);
        }
        $json["status"] = 312;
        $json["info"] = "更新失败";
        $this->ajaxReturn($json);
    }

    public function wxlogin() {
        $m_user = D("user");
        $code = I("post.code");
        add_log("user.log", "api", "code:" . var_export(I("post.code"), true));
        header("Content-Type:text/html; charset=utf-8");
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/WxPay.JsApiPay.php";
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/weixin.api.php";

        $tools = new WxAppLogin();
        $openId = $tools->getOpenidFromMp($code);
        $wx_data = [];
        if ($openId) {
            $wx_data = $tools->getUserInfo();
        }
        add_log("user.log", "api", "OpenID:" . var_export($openId, true));
        add_log("user.log", "api", "WXUserInfo:" . var_export($wx_data, true));
        if (!$openId || $openId == "" || !$wx_data || empty($wx_data)) {
            $json["status"] = 305;
            $json["info"] = "微信参数错误";
            $this->ajaxReturn($json);
        }
        $data = $wx_data;
        $nickname = '';     //用户昵称
        if (isset($data["nickname"])) {
            $tmpStr = json_encode($data['nickname']);
            $tmpStr = preg_replace_callback("#(\\\ud[0-9a-f]{3})|(\\\ue[0-9a-f]{3})#ie", "", $tmpStr); //将emoji的unicode置为空，其他不动  
            $nickname = delTrim(json_decode($tmpStr, true));
        }
        $other_id = delTrim($data["openid"]); //第三方ID
        $other_type = 1; //第三方类型(1微信，2QQ，3支付宝)
        $gender = isset($data["sex"]) ? $data["sex"] : 0;                  //性别
        $headurl = isset($data["headimgurl"]) ? $data["headimgurl"] : '';  //头像
        $province = isset($data["province"]) ? $data["province"] : '';     //地区
        $city = isset($data["city"]) ? $data["city"] : '';
        $unionid = isset($data["unionid"]) ? $data["unionid"] : '';                  //城市
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
        $user_data["recommend"] = 0;
        $user_data["headurl"] = $headurl;
        $user_data["province"] = $province;
        $user_data["city"] = $city;
        $user_data["channel"] = 2;
        $user_data["unionid"] = $unionid;
        if ($user_info) {
            if ($user_info["status"]) {
                $json["status"] = 307;
                $json["info"] = "用户被封";
                $this->ajaxReturn($json);
            }
            $user_data["lastip"] = getIP();
            $user_data["lasttime"] = time();
            $user_data["plat_uid"] = $user_info["id"];
            $user_data["regtime"] = $user_info["regtime"];
            $return_status = $m_user->updUser($user_data, $where);
            if ($return_status) {
                $l_data["u_id"] = $user_info["id"];
                $l_data["intro"] = "用户登陆成功";
                $l_data["add_date"] = time();
                $l_data["reg_date"] = $user_info["regtime"];
                $m_user->addUserLoginLog($l_data);

                $return_data['u_id'] = $user_info['id'];
                $return_data['auth_key'] = $auth_key;
                $return_data['nickname'] = $user_info['nickname'];
                $return_data['headurl'] = $user_info['headurl'];

                $json["status"] = 200;
                $json["info"] = "登陆成功";
                $json["data"] = $return_data;

                add_log("user.log", "api", "return_data:" . var_export($json, true));

                $this->ajaxReturn($json);
            } else {
                $json["status"] = 306;
                $json["info"] = "登陆失败";
                $this->ajaxReturn($json);
            }
        }

        $user_id = $m_user->addUser($user_data);
        if ($user_id) {
            $l_data["u_id"] = $user_id;
            $l_data["intro"] = "用户登陆成功";
            $l_data["add_date"] = time();
            $l_data["reg_date"] = time();
            $m_user->addUserLoginLog($l_data);

            $return_data['u_id'] = $user_id;
            $return_data['auth_key'] = $auth_key;
            $return_data['nickname'] = $nickname;
            $return_data['headurl'] = $headurl;
            $json["status"] = 200;
            $json["info"] = "登陆成功";
            $json["data"] = $return_data;

            add_log("user.log", "api", "return_data:" . var_export($json, true));

            $this->ajaxReturn($json);
        }
        $json["status"] = 306;
        $json["info"] = "登陆失败";
        $this->ajaxReturn($json);
    }

    /**
     * 绑定手机号码
     */
    public function binding() {
        $user_id = $this->user_id;
        $mobile = I("mobile", 0); //手机号码
        $code = I("code", 0); //验证码
        if (!$mobile || !$code) {
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $m_user = D("User");
        $m_redis = new Redis();
        $user_code = $m_redis->get("sms" . $mobile);
        if (!$user_code) {
            $json["status"] = 306;
            $json["info"] = "验证码已过期，请重新发送";
            $this->ajaxReturn($json);
        }
        if ($code != $user_code) {
            $json["status"] = 307;
            $json["info"] = "验证码错误";
            $this->ajaxReturn($json);
        }
        $user_info = $m_user->where(["phone" => $mobile])->find();
        if ($user_info) {
            $json["status"] = 309;
            $json["info"] = "此号码已绑定其它账号";
            $this->ajaxReturn($json);
        }
        $user_infos = $m_user->getUserOne($user_id);
        $u_data["phone"] = $mobile;
        $u_where["id"] = $user_id;
        $r_status = $m_user->where($u_where)->save($u_data);
        $r_status = $m_user->updGameUser($u_data, ["uid" => $user_id]);
        if ($r_status) {
            if (!$user_infos["phone"]) {
                $db_config = C("DB_CONFIG2");
                $mail_data["uid"] = $user_id;
                $mail_data["title"] = "手机号绑定成功";
                $mail_data["sender"] = "系统";
                $mail_data["sendtime"] = time();
                $mail_data["describe"] = "您已成功绑定手机号码，系统赠送您5000开心豆，请及时领取！";
                $mail_data["coin"] = 5000;
                $mail_data["awardnum"] = 0;
                $maill_model = M("user_mail", $db_config["DB_PREFIX"], $db_config);
                $maill_model->add($mail_data);
            }
            $json["status"] = 200;
            $json["info"] = "绑定成功";
            $this->ajaxReturn($json);
        }
        $json["status"] = 308;
        $json["info"] = "绑定失败";
        $this->ajaxReturn($json);
    }

    public function phonelogin() {
        $mobile = I("post.mobile", 0); //手机号码
        $code = I("post.code", 0); //验证码
        if (!$mobile || !$code) {
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $m_user = D("User");
        $m_redis = new Redis();
        $user_code = $m_redis->get("sms" . $mobile);
        if (!$user_code) {
            $json["status"] = 306;
            $json["info"] = "验证码已过期，请重新发送";
            $this->ajaxReturn($json);
        }
        if ($code != $user_code) {
            $json["status"] = 307;
            $json["info"] = "验证码错误";
            $this->ajaxReturn($json);
        }
        $where["phone"] = $mobile;
        $user_info = $m_user->where($where)->find();
        if (!$user_info) {
            $json["status"] = 308;
            $json["info"] = "用户不存在";
            $this->ajaxReturn($json);
        }
        $other_id = ""; //第三方ID
        $other_type = 0; //第三方类型(1微信，2QQ，3支付宝)
        $gender = 0;                  //性别
        $headurl = '';  //头像
        $province = '';     //地区
        $city = '';
        $unionid = '';                  //城市
        $auth_key = md5($mobile . time());
        $nickname = get_rand_str(6);
        $user_data["username"] = "";
        $user_data["nickname"] = $nickname;
        $user_data["password"] = "";
        $user_data["email"] = "";
        $user_data["gender"] = $gender;
        $user_data["other_id"] = $other_id;
        $user_data["other_type"] = $other_type;
        $user_data["regip"] = getIP();
        $user_data["authkey"] = $auth_key;
        $user_data["recommend"] = 0;
        $user_data["headurl"] = $headurl;
        $user_data["province"] = $province;
        $user_data["city"] = $city;
        $user_data["channel"] = 2;
        $user_data["unionid"] = $unionid;
        $user_data["lastip"] = getIP();
        $user_data["lasttime"] = time();
        if ($user_info) {
            if ($user_info["status"]) {
                $json["status"] = 309;
                $json["info"] = "用户被封";
                $this->ajaxReturn($json);
            }
            $u_data["lastip"] = getIP();
            $u_data["lasttime"] = time();
            $u_data["authkey"] = $auth_key;
            $return_status = $m_user->where($where)->save($u_data);
            $return_status = $m_user->updGameUser($u_data, $where);
            if ($return_status) {
                $l_data["u_id"] = $user_info["id"];
                $l_data["intro"] = "用户登陆成功";
                $l_data["add_date"] = time();
                $l_data["reg_date"] = $user_info["regtime"];
                $m_user->addUserLoginLog($l_data);

                $return_data['u_id'] = $user_info['id'];
                $return_data['auth_key'] = $auth_key;
                $return_data['nickname'] = $user_info['nickname'];
                $return_data['headurl'] = $user_info['headurl'];

                $json["status"] = 200;
                $json["info"] = "登陆成功";
                $json["data"] = $return_data;
                $this->ajaxReturn($json);
            } else {
                $json["status"] = 310;
                $json["info"] = "登陆失败";
                $this->ajaxReturn($json);
            }
        }

        $user_id = $m_user->addUser($user_data);
        if ($user_id) {
            $l_data["u_id"] = $user_id;
            $l_data["intro"] = "用户登陆成功";
            $l_data["add_date"] = time();
            $l_data["reg_date"] = time();
            $m_user->addUserLoginLog($l_data);

            $return_data['u_id'] = $user_id;
            $return_data['auth_key'] = $auth_key;
            $return_data['nickname'] = $nickname;
            $return_data['headurl'] = $headurl;
            $json["status"] = 200;
            $json["info"] = "登陆成功";
            $json["data"] = $return_data;
            $this->ajaxReturn($json);
        }
        $json["status"] = 310;
        $json["info"] = "登陆失败";
        $this->ajaxReturn($json);
    }
    /**
     * 用户信息
     */
    public function userinfo(){
        $user_id = $this->user_id;
        $model_user = D("User");
        $user_info = $model_user->getUserOne($user_id);
        if(!$user_info){
            $json["status"] = 305;
            $json["info"] = "用户不存在";
            $this->ajaxReturn($json);
        }
        unset($user_info["password"]);
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $user_info;
        $this->ajaxReturn($json);
    }
    /**
     * 用户信息
     */
    public function userdes(){
        $user_id = I("user_id");
        if(!$user_id){
            $json["status"] = 306;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $model_user = D("User");
        $user_info = $model_user->getUserOne($user_id);
        if(!$user_info){
            $json["status"] = 305;
            $json["info"] = "用户不存在";
            $this->ajaxReturn($json);
        }
        unset($user_info["password"]);
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $user_info;
        $this->ajaxReturn($json);
    }

    public function auth(){
        $user_id = $this->user_id;
        $model_user = M("user");
        $name = I("name");
        $card_id = I("card_id");
        if(!$name||!$card_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        if(_strlen($name)<4||_strlen($name)>10){
            $json["status"] = 308;
            $json["info"] = "请填写正确的姓名";
            $this->ajaxReturn($json);
        }
        if(strlen($card_id)!=18){
            $json["status"] = 307;
            $json["info"] = "请填写正确的身份证号码";
            $this->ajaxReturn($json);
        }
        $where["id"] = $user_id;
        $data["is_auth"] = 1;
        $data["name"] = $name;
        $data["card_id"] = $card_id;
        $r_status = $model_user->where($where)->save($data);
        if($r_status){
            $json["status"] = 200;
            $json["info"] = "成功";
            $this->ajaxReturn($json);
        }
        $json["status"] = 306;
        $json["info"] = "失败";
        $this->ajaxReturn($json);
    }
    /**
     * 好友列表
     */
    public function friend(){
        $user_id = $this->user_id;
        $model_user = D("User");
        $user_friend = $model_user->getUserAgencySubordinates(["parent_id"=>$user_id]);
        $friend_arr=[];
        if($user_friend&&!empty($user_friend)){
            foreach ($user_friend as $value) {
                $user_info = $model_user->getUserOne($value["u_id"]); //用户信息
                $down["id"] = $user_info["id"];
                $down["headurl"] = $user_info["headurl"]; //用户头像
                $down["nickname"] = $user_info["nickname"]; //用户昵称
                $down["regtime"] = $user_info["regtime"];  //初次注册时间
                $friend_arr[] = $down;
            }
        }
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $friend_arr;
        $this->ajaxReturn($json);
    }
    
     /**
     * 用户信息
     */
    public function userGameInfo(){
        $user_id = I("user_id");
        $type = I("type");
        $model_user = D("User");
        $db_config = C("DB_CONFIG2");
        $result_model = M("user_result",$db_config["DB_PREFIX"],$db_config);
        $user_info = $model_user->getGameUserOne(["uid"=>$user_id]);
        if(!$user_info){
            $json["status"] = 305;
            $json["info"] = "用户不存在";
            $this->ajaxReturn($json);
        }
        unset($user_info["password"]);
        $result_info = $result_model->where(["uid"=>$user_id,"gametype"=>$type])->find();
        $matchInfo = [];
        if($result_info&&!empty($result_info)){
            $matchInfo = $result_info;
        }
        $data = array('info'=>$user_info,'matchInfo'=>$matchInfo);
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $data;
        $this->ajaxReturn($json);
    }
}
