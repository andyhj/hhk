<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Model;

use Think\Model;
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
     * 注册推送微信消息
     * @param type $msg_uid  推送的用户
     * @param type $user_id  注册用户
     * @param type $nickname 注册用户昵称
     */
    public function wxRegMessage($msg_uid,$user_id,$nickname){
        if(!$msg_uid||!$user_id){
            return false;
        }
        $dl_user_info = $this->getUserOne($msg_uid);
        if(!$dl_user_info||!$dl_user_info["other_id"]){
            return false;
        }
        $msg_data["touser"] = $dl_user_info["other_id"];
        $msg_data["template_id"] = "76dBsTBTiwNCx5Q_uJ6bQN3TMq7KM4iYlyxUqyjw0Ho";
        $msg_data["url"] = HTTP_HOST.'/index/user/info.html';
        $msg_data["data"] = array(
            "first"=>array(
                "value"=>"您好，有新的用户成为你的下级",
                "color"=>""
            ),
            "keyword1"=>array(
                "value"=> $user_id,
                "color"=>""
            ),
            "keyword2"=>array(
                "value"=> $nickname,
                "color"=>""
            ),
            "keyword3"=>array(
                "value"=> "******",
                "color"=>""
            ),
            "remark"=>array(
                "value"=>"成功推荐用户，活动赠送2000开心豆已到账。",
                "color"=>""
            )
        );
        return $msg_data;
    }
    
    /**
     * 注册推送旁系下属微信消息
     * @param type $msg_uid  推送的用户
     * @param type $user_id  注册用户
     * @param type $nickname 注册用户昵称
     */
    public function wxRegPxMessage($msg_uid,$user_id,$nickname,$recommend){
        if(!$msg_uid||!$user_id){
            return false;
        }
        $dl_user_info = $this->getUserOne($msg_uid);
        if(!$dl_user_info||!$dl_user_info["other_id"]){
            return false;
        }
        $tj_user_info = $this->getUserOne($recommend);
        $msg_data["touser"] = $dl_user_info["other_id"];
        $msg_data["template_id"] = "76dBsTBTiwNCx5Q_uJ6bQN3TMq7KM4iYlyxUqyjw0Ho";
        $msg_data["url"] = HTTP_HOST.'/index/user/info.html';
        $msg_data["data"] = array(
            "first"=>array(
                "value"=>"您好，有新的用户成为你的旁系下级",
                "color"=>""
            ),
            "keyword1"=>array(
                "value"=> $user_id,
                "color"=>""
            ),
            "keyword2"=>array(
                "value"=> $nickname,
                "color"=>""
            ),
            "keyword3"=>array(
                "value"=> "******",
                "color"=>""
            ),
            "remark"=>array(
                "value"=>$tj_user_info["nickname"]." 成功推荐用户。",
                "color"=>""
            )
        );
        return $msg_data;
    }
    /**
     * 兑换失败
     * @param type $msg_uid
     * @param type $title
     * @param type $cause
     * @return boolean|array
     */
    public function wxExchangeFallMsg($msg_uid,$title,$cause,$des="请联系客服",$url=""){
        if(!$msg_uid||!$title||!$cause){
            return false;
        }
        $dl_user_info = $this->getUserOne($msg_uid);
        if(!$dl_user_info||!$dl_user_info["other_id"]){
            return false;
        }
        $r_url = HTTP_HOST.'/index/user/order.html';
        if($url){
            $r_url = $url;
        }
        $msg_data["touser"] = $dl_user_info["other_id"];
        $msg_data["template_id"] = "iQuU72sRz6bJkKH19R8kNO99zeyKWxCOAjVkP3X58VQ";
        $msg_data["url"] = $r_url;
        $msg_data["data"] = array(
            "first"=>array(
                "value"=>$title,
                "color"=>""
            ),
            "keyword1"=>array(
                "value"=> $cause,
                "color"=>""
            ),
            "keyword2"=>array(
                "value"=> date("Y-m-d H:i:s"),
                "color"=>""
            ),
            "remark"=>array(
                "value"=>$des,
                "color"=>""
            )
        );
        return $msg_data;
    }
    /**
     * 兑换成功
     * @param type $msg_uid
     * @param type $title
     * @param type $cause
     * @return boolean|array
     */
    public function wxExchangeSucceedMsg($msg_uid,$title,$cause,$number){
        if(!$msg_uid||!$title||!$cause||!$number){
            return false;
        }
        $dl_user_info = $this->getUserOne($msg_uid);
        if(!$dl_user_info||!$dl_user_info["other_id"]){
            return false;
        }
        $msg_data["touser"] = $dl_user_info["other_id"];
        $msg_data["template_id"] = "Be9TWhyaWCJfMHaJSfg05hBQpO8DLn-Z1e89jEp20ec";
        $msg_data["url"] = HTTP_HOST.'/index/user/order.html';
        $msg_data["data"] = array(
            "first"=>array(
                "value"=>$title,
                "color"=>""
            ),
            "keyword1"=>array(
                "value"=> $cause,
                "color"=>""
            ),
            "keyword2"=>array(
                "value"=> date("Y-m-d H:i:s"),
                "color"=>""
            ),
            "keyword3"=>array(
                "value"=> $number,
                "color"=>""
            ),
            "remark"=>array(
                "value"=>"点击查看详情",
                "color"=>""
            )
        );
        return $msg_data;
    }
    /**
     * 新用户关注推送消息
     * @param type $user_id
     */
    public function subscribeMsg($user_id){
        if(!$user_id){
            return false;
        }
        $dl_user_info = $this->getUserOne($user_id);
        if(!$dl_user_info||!$dl_user_info["other_id"]){
            return false;
        }
        $msg_data["touser"] = $dl_user_info["other_id"];
        $msg_data["template_id"] = "Z-9C8tLdC9HcgRRj9P1X-QUbMPXBnGk22y-63bSfFr0";
        $msg_data["url"] = HTTP_HOST;
        $msg_data["data"] = array(
            "first"=>array(
                "value"=>"您已成功登录开心逗棋牌",
                "color"=>""
            ),
            "keyword1"=>array(
                "value"=> date("Y-m-d H:i:s"),
                "color"=>""
            ),
            "keyword2"=>array(
                "value"=> getIP(),
                "color"=>""
            ),
            "remark"=>array(
                "value"=>"您已获得系统赠送的新手礼包20000开心豆，祝您游戏愉快！\n回复“兑换礼品”了解如何兑换话费、手机等礼品；",
                "color"=>""
            )
        );
        return $msg_data;
    }
    /**
     * 用户等级发生变化推送信息
     * @param type $user_id
     */
    public function agencyMsg($user_id,$state){
        if(!$user_id){
            return false;
        }
        $dl_user_info = $this->getUserOne($user_id);
        if(!$dl_user_info||!$dl_user_info["other_id"]){
            return false;
        }
        $msg_data["touser"] = $dl_user_info["other_id"];
        $msg_data["template_id"] = "fdjerfyJHRbOjPznHnvSAHBHZGE2B6Z_u1vVTTbzAPk";
        $msg_data["url"] = HTTP_HOST.'/index/user/instruction.html';
        $msg_data["data"] = array(
            "first"=>array(
                "value"=>"亲爱的用户，\n非常高兴的通知你",
                "color"=>""
            ),
            "reason"=>array(
                "value"=> "您已成功发展5个相同等级下线",
                "color"=>""
            ),
            "state"=>array(
                "value"=> $state,
                "color"=>""
            ),
            "remark"=>array(
                "value"=>"如果您再发展5个相同等级用户，您的等级就会往上提升，点击查看等级详情介绍",
                "color"=>""
            )
        );
        return $msg_data;
    }
    /**
     * 用户成为代理推送消息
     * @param type $msg_uid
     * @param type $title
     * @param type $cause
     * @return boolean|array
     */
    public function wxDlSucceedMsg($msg_uid,$title,$remark,$url){
        if(!$msg_uid||!$title||!$remark){
            return false;
        }
        $dl_user_info = $this->getUserOne($msg_uid);
        if(!$dl_user_info||!$dl_user_info["other_id"]){
            return false;
        }
        $msg_data["touser"] = $dl_user_info["other_id"];
        $msg_data["template_id"] = "75h958qd_8mhHqxgOTsK0SIX3DWkPnkKvnX_yAye8MA";
        $msg_data["url"] = $url;
        $msg_data["data"] = array(
            "first"=>array(
                "value"=>$title,
                "color"=>""
            ),
            "date"=>array(
                "value"=> date("Y-m-d H:i:s"),
                "color"=>""
            ),
            "expiry"=>array(
                "value"=> "----",
                "color"=>""
            ),
            "remark"=>array(
                "value"=>$remark,
                "color"=>""
            )
        );
        return $msg_data;
    }
    
    /**
     * 发货通知
     * @param type $msg_uid
     * @param type $title
     * @param type $cause
     * @return boolean|array
     */
    public function wxFhMsg($msg_uid,$title,$keyword1,$keyword2,$url){
        if(!$msg_uid||!$title||!$keyword1||!$keyword2){
            return false;
        }
        $dl_user_info = $this->getUserOne($msg_uid);
        if(!$dl_user_info||!$dl_user_info["other_id"]){
            return false;
        }
        $msg_data["touser"] = $dl_user_info["other_id"];
        $msg_data["template_id"] = "PO-7jNlZ0EEtYezeBYXwIHIvvIJq67kbwbgYofcpIyQ";
        $msg_data["url"] = $url;
        $msg_data["data"] = array(
            "first"=>array(
                "value"=>$title,
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
            "remark"=>array(
                "value"=>"点击查看详情",
                "color"=>""
            )
        );
        return $msg_data;
    }
    
    /**
     * 比赛开赛提醒
     * @param type $msg_uid
     * @param type $title
     * @param type $cause
     * @return boolean|array
     */
    public function wxCustomMsg($msg_uid,$title,$custom_name,$custom_date,$url,$game_name="开心斗地主",$remark="点击进入比赛"){
        if(!$msg_uid||!$title||!$custom_name){
            return false;
        }
        $dl_user_info = $this->getUserOne($msg_uid);
        if(!$dl_user_info||!$dl_user_info["other_id"]){
            return false;
        }
        $msg_data["touser"] = $dl_user_info["other_id"];
        $msg_data["template_id"] = "aKVigfLGpniMofqvIbkkP65BU7JoHleHmoWDQwr89sw";
        $msg_data["url"] = $url;
        $msg_data["data"] = array(
            "first"=>array(
                "value"=>$title,
                "color"=>""
            ),
            "keyword1"=>array(
                "value"=> $game_name,
                "color"=>""
            ),
            "keyword2"=>array(
                "value"=> $custom_name,
                "color"=>""
            ),
            "keyword3"=>array(
                "value"=> date("Y-m-d H:i:s",$custom_date),
                "color"=>""
            ),
            "remark"=>array(
                "value"=>$remark,
                "color"=>""
            )
        );
        return $msg_data;
    }
    
    /**
     * 退款通知
     * @param type $msg_uid
     * @param type $title
     * @param type $cause
     *  内容示例: 
     *  退款成功
        订单号：648-234323423
        退款金额：327
        体验完善的余额服务，请使用全民手游
     * @return boolean|array
     */
    public function wxTkMsg($msg_uid,$title,$keyword1,$keyword2,$url){
        if(!$msg_uid||!$title||!$keyword1||!$keyword2){
            return false;
        }
        $dl_user_info = $this->getUserOne($msg_uid);
        if(!$dl_user_info||!$dl_user_info["other_id"]){
            return false;
        }
        $msg_data["touser"] = $dl_user_info["other_id"];
        $msg_data["template_id"] = "XEs5AvWT90jTkybiThRYKg4taOONk9zIPkQZGv3h39g";
        $msg_data["url"] = $url;
        $msg_data["data"] = array(
            "first"=>array(
                "value"=>$title,
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
            "remark"=>array(
                "value"=>"点击查看详情",
                "color"=>""
            )
        );
        return $msg_data;
    }
    
    /**
     * 比赛结果告知
     * @param type $msg_uid
     * @param type $title
     * @param type $cause
     *  内容示例: 
     *  恭喜亲，您参与的好友赛已结束！
        是否有效：有效
        结束时间：09月15日23:00
        点击查看结果
     * @return boolean|array
     */
    public function wxBsJgMsg($msg_uid,$title,$result,$time,$url){
        if(!$msg_uid||!$title||!$result||!$time){
            return false;
        }
        $dl_user_info = $this->getUserOne($msg_uid);
        if(!$dl_user_info||!$dl_user_info["other_id"]){
            return false;
        }
        $msg_data["touser"] = $dl_user_info["other_id"];
        $msg_data["template_id"] = "75Njv-2iOGQk_YFSz_W1H0sgjmCvuiCqH-DsZnsm_OM";
        $msg_data["url"] = $url;
        $msg_data["data"] = array(
            "first"=>array(
                "value"=>$title,
                "color"=>""
            ),
            "keyword1"=>array(
                "value"=> $result,
                "color"=>""
            ),
            "keyword2"=>array(
                "value"=> $time,
                "color"=>""
            ),
            "remark"=>array(
                "value"=>"点击查看结果",
                "color"=>""
            )
        );
        return $msg_data;
    }
    /**
     * 38妇女节送豆活动
     * @param type $user_id
     */
    public function addActivity38($user_id){
        if(!$user_id){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $maill_model = M("user_mail",$db_config["DB_PREFIX"],$db_config);
        $where["uid"] = $user_id;
        $where["title"] = "38约惠女人节活动";
        $mail_info = $maill_model->where($where)->find();
        if(!$mail_info&&empty($mail_info)){
            $data["uid"] = $user_id;
            $data["title"] = "38约惠女人节活动";
            $data["sender"] = "系统";
            $data["describe"] = "3.8日女性玩家登录游戏即送3800开心豆！";
            $data["coin"] = 3800;
            return $this->addGameMail($data);
        }
        return false;
    }
    /**
     * 我的推广列表
     */
    public function agencyList($user_id){
        if(!$user_id){
            return false;
        }
        $user_agency = $this->getUserAgencyByUserId($user_id);
        $user_up = [];  //直系上级
        $user_down = []; //直系下级
        if($user_agency["parent_id"]&&$user_agency["parent_id"]!=10001){
            $user_info = $this->getUserOne($user_agency["parent_id"]); //发展我的用户(直系上级)
            $user_grade = $this->getUserAgencyByUserId($user_info["id"]); //查找用户代理等级
            $user_up["id"] = $user_info["id"];
            $user_up["headurl"] = $user_info["headurl"]; //用户头像
            $user_up["nickname"] = $user_info["nickname"]; //用户昵称
            $user_up["regtime"] = $user_info["regtime"];  //初次注册时间
            $user_up["grade"] = $user_grade["grade"];   //用户代理等级
        }
        
        $user_agency_list = $this->getUserAgencySubordinates(['parent_id'=>$user_id]); //直系下级
        if($user_agency_list&&!empty($user_agency_list)){
            foreach ($user_agency_list as $value) {
                $user_info = $this->getUserOne($value["u_id"]); //用户信息
                $down["id"] = $user_info["id"];
                $down["headurl"] = $user_info["headurl"]; //用户头像
                $down["nickname"] = $user_info["nickname"]; //用户昵称
                $down["regtime"] = $user_info["regtime"];  //初次注册时间
                $down["grade"] = $value["grade"];   //用户代理等级
                $user_down[] = $down;
            }
        }
        return array("user_up"=>$user_up,"user_down"=>$user_down);
    }
    
    /**
     * 查询房间信息
     * @param type $room_id
     * @return type
     */
    public function getRoom($room_id){
        if(!$room_id){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $m = M("mm_room",$db_config["DB_PREFIX"],$db_config);
        $sql = "SELECT *  FROM mm_room WHERE room_id = {$room_id} ";
        $result = $m->query($sql);
        if($result){
            return $result[0];
        }
        return false;
    }
}
