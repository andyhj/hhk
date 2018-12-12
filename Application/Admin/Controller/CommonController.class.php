<?php
namespace Admin\Controller;

use Think\Controller;

class CommonController extends Controller {

    public $loginMarked;

    /**
      +----------------------------------------------------------
     * 初始化
     * 如果 继承本类的类自身也需要初始化那么需要在使用本继承类的类里使用parent::_initialize();
      +----------------------------------------------------------
     */
    public function _initialize() {
        header("Content-Type:text/html; charset=utf-8");
        header('Content-Type:application/json; charset=utf-8');
        $systemConfig = include WEB_ROOT . 'Application/Common/Conf/systemConfig.php';
        if (empty($systemConfig['TOKEN']['admin_marked'])) {
            $systemConfig['TOKEN']['admin_marked'] = "andy";
            $systemConfig['TOKEN']['admin_timeout'] = 3600;
            $systemConfig['TOKEN']['member_marked'] = "";
            $systemConfig['TOKEN']['member_timeout'] = 3600;
            F("systemConfig", $systemConfig, WEB_ROOT . "Application/Common/Conf/");
        }
        $this->loginMarked = md5($systemConfig['TOKEN']['admin_marked']);
        $this->checkLogin();
        // 用户权限检查

//        if (C('USER_AUTH_ON') && !in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE')))) {
//            import('ORG.Util.RBAC');
//            if (!RBAC::AccessDecision()) {
//                //检查认证识别号
//                if (!$_SESSION [C('USER_AUTH_KEY')]) {
//                    //跳转到认证网关
//                    redirect(C('USER_AUTH_GATEWAY'));
////                    redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
//                }
//                // 没有权限 抛出错误
//                if (C('RBAC_ERROR_PAGE')) {
//                    // 定义权限错误页面
//                    redirect(C('RBAC_ERROR_PAGE'));
//                } else {
//                    if (C('GUEST_AUTH_ON')) {
//                        $this->assign('jumpUrl', C('USER_AUTH_GATEWAY'));
//                    }
//                    // 提示错误信息
////                     echo L('_VALID_ACCESS_');
//                    $this->error(L('_VALID_ACCESS_'));
//                }
//            }
//        }
        $this->assign("menu", $this->show_menu());
        $this->assign("sub_menu", $this->show_sub_menu());
        $this->assign("my_info", $_SESSION['my_info']);
        $this->assign("site", $systemConfig);

        //$this->getQRCode();
    }

    protected function getQRCode($url = NULL) {
        if (IS_POST) {
            $this->assign("QRcodeUrl", "");
        } else {
//            $url = empty($url) ? C('WEB_ROOT') . $_SERVER['REQUEST_URI'] : $url;
            $url = empty($url) ? C('WEB_ROOT') . U(MODULE_NAME . '/' . ACTION_NAME) : $url;
            import('QRCode');
            $QRCode = new QRCode('', 80);
            $QRCodeUrl = $QRCode->getUrl($url);
            $this->assign("QRcodeUrl", $QRCodeUrl);
        }
    }

    public function checkLogin() {
        if (isset($_COOKIE[$this->loginMarked])) {
            $cookie = explode("_", $_COOKIE[$this->loginMarked]);
            $systemConfig = include WEB_ROOT . 'Application/Common/Conf/systemConfig.php';
            $timeout = $systemConfig["TOKEN"];
            if (time() > (end($cookie) + $timeout['admin_timeout'])) {
                setcookie("$this->loginMarked", NULL, -3600, "/");
                unset($_SESSION[$this->loginMarked], $_COOKIE[$this->loginMarked]);
                $this->error("登录超时，请重新登录", U("Public/index"));
            } else {
                if ($cookie[0] == $_SESSION[$this->loginMarked]) {
                    setcookie("$this->loginMarked", $cookie[0] . "_" . time(), 0, "/");
                } else {
                    setcookie("$this->loginMarked", NULL, -3600, "/");
                    unset($_SESSION[$this->loginMarked], $_COOKIE[$this->loginMarked]);
                    $this->error("帐号异常，请重新登录", U("Public/index"));
                }
            }
        } else {
            $this->redirect("public/index");
        }
        return TRUE;
    }

    /**
      +----------------------------------------------------------
     * 验证token信息
      +----------------------------------------------------------
     */
    protected function checkToken() {
        if (IS_POST) {
            if (!M("Admin")->autoCheckToken($_POST)) {
                die(json_encode(array('status' => 0, 'info' => '令牌验证失败')));
            }
            unset($_POST[C("TOKEN_NAME")]);
        }
    }

    /**
      +----------------------------------------------------------
     * 显示一级菜单
      +----------------------------------------------------------
     */
    private function show_menu() {
        $_action = explode('/', __ACTION__);
        $model_name = $_action[2];
        $cache = $this->menu()['admin_big_menu'];
        $count = count($cache);
        $i = 1;
        $menu = "";
        $admin_info = $_SESSION['my_info'];
        if($admin_info["aid"]!=1){
            $role_user = M("role_user");
            $access = M("access");
            $role_user_info = $role_user->where(["user_id"=>$admin_info["aid"]])->find();
            if(!$role_user_info){
                return $menu;
            }
            $access_list = $access->where(["role_id"=>$role_user_info["role_id"],"level"=>2])->select();
            if(!$access_list){
                return $menu;
            }
            $model_list = [];
            foreach ($access_list as $value) {
                $model_list[] = strtolower($value["module"]);
            }
            
        }
        
        foreach ($cache as $url => $name) {
            if($admin_info["aid"]!=1){
                $mo = str_replace('_','',$url); 
                if(!in_array("$mo",$model_list)){
                    continue;
                }
            }
            if ($i == 1) {
                $css = $url == $model_name || !$cache[$model_name] ? "fisrt_current" : "fisrt";
                $menu .= '<li class="' . $css . '"><span><a href="' . U($url . '/index') . '">' . $name . '</a></span></li>';
            } else if ($i == $count) {
                $css = $url == $model_name ? "end_current" : "end";
                $menu .= '<li class="' . $css . '"><span><a href="' . U($url . '/index') . '">' . $name . '</a></span></li>';
            } else {
                $css = $url == $model_name ? "current" : "";
                $menu .= '<li class="' . $css . '"><span><a href="' . U($url . '/index') . '">' . $name . '</a></span></li>';
            }
            $i++;
        }
        return $menu;
    }

    /**
      +----------------------------------------------------------
     * 显示二级菜单
      +----------------------------------------------------------
     */
    private function show_sub_menu() {
        $_action = explode('/', __ACTION__);
        $big = $_action[2];
        $cache = $this->menu()['admin_sub_menu'];
        $sub_menu = array();
        $admin_info = $_SESSION['my_info'];
        $model_list = [];
        if($admin_info["aid"]!=1){
            $role_user = M("role_user");
            $access = M("access");
            $role_user_info = $role_user->where(["user_id"=>$admin_info["aid"]])->find();
            if(!$role_user_info){
                return $sub_menu;
            }
            $access_list = $access->where(["role_id"=>$role_user_info["role_id"],"level"=>3])->select();
            if(!$access_list){
                return $sub_menu;
            }
            foreach ($access_list as $value) {
                $model_list[] = strtolower($value["module"]);
            }
        }
//        print_r($model_list);
        if ($cache[$big]) {
            $cache = $cache[$big];
            foreach ($cache as $url => $title) {
                if($admin_info["aid"]!=1){
                    $mo = str_replace('_','',$url); 
                    if(!in_array("$mo",$model_list)){
                        continue;
                    }
                }
                $url = $big == "Index" ? $url : "$big/$url";
                $sub_menu[] = array('url' => U("$url"), 'title' => $title);
            }
            return $sub_menu;
        } else {
            return $sub_menu[] = array('url' => '#', 'title' => "该菜单组不存在");
        }
    }

    private function menu() {
        $mun = array('admin_big_menu' => array(
                'index' => '首页',
                'member' => '用户管理',
//                'news' => '资讯管理',
                'statistics' => '统计中心',
                'game' => '游戏管理',
//        'Webinfo'=>'系统设置',
                'sys_data' => '数据管理',
                'access' => '权限管理'
            ),
            'admin_sub_menu' => array(
                'index' => array(
                    'myInfo' => '修改密码',
                    'cache' => '缓存清理',
//                    'add' => '新闻发布'
                ),
                'webinfo' => array(
                    'index' => '站点配置',
                    'setEmailConfig' => '邮箱配置',
                    'setSafeConfig' => '安全配置'
                ),
                'member' => array(
                    'index' => '注册用户列表',
                    'fuserlist' => '在线用户列表',
                ),
//                'news' => array(
//                    'index' => '新闻列表',
//                    'category' => '新闻分类管理',
//                    'add' => '发布新闻',
//                ),
                'news' => array(
                    'index' => '新闻列表',
                    'category' => '新闻分类管理',
                    'add' => '发布新闻',
                ),
                'statistics' => array(
                    'index' => '每日注册统计',
                    'ordertimelog' => '每日充值统计',
                    'orderlist' => '用户充值订单',
                    'userlogin' => '用户登录统计',
                    'usermlogin' => '每周用户登录流失',
                    'userdalc' => '时间段用户留存'
                ),
                'game' => array(
                    'index' => '房间列表',
                    'news' => '公告列表',
                    'mail' => '邮件列表',
                    'sendbean' => '发送金币',
                    'service' => '设置客服',
                    'shop' => '充值档次',
//                    'bankrecord' => '银行记录',
                    'economylist' => '金币记录'
                ),
                'sys_data' => array(
                    'index' => '数据库备份',
                    'restore' => '数据库导入',
                    'zipList' => '数据库压缩包',
                    'repair' => '数据库优化修复'
                ),
                'access' => array(
                    'index' => '后台用户',
                    'nodelist' => '节点管理',
                    'rolelist' => '角色管理',
                    'addadmin' => '添加管理员',
                    'addnode' => '添加节点',
                    'addrole' => '添加角色',
                )
            )
        );
        return $mun;
    }
    

}
