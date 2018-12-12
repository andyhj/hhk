<?php
namespace Common\Model;
use Think\Model;
class IntegralSetModel extends Model{
    /**
     * 获取列表数据
     * 
     */
    public function getList($where=[],$current_page=1,$per_page=20,$order="add_time DESC"){
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
     * 修改
     * @param type $where
     * @param type $data
     * @return boolean
     */
    public function updSet($where,$data){
        if(empty($where)||empty($data)){
            return false;
        }
        return $this->where($where)->save($data);
    }
    /**
     * 添加
     * @param type $data
     * @return boolean
     */
    public function addSet($data){
        if(empty($data)){
            return false;
        }
        return $this->add($data);
    }
}
