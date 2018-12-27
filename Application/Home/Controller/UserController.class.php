<?php

namespace Home\Controller;

use Common\Common\WxH5Login;
class UserController extends InitController {
    private $user_info;
    private $user_wx_info;
    public function __construct() {
        header("Content-type: text/html; charset=utf-8"); 
        parent::__construct();
        $recommend = delTrim(I("rec", session("rec"))); //推荐人
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
//            $url = HSQ_HOST. '/mobile/perfect_info/registered';
            $url = HSQ_HOST. '/mobile/binding/new_binding';
            if ($return_status === 113) {
                header('Location: ' . $url);
                die();
            }
        }
        $db_config = C("DB_CONFIG2");
        $customer_wx_m = M("cunstomer_wx_binding",$db_config["DB_PREFIX"],$db_config);
        $wx = $customer_wx_m->where(["user_id"=>$this->user_info["id"]])->find();
//        if(!$wx["state"]){
//            echo '<script>alert("请先关注会收钱公众号");</script>';
//            die();
//        }
        $this->user_wx_info = $wx;
        $this->assign('is_gr',1);
        $this->assign('userInfo', $this->user_info);
        $this->assign('wx_share_url', $this->http . $_SERVER['HTTP_HOST'] . '/' . $this->user_info["id"] . '-0-0-0-0.html');
    }
    public function index(){
        $info = $this->user_info;
        $user_vip_model = M("user_vip");
        $wx = $this->user_wx_info;
        $user_vip_info = $user_vip_model->where(["u_id"=>$info["id"]])->find();
        $is_plus = 0;
        $dq_date = "";
        //判断是否plus会员
        if($user_vip_info && strtotime($user_vip_info["end_time"])> time()){
            $is_plus = 1;
            $dq_date = $user_vip_info["end_time"];
        }
        $rows = array(
            'tx' => $wx['wx_tx'],
            'name' => $info['name'],
            'id' => $info['id'],
            'is_plus' => $is_plus,
            'dq_date' => $dq_date
        );
        $this->assign('rows',$rows);
        $this->display();
    }
}
