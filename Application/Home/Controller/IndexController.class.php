<?php
namespace Home\Controller;
use Think\Controller;
use Common\Common\WechatMsg;
use Common\WxApi\class_weixin_adv;
use Common\Common\WxH5Login;
use Common\HeliPay\Heli;
class IndexController extends InitController {
    public function __construct() {
        header("Content-type: text/html; charset=utf-8"); 
        parent::__construct();
        $recommend = delTrim(I("rec", session("rec"))); //推荐人
        $user_info = $this->getUserInfo();
        $openid_bind = session("openidBind");
        $wxh5login = new WxH5Login();
        if (!$user_info&&!isset($openid_bind)) {
            $return_status = $wxh5login->wxLogin($recommend);
            if ($return_status === 200) {
                $this->user_info = $this->getUserInfo();
            }
            if ($return_status === 111) {
                echo '<script>alert("推荐用户不存在");</script>';
                die();
            }
            if ($return_status === 112) {
                echo '<script>alert("登陆失败");</script>';
                die();
            }
//            $url = HSQ_HOST. '/mobile/perfect_info/registered';
//            if ($return_status === 113) {
//                header('Location: ' . $url);
//                die();
//            }
        }
        D("User")->isVip($user_info["id"]);//每次进入检查会员是否到期
        $this->assign('userInfo', $user_info);
        $this->assign('wx_share_url', $this->http . $_SERVER['HTTP_HOST'] . '/' . $user_info["id"] . '-0-0-0-0.html');
    }
    
    public function index(){
        if(MT){
            $this->redirect('index/index/maintain',[], 1, '页面跳转中...');
        }
        $this->assign('channel', U("index/plan/channel"));
        $this->display();
    }

   
    
    public function logout(){
        session(null);
        $this->redirect("/index/index");
    }
    
    public function wxmsg(){
        define("TOKEN", "TGdfxHKsdavfWSgesDdc");
        $wechatObj = new WechatMsg();
             add_log("wx_msg.log", "wxmsg", "进入：". var_export($_GET,true));
        if (isset($_GET['echostr'])) {
            $wechatObj->valid();
        }else{
            $wechatObj->responseMsg();
        }
    }
    /**
     * 系统维护
     */
    public function maintain(){
        $this->display();
    }

    // public function hlb(){
    //     require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/helipay/HeliPay.php";
    //     $helipay = new Heli();
    //     $card_no = '6229180029220896';
    //     $u_id = '464885';
    //     $card_info = M('bank_card_hlb')->where(["card_no"=>$card_no,"uid"=>$u_id])->find();
    //     $card_data = array(
    //         "orderId" => $card_info["order_id"],
    //         "payerName" => $card_info["user_name"],
    //         "idCardNo" => $card_info["id_card"],
    //         "cardNo" => $card_info["card_no"],
    //         "year" => substr($card_info["validity_date"], 2, 4),
    //         "month" => substr($card_info["validity_date"], 0, 2),
    //         "cvv2" => $card_info["card_cvv"],
    //         "phone" => $card_info["phone"]
    //     );
    //     $re = $helipay->quickPayUser($card_data);
    //     print_r($re);
    // }
}