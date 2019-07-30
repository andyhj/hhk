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
        $wx = $customer_wx_m->where(["user_id"=>$this->user_info["id"],"state"=>1])->find();
        if(!$wx){
            echo '<script>alert("请先关注会收钱公众号");</script>';
            die();
        }
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
    public function plus(){
        $info = $this->user_info;
        $user_vip_log_m = M("user_vip_log");
        $user_vip_model = M("user_vip");
        $user_vip_info = $user_vip_model->where(["u_id"=>$info["id"]])->find();
        $user_vip_log_info = $user_vip_log_m->where(["u_id"=>$info["id"],"status"=>0])->order("end_time asc")->find();
        $user_vip_log_info1 = $user_vip_log_m->where(["u_id"=>$info["id"]])->order("end_time desc")->find();
        $user_vip_log_count = $user_vip_log_m->where(["u_id"=>$info["id"],"status"=>0])->count();
        $user_vip_log_info?$user_vip_log_info["type_name"] = $this->getTypeName($user_vip_log_info["type"]):"";
        $user_vip_log_info1?$user_vip_log_info1["type_name"] = $this->getTypeName($user_vip_log_info1["type"]):"";
        $this->assign('user_vip_info',$user_vip_info);
        $this->assign('user_vip_log_info',$user_vip_log_info);
        $this->assign('user_vip_log_info1',$user_vip_log_info1);
        $this->assign('user_vip_log_count',$user_vip_log_count);
        $this->assign('getPlus',U("index/user/getVip"));
        $this->display();
    }
    public function plusdes(){
        $info = $this->user_info;
        $user_vip_log_m = M("user_vip_log");
        $user_vip_log_list = $user_vip_log_m->where(["u_id"=>$info["id"]])->order("status desc,end_time desc")->select();
        $user_vip_log_arr = [];
        if($user_vip_log_list){
            foreach ($user_vip_log_list as $value) {
                $value["type_name"] = $this->getTypeName($value["type"]);
                $user_vip_log_arr[] = $value;
            }
        }
        $this->assign('user_vip_log_list',$user_vip_log_arr);
        $this->assign('getPlus',U("index/user/getVip"));
        $this->display();
    }

    private function getTypeName($type){
        if(!$type){
            return "";
        }
        $str = "";
        switch ($type) {
            case 1:
                $str = "新用户注册赠送";
                break;
            case 2:
                $str = "系统赠送会员";
                break;
            case 3:
                $str = "邀请好友赠送";
                break;
            default:
                break;
        }
        return $str;
    }

    public function getVip(){
        $id = I("post.id");
        $session_name = "get_vip_submit_".$id;
        if(session($session_name)){
            $json["status"] = 305;
            $json["info"] = "正在提交...";
            $this->returnJson($json);
        }
        session($session_name,1);
        if(!$id){
            $json["status"] = 306;
            $json["info"] = "参数错误";
            $this->returnJson($json,$session_name);
        }
        $info = $this->user_info;
        $user_vip_log_m = M("user_vip_log");
        $user_vip_model = M("user_vip");
        $user_vip_log_info = $user_vip_log_m->where(["u_id"=>$info["id"],"id"=>$id])->find();
        if($user_vip_log_info&&!empty($user_vip_log_info)){
            if($user_vip_log_info["status"]){
                $json["status"] = 307;
                $json["info"] = "已领取";
                $this->returnJson($json,$session_name);
            }else{
                if($user_vip_log_info["end_time"]&&$user_vip_log_info["end_time"]< time()){
                    $json["status"] = 308;
                    $json["info"] = "已过有效期";
                    $this->returnJson($json,$session_name);
                }
                $user_vip_log_m->where(["u_id"=>$info["id"],"id"=>$id])->save(["status"=>1,"get_time"=> time()]);
                $user_vip_info = $user_vip_model->where(["u_id"=>$info["id"]])->find();
                $user_m = M("user");
                $user_des = $user_m->where(["u_id"=>$info["id"]])->find();
                
                //判断是否plus会员
                if($user_vip_info){
                    if(strtotime($user_vip_info["end_time"])> time()){
                        $end_time = strtotime("+".$user_vip_log_info["vip_m"]." month",strtotime($user_vip_info["end_time"]));
                    }else{
                        $end_time = strtotime("+".$user_vip_log_info["vip_m"]." month");
                    }
                    $r_s = $user_vip_model->where(["u_id"=>$info["id"]])->save(["end_time"=>date("Y-m-d H:i:s",$end_time)]);
                    if($r_s){
                        if($user_des&&!$user_des['is_vip']){
                            $r_s = $user_m->where(["u_id"=>$info["id"]])->save(['is_vip'=>1]);
                            if($r_s){
                                $channel_model = M("channel");
                                $channel_info = $channel_model->where(["code"=>'gyf'])->find();
                                if($channel_info){
                                    D("User")->updateRate($info["id"],$channel_info['plus_user_fee'],$channel_info['plus_user_fee']);//更新工易付费率
                                }
                            }
                        }
                        $json["status"] = 200;
                        $json["info"] = "领取成功";
                        $this->returnJson($json,$session_name);
                    }
                }else{
                    $end_time = strtotime("+".$user_vip_log_info["vip_m"]." month");
                    $user_vip_data["u_id"] = $info["id"];
                    $user_vip_data["add_time"] = date("Y-m-d H:i:s");
                    $user_vip_data["end_time"] = date("Y-m-d H:i:s",$end_time);
                    $r_s = $user_vip_model->add($user_vip_data);
                    if($r_s){
                        if($user_des&&!$user_des['is_vip']){
                            $r_s = $user_m->where(["u_id"=>$info["id"]])->save(['is_vip'=>1]);
                            if($r_s){
                                $channel_model = M("channel");
                                $channel_info = $channel_model->where(["code"=>'gyf'])->find();
                                if($channel_info){
                                    D("User")->updateRate($info["id"],$channel_info['plus_user_fee'],$channel_info['plus_user_fee']);//更新工易付费率
                                }
                            }
                        }
                        $json["status"] = 200;
                        $json["info"] = "领取成功";
                        $this->returnJson($json,$session_name);
                    }
                }
            }
        }
        $json["status"] = 306;
        $json["info"] = "没有数据";
        $this->returnJson($json,$session_name);
    }
    public function rate(){
        $this->display();
    }
    /**
     * 通道列表
     */
    public function channel(){
        $u_id = $this->user_info["id"];
        $channel_moblie_m = M("channel_moblie");
        $where = [];
        if($u_id!=464885){
            $where["state"] = 1;
        }        
        $num = $channel_moblie_m->where($where)->count();
        $channel_moblie_list = $channel_moblie_m->where($where)->select();
        if($num==1&&$channel_moblie_list){
            $channel_info = M("channel")->where(["id"=>$channel_moblie_list[0]["c_id"]])->find();
            $url = U("index/card/index",["c_code"=>$channel_info["code"]]);
            header('Location: ' . $url);
            die();
        }
        $channels_arr = [];
        if($channel_moblie_list){
            foreach ($channel_moblie_list as $value) {
                $channel_info = M("channel")->where(["id"=>$value["c_id"]])->find();
                $value["channel_info"] = $channel_info;
                $channels_arr[] = $value;
            }
        }
        $this->assign('channels', $channels_arr);
        $this->display();
    }

    protected function returnJson($data,$session_name=""){
        if($session_name){
            session($session_name, null);
        }
        $this->ajaxReturn($data);
    }
}
