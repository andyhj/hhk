<?php
namespace Api\Controller;
use Think\Controller;
class InitController extends Controller{
    protected $http='http://';
    protected $user_info;
    protected $user_id;
    public function __construct() {
        header("Access-Control-Allow-Origin: *");
        parent::__construct();
        $ip = getIP();
        if(!empty($_SERVER["HTTPS"])&&$_SERVER["HTTPS"]='on'){
            $this->http='https://';
        }
        if(strpos($_SERVER["REQUEST_URI"],"wxlogin")){
            $code="DUAB85CAD427CHISDN365DSC";
            $post_code = I("post.code");
            $post_sign = I("post.sign");
            if(!$post_code||empty($post_code)||!$post_sign){
                $json["status"] = 301;
                $json["info"] = "参数错误";
                $this->ajaxReturn($json);
            }
            $sign = md5($post_code.$code);
            if($post_sign!=$sign){
                $json["status"] = 302;
                $json["info"] = "加密串验证失败";
                $this->ajaxReturn($json);
            }
        }elseif(strpos($_SERVER["REQUEST_URI"],"phonelogin")||strpos($_SERVER["REQUEST_URI"],"loginsms")){
            $code="DUAB85CAD427CHISDN365DSC";
            $post_mobile = I("post.mobile");
            $post_sign = I("post.sign");
            if(!$post_mobile||empty($post_mobile)||!$post_sign){
                $json["status"] = 301;
                $json["info"] = "参数错误";
                $this->ajaxReturn($json);
            }
            $sign = md5($post_mobile.$code);
            if($post_sign!=$sign){
                $json["status"] = 302;
                $json["info"] = "加密串验证失败";
                $this->ajaxReturn($json);
            }
        }elseif(strpos($_SERVER["REQUEST_URI"],"atlogin")||strpos($_SERVER["REQUEST_URI"],"register")){
            $code="DUAB85CAD427CHISDN365DSC";
            $post_username = I("post.username");
            $post_sign = I("post.sign");
            if(!$post_username||empty($post_username)||!$post_sign){
                $json["status"] = 301;
                $json["info"] = "参数错误";
                $this->ajaxReturn($json);
            }
            $sign = md5($post_username.$code);
            if($post_sign!=$sign){
                $json["status"] = 302;
                $json["info"] = "加密串验证失败";
                $this->ajaxReturn($json);
            }
        }else{
            $uid = I("uid",0);
            $authkey = I("authkey","");
            add_log("init.log", "game", "ip：". $ip);
            add_log("init.log", "game", "action：". $_SERVER["REQUEST_URI"]);
            if(!in_array($ip, C('IP_WHITE'))){
                //add_log("init.log", "game", "noip：");
                if(!$uid||!$authkey){
                    $json["status"] = 301;
                    $json["info"] = "参数错误";
                    $this->ajaxReturn($json);
                }else{
                    $where["authkey"] = $authkey;
                }
            }else{
                //add_log("init.log", "game", "isip：");
                if(!$uid){
                    $json["status"] = 301;
                    $json["info"] = "参数错误";
                    $this->ajaxReturn($json);
                }
            }
            
            $this->user_id = $uid;
            $m_user = D("user");
            $where["id"] = $uid;
            $this->user_info = $m_user->getUserOneByWhere($where);
            if(!$this->user_info){
                $json["status"] = 302;
                $json["info"] = "非法请求";
                $this->ajaxReturn($json);
            }
        }
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
}
