<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <title>计划详情列表</title>
        <?php include T('Common/header'); ?>
        <style>
            body{
                font: 12px/22px Verdana, Geneva, sans-serif;
            }
            .tabdes {
                margin: 1%;
                border-width: 1px;
                border-style: solid;
                border-color: #ddd #ddd #fff #fff;
                text-shadow: 0 1px 0 #FFFFFF;
                border-collapse: separate;
                width: 98%;
            }
            .tabdes td {
                padding: 3px;
                line-height: 24px;
                border-width: 1px;
                border-style: solid;
                border-color: #fff #fff #ddd #ddd;
            }
            
            .tab {
                margin:  1%;
                border-width: 1px;
                border-style: solid;
                border-color: #ddd #ddd #fff #fff;
                text-shadow: 0 1px 0 #FFFFFF;
                border-collapse: separate;
                width: 98%;
                padding-bottom: 0.2rem;
            }
            .tab td {
                line-height: 24px;
                border-width: 1px;
                border-style: solid;
                border-color: #fff #fff #ddd #ddd;
            }
            .cancel{
                border: 0;
                border-radius: 0.1rem;
                height: 0.6rem;
                width: 2rem;
                letter-spacing: 0.04rem;
            }
        </style>
    </head>
    <body>
        <div class="wrap">
            <div class="mainBody">
                <div>
                    <?php if(!empty($plan_des_list)): ?>
                    <?php $i=1; ?>
                    <table border="0" cellspacing="0" cellpadding="0" class="tab">
                        <?php foreach($plan_des_list as $n => $data): ?>
                        <tr>
                            <?php if(($n)%2==0): ?>
                            <td  rowspan="2" align="center">第 <?php echo $i;$i++; ?> 期</td>
                            <?php endif; ?>
                            <td>
                                <table border="0" cellspacing="0" cellpadding="0" class="tabdes">
                                    <tr>
                                        <td align="center" style="font-size: 0.3rem;color: #ff741a;"><?php echo $data['type_name']; ?></td>
                                        <td align="center">金额：<?php echo $data['amount']; ?></td>
                                        <td align="center">状态：<?php echo $data['status_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">执行时间：<?php echo date("Y-m-d H:i:s",$data['s_time']); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">实际执行时间：<?php echo $data['d_time']?date("Y-m-d H:i:s",$data['d_time']):""; ?></td>
                                    </tr>
                                    <?php if($data["message"]&&$data['order_state']!=1): ?>
                                    <tr>
                                        <td colspan="3" style="color: red;">提示：<?php echo $data['message']; ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if($plan_info['status']==4||$plan_info['status']==5){ ?><tr align="center" height='50'><td colspan='2'><input type="button" value="取消计划" id="cancel" class="cancel"></td></tr><?php } ?>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    <script>
            var _lock = false;
            $(".cancel").click(function(){
                if(_lock){
                    alert('正在提交....');
                    return false;
                }
                if(!confirm('是否取消计划？')){
                    return false;
                }
                _lock = true;
                var p_id = "<?php echo $data['p_id']; ?>";
                $.ajax({
                    url: "<?php echo $cancel_url; ?>",
                    data: {p_id: p_id},
                    type: 'post',
                    dataType: 'json',
                    success: function (data) {
                        if (data.status == 200) {
                            alert(data.info);
                            window.location.reload();
                        } else {
                            _lock = false;
                            alert(data.info);
                        }
                    }
                });
            });
        </script>
</html>
