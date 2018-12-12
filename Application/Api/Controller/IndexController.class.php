<?php
namespace Api\Controller;
use Common\Common\Sockets;
use Common\Common\JuheRecharge;
use Common\Common\Redis;
class IndexController extends InitController {
    private $num = 0;
    private $u_arr=[];
    public function index(){
        echo "this is index";
    }
    /**
     * 更新用户代理级别
     */
    public function updAgency() {
        die("接口已停用");
        $user_id = $this->user_id;
        $m_user = D("user");
        $return_status = $m_user->updUserAgency($user_id);
        if($return_status===false){
            $json["status"] = 308;
            $json["info"] = "用户异常";
            $this->ajaxReturn($json);
        }
        if($return_status===4){
            $json["status"] = 309;
            $json["info"] = "用户已经是最高代理";
            $this->ajaxReturn($json);
        }
        if ($return_status === 1) {
            $json["status"] = 306;
            $json["info"] = "未达标";
            $this->ajaxReturn($json);
        } elseif ($return_status === 2) {
            $json["status"] = 200;
            $json["info"] = "更新成功";
            $this->ajaxReturn($json);
        } else {
            $json["status"] = 307;
            $json["info"] = "更新失败";
            $this->ajaxReturn($json);
        }
    }
    /**
     * 我的推广列表
     */
    public function agencyList(){
        $user_id = $this->user_id;
        $m_user = D("user");
        $user_agency = $m_user->agencyList($user_id);
        $user["up"] = $user_agency['user_up'];
        $user["down"] = $user_agency['user_down'];
        $json["status"] = 200;
        $json["info"] = "获取成功";
        $json["data"] = $user;
        $this->ajaxReturn($json);
    }

    /**
     * 我的代理等级
     */
    public function iagency(){
        $user_id = $this->user_id;
        $m_user = D("user");
        $user_agency_info = $m_user->getUserAgencyByUserId($user_id);  //查询当前用户等级
        if(!$user_agency_info){
            $json["status"] = 305;
            $json["info"] = "用户代理异常";
            $this->ajaxReturn($json);
        }
        $grade = $user_agency_info["grade"];
        $grade_up = 0;
        if($grade!=2){
            $grade_up =$grade+1;
        }
        $user_data["grade"] = $grade;
        $user_data["grade_up"] = $grade_up;
        if($grade==2){
            $json["status"] = 200;
            $json["info"] = "获取成功";
            $json["data"] = $user_data;
            $this->ajaxReturn($json);
        }
        $user_data["user_num"] = 0;
        $user_data["is_upgrade"] = false;
//        $sum = $m_user->getAgencySum($user_id);
//        $user_data["user_num"] = $sum;
//        if($user_agency_info["grade"]<3&&$m_user->getAgencyOrder($user_id,3)){
//            $user_data["is_upgrade"] = true;
//        }elseif(($sum>=5&&$user_agency_info["grade"]<2)||($user_agency_info["grade"]==3&&$sum>=2&&$this->getAgencyOrder($user_id,4))){
//            $user_data["is_upgrade"] = true;
//        }else{
//            $user_data["is_upgrade"] = false;
//        } 
        $json["status"] = 200;
        $json["info"] = "获取成功";
        $json["data"] = $user_data;
        $this->ajaxReturn($json);
    }

    /**
     * 我的推广
     */
    public function generalize(){
        $user_id = $this->user_id;
        $m_user = D("user");
        $user_url = 'http://'.$_SERVER['HTTP_HOST'].'/'.$user_id.'-0-0-0-0.html';
        $equative = $m_user->getAgencySum($user_id); //同级下属
        $user_direct_list = $m_user->getUserAgencySubordinates(['parent_id'=>$user_id]); 
        $direct = $user_direct_list&&!empty($user_direct_list)?count($user_direct_list):0; //直系下属
        $user_agency_list = $m_user->getUserAgencySubordinates(['superior_id'=>$user_id]); 
       if($user_agency_list&&!empty($user_agency_list)){
           $this->num = count($user_agency_list);
           foreach ($user_agency_list as $val) {
                   $this->coset($val);
           }
       }
        
       $model = M("user_agency");
       $ag_list = $model->select();
       $us_data = [];
       if($ag_list&&!empty($ag_list)){
           foreach ($ag_list as $value) {
               if($value["superior_id"]==$user_id&&$value["u_id"]!=$user_id){
                   $us_data[] = $value;
               }
           }
       }
       if($us_data&&!empty($us_data)){
           $this->num = count($us_data);
           foreach ($us_data as $value) {
               $this->cosets($ag_list,$value);
           }
       }
        $data["url"] = $user_url;  //我的推广链接
        $data["equative"] = $equative;  //同级下属
        $data["direct"] = $direct;      //直系下属
        $data["offshoot"] = $this->num;  //旁系下属
        $json["status"] = 200;
        $json["info"] = "获取成功";
        $json["data"] = $data;
        $this->ajaxReturn($json);
    }
    public function cosets($ag_list,$data){
        $us_data = [];
        if($ag_list&&!empty($ag_list)){
            foreach ($ag_list as $value) {
                if($value["superior_id"]==$data["u_id"]&&$value["u_id"]!=$data["u_id"]){
                    $us_data[] = $value;
                }
            }
        }
        if($us_data&&!empty($us_data)){
            $this->num = count($us_data);
            foreach ($us_data as $value) {
                $this->cosets($ag_list,$value);
            }
        }
    }
    public function coset($data){
        $m_user = D("user");
        $user_agency_list = $m_user->getUserAgencySubordinates(['superior_id'=>$data["u_id"]]); 
        if($user_agency_list&&!empty($user_agency_list)){
            $this->num += count($user_agency_list);
            foreach ($user_agency_list as $val) {
                $this->coset($val);
            }
        }
    }
    
    public function addOrder(){
        die("接口已停用");
        $user_id = $this->user_id;
        $amount = I("amount",0);
        $grade = I("grade",0);
        $ratio = 14285;
//        if(!$amount){
//            $json["status"] = 305;
//            $json["info"] = "金额不能等于0";
//            $this->ajaxReturn($json);
//        }
        
        if(!$amount&&$grade<3){
            $this->updAgency();die();
        }
        
        if($grade>4){
            $json["status"] = 306;
            $json["info"] = "代理等级不正确";
            $this->ajaxReturn($json);
        }
        if($grade==3&&$amount!=6000*$ratio){
            $json["status"] = 307;
            $json["info"] = "D级代理缴费金额为".(6000*$ratio);
            $this->ajaxReturn($json);
        }
        if($grade==4&&$amount!=50000*$ratio){
            $json["status"] = 309;
            $json["info"] = "E级代理缴费金额为".(50000*$ratio);
            $this->ajaxReturn($json);
        }
        $model_agency_order = D("agency_order");
        $order_info = $model_agency_order->getAgencyOrderOne(["u_id"=>$user_id,"grade"=>$grade]);
        if($order_info){
            $json["status"] = 310;
            $json["info"] = "已扣除过此代理等级钻石";
            $this->ajaxReturn($json);
        }
        $m_user = D("User");
        $game_user = $m_user->getGameUserOne(["uid"=>$user_id]);
        if($game_user["coinnum"]<$amount){
            $json["status"] = 313;
            $json["info"] = "钻石不足";
            $this->ajaxReturn($json);
        }
        $extra = array(
		'add' => array('type' => 'int','size' => 2,'value' => 2),
		'coin' => array ('type' => 'int','size' => 4,'value' => $amount),
		'type' => array('type' => 'int','size' => 2,'value' => 89),
		'cointype' => array('type' => 'int','size' => 2,'value' => 4)
	);
	$response = Sockets :: call('call_back', 10, 20, $user_id, $extra);
        if(!$response||$response["retcode"]!==0){
            $json["status"] = 312;
            $json["info"] = "扣除钻石失败";
            $this->ajaxReturn($json);
        }
        $order_number = $user_id.time();
        $data["u_id"] = $user_id;
        $data["order_number"] = $order_number;
        $data["pay_number"] = $order_number;
        $data["pay_type"] = 3;
        $data["amount"] = $amount;
        $data["grade"] = $grade;
        $data["status"] = 200;
        $data["add_date"] = time();
        $return_id = $model_agency_order->addAgencyOrder($data);
        if($return_id){
            $this->agencyPay($order_number);
            $json["status"] = 200;
            $json["info"] = "扣款成功";
            $this->ajaxReturn($json);
        }
        $json["status"] = 311;
        $json["info"] = "扣款失败";
        $this->ajaxReturn($json);
    }

    /**
     * 代理充值分佣
     */
    private function agencyPay($order_number){
        die("接口已停用");
        if (!$order_number) {
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $m_user = D("user");
        $agency_order_info = $m_user->getAgencyOrderBynumber($order_number); //查找订单状态
        if(!$agency_order_info){
            $json["status"] = 306;
            $json["info"] = "订单不存在或未支付";
            $this->ajaxReturn($json);
        }
        $status = $this->clearing($agency_order_info); //分佣
        if(!$status){
            $json["status"] = 307;
            $json["info"] = "代理支付分佣失败";
            $this->ajaxReturn($json);
        }
        $return_status = $m_user->updUserAgency($agency_order_info["u_id"]); //更新代理级别
        if($return_status===false){
            $json["status"] = 308;
            $json["info"] = "用户异常";
            $this->ajaxReturn($json);
        }
        if($return_status===4){
            $json["status"] = 309;
            $json["info"] = "用户已经是最高代理";
            $this->ajaxReturn($json);
        }
        if ($return_status === 1) {
            $json["status"] = 310;
            $json["info"] = "未达标";
            $this->ajaxReturn($json);
        } elseif ($return_status === 2) {
//            $json["status"] = 200;
//            $json["info"] = "更新成功";
//            $this->ajaxReturn($json);
            return true;
        } else {
            $json["status"] = 311;
            $json["info"] = "更新失败";
            $this->ajaxReturn($json);
        }
        
    }

    /**
     * 代理充值分成
     * @return boolean
     */
    private function clearing($data){
        if(empty($data)){
            return false;
        }
        $date = date("Ymd",$data["add_date"]);
        $table = "award_agency_dsc_". $date;
        $m_award = D("Award");
        $return_status = $m_award->createAwardAgencyDsc($table);  //创建当天收益详细表
        if($return_status){
            $where["order_number"] = $data["order_number"];
            $agency_info = $m_award->getAwardAgencyList("l_".$table,$where);  //查询数据
            if($agency_info&&!empty($agency_info)){
                return true;
            }
            $u_id = $data["u_id"];
            $amounts = $data["amount"]/14285;
            $return_sql = $this->awardSql($table,$u_id,$amounts,$data["order_number"]); //返回每级代理的收益sql
            $sql_arr = explode("VALUES", $return_sql);
            if($sql_arr[1]){
                $sql = substr($return_sql, 0, -1).";";
                $status = $m_award->addAwardAgencyDsc($sql); //添加收益详细记录
                if($status){
                    $m_award->countAward($table, strtotime($date),3); //统计总收入
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * 用户分成sql
     * @param type $table
     * @param type $u_id
     * @param type $amounts
     * @param type $type  //类型1为台费，2为充值
     * @return string
     */
    private function awardSql($table,$u_id,$amounts,$order_number){
        $m_user = D("user");
        $userID = $u_id;
        $total = $amounts;
        $ratio=[
            "1"=>0,
            "2"=>0,
            "3"=>40,
            "4"=>60,
        ];
        $grade = 0;  //代理等级
        $sql = "INSERT INTO l_".$table."(`u_id`,`amount`,`source_id`,`order_number`,`add_date`)VALUES";
        $into = 0;  //下级分成比例
        for($a=0;$a<4;$a++){ //六级代理
            $user_info = $m_user->getUserAgencyByUserId($userID);
            if(!$user_info||!$user_info["superior_id"]){
                break;
            }
            $grade = $user_info["grade"];
            $user_s = $this->getUserSuperiorInfo($user_info["superior_id"],$grade);
            if(!$user_s){
                break;
            }
            $str=",";
            if($user_s["grade"]==4||$user_s["grade"]<=$grade||!$user_s["superior_id"]){
                $str=";";
            }
            $gd = $user_s["grade"]; //当前代理等级
            if($gd>$grade && ($ratio[$gd]-$into)>0){
                $user_id = $user_s["u_id"];
                $amount = round(floatval($total*($ratio[$gd]-$into)/100), 2);
                $source_id = $u_id;
                $add_date = time();
                $sql .= "({$user_id},{$amount},{$source_id},'{$order_number}',{$add_date})".$str;
            }
            $into = $ratio[$gd];
            
            if($gd==4||!$user_s["superior_id"]){
                break;
            }else{
                $userID = $user_s["u_id"];
            }
        }
        return $sql;
    }
    /**
     * 查找父类信息
     * @param type $userID
     * @param type $grade
     * @return type
     */
    private function getUserSuperiorInfo($userID,$grade){
        $m_user = D("user");
        $user_s = $m_user->getUserAgencyByUserId($userID);
        if($user_s["grade"]<$grade){
            return $this->getUserSuperiorInfo($user_s["superior_id"],$grade);
        }else{
            return $user_s;
        }
    }
    /**
     * 获取城市地区
     */
    public function getProvince() {
        header("Content-type: text/html; charset=utf-8");
        $id_str = I("id", 0);
        $paths = APP_ROOT . "Public/cascade";
        $province = "province.text";
        if (!is_dir($paths)) {
            mkdir($paths, 0777, true);
        }
        $json_data = [];

        if ($id_str) {
            $id_arr = explode("-", $id_str);
            $id = $id_arr[0];
            $city_json = file_get_contents($paths . '/' . $id . ".text");
            if (!$city_json) {
                $json_data = file_get_contents('http://apis.map.qq.com/ws/district/v1/getchildren?key=NQZBZ-S3PWF-4GKJP-JN55C-ZTJ53-JMB3R&id=' . $id);
                $data = json_decode($json_data, true);
                if ($data["status"] == 0) {
                    $json_data = $data["result"][0];
                    file_put_contents($paths . '/' . $id . ".text", json_encode($data["result"][0]));
                }
            } else {
                $json_data = json_decode($city_json, true);
            }
        } else {
            $province_json = file_get_contents($paths . '/' . $province);
            if (!$province_json) {
                $json_data = file_get_contents('http://apis.map.qq.com/ws/district/v1/list?key=NQZBZ-S3PWF-4GKJP-JN55C-ZTJ53-JMB3R');
                $data = json_decode($json_data, true);
                if ($data["status"] == 0) {
                    $json_data = $data["result"][0];
                    file_put_contents($paths . '/' . $province, json_encode($data["result"][0]));
                }
            } else {
                $json_data = json_decode($province_json, true);
            }
        }
        if (empty($json_data)) {
            $echo_data["status"] = "fail";
            $echo_data["data"] = [];
            $this->ajaxReturn($echo_data);
        }

        $echo_data["status"] = "succeed";
        $echo_data["data"] = $json_data;
        $this->ajaxReturn($echo_data);
    }
    /**
     * 发送信息
     */
    public function sendSms(){
        $user_id = $this->user_id;
        $mobile = I("mobile",0);
        if(!$mobile){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $m_user = D("User");
        $user_info = $m_user->where(["phone"=>$mobile])->find();
        if($user_info){
            $json["status"] = 309;
            $json["info"] = "此号码已绑定其它账号";
            $this->ajaxReturn($json);
        }
        $m_redis = new Redis();
        $letters = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $code = get_rand_str(4, $letters);
        $appkey = '8673595580408cc1427eebff74c757e4'; //从聚合申请的话费充值appkey
        $openid = C('JH_CONFIG.OPENID'); //注册聚合账号就会分配的openid，在个人中心可以查看
        $juhe_recharge = new JuheRecharge($appkey,$openid);
        $user_code = $m_redis->get("sms".$mobile);
        if($user_code){
            $json["status"] = 306;
            $json["info"] = "请不要重复发送，验证码1分钟有效";
            $this->ajaxReturn($json);
        }
        $smsRes =$juhe_recharge->sendSms($mobile, 70695, $code); 
        add_log("juhe_sms.log", "index", "短信发送返回状态：". var_export($smsRes,true));
        if($smsRes['error_code'] == '0'){
            $m_redis->set("sms".$mobile,$code,60);
            $json["status"] = 200;
            $json["info"] = "发送成功";
            $this->ajaxReturn($json);
        }else{
            $json["status"] = 307;
            $json["info"] = $smsRes['reason'];
            $this->ajaxReturn($json);
        }
    }
    
    /**
     * 发送信息
     */
    public function loginsms(){
        $user_id = $this->user_id;
        $mobile = I("mobile",0);
        if(!$mobile){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->ajaxReturn($json);
        }
        $m_user = D("User");
        $where["phone"] = $mobile;
        $user_info = $m_user->where($where)->find();
        if(!$user_info){
            $json["status"] = 308;
            $json["info"] = "用户不存在";
            $this->ajaxReturn($json);
        }
        $m_redis = new Redis();
        $letters = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $code = get_rand_str(4, $letters);
        $appkey = '8673595580408cc1427eebff74c757e4'; //从聚合申请的话费充值appkey
        $openid = C('JH_CONFIG.OPENID'); //注册聚合账号就会分配的openid，在个人中心可以查看
        $juhe_recharge = new JuheRecharge($appkey,$openid);
        $user_code = $m_redis->get("sms".$mobile);
        if($user_code){
            $json["status"] = 306;
            $json["info"] = "请不要重复发送，验证码1分钟有效";
            $this->ajaxReturn($json);
        }
        $smsRes =$juhe_recharge->sendSms($mobile, 70694, $code); 
        add_log("juhe_sms.log", "index", "短信发送返回状态：". var_export($smsRes,true));
        if($smsRes['error_code'] == '0'){
            $m_redis->set("sms".$mobile,$code,60);
            $json["status"] = 200;
            $json["info"] = "发送成功";
            $this->ajaxReturn($json);
        }else{
            $json["status"] = 307;
            $json["info"] = $smsRes['reason'];
            $this->ajaxReturn($json);
        }
    }
}