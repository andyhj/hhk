<?php
namespace Admin\Controller;
class StatisticsController extends CommonController{
    
    /*
     * 通道交易额统计
     */
    public function index(){
        $date = I('date');
        $channel_id = I('channel_id', 1);
        $plan_des_model = M("plan_des");
        $t_date = date("Y-m");
        $e_date = date("Y-m",strtotime("+1 month"));
        $where = '';
        if($date){
            $t_date = $date;
            $e_date = date("Y-m",strtotime("+1 month",strtotime($t_date)));
            $where  = ' pd.s_time>='.strtotime($t_date).' AND pd.s_time<'.strtotime($e_date);
        }else{
            $date = $t_date;
            $where  = ' pd.s_time>='.strtotime($t_date).' AND pd.s_time<'.strtotime($e_date);
        }
        $sql = "SELECT pd.days,SUM(pd.amount) AS amount,SUM((pd.amount * p.fee)+p.close_rate) sxf ,SUM((pd.amount * 0.005)+1) cb  FROM __PREFIX__plan_des pd RIGHT JOIN __PREFIX__plan p ON pd.p_id=p.id WHERE $where AND pd.order_state=1 AND pd.type=1 AND p.c_id=".$channel_id." GROUP BY pd.days";
        $plan_des_list = $plan_des_model->query($sql);

        $order_r_amount_sql = "SELECT pd.days,SUM(pd.amount) AS amount,SUM((pd.amount * p.fee)+p.close_rate) sxf ,SUM((pd.amount * 0.005)+1) cb  FROM __PREFIX__plan_des pd RIGHT JOIN __PREFIX__plan p ON pd.p_id=p.id WHERE $where AND pd.order_state=1 AND pd.type=1 AND p.c_id=".$channel_id;  //总额
        $count_amount = $plan_des_model->query($order_r_amount_sql);
        
        $this->assign("channel_list", M("channel")->order("id desc")->select());
        $this->assign('plan_des_list',$plan_des_list);
        $this->assign('count_amount',!empty($count_amount)?current($count_amount):'');
        $this->assign("channel_id", $channel_id);
        $this->assign("date", $date);
        $this->display();
    }
    /**
     * 通道提现记录
     */
    public function extractlog(){
        $channel_id = I('channel_id', 0);
        $current_page = (int)I('p',1);
        $per_page = 15;//每页显示条数
        $channel_extract_log_m = M("channel_extract_log");
        $where=[];
        if ($channel_id) {
            $where['c_id'] = $channel_id;
        }
        $channel_extract_log_list = $channel_extract_log_m->where($where)->order("add_time DESC")->page($current_page . ',' . $per_page)->select();
        $count = $channel_extract_log_m->where($where)->count();
        $page = getpage($count, $per_page); // 实例化分页类 传入总记录数和每页显示的记录数
        $order_r_amount_sql = "SELECT sum(amount) AS amount FROM __PREFIX__channel_extract_log";  //总额
        
        if ($channel_id) {
            $order_r_amount_sql .= " WHERE c_id=".$channel_id;
        }
        $r_amounts = current($channel_extract_log_m->query($order_r_amount_sql));
        $c_amount = isset($r_amounts["amount"])?$r_amounts["amount"]:0;
        
        $this->assign("channel_list", M("channel")->order("id desc")->select());
        $this->assign('c_amount',$c_amount);
        $this->assign('page',$page);// 赋值分页输出
        $this->assign('extractList',$channel_extract_log_list);
        $this->assign("channel_id", $channel_id);
        $this->display();
    }
    
    /*
     * 添加通道提现记录
     */
    public function addExtractlog(){
        $data["c_id"] = I("c_id",""); //通道id
        $data["amount"] = I("amount",""); //金额
        $error = "";
        if(is_post()){
            if(!$data["c_id"] || !$data["amount"]){
                $error = "参数不完整错误";
            }else{
                $channel_info = M('channel')->where(["id"=>$data["c_id"]])->find();
                $data["c_name"] = $channel_info['name']; 
                $r_id = M("channel_extract_log")->add($data);
                $admin_info = $_SESSION['my_info'];
                $m_admin_log = M("admin_log");
                if($r_id){
                    M('channel_tran_amount')->where(["c_id"=>$data["c_id"]])->setInc('extract',$data["amount"]); // 提现总额累加
                    $info = "添加通道提现记录".$r_id."成功 ";
                    $admin_log_data["a_id"] = $admin_info["aid"];
                    $admin_log_data["a_username"] = $admin_info["email"];
                    $admin_log_data["info"] = $info;
                    $admin_log_data["add_time"] = time();
                    $m_admin_log->add($admin_log_data);
                    $this->success("添加成功",U("statistics/extractlog"));die();
                }
                $info = "添加通道提现记录失败";
                $admin_log_data["a_id"] = $admin_info["aid"];
                $admin_log_data["a_username"] = $admin_info["email"];
                $admin_log_data["info"] = $info;
                $admin_log_data["add_time"] = time();
                $m_admin_log->add($admin_log_data);
                $error = $info;
            }
        }
        $this->assign("channel_list", M("channel")->order("id desc")->select());
        $this->assign("error",$error);        
        $this->assign("data",$data);
        $this->display();
    }
    /*
     * 更新每日时间段充值统计
     */
    public function updordertime(){
        $date_time = I('date_time');
        $return_url = U('statistics/ordertimelog');
        $m = M("order_time");
        if(!$date_time){
            $this->error("时间不能为空",$return_url);
        }else{
            $s_date = strtotime(date("Ymd", strtotime($date_time)));
            $n_date = $s_date+86400;
            $amount_sql = "SELECT id,amount,add_date FROM __PREFIX__order WHERE add_date>={$s_date} AND add_date<{$n_date} AND status=200 AND type in(1,2)";  //充值金额
            $user_sql = "SELECT count(DISTINCT u_id) AS user_num FROM __PREFIX__order WHERE add_date>={$s_date} AND add_date<{$n_date} AND status=200 AND type in(1,2)";  //充值人数
            $amount_list = $m->query($amount_sql);
            $user_num = current($m->query($user_sql));
            $hours_00=0.00;$hours_01=0.00;$hours_02=0.00;$hours_03=0.00;$hours_04=0.00;$hours_05=0.00;$hours_06=0.00;$hours_07=0.00;$hours_08=0.00;$hours_09=0.00;$hours_10=0.00;$hours_11=0.00;
            $hours_12=0.00;$hours_13=0.00;$hours_14=0.00;$hours_15=0.00;$hours_16=0.00;$hours_17=0.00;$hours_18=0.00;$hours_19=0.00;$hours_20=0.00;$hours_21=0.00;$hours_22=0.00;$hours_23=0.00;
            $amount_num=0.00;
            if($amount_list&&!empty($amount_list)){
                foreach ($amount_list as $value) {
                    $d = date("H",$value["add_date"]);
                    $amount_num += $value["amount"];
                    if($d==="00"){
                        $hours_00 += $value["amount"];
                    }
                    if($d==="01"){
                        $hours_01 += $value["amount"];
                    }
                    if($d==="02"){
                        $hours_02 += $value["amount"];
                    }
                    if($d==="03"){
                        $hours_03 += $value["amount"];
                    }
                    if($d==="04"){
                        $hours_04 += $value["amount"];
                    }
                    if($d==="05"){
                        $hours_05 += $value["amount"];
                    }
                    if($d==="06"){
                        $hours_06 += $value["amount"];
                    }
                    if($d==="07"){
                        $hours_07 += $value["amount"];
                    }
                    if($d==="08"){
                        $hours_08 += $value["amount"];
                    }
                    if($d==="09"){
                        $hours_09 += $value["amount"];
                    }
                    if($d==="10"){
                        $hours_10 += $value["amount"];
                    }
                    if($d==="11"){
                        $hours_11 += $value["amount"];
                    }
                    if($d==="12"){
                        $hours_12 += $value["amount"];
                    }
                    if($d==="13"){
                        $hours_13 += $value["amount"];
                    }
                    if($d==="14"){
                        $hours_14 += $value["amount"];
                    }
                    if($d==="15"){
                        $hours_15 += $value["amount"];
                    }
                    if($d==="16"){
                        $hours_16 += $value["amount"];
                    }
                    if($d==="17"){
                        $hours_17 += $value["amount"];
                    }
                    if($d==="18"){
                        $hours_18 += $value["amount"];
                    }
                    if($d==="19"){
                        $hours_19 += $value["amount"];
                    }
                    if($d==="20"){
                        $hours_20 += $value["amount"];
                    }
                    if($d==="21"){
                        $hours_21 += $value["amount"];
                    }
                    if($d==="22"){
                        $hours_22 += $value["amount"];
                    }
                    if($d==="23"){
                        $hours_23 += $value["amount"];
                    }
                }
            }
            $data["date_time"] = $s_date;
            $data["hours_00"] = $hours_00;
            $data["hours_01"] = $hours_01;
            $data["hours_02"] = $hours_02;
            $data["hours_03"] = $hours_03;
            $data["hours_04"] = $hours_04;
            $data["hours_05"] = $hours_05;
            $data["hours_06"] = $hours_06;
            $data["hours_07"] = $hours_07;
            $data["hours_08"] = $hours_08;
            $data["hours_09"] = $hours_09;
            $data["hours_10"] = $hours_10;
            $data["hours_11"] = $hours_11;
            $data["hours_12"] = $hours_12;
            $data["hours_13"] = $hours_13;
            $data["hours_14"] = $hours_14;
            $data["hours_15"] = $hours_15;
            $data["hours_16"] = $hours_16;
            $data["hours_17"] = $hours_17;
            $data["hours_18"] = $hours_18;
            $data["hours_19"] = $hours_19;
            $data["hours_20"] = $hours_20;
            $data["hours_21"] = $hours_21;
            $data["hours_22"] = $hours_22;
            $data["hours_23"] = $hours_23;
            $data["amount_num"] = $amount_num;
            $data["user_num"] = $user_num["user_num"]?$user_num["user_num"]:0;
            $data["upd_time"] = time();
            
            $reg_info = $m->where(["date_time"=>$data["date_time"]])->find();
            if($reg_info){
                $m->where(["date_time"=>$data["date_time"]])->delete();
            }
            $return_status = $m->add($data);
            $admin_info = $_SESSION['my_info'];
            $m_admin_log = M("admin_log");
            if($return_status){
                $info = "更新每日充值统计 ".date("Ymd", $s_date) ." 成功";
                $admin_log_data["a_id"] = $admin_info["aid"];
                $admin_log_data["a_username"] = $admin_info["email"];
                $admin_log_data["info"] = $info;
                $admin_log_data["add_time"] = time();
                $m_admin_log->add($admin_log_data);
                $this->success("更新成功",$return_url);
            }else{
                $info = "更新每日充值统计 ".date("Ymd", $s_date) ." 失败";
                $admin_log_data["a_id"] = $admin_info["aid"];
                $admin_log_data["a_username"] = $admin_info["email"];
                $admin_log_data["info"] = $info;
                $admin_log_data["add_time"] = time();
                $m_admin_log->add($admin_log_data);
                $this->error("更新成功",$return_url);
            }
        }
    }
    
    /**
     * 用户登录数据统计
     */
    public function userlogin(){
        $search_key = I('search_key');
        $current_page = (int)I('p',1);
        $date = date("Y-m-d");
        if($search_key){
            $date = $search_key;
        }else{
            $search_key = $date;
        }
        $where = [];
        $login_list = [];
        $showPage = '';
        $date = date("Ymd", strtotime($date));
        $table_name = "user_login_log_".$date;
        $s_table = "show tables like '__PREFIX__". $table_name."'";  //查询表是否存在
        $show_table = M()->query($s_table);
        if($show_table&&!empty($show_table)){
            $m = M($table_name); 
            $count = $m->where($where)->count();
            $per_page = 15;//每页显示条数
            $page       = getpage($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
            $showPage       = $page->show();// 分页显示输出
            $login_list = $m->where($where)->order("id DESC")->page($current_page.','.$per_page)->select();
        }
        $this->assign("login_list", $login_list);
        $this->assign("page", $showPage);
        $this->assign("search_key", $search_key);
        $this->display("userlogin");
    }
    /**
     * 每周用户登录流失数
     */
    public function usermlogin(){
        $view_datas['search_key'] = $search_key = I('dateTime');
        $sdefaultDate = date("Y/m/d"); 
    	$first=0; 
    	$w=date('w',strtotime($sdefaultDate)); 
    	$week_start=date('Y/m/d',strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days')); 
    	$week_end=date('Y/m/d',strtotime("$week_start +6 days"));
        if($search_key){
            $dateTime = explode("-",$search_key);
            $week_start=trim($dateTime[0]); 
            $week_end=trim($dateTime[1]); 
        }
        $dateStart = strtotime($week_start.' 00:00:00');
        $dateEnd = strtotime($week_end.' 23:59:59');
        $szStatrt = $dateStart-604800;
        $sszStatrt = $dateStart-604800-604800;

        $arr['s_opentime'] = $week_start.'<br> - <br>'.$week_end;
        $dl_user_where="";
        $dl_user_where1="";
        //本周注册用户
        $regsql = "select count(*) as userreg from __PREFIX__user where  regtime>=$dateStart and regtime<=$dateEnd ".$dl_user_where;
//         echo $regsql;
        $user_list = current(M()->query($regsql));
        $arr['userReg'] = 0;
        if($user_list){
            $arr['userReg'] = empty($user_list['userreg']) ? 0 : $user_list['userreg'];
        }

        //活跃用户
        $array=$array1=[];
        $hysql = "SELECT u_id from __PREFIX__user_login_log where add_date>=$szStatrt and add_date<{$dateStart} {$dl_user_where1}  GROUP BY u_id";
        $hy_login_log = M()->query($hysql);
        if($hy_login_log&&!empty($hy_login_log)){
            foreach ($hy_login_log as $hll) {
                $array[] = $hll['u_id'];
            }
        }
        $hysql1 = "SELECT u_id from __PREFIX__user_login_log where add_date>=$dateStart and add_date<=$dateEnd {$dl_user_where1} GROUP BY u_id";
        $hy_login_log1 = M()->query($hysql1);
        if($hy_login_log1&&!empty($hy_login_log1)){
            foreach ($hy_login_log1 as $hll) {
                $array1[] = $hll['u_id'];
            }
        }
        $repeat_arr = array_intersect( $array, $array1 );
        $arr['hylogUser'] = !empty($repeat_arr)?count($repeat_arr):0;

        //忠诚用户
        $zcsql1 = "SELECT u_id from __PREFIX__user_login_log where add_date>=$szStatrt and add_date<$dateStart {$dl_user_where1} GROUP BY u_id";
        $query1 = M()->query($zcsql1);
        $zcarr1 = $zcarr2 = $zcarr3 = [];
        if($query1&&!empty($query1)){
            foreach ($query1 as $val) {
                $zcarr1[] = $val['u_id'];
            }
        }
        $zcsql2 = "SELECT u_id from __PREFIX__user_login_log where add_date>=$dateStart and add_date<=$dateEnd {$dl_user_where1}  GROUP BY u_id";
        $query2 = M()->query($zcsql2);
        if($query2&&!empty($query2)){
            foreach ($query2 as $val) {
                $zcarr2[] = $val['u_id'];
            }
        }
        $zcsql3 = "SELECT u_id from __PREFIX__user_login_log where add_date>=$sszStatrt and add_date<$szStatrt {$dl_user_where1} GROUP BY u_id";
        $query3 = M()->query($zcsql3);
        if($query3&&!empty($query3)){
            foreach ($query3 as $val) {
                $zcarr3[] = $val['u_id'];
            }
        }
        $zcrepeat_arr = array_intersect($zcarr1,$zcarr2,$zcarr3);
        $arr['zclogUser'] = !empty($zcrepeat_arr)?count($zcrepeat_arr):0;

        $db_config = C("DB_CONFIG2");
        $game_m = M("",$db_config["DB_PREFIX"],$db_config);

        //新增流失用户
        $newlssql = "select count(*) as newls from __PREFIX__user where uid>100000 and lasttime<$dateStart and lasttime>=$szStatrt".$dl_user_where;
        // echo $regsql;
        $ls_user = current($game_m->query($newlssql));
        $arr['newls'] = 0;
        if($ls_user&&!empty($ls_user)){
            $arr['newls'] = empty($ls_user['newls']) ? 0 : $ls_user['newls'];
        }

        //近期流失用户
        $jqlssql = "select count(*) as jqls from __PREFIX__user where uid>100000 and lasttime<$szStatrt".$dl_user_where;
        // echo $regsql;
        $jqls_user = current($game_m->query($jqlssql));
        $arr['jqls'] = 0;
        if($jqls_user&&!empty($jqls_user)){
            $arr['jqls'] = empty($jqls_user['jqls']) ? 0 : $jqls_user['jqls'];
        }

        //长期流失用户
        $cqlssql = "select count(*) as cqls from __PREFIX__user where uid>100000 and lasttime<$sszStatrt".$dl_user_where;
        // echo $regsql;
        $cqls_user = current($game_m->query($cqlssql));
        $arr['cqls'] = 0;
        if($cqls_user&&!empty($cqls_user)){
            $arr['cqls'] = empty($cqls_user['cqls']) ? 0 : $cqls_user['cqls'];
        }
        $login_list = $arr;
        $view_datas['dateTime'] = $week_start.' - '.$week_end;
        $view_datas['login_list'] = $login_list;
        $this->assign("datas", $view_datas);
        $this->display("usermlogin");
    }
    /**
     * 时间段用户留存
     */
    public function userdalc(){
        $view_datas['search_key'] = $search_key = I('dateTime');
        $date = date("Y-m-d"); 
        $dl_user_where="";
        $dl_user_where1="";
        if($search_key){
            $date = $search_key;
        }
        $time_0 = strtotime($date);
        $time_1 = $time_0+86400; //新服开始第二天开始
        $time_2 = $time_1+86400; //新服开始第三天开始
        $time_3 = $time_2+86400; //新服开始第四天开始
        $time_4 = $time_3+86400; //新服开始第五天开始
        $time_5 = $time_4+86400; //新服开始第六天开始
        $time_6 = $time_5+86400; //新服开始第七天开始
        $time_7 = $time_6+86400; //新服开始第八天开始
        $time_15s = $time_0+1296000; //新服开始第15天开始
        $time_15e = $time_15s+86400; //新服开始第15天结束
        $time_30s = $time_0+2592000; //新服开始第30天开始
        $time_30e = $time_30s+86400; //新服开始第30天结束
        $arr['s_opentime'] = date('Y-m-d',$time_0);
        //###############开服当天###################
        //开服当天注册登陆人数：指当天注册并于当天第一次进入游戏服的人数
        $regsql = "select count(*) as num from __PREFIX__user where regtime>=$time_0 and regtime<$time_1".$dl_user_where;
        $user_list = current(M()->query($regsql));
        $arr['gl_onereglogin'] = 0;
        if($user_list){
            $arr['gl_onereglogin'] = empty($user_list['num']) ? 0 : $user_list['num'];
        }
        //当天注册2登人数：指开服当天注册登陆并于开服第2天有登陆游戏的人数
        $sql2 = "select count(DISTINCT u_id) as num from __PREFIX__user_login_log where reg_date>=$time_0 and reg_date<$time_1 and add_date>=$time_1 and add_date<$time_2 {$dl_user_where1}";
        $login_log_two = current(M()->query($sql2));
        $arr['gl_onelog_two'] = empty($login_log_two['num']) ? 0.00 : sprintf("%.2f",($login_log_two['num']/$arr['gl_onereglogin'])*100);

        //开服当天注册3登人数：指开服当天注册登陆并于开服第3天有登陆游戏的人数
        $sql3 = "select count(DISTINCT u_id) as num from __PREFIX__user_login_log where reg_date>=$time_0 and reg_date<$time_1 and add_date>=$time_2 and add_date<$time_3 {$dl_user_where1}";
        $login_log_three = current(M()->query($sql3));
        $arr['gl_onelog_three'] = empty($login_log_three['num']) ? 0.00 : sprintf("%.2f",($login_log_three['num']/$arr['gl_onereglogin'])*100);

        //开服当天注册4登人数：指开服当天注册登陆并于开服第4天有登陆游戏的人数
        $sql4 = "select count(DISTINCT u_id) as num from __PREFIX__user_login_log where reg_date>=$time_0 and reg_date<$time_1 and add_date>=$time_3 and add_date<$time_4 {$dl_user_where1}";
        $login_log_four = current(M()->query($sql4));
        $arr['gl_onelog_four'] = empty($login_log_four['num']) ? 0.00 : sprintf("%.2f",($login_log_four['num']/$arr['gl_onereglogin'])*100);

        //开服当天注册5登人数：指开服当天注册登陆并于开服第5天有登陆游戏的人数
        $sql5 = "select count(DISTINCT u_id) as num from __PREFIX__user_login_log where reg_date>=$time_0 and reg_date<$time_1 and add_date>=$time_4 and add_date<$time_5 {$dl_user_where1}";
        $login_log_five = current(M()->query($sql5));
        $arr['gl_onelog_five'] = empty($login_log_five['num']) ? 0.00 : sprintf("%.2f",($login_log_five['num']/$arr['gl_onereglogin'])*100);

        //开服当天注册6登人数：指开服当天注册登陆并于开服第6天有登陆游戏的人数
        $sql6 = "select count(DISTINCT u_id) as num from __PREFIX__user_login_log where reg_date>=$time_0 and reg_date<$time_1 and add_date>=$time_5 and add_date<$time_6 {$dl_user_where1}";
        $login_log_six = current(M()->query($sql6));
        $arr['gl_onelog_six'] = empty($login_log_six['num']) ? 0.00 : sprintf("%.2f",($login_log_six['num']/$arr['gl_onereglogin'])*100);

        //开服当天注册7登人数：指开服当天注册登陆并于开服第7天有登陆游戏的人数
        $sql7 = "select count(DISTINCT u_id) as num from __PREFIX__user_login_log where reg_date>=$time_0 and reg_date<$time_1 and add_date>=$time_6 and add_date<$time_7 {$dl_user_where1}";
        $login_log_seven = current(M()->query($sql7));
        $arr['gl_onelog_seven'] = empty($login_log_seven['num']) ? 0.00 : sprintf("%.2f",($login_log_seven['num']/$arr['gl_onereglogin'])*100);

        //开服当天注册15登人数：指开服当天注册登陆并于开服第15天有登陆游戏的人数
        $sql15 = "select count(DISTINCT u_id) as num from __PREFIX__user_login_log where reg_date>=$time_0 and reg_date<$time_1 and add_date>=$time_15s and add_date<$time_15e {$dl_user_where1}";
        $login_log_fifteen = current(M()->query($sql15));
        $arr['gl_onelog_fifteen'] = empty($login_log_fifteen['num']) ? 0.00 : sprintf("%.2f",($login_log_fifteen['num']/$arr['gl_onereglogin'])*100);

        //开服当天注册30登人数：指开服当天注册登陆并于开服第30天有登陆游戏的人数
        $sql30 = "select count(DISTINCT u_id) as num from __PREFIX__user_login_log where reg_date>=$time_0 and reg_date<$time_1 and add_date>=$time_30s and add_date<$time_30e {$dl_user_where1}";
        $login_log_thirty = current(M()->query($sql30));
        $arr['gl_onelog_thirty'] = empty($login_log_thirty['num']) ? 0.00 : sprintf("%.2f",($login_log_thirty['num']/$arr['gl_onereglogin'])*100);
        $view_datas['dateTime'] = $date;
        $view_datas['login_list'] = $arr;
        $this->assign("datas", $view_datas);
        $this->display("userdalc");
    }
}
    