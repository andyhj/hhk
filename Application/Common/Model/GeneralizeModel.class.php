<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Model;

/**
 * Description of GeneralizeModel
 *
 * @author Administrator
 */
use Think\Model;
class GeneralizeModel extends Model{
    /**
     * 获取活动列表数据
     * 
     */
    public function getList($where=[],$current_page=1,$per_page=50,$order="add_date DESC"){
        return $this->where($where)->order($order)->page($current_page.','.$per_page)->select();
    }
    /**
     * 根据条件查询一条数据
     * @param type $where
     * @return boolean
     */
    public function getOne($where){
        if(!$where||empty($where)){
            return false;
        }
        return $this->where($where)->find();
    }
}
