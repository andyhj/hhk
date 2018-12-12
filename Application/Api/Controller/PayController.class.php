<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Api\Controller;
use Common\Model\PayModel;
use Common\Common\JuheRecharge;
use Common\WxApi\JsApiPay;
use Common\WxApi\WxPayUnifiedOrder;
use Common\WxApi\WxPayApi;
use Common\WxApi\class_weixin_adv;
use Common\Common\Sockets;
use Common\JuBaoPay\jubaopay;
class PayController extends InitController {
    
    public function jubaopay(){
        $user_id = $this->user_id;
        $item_id = I("item_id",0);
        $type = I("type",1);
        $model_order = D("Order");
        if(!$item_id){
            echo '<script>alert("商品id不能等于0");location="'.$this->http . $_SERVER['HTTP_HOST'].'";</script>';
            die();
        }
        $shop_where = ["id"=>$item_id];
        $game_shop = $model_order->getGameShopOne($shop_where);
        if(!$game_shop){
            echo '<script>alert("商品不存在");location="'.$this->http . $_SERVER['HTTP_HOST'].'";</script>';
            die();
        }
        $amount = $game_shop['price'];
        $ratio = $game_shop['coin']+$game_shop['bonus'];
        $pay_number = $user_id.time().get_rand_str(3, ['1', '2', '3', '4', '5', '6', '7', '8', '9']);
        $pay_data["u_id"] = $user_id;
        $pay_data["pay_number"] = $pay_number;
        $pay_data["pay_type"] = 1;
        $pay_data["amount"] = $amount;
        $pay_data["item_id"] = $item_id;
        $pay_data["ratio"] = $ratio;
        $pay_data["status"] = 100;
        $pay_data["add_date"] = time();
        $pay_data["type"] = $type;
        $return_id = $model_order->addPayOrder($pay_data);
        if(!$return_id){
            echo '<script>alert("生成支付单失败");location="'.$this->http . $_SERVER['HTTP_HOST'].'";</script>';
            die();
        }
        require_once APP_ROOT .'Application/Common/Concrete/jubaopay/jubaopay.php';
        $partnerid="18103023331689153245";
        $payerName=$user_id;
        $remark=strip_tags($game_shop["name"]);
        $returnURL=HTTP_HOST;    // 可在商户后台设置
        $callBackURL=HTTP_HOST."/index/callback/jubaopay";  // 可在商户后台设置
        $payMethod="ALL";

        //////////////////////////////////////////////////////////////////////////////////////////////////
         //商户利用支付订单（payid）和商户号（partnerid）进行对账查询
        //echo APP_ROOT .'Application/Common/Concrete/jubaopay/jubaopay.ini';die();
        $jubaopay=new jubaopay(APP_ROOT .'Application/Common/Concrete/jubaopay/jubaopay.ini');
        $jubaopay->setEncrypt("payid", $pay_number);
        $jubaopay->setEncrypt("partnerid", $partnerid);
        $jubaopay->setEncrypt("amount", $amount);
        $jubaopay->setEncrypt("payerName", $payerName);
        $jubaopay->setEncrypt("remark", $remark);
        $jubaopay->setEncrypt("returnURL", $returnURL);
        $jubaopay->setEncrypt("callBackURL", $callBackURL);
        $jubaopay->setEncrypt("openid", $this->user_info["other_id"]);

        //对交易进行加密=$message并签名=$signature
        $jubaopay->interpret();
        $message=$jubaopay->message;
        $signature=$jubaopay->signature;
        //将message和signature一起aPOST到聚宝支付
        echo '<form method="post" action="https://mapi.xunbaobar.com/apiwapsyt.htm" id="payForm">
                <input type="hidden" name="message" value="'.$message.'"/>
                <input type="hidden" name="signature" value="'.$signature.'"/>
                <input type="hidden" name="payMethod" value="'.$payMethod.'"/>
                <input type="hidden" name="tab" value=""/>
        </form>

        <script type="text/javascript">
            document.getElementById("payForm").submit();
        </script>';
    }

        /**
     * 微信支付
     */
    public function wpay(){
        $user_id = $this->user_id;
        $item_id = I("item_id",0);
        $type = I("type",1);
        $return_url = I("return_url");
        $model_order = D("order");
        $amount = 0.00;
        $ratio = 0.00;
        $game_shop='';
        if($type==1){
            if(!$item_id){
//                $json["status"] = 305;
//                $json["info"] = "商品id不能等于0";
//                $this->ajaxReturn($json);
                echo '<script>alert("商品id不能等于0");location="'.$this->http . $_SERVER['HTTP_HOST'].'";</script>';
                die();
            }
            $shop_where = ["id"=>$item_id,"type"=>4];
            $game_shop = $model_order->getGameShopOne($shop_where);
            if(!$game_shop){
//                $json["status"] = 306;
//                $json["info"] = "商品不存在";
//                $this->ajaxReturn($json);
                echo '<script>alert("商品不存在");location="'.$this->http . $_SERVER['HTTP_HOST'].'";</script>';
                die();
            }
            $amount = $game_shop['price'];
            $ratio = $game_shop['coin']+$game_shop['bonus'];
        }elseif($type==2){
            if(!$item_id){
//                $json["status"] = 305;
//                $json["info"] = "活动id不能等于0";
//                $this->ajaxReturn($json);
                echo '<script>alert("活动id不能等于0");location="'.$this->http . $_SERVER['HTTP_HOST'].'";</script>';
                die();
            }
            $model_activity = D("activity");
            $where["start_date"] = array('elt', time());  
            $where["end_date"] = array('gt', time());  
            $where["status"] = 1;
            $where["is_pay"] = 1;
            $where["id"] = $item_id;
            $activity_list = $model_activity->getOne($where);
            if(!$activity_list){
//                $json["status"] = 306;
//                $json["info"] = "活动不存在";
//                $this->ajaxReturn($json);
                echo '<script>alert("活动不存在");location="'.$this->http . $_SERVER['HTTP_HOST'].'";</script>';
                die();
            }
            $order_count = $model_order->getCount(["u_id"=>$user_id,"type"=>2,"item_id"=>$item_id,"status"=>200]);
            if($activity_list["num"]>0&&$order_count>=$activity_list["num"]){
//                $json["status"] = 307;
//                $json["info"] = "此活动只能支付".$activity_list["num"]."次";
//                $this->ajaxReturn($json);
                echo '<script>alert("此活动只能支付'.$activity_list["num"].'次");location="'.$this->http . $_SERVER['HTTP_HOST'].'";</script>';
                die();
            }
            $amount = $activity_list['amount'];
            $ratio = $activity_list['ratio'];
        }elseif($type==4){
            if(!$item_id){
//                $json["status"] = 305;
//                $json["info"] = "活动id不能等于0";
//                $this->ajaxReturn($json);
                echo '<script>alert("比赛id不能等于0");</script>';
                die();
            }
            $custom = D("Custom");
            $c_where["id"] = $item_id;
            $c_where["is_del"] = 0;
            $c_where["audit_status"] = 1;
            $custom_info = $custom->getOne($c_where);
            if(!$custom_info){
                echo '<script>alert("比赛不存在")</script>';die();
            }
            $amount = $custom_info['tickets'];
            $ratio = 0;
        }else{
            $json["status"] = 309;
            $json["info"] = "类型错误";
            $this->ajaxReturn($json);
        }
        $pay_number = $user_id.time().get_rand_str(3, ['1', '2', '3', '4', '5', '6', '7', '8', '9']);
        $pay_data["u_id"] = $user_id;
        $pay_data["pay_number"] = $pay_number;
        $pay_data["pay_type"] = 1;
        $pay_data["amount"] = $amount;
        $pay_data["item_id"] = $item_id;
        $pay_data["ratio"] = $ratio;
        $pay_data["status"] = 100;
        $pay_data["add_date"] = time();
        $pay_data["type"] = $type;
        $return_id = $model_order->addPayOrder($pay_data);
        if(!$return_id){
//            $json["status"] = 307;
//            $json["info"] = "生成支付单失败";
//            $this->ajaxReturn($json);
            echo '<script>alert("生成支付单失败");location="'.$this->http . $_SERVER['HTTP_HOST'].'";</script>';
            die();
        }
        require_once APP_ROOT ."Application/Common/Concrete/wxapi/example/WxPay.JsApiPay.php";
        $user_info = $this->user_info;
        //①、获取用户openid
        $tools = new JsApiPay();
        $openId = $user_info["other_id"];
        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody("深圳开心娱乐科技有限公司");
        $input->SetAttach("开心逗棋牌");
        $input->SetOut_trade_no($pay_number);
        $input->SetTotal_fee($amount*100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag(strip_tags($game_shop["name"]));
        $input->SetNotify_url('http://'.$_SERVER['HTTP_HOST']."/index/callback/wxpay");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        $model_pay = new PayModel();
        if($return_url){
            $url = urldecode($return_url);
        }else{
            $url = $this->http.$_SERVER['HTTP_HOST']."/index/index.html";
        }
        
        $html = $model_pay->createPayHtml($jsApiParameters, $url);
        echo $html;die();
    }
    /**
     * 微信退款
     */
    public function wrefund(){
        $user_id = $this->user_id;
        $order_number = I("order_number","");
        $amount = I("amount",0);
        $model_order = D("Order");
        if(!$order_number||!$amount){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $order_info = $model_order->getOneByOrderNumber($order_number);
        if(!$order_info||$order_info["status"]!=200){
            $json["status"] = 306;
            $json["info"] = "订单不存在";
            $this->ajaxReturn($json);
        }
        if($amount>$order_info["amount"]){
            add_log("wrefund.log", "pay", "退款金额大于实际金额；退款金额：".$amount."；实际金额：".$order_info["amount"]);
            $json["status"] = 307;
            $json["info"] = "退款金额大于实际金额";
            $this->ajaxReturn($json);
        }
        $r_number = 'T'.$user_id.time().get_rand_str(3, ['1', '2', '3', '4', '5', '6', '7', '8', '9']);
        $r_data["status"] = 300;
        $r_data["return_number"] = $r_number;
        $r_data["refund_fee"] = $amount;
        $r_data["return_date"] = time();
        $return_status = $model_order->updOrder(["id"=>$order_info["id"]],$r_data);
        if(!$return_status){
            $json["status"] = 308;
            $json["info"] = "修改订单失败";
            $this->ajaxReturn($json);
        }
        require_once APP_ROOT ."Application/Common/Concrete/wxapi/example/WxPay.JsApiPay.php";
        $input = new \Common\WxApi\WxPayRefund();
        $input->SetOut_trade_no($order_info['pay_number']);         //自己的订单号  
        $input->SetOut_refund_no($r_number);         //退款单号  
        $input->SetTotal_fee($order_info["amount"]*100);         //订单标价金额，单位为分  
        $input->SetRefund_fee($amount*100);            //退款总金额，订单总金额，单位为分，只能为整数  
        
        //$input->SetNotify_url('http://'.$_SERVER['HTTP_HOST']."/index/callback/wrefund");
        $result = WxPayApi::refund($input);
        if(($result['return_code']=='SUCCESS') && ($result['result_code']=='SUCCESS')){  
            //退款成功  
            $return_number = $result["out_refund_no"];
            $refund_id = $result["refund_id"];
            $order_info = $model_order->getOneByReturnNumber($return_number);
            if($order_info){
                $order_upd_data["refund_id"] = $refund_id;
                $order_upd_data["status"] = 400;
                $order_where["return_number"] = $return_number;
                $return_status = $model_order->updOrder($order_where,$order_upd_data);
                if($return_status){
                    //推送微信消息
                    $m_user = D("User");
                    header("Content-Type:text/html; charset=utf-8");
                    require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
                    $user_info = $m_user->getUserOne($order_info["u_id"]);
                    $weixin = new class_weixin_adv();
                    $msg_data["touser"] = $user_info["other_id"];
                    $msg_data["template_id"] = "XEs5AvWT90jTkybiThRYKg4taOONk9zIPkQZGv3h39g";
                    $msg_data["url"] = 'https://'.$_SERVER['HTTP_HOST'].'/index/user/orderinfo.html?order_type=1&order_number='.$order_info["order_number"];
                    $msg_data["data"] = array(
                        "first"=>array(
                            "value"=>"退款成功",
                            "color"=>""
                        ),
                        "keyword1"=>array(
                            "value"=> $return_number,
                            "color"=>""
                        ),
                        "keyword2"=>array(
                            "value"=> $order_info["refund_fee"],
                            "color"=>""
                        ),
                        "remark"=>array(
                            "value"=>"祝您游戏愉快。",
                            "color"=>""
                        )
                    );
                    $return_status = $weixin->send_user_message($msg_data);
                    add_log("callback.log", "pay", "公众号退款消息推送状态：". var_export($return_status, true));
                    $json["status"] = 200;
                    $json["info"] = "退款成功";
                    $this->ajaxReturn($json);
                }else{
                    $json["status"] = 308;
                    $json["info"] = "更新订单状态失败";
                    $this->ajaxReturn($json);
                }
            }else{
                $json["status"] = 308;
                $json["info"] = "订单不存在";
                $this->ajaxReturn($json);
            } 
        }else if(($result['return_code']=='FAIL') || ($result['result_code']=='FAIL')){  
            $json["status"] = 310;
            $json["info"] = $result['err_code_des'];
            $this->ajaxReturn($json);
        }else{  
            $json["status"] = 311;
            $json["info"] = $result['err_code_des'];
            $this->ajaxReturn($json);
        } 
        $json["status"] = 309;
        $json["info"] = "退款失败";
        $this->ajaxReturn($json);
    }

    /**
     * 话费充值
     */
    public function phonePay(){
        $user_id = $this->user_id;
        $item_id = I("item_id",0);
        $model_order = D("Order");
        if(!$item_id){
            $json["status"] = 305;
            $json["info"] = "商品id不能等于0";
            $this->ajaxReturn($json);
        }
        $game_user = D("User");
        $user_where["uid"] = $user_id;
        $game_user_info = $game_user->getGameUserOne($user_where);
        $item_where = ["id"=>$item_id,"type"=>2,"status"=>1,"item_type"=>1];
        $game_item = $model_order->getGameItemOne($item_where);
        if(!$game_item){
            $json["status"] = 306;
            $json["info"] = "商品不存在";
            $this->ajaxReturn($json);
        }
        if($game_item["stock"]<1){
            $json["status"] = 307;
            $json["info"] = "库存不足";
            $this->ajaxReturn($json);
        }
        if($game_item["cost_type"]!=4){
            $json["status"] = 308;
            $json["info"] = "此商品不能用兑换券兑换";
            $this->ajaxReturn($json);
        }
        if($game_user_info["awardnum"]<$game_item["cost"]){
            $json["status"] = 309;
            $json["info"] = "兑换券不足";
            $this->ajaxReturn($json);
        }
        $game_address = $model_order->getGameUserAddress($user_id);
        if(!$game_address||!$game_address["phone"]){
            $json["status"] = 310;
            $json["info"] = "请填写收货信息";
            $this->ajaxReturn($json);
        }
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/weixin.api.php";
        $appkey = '7baf94b5d3250af823d88bb3fe1081e1'; //从聚合申请的话费充值appkey
        $openid = C('JH_CONFIG.OPENID'); //注册聚合账号就会分配的openid，在个人中心可以查看
        $juhe_recharge = new JuheRecharge($appkey,$openid);
        $weixin = new class_weixin_adv();
        $phone = $game_address["phone"];  //电话
        $amount = $game_item["amount"];   //充值额度
        $telCheckRes = $juhe_recharge->telcheck($phone,$amount);
        $user_info = $this->user_info;
        if($telCheckRes){
            $telQueryRes =$juhe_recharge->telquery($phone,$amount); #可以选择的面额5、10、20、30、50、100、300
            if($telQueryRes['error_code'] == '0'){
                //正常获取到话费商品信息
                $orderid = $user_info["id"]. time(); //自己定义一个订单号，需要保证唯一
                $telRechargeRes = $juhe_recharge->telcz($phone,$amount,$orderid); #可以选择的面额5、10、20、30、50、100、300
                if($telRechargeRes['error_code'] =='0'){
                    $msg_data = $game_user->wxExchangeSucceedMsg($user_id,"您好，您的话费已兑换成功，请注意查收",strip_tags($game_item["name"]),$phone);
                    $return_status = $weixin->send_user_message($msg_data);
                    add_log("juhe_pay.log", "pay", "微信消息状态：". var_export($return_status,true));
                    add_log("juhe_pay.log", "pay", "话费充值成功：". var_export($telRechargeRes,true));
                    $r_data["order_number"] = $telRechargeRes['result']['uorderid'];
                    $r_data["ordercash"] = $telRechargeRes['result']['ordercash'];
                    //提交话费充值成功，可以根据实际需求改写以下内容
                    $json["status"] = 200;
                    $json["info"] = "充值成功";
                    $json["data"] = $r_data;
                    $this->ajaxReturn($json);
                }else{
                    $msg_data = $game_user->wxExchangeFallMsg($user_id,"您好，您的话费兑换失败",$telRechargeRes["reason"]);
                    $return_status = $weixin->send_user_message($msg_data);
                    add_log("juhe_pay.log", "pay", "微信消息状态：". var_export($return_status,true));
                    add_log("juhe_pay.log", "pay", "话费充值失败：". var_export($telRechargeRes,true));
                    $json["status"] = 313;
                    $json["info"] = "充值失败";
                    $this->ajaxReturn($json);
                }
            }else{
                $msg_data = $game_user->wxExchangeFallMsg($user_id,"您好，您的话费兑换失败",$telQueryRes["reason"]);
                $return_status = $weixin->send_user_message($msg_data);
                add_log("juhe_pay.log", "pay", "微信消息状态：". var_export($return_status,true));
                add_log("juhe_pay.log", "pay", "根据手机号码以及面额查询商品信息失败：". var_export($telQueryRes,true));
                //查询失败，可能维护、不支持面额等情况
                $json["status"] = 312;
                $json["info"] = $telQueryRes['reason'];
                $this->ajaxReturn($json);
            }
        }else{
            $msg_data = $game_user->wxExchangeFallMsg($user_id,"您好，您的话费兑换失败","该面额暂不支持充值");
            $return_status = $weixin->send_user_message($msg_data);
            add_log("juhe_pay.log", "pay", "微信消息状态：". var_export($return_status,true));
            add_log("juhe_pay.log", "pay", "检测手机号码以及面额不可以充值，手机：". $phone."，面额：".$amount);
            //暂不支持充值，以下可以根据实际需求修改
            $json["status"] = 311;
            $json["info"] = "该面额暂不支持充值";
            $this->ajaxReturn($json);
        }
    }
    
    /**
     * 通用礼品卡兑换
     */
    public function buyCode(){
        $user_id = $this->user_id;
        $item_id = I("item_id",0);
        $model_order = D("Order");
        if(!$item_id){
            $json["status"] = 305;
            $json["info"] = "商品id不能等于0";
            $this->ajaxReturn($json);
        }
        $game_user = D("User");
        $user_where["uid"] = $user_id;
        $game_user_info = $game_user->getGameUserOne($user_where);
        $item_where = ["id"=>$item_id,"type"=>2,"status"=>1,"item_type"=>2];
        $game_item = $model_order->getGameItemOne($item_where);
        if(!$game_item){
            $json["status"] = 306;
            $json["info"] = "商品不存在";
            $this->ajaxReturn($json);
        }
        if($game_item["stock"]<1){
            $json["status"] = 307;
            $json["info"] = "库存不足";
            $this->ajaxReturn($json);
        }
        if($game_item["cost_type"]!=4){
            $json["status"] = 308;
            $json["info"] = "此商品不能用兑换券兑换";
            $this->ajaxReturn($json);
        }
        if($game_user_info["awardnum"]<$game_item["cost"]){
            $json["status"] = 309;
            $json["info"] = "兑换券不足";
            $this->ajaxReturn($json);
        }
        $game_address = $model_order->getGameUserAddress($user_id);
        if(!$game_address||!$game_address["phone"]){
            $json["status"] = 310;
            $json["info"] = "请填写收货信息";
            $this->ajaxReturn($json);
        }
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/weixin.api.php";
        $appkey = '4ec21cc5eab22e4a7dc6d64275bc5126'; //从聚合申请的通用礼品卡appkey
        $openid = C('JH_CONFIG.OPENID'); //注册聚合账号就会分配的openid，在个人中心可以查看
        $juhe_recharge = new JuheRecharge($appkey,$openid);
        $weixin = new class_weixin_adv();
        $product_id = $game_item["product_id"];   //礼品卡商品id
        $user_info = $this->user_info;
        //正常获取到话费商品信息
        $orderid = $user_info["id"]. time(); //自己定义一个订单号，需要保证唯一
        $telRechargeRes = $juhe_recharge->cartBuy($product_id,$orderid); 
        if($telRechargeRes['error_code'] =='0'){
            $msg_data = $game_user->wxExchangeSucceedMsg($user_id,"您好，您的礼品卡发货成功，请注意查收",strip_tags($game_item["name"]),$user_id);
            $return_status = $weixin->send_user_message($msg_data);
            add_log("juhe_pay.log", "pay", "微信消息状态：". var_export($return_status,true));
            add_log("juhe_pay.log", "pay", "礼品卡发货成功：". var_export($telRechargeRes,true));
            $r_data["order_number"] = $telRechargeRes['result']['userOrderId'];
            $r_data["ordercash"] = $telRechargeRes['result']['deduction'];
            //提交话费充值成功，可以根据实际需求改写以下内容
            $json["status"] = 200;
            $json["info"] = "礼品卡发货成功";
            $json["data"] = $r_data;
            $this->ajaxReturn($json);
        }else{
            $msg_data = $game_user->wxExchangeFallMsg($user_id,"您好，您的礼品卡兑换失败",$telRechargeRes["reason"]);
            $return_status = $weixin->send_user_message($msg_data);
            add_log("juhe_pay.log", "pay", "微信消息状态：". var_export($return_status,true));
            add_log("juhe_pay.log", "pay", "礼品卡发货失败：". var_export($telRechargeRes,true));
            $json["status"] = 313;
            $json["info"] = "礼品卡发货失败";
            $this->ajaxReturn($json);
        }
    }
    
    /**
     * 微信app支付
     */
    public function appwpay(){
        $user_id = $this->user_id;
        $item_id = I("post.item_id",0);
        $type = I("post.type",1);
        $model_order = D("order");
        $amount = 0.00;
        $ratio = 0.00;
        $game_shop='';
        if($type==1){
            if(!$item_id){
                $json["status"] = 305;
                $json["info"] = "商品id不能等于0";
                $this->ajaxReturn($json);
            }
            $shop_where = ["id"=>$item_id,"type"=>4];
            $game_shop = $model_order->getGameShopOne($shop_where);
            if(!$game_shop){
                $json["status"] = 306;
                $json["info"] = "商品不存在";
                $this->ajaxReturn($json);
            }
            $amount = $game_shop['price'];
            $ratio = $game_shop['coin']+$game_shop['bonus'];
        }elseif($type==2){
            if(!$item_id){
                $json["status"] = 305;
                $json["info"] = "活动id不能等于0";
                $this->ajaxReturn($json);
            }
            $model_activity = D("activity");
            $where["start_date"] = array('elt', time());  
            $where["end_date"] = array('gt', time());  
            $where["status"] = 1;
            $where["is_pay"] = 1;
            $where["id"] = $item_id;
            $activity_list = $model_activity->getOne($where);
            if(!$activity_list){
                $json["status"] = 306;
                $json["info"] = "活动不存在";
                $this->ajaxReturn($json);
            }
            $order_count = $model_order->getCount(["u_id"=>$user_id,"type"=>2,"item_id"=>$item_id,"status"=>200]);
            if($activity_list["num"]>0&&$order_count>=$activity_list["num"]){
                $json["status"] = 307;
                $json["info"] = "此活动只能支付".$activity_list["num"]."次";
                $this->ajaxReturn($json);
            }
            $amount = $activity_list['amount'];
            $ratio = $activity_list['ratio'];
        }else{
            $json["status"] = 309;
            $json["info"] = "类型错误";
            $this->ajaxReturn($json);
        }
        
        $pay_number = "A".$user_id.time().get_rand_str(3, ['1', '2', '3', '4', '5', '6', '7', '8', '9']);
        $pay_data["u_id"] = $user_id;
        $pay_data["pay_number"] = $pay_number;
        $pay_data["pay_type"] = 1;
        $pay_data["amount"] = $amount;
        $pay_data["item_id"] = $item_id;
        $pay_data["ratio"] = $ratio;
        $pay_data["status"] = 100;
        $pay_data["add_date"] = time();
        $pay_data["type"] = $type;
        $pay_data["channel"] = 2;
        $return_id = $model_order->addPayOrder($pay_data);
        if(!$return_id){
            $json["status"] = 307;
            $json["info"] = "生成支付单失败";
            $this->ajaxReturn($json);
        }
        require_once APP_ROOT ."Application/Common/Concrete/wxapi/example/WxPay.JsApiPay.php";
        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody("深圳开心娱乐科技有限公司");
        $input->SetAttach("开心逗棋牌");
        $input->SetAppid(C('WX_APP_CONFIG.APPID'));//公众账号ID
        $input->SetMch_id(C('WX_APP_CONFIG.MCHID'));//商户号
        $input->SetOut_trade_no($pay_number);
        $input->SetTotal_fee($amount*100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag(strip_tags($game_shop["name"]));
        $input->SetNotify_url('https://'.$_SERVER['HTTP_HOST']."/index/callback/wxpay");
        $input->SetTrade_type("APP");
        $order = WxPayApi::unifiedOrder($input);
        if($order["return_code"]=="SUCCESS"){
            $info['appid'] = $order["appid"];
            $info['partnerid'] = $order["mch_id"];
            $info['prepayid'] = $order["prepay_id"];
            $info['package'] = "Sign=WXPay";
            $info['noncestr'] = WxPayApi::getNonceStr();//生成随机数,下面有生成实例,统一下单接口需要$info['timestamp'] = time();
            $info['timestamp'] = time();
            $info['sign'] = $this->getSign($info,C('WX_APP_CONFIG.KEY'));//生成签名
            
            $json["status"] = 200;
            $json["info"] = "成功";
            $json["data"] = $info;
            $this->ajaxReturn($json);
        }
        $json["status"] = 308;
        $json["info"] = $order["return_msg"];
        $this->ajaxReturn($json);
    }
    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    private function getSign($Obj,$Key) {
        //签名步骤一：按字典序排序参数
        ksort($Obj);
        $buff = "";
        foreach ($Obj as $k => $v) {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        //签名步骤二：在string后加入KEY
        $string = $buff . "&key=" . $Key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
    
    /**
     * 苹果支付
     */
    public function applepay(){
        $user_id = $this->user_id;
        //苹果内购的验证收据
        $receipt_data = I('post.apple_receipt');
        //是否沙盒（1是，0否）
        $sandbox = I('post.sandbox',0);
        // 验证支付状态
        $result=validate_apple_pay($receipt_data,$sandbox);
        if($result['status']){
            add_log("apple_pay.log", "pay", "apple返回数据1：". var_export($result,true));
            // 验证通过 此处可以是修改数据库订单状态等操作
            $applepay_data = $result['data'];
            add_log("apple_pay.log", "pay", "apple返回数据2：". var_export($applepay_data,true));
            if(!$applepay_data||empty($applepay_data)){
                $json["status"] = 400;
                $json["info"] = "重复验证";
                $this->ajaxReturn($json);
            }
//            if($sandbox){
//                $json["status"] = 200;
//                $json["info"] = "验证通过";
//                $this->ajaxReturn($json);
//            }
            $item_id = $applepay_data["product_id"];
            $merchant_number = $applepay_data["transaction_id"];
            $pay_date = strtotime(date("Y-m-d H:i:s",$applepay_data["purchase_date_ms"]/1000));
            $model_order = D("order");
            if(!$item_id){
                $json["status"] = 305;
                $json["info"] = "商品id不能等于0";
                $this->ajaxReturn($json);
            }
            $shop_where = ["id"=>$item_id,"type"=>4];
            $game_shop = $model_order->getGameShopOne($shop_where);
            if(!$game_shop){
                $json["status"] = 306;
                $json["info"] = "商品不存在";
                $this->ajaxReturn($json);
            }
            $where["merchant_number"] = $merchant_number;
            $pay_order_info = $model_order->getPayOrderOneByWhere($where);
            if($pay_order_info){
                $json["status"] = 309;
                $json["info"] = "订单已存在";
                $this->ajaxReturn($json);
            }
            $amount = $game_shop['price'];
            $ratio = $game_shop['coin']+$game_shop['bonus'];
            $pay_number = "A".$user_id.time().get_rand_str(3, ['1', '2', '3', '4', '5', '6', '7', '8', '9']);
            $pay_data["u_id"] = $user_id;
            $pay_data["pay_number"] = $pay_number;
            $pay_data["merchant_number"] = $merchant_number;
            $pay_data["pay_type"] = 4;
            $pay_data["amount"] = $amount;
            $pay_data["item_id"] = $item_id;
            $pay_data["ratio"] = $ratio;
            $pay_data["status"] = 200;
            $pay_data["add_date"] = time();
            $pay_data["type"] = 1;
            $pay_data["channel"] = 2;
            $pay_data["pay_date"] = $pay_date;
            $return_id = $model_order->addPayOrder($pay_data);
            if(!$return_id){
                $json["status"] = 307;
                $json["info"] = "生成支付单失败";
                $this->ajaxReturn($json);
            }
            $order_data["u_id"] = $user_id;
            $order_data["order_number"] = $user_id. time();
            $order_data["pay_number"] = $pay_number;
            $order_data["pay_type"] = 4;
            $order_data["amount"] = $amount;
            $order_data["ratio"] = $ratio;
            $order_data["item_id"] = $item_id;
            $order_data["type"] = 1;
            $order_data["channel"] = 2;
            $order_data["status"] = 200;
            $order_data["add_date"] = time();
            $order_id = $model_order->add($order_data);
            if(!$order_id){
                add_log("callback.log", "pay", $pay_number."添加订单失败");
                $json["status"] = 308;
                $json["info"] = "添加订单失败";
                $this->ajaxReturn($json);
            }
            $extra = array(
                    'add' => array('type' => 'int','size' => 2,'value' => 1),
                    'coin' => array ('type' => 'int','size' => 4,'value' => $ratio),
                    'type' => array('type' => 'int','size' => 2,'value' => 88),
                    'cointype' => array('type' => 'int','size' => 2,'value' => 4)
            );
            $response = Sockets :: call('call_back', 10, 20, $user_id, $extra);
            add_log("callback.log", "pay", "Socket返回数据". var_export($response, true));
            
            $json["status"] = 200;
            $json["info"] = "验证通过";
            $this->ajaxReturn($json);
        }else{
            // 验证不通过
            $json["status"] = 401;
            $json["info"] = $result['message'];
            $this->ajaxReturn($json);
        }
    }
    
    public function payorder(){
        $user_id = $this->user_id;
        $item_id = I("item_id",0);
        $type = I("type",1);
        $model_order = D("Order");
        if(!$item_id){
            $json["status"] = 305;
            $json["info"] = "商品id不能等于0";
            $this->ajaxReturn($json);
        }
        $shop_where = ["id"=>$item_id];
        $game_shop = $model_order->getGameShopOne($shop_where);
        if(!$game_shop){
            $json["status"] = 305;
            $json["info"] = "商品不存在";
            $this->ajaxReturn($json);
        }
        $amount =$game_shop['price'];
        $ratio = $game_shop['coin']+$game_shop['bonus'];
        $pay_number = $user_id.time().get_rand_str(3, ['1', '2', '3', '4', '5', '6', '7', '8', '9']);
        $pay_data["u_id"] = $user_id;
        $pay_data["pay_number"] = $pay_number;
        $pay_data["pay_type"] = 1;
        $pay_data["amount"] = $amount;
        $pay_data["item_id"] = $item_id;
        $pay_data["ratio"] = $ratio;
        $pay_data["status"] = 100;
        $pay_data["add_date"] = time();
        $pay_data["type"] = $type;
        $return_id = $model_order->addPayOrder($pay_data);
        if(!$return_id){
            $json["status"] = 306;
            $json["info"] = "生成支付单失败";
            $this->ajaxReturn($json);
        }
        $pay_data["remark"]=strip_tags($game_shop["name"]);
        $json["status"] = 200;
        $json["info"] = "成功";
        $json["data"] = $pay_data;
        $this->ajaxReturn($json);
    }

}
