<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\AuthModel;
use Common\GyfPay\gyf;
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
        // $session['u_id'] = 464885;
        // session('userInfo', json_encode($session));
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
    public function isVip()
    {
        $user_info = session("userInfo");
        if(!$user_info){
            return false;
        }
        $user_info_arr = json_decode($user_info, true);
        $u_id = $user_info_arr["u_id"];
        $user_m = M("user");
        $user_info = $user_m->where(["u_id"=>$u_id])->find();
        if($user_info&&$user_info['is_vip']){
            $user_vip_model = M("user_vip");
            $user_vip_info = $user_vip_model->where(["u_id"=>$u_id])->find();
            //判断是否plus会员
            if($user_vip_info && strtotime($user_vip_info["end_time"])<= time()){
                $r_s = $user_m->where(["u_id"=>$u_id])->save(['is_vip'=>0]);
                if($r_s){
                    $channel_model = M("channel");
                    $channel_info = $channel_model->where(["code"=>'gyf'])->find();
                    if($channel_info){
                        $this->updateRate($u_id,$channel_info['user_fee'],$channel_info['user_close_rate']);//更新工易付费率
                    }
                }
            }
        }
        return false;
    }
    /*更新工易付费率*/
    public function updateRate($uid,$feeRate,$fee){
        $bank_card_gyf_model = M("bank_card_gyf");
        $bank_card_gyf_info = $bank_card_gyf_model->where(["u_id"=>$uid,"success"=>1])->find();
        if(!$bank_card_gyf_info){
            return false;
        }        
         //收集信息
        $param = array(          
            'merch_id'  => $bank_card_gyf_info['merch_id'], //子商户号
            'fee_rate'   => $feeRate*10000,//交易费率0.68% 传  68. 费率值乘于10000
            'extern_fee' => $fee*100,//附加手续费(结算手续费)，单位分：（1.00元，传 100）
        );
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/gyfpay/gyfpay.php";
        $res_j = gyf::updateRate($param);
        if(isset($res_j['status']) && $res_j['status'] == 1){
            return true;
        }
        return false;
    }
    protected function returnJson($data,$session_name=""){
        if($session_name){
            session($session_name, null);
        }
        $this->ajaxReturn($data);
    }
}
