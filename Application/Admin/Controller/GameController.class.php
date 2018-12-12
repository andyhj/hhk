<?php

namespace Admin\Controller;
use Common\Common\Sockets;
use Common\Common\Redis;
class GameController extends CommonController{
    /**
     * 房间列表
     */
    public function index(){
        $current_page = (int)I('p',1);
        $per_page = 15;//每页显示条数
        $db_config = C("DB_CONFIG2");
        $room_m = M("room","mm_",$db_config);
        $count = $room_m->count();
        $page = getpage($count, $per_page);
        $room_list = $room_m->page($current_page.','.$per_page)->select();
        if(is_post()){
            
        }
        $updurl = U("game/roomnotice");
        $this->assign("updurl",$updurl);
        $this->assign("page",$page->show());
        $this->assign("room_list",$room_list);
        $this->display();
    }
    /**
     * 修改房间概率通知游戏服
     */
    public function roomnotice(){
        $room_id = I("room_id",0);
        $extra = array(
                'roomid' => array ('type' => 'int','size' => 4,'value' => $room_id)
        );
        $response = Sockets :: call('call_back', 10, 500, $room_id, $extra);
        add_log("game.log", "admin", "Socket返回数据". var_export($response, true));
        if(isset($response["retcode"])&&$response["retcode"]==0){
            $json["status"] = 200;
            $json["info"] = "成功";
            $this->ajaxReturn($json);
        }else{
            $json["status"] = 306;
            $json["info"] = "失败";
            $this->ajaxReturn($json);
        }
    }
    
    function roominfo(){
        $room_id = I("room_id",0);
        $return_url = U('game/index');
        $rurl = I('rurl');
        if($rurl){
            $return_url = U($rurl);
        }
        $db_config = C("DB_CONFIG2");
        $room_m = M("room","mm_",$db_config);
        $room_info = $room_m->where(["room_id"=>$room_id])->find();
        
        $m_redis = new Redis();
        $StoreSmall = ""; //小奖池金额
	$StoreLarge = "";       //大奖池金额
	$StoreSmallLuck = "";            //小奖池幸运值
	$StoreLargeLuck = "";      //大奖池幸运值
	$DrillProbit = "";     //钻头概率
        if($room_id>=900&&$room_id<1100){
            $nine_fruit_room_info = $m_redis->hgetall("nine_fruit_room_info_".$room_id."_1");
            if($nine_fruit_room_info&&!empty($nine_fruit_room_info)){
                $StoreSmall = $nine_fruit_room_info["StoreSmall"]; //小奖池金额
                $StoreLarge = $nine_fruit_room_info["StoreLarge"];       //大奖池金额
                $StoreSmallLuck = $nine_fruit_room_info["StoreSmallLuck"];            //小奖池幸运值
                $StoreLargeLuck = $nine_fruit_room_info["StoreLargeLuck"];      //大奖池幸运值
            }
        }
        if($room_id>=1100&&$room_id<1122){
            $threasure_room_info = $m_redis->hgetall("threasure_room_info_".$room_id."_1");
            if($threasure_room_info&&!empty($threasure_room_info)){
                $StoreSmall = $threasure_room_info["StoreSmall"]; //小奖池金额
                $StoreLarge = $threasure_room_info["StoreLarge"];       //大奖池金额
                $StoreSmallLuck = $threasure_room_info["StoreSmallLuck"];            //小奖池幸运值
                $StoreLargeLuck = $threasure_room_info["StoreLargeLuck"];      //大奖池幸运值
                $DrillProbit = $threasure_room_info["DrillProbit"];     //钻头概率
            }
        }
        if(is_post()){
            $admin_info = $_SESSION['my_info'];
            $m_admin_log = M("admin_log");
            if($room_id>=900&&$room_id<1100){
                $nine_fruit_room_data['StoreSmall'] = I("StoreSmall"); //小奖池金额
                $nine_fruit_room_data['StoreLarge'] = I("StoreLarge");       //大奖池金额
                $nine_fruit_room_data['StoreSmallLuck'] = I("StoreSmallLuck");            //小奖池幸运值
                $nine_fruit_room_data['StoreLargeLuck'] = I("StoreLargeLuck");      //大奖池幸运值
                for($i=1;$i<51;$i++){
                    $m_redis->hmset("nine_fruit_room_info_".$room_id."_".$i,$nine_fruit_room_data);
                }
                $info = "更改房间".$room_id."概率成功 ";
                $admin_log_data["a_id"] = $admin_info["aid"];
                $admin_log_data["a_username"] = $admin_info["email"];
                $admin_log_data["info"] = $info;
                $admin_log_data["add_time"] = time();
                $m_admin_log->add($admin_log_data);
                $this->success("更新成功",U('game/index'));die();
            }
            if($room_id>=1100&&$room_id<1122){
                $threasure_room_data['StoreSmall'] = I("StoreSmall"); //小奖池金额
                $threasure_room_data['StoreLarge'] = I("StoreLarge");       //大奖池金额
                $threasure_room_data['StoreSmallLuck'] = I("StoreSmallLuck");            //小奖池幸运值
                $threasure_room_data['StoreLargeLuck'] = I("StoreLargeLuck");      //大奖池幸运值
                $threasure_room_data['DrillProbit'] = I("DrillProbit");     //钻头概率
                for($j=1;$j<51;$j++){
                    $m_redis->hmset("threasure_room_info_".$room_id."_".$j,$threasure_room_data);
                }
                $info = "更改房间".$room_id."概率成功 ";
                $admin_log_data["a_id"] = $admin_info["aid"];
                $admin_log_data["a_username"] = $admin_info["email"];
                $admin_log_data["info"] = $info;
                $admin_log_data["add_time"] = time();
                $m_admin_log->add($admin_log_data);
                $this->success("更新成功",U('game/index'));die();
            }
            $upd_date["base_limit"] = I("base_limit");
            $upd_date["base_times"] = I("base_times");
            $upd_date["enter_limit"] = I("enter_limit");
            $upd_date["high_limit"] = I("high_limit");
            $upd_date["top_limit"] = I("top_limit");
            $upd_date["basefee"] = I("basefee");
            $upd_date["basepoint"] = I("basepoint");
            $upd_date["ulosecoin"] = I("ulosecoin");
            $upd_date["uwincoin"] = I("uwincoin");
            $upd_date["aistart"] = I("aistart");
            
            $upd_where["room_id"] = $room_id;
            
            $return_status = $room_m->where($upd_where)->save($upd_date);
            if($return_status){
                $extra = array(
                        'roomid' => array ('type' => 'int','size' => 4,'value' => $room_id)
                );
                $response = Sockets :: call('call_back', 10, 500, $room_id, $extra);
                add_log("game.log", "admin", "Socket返回数据". var_export($response, true));
                
                $info = "更改房间".$room_id."最大输的钱为 ".$upd_date["ulosecoin"]." 最大赢得钱为".$upd_date["uwincoin"];
                $admin_log_data["a_id"] = $admin_info["aid"];
                $admin_log_data["a_username"] = $admin_info["email"];
                $admin_log_data["info"] = $info;
                $admin_log_data["add_time"] = time();
                $m_admin_log->add($admin_log_data);
                $this->success("更新成功",U('game/index'));
                //echo '<script>alert("更新成功");location="'.U('game/index').'";</script>';
            }else{
                $info = "更改房间 ".$room_id." 失败";
                $admin_log_data["a_id"] = $admin_info["aid"];
                $admin_log_data["a_username"] = $admin_info["email"];
                $admin_log_data["info"] = $info;
                $admin_log_data["add_time"] = time();
                $m_admin_log->add($admin_log_data);
                $this->error("更新失败",U("game/roominfo",["room_id"=>$room_id]));
                //echo '<script>alert("更新失败");location="'.U("game/roominfo",["room_id"=>$room_id]).'";</script>';
            }
        }
        $this->assign("StoreSmall", $StoreSmall);
        $this->assign("StoreLarge", $StoreLarge);
        $this->assign("StoreSmallLuck", $StoreSmallLuck);
        $this->assign("StoreLargeLuck", $StoreLargeLuck);
        $this->assign("DrillProbit", $DrillProbit);
        $this->assign("return_url", $return_url);
        $this->assign("roomInfo", $room_info);
        $this->display();
    }
    
    /**
     * 游戏全服公告列表
     */
    public function news(){
        $current_page = (int)I('p',1);
        $db_config = C("DB_CONFIG2");
        $m = M("user_news",$db_config["DB_PREFIX"],$db_config);
        $pagesize = 15;
        $count = $m->count();
        $pre_page = getpage($count, $pagesize);
        $page = $pre_page->show();
        $news_list = $m->order("id DESC")->page($current_page,$pagesize)->select();
        $this->assign("sendUrl",U("game/addNews"));
        $this->assign("newsList",$news_list);
        $this->assign("page",$page);
        $this->display();
    }
    /**
     * 删除公告
     */
    public function delnews(){
        $id = (int)I('id');
        $return_url = U("game/news");
        if(!$id){
            $this->error("参数错误",$return_url);
        }
        $db_config = C("DB_CONFIG2");
        $m = M("user_news",$db_config["DB_PREFIX"],$db_config);
        $where["id"] = $id;
        $return_status = $m->where($where)->delete();
        $admin_info = $_SESSION['my_info'];
        $m_admin_log = M("admin_log");
        if($return_status){
            $info = "删除公告 ".$id." 成功";
            $admin_log_data["a_id"] = $admin_info["aid"];
            $admin_log_data["a_username"] = $admin_info["email"];
            $admin_log_data["info"] = $info;
            $admin_log_data["add_time"] = time();
            $m_admin_log->add($admin_log_data);
            $this->success("删除公告成功",$return_url);
        }else{
            $info = "删除公告 ".$id." 失败";
            $admin_log_data["a_id"] = $admin_info["aid"];
            $admin_log_data["a_username"] = $admin_info["email"];
            $admin_log_data["info"] = $info;
            $admin_log_data["add_time"] = time();
            $m_admin_log->add($admin_log_data);
            $this->error("删除公告失败",$return_url);
        }
    }
    
    /**
     * 添加公告
     */
    public function addnews(){
        $return_url = U('game/news');
        $news_data["start_date"] = '';
        $news_data["expire_time"] = '';
        $news_data["interval"] = '';
        $news_data["describe"] = '';
        $error = '';
        $admin_info = $_SESSION['my_info'];
        $m_admin_log = M("admin_log");
        $m_redis = new Redis();
        $db_config = C("DB_CONFIG2");
        $m = M("user_news",$db_config["DB_PREFIX"],$db_config);
        if(is_post()){
            $data = $_POST;
            $news_data["describe"] = $data["describe"];
            $news_data["sender"] = $admin_info["email"];
            $news_data["start_date"] = $data["start_date"];
            $news_data["expire_time"] = $data["expire_time"];
            $news_data["interval"] = $data["interval"];
            if(!$data["start_date"]){
                $error = '发送时间不能为空';
            }elseif(strtotime($data["start_date"])<=time()){
                $error = '发送时间比赛大于当前时间';
            }elseif(strtotime($data["expire_time"])<strtotime($data["start_date"])){
                $error = '结束时间不能小于开始时间';
            }elseif(!$data["interval"]){
                $error = '时间间隔不能为空';
            }elseif(!$data["describe"]){
                $error = '内容不能为空';
            }else{
                $news_datas["describe"] = $data["describe"];
                $news_datas["sender"] = $admin_info["email"];
                $news_datas["start_date"] = strtotime($data["start_date"]);
                $news_datas["expire_time"] = strtotime($data["expire_time"]);
                $news_datas["interval"] = $data["interval"];
                $news_datas["sendtime"] = time();
                $return_id = $m->add($news_datas);
                if($return_id){
                    $news_info["id"] = (int)$return_id;
                    $news_info["sendtime"] = (int)$news_datas["sendtime"];
                    $news_info["start_date"] = (int)$news_datas["start_date"];
                    $news_info["expire_time"] = (int)$news_datas["expire_time"];
                    $news_info["interval"] = (int)$news_datas["interval"];
                    $m_redis->publish("gamenews", $news_info);

                    $info = "添加公告 ".$return_id." 成功";
                    $admin_log_data["a_id"] = $admin_info["aid"];
                    $admin_log_data["a_username"] = $admin_info["email"];
                    $admin_log_data["info"] = $info;
                    $admin_log_data["add_time"] = time();
                    $m_admin_log->add($admin_log_data);
                    echo '<script>alert("添加成功");location="'.$return_url.'";</script>';
                }else{
                    $info = "添加公告失败";
                    $admin_log_data["a_id"] = $admin_info["aid"];
                    $admin_log_data["a_username"] = $admin_info["email"];
                    $admin_log_data["info"] = $info;
                    $admin_log_data["add_time"] = time();
                    $m_admin_log->add($admin_log_data);
                    echo '<script>alert("添加公告失败");location="'.$return_url.'";</script>';
                }
            }
        }
        $this->assign("error", $error);
        $this->display();
    }
    
    /**
     * 游戏邮件列表
     */
    public function mail(){
        $current_page = (int)I('p',1);
        $search_key = (int)I('search_key');
        $where = [];
        if($search_key){
            $where["uid"] = $search_key;
        }
        $db_config = C("DB_CONFIG2");
        $m = M("user_mail",$db_config["DB_PREFIX"],$db_config);
        $pagesize = 15;
        $count = $m->where($where)->count();
        $pre_page = getpage($count, $pagesize);
        $page = $pre_page->show();
        $mail_list = $m->where($where)->order("mailid DESC")->page($current_page,$pagesize)->select();
        $this->assign("sendUrl",U("game/addMail"));
        $this->assign("mailList",$mail_list);
        $this->assign("page",$page);
        $this->display();
    }
    /**
     * 添加公告
     */
    public function addmail(){
        $return_url = U('game/mail');
        $uid = I("uid");
        $rurl = I('rurl');
        if($rurl){
            $return_url = U($rurl);
        }
        $error = '';
        $admin_info = $_SESSION['my_info'];
        $m_admin_log = M("admin_log");
        $db_config = C("DB_CONFIG2");
        $m = M("user_mail",$db_config["DB_PREFIX"],$db_config);
        $m_user = D("User");
        if(is_post()){
            $data = $_POST;
            if(!$data["uid"]){
                $error = '用户id不能为空';
            }elseif(!$data["title"]){
                $error = '标题不能为空';
            }elseif(!$data["describe"]){
                $error = '内容不能为空';
            }else{
                $user_info = $m_user->getUserOne($data["uid"]);
                if(!$user_info){
                    $error = '用户不存在';
                }else{
                    $mail_data["uid"] = $data["uid"];
                    $mail_data["title"] = $data["title"];
                    $mail_data["describe"] = $data["describe"];
                    $mail_data["sender"] = $admin_info["email"];
                    $mail_data["sendtime"] = time();
                    $mail_data["coin"] = $data["coin"];
                    $mail_data["awardnum"] = $data["awardnum"];
                    $return_id = $m->add($mail_data);
                    if($return_id){
                        $info = "添加邮件 ".$return_id." 成功";
                        $admin_log_data["a_id"] = $admin_info["aid"];
                        $admin_log_data["a_username"] = $admin_info["email"];
                        $admin_log_data["info"] = $info;
                        $admin_log_data["add_time"] = time();
                        $m_admin_log->add($admin_log_data);
                        echo '<script>alert("添加成功");location="'.$return_url.'";</script>';
                    }else{
                        $info = "添加邮件失败";
                        $admin_log_data["a_id"] = $admin_info["aid"];
                        $admin_log_data["a_username"] = $admin_info["email"];
                        $admin_log_data["info"] = $info;
                        $admin_log_data["add_time"] = time();
                        $m_admin_log->add($admin_log_data);
                        echo '<script>alert("添加公告失败");location="'.$return_url.'";</script>';
                    }
                }
            }
        }
        $this->assign("uid", $uid);
        $this->assign("return_url", $return_url);
        $this->assign("error", $error);
        $this->display();
    }
    /**
     * 发送道具
     */
    public function sendbean(){
        $m_user = D("User");
        if(is_post()){
            $data = $_POST;
            $uid = $data["uid"];
            $gold_bean = $data["gold_bean"];
            $silver_bean = $data["silver_bean"];
            $diamond = $data["diamond"];
            $user_info = $m_user->getGameUserOne($uid);
            if(!$user_info){
                $error = "用户不存在";
            }else{
                $admin_info = $_SESSION['my_info'];
                $m_admin_log = M("admin_log");
                if($gold_bean>0){ //金币
                    $extra = array(
                            'add' => array('type' => 'int','size' => 2,'value' => 1),
                            'coin' => array ('type' => 'int','size' => 4,'value' => $gold_bean),
                            'type' => array('type' => 'int','size' => 2,'value' => 91),
                            'cointype' => array('type' => 'int','size' => 2,'value' => 1)
                    );
                    $custom_rsp = Sockets :: call('call_back', 10, 20, $uid, $extra);
                    add_log("bean.log", "admin", "发送金币Socket返回数据：". var_export($custom_rsp, true));
                }
//                if($silver_bean>0){  //时光豆
//                    $extra = array(
//                            'add' => array('type' => 'int','size' => 2,'value' => 1),
//                            'coin' => array ('type' => 'int','size' => 4,'value' => $silver_bean),
//                            'type' => array('type' => 'int','size' => 2,'value' => 91),
//                            'cointype' => array('type' => 'int','size' => 2,'value' => 3)
//                    );
//                    $custom_rsp = Sockets :: call('call_back', 10, 20, $uid, $extra);
//                    add_log("bean.log", "admin", "发送时光豆Socket返回数据：". var_export($custom_rsp, true));
//                }
                if($diamond>0){  //钻石
                    $extra = array(
                            'add' => array('type' => 'int','size' => 2,'value' => 1),
                            'coin' => array ('type' => 'int','size' => 4,'value' => $diamond),
                            'type' => array('type' => 'int','size' => 2,'value' => 91),
                            'cointype' => array('type' => 'int','size' => 2,'value' => 3)
                    );
                    $custom_rsp = Sockets :: call('call_back', 10, 20, $uid, $extra);
                    add_log("bean.log", "admin", "发送钻石Socket返回数据：". var_export($custom_rsp, true));
                }
        
                $info = "发送给用户 ".$uid." 金币： ".$gold_bean."；时光豆：".$silver_bean."；钻石：".$diamond." 成功";
                $admin_log_data["a_id"] = $admin_info["aid"];
                $admin_log_data["a_username"] = $admin_info["email"];
                $admin_log_data["info"] = $info;
                $admin_log_data["add_time"] = time();
                $m_admin_log->add($admin_log_data);
                $return_url = U('game/sendBean');
//                echo '<script>alert("发送成功");location="'.$return_url.'";</script>';die();
                $this->success("发送成功",$return_url);
            }
        }
        $this->assign("error", $error);
        $this->display();
    }
    
    public function service(){
        $service_m = M("service");
        $service_info = $service_m->find();
        if(is_post()){
            $data = $_POST;
            $id = $data["id"];
            unset($data["id"]);
            if($id){
                $service_m->where(["id"=>$id])->save($data);
            }else{
                $service_m->add($data);
            }
            $service_info = $service_m->find();
        }
        $this->assign("service_info",$service_info);
        $this->display();
    }
    /**
     * 充值档次
     */
    public function shop(){
        $shop_m = D("Shop");
        $shop_list = $shop_m->getList();
        $this->assign("addUrl",U("game/addshop"));
        $this->assign("shopList",$shop_list);
        $this->display();
    }
    
    /**
     * 删除充值档次
     */
    public function delshop(){
        $id = (int)I('id');
        $return_url = U("game/shop");
        if(!$id){
            $this->error("参数错误",$return_url);
        }
        $shop_m = D("Shop");
        $where["id"] = $id;
        $return_status = $shop_m->delShop($where);
        $admin_info = $_SESSION['my_info'];
        $m_admin_log = M("admin_log");
        if($return_status){
            $info = "删除充值档次 ".$id." 成功";
            $admin_log_data["a_id"] = $admin_info["aid"];
            $admin_log_data["a_username"] = $admin_info["email"];
            $admin_log_data["info"] = $info;
            $admin_log_data["add_time"] = time();
            $m_admin_log->add($admin_log_data);
            $this->success("删除充值档次成功",$return_url);
        }else{
            $info = "删除充值档次 ".$id." 失败";
            $admin_log_data["a_id"] = $admin_info["aid"];
            $admin_log_data["a_username"] = $admin_info["email"];
            $admin_log_data["info"] = $info;
            $admin_log_data["add_time"] = time();
            $m_admin_log->add($admin_log_data);
            $this->error("删除充值档次失败",$return_url);
        }
    }
    
    /**
     * 添加充值档次
     */
    public function addshop(){
        $return_url = U('game/shop');
        $shop_data["type"] = '1';
        $shop_data["name"] = '';
        $shop_data["price"] = '';
        $shop_data["coin"] = '';
        $error = '';
        $admin_info = $_SESSION['my_info'];
        $m_admin_log = M("admin_log");
        $shop_m = D("Shop");
        if(is_post()){
            $data = $_POST;
            $shop_data["type"] = $data["type"];
            $shop_data["name"] = $data["name"];
            $shop_data["price"] = $data["price"];
            $shop_data["coin"] = $data["coin"];
            $n_preg = "/^[0-9]*$/";
            if(!$data["name"]){
                $error = '名称不能为空';
            }elseif(!preg_match($n_preg, $data["price"])){
                $error = '价格为数字';
            }elseif(!preg_match($n_preg, $data["coin"])){
                $error = '金币数量为数字';
            }else{
                $return_id = $shop_m->addShop($shop_data);
                if($return_id){
                    $info = "添加充值档次 ".$return_id." 成功";
                    $admin_log_data["a_id"] = $admin_info["aid"];
                    $admin_log_data["a_username"] = $admin_info["email"];
                    $admin_log_data["info"] = $info;
                    $admin_log_data["add_time"] = time();
                    $m_admin_log->add($admin_log_data);
                    echo '<script>alert("添加充值档次成功");location="'.$return_url.'";</script>';
                }else{
                    $info = "添加充值档次失败";
                    $admin_log_data["a_id"] = $admin_info["aid"];
                    $admin_log_data["a_username"] = $admin_info["email"];
                    $admin_log_data["info"] = $info;
                    $admin_log_data["add_time"] = time();
                    $m_admin_log->add($admin_log_data);
                    echo '<script>alert("添加充值档次失败");location="'.$return_url.'";</script>';
                }
            }
        }
        $this->assign("shop_data", $shop_data);
        $this->assign("error", $error);
        $this->display();
    }
    
    /**
     * 银行记录
     */
    public function bankrecord(){
        $bank_record = D("BankRecord");
        $m_user = D("User");
        $current_page = (int)I('p',1);
        $search_key = I('search_key','');
        
        $where = [];
        if($search_key){
            $where["uid"] = $search_key;
        }
        $db_config = C("DB_CONFIG2");
        $m = M("bank_record",$db_config["DB_PREFIX"],$db_config);
        $pagesize = 15;
        $count = $m->where($where)->count();
        $pre_page = getpage($count, $pagesize);
        $page = $pre_page->show();
        $rec_list = $m->where($where)->order("create_time DESC")->page($current_page,$pagesize)->select();
        $rec_arr = [];
        if($rec_list){
            foreach ($rec_list as $value) {
                $user_info = $m_user->getGameUserOne(["uid"=>$value["uid"]]);
                $from_user_info = $m_user->getGameUserOne(["uid"=>$value["from_or_to_uid"]]);
                $value["nickname"] = $user_info["nickname"];
                $value["from_nickname"] = $from_user_info["nickname"];
                $value["bankcoin"] = $user_info["bankcoin"];
                $value["type_name"] = $bank_record->getTypeText($value["type"]);
                $rec_arr[] = $value;
            }
        }
        $this->assign("page",$page);
        $this->assign("search_key",$search_key);
        $this->assign("list", $rec_arr);
        $this->display();
    }
    /**
     * 金币记录
     */
    public function economylist(){
        $current_page = (int)I('p',1);
        $uid = I('uid');
        $coin_type = I('coin_type',0);
        $view_datas['coin_type'] = $coin_type;
        $view_datas['uid'] = $uid;
        
        $db_config = C("DB_CONFIG2");
        $m_log_economy = M("log_economy",$db_config["DB_PREFIX"],$db_config);
        $m_user = D("User");
        $where = [];
        if($coin_type){
            $where["coin_type"] = $coin_type;
        }
        if($uid){
            $where["uid"] = $uid;
        }
        $count = $m_log_economy->where($where)->count();
        $per_page = 15;//每页显示条数
        $page       = getpage($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $showPage       = $page->show();// 分页显示输出
        $economy_list = $m_log_economy->where($where)->order("logid DESC")->page($current_page.','.$per_page)->select();
        $economy_arr = [];
        
        if($economy_list&&!empty($economy_list)){
            foreach ($economy_list as $value) {
                $u_info = $m_user->getUserOne($value["uid"]);
                $value["nickname"] = $u_info["nickname"];
                $room_id = $value["roomid"];
                $value["room_name"] = "";
                if($room_id){
                    $room_info = $m_user->getRoom($room_id);
                    if($room_info){
                        $value["room_name"] = $room_info["name"]."-".$room_info["summary"];
                    }
                }
                $value["logtype_name"] = logtypeName($value["logtype"]);
                if($value["oper"]==1){
                    $value["oper_name"] = "增加";
                    $value["changevalue"] = "+ ".$value["changevalue"];
                }elseif($value["oper"]==2){
                    $value["oper_name"] = "减少";
                    $value["changevalue"] = "- ".$value["changevalue"];
                }else{
                    $value["oper_name"] = "不统计";
                    $value["changevalue"] = $value["changevalue"];
                }
                $economy_arr[] = $value;
            }
        }
        $view_datas['economy_list'] = $economy_arr;
        $this->assign("datas", $view_datas);
        $this->assign("uid", $uid);
        $this->assign("coin_type", $coin_type);
        $this->assign("page", $showPage);
        $this->display();
    }
}
