<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\AuthModel;
class InitController extends Controller{
    protected $http='http://';
    public function __construct() {
        parent::__construct();
//        $headers = $this->getHeaders();
//        $token = $headers["MALL-TOKEN"];
//        $code = $headers["MALL-CODE"];
//        $obj_auth = new AuthModel();
//        if(!$token || !$code || !$obj_auth->check($token, $code)){
//            $this->ajaxReturn(['status'=>'300','info'=>'没有权限']);
//        }
        if(!empty($_SERVER["HTTPS"])&&$_SERVER["HTTPS"]='on'){
            $this->http='https://';
        }
//        print_r(count(explode(".", $_SERVER["SERVER_NAME"])));die();
        $wx_config = get_js_sdk("wxcef870e2241d618d","2357e42b795d3c0c3d1fa3a5cfdb394c"); 
        $this->assign('wx_config',$wx_config);
    }

    public function getHeaders(){
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if ('HTTP_' == substr($key, 0, 5)) {
                $headers[str_replace('_', '-', substr($key, 5))] = $value;
            }
        }
        return $headers;
    }
    public function getRawBody()
    {
        $str_raw_body = file_get_contents('php://input');
        $arr_raw_body = json_decode($str_raw_body,true);
        if($arr_raw_body){
            return $arr_raw_body;
        }
        return $str_raw_body;
    }

    /**
     * 获取登录用户信息
     */
    public function getUserInfo(){
        $user_info = session("userInfo");
        if(!$user_info){
            return false;
        }
        $user_info_arr = json_decode($user_info, true);
//        $user_info_arr["u_id"]=100170;
        $model_user = D("user");
        return $model_user->getUserOne($user_info_arr["u_id"]);
    }
}
