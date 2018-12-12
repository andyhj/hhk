<?php
namespace Home\Model;

class OrderModel extends \Common\Model\OrderModel{
    public function addOrder($data){
        if(!$data||empty($data)){
            return false;
        }
        $ip = get_client_ip();
        $number = get_rand_str(5,['1','2','3','4','5','6','7','8','9']);
        $time = time();
        $order_data = $data["order"];
        $order_product = $data["order_product"];
        
        $order_data["order_number"] = $number.$time;
        $order_data["ip"] = $ip;
        $order_data["add_date"] = $time;
        $order_data["edit_date"] = $time;
        
        $order_id = $this->add($order_data);
        if($order_id){
            if(!empty($order_product)){
                $obj_order_product = M("order_product");
                $obj_product = M("product");
                $obj_product_sku = M("product_sku");
                foreach ($order_product as $value) {
                    $value["order_id"] = $order_id;
                    $return_staus = $obj_order_product->add($value);
                    if($return_staus){ //減庫存
                        $obj_product->where(["id"=>$value["product_id"]])->setDec('stock',$value["quantity"]);
                        if(isset($value["sku_id"])&&$value["sku_id"]){
                            $obj_product_sku->where(["id"=>$value["sku_id"]])->setDec('stock',$value["quantity"]);
                        }
                    }
                }
            }
            $order_history["order_id"] = $order_id;
            $order_history["order_status"] = $order_data["status"];
            $order_history["comment"] = "用戶下單";
            $order_history["actor_user"] = "系統";
            $this->addHistory($order_history);
            return $order_id;
        }
        return false;
    }
}
