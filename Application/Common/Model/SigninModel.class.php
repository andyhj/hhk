<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Model;

use Think\Model;
class SigninModel extends Model{
    protected $db_config;
    function __construct() {
        $this->db_config = C("DB_CONFIG2");
    }
    /**
     * 根据条件查询一条数据
     * @param type $where
     */
    public function getSigninOne($where){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("signin",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->find();
    }
    /**
     * 根据条件查询一条数据
     * @param type $where
     */
    public function getUserSigninOne($where){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("user_signin",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->find();
    }
    public function updUserSigninOne($where,$data){
        if(!$where||empty($where)||!$data||empty($data)){
            return false;
        }
        $model = M("user_signin",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->save($data);
    }
    public function addUserSigninOne($data){
        if(!$data||empty($data)){
            return false;
        }
        $model = M("user_signin",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->add($data);
    }
}
