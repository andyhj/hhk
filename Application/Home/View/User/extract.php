<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>佣金提现列表</title>
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
        <style>
            ol, ul ,li{list-style: none;}
        </style>
    </head>
    <body>
        <?php include T('Common/nav'); ?>

        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">佣金提现列表</a>
            <button class="topnav" id="open-button"><span class="iconfont icon-1"></span></button>
        </nav>
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="sp1">
                <ul class="ddlist">
                    <if condition="$awardList" >
                    <volist name="awardList" id="ol" key="k"> 
                    <li class="red">
                        <a href="extractinfo.html?id={$ol.id}">
                            <table class="tablecolor-777676">
                                <tr>
                                    <td width="60%">
                                        <p class="tx14 padd5">类型：<if condition="$ol['type'] eq 1" >提现订单<elseif condition="$ol['type'] eq 2" />兑换欢乐豆订单</if></p>
                                        <p class="tx14 padd5">状态：<span class="tablecolor-red"><if condition="$ol['status'] eq 100" >系统审核中<elseif condition="$ol['status'] eq 200" />提交成功<elseif condition="$ol['status'] eq 300" />银行处理中<elseif condition="$ol['status'] eq 400" />银行资金退回<elseif condition="$ol['status'] eq 500" />提现失败</if></span></p>
                                        <p class="tx14 padd5">订单时间：{$ol.add_date|date="Y-m-d H:i",###}</p>
                                        <p class="tx14 padd5">订单号：<if condition="$ol['order_number']" >{$ol.order_number}<else/>-----</if></p>
                                    </td>
                                    <td class="imgtd">
                                        <p class="tx16 tablecolor-red name">{$ol.commission}</p>
                                    </td>
                                </tr>
                            </table>
                        </a>
                    </li>
                    </volist>
                    <else/>
                    <div class="nos"><img src="/src/img/h5/no.png"></div>
                    </if>
                </ul>
            </div>
        </div>



        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>

    </body></html>