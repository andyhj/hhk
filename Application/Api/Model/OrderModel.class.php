<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Api\Model;

/**
 * Description of OrderModel
 *
 * @author Administrator
 */
class OrderModel extends \Common\Model\OrderModel{
    /**
     * 添加支付单号
     * @param type $data
     * @return boolean
     */
    public function addPayOrder($data){
        if(empty($data)){
            return false;
        }
        $pay_order = M("pay_order");
        return $pay_order->add($data);
    }
    
}
