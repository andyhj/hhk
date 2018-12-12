<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Model;

use Think\Model;
class AwardModel extends Model{
    /**
     * 添加用户收益
     * @param type $sql
     * @return boolean
     */
    public function addAwardAgencyDsc($sql){
        if(!$sql){
            return false;
        }
        $model = M();
        return $model->execute($sql);
    }
    /**
     * 清空表数据
     * @param type $table
     * @return boolean
     */
    public function clearTable($table){
        if(!$table){
            return false;
        }
        $model = M();
        return $model->execute("truncate table l_$table");
    }
    /**
     * 创建台费收益详情表
     * @param type $table
     * @return boolean
     */
    public function createAwardAgencyDsc($table){
        if(!$table){
            return false;
        }
        $model = M();
        $s_table = "show tables like 'l_". $table."'";  //查询表是否存在
        $result = $model->query($s_table);
        if(empty($result)){
            $c_table = "CREATE TABLE `l_".$table."` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `u_id` int(11) NOT NULL COMMENT '用户id',
                    `amount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '额度',
                    `source_id` int(11) NOT NULL DEFAULT '0' COMMENT '来源用户id',
                    `order_number` varchar(50) NOT NULL DEFAULT '' COMMENT '来源订单',
                    `add_date` int(11) NOT NULL DEFAULT '0' COMMENT '时间',
                    PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            $model->execute($c_table);
            return true;
        }
        return true;
    }
    
    public function getAwardAgencyList($table,$where){
        if(!$table||!$where){
            return false;
        }
        $model = M();
        return $model->table($table)->where($where)->select();
    }

    /**
     * 统计当天收益
     * @param type $table
     * @param type $date  时间戳
     * @param type $source 来源（1：台费，2：充值，3：代理充值） 默认1
     */
    public function countAward($table,$date,$source=1){
        if(!$table||!$date){
            return false;
        }
        $model = M();
        $sql = "SELECT u_id,SUM(amount) AS total FROM l_".$table." GROUP BY u_id";
        $return_data = $model->query($sql);
        if(!empty($return_data)){
            $model->table("l_award_earn")->where(["add_date"=> $date,"source"=>$source])->delete();
            foreach ($return_data as $value) {
                $data["u_id"] = $value["u_id"];
                $data["amount"] = $value["total"];
                $data["source"] = $source;
                $data["add_date"] = $date;
                $model->table("l_award_earn")->add($data); //添加每天收益
            }
            $a_sql = "SELECT u_id,SUM(amount) AS total FROM l_award_earn GROUP BY u_id"; //计算总收益
            $award_earn_data = $model->query($a_sql);
            if(!empty($award_earn_data)){
                foreach ($award_earn_data as $value) {
                    $exist = $model->table("l_award")->where(['u_id' => $value["u_id"]])->find();
                    if($exist)
                    {
                        $upd_award_data["earn"] = $value["total"];
                        $upd_award_data["amount"] = round(floatval($value["total"]-$exist["extract"]), 2);
                        $model->table("l_award")->where(['u_id' => $value["u_id"]])->save($upd_award_data);
                    }else{
                        $award_data["u_id"] = $value["u_id"];
                        $award_data["earn"] = $value["total"];
                        $award_data["extract"] = 0.00;
                        $award_data["amount"] = $value["total"];
                        $model->table("l_award")->add($award_data);
                    }
                }
            }
        }
        return true;
    }
    public function updCommission($uid,$amount){
        if(!$uid||!$amount){
            return false;
        }
        $exist = $this->table("l_award")->where(['u_id' => $uid])->setInc('extract',$amount);
        $exist = $this->table("l_award")->where(['u_id' => $uid])->setDec('amount',$amount);
        if($exist)
        {
            return true;
        }
        return false;
    }
    /**
     * 提现回滚
     * @param type $uid
     * @param type $amount
     * @return boolean
     */
    public function returnCommission($uid,$amount,$order_number,$info='',$status=500){
        if(!$uid||!$amount){
            return false;
        }
        $exist = $this->table("l_award")->where(['u_id' => $uid])->setDec('extract',$amount);
        $exist = $this->table("l_award")->where(['u_id' => $uid])->setInc('amount',$amount);
        $model = M("award_extract");
        $exist = $model->where(['u_id'=>$uid,'order_number'=>$order_number])->save(["status"=>$status,"info"=>$info]);
        if($exist)
        {
            return true;
        }
        return false;
    }
    /**
     * 更新提现订单
     * @param type $where
     * @param type $data
     * @return boolean
     */
    public function updAwardExtract($where,$data){
        if(empty($where)||empty($data)){
            return false;
        }
        $model = M("award_extract");
        return $model->where($where)->save($data);
    }
    
    public function getAwardExtractList($where,$order=""){
        if(empty($where)){
            return false;
        }
        $model = M("award_extract");
        return $model->where($where)->order($order)->select();
    }
    public function getAwardExtractOne($where){
        if(empty($where)){
            return false;
        }
        $model = M("award_extract");
        return $model->where($where)->find();
    }

    /**
     * 根据用户id查询佣金收入
     * @param type $user_id
     */
    public function getOne($user_id){
        if(!$user_id){
            return false;
        }
        return $this->where(["u_id"=>$user_id])->find();
    }
    /**
     * 佣金获得记录
     * @param type $where
     * @param type $order
     * @return boolean
     */
    public function getAwardEarnList($where,$order=""){
        if(empty($where)){
            return false;
        }
        $model = M("award_earn");
        return $model->where($where)->order($order)->select();
    }
}
