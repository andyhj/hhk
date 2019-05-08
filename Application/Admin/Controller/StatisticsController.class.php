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
    /*下载文件*/
    public function export(){
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
        $sql = "SELECT pd.order_id, pd.s_time, pd.amount AS amount, p.fee, p.close_rate, (pd.amount * p.fee) + p.close_rate AS sxf, (pd.amount * 0.005) + 1 AS cb  FROM __PREFIX__plan_des pd RIGHT JOIN __PREFIX__plan p ON pd.p_id=p.id WHERE $where AND pd.order_state=1 AND pd.type=1 AND p.c_id=".$channel_id;
        $plan_des_list = $plan_des_model->query($sql);
        //p($data);
        // 创建csv下载
        $file_name = "tdlrtj_".$date.".csv";

        $content = "订单金额,费率,加收,手续费,通道成本,收益,日期"."\r\n";

        foreach ($plan_des_list as $key => $value) {

            $content .= $value['amount']
            .",".$value['fee']
            .",".$value['close_rate']
            .",".round($value['sxf'],2)
            .",".round($value['cb'],2)
            .",".round(($value['sxf']-$value['cb']),2)
            .",".date("Y-m-d H:i:s",$value['s_time']);

            $content .= "\r\n";
        }

        $content = iconv("utf-8","gbk",$content);
        //die($content) ;
        //p($content);
        // file_put_contents("./static/tdlrtj/".$file_name,$content);
        //$file_name = "tdlrtj_11.csv";
        // 下载
        header("location:doexport?file_name=$file_name");

        //die;
    }
}
    