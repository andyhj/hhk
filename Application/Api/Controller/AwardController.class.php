<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Api\Controller;

use Common\Common\Sockets;
use Common\Common\Daifu;
use Common\Common\HttpClient;
class AwardController extends InitController {
    /**
     * 我的收益
     */
    public function index(){
        $user_id = $this->user_id;
        $model_award = D("award");
        $award_info = $model_award->getOne($user_id);
        if(!$award_info){
            $json["status"] = 305;
            $json["info"] = "没有数据";
            $this->ajaxReturn($json);
        }
        $json["status"] = 200;
        $json["info"] = "获取成功";
        $json["data"] = $award_info;
        $this->ajaxReturn($json);
    }
    /**
     * 日收益
     */
    public function dayEarnings(){
        $user_id = $this->user_id;
        $page = I("page",1);
        $num = I("num",30);
        $model_award = D("award");
        $award_list = $model_award->getAwardEarnList(["u_id"=>$user_id],$page,$num,"add_date DESC");
        if(!$award_list){
            $json["status"] = 305;
            $json["info"] = "没有数据";
            $this->ajaxReturn($json);
        }
        $json["status"] = 200;
        $json["info"] = "请求成功";
        $json["data"] = $award_list;
        $this->ajaxReturn($json);
    }
    /**
     * 月收益
     */
    public function monthEarnings(){
        $user_id = $this->user_id;
        $page = I("page",1);
        $num = I("num",30);
        $model_award = D("award");
        $award_list = $model_award->queryAward("select u_id,DATE_FORMAT(FROM_UNIXTIME(add_date),'%Y%m') months,sum(amount) amount from l_award_earn where u_id={$user_id} group by months LIMIT ".($page-1)*$num.",{$num};");
        if(!$award_list){ 
            $json["status"] = 305;
            $json["info"] = "没有数据";
            $this->ajaxReturn($json);
        }
        $json["status"] = 200;
        $json["info"] = "请求成功";
        $json["data"] = $award_list;
        $this->ajaxReturn($json);
    }
    public function buyBean(){
        $user_id = $this->user_id;
        $item_id = I("item_id",0);
        if(!$item_id){
            $json["status"] = 305;
            $json["info"] = "商品id不能等于0";
            $this->ajaxReturn($json);
        }
        $model_order = D("order");
        $shop_where = ["id"=>$item_id,"type"=>2];
        $game_shop = $model_order->getGameShopOne($shop_where);
        if(!$game_shop){
            $json["status"] = 306;
            $json["info"] = "商品不存在";
            $this->ajaxReturn($json);
        }
        $amount = $game_shop['price'];
        $ratio = $game_shop['coin']+$game_shop['bonus'];
        $model_award = D("award");
        $award_info = $model_award->getOne($user_id);
        if(!$award_info||$award_info["amount"]<$amount){
            $json["status"] = 307;
            $json["info"] = "佣金额度不足";
            $this->ajaxReturn($json);
        }
        $return_status = $model_award->updCommission($user_id,$amount);
        if(!$return_status){
            $json["status"] = 308;
            $json["info"] = "扣除佣金失败";
            $this->ajaxReturn($json);
        }
        $pay_number = $user_id.time();
        $extract_data["u_id"] = $user_id;
        $extract_data["commission"] = $amount;
        $extract_data["balance"] = $award_info["amount"]-$amount;
        $extract_data["status"] = 200;
        $extract_data["type"] = 2;
        $extract_data["add_date"] = time();
        $extract_data["order_number"] = $pay_number;
        
        $pay_data["u_id"] = $user_id;
        $pay_data["order_number"] = $pay_number;
        $pay_data["pay_type"] = 3;
        $pay_data["amount"] = $amount;
        $pay_data["ratio"] = $ratio;
        $pay_data["status"] = 200;
        $pay_data["add_date"] = time();
        $pay_data["item_id"] = $item_id;
        $pay_data["type"] = 3;
        
        $return_extract = $model_award->addAwardExtract($extract_data);
        if($return_extract){
            $model_order->add($pay_data);
            $extra = array(
                    'add' => array('type' => 'int','size' => 2,'value' => 1),
                    'coin' => array ('type' => 'int','size' => 4,'value' => $ratio),
                    'type' => array('type' => 'int','size' => 2,'value' => 90),
                    'cointype' => array('type' => 'int','size' => 2,'value' => 1)
            );
            $response = Sockets :: call('call_back', 10, 20, $user_id, $extra);
            add_log("callback.log", "commission", "佣金充值Socket返回数据". var_export($response, true));
            $json["status"] = 200;
            $json["info"] = "兑换成功";
            $this->ajaxReturn($json);
        }
        $json["status"] = 309;
        $json["info"] = "兑换失败";
        $this->ajaxReturn($json);
    }
    
    /**
     * 佣金提现
     */
    public function take(){
        $user_id = $this->user_id;
        $amount = I("amount",0); //佣金
        if(!$amount||$amount<50){
            $json["status"] = 305;
            $json["info"] = "提现金额不能小于50";
            $this->ajaxReturn($json);
        }
        $model_award = D("award");
        $model_user = D("user");
        $award_info = $model_award->getOne($user_id);
        if(!$award_info||$award_info["amount"]<$amount){
            $json["status"] = 307;
            $json["info"] = "佣金额度不足";
            $this->ajaxReturn($json);
        }
        $user_bank = $model_user->getUserBank($user_id);
        if(!$user_bank){
            $json["status"] = 308;
            $json["info"] = "请填写结算账户";
            $this->ajaxReturn($json);
        }
        $return_status = $model_award->updCommission($user_id,$amount);
        if(!$return_status){
            $json["status"] = 309;
            $json["info"] = "扣除佣金失败";
            $this->ajaxReturn($json);
        }
        $tx_amount = round(floatval($amount*80/100), 2);
        $pay_number = $user_id.time();
        $extract_data["u_id"] = $user_id;
        $extract_data["amount"] = $tx_amount;
        $extract_data["tax"] = $amount-$tx_amount;
        $extract_data["commission"] = $amount;
        $extract_data["balance"] = $award_info["amount"]-$amount;
        $extract_data["status"] = 300;
        $extract_data["type"] = 1;
        $extract_data["add_date"] = time();
        $extract_data["order_number"] = $pay_number;
        $return_extract = $model_award->addAwardExtract($extract_data);
        if($return_extract){
            $daifu = new Daifu();
            $url = "https://gwapi.yemadai.com/transfer/transferFixed";
            $data["accountNumber"] = 43769;
            $data["transId"] = $pay_number;
            $data["notifyURL"] = 'http://'.$_SERVER['HTTP_HOST']."/index/callback/df";
            $data["tt"] = 0;
            $data["bankCode"] = $user_bank["bank"];
            $data["provice"] = $user_bank["province"];
            $data["city"] = $user_bank["city"];
            $data["branchName"] = $user_bank["branch_name"];
            $data["accountName"] = $user_bank["name"];
            $data["cardNo"] = $user_bank["card"];
            $data["amount"] = $tx_amount;
            $data["remark"] = "佣金提现";
            $post_xml = $daifu->pay($data);
            $post_data["transData"] = $post_xml;
//            $result = curlSend($url,$post_data);
//                $result = HttpClient::post($url, $post_data);
            $result = file_get_contents($url."?transData=".$post_xml);
            $result = base64_decode($result);
            $xml = simplexml_load_string($result);
            $xml_arr = json_decode(json_encode($xml),TRUE);
            //add_log("daifu_data.log", "commission", "请求base64XML:". $post_xml);
            add_log("daifu_data.log", "commission", "佣金提现返回数据：". var_export($xml_arr, true));
            if($xml_arr["errCode"]=="0000"){
                $json["status"] = 200;
                $json["info"] = "提交成功";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR1001"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"IP白名单未绑定");
                $json["status"] = 310;
                $json["info"] = "IP白名单未绑定";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR1002"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"xml格式错误");
                $json["status"] = 311;
                $json["info"] = "xml格式错误";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR1003"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"secureCode验证错误");
                $json["status"] = 312;
                $json["info"] = "secureCode验证错误";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR1004"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"最大转账笔数超过50笔或者小于1笔");
                $json["status"] = 313;
                $json["info"] = "最大转账笔数超过50笔或者小于1笔";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR1005"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"含有必要参数为空");
                $json["status"] = 314;
                $json["info"] = "含有必要参数为空";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR1006"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"Base64解析错误");
                $json["status"] = 315;
                $json["info"] = "Base64解析错误";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR1007"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"账户错误或者不存在此账户");
                $json["status"] = 316;
                $json["info"] = "账户错误或者不存在此账户";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR1008"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"金额小于0");
                $json["status"] = 317;
                $json["info"] = "金额小于0";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR1009"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"金额错误");
                $json["status"] = 318;
                $json["info"] = "金额错误";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR1010"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"余额不足");
                $json["status"] = 319;
                $json["info"] = "余额不足";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR1011"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"系统异常");
                $json["status"] = 320;
                $json["info"] = "系统异常";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR1012"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"订单号重复");
                $json["status"] = 321;
                $json["info"] = "订单号重复";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR2001"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"开户名与卡号不匹配");
                $json["status"] = 322;
                $json["info"] = "开户名与卡号不匹配";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR2002"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"开户行与卡号不匹配");
                $json["status"] = 323;
                $json["info"] = "开户行与卡号不匹配";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR2003"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"省、市信息不匹配");
                $json["status"] = 324;
                $json["info"] = "省、市信息不匹配";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR5002"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"商户未开通下发权限");
                $json["status"] = 325;
                $json["info"] = "商户未开通下发权限";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR5003"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"下发超过单笔限额设置");
                $json["status"] = 326;
                $json["info"] = "下发超过单笔限额设置";
                $this->ajaxReturn($json);
            }elseif($xml_arr["errCode"]=="ERR5005"){
                $model_award->returnCommission($user_id,$amount,$pay_number,"商户下发超过单日限额");
                $json["status"] = 327;
                $json["info"] = "商户下发超过单日限额";
                $this->ajaxReturn($json);
            }else{
                $model_award->returnCommission($user_id,$amount,$pay_number,"提现失败");
                $json["status"] = 309;
                $json["info"] = "提现失败";
                $this->ajaxReturn($json);
            }
            
        }
        $json["status"] = 309;
        $json["info"] = "提现失败";
        $this->ajaxReturn($json);
    }
    /**
     * 我的推广福利
     */
    public function getAgency(){
        $user_id = $this->user_id;
        $model_user = D("User");
        $ug_info = $model_user->getUserAgencyByUserId($user_id);
        if(!$ug_info){
            $json["status"] = 305;
            $json["info"] = "数据不存在";
            $this->ajaxReturn($json);
        }
        $where["parent_id"] = $user_id;
        $ug_count = $model_user->getUserAgencyCount($where);
        $data["ug_count"] = $ug_count;
        $data["num"] = $ug_info["num"];
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $data;
        $this->ajaxReturn($json);
    }
    public function addAgency(){
        $user_id = $this->user_id;
        $model_user = D("User");
        $ug_info = $model_user->getUserAgencyByUserId($user_id);
        if(!$ug_info){
            $json["status"] = 305;
            $json["info"] = "数据不存在";
            $this->ajaxReturn($json);
        }
        if($ug_info["num"]<5){
            $json["status"] = 306;
            $json["info"] = "推广用户累计小于5人";
            $this->ajaxReturn($json);
        }
        $num = $ug_info["num"]-5;
        $r_status = $model_user->updateUserAgency(["u_id"=>$user_id],["num"=>$num]);
        if($r_status){
            $extra = array(
                    'add' => array('type' => 'int','size' => 2,'value' => 1),
                    'coin' => array ('type' => 'int','size' => 4,'value' => 88000),
                    'type' => array('type' => 'int','size' => 2,'value' => 91),
                    'cointype' => array('type' => 'int','size' => 2,'value' => 1)
            );
            $response = Sockets :: call('call_back', 10, 20, $user_id, $extra);
            $json["status"] = 200;
            $json["info"] = "领取成功";
            $json["data"] = $num;
            $this->ajaxReturn($json);
        }else{
            $json["status"] = 306;
            $json["info"] = "领取失败";
            $this->ajaxReturn($json);
        }
        
    }
}
