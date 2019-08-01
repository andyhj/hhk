<?php
namespace Home\Controller;

/**
 * 银行卡管理类
 *
 * @author Administrator
 */
use Common\Common\WxH5Login;
use Common\HeliPay\Heli;
use Common\GyfPay\gyf;
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
        $code = I("get.c_code");  //通道编码
        if(!$code){
            $code = I("post.c_code");  //通道编码
        }
        $this->c_code = $code;  //通道编码
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
        $card_no = trim(I("post.card_no"));  //银行卡号
        $card_cvv = trim(I("post.card_cvv"));  //cvv
        $validity_date = trim(I("post.validity_date")); //有效期
        $bank_name = I("post.bank_name");       //银行名称
        $bill = (int)trim(I("post.bill"));             //账单日
        $repayment = (int)trim(I("post.repayment"));  //还款日
        $phone = trim(I("post.phone"));           //预留手机号
        $code = trim(I("post.code"));             //验证码
        $session_name = "submitCard_".$u_id;
        // session($session_name,null);
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
                            $json["url"] ='';
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
            case "gyf":
                require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/gyfpay/gyfpay.php";
                $cookie_code = cookie('card_'.$card_no); 
                if($cookie_code!=MD5($code)){
                    $json["status"] = 311;
                    $json["info"] = "验证码不正确";
                    $this->returnJson($json, $session_name);
                }
                //参数
                $param=[
                    'merch_id' => $card_info['merch_id'],//子商户号
                    'name'=> $card_info["user_name"],//法人姓名
                    'phone'=> $card_info["phone"],//法人电话
                    'id_card'=> $card_info["id_card"],//身份证号
                    'card_id'=> $card_info["card_no"],//交易卡号
                    'notify_url'=> HTTP_HOST."/index/gyfCallback/bKreceive.html",//异步通知地址
                    'front_url'=> HTTP_HOST."/index/card/index.html?c_code=".$this->c_code, //页面通知地址,绑卡结束调回支付页
                    'order_id' => 'BK'.$u_id.time(),//请求流水号
                ];
                $res_j = gyf::bindCardHtml($param);
                if(isset($res_j['status']) && $res_j['status'] == 1){
                    if($res_j['ret_data']['data']['html']){
                        $html =  stripslashes($res_j['ret_data']['data']['html']);
                        $this->card_m->where(["card_no"=>$card_no,"u_id"=>$u_id])->save(['html'=>$html]);
                        $json["status"] = 200;
                        $json["info"] = "下一步跳至银联页面完成绑卡";
                        $json["url"] = U("index/card/showHtml",['c_code'=>$this->c_code,'card_id'=>$card_info["id"]]);
                        $this->returnJson($json, $session_name);
                    }
                    $json["status"] = 312;
                    $json["info"] = "绑卡失败，银联返回参数错误";
                    $this->returnJson($json, $session_name);
                }
                $json["status"] = 313;
                $json["info"] = "绑卡失败(".$res_j['msg'].")";
                $this->returnJson($json, $session_name);
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
        $card_no = trim(I("post.card_no"));  //银行卡号
        $card_cvv = trim(I("post.card_cvv"));  //cvv
        $validity_date = trim(I("post.validity_date")); //有效期
        $bank_name = I("post.bank_name");       //银行名称
        $bill = (int)trim(I("post.bill"));             //账单日
        $repayment = (int)trim(I("post.repayment"));  //还款日
        $phone = trim(I("post.phone"));           //预留手机号
        $session_name = "submitCard_".$u_id;
        // session($session_name,null);
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
        $order_id = strtoupper($this->c_code).$u_id.time();
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
            case "gyf":
                require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/gyfpay/gyfpay.php";
                $merch_id = '';
                $card_merch_info = $this->card_m->where(["uid"=>$u_id,"success"=>1])->find();
                if($card_merch_info&&$card_merch_info['merch_id']){
                    $merch_id = $card_merch_info['merch_id'];
                }else{
                    if(!$customer_bankaccount_info['province']||!$customer_bankaccount_info['city']){
                        $json["status"] = 310;
                        $json["info"] = "进件异常(结算账户填写不完整)";
                        $this->returnJson($json, $session_name);
                    }
                    $addr = $customer_bankaccount_info['province'].$customer_bankaccount_info['city'];//商户详细地址
                    $merch_arr = $this->getmchNo($u_id,$customer_bankaccount_info["accountname"],$phone,$addr,$this->user_info["idcard"],$card_no);
                    if($merch_arr&&$merch_arr['code']==1&&$merch_arr['merch_id']){
                        $merch_id = $merch_arr['merch_id'];
                    }else{
                        $json["status"] = 311;
                        $json["info"] = "进件异常(".$merch_arr['msg'].")";
                        $this->returnJson($json, $session_name);
                    }                    
                }                
                $add_card_data['merch_id'] = $merch_id;
                $re = send_sms($add_card_data["phone"]);
                if (!$re) {
                    $json["status"] = 310;
                    $json["info"] = "发送绑卡短信失败";
                    $this->returnJson($json, $session_name);
                }
                if($card_id){
                    $r_s = $this->card_m->where(["id"=>$card_id])->save($add_card_data);
                }else{
                    $r_s = $this->card_m->add($add_card_data);
                }
                
                if($r_s){
                    cookie('card_'.$card_no,MD5($re),60); 
                    $json["status"] = 200;
                    $json["info"] = "发送绑卡短信成功";
                    $this->returnJson($json, $session_name);
                }else{
                    $json["status"] = 311;
                    $json["info"] = "发送绑卡短信成功，添加数据失败，请联系客服";
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
                break;
            case "gyf":
                $r_s = $this->card_m->where(["uid"=>$u_id,"id"=>$id])->save(["success"=>0]);
                if($r_s){
                    $json["status"] = 200;
                    $json["info"] = "银行卡已经解绑成功";
                    $this->returnJson($json);
                }else{
                    $json["status"] = 311;
                    $json["info"] = "解绑失败，请联系客服";
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
    /**
     * 工易付获取商户号
     *
     * @param [type] $u_id      用户id
     * @param [type] $u_name    用户真实姓名
     * @param [type] $phone     手机号码
     * @param [type] $addr      地址
     * @param [type] $id_card   身份证号码
     * @param [type] $account   卡号
     * @return void
     */
    private function getmchNo($u_id,$u_name,$phone,$addr,$id_card,$account){
        if(!$u_id||!$u_name||!$phone||!$addr||!$id_card||!$account){
            return array('code' => 0, 'msg' => '进件异常(数据不存在)');
        }
        $channel_model = M("channel");
        $channel_info = $channel_model->where(["code"=>$this->c_code])->find();
        if(!$channel_info){
            return array('code' => 0, 'msg' => '通道不存在');
        }
        $fee = $channel_info["user_fee"]; //普通用户交易费率
        $close_rate = $channel_info["user_close_rate"];   //普通用户结算费用（每笔）

        $user_m = M("user");
        $user_des = $user_m->where(["u_id"=>$u_id])->find();
        //判断是否plus会员
        if($user_des && $user_des['is_vip']){
            $fee = $channel_info["plus_user_fee"]; //plus用户交费率
            $close_rate = $channel_info["plus_user_close_rate"];   //plus用户结算费用（每笔）
        }
        //收集信息
        $param = array(          
            'name'=> $u_name,//真实姓名
            'phone'=> $phone,//手机号
            'id_card'=> $id_card,//身份证号码
            'merch_addr'=> $addr,//商户详细地址
            'card_id'=> $account,//结算账号
            'fee_rate'=> $fee*10000,//交易费率0.68% 传  68. 费率值乘于10000
            'extern_fee'=> $close_rate*100,//附加手续费(结算手续费)，单位分：（1.00元，传 100）
        );
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/gyfpay/gyfpay.php";
        $res_j = gyf::regMchInfo($param);
        if(isset($res_j['status']) && $res_j['status'] == 1){
            return array('code' => 1, 'merch_id' => $res_j['ret_data']['data']['subMerchId']);
        }else{
            return array('code' => 0, 'msg' => '进件异常('.$res_j['msg'].')');
        }
        
    }
    /**
     * 工易付银联绑卡页面
     *
     * @return void
     */
    public function showHtml()
    {
        $card_id = I('card_id');
        $u_id = $this->user_info["id"];
        $card_info = $this->card_m->where(["id"=>$card_id,"uid"=>$u_id])->find();
        echo $card_info['html'];
    }
}
