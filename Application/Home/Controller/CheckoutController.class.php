<?php
namespace Home\Controller;

class CheckoutController extends InitController{
    public function submit(){
        $data = $this->getRawBody();//获取参数
        //{"shipping_name":"andy","shipping_postcode":"518000","shipping_phone":"138000000000","shipping_email":"952787667@qq.com","shipping_address":"廣東省深圳市南山區科技園南區","remark":"盡快發貨","product":[{"id":2,"quantity":5},{"id":3,"quantity":8,"sku_id":5}]}
//        $order["shipping_name"] = "andy";
//        $order["shipping_postcode"] = "518000";
//        $order["shipping_phone"] = "138000000000";
//        $order["shipping_email"] = "952787667@qq.com";
//        $order["shipping_address"] = "廣東省深圳市南山區科技園南區";
//        $order["product"][] = [
//            "id"=> 2,
//            "quantity"=> 5
//        ];
//        $order["order_product"][] = [
//            "id"=> 3,
//            "quantity"=> 8,
//            "sku_id"=> 5
//        ];
       // print_r($data);die();
        
        if(!$data||empty($data)||empty($data["product"])){
            $this->ajaxReturn(['status'=>305,'info'=>'参数错误']);
        }
        if(!isset($data["shipping_name"])||!$data["shipping_name"]){
            $this->ajaxReturn(['status'=>310,'info'=>'收件人不能為空!']);
        }
        if(!isset($data["shipping_phone"])||!$data["shipping_phone"]){
            $this->ajaxReturn(['status'=>310,'info'=>'收件人電話不能為空!']);
        }
        if(!isset($data["shipping_email"])||!$data["shipping_email"]){
            $this->ajaxReturn(['status'=>310,'info'=>'收件人郵箱不能為空!']);
        }
        if(!isset($data["shipping_address"])||!$data["shipping_address"]){
            $this->ajaxReturn(['status'=>310,'info'=>'收件人地址不能為空!']);
        }
        $this->verification($data["product"]);
        $obj_product = D("product");
        $obj_order = D("order");
        $order["order_product"] = [];
        $total = 0.00;
        foreach ($data["product"] as $val) {
            $product_info = $obj_product->getOne($val["id"]);
            $product["product_id"] = $val["id"];
            $product["title"] = $product_info["title"];
            $product["quantity"] = $val["quantity"];
            if(isset($val["sku_id"])&&$val["sku_id"]){
                $sku_info = $obj_product->getSkuOne($val["sku_id"]);
                $product["price"] = $sku_info["price"];
                $product["image"] = $sku_info["image"];
                $product["sku_id"] = $sku_info["id"];
                $product["sku_name"] = $sku_info["sku_name"];
            }else{
                $product["price"] = $product_info["price"];
                $product["image"] = $product_info["image"];
            }
            $total += $product["price"];
            $order["order_product"][] = $product;
        }
        $order["order"]["shipping_name"] = $data["shipping_name"];
        $order["order"]["shipping_postcode"] = $data["shipping_postcode"]?$data["shipping_postcode"]:"";
        $order["order"]["shipping_phone"] = $data["shipping_phone"];
        $order["order"]["shipping_email"] = $data["shipping_email"];
        $order["order"]["shipping_address"] = $data["shipping_address"];
        $order["order"]["total"] = $total;
        $order["order"]["status"] = 300;
        $order["order"]["remark"] = $data["remark"]?$data["remark"]:"";
        $return_status = $obj_order->addOrder($order);
        if($return_status){
            $this->ajaxReturn(['status'=>200,'info'=>'下單成功']);
        }else{
            $this->ajaxReturn(['status'=>200,'info'=>'下單失敗']);
        }
    }
    public function verification($data){
        if(empty($data)){
            $this->ajaxReturn(['status'=>305,'info'=>'商品數據錯誤']);
        }
        $obj_product = D("product");
        foreach ($data as $val) {
            $product_info = $obj_product->getOne($val["id"]);
            if(!$product_info){
                $this->ajaxReturn(['status'=>306,'info'=>'商品不存在']);
            }
            if($val["quantity"]>$product_info["stock"]){
                $this->ajaxReturn(['status'=>307,'info'=>'庫存不足']);
            }
            if(isset($val["sku_id"])&&$val["sku_id"]){
                $sku_info = $obj_product->getSkuOne($val["sku_id"]);
                if(!$sku_info){
                    $this->ajaxReturn(['status'=>308,'info'=>'選項商品不存在']);
                }
                if($val["quantity"]>$sku_info["stock"]){
                    $this->ajaxReturn(['status'=>309,'info'=>'庫存不足']);
                }
            }
        }
    }
}
