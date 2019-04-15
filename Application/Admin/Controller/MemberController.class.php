<?php
namespace Admin\Controller;
class MemberController extends CommonController {
    public function index(){
        $current_page = (int)I('p',1);
        $view_datas['search_key'] = $search_key = I('search_key');
        $m_user = D("User");
        $where = [];
        if($search_key){
            $where_s['id']  = array('like', "%{$search_key}%");
            $where_s['login_id']  = array('like',"%{$search_key}%");
            $where_s['_logic'] = 'or';
            $where['_complex'] = $where_s;
        }
        $count = $m_user->where($where)->count();
        $per_page = 15;//每页显示条数
        $page       = getpage($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $showPage       = $page->show();// 分页显示输出
        $view_datas['list'] = $m_user->where($where)->order("add_time DESC")->page($current_page.','.$per_page)->select();
        
        $view_datas['num'] = $count;
        $this->assign("datas", $view_datas);
        $this->assign("page", $showPage);
        $this->display("memberlist");
    }
    
    
    public function info(){
        $user_id = (int)I('uid');
        $rurl = base64_decode(I('rurl'));
        $status = I('status',3);
        $return_url = U('member/index');
        if($status==1){
            $return_url = U('member/fuserlist');
        }
        if($rurl){
            $return_url = U($rurl);
        }
        $admin_info = $_SESSION['my_info'];
        $db_config = C("DB_CONFIG2");
        $M = M("user",$db_config["DB_PREFIX"],$db_config);
        $m_user = D("User");
        $m_order = D("Order");
        $m_admin_log = M("admin_log");
        $view_datas['return_url'] = $return_url;
        $view_datas['status'] = $status;
        $sql = "SELECT sum(amount) AS amount FROM dz_order WHERE u_id=".$user_id." AND type IN(1,2) AND `status`=200";
        $order_info = current($m_order->getOneBySql($sql));
        $view_datas['user_data'] = $m_user->getUserOne($user_id);
        $view_datas['user_data']["amount"] = 0;
        if($order_info&&$order_info["amount"]){
            $view_datas['user_data']["amount"] = $order_info["amount"];
        }
        $game_user_info = $m_user->getGameUserOne(["uid"=>$user_id]);
        $view_datas['user_data']["coinnum"] = 0;
        $view_datas['user_data']["bankcoin"] = 0;
        if($game_user_info&&!empty($game_user_info)){
            $view_datas['user_data']["coinnum"] = $game_user_info["coinnum"];
            $view_datas['user_data']["bankcoin"] = $game_user_info["bankcoin"];
        }
        $user_agency = $m_user->getUserAgencyByUserId($user_id);
        $view_datas['user_data']["grade"] = 0;
        $view_datas['user_data']["parent_id"] = 0;
        if($user_agency&&!empty($user_agency)){
            $view_datas['user_data']["grade"] = $user_agency["grade"];
            $view_datas['user_data']["parent_id"] = $user_agency["parent_id"];
        }
        $user_direct_list = $m_user->getUserAgencySubordinates(['parent_id'=>$user_id]); 
        $view_datas['user_data']["direct"] = $user_direct_list&&!empty($user_direct_list)?count($user_direct_list):0; //直系下属
        $ag_user_list  =  $m_user->agencyList($user_id);
        $direct = [];
        if($ag_user_list){
            foreach ($ag_user_list["user_down"] as $value) {
                $ag_user_info = $m_user->getUserOne($value["id"]);
                $value = $ag_user_info;
                $ag_game_user_info = $m_user->getGameUserOne(["uid"=>$value["id"]]);
                $sql = "SELECT sum(amount) AS amount FROM dz_order WHERE u_id=".$value["id"]." AND type IN(1,2) AND `status`=200";
                $order_info = current($m_order->getOneBySql($sql));
                $value["amount"] = 0;
                if($order_info&&$order_info["amount"]){
                    $value["amount"] = $order_info["amount"];
                }
                $value["coinnum"] = 0;
                $value["offline"] = 0;
                if($ag_game_user_info){
                    $value["coinnum"] = $ag_game_user_info["coinnum"];
                    $value["offline"] = $ag_game_user_info["coinnum"];
                }
                $user_agencys = $m_user->getUserAgencyByUserId($value["id"]);
                $value["grade"] = "注册用户";
                if($user_agencys&&!empty($user_agencys)){
                    $value["grade"] = $m_user->getLevelText($user_agencys["grade"]);
                }
                $direct[] = $value;
            }
        }
        $view_datas["direct"] = $direct;
        if(is_post()){
            $data["is_proxy"] = I("post.is_proxy");
            $bankpwd = trim(I("post.bankpwd"));
            $password = trim(I("post.password"));
            $where["id"] = I("post.uid");
            $u_info = $m_user->getUserOne($where["id"]);
            if($password){
                $p_preg = '/[\x{4e00}-\x{9fa5}]/u';
                if (preg_match($p_preg, $password) || strlen($password) < 6 || strlen($password) > 12) {
                    $this->error("密码长度为6位至12位字符！", U('member/index',["uid"=>$where["id"]]));
                }
                $data["password"] =  md5($u_info['username'] . $password);
            }
            if($bankpwd){
                $n_preg = "/^\d{6}$/";
                if (!preg_match($n_preg, $password)) {
                    $this->error("银行密码为6位的数字！", U('member/index',["uid"=>$where["id"]]));
                }
            }
            if($u_info["status"]!=I("post.ustatus")){
                $data["status"] = I("post.ustatus");
            }
            $return_status = $m_user->where($where)->save($data);
            if($bankpwd){
                $data["bankpwd"] = I("post.bankpwd");
            }
            unset($data["status"]);
            $return_status = $M->where(["uid"=>$where["id"]])->save($data);
            
            
            if($return_status){
                $view_datas['user_data']["status"] = $data["status"];
                $view_datas['user_data']["is_proxy"] = $data["is_proxy"];
                if($data["status"]){
                    $admin_log_data["a_id"] = $admin_info["aid"];
                    $admin_log_data["a_username"] = $admin_info["email"];
                    $admin_log_data["info"] = "关闭用户 ".$view_datas['user_data']["id"]." 成功";
                    $admin_log_data["add_time"] = time();
                }else{
                    $admin_log_data["a_id"] = $admin_info["aid"];
                    $admin_log_data["a_username"] = $admin_info["email"];
                    $admin_log_data["info"] = "开启用户 ".$view_datas['user_data']["id"]." 成功";
                    $admin_log_data["add_time"] = time();
                }
                $m_admin_log->add($admin_log_data);
                $this->success("修改成功！", U('member/index',["uid"=>$where["id"]]));
            }
        }        
        $this->assign("datas", $view_datas);
        $this->assign("return_url", $return_url);
        $this->display();
    }
    /**
     * 用户金币记录
     */
    public function economylist(){
        $rurl = I('rurl');
        $current_page = (int)I('p',1);
        $uid = I('uid');
        $coin_type = I('coin_type',0);
        $view_datas['coin_type'] = $coin_type;
        $view_datas['uid'] = $uid;
        $view_datas['rurl'] = $rurl;
        $view_datas['return_url'] = U(base64_decode($rurl));
        
        $db_config = C("DB_CONFIG2");
        $m_log_economy = M("log_economy",$db_config["DB_PREFIX"],$db_config);
        $m_user = D("User");
        $where["uid"] = $uid;
        if($coin_type){
            $where["coin_type"] = $coin_type;
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
        $z_changevalue_sql = "SELECT SUM(changevalue) AS num FROM tb_log_economy WHERE uid={$uid} AND oper=1"; //增加
        $j_changevalue_sql = "SELECT SUM(changevalue) AS num FROM tb_log_economy WHERE uid={$uid} AND oper=2"; //减少
        $z_changevalue = current($m_log_economy->query($z_changevalue_sql));
        $j_changevalue = current($m_log_economy->query($j_changevalue_sql));
        $view_datas['z_amount'] = $z_changevalue["num"];
        $view_datas['j_amount'] = $j_changevalue["num"];
        $view_datas['economy_list'] = $economy_arr;
        $this->assign("return_url", $view_datas['return_url']);
        $this->assign("datas", $view_datas);
        $this->assign("uid", $uid);
        $this->assign("rurl", $view_datas['rurl']);
        $this->assign("coin_type", $coin_type);
        $this->assign("page", $showPage);
        $this->display();
    }

    /**
     * 在线用户
     */
    public function fuserlist(){
        $current_page = (int)I('p',1);
        $view_datas['search_key'] = $search_key = I('search_key');
        $coinnum_sort = I('csort');
        $bankcoin_sort = I('asort');
        $db_config = C("DB_CONFIG2");
        $M = M("user",$db_config["DB_PREFIX"],$db_config);
        $m_user = D("User");
        $m_order = D("Order");
        $page_url = "";
        $sort_name = "uid";
        $sort = "DESC";
        $where["uid"] = array("EGT","100000");
        $where['offline'] = 1;
        if($search_key){
            $where_s['uid']  = array('like', "%{$search_key}%");
            $where_s['nickname']  = array('like',"%{$search_key}%");
            $where_s['_logic'] = 'or';
            $where['_complex'] = $where_s;
        }
        $count = $M->where($where)->count();
        $per_page = 15;//每页显示条数
        $page       = getpage($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $showPage       = $page->show();// 分页显示输出
        $user_list = $M->where($where)->order("uid DESC")->page($current_page.','.$per_page)->select();
        if($coinnum_sort){
            $sort_name = "coinnum";
            $sort = $coinnum_sort;
            $page_url = "&csort=".$coinnum_sort;
            $user_list = $M->where($where)->order("coinnum ".$coinnum_sort)->page($current_page.','.$per_page)->select();
            if($coinnum_sort=="ASC"){
                $coinnum_sort = "DESC";
            }else{
                $coinnum_sort = "ASC";
            }
            $view_datas['c_sort_url'] = U('member/fuserlist')."?status=".$status."&csort=".$coinnum_sort;
        }else{
            $view_datas['c_sort_url'] = U('member/fuserlist')."?status=".$status."&csort=ASC";
        }
        if($bankcoin_sort){
            $sort_name = "bankcoin";
            $sort = $bankcoin_sort;
            $page_url = "&asort=".$bankcoin_sort;
            $user_list = $M->where($where)->order("bankcoin ".$bankcoin_sort)->page($current_page.','.$per_page)->select();
            if($bankcoin_sort=="ASC"){
                $bankcoin_sort = "DESC";
            }else{
                $bankcoin_sort = "ASC";
            }
            $view_datas['a_sort_url'] = U('member/fuserlist')."?status=".$status."&asort=".$bankcoin_sort;
        }else{
            $view_datas['a_sort_url'] = U('member/fuserlist')."?status=".$status."&asort=ASC";
        }
        
        if($user_list){
            foreach ($user_list as $val) {
                $val["id"] = $val["uid"];
                $user_info = $m_user->getUserOne($val["id"]);
                $val["type"] = isset($user_info["type"])?$user_info["type"]:1;
                $val["status"] = isset($user_info["status"])?$user_info["status"]:0;
                $sql = "SELECT sum(amount) AS amount FROM dz_order WHERE u_id=".$val["id"]." AND type IN(1,2) AND `status`=200";
                $order_info = current($m_order->getOneBySql($sql));
                $val["amount"] = 0;
                $user_agency = $m_user->getUserAgencyByUserId($val["id"]);
                $val["grade"] = "注册用户";
                if($user_agency&&!empty($user_agency)){
                    $val["grade"] = $m_user->getLevelText($user_agency["grade"]);
                }
                if($order_info&&$order_info["amount"]){
                    $val["amount"] = $order_info["amount"];
                }
                $room_id = $val["last_room"];
                $val["room_name"] = "";
                if($room_id){
                    $room_info = $m_user->getRoom($room_id);
                    if($room_info){
                        $val["room_name"] = $room_info["name"]."-".$room_info["summary"];
                    }
                }
                $view_datas['list'][] = $val;
            }
        }
        $view_datas['num'] = $count;
        $this->assign("datas", $view_datas);
        $this->assign("page", $showPage);
        $this->display();
    }
}