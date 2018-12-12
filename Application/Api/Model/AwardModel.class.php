<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Api\Model;

/**
 * Description of AwardModel
 *
 * @author Administrator
 */
class AwardModel extends \Common\Model\AwardModel{
    public function getAwardEarnList($where,$current_page=1,$per_page=30,$order=""){
        if(empty($where)){
            return false;
        }
        return $this->table("l_award_earn")->where($where)->order($order)->page($current_page.','.$per_page)->select();
    }
    public function queryAward($sql){
        if(!$sql){
            return false;
        }
        return $this->table("l_award_earn")->query($sql);
    }
    public function addAwardExtract($data){
        $model = M("award_extract");
        return $model->add($data);
    }
}
