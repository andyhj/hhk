<?php 
namespace Admin\Model;
use Common\Common\Pinyin;
class ProductModel extends \Common\Model\ProductModel{
    /**
     * 添加一条商品
     * @param type $data
     * @return boolean
     */
    public function addProduct($data){
        if(empty($data)){
            return false;
        }
        $obj_pinyin = new Pinyin();
        $product_data['product'] = $data['product'];
        $product_data['product_sku'] = $data['product_sku'];
        $product_data['product_image'] = $data['product_image'];
        
        $product_data['product']['title_pinyin'] = $obj_pinyin->pinyin($data['product']['title']);
        $product_data['product']['title_pinyin_2'] = $obj_pinyin->pinyin($data['product']['title'],true);
        $product_data['product']['add_date'] = time();
        $product_data['product']['edit_date'] = time();
        if(!empty($product_data['product'])){
            $product_id = $this->add($product_data['product']);
            if($product_id){
                if(!empty($product_data['product_sku'])){
                    $product_sku = M("product_sku");
                    foreach ($product_data['product_sku'] as $value) {
                        $value["product_id"] = $product_id;
                        $product_sku->add($value);
                    }
                }
                if(!empty($product_data['product_image'])){
                    $product_image = M("product_image");
                    foreach ($product_data['product_image'] as $value) {
                        $value["product_id"] = $product_id;
                        $product_image->add($value);
                    }
                }
                return $product_id;
            }
        }
        return false;
    }
    /**
     * 修改一条商品
     * @param type $product_id
     * @param type $data
     * @return boolean
     */
    public function editProduct($product_id,$data){
        if(!$product_id||empty($data)){
            return false;
        }
        $obj_pinyin = new pinyin();
        $product_data['product'] = $data['product'];
        $product_data['product_sku'] = $data['product_sku'];
        $product_data['product_image'] = $data['product_image'];
        
        $product_data['product']['title_pinyin'] = $obj_pinyin->pinyin($data['product']['title']);
        $product_data['product']['title_pinyin_2'] = $obj_pinyin->pinyin($data['product']['title'],true);
        $product_data['product']['edit_date'] = time();
        if(!empty($product_data['product'])){
            $return_status = $this->where(["id"=>$product_id])->save($product_data['product']);
            if($return_status){
                if(!empty($product_data['product_sku'])){
                    $product_sku = M("product_sku");
                    $product_sku->where('product_id='.$product_id)->delete();
                    foreach ($product_data['product_sku'] as $value) {
                        $value["product_id"] = $product_id;
                        $product_sku->add($value);
                    }
                }
                if(!empty($product_data['product_image'])){
                    $product_image = M("product_image");
                    $product_image->where('product_id='.$product_id)->delete();
                    foreach ($product_data['product_image'] as $value) {
                        $value["product_id"] = $product_id;
                        $product_image->add($value);
                    }
                }
                return true;
            }
        }
        return false;
    }
    
    public function delProduct($product_id){
        if(!$product_id){
            return false;
        }
        $return_status = $this->where(["id"=>$product_id])->delete();
        if($return_status){
            $product_sku = M("product_sku");
            $product_sku->where('product_id='.$product_id)->delete();
            $product_image = M("product_image");
            $product_image->where('product_id='.$product_id)->delete();
            return true;
        }
        return false;
    }
    public function pageShow($per_page,$where=[])
    {
        //分页
        $count      = $this->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        return $show;
    }
}