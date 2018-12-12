<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Model;
use Think\Model;
class CliServiceModel extends Model{
    public function getList($where){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("cli_service");
        return $model->where($where)->select();
    }
}
