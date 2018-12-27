<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>我的计划</title>
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
                margin-bottom: 0.2rem;
            }
            .list-ul table td{
                height: 0.25rem;
                font-size: 0.2rem;
            }
            .plan-des{
                width:100%;
                letter-spacing: 2px;
                height: 1.35rem;
                position: fixed;
                top: 1.4rem;
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
                margin-top: 0.92rem;
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
        </style>
    </head>
    <body>       
        <div style="width:100%;overflow:hidden;height: 100vh">
            <img src="/src/img/plan/jhbg.jpg" style="width: 100%;display:block;">
        </div> 
        <div class="plan-des">
            <ul id="myTab" class="ui">
                <li class="tbli active" style="padding-left:1%"><a href="#sp1" data-toggle="tab" >正在执行计划</a></li>
                <li class="tbli" style="padding-left:1%"><a href="#sp2" data-toggle="tab">未执行计划</a></li>
                <li class="tbli" style="padding-left:1%"><a href="#sp3" data-toggle="tab">已执行完计划</a></li>
            </ul>
            <div id="myTabContent" class="tab-cot">
                <div class="tab-pane fade active in " id="sp1" >
                    <?php if($plan_arr1&&!empty($plan_arr1)){?>
                    <ul class="list-ul" style="margin-bottom: 0px;">
                        <?php foreach ($plan_arr1 as $k=>$v){?>
                        <li  onclick="javascript:window.location.href='<?php echo U('index/plan/plandes',['id'=>$v['id']]);?>'">
                            <table>
                                <tr>
                                    <td>
                                        <span style="font-size: 0.36rem;color: #595757;font-weight:bold;"><?php echo $v['bank_name']?></span>
                                    </td>
                                    <td rowspan="3"  style="text-align: right;font-size: 0.3rem;">
                                        共<span style="font-size: 0.6rem;color: #fc8b5a"><?php echo $v['periods']?></span>期
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <?php echo $v['user_name']?>|尾号<?php echo $v['card_no']?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        还款总额：<?php echo $v['amount']?>元
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        费率：<?php echo ($v["fee"]*100).'%+'.(int)$v["close_rate"];?>
                                    </td>
                                    <td  style="text-align: right;">
                                        每期扣款金额：<?php echo round(($v['p_amount']+$v['p_fee']),2); ?>元
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

                <div class="tab-pane fade " id="sp2">
                    <?php if($plan_arr3&&!empty($plan_arr3)){?>
                    <ul class="list-ul" style="margin-bottom: 0px;">
                        <?php foreach ($plan_arr3 as $k=>$v){?>
                        <li  onclick="javascript:window.location.href='<?php echo U('index/plan/plandes',['id'=>$v['id']]);?>'">
                            <table>
                                <tr>
                                    <td>
                                        <span style="font-size: 0.36rem;color: #595757;font-weight:bold;"><?php echo $v['bank_name']?></span>
                                    </td>
                                    <td rowspan="3"  style="text-align: right;font-size: 0.3rem;">
                                        共<span style="font-size: 0.6rem;color: #fc8b5a"><?php echo $v['periods']?></span>期
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <?php echo $v['user_name']?>|尾号<?php echo $v['card_no']?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        还款总额：<?php echo $v['amount']?>元
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        费率：<?php echo ($v["fee"]*100).'%+'.(int)$v["close_rate"];?>
                                    </td>
                                    <td  style="text-align: right;">
                                        每期扣款金额：<?php echo round(($v['p_amount']+$v['p_fee']),2); ?>元
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

                <div class="tab-pane fade" id="sp3">
                    <?php if($plan_arr2&&!empty($plan_arr2)){?>
                    <ul class="list-ul" style="margin-bottom: 0px;">
                        <?php foreach ($plan_arr2 as $k=>$v){?>
                        <li  onclick="javascript:window.location.href='<?php echo U('index/plan/plandes',['id'=>$v['id']]);?>'">
                            <table>
                                <tr>
                                    <td>
                                        <span style="font-size: 0.36rem;color: #595757;font-weight:bold;"><?php echo $v['bank_name']?></span>
                                    </td>
                                    <td rowspan="3"  style="text-align: right;font-size: 0.3rem;">
                                        共<span style="font-size: 0.6rem;color: #c1c1c1"><?php echo $v['periods']?></span>期
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <?php echo $v['user_name']?>|尾号<?php echo $v['card_no']?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        还款总额：<?php echo $v['amount']?>元
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        费率：<?php echo ($v["fee"]*100).'%+'.(int)$v["close_rate"];?>
                                    </td>
                                    <td  style="text-align: right;">
                                        每期扣款金额：<?php echo round(($v['p_amount']+$v['p_fee']),2); ?>元
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
        </div>
<?php include T('Common/footer'); ?>
    </body></html>