<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Model;

use Think\Model;
use Common\WxApi\class_weixin_adv;
use Common\GyfPay\gyf;
use Common\ybfPay\Ybf;
class UserModel extends Model{
    /**
     * 根据用户id查找用户
     * @param type $user_id
     * @return boolean
     */
    public function getUserOne($user_id){
        if(!$user_id){
            return false;
        }
        $user_info = $this->where(["u_id"=>$user_id])->find();
        return $user_info;
    }
     /**
     * 根据条件查找用户,返回一条数据
     * @param type $where
     * @return boolean
     */
    public function getUserOneByWhere($where){
        if(empty($where)){
            return false;
        }
        $user_info = $this->where($where)->find();
        return $user_info;
    }

    /**
     * 公众号推送信息
     * @param type $uid
     * @param type $plan_des_info
     */
    public function wxMessagewxYwlcMsg($msg_uid,$title,$keyword1,$keyword2,$keyword3,$keyword4,$remark='',$url='',$open_id=''){
        if(!$title||!$keyword1||!$keyword2||!$keyword3||!$keyword4){
            return false;
        }
        if($open_id==''){
            if(!$msg_uid){
                 return false;
            }
            $user_info = $this->getUserOne($msg_uid);
            $open_id = $user_info["open_id"];
        }
        if($open_id){
            require_once APP_ROOT ."Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            $msg_data["touser"] = $open_id;
            $msg_data["template_id"] = "0rAKRWnyzyiW9ICydVIJj4W4NZAFR_PGNoM4XsUr92A";
            $msg_data["url"] = $url;//HTTP_HOST.'/index/user/plusdes.html';
            $msg_data["data"] = array(
                "first"=>array(
                    "value"=> $title,
                    "color"=>""
                ),
                "keyword1"=>array(
                    "value"=> $keyword1,
                    "color"=>""
                ),
                "keyword2"=>array(
                    "value"=> $keyword2,
                    "color"=>""
                ),
                "keyword3"=>array(
                    "value"=> $keyword3,
                    "color"=>""
                ),
                "keyword4"=>array(
                    "value"=> $keyword4,
                    "color"=>""
                ),
                "remark"=>array(
                    "value"=> $remark,
                    "color"=>""
                )
            );
            $return_status = $weixin->send_user_message($msg_data);
            add_log("wxMessage.log", "wxmessage", "公众号推送信息数据：". var_export($msg_data, true));
            add_log("wxMessage.log", "wxmessage", "公众号推送信息：". var_export($return_status, true));
            $return_status = json_decode($return_status, true);
            if($return_status["errcode"]===0){
                return true;
            }
        }
        return false;
    }
    /**
     * 查询费率
     *
     * @param [type] $uid
     * @param [type] $code
     * @return void
     */
    public function getFee($uid,$code)
    {
        $this->isVip($uid);
        $user_m = M("user");
        $user_info = $user_m->where(["u_id"=>$uid])->find();
        $arr  = [];
        if($user_info){
            $channel_model = M("channel");
            $channel_info = $channel_model->where(["code"=>$code])->find();
            $arr['fee'] = $channel_info['user_fee'];
            $arr['close_rate'] = $channel_info['user_close_rate'];
            if($user_info['is_vip']){
                $arr['fee'] = $channel_info['plus_user_fee'];
                $arr['close_rate'] = $channel_info['plus_user_close_rate'];
            }
        }
        return $arr;
    }
    /**
     * 根据新费率更新计划
     *
     * @param [type] $plan
     * @param [type] $plan_des
     * @return void
     */
    public function updPlanFee($plan,$plan_des)
    {
        $plan_des_m = M("plan_des");
        $fee_arr = $this->getFee($plan['u_id'],$plan['c_code']);  //获取费率
        if($fee_arr && !empty($fee_arr) && $fee_arr['fee']>0 && $fee_arr['close_rate']>0){ 
            //判断跟现在费率是否一样
            if($fee_arr['fee']!=$plan_des['fee'] || $fee_arr['close_rate']!=$plan_des['close_rate']){ 
                //查询之后未执行的计划
                $plan_des_list = $plan_des_m->where("p_id=".$plan['id']." AND id>=".$plan_des['id']." AND type=1")->select();
                if($plan_des_list){
                    foreach ($plan_des_list as $key => $value) {
                        //计算出还款金额
                        $y_amount = round($value['amount']-($value['amount']*$value['fee']+$value['close_rate']),2);
                        //根据还款金额计算出现在费率的扣款金额
                        $x_amount = round(($y_amount+$fee_arr['close_rate'])/($fee_arr['close_rate']-$fee_arr['fee']),2);
                        $r_s = $plan_des_m->where(['id'=>$value['id']])->save(['amount'=>$x_amount,'fee'=>$fee_arr['fee'],'close_rate'=>$fee_arr['close_rate']]);
                        if(!$r_s){
                            add_log("updPlanFee.log", "common", "更新失败ID：" . $value['id']);		
                        }
                    }
                    $plan_des_m->where("p_id=".$plan['id']." AND id>=".$plan_des['id']." AND type=2")->save(['fee'=>$fee_arr['fee'],'close_rate'=>$fee_arr['close_rate']]);
                }
            }
        }
        return true;
    }

    /**
     * 是否vip
     *
     * @param [type] $uid
     * @return boolean
     */
    public function isVip($u_id)
    {
        if(!$u_id){
            return false;
        }
        $user_m = M("user");
        $user_info = $user_m->where(["u_id"=>$u_id])->find();
        if($user_info&&$user_info['is_vip']){
            $user_vip_model = M("user_vip");
            $user_vip_info = $user_vip_model->where(["u_id"=>$u_id])->find();
            //判断是否plus会员
            if($user_vip_info && strtotime($user_vip_info["end_time"])<= time()){
                $r_s = $user_m->where(["u_id"=>$u_id])->save(['is_vip'=>0]);
                if($r_s){
                    $channel_model = M("channel");
                    $channel_info = $channel_model->where(["code"=>'gyf'])->find();
                    if($channel_info){
                        return $this->updateRate($u_id,$channel_info['user_fee'],$channel_info['user_close_rate']);//更新工易付费率
                    }
                }
            }
        }
        return false;
    }
    /*更新工易付费率*/
    public function updateRate($uid,$feeRate,$fee){
        $bank_card_gyf_model = M("bank_card_gyf");
        $bank_card_gyf_info = $bank_card_gyf_model->where(["uid"=>$uid,"success"=>1])->find();
        if(!$bank_card_gyf_info){
            return false;
        }        
         //收集信息
        $param = array(          
            'merch_id'  => $bank_card_gyf_info['merch_id'], //子商户号
            'fee_rate'   => $feeRate*10000,//交易费率0.68% 传  68. 费率值乘于10000
            'extern_fee' => $fee*100,//附加手续费(结算手续费)，单位分：（1.00元，传 100）
        );
        require_once APP_ROOT. "Application/Common/Concrete/gyfpay/gyfpay.php";
        $res_j = gyf::updateRate($param);
        if(isset($res_j['status']) && $res_j['status'] == 1){
            return true;
        }
        return false;
    }
    /**
     * 银宝付进件
     *
     * @param Type $var
     * @return void
     */
    public function ybfRegisterAndAccess($uid,$fee)
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/ybfpay/YbfPay.php";
        $user_m = D('User');
        $user_info = $user_m->where(['u_id'=>$uid])->find();
        $json['code'] = 1;
        $json['msg'] = '进件成功';
        if (!$user_info) {
            $json['code'] = 4;
            $json['msg'] = '用户不存在';
            return $json;
        }
        $merchant_ybf_info = M('merchant_ybf')->where(['u_id'=>$uid])->find();
        if ($merchant_ybf_info&&$merchant_ybf_info['success']==1) {
            return $json;
        }
        $db_config = C("DB_CONFIG2");
        $customer_bankaccount_m = M("customer_bankaccount",$db_config["DB_PREFIX"],$db_config);
        $customer_bankaccount_des = $customer_bankaccount_m->where(["userId"=>$u_id])->find();
        if(!$customer_bankaccount_des||!$customer_bankaccount_des['province']||!$customer_bankaccount_des['city']||!$customer_bankaccount_des['bank_branch']||!$customer_bankaccount_des['reserved_phone']){
            $json['code'] = 2;
            $json['msg'] = '结算卡信息不完整，请更新结算卡';
            return $json;            
        }
        $param = array(
            'province' => $customer_bankaccount_des['province'], //省
            'city' => $customer_bankaccount_des['city'], //市
            'area' => $customer_bankaccount_des['city'], //区
            'address' => $customer_bankaccount_des['province'].$customer_bankaccount_des['city'], //详细地址
            'mer_name' => $customer_bankaccount_des['accountName'], //真实姓名
            'id_card' => $user_info['id_card'], //身份证号码
            'account' => $customer_bankaccount_des['account'], //结算账号  
            'reserved_phone' => $customer_bankaccount_des['reserved_phone'], //结算卡银行预留手机号 
            'bank_branch' => $customer_bankaccount_des['bank_branch'], //银行行号
            'down_pay_fee' => $fee, //费率 
        );
        $ybf = new Ybf();
        $ybf_dh = $ybf->registerAndAccess($param);//执行代扣
        if(isset($ybf_dh['status']) && $ybf_dh['status'] == 40000 ){
            $merchantNo = $ybf_dh['merchant_no'];
            $indata['u_id']   = $uid;
            $indata['mer_name']   = $customer_bankaccount_des['accountName'];
            $indata['id_card']    = $user_info['id_card'];
            $indata['account']    = $customer_bankaccount_des['account'];
            $indata['bank_branch']  = $customer_bankaccount_des['bank_branch'];
            $indata['reserved_phone'] = $customer_bankaccount_des['reserved_phone'];
            $indata['merchant_no']    = $merchantNo;
            $indata['success'] = 1;
            $rs = M('merchant_ybf')->add($indata);
            if ($rs) {
                return $json;
            }
            $json['code'] = 5;
            $json['msg'] = '进件记录失败';
            return $json;
        }else{
            $json['code'] = 3;
            $json['msg'] = $ybf_dh["msg"];
            return $json; 
        }
    }
}
