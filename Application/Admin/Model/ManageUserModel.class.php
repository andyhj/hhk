<?php
namespace Admin\Model;
use Think\Model;
class ManageUserModel extends Model{
    /**
     * 用户登录
     * @param type $name
     * @param type $password
     * @return int
     */
    public function getLoginInfo($name,$password){
        if(!$name||!$password){
            return false;
        }
        $md5_password = md5($name.$password);
        $is_user = $this->getUserByName($name);
        if(!$is_user){
            return 300;
        }
        $where = [
            "name"=>$name,
            "password"=>$md5_password,
            "is_del"=>0
        ];
        $user_info = $this->where($where)->find();
        if(!$user_info){
            $this->updateHistory($is_user["id"]);
            return 301;
        }
        $this->updateHistory($is_user["id"],1);
        return 200;
    }
    /**
     * 根据用户名查询用户
     * @param type $name
     * @return boolean
     */
    public function getUserByName($name){
        if(!$name){
            return false;
        }
        return $this->where(["name"=>$name,"is_del"=>0])->find();
    }

    /**
     * 登录日志记录
     * @param type $user_id
     * @param type $status
     * @return boolean
     */
    public function updateHistory($user_id,$status=0){
        if(!$user_id){
            return false;
        }
        $time = strtotime(date("Y-m-d"));
        $ip = get_client_ip();
        $user_history = M("manage_history");
        $history_info = $user_history->where(["user_id"=>$user_id,"add_date"=>$time])->find();
        if($history_info){
            $where = array(
                'id' => $history_info["id"]
            );
            $data = array(
                'number' => array('exp','number+1'),
                'login' => array('exp','login+1'),
                'end_date' => time(),
                'end_ip' => $ip
            );
            if($status==1){
                $data["number"] = 0;
            }
            $user_history->where($where)->save($data);
        }else{
            $data = array(
                'user_id' => $user_id,
                'number' => 1,
                'login' => 1,
                'add_date' => $time,
                'end_date' => time(),
                'end_ip' => $ip
            );
            if($status==1){
                $data["number"] = 0;
            }
            $user_history->add($data);
        }
    }
    /**
     * 查询一条登录记录
     * @param type $user_id
     * @return boolean
     */
    public function getHistory($user_id){
        if(!$user_id){
            return false;
        }
        $user_history = M("manage_history");
        $time = strtotime(date("Y-m-d"));
        $history_info = $user_history->where(["user_id"=>$user_id,"add_date"=>$time])->find();
        return $history_info;
    }
    /**
     * 获取登录用户信息
     */
    public function getUserInfo(){
        $user_id = session("userId");
        if(!$user_id){
            return false;
        }
        return $this->where(["id"=>$user_id,"is_del"=>0])->find();
    }
}
