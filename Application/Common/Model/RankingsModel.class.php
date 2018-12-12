<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Model;

/**
 * Description of RankingsModel
 *
 * @author andy
 */
use Think\Model;
class RankingsModel extends Model{
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
        $model = M("rankings",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->find();
    }
    /**
     * 查询多条数据
     * @param type $where
     * @return boolean
     */
    public function getList($where){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("rankings",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->select();
    }

    /**
     * 添加一条数据
     * @param type $data
     * @return boolean
     */
    public function addRankings($data){
        if(!$data||empty($data)){
            return false;
        }
        $model = M("rankings",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->add($data);
    }
    /**
     * 更新排名
     * @param type $data
     * @return boolean
     */
    public function editRankings($where,$data){
        if(!$where||empty($where)||!$data||empty($data)){
            return false;
        }
        $model = M("rankings",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->save($data);
    }
    /**
     * 获取比赛详情数据
     * @param type $where
     * @return boolean
     */
    public function getDesOne($where){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("rankings_des",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->find();
    }
    /**
     * 获取比赛详情数据
     * @param type $where
     * @param type $current_page
     * @param type $per_page
     * @param type $order
     * @return boolean
     */
    public function getDesList($where=[],$current_page=1,$per_page=20,$order=""){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("rankings_des",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->order($order)->page($current_page.','.$per_page)->select();
    }
    /**
     * 添加排名详情
     * @param type $data
     * @return boolean
     */
    public function addRankingsDes($data){
        if(!$data||empty($data)){
            return false;
        }
        $model = M("rankings_des",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->add($data);
    }
    /**
     * 更新排名详情
     * @param type $data
     * @return boolean
     */
    public function editRankingsDes($where,$data){
        if(!$where||empty($where)||!$data||empty($data)){
            return false;
        }
        $model = M("rankings_des",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->save($data);
    }
}
