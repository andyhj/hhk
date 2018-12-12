<?php
namespace Common\Model;
use Think\Model;
class OrderModel extends Model{
    const ORDER_STATUS_WAITING_PAY = 100; //待付款
    const ORDER_STATUS_WAITTING_SEND = 200; //已支付
    public function getStatus(){
        return [
            self::ORDER_STATUS_WAITING_PAY => '待付款',
            self::ORDER_STATUS_WAITTING_SEND => '已支付',
        ];
    }
    /**
     * 返回订单状态名称
     * @param type $id
     * @return string
     */
    public function getStatusText($id){
        $arr_status = $this->getStatus();
        return $arr_status[$id];
    }
    /**
     * 获取订单列表数据
     * 
     */
    public function getList($where=[],$current_page=1,$per_page=20,$order=""){
        return $this->where($where)->order($order)->page($current_page.','.$per_page)->select();
    }
    /**
     * 获取总条数
     * @param type $where
     */
    public function getCount($where=[]){
        return (int)$this->where($where)->count();
    }
    /**
     * 获取总条数
     * @param type $where
     */
    public function getPayCount($where=[]){
        $model = M("pay_order");
        return (int)$model->where($where)->count();
    }

    /**
     * 获取单条数据
     * @param type $id
     * @return boolean
     */
    public function getOne($id){
        if(!$id){
            return false;
        }
        return $this->where(["id"=>$id])->find();
    }
    
    /**
     * 根据订单号获取单条数据
     * @param type $order_number
     * @return boolean
     */
    public function getOneByOrderNumber($order_number){
        if(!$order_number){
            return false;
        }
        return $this->where(["order_number"=>$order_number])->find();
    }
    /**
     * 根据退款订单号获取单条数据
     * @param type $return_number
     * @return boolean
     */
    public function getOneByReturnNumber($return_number){
        if(!$return_number){
            return false;
        }
        return $this->where(["return_number"=>$return_number])->find();
    }
    /**
     * 根据条件获取单条数据
     * @param type $where
     * @return boolean
     */
    public function getOneByWhere($where){
        if(!$where){
            return false;
        }
        return $this->where($where)->find();
    }
    /**
     * 修改订单
     * @param type $where
     * @param type $data
     * @return boolean
     */
    public function updOrder($where,$data){
        if(empty($where)||empty($data)){
            return false;
        }
        return $this->where($where)->save($data);
    }
    
    /**
     * 查询支付单号
     * @param type $pay_number
     * @return boolean
     */
    public function getPayOrderOne($pay_number){
        if(!$pay_number){
            return false;
        }
        $pay_order = M("pay_order");
        return $pay_order->where(["pay_number"=>$pay_number])->find();
    }
    /**
     * 查询支付单号
     * @param type $where
     * @return boolean
     */
    public function getPayOrderOneByWhere($where){
        if(empty($where)){
            return false;
        }
        $pay_order = M("pay_order");
        return $pay_order->where($where)->find();
    }
    /**
     * 修改支付单号
     * @param type $where
     * @param type $data
     * @return boolean
     */
    public function updPayOrder($where,$data){
        if(empty($where)||empty($data)){
            return false;
        }
        $pay_order = M("pay_order");
        return $pay_order->where($where)->save($data);
    }
    /**
     * 添加订单
     * @param type $data
     * @return boolean
     */
    public function addOrder($data){
        add_log("callback.log", "pay", "添加订单:". var_export($data, true));
        if(empty($data)){
            return false;
        }
        return $this->add($data);
    }
    /**
     * 充值商城
     * @param type $where
     * @return boolean
     */
    public function getGameShopOne($where){
        if(!$where||empty($where)){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("shop",$db_config["DB_PREFIX"],$db_config);
        return $model->where($where)->find();
    }
    /**
     * 充值商城
     * @param type $where
     * @return boolean
     */
    public function getGameShopList($where,$order="price ASC"){
        if(!$where||empty($where)){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("shop",$db_config["DB_PREFIX"],$db_config);
        return $model->where($where)->order($order)->select();
    }
    /**
     * 游戏兑换商城订单
     * @param type $where
     * @param type $current_page
     * @param type $per_page
     * @param type $order
     * @return boolean
     */
    public function getGameOrder($where=[],$current_page=1,$per_page=20,$order=""){
        if(!$where||empty($where)){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("order",$db_config["DB_PREFIX"],$db_config);
        return $model->where($where)->order($order)->page($current_page.','.$per_page)->select();
    }
    /**
     * 游戏兑换商城订单
     * @param type $where
     * @param type $current_page
     * @param type $per_page
     * @param type $order
     * @return boolean
     */
    public function getGameOrderOne($order_number){
        if(!$order_number){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("order",$db_config["DB_PREFIX"],$db_config);
        return $model->where(["order_number"=>$order_number])->find();
    }
    public function getGameShop($id){
        if(!$id){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("shop",$db_config["DB_PREFIX"],$db_config);
        return $model->where(["id"=>$id])->find();
    }
    /**
     * 兑换商城
     * @param type $where
     * @return boolean
     */
    public function getGameItemOne($where){
        if(!$where||empty($where)){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("item",$db_config["DB_PREFIX"],$db_config);
        return $model->where($where)->find();
    }
    public function updGameOrder($where,$data){
        if(!$where||empty($where)||!$data||empty($data)){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("order",$db_config["DB_PREFIX"],$db_config);
        return $model->where($where)->save($data);
    }
    public function addGameOrder($data){
        if(!$data||empty($data)){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("order",$db_config["DB_PREFIX"],$db_config);
        return $model->add($data);
    }

    /**
     * 兑换商城收货地址
     * @param type $uid
     * @return boolean
     */
    public function getGameUserAddress($uid){
        if(!$uid){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("user_address",$db_config["DB_PREFIX"],$db_config);
        return $model->where(["uid"=>$uid])->find();
    }
    /**
     * 累计充值
     * @param type $where
     * @return boolean
     */
    public function addUserRecharge($uid,$num){
        if(!$uid||!$num){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("user_recharge",$db_config["DB_PREFIX"],$db_config);
        $info = $model->where(["uid"=>$uid])->find();
        if($info){
            $num = $num+$info["num"];
            return $model->where(["uid"=>$uid])->save(["num"=>$num]);
        }else{
            return $model->add(["uid"=>$uid,"num"=>$num]);
        }
    }
    /**
     * 充值商城
     * @param type $where
     * @return boolean
     */
    public function getItemCostList($where,$order="id ASC"){
        if(!$where||empty($where)){
            return false;
        }
        $db_config = C("DB_CONFIG2");
        $model = M("item_cost",$db_config["DB_PREFIX"],$db_config);
        return $model->where($where)->order($order)->select();
    }
    
     /**
     * 根据SQL查询
     * @param type $sql
     */
    public function getOneBySql($sql){
        if(!$sql){
            return false;
        }
        $model = M("order");
        $result = $model->query($sql);
        if($result){
            return $result;
        }
        return false;
    }
}
