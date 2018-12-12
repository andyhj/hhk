<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Api\Controller;
use Common\Common\Sockets;
use Common\WxApi\JsApiPay;
use Common\WxApi\class_weixin_adv;
use QRcode;
use Common\Common\Custom;
class CustomController extends InitController {
    public function delcustom(){
        $custom_id = I("post.custom_id");
        if (!$custom_id) {
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $custom = new Custom();
        $return_status = $custom->delcustom($custom_id);
        if($return_status===11){
            $json["status"] = 306;
            $json["info"] = "比赛不存在";
            $this->ajaxReturn($json);
        }
        if($return_status===20){
            $json["status"] = 200;
            $json["info"] = "删除成功";
            $this->ajaxReturn($json);
        }
        if($return_status===13){
            $json["status"] = 320;
            $json["info"] = "删除失败";
            $this->ajaxReturn($json);
        }
        if($return_status===14){
            $json["status"] = 307;
            $json["info"] = "比赛进行中不能删除";
            $this->ajaxReturn($json);
        }
        if($return_status===15){
            $json["status"] = 308;
            $json["info"] = "奖品发放中不能删除";
            $this->ajaxReturn($json);
        }
    }
    /**
     * 添加比赛
     */
    public function addcustom(){
        $user_info = $this->user_info;
        $custom = D("Custom");
        
        $custom_user_info = $custom->getCustomUserOne($user_info["id"]);
        $where["u_id"] = $user_info["id"];
        $where["is_del"] = 0;
        $custom_info = $custom->getOne($where);
        if($custom_info){
            $json["status"] = 305;
            $json["info"] = "已经存在一个比赛，请先删除后再创建";
            $this->ajaxReturn($json);
        }
        
        //$game_id = 1;       //游戏id
        $max_number = 1;    //最大名次
        $data["name"] = I("post.name");      //名称
        $data["game_id"] = I("post.game_id",1);      //名称
        $data["number"] = I("post.number");  //比赛人数
        $data["type"] = 2;  //模式
        $data["inning"] = I("post.inning");  //场次
        $data["custom_time"] = I("post.custom_time");  //比赛时长
        $data["tickets"] = floatval(I("post.tickets"));  //门票
        $data["welfare"] = floatval(I("post.welfare"));  //推广福利
        $data["prizes1_range"] = I("post.prizes1_name")?"1-1":"";    //奖品1范围
        $data["prizes1_name"] = I("post.prizes1_name");      //奖品1名称
        $data["prizes1_value"] = floatval(I("post.prizes1_value"));        //奖品1价值
        $data["prizes2_range"] = I("post.prizes2_name")?"2-2":"";    //奖品2范围
        $data["prizes2_name"] = I("post.prizes2_name");      //奖品2名称
        $data["prizes2_value"] = floatval(I("post.prizes2_value"));        //奖品2价值
        $data["prizes3_range"] = I("post.prizes3_name")?"3-3":"";    //奖品3范围
        $data["prizes3_name"] = I("post.prizes3_name");      //奖品3名称
        $data["prizes3_value"] = floatval(I("post.prizes3_value"));        //奖品3价值
        $data["prizes4_range"] = I("post.prizes4_name")?"4-4":"";    //奖品4范围
        $data["prizes4_name"] = I("post.prizes4_name");      //奖品4名称
        $data["prizes4_value"] = floatval(I("post.prizes4_value"));        //奖品4价值
        $data["prizes5_range"] = I("post.prizes5_name")?"5-5":"";    //奖品5范围
        $data["prizes5_name"] = I("post.prizes5_name");      //奖品5名称
        $data["prizes5_value"] = floatval(I("post.prizes5_value"));        //奖品5价值
        $data["join_prizes_name"] = I("post.join_prizes_name");      //参与奖名称
        $data["join_prizes_value"] = floatval(I("post.join_prizes_value"));        //参与奖价值
        $data["join_prizes_number"] = I("post.join_prizes_number",0); 
        $data["start_date"] = I("post.start_date");  //比赛开始时间

        $cu_data["nickname"] = I("post.nickname");  //创建者名称
        $cu_data["mobile"] = I("post.mobile");  //手机号码
        $cu_data["phone"] = I("post.phone");  //固定电话
        $cu_data["card_id"] = I("post.card_id");  //身份证号码
        $cu_data["bank_card"] = I("post.bank_card");  //银行卡号
        $cu_data["card_name"] = I("post.card_name");  //持卡人姓名

        $word = C("WORD");
        if(!$data["name"]|| _strlen($data["name"])>32){
            $json["status"] = 306;
            $json["info"] = "比赛名称不能为空并且不能大于16个字";
            $this->ajaxReturn($json);
        }elseif(in_array($data["name"],$word)){
            $json["status"] = 306;
            $json["info"] = "比赛名字有敏感字存在！请重新输入";
            $this->ajaxReturn($json);
        }elseif($data["type"]==1&&(!$data["number"]||!is_int(intval($data["number"]))||intval($data["number"])<6)){
            $json["status"] = 306;
            $json["info"] = "比赛人数大于等于6人";
            $this->ajaxReturn($json);
        }elseif(!$data["inning"]||!is_int(intval($data["inning"]))||intval($data["inning"])>10){
            $json["status"] = 306;
            $json["info"] = "场次要大于1小于10次";
            $this->ajaxReturn($json);
        }elseif($data["type"]==2&&(!$data["number"]||!is_int(intval($data["number"]))||intval($data["number"])<1)){
            $json["status"] = 306;
            $json["info"] = "比赛轮数不能小于1";
            $this->ajaxReturn($json);
        }elseif(!$data["custom_time"]||!is_int(intval($data["custom_time"]))||intval($data["custom_time"])>120){
            $json["status"] = 306;
            $json["info"] = "比赛时长大于1小于120分钟";
            $this->ajaxReturn($json);
        }elseif($data["tickets"]>200){
            $json["status"] = 306;
            $json["info"] = "报名费不能大于200";
            $this->ajaxReturn($json);
        }elseif(!$data["tickets"]&&$data["welfare"]){
            $json["status"] = 306;
            $json["info"] = "报名费为0的时候推广福利必须为0";
            $this->ajaxReturn($json);
        }elseif($data["tickets"]&&($data["welfare"]>($data["tickets"]*0.9))){
            $json["status"] = 306;
            $json["info"] = "推广福利不能大于门票的90%价格";
            $this->ajaxReturn($json);
        }elseif(!$data["start_date"]){
            $json["status"] = 306;
            $json["info"] = "开始时间不能为空";
            $this->ajaxReturn($json);
        }elseif($data["start_date"]<= time()){
            $json["status"] = 306;
            $json["info"] = "至少提前一日申请";
            $this->ajaxReturn($json);
        }elseif(!$data["prizes1_name"]&&!$data["prizes2_name"]&&!$data["prizes3_name"]&&!$data["prizes4_name"]&&!$data["prizes5_name"]){
            $json["status"] = 306;
            $json["info"] = "必须设置一个奖品";
            $this->ajaxReturn($json);
        }elseif(_strlen($data["prizes1_name"])>32||_strlen($data["prizes2_name"])>32||_strlen($data["prizes3_name"])>32||_strlen($data["prizes4_name"])>32||_strlen($data["prizes5_name"])>32||_strlen($data["join_prizes_name"])>32){
            $json["status"] = 306;
            $json["info"] = "奖品名称不能大于16个字";
            $this->ajaxReturn($json);
        }elseif(in_array($data["prizes1_name"],$word)||in_array($data["prizes2_name"],$word)||in_array($data["prizes3_name"],$word)||in_array($data["prizes4_name"],$word)||in_array($data["prizes5_name"],$word)||in_array($data["join_prizes_name"],$word)){
            $json["status"] = 306;
            $json["info"] = "奖品名称有敏感字存在！请重新输入！";
            $this->ajaxReturn($json);
        }else{
            if(intval($data["prizes2_range2"])>$max_number){
                $max_number = intval($data["prizes2_range2"]);
            }
            if(intval($data["prizes3_range2"])>$max_number){
                $max_number = intval($data["prizes3_range2"]);
            }
            if($data["prizes1_name"]){
                $max_number = 1;
            }
            if($data["prizes2_name"]){
                $max_number = 2;
            }
            if($data["prizes3_name"]){
                $max_number = 3;
            }
            if($data["prizes4_name"]){
                $max_number = 4;
            }
            if($data["prizes5_name"]){
                $max_number = 5;
            }
//            if($data["join_prizes_number"]==0&&$data["join_prizes_name"]){
//                $max_number = $data["number"];
//            }
//            if($data["join_prizes_number"]>0&&$data["join_prizes_name"]){
//                $max_number = $max_number+$data["join_prizes_number"];
//            }
            $data["u_id"] = $user_info["id"];
            $data["max_number"] = $max_number;
            
            $data["add_date"] = time();
            //add_log("custom.log", "api", "添加比赛数据". var_export($data, true));
            $custom_id = $custom->addCustom($data);
            if($custom_user_info){
                $custom->updCustomUser(["id"=>$custom_user_info["id"]],$cu_data);
            }else{
                $cu_data["u_id"] = $user_info["id"];
                $return_status = $custom->addCustomUser($cu_data);
            }
            if($custom_id){
                $json["status"] = 200;
                $json["info"] = "提交成功";
                $json["data"] = $custom_id;
                $this->ajaxReturn($json);
            }else{
                $json["status"] = 320;
                $json["info"] = "提交失败";
                $this->ajaxReturn($json);
            }
        }
    }
    /**
     * 修改比赛
     */
    public function updcustom(){
        $user_info = $this->user_info;
        $custom = D("Custom");
        $custom_id = I("post.custom_id",0);
        if(!$custom_id){
            $json["status"] = 307;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $custom_user_info = $custom->getCustomUserOne($user_info["id"]);
        $where["id"] = $custom_id;
        $where["u_id"] = $user_info["id"];
        $where["is_del"] = 0;
        $where["audit_status"] = 2;
        $custom_info = $custom->getOne($where);
        if(!$custom_info){
            $json["status"] = 305;
            $json["info"] = "不能修改";
            $this->ajaxReturn($json);
        }
        
        $max_number = 1;    //最大名次
        $data["name"] = I("post.name");      //名称
        $data["number"] = I("post.number");  //比赛人数
        $data["type"] = 2;  //模式
        $data["inning"] = I("post.inning");  //场次
        $data["custom_time"] = I("post.custom_time");  //比赛时长
        $data["tickets"] = floatval(I("post.tickets"));  //门票
        $data["welfare"] = floatval(I("post.welfare"));  //推广福利
        $data["prizes1_range"] = I("post.prizes1_name")?"1-1":"";    //奖品1范围
        $data["prizes1_name"] = I("post.prizes1_name");      //奖品1名称
        $data["prizes1_value"] = floatval(I("post.prizes1_value"));        //奖品1价值
        $data["prizes2_range"] = I("post.prizes2_name")?"2-2":"";    //奖品2范围
        $data["prizes2_name"] = I("post.prizes2_name");      //奖品2名称
        $data["prizes2_value"] = floatval(I("post.prizes2_value"));        //奖品2价值
        $data["prizes3_range"] = I("post.prizes3_name")?"3-3":"";    //奖品3范围
        $data["prizes3_name"] = I("post.prizes3_name");      //奖品3名称
        $data["prizes3_value"] = floatval(I("post.prizes3_value"));        //奖品3价值
        $data["prizes4_range"] = I("post.prizes4_name")?"4-4":"";    //奖品4范围
        $data["prizes4_name"] = I("post.prizes4_name");      //奖品4名称
        $data["prizes4_value"] = floatval(I("post.prizes4_value"));        //奖品4价值
        $data["prizes5_range"] = I("post.prizes5_name")?"5-5":"";    //奖品5范围
        $data["prizes5_name"] = I("post.prizes5_name");      //奖品5名称
        $data["prizes5_value"] = floatval(I("post.prizes5_value"));        //奖品5价值
        $data["join_prizes_name"] = I("post.join_prizes_name");      //参与奖名称
        $data["join_prizes_value"] = floatval(I("post.join_prizes_value"));        //参与奖价值
        $data["join_prizes_number"] = I("post.join_prizes_number",0); 
        $data["start_date"] = I("post.start_date");  //比赛开始时间
             add_log("custom.log", "api", "比赛修改数据1：". var_export($data, true));

        $cu_data["nickname"] = I("post.nickname");  //创建者名称
        $cu_data["mobile"] = I("post.mobile");  //手机号码
        $cu_data["phone"] = I("post.phone");  //固定电话
        $cu_data["card_id"] = I("post.card_id");  //身份证号码
        $cu_data["bank_card"] = I("post.bank_card");  //银行卡号
        $cu_data["card_name"] = I("post.card_name");  //持卡人姓名

        $word = C("WORD");
        if(!$data["name"]|| _strlen($data["name"])>32){
            $json["status"] = 306;
            $json["info"] = "比赛名称不能为空并且不能大于16个字";
            $this->ajaxReturn($json);
        }elseif(in_array($data["name"],$word)){
            $json["status"] = 306;
            $json["info"] = "比赛名字有敏感字存在！请重新输入";
            $this->ajaxReturn($json);
        }elseif($data["type"]==1&&(!$data["number"]||!is_int(intval($data["number"]))||intval($data["number"])<6)){
            $json["status"] = 306;
            $json["info"] = "比赛人数大于等于6人";
            $this->ajaxReturn($json);
        }elseif(!$data["inning"]||!is_int(intval($data["inning"]))||intval($data["inning"])>10){
            $json["status"] = 306;
            $json["info"] = "场次要大于1小于10次";
            $this->ajaxReturn($json);
        }elseif($data["type"]==2&&(!$data["number"]||!is_int(intval($data["number"]))||intval($data["number"])<1)){
            $json["status"] = 306;
            $json["info"] = "比赛轮数不能小于1";
            $this->ajaxReturn($json);
        }elseif(!$data["custom_time"]||!is_int(intval($data["custom_time"]))||intval($data["custom_time"])>120){
            $json["status"] = 306;
            $json["info"] = "比赛时长大于1小于120分钟";
            $this->ajaxReturn($json);
        }elseif($data["tickets"]>200){
            $json["status"] = 306;
            $json["info"] = "报名费不能大于200";
            $this->ajaxReturn($json);
        }elseif(!$data["tickets"]&&$data["welfare"]){
            $json["status"] = 306;
            $json["info"] = "报名费为0的时候推广福利必须为0";
            $this->ajaxReturn($json);
        }elseif($data["tickets"]&&($data["welfare"]>($data["tickets"]*0.9))){
            $json["status"] = 306;
            $json["info"] = "推广福利不能大于门票的90%价格";
            $this->ajaxReturn($json);
        }elseif(!$data["start_date"]){
            $json["status"] = 306;
            $json["info"] = "开始时间不能为空";
            $this->ajaxReturn($json);
        }elseif($data["start_date"]<= time()){
            $json["status"] = 306;
            $json["info"] = "至少提前一日申请";
            $this->ajaxReturn($json);
        }elseif(!$data["prizes1_name"]&&!$data["prizes2_name"]&&!$data["prizes3_name"]&&!$data["prizes4_name"]&&!$data["prizes5_name"]){
            $json["status"] = 306;
            $json["info"] = "必须设置一个奖品";
            $this->ajaxReturn($json);
        }elseif(_strlen($data["prizes1_name"])>32||_strlen($data["prizes2_name"])>32||_strlen($data["prizes3_name"])>32||_strlen($data["prizes4_name"])>32||_strlen($data["prizes5_name"])>32||_strlen($data["join_prizes_name"])>32){
            $json["status"] = 306;
            $json["info"] = "奖品名称不能大于16个字";
            $this->ajaxReturn($json);
        }elseif(in_array($data["prizes1_name"],$word)||in_array($data["prizes2_name"],$word)||in_array($data["prizes3_name"],$word)||in_array($data["prizes4_name"],$word)||in_array($data["prizes5_name"],$word)||in_array($data["join_prizes_name"],$word)){
            $json["status"] = 306;
            $json["info"] = "奖品名称有敏感字存在！请重新输入！";
            $this->ajaxReturn($json);
        }else{
            if(intval($data["prizes2_range2"])>$max_number){
                $max_number = intval($data["prizes2_range2"]);
            }
            if(intval($data["prizes3_range2"])>$max_number){
                $max_number = intval($data["prizes3_range2"]);
            }
            if($data["prizes1_name"]){
                $max_number = 1;
            }
            if($data["prizes2_name"]){
                $max_number = 2;
            }
            if($data["prizes3_name"]){
                $max_number = 3;
            }
            if($data["prizes4_name"]){
                $max_number = 4;
            }
            if($data["prizes5_name"]){
                $max_number = 5;
            }
//            if($data["join_prizes_number"]==0&&$data["join_prizes_name"]){
//                $max_number = $data["number"];
//            }
//            if($data["join_prizes_number"]>0&&$data["join_prizes_name"]){
//                $max_number = $max_number+$data["join_prizes_number"];
//            }
            $data["max_number"] = $max_number;
            $data["audit_status"] = 0;
             //add_log("custom.log", "api", "比赛修改数据2：". var_export($data, true));
            $return_status = $custom->updCustom(["id"=>$custom_id],$data);
            if($custom_user_info){
                $custom->updCustomUser(["id"=>$custom_user_info["id"]],$cu_data);
            }else{
                $cu_data["u_id"] = $user_info["id"];
                $return_status = $custom->addCustomUser($cu_data);
            }
            if($return_status){
                $json["status"] = 200;
                $json["info"] = "提交成功";
                $this->ajaxReturn($json);
            }else{
                $json["status"] = 320;
                $json["info"] = "提交失败";
                $this->ajaxReturn($json);
            }
        }
    }
    /**
     * 删除比赛
     */
    public function remcustom(){
        $id = I("post.custom_id",0);
        if(!$id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $user_info = $this->user_info;
        $model_custom = D("Custom");
        $where["u_id"] = $user_info["id"];
        $where["id"] = $id;
        $custom_info = $model_custom->getOne($where);
        if($custom_info&&$custom_info["status"]==1){
            $json["status"] = 309;
            $json["info"] = "比赛开始不能删除";
            $this->ajaxReturn($json);
        }
        $custom = new Custom();
        $return_status = $custom->delcustom($id);
        if($return_status===11){
            $json["status"] = 306;
            $json["info"] = "比赛不存在";
            $this->ajaxReturn($json);
        }
        if($return_status===20){
            $json["status"] = 200;
            $json["info"] = "删除成功";
            $this->ajaxReturn($json);
        }
        if($return_status===13){
            $json["status"] = 320;
            $json["info"] = "删除失败";
            $this->ajaxReturn($json);
        }
        if($return_status===14){
            $json["status"] = 307;
            $json["info"] = "比赛进行中不能删除";
            $this->ajaxReturn($json);
        }
        if($return_status===15){
            $json["status"] = 308;
            $json["info"] = "奖品发放中不能删除";
            $this->ajaxReturn($json);
        }
    }
    /**
     * 比赛二维码
     */
    public function customcode(){
        $custom_id = I("custom_id",0);
        if(!$custom_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/WxPay.JsApiPay.php";
        require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
        $weixin = new class_weixin_adv();
        $user_info = $this->user_info;
        $custom = D("Custom");
        $where["id"] = $custom_id;
        $where["u_id"] = $user_info["id"];
        $where["is_del"] = 0;
        $where["audit_status"] = 1;
        $custom_info = $custom->getOne($where);
        if(!$custom_info){
            $json["status"] = 306;
            $json["info"] = "房间不存在";
            $this->ajaxReturn($json);
        }
        $c_where["c_id"] = $custom_id;
        $c_where["u_id"] = $user_info["id"];
        $code_info = $custom->getCodeOne($c_where);
        $code_id = 0;
        if($code_info){
            $code_id = $code_info["id"];
        }else{
            $c_where["c_id"] = 0;
            $c_where["u_id"] = 0;
            $code_info = $custom->getCodeOne($c_where);
            if($code_info){
                $code_status = $custom->updCustomCode(["id"=>$code_info["id"]],["u_id"=>$user_info["id"],"c_id"=>$custom_id]);
                if($code_status){
                    $code_id = $code_info["id"];
                }
            }else{
                $code_count = $custom->getCodeCount();
                if($code_count<100000){
                    $c_data["u_id"] = $user_info["id"];
                    $c_data["c_id"] = $custom_id;
                    $code_status = $custom->addCustomCode($c_data);
                    if($code_status){
                        $code_id = $code_status;
                    }
                }
            }
        }
        if(!$code_id){
            $json["status"] = 307;
            $json["info"] = "生成二维码失败";
            $this->ajaxReturn($json);
        }
        $code_date = $custom_info["code_date"];
        $paths = APP_ROOT."Public/file/custom/";
        $filename = $code_id."_qrcode.jpg"; //新文件名字
        if(!file_exists($paths)){
            mkdir ($paths, 0777);//创建二维码存放文件夹
        }else{
            if(!file_exists($paths.$filename)){
                //if(($custom_info["code_date"]+(60*60*24*30))<= time()){
                    //1,永久二维码，2，临时二维码，有效期30天
                    $image = $weixin->create_qrcode(1,$code_id);
                    $imageinfo = curl_get($image);
                    $local_file = fopen($paths.$filename, 'w');
                    //如果没有打开文件，进行写入操作
                    if(false !==$local_file){
                        if(false !==fwrite($local_file, $imageinfo['body'])){
                            $data["code_date"] = time();
                            $code_date = $data["code_date"];
                            $custom->updCustom($where,$data);
                            fclose($local_file);
                        }
                    }
                //}
                
            }else{
                //已经存在的二维码不执行
            }
        }
        $json["status"] = 200;
        $json["info"] = "生成二维码成功";
        $json["data"] = $this->http.$_SERVER['HTTP_HOST']."/Public/file/custom/".$filename;
        $this->ajaxReturn($json);
    }
    /**
     * 自定义比赛列表
     */
    public function custom(){
        $user_info = $this->user_info;
        $custom = D("Custom");
        $game_id = I("game_id",1);
        $where["u_id"] = $user_info["id"];
        $where["is_del"] = 0;
        $where["game_id"] = $game_id;
        $custom_info = $custom->getOne($where);
        if(!$custom_info){
            $json["status"] = 305;
            $json["info"] = "没有比赛信息";
            $this->ajaxReturn($json);
        }
        if($custom_info["status"]==2){
            $this->rankingList($custom_info["id"]);
        }
        $apply_info = $custom->getApplyOne(["gameid"=>$custom_info["id"],"uid"=>$user_info["id"]]);
        $custom_info["is_join"] = 0;
        if($apply_info){
            $custom_info["is_join"] = 1;
        }
        $custom_info["custom_url"] = $this->http.$_SERVER['HTTP_HOST']."/index/index/custom.html?custom_id=".$custom_info["id"];
        $custom_user_info = $custom->getCustomUserOne($user_info["id"]);
        $custom_info["nickname"] = $custom_user_info["nickname"];
        $custom_info["mobile"] = $custom_user_info["mobile"];
        $custom_info["phone"] = $custom_user_info["phone"];
        $custom_info["card_id"] = $custom_user_info["card_id"];
        $custom_info["bank_card"] = $custom_user_info["bank_card"];
        $custom_info["card_name"] = $custom_user_info["card_name"];
        $data["custom_info"] = $custom_info;
        $data["custom_code"] = "";
        if($custom_info["audit_status"]==1){
            require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/WxPay.JsApiPay.php";
            require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            $c_where["c_id"] = $custom_info["id"];
            $c_where["u_id"] = $user_info["id"];
            $code_info = $custom->getCodeOne($c_where);
            $code_id = 0;
            if($code_info){
                $code_id = $code_info["id"];
            }else{
                $c_where["c_id"] = 0;
                $c_where["u_id"] = 0;
                $code_info = $custom->getCodeOne($c_where);
                if($code_info){
                    $code_status = $custom->updCustomCode(["id"=>$code_info["id"]],["u_id"=>$user_info["id"],"c_id"=>$custom_info["id"],"game_type"=>$custom_info["game_id"]]);
                    if($code_status){
                        $code_id = $code_info["id"];
                    }
                }else{
                    $code_count = $custom->getCodeCount();
                    if($code_count<100000){
                        $c_data["u_id"] = $user_info["id"];
                        $c_data["c_id"] = $custom_info["id"];
                        $c_data["game_type"] = $custom_info["game_id"];
                        $code_status = $custom->addCustomCode($c_data);
                        if($code_status){
                            $code_id = $code_status;
                        }
                    }
                }
            }
            if(!$code_id){
                $json["status"] = 307;
                $json["info"] = "生成二维码失败";
                $this->ajaxReturn($json);
            }
            $code_date = $custom_info["code_date"];
            $paths = APP_ROOT."Public/file/custom/";
            $filename = $code_id."_qrcode.jpg"; //新文件名字
            if(!file_exists($paths)){
                mkdir ($paths, 0777);//创建二维码存放文件夹
            }else{
                if(!file_exists($paths.$filename)){
                    //if(($custom_info["code_date"]+(60*60*24*30))<= time()){
                        //1,永久二维码，2，临时二维码，有效期30天
                        $image = $weixin->create_qrcode(1,$code_id);
                        $imageinfo = curl_get($image);
                        $local_file = fopen($paths.$filename, 'w');
                        //如果没有打开文件，进行写入操作
                        if(false !==$local_file){
                            if(false !==fwrite($local_file, $imageinfo['body'])){
                                $data["code_date"] = time();
                                $code_date = $data["code_date"];
                                $custom->updCustom($where,$data);
                                fclose($local_file);
                            }
                        }
                    //}

                }else{
                    //已经存在的二维码不执行
                }
            }
            $data["custom_code"] = $this->http.$_SERVER['HTTP_HOST']."/Public/file/custom/".$filename;
        }
        $json["status"] = 200;
        $json["info"] = "获取成功";
        $json["data"] = $data;
        $this->ajaxReturn($json);
    }
    /**
     * 加入比赛列表
     */
    public function apply(){
        $custom = D("Custom");
        $user_info = $this->user_info;
//        $sql = "SELECT * FROM tb_custom_apply WHERE uid=".$user_info["id"]." AND gameid IN(SELECT id FROM tb_custom WHERE is_del=0)";
//        $apply_list = $custom->getApplyListBySql($sql);
        $game_type = I("gameType",1);
        $where["is_del"] = 0;
        $where["audit_status"] = 1;
        $where["game_id"] = $game_type;
        $where["status"] = array('NEQ', 2);
        $where["start_date"] = array('EGT', time()-604800);
        $custom_list = $custom->getList($where);
        if(!$custom_list){
            $json["status"] = 305;
            $json["info"] = "没有数据";
            $this->ajaxReturn($json);
        }
        $apply_data = [];
        foreach ($custom_list as $value) {
//            $custom_info = $custom->getOne(["id"=>$value["gameid"]]);
            $custom_user_info = $custom->getCustomUserOne($value["u_id"]);
            $custom_apply_info = $custom->getApplyOne(["uid"=>$user_info["id"],"gameid"=>$value["id"]]);
            $apply["is_apply"] = 0;
            if($custom_apply_info){
                $apply["is_apply"] = 1;
            }
            $apply["custom_id"] = $value["id"];
            $apply["name"] = $value["name"];
            $apply["nickname"] = $custom_user_info["nickname"];
            $apply["start_date"] = $value["start_date"];
            $apply["number"] = $value["number"];
            $apply["status"] = $value["status"];
            $apply["type"] = $value["type"];
            $apply["apply_url"] = HTTP_HOST."/index/index/custom.html?custom_id=".$value["id"];
            
            $apply_data[] = $apply;
        }
        $json["status"] = 200;
        $json["info"] = "获取成功";
        $json["data"] = $apply_data;
        $this->ajaxReturn($json);
    }
    
    /**
     * 开心杯报名
     */
    public function capply(){
//        $arr = [100170,100172,100173,100208,100175,100176,100174,100325];
//        if(!in_array($this->user_id, $arr)){
//            $json["status"] = 311;
//            $json["info"] = "暂未开放";
//            $this->ajaxReturn($json);
//        }
        $custom_id = I("custom_id");
        if(!$custom_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $user_info = $this->user_info;
        $custom = D("Custom");
        $m_user = D("User");
        $custom_info = $custom->getOne(["id"=>$custom_id,"is_del"=>0]);
        if(!$custom_info){
            $json["status"] = 306;
            $json["info"] = "比赛不存在";
            $this->ajaxReturn($json);
        }
        if($custom_info["status"]==2||$custom_info["status"]==3){
            $json["status"] = 307;
            $json["info"] = "比赛已结束";
            $this->ajaxReturn($json);
        }
        if($custom_info["status"]==1){
            $json["status"] = 308;
            $json["info"] = "比赛进行中";
            $this->ajaxReturn($json);
        }
        $ca_where["gameid"] = $custom_info["id"];
        $ca_where["uid"] = $user_info["id"];
        $ca_info = $custom->getApplyOne($ca_where);
        if($ca_info){
            $json["status"] = 309;
            $json["info"] = "您已经报名";
            $this->ajaxReturn($json);
        }
        require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
        $weixin = new class_weixin_adv();
        $game_user = $m_user->getGameUserOne(["uid"=>$this->user_id]);
        if($game_user){
            $ca_data["gameid"] = $custom_info["id"];
            $ca_data["uid"] = $user_info["id"];
            $ca_data["applytime"] = time();
//            $ca_data["mobile"] = $mobile;
            $return_ca_id = $custom->addCustomApply($ca_data);
            if($return_ca_id){
                $dtime = "";
                $fdate=formatDate($custom_info["start_date"],time());
                if($fdate["d"]>0){
                    $dtime = $fdate["d"]."天";
                }
                if($fdate["h"]>0){
                    $dtime .= $fdate["h"]."小时";
                }
                if($fdate["i"]>0){
                    $dtime .= $fdate["i"]."分钟";
                }
                if($fdate["s"]>0){
                    $dtime .= $fdate["s"]."秒";
                }
                $msg_title = "您报名的".$custom_info["name"]."还有".$dtime."开始";
                $msg_url = 'https://'.$_SERVER['HTTP_HOST'].'/index/index/custom/?custom_id='.$custom_info["id"];
                $custom_wx_msg = $m_user->wxCustomMsg($user_info["id"],$msg_title,$custom_info["name"],$custom_info["start_date"],$msg_url);
                $custom_wx_msg_return = $weixin->send_user_message($custom_wx_msg);
                add_log("custom.log", "game", "开赛提醒公众号消息推送状态：". var_export($custom_wx_msg_return, true));
                $json["status"] = 200;
                $json["info"] = "报名成功";
                $json["data"] = $custom_id;
                $this->ajaxReturn($json);
            }else{
                $json["status"] = 310;
                $json["info"] = "报名失败";
                $this->ajaxReturn($json);
            }
        }else{
            $json["status"] = 311;
            $json["info"] = "用户不存在";
            $this->ajaxReturn($json);
        }
    }
    /**
     * 奖品列表
     */
    private function rankingList($custom_id){
        $custom = D("Custom");
        $user = D("User");
        if(!$custom_id){
            $json["status"] = 305;
            $json["info"] = "比赛id不能为空";
            $this->ajaxReturn($json);
        }
        $where["c_id"] = $custom_id;
        $ranking_list = $custom->getCustomRanking($where);
        if(!$ranking_list){
            $json["status"] = 306;
            $json["info"] = "没有数据";
            $this->ajaxReturn($json);
        }
        $ranking_data = [];
        foreach ($ranking_list as $value) {
            $apply_info = $custom->getApplyOne(["gameid"=>$value["c_id"],"uid"=>$value["u_id"]]);
            $user_info = $user->getUserOne($value["u_id"]);
            $ranking["ranking"] = $value["ranking"];
            $ranking["u_id"] = $value["u_id"];
            $ranking["nickname"] = $user_info["nickname"];
            $ranking["mobile"] = $apply_info["mobile"];
            if($value["prizes1_name"]){
                $ranking["prizes_name"] = $value["prizes1_name"];
                $ranking["prizes_value"] = $value["prizes1_value"];
                if($value["prizes1_exchange"]==0){
                    $ranking["prizes_exchange"] = "未发放";
                }
                if($value["prizes1_exchange"]==1){
                    $ranking["prizes_exchange"] = "已发放";
                }
                if($value["prizes1_exchange"]==2){
                    $ranking["prizes_exchange"] = "已领取";
                }
            }
            if($value["prizes2_name"]){
                $ranking["prizes_name"] = $value["prizes2_name"];
                $ranking["prizes_value"] = $value["prizes2_value"];
                if($value["prizes2_exchange"]==0){
                    $ranking["prizes_exchange"] = "未发放";
                }
                if($value["prizes2_exchange"]==1){
                    $ranking["prizes_exchange"] = "已发放";
                }
                if($value["prizes2_exchange"]==2){
                    $ranking["prizes_exchange"] = "已领取";
                }
            }
            if($value["prizes3_name"]){
                $ranking["prizes_name"] = $value["prizes3_name"];
                $ranking["prizes_value"] = $value["prizes3_value"];
                if($value["prizes3_exchange"]==0){
                    $ranking["prizes_exchange"] = "未发放";
                }
                if($value["prizes3_exchange"]==1){
                    $ranking["prizes_exchange"] = "已发放";
                }
                if($value["prizes3_exchange"]==2){
                    $ranking["prizes_exchange"] = "已领取";
                }
            }
            if($value["prizes4_name"]){
                $ranking["prizes_name"] = $value["prizes4_name"];
                $ranking["prizes_value"] = $value["prizes4_value"];
                if($value["prizes4_exchange"]==0){
                    $ranking["prizes_exchange"] = "未发放";
                }
                if($value["prizes4_exchange"]==1){
                    $ranking["prizes_exchange"] = "已发放";
                }
                if($value["prizes4_exchange"]==2){
                    $ranking["prizes_exchange"] = "已领取";
                }
            }
            if($value["prizes5_name"]){
                $ranking["prizes_name"] = $value["prizes5_name"];
                $ranking["prizes_value"] = $value["prizes5_value"];
                if($value["prizes5_exchange"]==0){
                    $ranking["prizes_exchange"] = "未发放";
                }
                if($value["prizes5_exchange"]==1){
                    $ranking["prizes_exchange"] = "已发放";
                }
                if($value["prizes5_exchange"]==2){
                    $ranking["prizes_exchange"] = "已领取";
                }
            }
            if($value["join_prizes_name"]){
                $ranking["prizes_name"] = $value["join_prizes_name"];
                $ranking["prizes_value"] = $value["join_prizes_value"];
                if($value["join_prizes_exchange"]==0){
                    $ranking["prizes_exchange"] = "未发放";
                }
                if($value["join_prizes_exchange"]==1){
                    $ranking["prizes_exchange"] = "已发放";
                }
                if($value["join_prizes_exchange"]==2){
                    $ranking["prizes_exchange"] = "已领取";
                }
            }
            
            $ranking_data[] = $ranking;
        }
        $json["status"] = 200;
        $json["info"] = "获取成功";
        $json["msg"] = 1;
        $json["data"] = $ranking_data;
        $this->ajaxReturn($json);
    }
    
    /**
     * 奖品列表
     */
    public function ranking(){
        $custom = D("Custom");
        $user = D("User");
        $custom_id = I("custom_id",0);
        if(!$custom_id){
            $json["status"] = 305;
            $json["info"] = "比赛id不能为空";
            $this->ajaxReturn($json);
        }
        $where["gameid"] = $custom_id;
        $apply_list = $custom->getApplyList($where,"ranking ASC");
        if(!$apply_list){
            $json["status"] = 306;
            $json["info"] = "没有数据";
            $this->ajaxReturn($json);
        }
        $custom_info = $custom->getOne(["id"=>$custom_id]);
        $apply_data = [];
        foreach ($apply_list as $value) {
            if($value["ranking"]>0){
                if(!$custom_info["join_prizes_name"]&&$value["ranking"]>$custom_info["max_number"]){
                    break;
                }
                $user_info = $user->getUserOne($value["uid"]);
                $ranking["ranking"] = $value["ranking"];
                $ranking["u_id"] = $value["uid"];
                $ranking["nickname"] = $user_info["nickname"];
                $ranking["mobile"] = $value["mobile"];
                $ranking["prizes_name"] = "";
                $ranking["prizes_value"] = "";
                $ranking["is_ppt"] = 0;
                $hj = 0;
                if($custom_info["prizes1_name"]){
                    $range1 = explode("-", $custom_info["prizes1_range"]);
                    if($value["ranking"]>=$range1[0]&&$value["ranking"]<=$range1[1]){
                        $hj = 1;
                        $ranking["prizes_name"] = $custom_info["prizes1_name"];
                        $ranking["prizes_value"] = $custom_info["prizes1_value"];
                    }
                }
                if($custom_info["prizes2_name"]){
                    $range2 = explode("-", $custom_info["prizes2_range"]);
                    if($value["ranking"]>=$range2[0]&&$value["ranking"]<=$range2[1]){
                        $hj = 1;
                        $ranking["prizes_name"] = $custom_info["prizes2_name"];
                        $ranking["prizes_value"] = $custom_info["prizes2_value"];
                    }
                }
                if($custom_info["prizes3_name"]){
                    $range3 = explode("-", $custom_info["prizes3_range"]);
                    if($value["ranking"]>=$range3[0]&&$value["ranking"]<=$range3[1]){
                        $hj = 1;
                        $ranking["prizes_name"] = $custom_info["prizes3_name"];
                        $ranking["prizes_value"] = $custom_info["prizes3_value"];
                    }
                }
                if($custom_info["prizes4_name"]){
                    $range4 = explode("-", $custom_info["prizes4_range"]);
                    if($value["ranking"]>=$range4[0]&&$value["ranking"]<=$range4[1]){
                        $hj = 1;
                        $ranking["prizes_name"] = $custom_info["prizes4_name"];
                        $ranking["prizes_value"] = $custom_info["prizes4_value"];
                    }
                }
                if($custom_info["prizes5_name"]){
                    $range5 = explode("-", $custom_info["prizes5_range"]);
                    if($value["ranking"]>=$range5[0]&&$value["ranking"]<=$range5[1]){
                        $hj = 1;
                        $ranking["prizes_name"] = $custom_info["prizes5_name"];
                        $ranking["prizes_value"] = $custom_info["prizes5_value"];
                    }
                }
                if(!$hj&&$custom_info["join_prizes_name"]){
                    $ranking["prizes_name"] = $custom_info["join_prizes_name"];
                    $ranking["prizes_value"] = $custom_info["join_prizes_value"];
                    $ranking["is_ppt"] = 1;
                }
                if($ranking["prizes_name"]){
                    $apply_data[] = $ranking;
                }
            }
        }
        $json["status"] = 200;
        $json["info"] = "获取成功";
        $json["msg"] = 1;
        $json["data"] = $apply_data;
        $this->ajaxReturn($json);
    }
    /**
     * 比赛排名奖励
     */
    public function prizes(){
        $custom_id = I("custom_id",0);
        if(!$custom_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $custom = D("Custom");
        $where["is_del"] = 0;
        $where["id"] = $custom_id;
        $custom_info = $custom->getOne($where);
        if(!$custom_info){
            $json["status"] = 306;
            $json["info"] = "没有比赛信息";
            $this->ajaxReturn($json);
        }
        $prizes_list["start_date"] = $custom_info["start_date"];
        $prizes_list["name"] = $custom_info["name"];
        $prizes_list["number"] = $custom_info["number"];
        $prizes_list["custom_time"] = $custom_info["custom_time"];
        $prizes_arr = [];
        if($custom_info["prizes1_name"]){
            $piz1["ranking"] = 1;
            $piz1["name"] = $custom_info["prizes1_name"];
            $piz1["value"] = $custom_info["prizes1_value"];
            $prizes_arr[] = $piz1;
        }
        if($custom_info["prizes2_name"]){
            $piz2["ranking"] = 2;
            $piz2["name"] = $custom_info["prizes2_name"];
            $piz2["value"] = $custom_info["prizes2_value"];
            $prizes_arr[] = $piz2;
        }
        if($custom_info["prizes3_name"]){
            $piz3["ranking"] = 3;
            $piz3["name"] = $custom_info["prizes3_name"];
            $piz3["value"] = $custom_info["prizes3_value"];
            $prizes_arr[] = $piz3;
        }
        if($custom_info["prizes4_name"]){
            $piz4["ranking"] = 4;
            $piz4["name"] = $custom_info["prizes4_name"];
            $piz4["value"] = $custom_info["prizes4_value"];
            $prizes_arr[] = $piz4;
        }
        if($custom_info["prizes5_name"]){
            $piz5["ranking"] = 5;
            $piz5["name"] = $custom_info["prizes5_name"];
            $piz5["value"] = $custom_info["prizes5_value"];
            $prizes_arr[] = $piz5;
        }
        if($custom_info["join_prizes_name"]){
            $piz5["ranking"] = 6;
            $piz5["name"] = $custom_info["join_prizes_name"];
            $piz5["value"] = $custom_info["join_prizes_value"];
            $prizes_arr[] = $piz5;
        }
        $prizes_list["prizes"] = $prizes_arr;
        $json["status"] = 200;
        $json["info"] = "获取成功";
        $json["data"] = $prizes_list;
        $this->ajaxReturn($json);
    }
    /**
     * 比赛报名人数
     */
    public function numapply(){
        $custom_id = I("custom_id",0);
        if(!$custom_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $custom = D("Custom");
        $where["gameid"] = $custom_id;
        $num = $custom->getApplyCount($where);
        $json["status"] = 200;
        $json["info"] = "获取成功";
        $json["data"] = $num;
        $this->ajaxReturn($json);
    }
    /**
     * 官方赛事列表
     */
    public function ocustom(){
        $user_info = $this->user_info;
        $ocustom = D("OfficialCustom");
        $game_id = I("game_id",1);
        $where["game_id"] = $game_id;
        $where["is_del"] = 0;
        $ocustom_list = $ocustom->getList($where);
        $ocustom_arr = [];
        if($ocustom_list){
            foreach ($ocustom_list as $value) {
                $id = $value["id"];
                $ocustom_apply = $ocustom->getApplyOne(["gameid"=>$id,"uid"=>$user_info["id"]]);
                $ocustom_apply_count = $ocustom->getApplyCount(["gameid"=>$id]);
                $value["is_apply"] = 0;
                $value["apply_count"] = 0;
                if($ocustom_apply){
                    $value["is_apply"] = 1;
                }
                if($ocustom_apply_count){
                    $value["apply_count"] = $ocustom_apply_count;
                }
                $ocustom_arr[] = $value;
            }
            $json["status"] = 200;
            $json["info"] = "成功";
            $json["data"] = $ocustom_arr;
            $this->ajaxReturn($json);
        }
        $json["status"] = 305;
        $json["info"] = "没有数据";
        $this->ajaxReturn($json);
    }
    /**
     * 官方赛事用户获奖列表
     */
    public function userRankingList(){
        $user_info = $this->user_info;
        $ocustom = D("OfficialCustom");
        $where["u_id"] = $user_info["id"];
        $ocustom_ranking_list = $ocustom->getCustomRanking($where,"add_time DESC");
        $ocustom_ranking_arr = [];
        if($ocustom_ranking_list){
            foreach ($ocustom_ranking_list as $value) {
                if($value["prizes_name"]&&$value["prizes_value"]){
                    $c_id = $value["c_id"];
                    $ocustom_info = $ocustom->getOne(["id"=>$c_id]);
                    $value["custom_date"] = $ocustom_info["start_date"];
                    $ocustom_ranking_arr[] = $value;
                }
            }
            $json["status"] = 200;
            $json["info"] = "成功";
            $json["data"] = $ocustom_ranking_arr;
            $this->ajaxReturn($json);
        }
        $json["status"] = 305;
        $json["info"] = "没有数据";
        $this->ajaxReturn($json);
    }
    /**
     * 官方赛事详情
     */
    public function ocustomdes(){
        $custom_id = I("custom_id");
        if(!$custom_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $ocustom = D("OfficialCustom");
        $ocustom_info = $ocustom->getOne(["id"=>$custom_id,"is_del"=>0]);
        if(!$ocustom_info){
            $json["status"] = 306;
            $json["info"] = "比赛不存在";
            $this->ajaxReturn($json);
        }
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $ocustom_info;
        $this->ajaxReturn($json);
    }
    /**
     * 官方赛事报名人数
     */
    public function numoapply(){
        $custom_id = I("custom_id");
        if(!$custom_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $ocustom = D("OfficialCustom");
        $ocustom_apply_count = $ocustom->getApplyCount(["gameid"=>$custom_id]);
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $ocustom_apply_count;
        $this->ajaxReturn($json);
    }
    /**
     * 官方赛事报名
     */
    public function oapply(){
//        $arr = [100170,100172,100173,100208,100175,100176,100174,100325];
//        if(!in_array($this->user_id, $arr)){
//            $json["status"] = 311;
//            $json["info"] = "暂未开放";
//            $this->ajaxReturn($json);
//        }
        $custom_id = I("custom_id");
        if(!$custom_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $user_info = $this->user_info;
        $ocustom = D("OfficialCustom");
        $user = D("User");
        $ocustom_info = $ocustom->getOne(["id"=>$custom_id,"is_del"=>0]);
        if(!$ocustom_info){
            $json["status"] = 306;
            $json["info"] = "比赛不存在";
            $this->ajaxReturn($json);
        }
        if($ocustom_info["status"]==2||$ocustom_info["status"]==3){
            $json["status"] = 307;
            $json["info"] = "比赛已结束";
            $this->ajaxReturn($json);
        }
        if($ocustom_info["status"]==1){
            $json["status"] = 308;
            $json["info"] = "比赛进行中";
            $this->ajaxReturn($json);
        }
        require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
        $weixin = new class_weixin_adv();
        $game_user = $user->getGameUserOne(["uid"=>$this->user_id]);
        if($ocustom_info["t_type"]==1){
            if($ocustom_info["tickets"]>$game_user["coinnum"]){
                $json["status"] = 309;
                $json["info"] = "开心豆不足";
                $this->ajaxReturn($json);
            }else{
                if($ocustom_info["tickets"]>0){
                    $extra = array(
                            'add' => array('type' => 'int','size' => 2,'value' => 2),
                            'coin' => array ('type' => 'int','size' => 4,'value' => $ocustom_info["tickets"]),
                            'type' => array('type' => 'int','size' => 2,'value' => 98),
                            'cointype' => array('type' => 'int','size' => 2,'value' => 1)
                    );
                    $custom_rsp = Sockets :: call('call_back', 10, 20, $this->user_id, $extra);
                    add_log("ocustom.log", "game", "报名扣除开心豆Socket返回数据". var_export($custom_rsp, true));
                }
                $apply_data["gameid"] = $custom_id;
                $apply_data["uid"] = $this->user_id;
                $apply_data["applytime"] = time();
                $apply_id = $ocustom->addCustomApply($apply_data);
                if($apply_id){
                    $dtime = "";
                    $fdate=formatDate($ocustom_info["start_date"],time());
                    if($fdate["d"]>0){
                        $dtime = $fdate["d"]."天";
                    }
                    if($fdate["h"]>0){
                        $dtime .= $fdate["h"]."小时";
                    }
                    if($fdate["i"]>0){
                        $dtime .= $fdate["i"]."分钟";
                    }
                    if($fdate["s"]>0){
                        $dtime .= $fdate["s"]."秒";
                    }
                    $msg_title = "您报名的".$ocustom_info["name"]."还有".$dtime."开始";
                    $msg_url = 'https://'.$_SERVER['HTTP_HOST'] . '/0-' . $ocustom_info["id"] . '-0-'.$ocustom_info["game_id"].'-4.html';
                    $custom_wx_msg = $user->wxCustomMsg($user_info["id"],$msg_title,$ocustom_info["name"],$ocustom_info["start_date"],$msg_url);
                    $custom_wx_msg_return = $weixin->send_user_message($custom_wx_msg);
                    add_log("ocustom.log", "home", "开赛提醒公众号消息推送状态：". var_export($custom_wx_msg_return, true));
                    $json["status"] = 200;
                    $json["info"] = "报名成功";
                    $json["data"] = $custom_id;
                    $this->ajaxReturn($json);
                }else{
                    $json["status"] = 310;
                    $json["info"] = "报名失败";
                    $this->ajaxReturn($json);
                }
            }
        }
        
        if($ocustom_info["t_type"]==2){
            if($ocustom_info["tickets"]>$game_user["cashpoint"]){
                $json["status"] = 309;
                $json["info"] = "钻石不足";
                $this->ajaxReturn($json);
            }else{
                if($ocustom_info["tickets"]>0){
                    $extra = array(
                            'add' => array('type' => 'int','size' => 2,'value' => 2),
                            'coin' => array ('type' => 'int','size' => 4,'value' => $ocustom_info["tickets"]),
                            'type' => array('type' => 'int','size' => 2,'value' => 99),
                            'cointype' => array('type' => 'int','size' => 2,'value' => 4)
                    );
                    $custom_rsp = Sockets :: call('call_back', 10, 20, $this->user_id, $extra);
                    add_log("ocustom.log", "game", "报名扣除钻石Socket返回数据". var_export($custom_rsp, true));
                }
                $apply_data["gameid"] = $custom_id;
                $apply_data["uid"] = $this->user_id;
                $apply_data["applytime"] = time();
                $apply_id = $ocustom->addCustomApply($apply_data);
                if($apply_id){
                    $dtime = "";
                    $fdate=formatDate($ocustom_info["start_date"],time());
                    if($fdate["d"]>0){
                        $dtime = $fdate["d"]."天";
                    }
                    if($fdate["h"]>0){
                        $dtime .= $fdate["h"]."小时";
                    }
                    if($fdate["i"]>0){
                        $dtime .= $fdate["i"]."分钟";
                    }
                    if($fdate["s"]>0){
                        $dtime .= $fdate["s"]."秒";
                    }
                    $msg_title = "您报名的".$ocustom_info["name"]."还有".$dtime."开始";
                    $msg_url = 'https://'.$_SERVER['HTTP_HOST'];
                    $custom_wx_msg = $user->wxCustomMsg($user_info["id"],$msg_title,$ocustom_info["name"],$ocustom_info["start_date"],$msg_url);
                    $custom_wx_msg_return = $weixin->send_user_message($custom_wx_msg);
                    add_log("ocustom.log", "home", "开赛提醒公众号消息推送状态：". var_export($custom_wx_msg_return, true));
                    $json["status"] = 200;
                    $json["info"] = "报名成功";
                    $json["data"] = $custom_id;
                    $this->ajaxReturn($json);
                }else{
                    $json["status"] = 310;
                    $json["info"] = "报名失败";
                    $this->ajaxReturn($json);
                }
            }
        }
    }
}
