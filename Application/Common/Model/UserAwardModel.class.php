<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Model;
use Think\Model;
class UserAwardModel extends Model{
    protected $db_config;
    function __construct() {
        $this->db_config = C("DB_CONFIG2");
    }
    /**
     * 根据条件查询一条数据
     * @param type $where
     */
    public function getOne($where){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("user_award",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->find();
    }
    /**
     * 更新数据
     * @param type $where
     * @param type $data
     * @return boolean
     */
    public function upd($where,$data){
        if(!$data||empty($data)||!$where||empty($where)){
            return false;
        }
        $model = M("user_award",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->save($data);
    }
}
