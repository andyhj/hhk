<?php 
namespace Common\Model;
use Think\Model;
class ProductModel extends Model{
    
    /**
     * 获取产品列表数据
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
     * 根据商品id查询sku
     * @param type $product_id
     * @return boolean
     */
    public function getSkuList($product_id){
        if(!$product_id){
            return false;
        }
        $product_sku = M("product_sku");
        return $product_sku->where(["product_id"=>$product_id])->select();
    }
    /**
     * 查询一条sku
     * @param type $id
     * @return boolean
     */
    public function getSkuOne($id){
        if(!$id){
            return false;
        }
        $product_sku = M("product_sku");
        return $product_sku->where(["id"=>$id])->find();
    }
    /**
     * 根据商品id查询图片
     * @param type $product_id
     * @return boolean
     */
    public function getImageList($product_id){
        if(!$product_id){
            return false;
        }
        $product_image = M("product_image");
        return $product_image->where(["product_id"=>$product_id])->select();
    }
    /**
     * 计算虚拟销量
     * @param type $item_id 商品id
     * @param type $real_sales 真实销量
     * @param type $date_added 上架时间，时间戳
     */
    public function getVirtualSales($item_id, $real_sales, $date_added) {
        $add_timestamp = $date_added; //上架时间(时间戳)
        $cur_timestamp = time(); //当前时间
        $first_grow_factor = 139; //初始化增长因子
        $day_grow_factor = 3;  //每天增长因子
        $day_rectify_factor = 0.45; //每天纠正因子
        $log = log((($real_sales + 1) / 2), 10); //求出自然对数
        $nub = ($item_id % 10) / 10.01 + 1;
        $num = ($item_id % 3) * 0.2 + 0.8;
        $show_sales = $real_sales + (($log + $nub) * $first_grow_factor) + ((($cur_timestamp - $add_timestamp) / 86400.01) * $num * $day_grow_factor) + ((($cur_timestamp - 10800) % 3) * $day_rectify_factor);
        return intval($show_sales);
    }
}
?>