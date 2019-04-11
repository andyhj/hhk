<?php
namespace Admin\Controller;

/**
 * 计划类
 *
 * @author Andy
 */
class PlanController extends CommonController{
    /**
     * 计划列表
     */
    public function index(){
        $current_page = (int)I('p',1);
        $search_key = I('search_key',"");
        $per_page = 15;//每页显示条数
        $plan_m = M("plan");
        $plan_des_m = M("plan_des");
        $channel_m = M("channel");
        $db_config = C("DB_CONFIG2");
        $customer_m = M("customer_info",$db_config["DB_PREFIX"],$db_config);
        $where = [];
        if($search_key){
            $condition['id'] = $search_key;
            $condition['loginId'] = $search_key;
            $condition['_logic'] = 'OR';
            // 把查询条件传入查询方法
            $customer_info = $customer_m->where($condition)->find(); 
            if($customer_info&&!empty($customer_info)){
                $where["u_id"] = $customer_info["id"];
            }else{
                $where["u_id"] = "";
            }
        }
        $count = $plan_m->where($where)->count();
        $page = getpage($count, $per_page);
        $plan_list = $plan_m->where($where)->page($current_page.','.$per_page)->select();
        $plan_arr = [];
        if($plan_list&&!empty($plan_list)){
            foreach ($plan_list as $val) {
                $customer_info = $customer_m->where(["id"=>$val["u_id"]])->find();
                $channel_info = $channel_m->where(["id"=>$val["c_id"]])->find();
                $plan_des_list = $plan_des_m->where(["p_id"=>$val["id"]])->select();
                $val["user_loginid"] = "";  //登陆账号
                $val["user_name"] = "";    //商户名称
                $val["channel_name"] = "";    //通道名称
                $val["channel_start_time"] = "";    //任务开始时间
                $val["channel_end_time"] = "";    //任务结束时间
                if($customer_info&&!empty($customer_info)){
                    $val["user_loginid"] = $customer_info["loginid"];  //登陆账号
                    $val["user_name"] = $customer_info["name"];    //商户名称
                }
                if($channel_info&&!empty($channel_info)){
                    $val["channel_name"] = $channel_info["name"];  //通道名称
                }
                if($plan_des_list&&!empty($plan_des_list)){
                    $val["channel_start_time"] = date("Y-m-d H:i:s",$plan_des_list[0]["s_time"]);    //任务开始时间
                    $val["channel_end_time"] = date("Y-m-d H:i:s",$plan_des_list[count($plan_des_list)-1]["s_time"]);    //任务结束时间
                }
                switch ($val["status"]) {
                    case 1:
                        $val["status_name"] = "计划完成";
                        break;
                    case 2:
                        $val["status_name"] = "用户终止计划";
                        break;
                    case 3:
                        $val["status_name"] = "执行中";
                        break;
                    case 4:
                        $val["status_name"] = "待执行";
                        break;
                    case 5:
                        $val["status_name"] = "计划中断";
                        break;
                    default:
                        $val["status_name"] = "";
                        break;
                }
                $plan_arr[] = $val;
            }
        }
        $this->assign("page",$page->show());
        $this->assign("plan_list",$plan_arr);
        $this->assign("search_key",$search_key);
        $this->display();
    }
    public function info(){
        $p_id = I("id");
        if(!$p_id){
            $this->error("参数错误",U("plan/index"));die();
        }
        $plan_des_list = M("plan_des")->where(["p_id"=>$p_id])->select();
        if(!$plan_des_list||empty($plan_des_list)){
            $this->error("计划不存在",U("plan/index"));die();
        }
        $plan_des_arr = [];
        if($plan_des_list&&!empty($plan_des_list)){
            foreach ($plan_des_list as $val) {
                $val["type_name"] = ""; 
                if($val["type"]==1){
                    $val["type_name"] = "消费"; 
                }
                if($val["type"]==2){
                    $val["type_name"] = "还款"; 
                }
                switch ($val["order_state"]) {
                    case 1:
                        $val["status_name"] = "成功";
                        break;
                    case 2:
                        $val["status_name"] = "待执行";
                        break;
                    case 3:
                        $val["status_name"] = "执行中";
                        break;
                    case 4:
                        $val["status_name"] = "失败";
                        break;
                    default:
                        $val["status_name"] = "";
                        break;
                }
                $plan_des_arr[] = $val;
            }
        }
        $this->assign("plan_des_list",$plan_des_arr);
        $this->assign("bd_url",HTTP_HOST."/index/plan/repOrder");
        $this->display();
    }
}
