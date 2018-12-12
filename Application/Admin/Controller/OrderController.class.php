<?php

namespace Admin\Controller;
class OrderController extends CommonController{
    public function index(){
        $current_page = I('p') ? (int)I('p') : 1;
        $status = I('post.status');
        $per_page = 20;//每页显示条数
        $obj_order = D("order");
        $where=[];
        if($status){
            $where=["status"=>$status];
        }
        $order_list = $obj_order->getList($where,$current_page,$per_page,"add_date DESC");
        $show  = $obj_order->pageShow($per_page,$where);// 分页显示输出
        $order_arr = [];
        if($order_list){
            foreach ($order_list as $val) {
                $val["item_name"] = "";
                if($val["type"]!=4){
                    $game_shop_info = $obj_order->getGameShopOne($val["item_id"]);
                    if($game_shop_info&&!empty($game_shop_info)){
                        $val["item_name"] = strip_tags($game_shop_info["name"]);
                    }
                }
                
                $val["pay_type_name"] = '';
                $val["type_name"] = '';
                if($val["pay_type"]==1){
                    $val["pay_type_name"] = '微信支付';
                }
                if($val["pay_type"]==2){
                    $val["pay_type_name"] = '支付宝支付';
                }
                if($val["pay_type"]==3){
                    $val["pay_type_name"] = '佣金支付';
                }
                if($val["pay_type"]==4){
                    $val["pay_type_name"] = '苹果支付';
                }
                if($val["type"]==1){
                    $val["type_name"] = '商城下单';
                }
                if($val["type"]==2){
                    $val["type_name"] = '活动下单';
                }
                if($val["type"]==3){
                    $val["type_name"] = '佣金兑换商城';
                }
                if($val["type"]==4){
                    $val["type_name"] = '比赛报名费';
                }
                $order_arr[] = $val;
            }
        }
        $status_list = $obj_order->getStatus();
        $this->assign('status',$status);
        $this->assign('statusList',$status_list);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('orderList',$order_arr);
        $this->display();
    }
    
    public function info(){
        $order_number = I('order_number') ? I('order_number') : 0;
        if(!$order_number){
            $this->error("参数错误");
            exit();
        }
        $obj_order = D("order");
        $order_info = $obj_order->getOneByOrderNumber($order_number);
        if($order_info){
            $order_info["status_name"] = $obj_order->getStatusText($order_info["status"]);
            $order_info["add_date"] = date("Y-m-d H:i:s",$order_info["add_date"]);
            $order_info["edit_date"] = date("Y-m-d H:i:s",$order_info["edit_date"]);
            $order_product = $obj_order->getOrderProduct($order_info["id"]);
            $order_history = $obj_order->getOrderHistory($order_info["id"]);
            $order_history_arr = [];
            if($order_history){
                foreach ($order_history as $value) {
                    $value["order_status"] = $obj_order->getStatusText($value["order_status"]);
                    $value["add_date"] = date("Y-m-d H:i:s",$value["add_date"]);
                    $order_history_arr[] = $value;
                }
            }
            $status_list = $obj_order->getStatus();
            $order_info["order_product"] = $order_product;
            $order_info["order_history"] = $order_history_arr;
            $this->assign('orderInfo',$order_info);
            $this->assign('statusList',$status_list);
            $this->display();
        }else{
            $this->error("没有数据");
            exit();
        }
    }

    /**
     * 订单列表接口
     */
    public function lists(){
        $current_page = I('page') ? (int)I('page') : 1;
        $per_page = 20;//每页显示条数
        $obj_order = D("order");
        $order_list = $obj_order->getList([],$current_page,$per_page,"add_date DESC");
        if($order_list){
            $data["page"] = $current_page;
            $data["count"] = $obj_order->getCount();
            $data["list"] = $order_list;
            $this->ajaxReturn(['status'=>200,'info'=>'获取成功','data'=>$data]);
        }else{
            $this->ajaxReturn(['status'=>305,'info'=>'没有数据']);
        }
    }
    /**
     * 订单详情
     */
    public function detail(){
        $order_number = I('order_number') ? I('order_number') : 0;
        if(!$order_number){
            $this->ajaxReturn(['status'=>305,'info'=>'参数错误']);
        }
        $obj_order = D("order");
        $order_info = $obj_order->getOneByOrderNumber($order_number);
        if($order_info){
            $order_product = $obj_order->getOrderProduct($order_info["id"]);
            $order_history = $obj_order->getOrderHistory($order_info["id"]);
            $order_info["order_product"] = $order_product;
            $order_info["order_history"] = $order_history;
            $this->ajaxReturn(['status'=>200,'info'=>'获取成功','data'=>$order_info]);
        }else{
            $this->ajaxReturn(['status'=>306,'info'=>'没有数据']);
        }
    }
    /**
     * 修改订单状态
     */
    public function updStatus(){
        $data = $this->getRawBody();//获取参数
        $order_number = I("post.order_number");
        $status_id = I("post.status_id");
        if(!$order_number||!$status_id){
            $this->ajaxReturn(['status'=>305,'info'=>'参数错误']);
        }
        $obj_order = D("order");
        $order_info = $obj_order->getOneByOrderNumber($order_number);
        if(!$order_info){
            $this->ajaxReturn(['status'=>308,'info'=>'订单不存在']);
        }
        $status_name = $obj_order->getStatusText($status_id);
        if(!$status_name){
            $this->ajaxReturn(['status'=>306,'info'=>'状态码不正确']);
        }
        $user_info = $this->getUserInfo();
        $order_data["order"]["status"] = $status_id;
        
        $order_data["history"]["order_id"] = $order_info["id"];
        $order_data["history"]["order_status"] = $status_id;
        $order_data["history"]["comment"] = "管理员 ".$user_info["name"]." 修改订单状态为 ".$status_name;
        $order_data["history"]["actor_user"] = $user_info["name"];
        $return_status = $obj_order->updateOrder($order_info["id"],$order_data);
        if($return_status){
            $this->ajaxReturn(['status'=>200,'info'=>'修改成功']);
        }else{
            $this->ajaxReturn(['status'=>307,'info'=>'修改失败']);
        }
    }
}
