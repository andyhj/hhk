<?php
namespace Admin\Controller;
use Think\Controller;
class InitController extends Controller{
    public function __construct() {
        parent::__construct();
        $headers = $this->getHeaders();
        $access_token = session('accessToken');
        $path = $_SERVER["PATH_INFO"];
        //忽略列表
        if (in_array($path, $this->getIgnoreList())) {
            return true;
        }
//        if($access_token!=$headers["MALL-ACCESS-TOKEN"]||!$this->getUserInfo()){
//            $this->ajaxReturn(['status'=>301,'info'=>'登录超时']);
//        }
        if(!$access_token){
            $this->redirect('/user/index/');
        }
        
        $arr_breadcrumb_title_2 = [
            'add' => '新增',
            'edit' => '编辑',
            'view' => '查看详细',
            'index' => '列表',
            'list' => '列表',
        ];
        $arr_breadcrumb_title_1 = [
            'product' => '商品管理',
            'order' => '订单管理',
        ];
        $controller_name = strtolower(substr(__CONTROLLER__,1));
        $_action = explode('/', __ACTION__);
        $action_name = $_action[2];
        $breadcrumb_title_1 = $arr_breadcrumb_title_1[$action_name];
        $breadcrumb_title_2 = $arr_breadcrumb_title_2[$action_name];
        if(!$breadcrumb_title_1){
            $breadcrumb_title_1 = '其他';
        }
        if(!$breadcrumb_title_2){
            $breadcrumb_title_2 = '';
        }
        define('BREADCRUMB_TITLE_1', $breadcrumb_title_1);
        define('BREADCRUMB_TITLE_2', $breadcrumb_title_2);
        
    }
    private function getIgnoreList(){
        return [
            'user/login',
            'user/index',
            'user/logout',
            ];
    }
    public function getHeaders(){
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if ('HTTP_' == substr($key, 0, 5)) {
                $headers[str_replace('_', '-', substr($key, 5))] = $value;
            }
        }
        return $headers;
    }
    public function getRawBody()
    {
        $str_raw_body = file_get_contents('php://input');
        $arr_raw_body = json_decode($str_raw_body,true);
        if($arr_raw_body){
            return $arr_raw_body;
        }
        return $str_raw_body;
    }
    public function getUserInfo(){
        $obj_user = D("manage_user");
        return $obj_user->getUserInfo();
    }
}
