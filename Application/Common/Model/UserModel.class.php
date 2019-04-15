<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Model;

use Think\Model;
use Common\WxApi\class_weixin_adv;
class UserModel extends Model{
    public function getLevelText($level){
        $level_arr = array(
            0=>"注册用户",
            1=>"贵宾用户",
            2=>"金尊用户",
            3=>"白金代理",
            4=>"钻石代理"
        );
        return $level_arr[$level];
    }

    /**
     * 根据用户id查找用户
     * @param type $user_id
     * @return boolean
     */
    public function getUserOne($user_id){
        if(!$user_id){
            return false;
        }
        return $this->where(["id"=>$user_id])->find();
    }
    /**
     * 根据用户other_id查找用户
     * @param type $other_id
     * @return boolean
     */
    public function getUserOneByOtherId($other_id){
        if(!$other_id){
            return false;
        }
        return $this->where(["other_id"=>$other_id])->find();
    }
    /**
     * 根据用户名查找用户
     * @param type $username
     * @return boolean
     */
    public function getUserOneByName($username){
        if(!$username){
            return false;
        }
        return $this->where(['username' => $username])->find();
    }
    /**
     * 根据邮箱查找用户
     * @param type $email
     * @return boolean
     */
    public function getUserOneByEmail($email){
        if(!$email){
            return false;
        }
        return $this->where(['email' => $email])->find();
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
        return $this->where($where)->find();
    }
    /**
     * 查找用户代理
     * @param type $user_id
     * @return boolean
     */
    public function getUserAgencyByUserId($user_id){
        if(!$user_id){
            return false;
        }
        $model = M("user_agency");
        return $model->where(['u_id'=>$user_id])->find();
    }
    /**
     * 查找用户代理
     * @param type $user_id
     * @return boolean
     */
    public function updateUserAgency($where,$data){
        if(!$where||!$data){
            return false;
        }
        $model = M("user_agency");
        return $model->where($where)->save($data);
    }
    /**
     * 查找用户代理
     * @param type $where
     * @return boolean
     */
    public function getUserAgencySubordinates($where){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("user_agency");
        return $model->where($where)->order("add_date DESC")->select();
    }
    
    /**
     * 查找用户代理
     * @param type $where
     * @return boolean
     */
    public function getUserAgencyCount($where){
        if(!$where||empty($where)){
            return false;
        }
        $model = M("user_agency");
        return $model->where($where)->count();
    }
    
    /**
     * 查找用户结算账户
     * @param type $user_id
     * @return boolean
     */
    public function getUserBank($user_id){
        if(!$user_id){
            return false;
        }
        $model = M("user_bank");
        return $model->where(['u_id'=>$user_id])->find();
    }
    /**
     * 添加银行信息
     * @param type $data
     */
    public function addUserBank($data){
        if(empty($data)||!isset($data["u_id"])){
            return false;
        }
        $model = M("user_bank");
        $model->where(["u_id"=>$data["u_id"]])->delete();
        return $model->add($data);
    }

    /**
     * 获取当前用户下相同代理等级的总数
     * @param type $user_id
     */
    public function getAgencySum($user_id){
        if(!$user_id){
            return 0;
        }
        $model = M("user_agency");
        $sql = "SELECT count(*) AS sum FROM __TABLE__ WHERE parent_id = {$user_id} AND grade=(SELECT grade FROM __TABLE__ WHERE u_id = {$user_id})";
        $result = $model->query($sql);
        if($result){
            return $result[0]["sum"];
        }
        return 0;
    }
    public function getAgencyOrderBynumber($order_number){
        if(!$order_number){
            return false;
        }
        $model = M("agency_order");
        return $model->where(['order_number'=>$order_number,'status'=>200])->find();
    }
    public function getAgencyOrder($user_id,$grade){
        if(!$user_id||!$grade){
            return false;
        }
        $model = M("agency_order");
        return $model->where(['u_id'=>$user_id,'grade'=>$grade,'status'=>200])->find();
    }

    /**
     * 更新用户代理级别
     * @param type $user_id
     */
    public function updUserAgency($user_id){
        if(!$user_id){
            return false;
        }
        $user_agency_info = $this->getUserAgencyByUserId($user_id);
        if(!$user_agency_info){
            return;
        }
        if($user_agency_info["grade"]==4){
            return 4;
        }
        $sum = $this->getAgencySum($user_id);
        if($user_agency_info["grade"]<3&&$this->getAgencyOrder($user_id,3)){
            $return_status=$this->updAgency($user_id, 3);
            if($return_status){
                return 2;
            } else {
                return 3;
            }
        }elseif(($sum>=5&&$user_agency_info["grade"]<2)||($user_agency_info["grade"]==3&&$sum>=2&&$this->getAgencyOrder($user_id,4))){
            $return_status=$this->updAgency($user_id, ($user_agency_info["grade"]+1));
            if($return_status){
                return 2;
            } else {
                return 3;
            }
        }else{
            return 1;
        }        
    }
    public function updAgency($user_id,$grade){
        if(!$user_id||!$grade){
            return false;
        }
        $m_user_agency = M("user_agency");
        $a_info = $m_user_agency->where(["u_id"=>$user_id])->field("grade")->find();   //当前代理等级
        $result = $m_user_agency->where(["u_id"=>$user_id])->save(['grade'=>$grade]);   //更新当前用户代理级别
        if($result){    
            $u_info = $m_user_agency->where(["u_id"=>$user_id])->field("parent_id,superior_id,grade")->find();   //查询当前用户级别及父代理
            if(!empty($u_info["parent_id"])){
                $m_user_log = M("user_log");
                $user_log["u_id"] = $user_id;
                $user_log["intro"] = "用户代理级别从".($a_info["grade"])."升级为".$u_info["grade"];
                $user_log["add_date"] = time();
                $m_user_log->add($user_log);  //存入用户操作日志

//                $ua_data["superior_id"] = $user_id;
//                $ua_where["parent_id"] = $user_id;
//                $ua_where["superior_id"] = array('NEQ',$user_id);
//                $ua_where["grade"] = array('LT',$u_info["grade"]);
//                $m_user_agency->where($ua_where)->save($ua_data);  //更新当前用户下的用户的上级
//
//                $p_info = $m_user_agency->where(["u_id"=>$u_info["parent_id"]])->field("parent_id,superior_id,grade")->find();  //查询父代理级别及上级
//                if($u_info["grade"]==$p_info["grade"]){  //如果当前用户代理级别和父代理级别相同则把当前用户的上级改为父代理的上级
//                    $m_user_agency->where(["u_id"=>$user_id])->save(["superior_id"=>$p_info["superior_id"]]);
//                }elseif($u_info["grade"]>$p_info["grade"]){
//                    $superior_id = $this->fun($u_info, $p_info);
//                    $m_user_agency->where(["u_id"=>$user_id])->save(["superior_id"=>$superior_id]);
//                }
            }
            return true;
        }
        return false;
    }
    
    private function fun($u_info,$p_info){
        if ($u_info["grade"]>$p_info["grade"]) {
            $m_user_agency = M("user_agency");
            $y_info = $m_user_agency->where(["u_id"=>$p_info["parent_id"]])->field("u_id,parent_id,superior_id,grade")->find();  //查询父代理级别及上级
            return $this->fun($u_info,$y_info);  //递归查询上级
        }elseif($u_info["grade"]==$p_info["grade"]){
            return $p_info["superior_id"];
        }else{
            return $p_info["u_id"];
        }
    }

    /**
     * 添加用户
     * @param type $data
     * @return boolean
     */
    public function addUser($data){
        if(empty($data)){
            return false;
        }
        $data["regtime"] = time();
        $data["nickname"] = $data["nickname"]?$data["nickname"]:get_rand_str(6);
        $letters = [
            '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $headurl = CDN_HOST."/images/wxhead/".get_rand_str(2,$letters).".jpg";
        $data["headurl"] = $data["headurl"]?$data["headurl"]:$headurl;
        $recommend = $data["recommend"];
        unset($data["recommend"]);
        $user_id = $this->add($data);
        unset($data["channel"]);
        unset($data["is_msg"]);
        $game_data = $data;
        if($user_id){
            
            //38活动送豆，限制女性和人妖
            $t_date = date("Ymd");
            if($t_date=="20180308"&&$data["gender"]!=1){
                $this->addActivity38($user_id);
            }
            
            if(!empty($data["type"])){
                unset($data["type"]);
            }
            $game_data["plat_uid"] = $user_id;
            $game_data["uid"] = $user_id;
            $this->addGameUser($game_data);
            if(!$recommend){
                $recommend = "10001";
            }
            $agency["u_id"] = $user_id;
            $agency["parent_id"] = $recommend;
            $agency["superior_id"] = $recommend;
            $agency["grade"] = 0;
            $agency["add_date"] = time();
            $m_user_agency = M("user_agency");
            $m_user_agency->add($agency);
            return $user_id;
        }
        return false;
    }
    /**
     * 游客登陆
     * @param type $data
     * @return boolean
     */
    public function addVisitor($data){
        if(empty($data)){
            return false;
        }
        $data["regtime"] = time();
        $recommend = $data["recommend"];
        unset($data["recommend"]);
        $user_id = $this->add($data);
        if($user_id){
            if(!empty($data["type"])){
                unset($data["type"]);
            }
            $nickname = "游客".$user_id;
            $u_data["nickname"] = $nickname;
            $this->where(["id"=>$user_id])->save($u_data);
            $data["plat_uid"] = $user_id;
            $data["nickname"] = $nickname;
            $this->addGameUser($data);
            
            if(!$recommend){
                $recommend = "10001";
            }
            $agency["u_id"] = $user_id;
            $agency["parent_id"] = $recommend;
            $agency["superior_id"] = $recommend;
            $agency["grade"] = 0;
            $agency["add_date"] = time();
            $m_user_agency = M("user_agency");
            $m_user_agency->add($agency);
            return $user_id;
        }
        return false;
    }
    /**
     * 添加用户登陆日志
     * @param type $data
     */
    public function addUserLoginLog($data){
        if(empty($data)){
            return false;
        }
        $date = date("Ymd");
        $table = "user_login_log_". $date;
        if(!$this->createUserLogin($table)){
            return false;
        }
        $m_user_login_log = M($table);
        $m_login_log = M("user_login_log");
        $id=$m_login_log->add($data);
        if($id){
            $data["id"] = $id;
            return $m_user_login_log->add($data);
        }
        return false;
    }
    /**
     * 创建用户登录日志表
     * @param type $table
     * @return boolean
     */
    public function createUserLogin($table){
        if(!$table){
            return false;
        }
        $model = M();
        $s_table = "show tables like 'dz_". $table."'";  //查询表是否存在
        $result = $model->query($s_table);
        if(empty($result)){
            $c_table = "CREATE TABLE `dz_".$table."` (
                `id` int(11) NOT NULL,
                `u_id` int(11) NOT NULL COMMENT '用户id',
                `intro` varchar(50) DEFAULT '' COMMENT '描述',
                `add_date` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
                `reg_date` int(11) NOT NULL DEFAULT '0' COMMENT '注册时间',
                PRIMARY KEY (`id`)
              ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
            $model->execute($c_table);
            return true;
        }
        return true;
    }

    /**
     * 更新用户
     * @param type $data
     * @param type $where
     * @return boolean
     */
    public function updUser($data,$where){
        if(empty($data)||empty($where)){
            return false;
        }
        $user_data["lastip"] = $data["lastip"];
        $user_data["lasttime"] = $data["lasttime"];
        $user_data["authkey"] = $data["authkey"];
        if($data["gender"]){
            $user_data["gender"] = $data["gender"];
        }
        if($data["nickname"]){
            $user_data["nickname"] = $data["nickname"];
        }else{
            $user_data["nickname"] = get_rand_str(6);
        }
        if($data["headurl"]){
            $user_data["headurl"] = $data["headurl"];
        }else{
            $letters = [
            '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $user_data["headurl"] = CDN_HOST."/images/wxhead/".get_rand_str(2,$letters).".jpg";
        }
        if($data["province"]){
            $user_data["province"] = $data["province"];
        }
        if($data["city"]){
            $user_data["city"] = $data["city"];
        }
        
        //38活动送豆，限制女性和人妖
        $t_date = date("Ymd");
        if($t_date=="20180308"&&$data["gender"]!=1){
            $this->addActivity38($data["plat_uid"]);
        }
            
        $upd_status = $this->where($where)->save($user_data);
        if($upd_status){
            $game_user_info = $this->getGameUserOne($where);
            if($game_user_info){
                $game_data = $user_data;
                $upd_status = $this->updGameUser($game_data, $where);
            }else{
                unset($data["recommend"]);
                unset($data["lastip"]);
                unset($data["lasttime"]);
                unset($data["is_msg"]);
                $data["uid"] = $data["plat_uid"];
                $upd_status = $this->addGameUser($data);
            }
        }
        return $upd_status;
    }
    public function updIsMsg($user_id){
        if(!$user_id){
            return false;
        }
        $model = M("user");
        $result = $model->query("UPDATE __TABLE__ SET is_msg=1 WHERE id=".$user_id);
        return $result;
    }

    /**
     * 修改密码
     * @param type $password
     * @param type $where
     * @return boolean
     */
    public function updPassWord($password,$where){
        if(!$password||empty($where)){
            return false;
        }
        $data["password"] = $password;
        $upd_status = $this->where($where)->save($data);
        if($upd_status){
            $upd_status = $this->updGameUser($data, $where);
        }
        return $upd_status;
    }

    public function getGameUserOne($where){
        if(!$where||empty($where)){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("user",$db_config["DB_PREFIX"],$db_config);
        return $model->where($where)->find();
    }
    
    public function getGameLogEconomy($where){
        if(!$where){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $log_economy = M("log_economy",$db_config["DB_PREFIX"],$db_config);
        return $log_economy->where([$where])->find();
    }
    
    public function addGameLogEconomy($user_id){
        if(!$user_id){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("user",$db_config["DB_PREFIX"],$db_config);
        $log_economy = M("log_economy",$db_config["DB_PREFIX"],$db_config);
        $user_info = $model->where(["uid"=>$user_id])->find();
        if($user_info){
            $log_economy_data["uid"] = $user_id;
            $log_economy_data["oper"] = 1;
            $log_economy_data["changevalue"] = 20000;
            $log_economy_data["coin_type"] = 1;
            $log_economy_data["logtype"] = 92;
            $log_economy_data["logtime"] = time();
            $log_economy->add($log_economy_data);
            $data["coinnum"] = $user_info["coinnum"]+20000;
            $model->where(["uid"=>$user_id])->save($data);
        }
        return false;
    }
    /**
     * 添加邮件
     * @param type $data
     * @return boolean
     */
    public function addGameMail($data){
        if(empty($data)){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $maill_model = M("user_mail",$db_config["DB_PREFIX"],$db_config);
        $data["sendtime"] = time();
        return $maill_model->add($data);
    }

    /**
     * 往游戏数据库添加用户数据
     * @param type $data
     * @return boolean
     */
    public function addGameUser($data){
        if(empty($data)){
            return false;
        }
        //$data["coinnum"] = 10000000;  //游戏币，临时测试
        $db_config = C("DB_CONFIG2");
//        $mail_data["uid"] = $data["uid"];
//        $mail_data["title"] = "欢迎来到《欢乐逗棋牌》";
//        $mail_data["sender"] = "系统";
//        $mail_data["sendtime"] = time();
//        $mail_data["describe"] = "欢迎使用《欢乐逗棋牌》游戏大厅，初次登陆系统赠送给您20000开心豆，祝您游戏愉快！";
//        $mail_data["coin"] = 20000;
//        $mail_data["awardnum"] = 0;
        $model = M("user",$db_config["DB_PREFIX"],$db_config);
        //$maill_model = M("user_mail",$db_config["DB_PREFIX"],$db_config);
        $result_model = M("user_result",$db_config["DB_PREFIX"],$db_config);
        //$log_economy = M("log_economy",$db_config["DB_PREFIX"],$db_config);
        //$maill_model->add($mail_data);
        
//        $log_economy_data["uid"] = $data["uid"];
//        $log_economy_data["oper"] = 1;
//        $log_economy_data["changevalue"] = 20000;
//        $log_economy_data["coin_type"] = 1;
//        $log_economy_data["logtype"] = 91;
//        $log_economy_data["logtime"] = time();
//        $log_economy->add($log_economy_data);
        
        $result_data["uid"] = $data["uid"];
        $result_data["gametype"] = 1;
        $result_model->add($result_data);
        $result_data["gametype"] = 2;
        $result_model->add($result_data);
        $result_data["gametype"] = 3;
        $result_model->add($result_data);
        $result_data["gametype"] = 4;
        $result_model->add($result_data);
        $result_data["gametype"] = 5;
        $result_model->add($result_data);
        $result_data["gametype"] = 6;
        $result_model->add($result_data);
        
//        $data["coinnum"] = 20000;
        return $model->add($data);
    }
    /**
     * 修改游戏用户数据
     * @param type $data
     * @param type $where
     * @return boolean
     */
    public function updGameUser($data,$where){
        if(empty($data)||empty($where)){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("user",$db_config["DB_PREFIX"],$db_config);
        return $model->where($where)->save($data);
    }
    /**
     * 添加兑换券记录
     * @param type $data
     * @return boolean
     */
    public function addLogTicket($data){
        if(empty($data)){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("log_ticket",$db_config["DB_PREFIX"],$db_config);
        return $model->add($data);
    }
    /**
     * 获取游戏兑换商城收货地址
     * @param type $user_id
     */
    public function getGameUserAddr($user_id){
        if(!$user_id){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("user_address",$db_config["DB_PREFIX"],$db_config);
        return $model->where(["uid"=>$user_id])->find();
    }
    /**
     * 获取公告
     * @param type $id
     */
    public function getGameUserNews($id){
        if(!$id){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("user_news",$db_config["DB_PREFIX"],$db_config);
        return $model->where(["id"=>$id])->find();
    }
    /**
     * 获取列表
     * @param type $where
     */
    public function getGameUserNewsList($where){
        if(!$where){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("user_news",$db_config["DB_PREFIX"],$db_config);
        return $model->where($where)->select();
    }
    /**
     * 返回能拿到充值提出的上级用户
     * @param type $table
     * @param type $u_id
     * @param type $amounts
     * @param type $type  //类型1为台费，2为充值
     * @return string
     */
    public function awardArr($u_id,$amounts=0){
        $userID = $u_id;
        $total = $amounts;
        $ratio=[
            "1"=>3,
            "2"=>6,
            "3"=>9,
            "4"=>9,
            "5"=>9,
            "6"=>9,
        ];
        $grade = 0;  //代理等级
        $return_data = [];
        $into = 0;  //下级分成比例
        for($a=0;$a<6;$a++){ //六级代理
            $user_info = $this->getUserAgencyByUserId($userID);
            if(!$user_info||!$user_info["superior_id"]){
                break;
            }
            $grade = $user_info["grade"];
            $user_s = $this->getUserSuperiorInfo($user_info["superior_id"],$grade);
            if(!$user_s){
                break;
            }
            $gd = $user_s["grade"]; //当前代理等级
            if($gd>$grade && ($ratio[$gd]-$into)>0){
                $rd["user_id"] = $user_s["u_id"];
                if($total>0){
                    $rd["amount"] = round(floatval($total*($ratio[$gd]-$into)/100), 2);
                }
                $return_data[] = $rd;
            }
            $into = $ratio[$gd];
            
            if($gd==6||!$user_s["superior_id"]){
                break;
            }else{
                $userID = $user_s["u_id"];
            }
        }
        return $return_data;
    }
    /**
     * 查找父类信息
     * @param type $userID
     * @param type $grade
     * @return type
     */
    private function getUserSuperiorInfo($userID,$grade){
        $user_s = $this->getUserAgencyByUserId($userID);
        if($user_s["grade"]<$grade){
            return $this->getUserSuperiorInfo($user_s["superior_id"],$grade);
        }else{
            return $user_s;
        }
    }
    
    /**
     * 公众号推送信息
     * @param type $uid
     * @param type $plan_des_info
     */
    public function wxMessagewxYwlcMsg($msg_uid,$title,$keyword1,$keyword2,$keyword3,$keyword4,$remark='',$url=''){
        if(!$msg_uid||!$title||!$keyword1||!$keyword2||!$keyword3||!$keyword4){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $customer_m = M("cunstomer_wx_binding",$db_config["DB_PREFIX"],$db_config);
        $cunstomer_wx_binding_info = $customer_m->where(["user_id"=>$msg_uid,"state"=>1])->find();
        if($cunstomer_wx_binding_info&&!empty($cunstomer_wx_binding_info)){
            require_once APP_ROOT ."Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            $msg_data["touser"] = $cunstomer_wx_binding_info["open_id"];
            $msg_data["template_id"] = "qq5apA1Ku6rbm0IWkD_QMHRjAaSOuCu9Fv62SjPpmrE";
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
            add_log("wxMessage.log", "wxmessage", "计划失败公众号消息推送状态：". var_export($return_status, true));
            $return_status = json_decode($return_status, true);
            if($return_status["errcode"]===0){
                return true;
            }
        }
        return false;
    }
}
