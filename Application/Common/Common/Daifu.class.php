<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Common;

/**
 * Description of Daifu
 *
 * @author Administrator
 */
use Common\Common\Rsa;
class Daifu {
    public function pay($data){
        if(empty($data)){
            return false;
        }
        $rsa = new Rsa();
        $enc = "transId=".$data['transId']."&accountNumber=".$data['accountNumber']."&cardNo=".$data['cardNo']."&amount=".$data['amount'];
        //$enc = "transId=1001161510286035&accountNumber=43769&cardNo=60138220000644697933&amount=0.8";
        //add_log("daifu_data.log", "commission", "请求签名明文:". $enc);
        // 使用私钥加密
        $rsaCode = $rsa->privateSHA1withRSAEncrypt($enc);
        // 这里使用base64是为了不出现乱码，默认加密出来的值有乱码
        $secureCode = base64_encode($rsaCode);
        //add_log("daifu_data.log", "commission", "请求签名SHAR1:". $rsaCode);
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><yemadai><accountNumber>'.$data['accountNumber'].'</accountNumber><notifyURL>'.$data['notifyURL'].'</notifyURL><tt>'.$data['tt'].'</tt><signType>RSA</signType><transferList><transId>'.$data['transId'].'</transId><bankCode>'.$data['bankCode'].'</bankCode><provice>'.$data['provice'].'</provice><city>'.$data['city'].'</city><branchName>'.$data['branchName'].'</branchName><accountName>'.$data['accountName'].'</accountName><cardNo>'.$data['cardNo'].'</cardNo><amount>'.$data['amount'].'</amount><remark>'.$data['remark'].'</remark><secureCode>'.$secureCode.'</secureCode></transferList></yemadai>';
        $transData = base64_encode($xml);
        return $transData;
    }
    public function getOrder($data){
        if(empty($data)){
            return false;
        }
        $rsa = new Rsa();
        $enc = $data['merchantNumber']."&".$data['requestTime'];
        //$enc = "transId=1001161510286035&accountNumber=43769&cardNo=60138220000644697933&amount=0.8";
        //add_log("daifu_data.log", "commission", "请求签名明文:". $enc);
        // 使用私钥加密
        $rsaCode = $rsa->privateSHA1withRSAEncrypt($enc);
        // 这里使用base64是为了不出现乱码，默认加密出来的值有乱码
        $secureCode = base64_encode($rsaCode);
        //add_log("daifu_data.log", "commission", "请求签名SHAR1:". $rsaCode);
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <yemadai>
                    <signType>RSA</signType>
                    <merchantNumber>'.$data['merchantNumber'].'</merchantNumber>
                    <mertransferID>'.$data['mertransferID'].'</mertransferID>
                    <sign>'.$secureCode.'</sign>
                    <queryTimeBegin></queryTimeBegin>
                    <queryTimeEnd></queryTimeEnd> 
                    <requestTime>'.$data['requestTime'].'</requestTime>  
                </yemadai>';
        $transData = base64_encode($xml);
        return $transData;
    }
}
