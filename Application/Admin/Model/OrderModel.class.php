<?php
namespace Admin\Model;

class OrderModel extends \Common\Model\OrderModel{
    public function pageShow($per_page,$where=[])
    {
        //分页
        $count      = $this->where($where)->count();// 查询满足要求的总记录数
        $Page       = getpage($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        return $show;
    }
}
