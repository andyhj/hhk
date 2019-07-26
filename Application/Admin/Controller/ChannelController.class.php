<?php
/**
 * 通道管理类
 */
namespace Admin\Controller;
class ChannelController  extends CommonController{
    public function index(){
        $this->display();
    }

    /**
     * 通道列表
     */
    public function channel(){
        $current_page = (int)I('p',1);
        $per_page = 15;//每页显示条数
        $channel_m = M("channel");
        $count = $channel_m->count();
        $page = getpage($count, $per_page);
        $channel_list = $channel_m->page($current_page.','.$per_page)->select();
        $this->assign("page",$page->show());
        $this->assign("channel_list",$channel_list);
        $this->display();
    }
    /**
     * 添加通道
     */
    public function addChannel(){
        $data["name"] = I("name",""); //通道名称
        $data["fee"] = I("fee",""); //通道成本费率
        $data["close_rate"] = I("close_rate",""); //通道成本结算费用（每笔）
        $data["user_fee"] = I("user_fee","");  //普通用户交易费率
        $data["plus_user_fee"] = I("plus_user_fee",""); //plus用户交费率
        $data["user_close_rate"] = I("user_close_rate",""); //普通用户结算费用（每笔）
        $data["plus_user_close_rate"] = I("plus_user_close_rate",""); //plus用户结算费用（每笔）
        $data["code"] = I("code",""); //通道编码
        $error = "";
        if(is_post()){
            if(!$data["name"] || !$data["fee"] || !$data["close_rate"] || !$data["user_fee"] || !$data["plus_user_fee"] || !$data["user_close_rate"] || !$data["plus_user_close_rate"] || !$data["code"]){
                $error = "参数不完整错误";
            }else{
                $r_id = M("channel")->add($data);
                $admin_info = $_SESSION['my_info'];
                $m_admin_log = M("admin_log");
                if($r_id){
                    $info = "添加通道".$r_id."成功 ";
                    $admin_log_data["a_id"] = $admin_info["aid"];
                    $admin_log_data["a_username"] = $admin_info["email"];
                    $admin_log_data["info"] = $info;
                    $admin_log_data["add_time"] = time();
                    $m_admin_log->add($admin_log_data);
                    $this->success("添加成功",U("channel/channel"));die();
                }
                $info = "添加通道失败";
                $admin_log_data["a_id"] = $admin_info["aid"];
                $admin_log_data["a_username"] = $admin_info["email"];
                $admin_log_data["info"] = $info;
                $admin_log_data["add_time"] = time();
                $m_admin_log->add($admin_log_data);
                $error = $info;
            }
        }
        $this->assign("error",$error);        
        $this->assign("data",$data);
        $this->display();
    }
    /**
     * 更新通道
     */
    public function editChannel(){
        $id = I("id");
        if(!$id){
            $this->error("参数错误",U("channel/index"));die();
        }
        $channel_info = M("channel")->where(["id"=>$id])->find();
        if(!$channel_info){
            $this->error("通道不存在",U("channel/channel"));die();
        }
        $data["name"] = I("name",$channel_info["name"]); //通道名称
        $data["fee"] = I("fee",$channel_info["fee"]); //通道成本费率
        $data["close_rate"] = I("close_rate",$channel_info["close_rate"]); //通道成本结算费用（每笔）
        $data["user_fee"] = I("user_fee",$channel_info["user_fee"]);  //普通用户交易费率
        $data["plus_user_fee"] = I("plus_user_fee",$channel_info["plus_user_fee"]); //plus用户交费率
        $data["user_close_rate"] = I("user_close_rate",$channel_info["user_close_rate"]); //普通用户结算费用（每笔）
        $data["plus_user_close_rate"] = I("plus_user_close_rate",$channel_info["plus_user_close_rate"]); //plus用户结算费用（每笔）
        $data["code"] = I("code",$channel_info["code"]); //通道编码
        $error = "";
        if(is_post()){
            if(!$data["name"] || !$data["fee"] || !$data["close_rate"] || !$data["user_fee"] || !$data["plus_user_fee"] || !$data["user_close_rate"] || !$data["plus_user_close_rate"] || !$data["code"]){
                $error = "参数不完整错误";
            }else{
                $r_id = M("channel")->where(["id"=>$id])->save($data);
                $admin_info = $_SESSION['my_info'];
                $m_admin_log = M("admin_log");
                if($r_id){
                    $info = "修改通道".$id."成功 ";
                    $admin_log_data["a_id"] = $admin_info["aid"];
                    $admin_log_data["a_username"] = $admin_info["email"];
                    $admin_log_data["info"] = $info;
                    $admin_log_data["add_time"] = time();
                    $m_admin_log->add($admin_log_data);
                    $this->success("修改通道成功",U("channel/channel"));die();
                }
                $info = "修改通道失败";
                $admin_log_data["a_id"] = $admin_info["aid"];
                $admin_log_data["a_username"] = $admin_info["email"];
                $admin_log_data["info"] = $info;
                $admin_log_data["add_time"] = time();
                $m_admin_log->add($admin_log_data);
                $error = $info;
            }
        }
        $data["id"] = $id;
        $this->assign("error",$error);        
        $this->assign("data",$data);
        $this->display("addChannel");
    }
    
    /**
     * 通道移动端显示列表
     */
    public function moblie(){
        $current_page = (int)I('p',1);
        $per_page = 15;//每页显示条数
        $room_m = M("channel_moblie");
        $count = $room_m->count();
        $page = getpage($count, $per_page);
        $channel_moblie_list = $room_m->order("sort ASC")->page($current_page.','.$per_page)->select();
        $channel_moblie_arr = [];
        if($channel_moblie_list&&!empty($channel_moblie_list)){
            foreach ($channel_moblie_list as $value) {
                $cid = $value["c_id"];
                $channel_info = M("channel")->where(["id"=>$cid])->find();
                $value["channel_name"] = $channel_info["name"];
                $channel_moblie_arr[] = $value;
            }
        }
        $this->assign("page",$page->show());
        $this->assign("channel_moblie_list",$channel_moblie_arr);
        $this->display();
    }
    
    /**
     * 添加通道移动端显示
     */
    public function addChannelMoblie(){
        $data["c_id"] = I("c_id",""); //通道id
        $data["title"] = I("title",""); //通道显示标题
        $data["quota"] = I("quota",""); //限制金额说明
        $data["settlement"] = I("settlement","");  //结算方式说明
        $data["date"] = I("date",""); //服务时间
        $data["prompt"] = I("prompt",""); //提示
        $data["state"] = I("state",""); //状态，是否显示
        $data["sort"] = I("sort",""); //显示顺序
        $error = "";
        if(is_post()){
            if(!$data["c_id"] || !$data["title"] || !$data["quota"] || !$data["settlement"] || !$data["date"] || !$data["prompt"] || !$data["state"]){
                $error = "参数不完整错误";
            }else{
                $r_id = M("channel_moblie")->add($data);
                $admin_info = $_SESSION['my_info'];
                $m_admin_log = M("admin_log");
                if($r_id){
                    $info = "添加通道移动端显示".$r_id."成功 ";
                    $admin_log_data["a_id"] = $admin_info["aid"];
                    $admin_log_data["a_username"] = $admin_info["email"];
                    $admin_log_data["info"] = $info;
                    $admin_log_data["add_time"] = time();
                    $m_admin_log->add($admin_log_data);
                    $this->success("添加通道移动端显示成功",U("channel/moblie"));die();
                }
                $info = "添加通道移动端显示";
                $admin_log_data["a_id"] = $admin_info["aid"];
                $admin_log_data["a_username"] = $admin_info["email"];
                $admin_log_data["info"] = $info;
                $admin_log_data["add_time"] = time();
                $m_admin_log->add($admin_log_data);
                $error = $info;
            }
        }
        $channel_m = M("channel");
        $this->assign("channel_list",$channel_m->select());
        $this->assign("error",$error);        
        $this->assign("data",$data);
        $this->display();
    }
    /**
     * 更新通道移动端显示
     */
    public function editChannelMoblie(){
        $id = I("id");
        if(!$id){
            $this->error("参数错误",U("channel/index"));die();
        }
        $channel_modlie_info = M("channel_moblie")->where(["id"=>$id])->find();
        if(!$channel_modlie_info){
            $this->error("通道移动端显示不存在",U("channel/index"));die();
        }
        $data["c_id"] = I("c_id",$channel_modlie_info["c_id"]); //通道id
        $data["title"] = I("title",$channel_modlie_info["title"]); //通道显示标题
        $data["quota"] = I("quota",$channel_modlie_info["quota"]); //限制金额说明
        $data["settlement"] = I("settlement",$channel_modlie_info["settlement"]);  //结算方式说明
        $data["date"] = I("date",$channel_modlie_info["date"]); //服务时间
        $data["prompt"] = I("prompt",$channel_modlie_info["prompt"]); //提示
        $data["state"] = I("state",$channel_modlie_info["state"]); //状态，是否显示
        $data["sort"] = I("sort",$channel_modlie_info["sort"]); //显示顺序
        $error = "";
        if(is_post()){
            if(!$data["c_id"] || !$data["title"] || !$data["quota"] || !$data["settlement"] || !$data["date"] || !$data["prompt"]){
                $error = "参数不完整错误";
            }else{
                $r_id = M("channel_moblie")->where(["id"=>$id])->save($data);
                $admin_info = $_SESSION['my_info'];
                $m_admin_log = M("admin_log");
                if($r_id){
                    $info = "修改通道移动端显示".$r_id."成功 ";
                    $admin_log_data["a_id"] = $admin_info["aid"];
                    $admin_log_data["a_username"] = $admin_info["email"];
                    $admin_log_data["info"] = $info;
                    $admin_log_data["add_time"] = time();
                    $m_admin_log->add($admin_log_data);
                    $this->success("修改通道移动端显示成功",U("channel/moblie"));die();
                }
                $info = "修改通道移动端显示失败";
                $admin_log_data["a_id"] = $admin_info["aid"];
                $admin_log_data["a_username"] = $admin_info["email"];
                $admin_log_data["info"] = $info;
                $admin_log_data["add_time"] = time();
                $m_admin_log->add($admin_log_data);
                $error = $info;
            }
        }
        $data["id"] = $id;
        $channel_m = M("channel");
        $this->assign("channel_list",$channel_m->select());
        $this->assign("error",$error);        
        $this->assign("data",$data);
        $this->display("addChannelMoblie");
    }
}
