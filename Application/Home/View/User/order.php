<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>订单列表</title>
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
        <style>
            ol, ul ,li{list-style: none;}
        </style>
    </head>
    <body class="huibg" style="background: #fff;">
        <?php include T('Common/nav'); ?>

        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">订单中心</a>
            <button class="topnav" id="open-button"><span class="iconfont icon-1"></span></button>
        </nav>
        <ul id="myTab" class="nav nav-tabs">
            <li class="active"><a href="#sp1" data-toggle="tab">充值订单</a>
            </li>
            <li class=""><a href="#sp2" data-toggle="tab">兑换订单</a></li>
        </ul>

        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="sp1">
                <ul class="ddlist">
                    <if condition="$orderList" >
                    <volist name="orderList" id="ol" key="k"> 
                    <li class="red">
                        <a href="orderinfo.html?order_type=1&order_number={$ol.order_number}">
                            <table class="tablecolor-777676">
                                <tr>
                                    <td width="60%">
                                        <p class="tx16"><if condition="$ol['type'] eq 1" >商城充值订单<elseif condition="$ol['type'] eq 2" />活动充值订单<elseif condition="$ol['type'] eq 3" />佣金兑换欢乐豆<elseif condition="$ol['type'] eq 4" />比赛报名费</if></p>
                                        <p class="name padd10 tablecolor-000">
<!--                                        <if condition="$ol['type'] eq 1" >{$ol.item_name}<elseif condition="$ol['type'] eq 2" />活动充值{$ol.amount}元<else/>{$ol.item_name}</if>-->
                            </p>
                                        <p class="tx16">数量：×1</p>
                                        <p class="tx12 padd10">订单时间：{$ol.add_date|date="Y-m-d H:i",###}</p>
                                        <p class="tx12">订单号：{$ol.order_number}</p>
                                    </td>
                                    <td class="imgtd">
                                        <p class="tx16 tablecolor-red">￥ <span class="name">{$ol.amount}</span></p>
                                        <p class="padd10">获得开心豆</p>
                                        <p class="tx16 padd10 tablecolor-red">+ {$ol.ratio}</p>
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
            <div class="tab-pane fade" id="sp2">
                <ul class="ddlist">
                    <if condition="$gameOrderList" >
                    <volist name="gameOrderList" id="ol" key="k"> 
                    <li class="order-red">
                        <a href="orderinfo.html?order_type=2&order_number={$ol.order_number}">
                            <table class="tablecolor-fff">
                                <tr>
                                    <td width="60%">
                                        <p class="tx16">兑换商城</p>
                                        <p class="name padd10">{$ol.item_name}</p>
                                        <p class="tx16">数量：×1</p>
                                        <p class="tx12 padd10">订单时间：{$ol.add_time|date="Y-m-d H:i",###}</p>
                                        <p class="tx12">订单号：{$ol.order_number}</p>
                                    </td>
                                    <td class="imgtd">
                                        <p><if condition="$ol['item_image']" ><img src="<?=CDN_HOST; ?>/images/icons/exchange/100/{$ol.item_image}" width="50" height="50"><else/>{$ol.amount}</if></p>
                                        <p class="padd10">消耗兑换券数量</p>
                                        <p class="tx16 padd10">- {$ol.cost}</p>
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