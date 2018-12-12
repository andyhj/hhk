<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Api\Model;

/**
 * Description of AgencyOrderModel
 *
 * @author Administrator
 */
class AgencyOrderModel extends \Common\Model\AgencyOrderModel{
    /**
     * 添加代理升级订单
     * @param type $data
     * @return boolean
     */
    public function addAgencyOrder($data){
        if(empty($data)){
            return false;
        }
        return $this->add($data);
    }
    /**
     * 
     * @param type $where
     */
    public function getAgencyOrderOne($where){
        if(empty($where)){
            return false;
        }
        return $this->where($where)->find();
    }
}
