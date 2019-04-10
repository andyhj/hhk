<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>会员记录</title>
        <?php include T('Common/header'); ?>
        <style type="text/css" media="screen">
            .list-ul{
                padding-left: 0;
                padding-top: 0.08rem;
            }
            .list-ul li{
                position: relative;
                display: block;
                padding-left: 0.22rem;
                padding-right: 0.22rem;
                padding-top: 0.22rem;
                margin-bottom: -1px;
                border-bottom: 1px solid #ddd;
            }
            .list-ul table{
                width: 100%;
                margin-bottom: 0.1rem;
            }
            .list-ul table td{
                height: 0.35rem;
                font-size: 0.2rem;
            }
            .plan-des{
                width:100%;
                letter-spacing: 2px;
                height: 1.35rem;
                position: fixed;
            }
            .ui{
                 width:100%;
                 list-style: none;
                 margin:0;
                 padding:0;
                 margin-left: 5%;
             }
            .ui li{
                width:30%;
                height:1rem;
                float: left;/*该处换为display:inline-block;同样效果*/
                text-align: center;
            }

            .tbli a{
                width:100%;
                height: 100%;
                font-size: 0.22rem;
                height:0.7rem;
                line-height: 0.7rem;
                background-image:url(/src/img/plan/wxz.png);
                background-repeat:no-repeat; 
                background-size:100% 100%;
                -moz-background-size:100% 100%;
                display: inline-block;
                margin-top: 0.31rem;
                text-decoration:none;
                color: #9d9d9d;
            }
            .active a{
                width:100%;
                font-size: 0.22rem;
                height:1rem;
                line-height: 1rem;
                background-image:url(/src/img/plan/xz.png);
                background-repeat:no-repeat; 
                background-size:100% 100%;
                -moz-background-size:100% 100%;
                display: inline-block;
                margin-top: 0rem;
                text-decoration:none;
                color: #595757;
            }
            .tab-cot{
                background-image:url(/src/img/plan/text.png);
                background-repeat:no-repeat; 
                background-size:100% 100%;
                -moz-background-size:100% 100%;
                width: 96%;
                margin-left: 2%;
                height: 7.75rem;
            }
            .tab-cot div{
                display: none;
            }
            .tab-cot .active{
                display:block;
            }
            .plan{
                height: 7.5rem;
                overflow-y: auto;
            }
        </style>
    </head>
    
    <script>
        var _lock = false;
        function lq(id){
//            if(_lock){
//                alert('正在提交....');
//                return false;
//            }
//            _lock = true;
            var id = id;
            console.log(id);
            $.ajax({
                    url: "<?php echo $getPlus; ?>",
                    data: {id:id},
                    type: 'post',
                    dataType: 'json',
                    success: function (data) {
                        if (data.status == 200) {
                            alert("领取成功");
                            location='<?php echo U("index/user/plus");?>';
                        } else {
                            _lock = false;
                            alert(data.info);
                        }
                    }
                });
        }
    </script>
    <body>       
            <div id="myTabContent" class="tab-cot plan">
                <div class="tab-pane fade active in " id="sp1" >
                    <?php if($user_vip_log_list&&!empty($user_vip_log_list)){?>
                    <ul class="list-ul" style="margin-bottom: 0px;">
                        <?php foreach ($user_vip_log_list as $k=>$v){?>
                        <li>
                            <table>
                                <tr>
                                    <td>
                                        <span style="font-size: 0.3rem;color: #595757;font-weight:bold;">
                                        <?php echo $v["type_name"]."(".$v["vip_m"]."个月)";?>
                                        </span>
                                    </td>
                                    <td rowspan="4"  style="text-align: right;font-size: 0.3rem;">
                                         <?php if($v["status"]){?>
                                            <img src="/src/img/plus/ylq.png" style="width:1.3rem;">
                                        <?php }else{?>
                                            <?php if($v["end_time"]< time()){?>
                                            <img src="/src/img/plus/gq.png" style="width:1.3rem;">
                                            <?php }else{?>
                                            <img src="/src/img/plus/get.png" style="width:1.3rem;" onclick="lq(<?php echo $v["id"];?>)">
                                            <?php }?>
                                        <?php }?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        时长：<?php echo $v["vip_m"]."个月";?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                       来源：<?php echo $v["type_name"];?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        有效时间：<?php echo date("Y-m-d",$v["add_time"]);?> - <?php echo $v["end_time"]?date("Y-m-d",$v["end_time"]):"永久";?>
                                    </td>
                                </tr>
                            </table>
                        </li>
                        <?php }?>
                    </ul>
                    <?php }else{?>
                    <img src="/src/img/no.png" class="nos">
                    <?php }?>
                </div>
            </div>
<?php include T('Common/footer'); ?>
    </body></html>