<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cli\Controller;

use Common\Common\Daifu;
class AwardController extends InitController {
    /**
     * 我的收益
     */
    public function index(){
        echo "this is index";
    }
    public function updDfOrder(){
        $model_award = D("award");
        $daifu = new Daifu();
        $url = "https://gwapi.yemadai.com/transfer/transferQueryFixed";
        $where["status"] = 300;
        $award_list = $model_award->getAwardExtractList($where);
        if($award_list&&!empty($award_list)){
            add_log("daifu_updOrder.log", "cli", "银行处理中数据". var_export($award_list, true));
            foreach ($award_list as $value) {
                $order_number = $value["order_number"];
                $user_id = $value["u_id"];
                $amount = $value["commission"];
                $data["merchantNumber"] = 43769;
                $data["mertransferID"] = $order_number;
                $data["requestTime"] = date("YmdHis");
                $post_xml = $daifu->getOrder($data);
                //$post_data["requestDomain"] = $post_xml;
                $result = file_get_contents($url."?requestDomain=".$post_xml);
                $result = base64_decode($result);
                $xml = simplexml_load_string($result);
                $xml_arr = json_decode(json_encode($xml),TRUE);
                if(isset($xml_arr["code"])&&$xml_arr["code"]=="0000"&&isset($xml_arr["transfer"])&&$xml_arr["transfer"]["state"]=="00"){
                    add_log("daifu_updOrder.log", "cli", "订单状态：". var_export($xml_arr, true));
                    $extract_data["status"]=200;
                    $extract_where["order_number"]=$xml_arr["transfer"]["mertransferID"];
                    $retrun_status = $model_award->updAwardExtract($extract_where,$extract_data);
                    if(!$retrun_status){
                        add_log("daifu_updOrder.log", "cli", $xml_arr["transfer"]["mertransferID"]."更新状态失败");
                    }
                }
                if(isset($xml_arr["code"])&&$xml_arr["code"]=="0000"&&isset($xml_arr["transfer"])&&$xml_arr["transfer"]["state"]=="11"){
                    add_log("daifu_updOrder.log", "cli", "订单状态：". var_export($xml_arr, true));
                    $extract_data["status"]=200;
                    $extract_where["order_number"]=$xml_arr["transfer"]["mertransferID"];
                    $retrun_status = $model_award->returnCommission($user_id,$amount,$order_number,"银行处理失败，资金退回",400);
                    if(!$retrun_status){
                        add_log("daifu_updOrder.log", "cli", $xml_arr["transfer"]["mertransferID"]."回滚失败");
                    }
                }
            }
        }
    }
}
