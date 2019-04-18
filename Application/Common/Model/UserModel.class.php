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
    /**
     * 根据用户id查找用户
     * @param type $user_id
     * @return boolean
     */
    public function getUserOne($user_id){
        if(!$user_id){
            return false;
        }
        $user_info = $this->where(["u_id"=>$user_id])->find();
        return $user_info;
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
        $user_info = $this->where($where)->find();
        return $user_info;
    }

    /**
     * 公众号推送信息
     * @param type $uid
     * @param type $plan_des_info
     */
    public function wxMessagewxYwlcMsg($msg_uid,$title,$keyword1,$keyword2,$keyword3,$keyword4,$remark='',$url='',$open_id=''){
        if(!$title||!$keyword1||!$keyword2||!$keyword3||!$keyword4){
            return false;
        }
        if($open_id==''){
            if(!$msg_uid){
                 return false;
            }
            $user_info = $this->getUserOne($msg_uid);
            $open_id = $user_info["open_id"];
        }
        if($open_id){
            require_once APP_ROOT ."Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            $msg_data["touser"] = $open_id;
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
            add_log("wxMessage.log", "wxmessage", "公众号推送信息数据：". var_export($msg_data, true));
            add_log("wxMessage.log", "wxmessage", "公众号推送信息：". var_export($return_status, true));
            $return_status = json_decode($return_status, true);
            if($return_status["errcode"]===0){
                return true;
            }
        }
        return false;
    }
}
