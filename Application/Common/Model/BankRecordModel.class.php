<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Model;

use Think\Model;
class BankRecordModel extends Model{
    protected $db_config;
    function __construct() {
        $this->db_config = C("DB_CONFIG2");
    }
    public function getTypeText($level){
        $type_arr = array(
            0=>"存",
            1=>"取",
            2=>"个人赠送",
            3=>"个人被赠送",
            4=>"充值",
            5=>"返利",
            6=>"系统赠送"
        );
        return $type_arr[$level];
    }
    /**
     * 根据条件查询一条数据
     * @param type $where
     */
    public function getOne($where){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("bank_record",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->find();
    }
    /**
     * 根据条件查询多条条数据
     * @param type $where
     */
    public function getList($where=[],$current_page=1,$per_page=20,$order="create_time DESC"){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("bank_record",$this->db_config["DB_PREFIX"],$this->db_config);
        return $model->where($where)->order($order)->page($current_page.','.$per_page)->select();
    }
    /**
     * 获取总条数
     * @param type $where
     */
    public function getCount($where=[]){
        $model = M("bank_record",$this->db_config["DB_PREFIX"],$this->db_config);
        return (int)$model->where($where)->count();
    }
    /**
     * 添加一条数据
     * @param type $data
     * @param type $type  (2 增加，1 减少)
     * @return boolean
     */
    public function addRecord($data,$type=2){
        if(!$data||empty($data)||!$data["uid"]){
            return 1;
        }
        $s_model = M("bank_statistic",$this->db_config["DB_PREFIX"],$this->db_config);
        $r_model = M("bank_record",$this->db_config["DB_PREFIX"],$this->db_config);
        $u_model = M("user",$this->db_config["DB_PREFIX"],$this->db_config);
        $statisic_info = $s_model->where(["uid"=>$data["uid"]])->find();
        if($type===1){
            if(!$statisic_info){
                return 2;
            }elseif($statisic_info["remain_coin"]<$data["coinnum"]){
                return 3;
            }else{
                $remain_coin = $statisic_info["remain_coin"]-$data["coinnum"];
                $data["present_coinnum"] = $remain_coin;
                $return_status = $r_model->add($data);
                if($return_status){
                    $statisic_data["total_coin_out"] = $statisic_info["total_coin_out"]+$data["coinnum"];
                    $statisic_data["remain_coin"] = $remain_coin;
                    $return_status = $s_model->where(["uid"=>$data["uid"]])->save($statisic_data);
                    $return_status = $u_model->where(["uid"=>$data["uid"]])->save(["bankcoin"=>$remain_coin]);
                }
            }
        }
        if($type===2){
            if($statisic_info){
                $remain_coin = $statisic_info["remain_coin"]+$data["coinnum"];
                $data["present_coinnum"] = $remain_coin;
                $return_status = $r_model->add($data);
                if($return_status){
                    $statisic_data["total_coin_in"] = $statisic_info["total_coin_in"]+$data["coinnum"];
                    $statisic_data["remain_coin"] = $remain_coin;
                    $return_status = $s_model->where(["uid"=>$data["uid"]])->save($statisic_data);
                    $return_status = $u_model->where(["uid"=>$data["uid"]])->save(["bankcoin"=>$remain_coin]);
                }
            }else{
                $data["present_coinnum"] = $data["coinnum"];
                $return_status = $r_model->add($data);
                if($return_status){
                    $statisic_data["total_coin_in"] = $data["coinnum"];
                    $statisic_data["remain_coin"] = $data["coinnum"];
                    $statisic_data["uid"] = $data["uid"];
                    $return_status = $s_model->add($statisic_data);
                    $return_status = $u_model->where(["uid"=>$data["uid"]])->save(["bankcoin"=>$data["coinnum"]]);
                }
            }
        }
        if($return_status){
            return 200;
        }
        return 4;
    }
}
