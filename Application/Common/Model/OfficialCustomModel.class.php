<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Model;

use Think\Model;
class OfficialCustomModel extends Model{
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
        $model = M("official_custom",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->find();
    }
    /**
     * 根据条件查询多条条数据
     * @param type $where
     */
    public function getList($where,$order="start_date ASC"){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("official_custom",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->order($order)->select();
    }
    /**
     * 根据条件查询一条数据
     * @param type $where
     */
    public function getApplyOne($where){
        $model = M("official_custom_apply",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->find();
    }
    /**
     * 根据条件查询多条数据
     * @param type $where
     */
    public function getApplyList($where,$order="applytime DESC"){
        $model = M("official_custom_apply",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->order($order)->select();
    }
    /**
     * 根据条件查询多条数据
     * @param type $sql
     */
    public function getApplyListBySql($sql){
        $model = M("official_custom_apply",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->query($sql);
    }
    /**
     * 根据条件查询总数据
     * @param type $where
     */
    public function getApplyCount($where){
        $model = M("official_custom_apply",$this->db_config["DB_PREFIX"],$this->db_config);
        return (int)$model->where($where)->count();
    }
    /**
     * 添加一条数据
     * @param type $data
     * @return boolean
     */
    public function addCustomApply($data){
        if(!$data||empty($data)){
            return false;
        }
        $model = M("official_custom_apply",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->add($data);
    }
    /**
     * 添加一条数据
     * @param type $data
     * @return boolean
     */
    public function addCustom($data){
        if(!$data||empty($data)){
            return false;
        }
        $model = M("official_custom",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->add($data);
    }
    /**
     * 更新数据
     * @param type $where
     * @param type $data
     * @return boolean
     */
    public function updCustom($where,$data){
        if(!$data||empty($data)||!$where||empty($where)){
            return false;
        }
        $model = M("official_custom",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->save($data);
    }

    /**
     * 删除数据
     * @param type $where
     * @return boolean
     */
    public function delCustom($where){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("official_custom",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->delete();
    }
    /**
     * 获取比赛排名
     * @param type $where
     * @return boolean
     */
    public function getCustomRanking($where,$order="ranking ASC"){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("official_custom_ranking",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->order($order)->select();
    }
    /**
     * 获取一条比赛排名
     * @param type $where
     * @return boolean
     */
    public function getCustomRankingOne($where){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("official_custom_ranking",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->find();
    }
    /**
     * 更新比赛排名
     * @param type $where
     * @return boolean
     */
    public function updCustomRanking($where,$data){
        if(!$where||empty($where)||!$data||empty($data)){
            return false;
        }
        $model = M("official_custom_ranking",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->save($data);
    }
}
