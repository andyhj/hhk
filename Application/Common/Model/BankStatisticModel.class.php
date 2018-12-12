<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Model;

use Think\Model;
class BankStatisticModel extends Model{
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
        $model = M("bank_statistic",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->find();
    }
    /**
     * 根据条件查询多条条数据
     * @param type $where
     */
    public function getList($where,$order="update_time DESC"){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("bank_statistic",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->order($order)->select();
    }
    /**
     * 添加一条数据
     * @param type $data
     * @return boolean
     */
    public function addStatistic($data){
        if(!$data||empty($data)){
            return false;
        }
        $model = M("bank_statistic",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->add($data);
    }
    /**
     * 更新数据
     * @param type $where
     * @param type $data
     * @return boolean
     */
    public function updStatistic($where,$data){
        if(!$data||empty($data)||!$where||empty($where)){
            return false;
        }
        $model = M("bank_statistic",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->save($data);
    }

}
