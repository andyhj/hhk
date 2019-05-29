<?php
namespace Admin\Controller;

/**
 * 计划类
 *
 * @author Andy
 */
use Common\HeliPay\Heli;
use Common\WxApi\class_weixin_adv;
class PlanController extends CommonController{
    /**
     * 计划列表
     */
    public function index(){
        $current_page = (int)I('p',1);
        $search_key = I('search_key',"");
        $status = I('status', 0);
        $per_page = 15;//每页显示条数
        $plan_m = M("plan");
        $plan_des_m = M("plan_des");
        $channel_m = M("channel");
        $db_config = C("DB_CONFIG2");
        $customer_m = M("customer_info",$db_config["DB_PREFIX"],$db_config);
        $where = [];
        if($search_key){
            $condition['id'] = $search_key;
            $condition['loginId'] = $search_key;
            $condition['_logic'] = 'OR';
            // 把查询条件传入查询方法
            $customer_info = $customer_m->where($condition)->find();
            if($customer_info&&!empty($customer_info)){
                $where["u_id"] = $customer_info["id"];
            }else{
                $where["u_id"] = "";
            }
        }
        if ($status) {
            $where['status'] = $status;
        }
        $count = $plan_m->where($where)->count();
        $page = getpage($count, $per_page);
        $plan_list = $plan_m->where($where)->order('status desc,id desc')->page($current_page.','.$per_page)->select();
        $plan_arr = [];
        if($plan_list&&!empty($plan_list)){
            foreach ($plan_list as $val) {
                $bank_card_info = M("bank_card_".$val['c_code'])->where(["id"=>$val["bc_id"]])->find();
                $customer_info = $customer_m->where(["id"=>$val["u_id"]])->find();
                $channel_info = $channel_m->where(["id"=>$val["c_id"]])->find();
                $plan_des_list = $plan_des_m->where(["p_id"=>$val["id"]])->select();
                $val["user_loginid"] = "";  //登陆账号
                $val["user_name"] = "";    //商户名称
                $val["channel_name"] = "";    //通道名称
                $val["channel_start_time"] = "";    //任务开始时间
                $val["channel_end_time"] = "";    //任务结束时间
                $val["card_no"] = substr($bank_card_info['card_no'],-4);
                $val["bank_name"] = $bank_card_info['bank_name'];
                if($customer_info&&!empty($customer_info)){
                    $val["user_loginid"] = $customer_info["loginid"];  //登陆账号
                    $val["user_name"] = $customer_info["name"];    //商户名称
                }
                if($channel_info&&!empty($channel_info)){
                    $val["channel_name"] = $channel_info["name"];  //通道名称
                }
                if($plan_des_list&&!empty($plan_des_list)){
                    $val["channel_start_time"] = date("Y-m-d H:i:s",$plan_des_list[0]["s_time"]);    //任务开始时间
                    $val["channel_end_time"] = date("Y-m-d H:i:s",$plan_des_list[count($plan_des_list)-1]["s_time"]);    //任务结束时间
                }
                switch ($val["status"]) {
                    case 0:
                        $val["status_name"] = "计划取消";
                        break;
                    case 1:
                        $val["status_name"] = "计划完成";
                        break;
                    case 2:
                        $val["status_name"] = "用户终止计划";
                        break;
                    case 3:
                        $val["status_name"] = "执行中";
                        break;
                    case 4:
                        $val["status_name"] = "待执行";
                        break;
                    case 5:
                        $val["status_name"] = "计划中断";
                        break;
                    default:
                        $val["status_name"] = "";
                        break;
                }
                $plan_arr[] = $val;
            }
        }
        $this->assign("status", $status);
        $this->assign("page",$page->show());
        $this->assign("plan_list",$plan_arr);
        $this->assign("search_key",$search_key);
        $this->display();
    }
    public function info(){
        $p_id = I("id");
        if(!$p_id){
            $this->error("参数错误",U("plan/index"));die();
        }
        $plan_des_list = M("plan_des")->where(["p_id"=>$p_id])->select();
        if(!$plan_des_list||empty($plan_des_list)){
            $this->error("计划不存在",U("plan/index"));die();
        }
        $plan_des_arr = [];
        if($plan_des_list&&!empty($plan_des_list)){
            foreach ($plan_des_list as $val) {
                $val["type_name"] = "";
                if($val["type"]==1){
                    $val["type_name"] = "消费";
                }
                if($val["type"]==2){
                    $val["type_name"] = "还款";
                }
                switch ($val["order_state"]) {
                    case 1:
                        $val["status_name"] = "成功";
                        break;
                    case 2:
                        $val["status_name"] = "待执行";
                        break;
                    case 3:
                        $val["status_name"] = "执行中";
                        break;
                    case 4:
                        $val["status_name"] = "失败";
                        break;
                    default:
                        $val["status_name"] = "";
                        break;
                }
                $plan_des_arr[] = $val;
            }
        }
        $this->assign("plan_des_list",$plan_des_arr);
        $this->assign("bd_url",U("plan/reporder"));
        $this->display();
    }
    /**
     * 计划详情
     */
    public function plandes(){
        $current_page = (int)I('p',1);
        $search_key = I('search_key',"");
        $status = I('status', 0);
        $per_page = 15;//每页显示条数
        $where = [];
        if($search_key){
            $where_s['u_id'] = $search_key;
            $where_s['p_id'] = $search_key;
            $where_s['order_id'] = $search_key;
            $where_s['_logic'] = 'or';
            $where['_complex'] = $where_s;
        }
        if ($status) {
            $where['order_state'] = $status;
        }
        $plan_list = M("plan")->field('id')->where(["status"=>array('in','3,4,5')])->field('id')->select();
        $plan_des_arr = [];
        $count = 0;
        if($plan_list){
            $pid_arr = [];
            foreach ($plan_list as $value) {
                $pid_arr[] = $value['id'];
            }
            $pid_str = implode(",", $pid_arr);
            $where['p_id'] = array('in',$pid_str);
            $count = M("plan_des")->where($where)->count();
            $plan_des_list = M("plan_des")->where($where)->order('order_state desc,s_time asc')->page($current_page.','.$per_page)->select();
            if($plan_des_list&&!empty($plan_des_list)){
                foreach ($plan_des_list as $val) {
                    $val["type_name"] = "";
                    if($val["type"]==1){
                        $val["type_name"] = "消费";
                    }
                    if($val["type"]==2){
                        $val["type_name"] = "还款";
                    }
                    switch ($val["order_state"]) {
                        case 1:
                            $val["status_name"] = "成功";
                            break;
                        case 2:
                            $val["status_name"] = "待执行";
                            break;
                        case 3:
                            $val["status_name"] = "执行中";
                            break;
                        case 4:
                            $val["status_name"] = "失败";
                            break;
                        default:
                            $val["status_name"] = "";
                            break;
                    }
                    $plan_des_arr[] = $val;
                }
            }
        }
        $page = getpage($count, $per_page);
        $this->assign("plan_des_list",$plan_des_arr);
        $this->assign("status", $status);
        $this->assign("search_key",$search_key);
        $this->assign("page",$page->show());
        $this->assign("bd_url",U("plan/reporder"));
        $this->assign("select",U("plan/getWithdraw"));
        $this->display();
    }
    /**
     * 取消计划
     *
     * @return boolean
     */
    public function cancel(){
        $id = I("id");
        if(!$id){
            $this->error("参数错误",U("plan/index"));die();
        }
        $plan_info = M("plan")->where(["id"=>$id])->find();
        if(!$plan_info){
            $this->error("计划不存在",U("plan/index"));die();
        }
        $s = M("plan")->where(["id"=>$id])->save(["status"=>0]);
        $admin_info = $_SESSION['my_info'];
        $m_admin_log = M("admin_log");
        if($s){
            $info = "取消计划".$id."成功 ";
            $admin_log_data["a_id"] = $admin_info["aid"];
            $admin_log_data["a_username"] = $admin_info["email"];
            $admin_log_data["info"] = $info;
            $admin_log_data["add_time"] = time();
            $m_admin_log->add($admin_log_data);
            $this->success("取消计划成功",U("plan/index"));die();
        }
        $info = "取消计划失败";
        $admin_log_data["a_id"] = $admin_info["aid"];
        $admin_log_data["a_username"] = $admin_info["email"];
        $admin_log_data["info"] = $info;
        $admin_log_data["add_time"] = time();
        $m_admin_log->add($admin_log_data);
        $this->error($info,U("plan/index"));die();
    }
    //补单
    public function reporder(){
        $pd_id = I("post.id");  //计划详情id
        $session_name = "plan_rep_".$pd_id;
        if(session($session_name)){
            $json["status"] = 305;
            $json["info"] = "正在提交...";
            $this->returnJson($json);
        }
        session($session_name,1);
        $h = (int)date("H");
        if($h>20||$h<8){
            $json["status"] = 305;
            $json["info"] = "请在8-20点时间段补单";
            $this->returnJson($json,$session_name);
        }
        if(!$pd_id){
            $json["status"] = 305;
            $json["info"] = "参数错误";
            $this->returnJson($json,$session_name);
        }
        $plan_model = M("plan");
        $plan_des_model = M("plan_des");
        $plan_des_info = $plan_des_model->where(["id"=>$pd_id])->find();  //计划详情
        if(!$plan_des_info||empty($plan_des_info)){
            $json["status"] = 306;
            $json["info"] = "计划不存在";
            $this->returnJson($json,$session_name);
        }
        $plan_info = $plan_model->where(["id"=>$plan_des_info["p_id"]])->find(); //查询计划信息
        if(!$plan_info||empty($plan_info)){
            $json["status"] = 307;
            $json["info"] = "计划不存在";
            $this->returnJson($json,$session_name);
        }
        $bank_card_hlb_model = M("bank_card_".$plan_info["c_code"]);
        $bank_card_hlb_info = $bank_card_hlb_model->where(["id"=>$plan_info["bc_id"],"success"=>1])->find(); //查询银行卡信息
        if(!$bank_card_hlb_info||empty($bank_card_hlb_info)){
            $json["status"] = 308;
            $json["info"] = "银行卡不存在";
            $this->returnJson($json,$session_name);
        }
        $t_time = date("Y-m-d");
        $periods = $plan_info["periods"]; //总期数
        $p_time = date("Y-m", strtotime($plan_info["add_time"])); //获取计划月份
        $repayment = $p_time."-".$bank_card_hlb_info["repayment"]; //还款日
        if($bank_card_hlb_info["bill"]>$bank_card_hlb_info["repayment"]){
            $repayment = date("Y-m",strtotime("+1 month",strtotime($p_time)))."-".$bank_card_hlb_info["repayment"]; //还款日
        }
        //判断是否已过还款期
        if(strtotime($t_time)>= strtotime($repayment)){
            $json["status"] = 309;
            $json["info"] = "已过还款期";
            $this->returnJson($json,$session_name);
        }

        //判断是否最后一期，如果是 直接补单
        if($periods*2==$plan_des_info["num"]){
            if($plan_des_info["type"]==1){
                $json["status"] = 310;
                $json["info"] = "计划异常";
                $this->returnJson($json,$session_name);
            }
            $this->replacementOrder($plan_info, $plan_des_info,$bank_card_hlb_info,$session_name); //通道补单
        }

        $plan_des_next_info = $plan_des_model->where(["num"=>$plan_des_info["num"]+1,"u_id"=>$plan_des_info["u_id"],"p_id"=>$plan_des_info["p_id"]])->find();  //查询下一期计划
        if(!$plan_des_next_info||empty($plan_des_next_info)){
            $json["status"] = 312;
            $json["info"] = "计划异常";
            $this->returnJson($json,$session_name);
        }
        //判断当前补单时间是否小于下一期，如果是 直接补单
        if(time()<$plan_des_next_info["s_time"]){
            if($plan_des_next_info["s_time"]- time()<1800){ //当前时间距离下一期任务小于半小时，则下一期任务延期半小时
                $plan_des_model->where(["id"=>$plan_des_next_info["id"]])->save(["s_time"=>$plan_des_next_info["s_time"]+1800]);
            }
            $this->replacementOrder($plan_info, $plan_des_info,$bank_card_hlb_info,$session_name); //通道补单
        }

        //如果当前期数补单时间大于下一期时间，则要修改之后期数时间
        $residue_periods = $periods*2-$plan_des_info["num"]; //查询剩余期数
        $date_2 = $repayment;
        $d1 = strtotime($t_time);
        $d2 = strtotime($date_2);
        $days = round(($d2-$d1)/3600/24); //计算距离还款日天数
        $d_periods = ceil($residue_periods/4); //每天最多执行（代扣+代还）4次，计算出要几天
        if($days<$d_periods){
            $json["status"] = 313;
            $json["info"] = "距离还款日较近，请重新制定计划";
            $this->returnJson($json,$session_name);
        }
        $p_des_where["num"] = array('GT',$plan_des_info["num"]);
        $p_des_where["u_id"] = $plan_des_info["u_id"];
        $p_des_where["p_id"] = $plan_des_info["p_id"];
        $plan_des_list = $plan_des_model->where($p_des_where)->order('num asc')->select();
        if($plan_des_list&&!empty($plan_des_list)){
            $d = 0;
            $rt_s = 0;
            foreach ($plan_des_list as $k=>$pdl) {
                //查询下一期距离现在天数
                if($k==0){
                    $pd1 = strtotime($pdl["days"]);
                    $pd2 = strtotime($t_time);
                    $d = round(($pd2-$pd1)/3600/24)+1;
                }
                $pds_data["s_time"] = strtotime("+$d day",$pdl["s_time"]);
                $pds_data["days"] = date("Y-m-d",strtotime("+$d day",$pdl["s_time"]));
                $rt_s = $plan_des_model->where(["id"=>$pdl["id"]])->save($pds_data); //修改之后计划执行时间
            }
            if($rt_s){
                $this->replacementOrder($plan_info, $plan_des_info,$bank_card_hlb_info,$session_name); //通道补单
            }
        }
        $json["status"] = 318;
        $json["info"] = "补单重新制定计划失败";
        $this->returnJson($json,$session_name);
    }
    /**
     * 通道补单，如果新增通道，修改此方法即可
     * @param type $plan_info    //计划
     * @param type $plan_des_info //计划详情
     * @param type $bank_card_hlb_info //银行卡信息
     */
    private function replacementOrder($plan_info,$plan_des_info,$bank_card_hlb_info,$session_name=''){
        $json["status"] = 311;
        $json["info"] = "补单失败";
        if(!$plan_info||empty($plan_info)||!$plan_des_info||empty($plan_des_info)||!$bank_card_hlb_info||empty($bank_card_hlb_info)){
            $this->returnJson($json,$session_name);
        }
        $plan_model = M("plan");
        $plan_des_model = M("plan_des");
        $letters = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9','Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', 'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'Z', 'X', 'C', 'V', 'B', 'N', 'M'];
        $uid = $plan_des_info["u_id"];
        $pd_id = $plan_des_info["id"];
        $remedy_id = "BD".get_rand_str(6,$letters).$uid. time(); //补单订单号
        $upd_plan_des_data["remedy_id"] = $remedy_id;
        $upd_plan_des_data["remedy_time"] = time();
        if($plan_des_info["type"]==1){
            //执行代扣
            switch ($plan_info["c_code"]) {
                case "hlb":
                    require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/helipay/HeliPay.php";
                    $heli_pay = new Heli();
                    $arg = array(
                        'bindId'=>$bank_card_hlb_info['bind_id'],
                        'userId'=>$plan_info['u_id'],
                        'orderId'=>$remedy_id,
                        'orderAmount'=>$plan_des_info["amount"],
                        'terminalType'=>'IMEI',
                        'terminalId'=>'122121212121',
                        'queryUrl'=>HTTP_HOST."/index/callback/hlbPay",
                        'Code'=>'',
                    );
                    $hlb_dh = $heli_pay->bindingCardPay($arg);//执行代扣
                    if(!$hlb_dh){
                        $upd_plan_des_data["message"] = "补单失败";
                        $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                        $this->sendWxErrorMessage($plan_info, "消费补单失败", "消费");
                    }else{
                        if ($hlb_dh['rt2_retCode'] == '0000') {
                            $upd_plan_des_data["message"] = "提交成功,等待回调通知";
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
//                                $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>1]);
                            $json["status"] = 200;
                            $json["info"] = "提交成功,等待回调通知";
                        }elseif($hlb_dh['rt2_retCode'] == '0001'){
                            $upd_plan_des_data["message"] = $hlb_dh['rt3_retMsg'];
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            $json["status"] = 311;
                            $json["info"] = $hlb_dh['rt3_retMsg'];
                        }else{
                            $upd_plan_des_data["message"] = $hlb_dh['rt3_retMsg'];
                            $upd_plan_des_data["order_state"] = 4;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            $this->sendWxErrorMessage($plan_info, $hlb_dh['rt3_retMsg'], "消费");
                            $json["status"] = 311;
                            $json["info"] = $hlb_dh['rt3_retMsg'];
                        }
                    }
                    break;

                default:
                    break;
            }
        }elseif($plan_des_info["type"]==2){
            //执行代还
            switch ($plan_info["c_code"]) {
                case "hlb":
                    require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/helipay/HeliPay.php";
                    $heli_pay = new Heli();
                    $arg = array(
                        'userId' => $plan_info['u_id'],
                        'bindId' => $bank_card_hlb_info['bind_id'],
                        'order_id' => $remedy_id,
                        'amount' => $plan_des_info["amount"],
                    );
                    $hlb_dh = $heli_pay->creditWithdraw($arg);//执行代还
                    if(!$hlb_dh){
                        $upd_plan_des_data["message"] = "补单失败";
                        $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                        $this->sendWxErrorMessage($plan_info, "还款补单失败", "还款");
                    }else{
                        if ($hlb_dh['rt2_retCode'] == '0000') {
                            $upd_plan_des_data["message"] = "补单成功";
                            $upd_plan_des_data["order_state"] = 1;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            $plan_status = 3;
                            if((int)($plan_info["periods"]*2)==$upd_plan_des_data["num"]){
                                $plan_status = 1;
                            }
                            $plan_model->where(["id"=>$plan_des_info["p_id"]])->save(["status"=>$plan_status]);
                            $this->sendWxMessage($plan_info, $plan_des_info);
                            $json["status"] = 200;
                            $json["info"] = "补单成功";
                        }elseif($hlb_dh['rt2_retCode'] == '0001'){
                            $upd_plan_des_data["message"] = $hlb_dh['rt3_retMsg'];
                            $upd_plan_des_data["order_state"] = 3;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            $json["status"] = 311;
                            $json["info"] = $hlb_dh['rt3_retMsg'];
                        }else{
                            $upd_plan_des_data["message"] = $hlb_dh['rt3_retMsg'];
                            $upd_plan_des_data["order_state"] = 4;
                            $plan_des_model->where(["id"=>$pd_id])->save($upd_plan_des_data);
                            $this->sendWxErrorMessage($plan_info, $hlb_dh['rt3_retMsg'], "还款");
                            $json["status"] = 311;
                            $json["info"] = $hlb_dh['rt3_retMsg'];
                        }
                    }
                    break;

                default:
                    break;
            }
        }else{
            $json["status"] = 311;
            $json["info"] = "类型错误";
        }
        $this->returnJson($json,$session_name);
    }
    /**
     * 结算卡提现、信用卡还款查询
     *
     * @return void
     */
    public function getWithdraw(){
        $order_number = I("order_number");
        if(!$order_number){
            $json["status"] = 311;
            $json["info"] = "订单号错误";
            $this->returnJson($json);
        }
        require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/helipay/HeliPay.php";
        $heli_pay = new Heli();
        $arg = array(
            'order_id' => $order_number
        );
        $hlb_ye = $heli_pay->getWithdraw($arg);
        if($hlb_ye["rt2_retCode"]=="0000"){
            if($hlb_ye["rt7_orderStatus"]=="SUCCESS"){
                $json["status"] = 200;
                $json["info"] = "成功";
                $this->returnJson($json);
            }
            if($hlb_ye["rt7_orderStatus"]=="DOING"){
                $json["status"] = 312;
                $json["info"] = "处理中（".$hlb_ye["rt3_retMsg"]."）";
                $this->returnJson($json);
            }
            if($hlb_ye["rt7_orderStatus"]=="FAIL"){
                $json["status"] = 313;
                $json["info"] = "失败（".$hlb_ye["rt3_retMsg"]."）";
                $this->returnJson($json);
            }
            if($hlb_ye["rt7_orderStatus"]=="REFUND"){
                $json["status"] = 313;
                $json["info"] = "退款（".$hlb_ye["rt3_retMsg"]."）";
                $this->returnJson($json);
            }
        }
        $json["status"] = 313;
        $json["info"] = "失败";
        $this->returnJson($json);
    }
    /**
     * 查询合利宝余额
     *
     * @return void
     */
    public function balance()
    {
        $uid = I("uid");
        $accountBalance = 0;
        $accountFrozenBalance = 0;
        if($uid){
            require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/helipay/HeliPay.php";
            $heli_pay = new Heli();
            $arg = array(
                'userId' => $uid
            );
            $hlb_ye = $heli_pay->getAccountQuery($arg);
            if($hlb_ye["rt2_retCode"]=="0000"){
                $accountBalance = $hlb_ye["rt9_accountBalance"];
                $accountFrozenBalance = $hlb_ye["rt10_accountFrozenBalance"];
            }
        }
        $this->assign("accountBalance",$accountBalance);
        $this->assign("accountFrozenBalance",$accountFrozenBalance);
        $this->assign("uid",$uid);
        $this->display();
    }
/**
     * 公众号推送信息
     * @param type $plan_info
     * @param type $plan_des_info
     */
    private function sendWxMessage($plan_info,$plan_des_info,$trade_type="还款"){
        $db_config = C("DB_CONFIG2");
        $customer_m = M("cunstomer_wx_binding",$db_config["DB_PREFIX"],$db_config);
        $cunstomer_wx_binding_info = $customer_m->where(["user_id"=>$plan_info["u_id"],"state"=>1])->find();
        if($cunstomer_wx_binding_info&&!empty($cunstomer_wx_binding_info)){
            $type=2;
            if($trade_type=="消费"){
                $type=1;
            }
            require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            $msg_data["touser"] = $cunstomer_wx_binding_info["open_id"];
            $msg_data["template_id"] = "_nQ9Iqu1cT6z2aiHV2vvL366b3Qr4nFpfsU7GQ1cg4U";
            $msg_data["url"] = HTTP_HOST.'/index/plan/orderdes.html?id='.$plan_info["id"].'&type='.$type;
            $bank_card_model = M("bank_card_".$plan_info["c_code"]);
            $card_info = $bank_card_model->where(["id"=>$plan_info["bc_id"]])->find(); //查询银行卡信息
            $msg_data["data"] = array(
                "first"=>array(
                    "value"=>"《会还款》尊敬的用户您好，您尾号".substr($card_info["card_no"],-4)."的信用卡发生一笔交易。",
                    "color"=>""
                ),
                "tradeDateTime"=>array(
                    "value"=> date("Y-m-d H:i:s"),
                    "color"=>""
                ),
                "tradeType"=>array(
                    "value"=> $trade_type,
                    "color"=>""
                ),
                "curAmount"=>array(
                    "value"=> $plan_des_info["amount"],
                    "color"=>""
                ),
                "remark"=>array(
                    "value"=>"点击查看详情。",
                    "color"=>""
                )
            );
            $return_status = $weixin->send_user_message($msg_data);
            add_log("sendWxMessage.log", "admin", "公众号消息推送状态：". var_export($return_status, true));
        }
    }
    /**
     * 计划失败公众号推送信息
     * @param type $plan_info
     * @param type $plan_des_info
     */
    private function sendWxErrorMessage($plan_info,$title,$des){
        $db_config = C("DB_CONFIG2");
        $customer_m = M("cunstomer_wx_binding",$db_config["DB_PREFIX"],$db_config);
        $cunstomer_wx_binding_info = $customer_m->where(["user_id"=>$plan_info["u_id"],"state"=>1])->find();
        if($cunstomer_wx_binding_info&&!empty($cunstomer_wx_binding_info)){
            require_once $_SERVER['DOCUMENT_ROOT'] ."/Application/Common/Concrete/wxapi/example/weixin.api.php";
            $weixin = new class_weixin_adv();
            $msg_data["touser"] = $cunstomer_wx_binding_info["open_id"];
            $msg_data["template_id"] = "0rAKRWnyzyiW9ICydVIJj4W4NZAFR_PGNoM4XsUr92A";
            $msg_data["url"] = HTTP_HOST.'/index/plan/plandes.html?id='.$plan_info["id"];
            $msg_data["data"] = array(
                "first"=>array(
                    "value"=> "《会还款》计划失败提醒，请关注",
                    "color"=>""
                ),
                "keyword1"=>array(
                    "value"=> $des,
                    "color"=>""
                ),
                "keyword2"=>array(
                    "value"=> date("Y-m-d H:i:s"),
                    "color"=>""
                ),
                "keyword3"=>array(
                    "value"=> $title,
                    "color"=>""
                ),
                "keyword4"=>array(
                    "value"=>"请根据提醒内容处理计划",
                    "color"=>""
                ),
                "remark"=>array(
                    "value"=>"点击查看详情。",
                    "color"=>""
                )
            );
            $return_status = $weixin->send_user_message($msg_data);
            add_log("sendWxMessage.log", "admin", "计划失败公众号消息推送状态：". var_export($return_status, true));
        }
    }
}
