<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>计划详情列表</title>
        <link rel="stylesheet" href="/src/admin/css/base.css">
<link rel="stylesheet" href="/src/admin/css/layout.css">
<link rel="stylesheet" href="/src/admin/css/common/base.css">
<link rel="stylesheet" href="/src/admin/css/common/layout.css">
<link rel="stylesheet" href="/src/admin/js/asyncbox/skins/default.css">
<link rel="stylesheet" href="/src/css/page.css">
<script src="/src/admin/js/jquery-1.9.0.min.js"></script>
<script src="/src/admin/js/jquery.lazyload.js"></script>
<script src="/src/admin/js/jquery.form.js"></script>
<script src="/src/admin/js/asyncbox/asyncbox.js"></script>
<script src="/src/admin/js/functions.js"></script>
<script src="/src/admin/js/base.js"></script>
<script src="/src/admin/js/date.js"></script>
    </head>
    <body>
        <div class="wrap">
            <div class="mainBody">
                <div>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tab">
                        <tr>
                            <td>期数</td>
                            <td>类型</td>
                            <td>金额</td>
                            <td>执行时间</td>
                            <td>实际执行时间</td>
                            <td>信息</td>
                            <td>状态</td>
                        </tr>
                        <?php if(!empty($plan_des_list)): ?>
                        <?php $i=1; ?>
                        <?php foreach($plan_des_list as $n => $data): ?>
                        <tr id="tr_user_<?php echo $data['id']; ?>">
                            <?php if(($n)%2==0): ?>
                                <td  rowspan="2">第 <?php echo $i;$i++; ?> 期</td>
                            <?php endif; ?>
                            <td><?php echo $data['type_name']; ?></td>
                            <td><?php echo $data['amount']; ?></td>
                            <td><?php echo date("Y-m-d H:i:s",$data['s_time']); ?></td>
                            <td><?php echo $data['d_time']?date("Y-m-d H:i:s",$data['d_time']):""; ?></td>
                            <td><?php echo $data['message']; ?></td>
                            <td><?php echo $data['status_name']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>
