<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Model;

use Think\Model;
use Common\WxApi\class_weixin_adv;
class UserModel extends Model{
    protected $db_config;
    function __construct() {
        $this->db_config = C("DB_CONFIG2");
    }
    public function getLevelText($level){
        $level_arr = array(
            0=>"注册用户",
            1=>"贵宾用户",
            2=>"金尊用户",
            3=>"白金代理",
            4=>"钻石代理"
        );
        return $level_arr[$level];
    }

    /**
     * 根据用户id查找用户
     * @param type $user_id
     * @return boolean
     */
    public function getUserOne($user_id){
        if(!$user_id){
            return false;
        }
        $model = M("customer_info",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where(["id"=>$user_id])->find();
    }
    /**
     * 根据条件查找用户,返回一条数据
     * @param type $where
     * @return boolean
     */
    public function getUserOneByWhere($where){
        if(empty($where)){
            return false;
        }
        $model = M("customer_info",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->find();
    }

    /**
     * 公众号推送信息
     * @param type $uid
     * @param type $plan_des_info
     */
    public function wxMessagewxYwlcMsg($msg_uid,$title,$keyword1,$keyword2,$keyword3,$keyword4,$remark='',$url=''){
        if(!$msg_uid||!$title||!$keyword1||!$keyword2||!$keyword3||!$keyword4){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $customer_m = M("cunstomer_wx_binding",$db_config["DB_PREFIX"],$db_config);
        $cunstomer_wx_binding_info = $customer_m->where(["user_id"=>$msg_uid,"state"=>1])->find();
        if($cunstomer_wx_binding_info&&!empty($cunstomer_wx_binding_info)){
            require_once APP_ROOT ."Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            $msg_data["touser"] = $cunstomer_wx_binding_info["open_id"];
            $msg_data["template_id"] = "qq5apA1Ku6rbm0IWkD_QMHRjAaSOuCu9Fv62SjPpmrE";
            $msg_data["url"] = $url;//HTTP_HOST.'/index/user/plusdes.html';
            $msg_data["data"] = array(
                "first"=>array(
                    "value"=> $title,
                    "color"=>""
                ),
                "keyword1"=>array(
                    "value"=> $keyword1,
                    "color"=>""
                ),
                "keyword2"=>array(
                    "value"=> $keyword2,
                    "color"=>""
                ),
                "keyword3"=>array(
                    "value"=> $keyword3,
                    "color"=>""
                ),
                "keyword4"=>array(
                    "value"=> $keyword4,
                    "color"=>""
                ),
                "remark"=>array(
                    "value"=> $remark,
                    "color"=>""
                )
            );
            $return_status = $weixin->send_user_message($msg_data);
            add_log("wxMessage.log", "wxmessage", "计划失败公众号消息推送状态：". var_export($return_status, true));
            $return_status = json_decode($return_status, true);
            if($return_status["errcode"]===0){
                return true;
            }
        }
        return false;
    }
}
