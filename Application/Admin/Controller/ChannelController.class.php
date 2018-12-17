<?php
/**
 * 通道管理类
 */
namespace Admin\Controller;
class ChannelController  extends CommonController{
    public function index(){
        $current_page = (int)I('p',1);
        $per_page = 15;//每页显示条数
        $room_m = M("channel");
        $count = $room_m->count();
        $page = getpage($count, $per_page);
        $channel_list = $room_m->page($current_page.','.$per_page)->select();
        $this->assign("page",$page->show());
        $this->assign("channel_list",$channel_list);
        $this->display();
    }
    public function addChannel(){
        $data["name"] = I("name",""); //通道名称
        $data["fee"] = I("fee",""); //通道成本费率
        $data["close_rate"] = I("close_rate",""); //通道成本结算费用（每笔）
        $data["user_fee"] = I("user_fee","");  //普通用户交易费率
        $data["plus_user_fee"] = I("plus_user_fee",""); //plus用户交费率
        $data["user_close_rate"] = I("user_close_rate",""); //普通用户结算费用（每笔）
        $data["plus_user_close_rate"] = I("plus_user_close_rate",""); //plus用户结算费用（每笔）
        $error = "";
        if(is_post()){
            if(!$data["name"] || !$data["fee"] || !$data["close_rate"] || !$data["user_fee"] || !$data["plus_user_fee"] || !$data["user_close_rate"] || !$data["plus_user_close_rate"]){
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
                    $this->success("添加成功",U("channel/index"));die();
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
    public function editChannel(){
        $id = I("id");
        if(!$id){
            $this->error("参数错误",U("channel/index"));die();
        }
        $channel_info = M("channel")->where(["id"=>$id])->find();
        if(!$channel_info){
            $this->error("通道不存在",U("channel/index"));die();
        }
        $data["name"] = I("name",$channel_info["name"]); //通道名称
        $data["fee"] = I("fee",$channel_info["fee"]); //通道成本费率
        $data["close_rate"] = I("close_rate",$channel_info["close_rate"]); //通道成本结算费用（每笔）
        $data["user_fee"] = I("user_fee",$channel_info["user_fee"]);  //普通用户交易费率
        $data["plus_user_fee"] = I("plus_user_fee",$channel_info["plus_user_fee"]); //plus用户交费率
        $data["user_close_rate"] = I("user_close_rate",$channel_info["user_close_rate"]); //普通用户结算费用（每笔）
        $data["plus_user_close_rate"] = I("plus_user_close_rate",$channel_info["plus_user_close_rate"]); //plus用户结算费用（每笔）
        $error = "";
        if(is_post()){
            if(!$data["name"] || !$data["fee"] || !$data["close_rate"] || !$data["user_fee"] || !$data["plus_user_fee"] || !$data["user_close_rate"] || !$data["plus_user_close_rate"]){
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
                    $this->success("修改通道成功",U("channel/index"));die();
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
}
