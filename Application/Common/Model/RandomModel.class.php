<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Model;

/**
 * Description of RandomModel
 *
 * @author andy
 */
use Think\Model;
class RandomModel extends Model{
    protected $db_config;
    function __construct() {
        $this->db_config = C("DB_CONFIG3");
    }
    /**
     * 根据条件查询一条数据
     * @param type $where
     */
    public function getOne($where){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("random",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->find();
    }
    /**
     * 查询多条数据
     * @param type $where
     * @return boolean
     */
    public function getList($where=[]){
        $model = M("random",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->select();
    }
    /**
     * 添加一条数据
     * @param type $data
     * @return boolean
     */
    public function addRandom($data){
        if(!$data||empty($data)){
            return false;
        }
        $model = M("random",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->add($data);
    }
    /**
     * 更新排名
     * @param type $data
     * @return boolean
     */
    public function editRandom($where,$data){
        if(!$where||empty($where)||!$data||empty($data)){
            return false;
        }
        $model = M("random",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->save($data);
    }
    public function getSqlList($sql){
        if(!$sql){
            return false;
        }
        $model = M("random",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->query($sql);
    }
}
