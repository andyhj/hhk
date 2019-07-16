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

    public function updatewx(){
        $db_config = C("DB_CONFIG2");
        $cunstomer_wx_binding_m = M("cunstomer_wx_binding",$db_config["DB_PREFIX"],$db_config);
        $user=M("user")->find();
        $cunstomer_wx_binding=$cunstomer_wx_binding_m->find();
        dump($cunstomer_wx_binding);
        dump($user);

        $this->display();
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
        $user_des = $customer_m->where(["id"=>$user_info_arr["u_id"]])->find();
        if($user_des&&!empty($user_des)){
            $user_vip_log_m = M("user_vip_log");
            $user_vip_log_info = $user_vip_log_m->where(["u_id"=>$user_des["id"],"type"=>1])->find();
            if(!$user_vip_log_info&&empty($user_vip_log_info)){
                $user_vip_log_data["u_id"] = $user_des["id"];
                $user_vip_log_data["add_time"] = time();
                $user_vip_log_data["end_time"] = strtotime("+1 month");
                $s=$user_vip_log_m->add($user_vip_log_data);
                if($s){
                    $user_m = D('User');
                    $user_m->wxMessagewxYwlcMsg($user_des["id"],'恭喜您获得《会还款》一个月PLUS会员','系统赠送《会还款》一个月PLUS会员',date("Y-m-d H:i:s"),'请尽快领取','点击领取','',HTTP_HOST.'/index/user/plusdes.html');
                }
            }
        }
        return $user_des;
    }
    protected function returnJson($data,$session_name=""){
        if($session_name){
            session($session_name, null);
        }
        $this->ajaxReturn($data);
    }
}
