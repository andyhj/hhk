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
        $wx_config = get_js_sdk("",""); 
        $this->assign('wx_config',$wx_config);
        $this->assign('wdjh',U("index/plan/index"));
        $this->assign('grzx',U("index/user/index"));
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
//        $user_info_arr["u_id"]=464885;
        $db_config = C("DB_CONFIG2");
        $customer_m = M("customer_info",$db_config["DB_PREFIX"],$db_config);
        return $customer_m->where(["id"=>$user_info_arr["u_id"]])->find();
    }
}
