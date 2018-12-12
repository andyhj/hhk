<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Controller;

use Common\WxApi\JsApiPay;
use Common\WxApi\class_weixin_adv;
use Common\Common\Sockets;
use Common\Common\WxH5Login;
class ShareController extends InitController {
    private $user_info;
    public function __construct() {
        parent::__construct();
        $recommend = delTrim(I("rec",0)); //推荐人
        $roomid = delTrim(I("roomid",0));
        $roomcode = delTrim(I("roomcode",0));
        $gameType = delTrim(I("gametype",0));
        $modetype = delTrim(I("modetype",0));
        $this->user_info = $this->getUserInfo();
        $wxh5login = new WxH5Login();
        $return_status = $wxh5login->wxLogin($recommend);
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

        if($modetype==3){
            if ($return_status === 113) {
                $url = $this->http . $_SERVER['HTTP_HOST'] . '/index/custom/code.html?id='.$roomid;
                header('Location: ' . $url);
                die();
            }else{
                $url = $this->http . $_SERVER['HTTP_HOST'] . '/index/index/custom.html?custom_id='.$roomid;
                header('Location: ' . $url);
                die();
            }
        }
        $url = $this->http . $_SERVER['HTTP_HOST'] . '/' . $recommend . '-' . $roomid . '-' . $roomcode . '-' . $gameType . '-' . $modetype . '.html';
        if($roomid&&$roomcode&&$return_status==200){
            header('Location: ' . $url);die();
        }
        $this->assign('return_status', $return_status);
        $this->assign('game_login_url', $url);
        $this->assign('userInfo', $this->user_info);
        $this->assign('wx_share_url', $this->http . $_SERVER['HTTP_HOST'] . '/s/' . $this->user_info["id"] . '-0-0-0-0.html');
    }
    public function index(){
        $this->display();
    }
}
