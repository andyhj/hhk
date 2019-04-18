<?php
namespace Home\Controller;

/**
 * 银行卡管理类
 *
 * @author Administrator
 */
use Common\Common\WxH5Login;
use Common\HeliPay\Heli;
class CardController extends InitController {
    private $user_info;
    private $c_code;
    private $card_m;
    public function __construct() {
        header("Content-type: text/html; charset=utf-8"); 
        parent::__construct();
        $recommend = delTrim(I("rec", session("rec"))); //推荐人
        $this->user_info = $this->getUserInfo();
        $wxh5login = new WxH5Login();
        if (!$this->user_info) {
            $return_status = $wxh5login->wxLogin($recommend);
            if ($return_status === 200) {
                $this->user_info = $this->getUserInfo();
            }
            if ($return_status === 111) {
                $this->error("推荐用户不存在");die();
            }
            if ($return_status === 112) {
                $this->error("登陆失败");die();
            }
            $url = HSQ_HOST. '/mobile/perfect_info/registered';
            if ($return_status === 113) {
                header('Location: ' . $url);
                die();
            }
        }
        $this->c_code = I("c_code");  //通道编码
        if(!$this->c_code){
            $this->error("参数错误");die();
        }
        $table_name ="bank_card_".$this->c_code;
        $isTable = M()->query('SHOW TABLES LIKE "'. C("DB_PREFIX").$table_name.'"');
        if(!empty($isTable) ){
            $this->card_m = M($table_name);
        }else{
            $this->error("非法请求");die();
        }
        if(!$this->user_info["idcard"]){
            $url = HSQ_HOST.'/mobile/info/index.html';
            $this->error("请实名认证", $url);die();
        }
        $this->assign('c_code', $this->c_code);
        $this->assign('userInfo', $this->user_info);
        $this->assign('wx_share_url', $this->http . $_SERVER['HTTP_HOST'] . '/' . $this->user_info["id"] . '-0-0-0-0.html');
    }
    public function index(){
        $u_id = $this->user_info["id"];
        $bank_card_list = $this->card_m->where(["uid"=>$u_id,"success"=>1])->select();
        $this->assign('bank_card_list', $bank_card_list);
        $this->assign('add_card_url', U("index/card/addCard",["c_code"=>$this->c_code]));
        $this->assign('del_card_url', U("index/card/delCard",["c_code"=>$this->c_code]));
        $this->display();
    }
    /**
     * 添加银行卡
     */
    public function addCard(){
        $db_config = C("DB_CONFIG2");
        $u_id = $this->user_info["id"];
        $bank_id = $this->user_info["bankid"];
        $url = HSQ_HOST.'/mobile/info/index.html';
        if(!$bank_id){
            $this->error("请完善个人资料", $url);die();
        }
        $customer_bankaccount_m = M("customer_bankaccount",$db_config["DB_PREFIX"],$db_config);
        $customer_bankaccount_info = $customer_bankaccount_m->where(["id"=>$bank_id,"userId"=>$u_id])->find();
        if(!$customer_bankaccount_info){            
            $this->error("请完善个人资料", $url);die();
        }
        $this->assign('account_name', $customer_bankaccount_info["accountname"]);
        $this->assign('get_code_url', U("index/card/sendCode"));
        $this->assign('add_card_url', U("index/card/submitCard"));
        $this->assign('card_url', U("index/card/index",["c_code"=>$this->c_code]));
        $this->display();
    }
    /**
     * 鉴权绑卡
     */
    public function submitCard(){
        $db_config = C("DB_CONFIG2");
        $u_id = $this->user_info["id"];
        $bank_id = $this->user_info["bankid"];
        $card_no = I("post.card_no");  //银行卡号
        $card_cvv = I("post.card_cvv");  //cvv
        $validity_date = I("post.validity_date"); //有效期
        $bank_name = I("post.bank_name");       //银行名称
        $bill = I("post.bill");             //账单日
        $repayment = I("post.repayment");  //还款日
        $phone = I("post.phone");           //预留手机号
        $code = I("post.code");             //验证码
        $session_name = "submitCard_".$u_id;
//        session($session_name,null);
        if(session($session_name)){
            $json["status"] = 305;
            $json["info"] = "正在提交...";
            $this->returnJson($json);
        }
        session($session_name,1);
        if(!$card_no||!$card_cvv||!$validity_date||!$bank_name||!$bill||!$repayment||!$phone||!$code){
            $json["status"] = 306;
            $json["info"] = "参数错误";
            $this->returnJson($json, $session_name);
        }
        //手机号码校验
        if (!preg_match('/^1[345678]\d{9}$/', $phone)) {
            $json["status"] = 307;
            $json["info"] = "手机号码格式错误";
            $this->returnJson($json, $session_name);
        }
        if(strlen($card_cvv)!=3){
            $json["status"] = 307;
            $json["info"] = "cvv为3位数";
            $this->returnJson($json, $session_name);
        }
        if(strlen($validity_date)!=4){
            $json["status"] = 307;
            $json["info"] = "有效期为4位数";
            $this->returnJson($json, $session_name);
        }
        if($bill<1||$bill>31){
            $json["status"] = 307;
            $json["info"] = "请填写有效的账单日";
            $this->returnJson($json, $session_name);
        }
        if($repayment<1||$repayment>31){
            $json["status"] = 307;
            $json["info"] = "请填写有效的还款日";
            $this->returnJson($json, $session_name);
        }
        $customer_bankaccount_m = M("customer_bankaccount",$db_config["DB_PREFIX"],$db_config);
        $customer_bankaccount_info = $customer_bankaccount_m->where(["id"=>$bank_id,"userId"=>$u_id])->find();
        if(!$customer_bankaccount_info){
            $json["status"] = 308;
            $json["info"] = "请完善个人资料";
            $this->returnJson($json, $session_name);
        }
        $card_info = $this->card_m->where(["card_no"=>$card_no,"u_id"=>$u_id])->find();
        if(!$card_info){
            $json["status"] = 309;
            $json["info"] = "请先获取短信验证码";
            $this->returnJson($json, $session_name);
        }
        if($card_info["success"]){
            $json["status"] = 200;
            $json["info"] = "银行卡已经绑定成功";
            $this->returnJson($json, $session_name);
        }
        switch ($this->c_code) {
            case "hlb":
                require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/helipay/HeliPay.php";
                $helipay = new Heli();
                $card_data = array(
                    "userId" => $u_id,
                    "orderId" => $card_info["order_id"],
                    "payerName" => $card_info["user_name"],
                    "idCardNo" => $card_info["id_card"],
                    "cardNo" => $card_info["card_no"],
                    "year" => substr($card_info["validity_date"], 2, 4),
                    "month" => substr($card_info["validity_date"], 0, 2),
                    "cvv2" => $card_info["card_cvv"],
                    "phone" => $card_info["phone"],
                    "code" => $code
                );
                $re = $helipay->bindingCard($card_data);
                if (!$re) {
                    $json["status"] = 310;
                    $json["info"] = "绑卡失败";
                    $this->returnJson($json, $session_name);
                }
                if ($re['rt2_retCode'] != '0000') {
                    $json["status"] = 310;
                    $json["info"] = "绑卡失败(".$re['rt3_retMsg'].")";
                    $this->returnJson($json, $session_name);
                }else{
                    if($re['rt7_bindStatus']=="SUCCESS"){
                        $r_s = $this->card_m->where(["card_no"=>$card_no])->save(["success"=>1,"bind_id"=>$re['rt10_bindId']]);
                        if($r_s){
                            $json["status"] = 200;
                            $json["info"] = "银行卡已经绑定成功";
                            $this->returnJson($json, $session_name);
                        }else{
                            $json["status"] = 311;
                            $json["info"] = "绑卡成功，更新数据失败，请联系客服";
                            $this->returnJson($json, $session_name);
                        }
                    }
                    $json["status"] = 310;
                    $json["info"] = "绑卡失败(".$re['rt3_retMsg'].")";
                    $this->returnJson($json, $session_name);
                }
                break;

            default:
                $json["status"] = 311;
                $json["info"] = "非法请求";
                $this->returnJson($json, $session_name);
                break;
        }
    }
    /**
     * 发送鉴权绑卡短信
     */
    public function sendCode(){
        $db_config = C("DB_CONFIG2");
        $u_id = $this->user_info["id"];
        $bank_id = $this->user_info["bankid"];
        $card_no = I("post.card_no");  //银行卡号
        $card_cvv = I("post.card_cvv");  //cvv
        $validity_date = I("post.validity_date"); //有效期
        $bank_name = I("post.bank_name");       //银行名称
        $bill = I("post.bill");             //账单日
        $repayment = I("post.repayment");  //还款日
        $phone = I("post.phone");           //预留手机号
        $session_name = "submitCard_".$u_id;
//        session($session_name,null);
        if(session($session_name)){
            $json["status"] = 305;
            $json["info"] = "正在提交...";
            $this->returnJson($json);
        }
        session($session_name,1);
        if(!$card_no||!$card_cvv||!$validity_date||!$bank_name||!$bill||!$repayment||!$phone){
            $json["status"] = 306;
            $json["info"] = "参数错误";
            $this->returnJson($json, $session_name);
        }
        //手机号码校验
        if (!preg_match('/^1[345678]\d{9}$/', $phone)) {
            $json["status"] = 307;
            $json["info"] = "手机号码格式错误";
            $this->returnJson($json, $session_name);
        }
        if(strlen($card_cvv)!=3){
            $json["status"] = 307;
            $json["info"] = "cvv为3位数";
            $this->returnJson($json, $session_name);
        }
        if(strlen($validity_date)!=4){
            $json["status"] = 307;
            $json["info"] = "有效期为4位数";
            $this->returnJson($json, $session_name);
        }
        if($bill<1||$bill>31){
            $json["status"] = 307;
            $json["info"] = "请填写有效的账单日";
            $this->returnJson($json, $session_name);
        }
        if($repayment<1||$repayment>31){
            $json["status"] = 307;
            $json["info"] = "请填写有效的还款日";
            $this->returnJson($json, $session_name);
        }
        $customer_bankaccount_m = M("customer_bankaccount",$db_config["DB_PREFIX"],$db_config);
        $customer_bankaccount_info = $customer_bankaccount_m->where(["id"=>$bank_id,"userId"=>$u_id])->find();
        if(!$customer_bankaccount_info){
            $json["status"] = 308;
            $json["info"] = "请完善个人资料";
            $this->returnJson($json, $session_name);
        }
        $card_info = $this->card_m->where(["card_no"=>$card_no,"u_id"=>$u_id])->find();
        $card_id = 0;
        if($card_info){
            if($card_info["success"]){
                $json["status"] = 200;
                $json["info"] = "银行卡已经绑定成功";
                $this->returnJson($json, $session_name);
            }else{
                $card_id = $card_info["id"];
            }            
        }
        $order_id = "HLB".$u_id.time();
        $add_card_data = array(
            "uid" => $u_id,
            "order_id" => $order_id,
            "user_name" => $customer_bankaccount_info["accountname"],
            "id_card" => $this->user_info["idcard"],
            "bank_name" => $bank_name,
            "card_no" => $card_no,
            "card_cvv" => $card_cvv,
            "validity_date" => $validity_date,
            "phone" => $phone,
            "bill" => $bill,
            "repayment" => $repayment
        );
        switch ($this->c_code) {
            case "hlb":
                require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/helipay/HeliPay.php";
                $helipay = new Heli();
                $card_data = array(
                    "userId" => $u_id,
                    "orderId" => $add_card_data["order_id"],
                    "cardNo" => $add_card_data["card_no"],
                    "phone" => $add_card_data["phone"],
                    "idCardNo" => $add_card_data["id_card"],
                    "payerName" => $add_card_data["user_name"],
                    "year" => substr($add_card_data["validity_date"], 2, 4),
                    "month" => substr($add_card_data["validity_date"], 0, 2),
                    "cvv2" => $add_card_data["card_cvv"]
                );
                $re = $helipay->bindingCardCode($card_data);
                if (!$re) {
                    $json["status"] = 310;
                    $json["info"] = "发送绑卡短信失败";
                    $this->returnJson($json, $session_name);
                }
                if ($re['rt2_retCode'] != '0000') {
                    $json["status"] = 310;
                    $json["info"] = "绑卡失败(".$re['rt3_retMsg'].")";
                    $this->returnJson($json, $session_name);
                }else{
                    if($card_id){
                        $r_s = $this->card_m->where(["id"=>$card_id])->save($add_card_data);
                    }else{
                        $r_s = $this->card_m->add($add_card_data);
                    }
                    
                    if($r_s){
                        $json["status"] = 200;
                        $json["info"] = "发送绑卡短信成功";
                        $this->returnJson($json, $session_name);
                    }else{
                        $json["status"] = 311;
                        $json["info"] = "发送绑卡短信成功，添加数据失败，请联系客服";
                        $this->returnJson($json, $session_name);
                    }
                }
                break;

            default:
                $json["status"] = 311;
                $json["info"] = "非法请求";
                $this->returnJson($json, $session_name);
                break;
        }
    }

    /**
     * 解除银行卡
     */
    public function delCard(){
        $id = I("post.id"); 
        $u_id = $this->user_info["id"];
        if(!$id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->returnJson($json);
        }
        $bank_card_info = $this->card_m->where(["uid"=>$u_id,"id"=>$id,"success"=>1])->find();
        if(!$bank_card_info||!$bank_card_info["bind_id"]){
            $json["status"] = 306;
            $json["info"] = "信用卡不存在";
            $this->returnJson($json);
        }
        $plan_info = M("plan")->where(["u_id"=>$u_id,"bc_id"=>$id])->find();
        if($plan_info&&($plan_info["status"]==3||$plan_info["status"]==4)){
            $json["status"] = 307;
            $json["info"] = "此卡片有在执行或待执行的计划，不能解绑";
            $this->returnJson($json);
        }
        switch ($this->c_code) {
            case "hlb":
                require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/helipay/HeliPay.php";
                $helipay = new Heli();
                $card_data = array(
                    "hash" => $u_id,
                    "order_id" => 'UD'.$u_id. time(),
                    "bind_id" => $bank_card_info["bind_id"]
                );
                $re = $helipay->card_unbind($card_data);
                if (!$re) {
                    $json["status"] = 310;
                    $json["info"] = "解绑卡失败";
                    $this->returnJson($json);
                }
                if ($re['rt2_retCode'] != '0000') {
                    $json["status"] = 310;
                    $json["info"] = "解绑卡失败(".$re['rt3_retMsg'].")";
                    $this->returnJson($json);
                }else{
                    if($re['rt7_bindStatus']=="SUCCESS"){
                        $r_s = $this->card_m->where(["uid"=>$u_id,"id"=>$id])->save(["success"=>0]);
                        if($r_s){
                            $json["status"] = 200;
                            $json["info"] = "银行卡已经解绑成功";
                            $this->returnJson($json);
                        }else{
                            $json["status"] = 311;
                            $json["info"] = "解绑卡成功，更新数据失败，请联系客服";
                            $this->returnJson($json);
                        }
                    }
                    $json["status"] = 310;
                    $json["info"] = "解绑卡失败(".$re['rt3_retMsg'].")";
                    $this->returnJson($json);
                }
                break;

            default:
                $json["status"] = 311;
                $json["info"] = "非法请求";
                $this->returnJson($json);
                break;
        }
    }
}
