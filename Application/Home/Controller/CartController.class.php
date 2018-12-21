<?php
namespace Home\Controller;

/**
 * 银行卡管理类
 *
 * @author Administrator
 */
class CartController extends InitController {
    private $user_info;
    private $c_code;
    private $cart_m;
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
                echo '<script>alert("推荐用户不存在");</script>';
                die();
            }
            if ($return_status === 112) {
                echo '<script>alert("登陆失败");</script>';
                die();
            }
            $url = HSQ_HOST. '/mobile/perfect_info/registered';
            if ($return_status === 113) {
                header('Location: ' . $url);
                die();
            }
        }
        $this->c_code = I("c_code");  //通道编码
        if(!$this->c_code){
            echo '<script>alert("参数错误");</script>';
            die();
        }
        $table_name = "bank_card_".$this->c_code;
        $isTable = M()->query('SHOW TABLES LIKE "'.$table_name.'"');
        if( $isTable ){
            $this->cart_m = M($table_name);
        }else{
            echo '<script>alert("非法请求");</script>';
            die();
        }
        $this->assign('userInfo', $this->user_info);
        $this->assign('wx_share_url', $this->http . $_SERVER['HTTP_HOST'] . '/s/' . $this->user_info["id"] . '-0-0-0-0.html');
    }
    public function index(){
        $u_id = $this->user_info["id"];
        $bank_card_list = $this->cart_m->where(["uid"=>$u_id,"success"=>1])->select();
        $this->assign('bank_card_list', $bank_card_list);
        $this->assign('del_cart_url', U("cart/delCart"));
        $this->display();
    }
    /**
     * 添加银行卡
     */
    public function addCart(){
        $db_config = C("DB_CONFIG2");
        $u_id = $this->user_info["id"];
        $bank_id = $this->user_info["bankId"];
        $url = HSQ_HOST.'/mobile/info/index.html';
        if(!$bank_id){
            echo '<script>alert("请完善个人资料");location="'.$url.'"</script>';
            die();
        }
        $customer_bankaccount_m = M("customer_bankaccount",$db_config["DB_PREFIX"],$db_config);
        $customer_bankaccount_info = $customer_bankaccount_m->where(["id"=>$bank_id,"userId"=>$u_id])->find();
        if(!$customer_bankaccount_info){
            echo '<script>alert("请完善个人资料");location="'.$url.'"</script>';
            die();
        }
        if(is_post()){
            
        }
        $this->assign('account_name', $customer_bankaccount_info["name"]);
        $this->display();
    }
    /**
     * 解除银行卡
     */
    public function delCart(){
        
    }
}
