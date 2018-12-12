<?php

namespace Home\Controller;

use Common\Common\Daifu;
use QRcode;
use Common\Common\WechatMsg;
use Common\Common\JuheRecharge;
use Common\Common\Sockets;
use Common\Common\WxH5Login;
use Common\WxApi\class_weixin_adv;
use Common\Common\Redis;
class UserController extends InitController {

    private $user_info;
    
    public function __construct() {
        header("Content-type: text/html; charset=utf-8"); 
        parent::__construct();
        $recommend = delTrim(I("rec", session("rec"))); //推荐人
        $n = delTrim(I("n"));
        $mode_type = session("modeType"); 
        $roomid = session("roomid"); 
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
            if ($return_status === 130) {
                echo '<script>alert("账号被封号，请联系客服");</script>';
                die();
            }
            $url = $this->http . $_SERVER['HTTP_HOST'] . '/index/user/qrcode.html';
            if ($return_status === 113) {
//                $model_user = D("user");
//                if($recommend){
//                    $recommend_info = $model_user->getUserOne($recommend);
//                    if(!$recommend_info||$recommend_info["type"]==1){
//                        header('Location: ' . $url);
//                        die();
//                    }
//                }else{
//                    header('Location: ' . $url);
//                    die();
//                }
                if(!$n){
                    if($mode_type==3){
                        $url = $this->http . $_SERVER['HTTP_HOST'] . '/index/custom/code.html?id='.$roomid;
                    }
                    header('Location: ' . $url);
                    die();
                }
            }
        } else {
            if ($this->user_info["status"]) {
                echo '<script>alert("账号被封号，请联系客服");</script>';
                die();
            }
        }
        $this->assign('userInfo', $this->user_info);
        $this->assign('wx_share_url', $this->http . $_SERVER['HTTP_HOST'] . '/s/' . $this->user_info["id"] . '-0-0-0-0.html');
    }

    private function startService() {
        $cli_service = D("CliService");
        $where["is_del"] = 0;
        $cli_service_list = $cli_service->getList($where);
        if ($cli_service_list) {
            foreach ($cli_service_list as $v) {
                $status = $this->getStatus($v['start_exec']);
                if (!$status) {
                    $this->exec($v['start_exec']);
                }
            }
        }
    }

    private function getStatus($kw) {
        if (strpos($kw, 'pt') === false) {
            $kw = "/data/www/platform_admin/" . $kw;
        } else {
            $arr_exec = explode("#", $kw);
            $kw = APP_ROOT . $arr_exec[1];
        }
        $str_exec = "ps -fae | grep '{$kw}'|grep -v 'grep'";
        exec($str_exec, $out);
        if ($out) {
            return 1;
        }
        return 0;
    }

    private function exec($str_exec) {
        if (strpos($str_exec, 'pt') === false) {
            $kw = "/data/www/platform_admin/" . $str_exec;
        } else {
            $arr_exec = explode("#", $str_exec);
            $kw = APP_ROOT . $arr_exec[1];
        }
        $str_exec = "php " . $kw;
        $str_exec .= ' >/dev/null &';
        exec($str_exec);
    }

    public function index() {
//        $extra = array(
//                            'add' => array('type' => 'int','size' => 2,'value' => 1),
//                            'coin' => array ('type' => 'int','size' => 4,'value' => 2),
//                            'type' => array('type' => 'int','size' => 2,'value' => 88),
//                            'cointype' => array('type' => 'int','size' => 2,'value' => 1)
//                    );
//        $response = Sockets :: call('call_back', 10, 20, 100170, $extra);
//        print_r($response);
//        die();
//        $return_status = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxdf18f2021afd7602&secret=b35af2e786877385cca5761bb82aece1");
//        print_r($return_status);
//        $user_model = D("User");
//        print_r($user_model->updIsMsg(100170));
//        echo date("Y-m-d H:i:s",1520408656684/1000);
        die();
        $recommend = delTrim(I("rec")); //推荐人
        if ($recommend) {
            session("rec", $recommend);
        } else {
            session("rec", null);
        }
        //echo "this is user index";
        $this->display();
    }
    /**
     * 账号密码登陆
     */
    public function login() {
        $data = [];
        session(null);
        if (is_post) {  //判断是否post提交
            $username = delTrim(I("post.username")); //用户名
            $password = delTrim(I("post.password")); //密码
            if (!$username) {
                $data["error"] = "用户名不能为空";
                $this->assign('data', $data);
                $this->display();
                die();
            }
            if (!$password) {
                $data["error"] = "密码不能为空";
                $this->assign('data', $data);
                $this->display();
                die();
            }
            $m_user = D("user");
            $where["username"] = $username;
            $where["password"] = md5($username . $password);
            $user_info = $m_user->getUserOneByWhere($where);
            $auth_key = md5($username . time());
            if ($user_info) {
                $user_data["username"] = $user_info["username"];
                $user_data["nickname"] = $user_info["nickname"];
                $user_data["password"] = $user_info["password"];
                $user_data["email"] = $user_info["email"];
                $user_data["gender"] = $user_info["gender"];
                $user_data["other_id"] = $user_info["other_id"];
                $user_data["other_type"] = $user_info["other_type"];
                $user_data["regip"] = $user_info["regip"];
                $user_data["regtime"] = $user_info["regtime"];
                $user_data["authkey"] = $auth_key;

                $user_data["lastip"] = getIP();
                $user_data["lasttime"] = time();
                $user_data["plat_uid"] = $user_info["id"];
                $return_status = $m_user->updUser($user_data, $where);

                if ($return_status) {
                    $l_data["u_id"] = $user_info['id'];
                    $l_data["intro"] = "用户登陆成功";
                    $l_data["add_date"] = time();
                    $m_user->addUserLoginLog($l_data);
                }

                $game_where["plat_uid"] = $user_info["id"];
                $game_user_info = $m_user->getGameUserOne($game_where);
                $session['game_uid'] = $game_user_info['uid'];
                $session['u_id'] = $user_info['id'];
                $session['u_name'] = $user_info['username'];
                $session['u_email'] = $user_info['email'];
                $session['auth_key'] = $auth_key;
                $session['other_id'] = $user_info['other_id'];
                $session['other_type'] = $user_info['other_type'];
                session('userInfo', json_encode($session));
                $data["status_info"] = "登录成功";
                //$this->assign('data',$data);
                $this->redirect('index/index', [], 0, '页面跳转中...');
            } else {
                $data["status_info"] = "登陆失败";
                $this->assign('data', $data);
                $this->display();
            }
        }
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 注册
     */
    public function register() {
        $data = [];
        if (is_post()) {
            $m_user = D("user");
            $username = delTrim(I("post.username")); //用户名
            $nickname = delTrim(I("post.nickname")); //昵称
            $password = delTrim(I("post.password")); //密码
            $repassword = delTrim(I("post.repassword")); //再次输入密码
            $email = delTrim(I("post.email")); //邮箱
            $recommend = delTrim(I("post.rec", session("rec"))); //推荐人
            if (!$username || !$password || !$email) {
                $data["status_info"] = "用户名、密码或邮箱不能空";
                $this->assign('data', $data);
                $this->display();
                die();
            }
//        $n_preg = '/^[a-zA-Z0-9]{3,12}$/';
//        if(!preg_match($n_preg,$username)){
//            $json["status"] = 306;
//            $json["info"] = "用户名为3~12位的英文或数字";
//            $this->ajaxReturn($json);
//        }
            if (!preg_match("/^13[0-9]{1}[0-9]{8}$|14[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$|177[0-9]{8}$/", $username)) {
                $data["status_info"] = "请输入正确的手机号";
                $this->assign('data', $data);
                $this->display();
                die();
            }
            $user = $m_user->getUserOneByName($username);
            if ($user) {
                $data["status_info"] = "用户名已存在";
                $this->assign('data', $data);
                $this->display();
                die();
            }
            $p_preg = '/[\x{4e00}-\x{9fa5}]/u';
            if (preg_match($p_preg, $password) || strlen($password) < 6 || strlen($password) > 12) {
                $data["status_info"] = "密码长度为6位至12位字符";
                $this->assign('data', $data);
                $this->display();
                die();
            }
            if ($password != $repassword) {
                $data["status_info"] = "两次密码不一致";
                $this->assign('data', $data);
                $this->display();
                die();
            }
            $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
            if (!preg_match($pattern, $email)) {
                $data["status_info"] = "邮箱格式不符，请重新输入";
                $this->assign('data', $data);
                $this->display();
                die();
            }
            $user_email = $m_user->getUserOneByEmail($email);
            if ($user_email) {
                $data["status_info"] = "该邮箱已被绑定";
                $this->assign('data', $data);
                $this->display();
                die();
            }
            if (!$recommend) {
                $user_info = $m_user->getUserOne($recommend);
                if (!$user_info) {
                    $data["status_info"] = "推荐用户不存在";
                    $this->assign('data', $data);
                    $this->display();
                    die();
                }
            }
            $u_data["username"] = $username;
            $u_data["nickname"] = $nickname;
            $u_data["password"] = md5($username . $password);
            $u_data["email"] = $email;
            $u_data["regip"] = getIP();
            $u_data["recommend"] = $recommend;
            $user_id = $m_user->addUser($u_data);
            if ($user_id) {
                $this->success("注册成功", "/user/login");
            }
            $json["status"] = 311;
            $json["info"] = "注册失败";
            $this->ajaxReturn($json);
        }

        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 游客登陆
     */
    public function visitor() {
        $recommend = delTrim(I("rec", session("rec"))); //推荐人
        $m_user = D("user");
        if (!$recommend) {
            $user_info = $m_user->getUserOne($recommend);
            if (!$user_info) {
                $json["status"] = 310;
                $json["info"] = "推荐用户不存在";
                $this->ajaxReturn($json);
            }
        }

        $data["username"] = "";
        $data["nickname"] = "";
        $data["password"] = "";
        $data["email"] = "";
        $data["gender"] = "1";
        $data["regip"] = getIP();
        $data["recommend"] = $recommend;
        $user_id = $m_user->addVisitor($data);
        if ($user_id) {
            $this->redirect('index/index', [], 0, '页面跳转中...');
        }
        $this->error("登陆失败", "/user/index");
    }

    public function wxlogin() {
        $recommend = delTrim(I("rec", session("rec"))); //推荐人
        $roomid = session("roomid");
        $roomcode = session("roomcode");
        $gameType = session("gameType");
        $modetype = session("modeType");
        $fk = session("fk");
        if (!$recommend) {
            $recommend = 0;
        }
        if (!$roomid) {
            $roomid = 0;
        }
        if (!$roomcode) {
            $roomcode = 0;
        }
        if (!$gameType) {
            $gameType = 0;
        }
        if (!$modetype) {
            $modetype = 0;
        }
        $url = $this->http . $_SERVER['HTTP_HOST'] . '/' . $recommend . '-' . $roomid . '-' . $roomcode . '-' . $gameType . '-' . $modetype . '.html';
        if($fk){
            $url = $this->http . $_SERVER['HTTP_HOST'] . '/fk/' . $recommend . '-' . $roomid . '-' . $roomcode . '-' . $gameType . '-' . $modetype . '.html';
        }
        header('Location: ' . $url);
        die();
        $wxh5login = new WxH5Login();
        $return_status = $wxh5login->wxLogin($recommend);
        if ($return_status === 111) {
            //$this->error("推荐用户不存在",  U("/index/index"));
            echo '<script>alert("推荐用户不存在");location="' . $url . '";</script>';
            die();
            //header('Location: ' . $url);
        }
        if ($return_status === 112) {
            //$this->error("登陆失败", U("/index/index",["rec"=>$recommend,"roomid"=>$roomid,"roomcode"=>$roomcode]));
            echo '<script>alert("登陆失败");location="' . $url . '";</script>';
            die();
        }
        if ($return_status === 130) {
            //$this->error("登陆失败", U("/index/index",["rec"=>$recommend,"roomid"=>$roomid,"roomcode"=>$roomcode]));
            echo '<script>alert("账号被封号，请联系客服");location="' . $url . '";</script>';
            die();
        }
        if ($return_status === 200) {
            //$this->redirect("/index/index", ["roomid"=>$roomid,"roomcode"=>$roomcode], 0, '页面跳转中...');
            header('Location: ' . $url);
        }
    }

    public function info() {
        $this->startService();
        $award_model = D("Award");
        $user_model = D("user");
        $award_info = $award_model->getOne($this->user_info["id"]);
        $user_agency = $user_model->getUserAgencyByUserId($this->user_info["id"]);
        $award_arr["earn"] = 0;
        $award_arr["amount"] = 0;
        if ($award_info) {
            $award_arr["earn"] = $award_info["earn"];
            $award_arr["amount"] = $award_info["amount"];
        }
        $this->assign('award_info', $award_arr);
        $this->assign('grade', $user_model->getLevelText($user_agency["grade"]));
        $this->assign('custom', $this->http . $_SERVER['HTTP_HOST'] . '/index/');
        $this->display();
    }

    public function generalize() {
        $model_gl = D("Generalize");
//        $image = U("user/code");
//        $wx_config = get_js_sdk("wxdf18f2021afd7602", "b35af2e786877385cca5761bb82aece1");
//        $this->assign('wx_config', $wx_config);
//        $this->assign('image', $this->http . $_SERVER['HTTP_HOST'] . '/index' . $image);
//        $this->assign('uid', $this->user_info["id"]);
//        $this->assign('nickname', $this->user_info["nickname"]);
//        $this->assign('s_url', $this->http . $_SERVER['HTTP_HOST'] . '/index/user/generalize.html?rec='.$this->user_info["id"]);
        $gl_list = $model_gl->getList();
        $this->assign('gl_list', $gl_list);
        $this->display("gllist");
    }
    public function glinfo() {
        $model_gl = D("Generalize");
        $id=I("id");
        $image = U("user/code");
        $wx_config = get_js_sdk("wxdf18f2021afd7602", "b35af2e786877385cca5761bb82aece1");
        $gl_info = $model_gl->getOne(["id"=>$id]);
        $this->assign('wx_config', $wx_config);
        $this->assign('image', $this->http . $_SERVER['HTTP_HOST'] . '/index' . $image);
        $this->assign('uid', $this->user_info["id"]);
        $this->assign('nickname', $this->user_info["nickname"]);
        $this->assign('s_url', $this->http . $_SERVER['HTTP_HOST'] . '/index/user/glinfo.html?rec='.$this->user_info["id"]."&id=".$id);
        $this->assign('gl_info', $gl_info);
        $this->display("generalize");
    }

    public function code() {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/phpqrcode/phpqrcode.php";
        $url = $this->http . $_SERVER['HTTP_HOST'] . '/' . $this->user_info["id"] . '-0-0-0.html';
        $image = QRcode::png($url, false, "L", 5);
        print_r($image);
        die();
    }

    public function extract() {
        $award_model = D("Award");
        $award_list = $award_model->getAwardExtractList(["u_id" => $this->user_info["id"]], "add_date DESC");
        $this->assign('awardList', $award_list);
        $this->display();
    }
    
    public function csncode() {
        $model_order = D("Order");
        $user_model = D("User");
        $user_agency = $user_model->getUserAgencyByUserId($this->user_info["id"]);
        $shop_list = [];
        if($user_agency){
            $shop_list = $model_order->getGameShopList(["level" => $user_agency["grade"]+1,"type"=>2]);
        }
        $url = $this->http . $_SERVER['HTTP_HOST'] . '/api/award/buyBean?uid='.$this->user_info["id"]."&authkey=".$this->user_info["authkey"];
        $this->assign('shopUrl', $url);
        $this->assign('shopList', $shop_list);
        $this->display();
    }

    public function extractinfo() {
        $id = I("id", 0);
        $award_model = D("Award");
        $award_info = $award_model->getAwardExtractOne(["id" => $id]);
        $this->assign('awardInfo', $award_info);
        $this->display();
    }

    public function userinfo() {
        $user_model = D("User");
        $user_bank = $user_model->getUserBank($this->user_info["id"]);
        $game_user = $user_model->getGameUserOne(["uid"=>$this->user_info["id"]]);
        $user_agency_info = $user_model->getUserAgencyByUserId($this->user_info["id"]);  //查询当前用户等级
        $grade = $user_agency_info["grade"];
        $url = $this->http . $_SERVER['HTTP_HOST'] . '/api/index/generalize?uid='.$this->user_info["id"]."&authkey=".$this->user_info["authkey"];
        $r_data = file_get_contents($url);
        $r_data = json_decode($r_data, true);
        $a_data = [];
        if($r_data&&$r_data["status"]==200){
            $a_data = $r_data["data"];  //旁系下属
        }
        $this->assign('grade_name', $user_model->getLevelText($grade));
        $this->assign('user_bank', $user_bank);
        $this->assign('gameUser', $game_user);
        $this->assign('a_data', $a_data);
        $this->display();
    }
    /**
     * 我的推广列表
     */
    public function agencylist(){
        $user_model = D("User");
        $user_agency = $user_model->agencyList($this->user_info["id"]);
        $a_data_downs = [];
        $a_data_ups = [];
        if($user_agency){
            $a_data_up = $user_agency['user_up'];
            $a_data_down = $user_agency['user_down'];
            if($a_data_up&&!empty($a_data_up)){
                $a_data_up["grade"] = $user_model->getLevelText($a_data_up["grade"]);
                $a_data_up["regtime"] = date("Y-m-d",$a_data_up["regtime"]);
                $a_data_ups = $a_data_up;
            }
            if($a_data_down&&!empty($a_data_down)){
                foreach ($a_data_down as $value) {
                    $value["grade"] = $user_model->getLevelText($value["grade"]);
                    $value["regtime"] = date("Y-m-d",$value["regtime"]);
                    $a_data_downs[] = $value;
                }
            }
        }
        $this->assign('a_data_down', $a_data_downs);
        $this->assign('a_data_up', $a_data_ups);
        $this->display();
    }

    public function userbank() {
        $user_model = D("User");
        $user_bank["name"] = '';
        $user_bank["card"] = '';
        $user_bank["bank_code"] = '';
        $user_bank["bank"] = '';
        $user_bank["province_code"] = 0;
        $user_bank["province"] = '';
        $user_bank["city_code"] = 0;
        $user_bank["city"] = '';
        $user_bank["branch_name"] = '';

        $user_bank_info = $user_model->getUserBank($this->user_info["id"]);
        if ($user_bank_info) {
            $user_bank = $user_bank_info;
        }
        $this->assign('user_bank', $user_bank);
        $this->assign('uid', $this->user_info["id"]);
        $this->assign('authkey', $this->user_info["authkey"]);
        $this->assign('subUrl', $this->http . $_SERVER['HTTP_HOST'] . '/api/user/addBank');
        $this->assign('pcUrl', $this->http . $_SERVER['HTTP_HOST'] . '/api/index/getProvince?uid='.$this->user_info["id"]."&authkey=".$this->user_info["authkey"]);
        $this->display();
    }

    public function userauth() {
        $this->display();
    }

    public function order() {
        $order_model = D("Order");
        $order_list = $order_model->getList(["u_id" => $this->user_info["id"]], 1, 100, "add_date DESC");
        $game_order_list = $order_model->getGameOrder(["uid" => $this->user_info["id"]], 1, 100, "add_time DESC");
        $order_arr = [];
        if($order_list){
            foreach ($order_list as $value) {
                $shop_info = $order_model->getGameShop($value["item_id"]);
                $value["item_name"] = strip_tags($shop_info["name"]);
                $value["amount"] = (int)$value["amount"];
                $value["ratio"] = (int)$value["ratio"];
                $order_arr[] = $value;
            }
        }
        $this->assign('orderList', $order_arr);
        $this->assign('gameOrderList', $game_order_list);
        $this->display();
    }

    public function orderinfo() {
        $order_type = I("order_type", 0);
        $order_number = I("order_number", '');
        $order_model = D("Order");
        $order_info = [];
        if ($order_type == 1) {
            $order_info = $order_model->getOneByOrderNumber($order_number);
        }
        if ($order_type == 2) {
            $order_info = $order_model->getGameOrderOne($order_number);
            if ($order_info && $order_info["cards"]) {
                $appkey = '4ec21cc5eab22e4a7dc6d64275bc5126'; //从聚合申请的通用礼品卡appkey
                $openid = C('JH_CONFIG.OPENID'); //注册聚合账号就会分配的openid，在个人中心可以查看
                $juhe_recharge = new JuheRecharge($appkey, $openid);
                $key = substr(str_pad("kxyl123456", 8, '0'), 0, 8);
                $cards_arr = json_decode($order_info["cards"], true);
                $cardNo = $juhe_recharge->decode($cards_arr["cardNo"], $key);
                $cardPws = $juhe_recharge->decode($cards_arr["cardPws"], $key);
                $order_info["cardNo"] = $cardNo;
                $order_info["cardPws"] = $cardPws;
            }
        }
        $this->assign('order_info', $order_info);
        $this->assign('order_type', $order_type);
        $this->display();
    }

    public function address() {
        $user_model = D("User");
        $user_address = $user_model->getGameUserAddr($this->user_info["id"]);
        $address["name"] = "";
        $address["number"] = "";
        $address["phone"] = "";
        $address["postcode"] = "";
        $address["address"] = "";
        if ($user_address) {
            $address["name"] = $user_address["name"];
            $address["number"] = $user_address["number"];
            $address["phone"] = $user_address["phone"];
            $address["postcode"] = $user_address["postcode"];
            $address["address"] = $user_address["address"];
        }
        $this->assign('address', $address);
        $this->display();
    }

    /**
     * 找回密码
     */
    function findPassword() {
        $data = [];
        if (is_post) {  //判断是否post提交
            $username = delTrim(I("post.username")); //用户名
            $email = delTrim(I("post.email")); //邮箱
            if (!$username || !$email) {
                $data["error"] = "用户名和邮箱不能为空";
                $this->assign('data', $data);
                $this->display();
                die();
            }
            $m_user = D("user");
            $where["username"] = $username;
            $where["email"] = $email;
            $user_info = $m_user->getUserOneByWhere($where);
            if (!$user_info) {
                $data["error"] = "该帐号绑定的邮箱有误，请重新输入";
                $this->assign('data', $data);
                $this->display();
                die();
            }
            $chars = array(
                "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
                "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
                "w", "x", "y", "z", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"
            );
            $password = get_rand_str(6, $chars);
            $title = "开心逗棋牌密码找回";
            $msg = "您在开心逗棋牌的帐号是{$username} 密码是：{$password}，请牢记!祝您游戏愉快!";
            $pass_word = md5($user_info["username"] . $password);
            $return_status = $m_user->updPassWord($pass_word, $where);
            if ($return_status) {
                sendMail($email, $title, $msg);
                $this->success("找回密码成功", "/user/login");
            } else {
                $this->error("找回密码失败", "/user/findpassword");
            }
        }
        $this->display();
    }

    public function qrcode() {
        session("userInfo", null);
        $this->display();
    }

    /**
     * 佣金提现
     */
    public function withdrawal() {
        $award_model = D("Award");
        $user_model = D("user");
        $award_info = $award_model->getOne($this->user_info["id"]);
        $award_earn_list = $award_model->getAwardEarnList(["u_id"=>$this->user_info["id"]],"add_date DESC");
        $this->assign('award_earn_list', $award_earn_list);
        $this->assign('awardInfo', $award_info);
        $this->assign('uid', $this->user_info["id"]);
        $this->assign('authkey', $this->user_info["authkey"]);
        $this->assign('subUrl', $this->http . $_SERVER['HTTP_HOST'] . '/api/award/take');
        $this->display();
    }
    /**
     * 代理等级说明
     */
    public function instruction(){
        $this->display();
    }
    
    public function app(){
        $type="Android";
        $url=CDN_HOST."/wd/download/kxdqp_v1.1.2.apk?v=".get_rand_str(10);
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
            $type="IOS";
            $url="";
        }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
            
        }else{
            
        }
        $this->assign('type', $type);
        $this->assign('url', $url);
        $this->display();
    }
    /**
     * 我的团队
     */
    public function team(){
        $award_model = D("Award");
        $user_model = D("user");
        $award_info = $award_model->getOne($this->user_info["id"]);
        $user_agency = $user_model->getUserAgencyByUserId($this->user_info["id"]);
        $award_arr["earn"] = 0;
        $award_arr["amount"] = 0;
        if ($award_info) {
            $award_arr["earn"] = $award_info["earn"];
            $award_arr["amount"] = $award_info["amount"];
        }
        $game_user = $user_model->getGameUserOne(["uid"=>$this->user_info["id"]]);
        $url = $this->http . $_SERVER['HTTP_HOST'] . '/api/index/generalize?uid='.$this->user_info["id"]."&authkey=".$this->user_info["authkey"];
        $r_data = file_get_contents($url);
        $r_data = json_decode($r_data, true);
        $a_data = [];
        if($r_data&&$r_data["status"]==200){
            $a_data = $r_data["data"];  //旁系下属
        }
        $url = $this->http . $_SERVER['HTTP_HOST'] . '/api/index/agencyList?uid='.$this->user_info["id"]."&authkey=".$this->user_info["authkey"];
        $r_data = file_get_contents($url);
        $r_data = json_decode($r_data, true);
        $a_data_down = [];
        $a_data_up = [];
        if($r_data&&$r_data["status"]==200){
            if($r_data["data"]["up"]&&!empty($r_data["data"]["up"])){
                $r_data["data"]["up"]["grade"] = $user_model->getLevelText($r_data["data"]["up"]["grade"]);
                $r_data["data"]["up"]["regtime"] = date("Y-m-d",$r_data["data"]["up"]["regtime"]);
                $a_data_up = $r_data["data"]["up"];
            }
            if($r_data["data"]["down"]&&!empty($r_data["data"]["down"])){
                foreach ($r_data["data"]["down"] as $value) {
                    $value["grade"] = $user_model->getLevelText($value["grade"]);
                    $value["regtime"] = date("Y-m-d",$value["regtime"]);
                    $a_data_down[] = $value;
                }
            }
        }
        $url = $this->http . $_SERVER['HTTP_HOST'] . '/api/index/iagency?uid='.$this->user_info["id"]."&authkey=".$this->user_info["authkey"];
        $r_data = file_get_contents($url);
        $r_data = json_decode($r_data, true);
        $this->assign('is_upgrade', $r_data["is_upgrade"]);
        $this->assign('upd_agency', $this->http . $_SERVER['HTTP_HOST'] . '/api/index/updAgency?uid='.$this->user_info["id"]."&authkey=".$this->user_info["authkey"]);
        $this->assign('a_data_down', $a_data_down);
        $this->assign('a_data_up', $a_data_up);
        $this->assign('gameUser', $game_user);
        $this->assign('a_data', $a_data);
        $this->assign('award_info', $award_arr);
        $this->assign('grade', $user_model->getLevelText($user_agency["grade"]));
        $this->display();
    }

    public function test(){
        //$this->display();
//        header("Content-Type:text/html; charset=utf-8");
//        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/WxPay.JsApiPay.php";
//        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/weixin.api.php";
//        
//        $m_user =D("user");
//        $db_config = C("DB_CONFIG2");
//        $model = M("order",$db_config["DB_PREFIX"],$db_config);
//        $order_list = $model->select();
//        $appkey = '7baf94b5d3250af823d88bb3fe1081e1'; //从聚合申请的话费充值appkey
//        $openid = C('JH_CONFIG.OPENID'); //注册聚合账号就会分配的openid，在个人中心可以查看
//        $juhe_recharge = new JuheRecharge($appkey,$openid);
//        if($order_list){
//            foreach ($order_list as $value) {
//                $order_number = $value["order_number"];
//                $r_data = $juhe_recharge->sta($order_number);
//                if($r_data["error_code"]==0){
//                    $ordercash = $r_data["result"]["uordercash"];
//                    $o_data["ordercash"] = $ordercash;
//                    $o_where["id"] = $value["id"];
//                    $model->where($o_where)->save($o_data);
//                }
//            }
//        }

//        $sql = "SELECT unionid,other_id FROM __TABLE__ WHERE id=100742";
//        $result = $m_user->query($sql);
//        $weixin = new class_weixin_adv();
//        if($result){
//            foreach ($result as $value) {
//                $openId = $value["other_id"];
//                $wx_info_data = $weixin->get_user_info($openId);
//                $user_data["unionid"] = $wx_info_data["unionid"];
//                $where["other_id"] = $openId;
//                $m_user->where($where)->save($user_data);
//                $m_user->updGameUser($user_data, $where);
//            }
//        }
//        $user_info = $m_user->getUserOne(10001);
//        if($user_info){
//            echo 1;
//            $db_config = C("DB_CONFIG2");
//            $model = M("user",$db_config["DB_PREFIX"],$db_config);
//            $result_model = M("user_result",$db_config["DB_PREFIX"],$db_config);
//            $result_data["uid"] = $user_info["id"];
//            $result_data["gametype"] = 1;
//            $result_model->add($result_data);
//            $result_data["gametype"] = 2;
//            $result_model->add($result_data);
//            $result_data["gametype"] = 3;
//            $result_model->add($result_data);
//            $result_data["gametype"] = 4;
//            $result_model->add($result_data);
//            $result_data["gametype"] = 5;
//            $result_model->add($result_data);
//            $result_data["gametype"] = 6;
//            $result_model->add($result_data);
//            $uid = $user_info["id"];
//            unset($user_info["wechat"]);
//            unset($user_info["type"]);
//            unset($user_info["status"]);
//            unset($user_info["channel"]);
//            unset($user_info["is_msg"]);
//            unset($user_info["id"]);
//            $data = $user_info;
//            $data["uid"] = $uid;
//            $model->add($data);
//        }
//        $order = M("order");
//        $db_config = C("DB_CONFIG2");
//        $model_economy = M("log_economy",$db_config["DB_PREFIX"],$db_config);
//        $model = M("user",$db_config["DB_PREFIX"],$db_config);
//        $sql = "SELECT u_id FROM __TABLE__ WHERE 1 GROUP BY u_id";
//        $e_data = $order->query($sql);
//        if($e_data){
//            foreach ($e_data as $value) {
//                $uid = $value["u_id"];
//                $data["uid"] = $uid;
//                $user_info = $model->where($data)->find();
//                if(!$user_info){
//                    echo $uid.",";
//                }
//            }
//        }
        die();
        
    }
}
