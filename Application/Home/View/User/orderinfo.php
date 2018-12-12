<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>订单详情</title>
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
    </head>
    <body class="huibg">
        <?php include T('Common/nav'); ?>

        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">订单详情</a>
            <button class="topnav" id="open-button"><span class="iconfont icon-1"></span></button>
        </nav>

        <div class="dingdan">
            <if condition="$order_type eq 1" >
            <div class="ddlist">
                <div class="dtit">订单信息</div>
                <div class="dz"><p class="ziku">订单号：</p>{$order_info.order_number}</div>
                <div class="dz"><p class="ziku">名  称：</p><if condition="$order_info['type'] eq 1" >商城充值订单<elseif condition="$order_info['type'] eq 2" />活动充值订单<elseif condition="$order_info['type'] eq 3" />佣金兑换欢乐豆<elseif condition="$order_info['type'] eq 4" />比赛报名费</if></div>
                <div class="dz"><p class="ziku">数  量：</p>1</div>
                <div class="dz"><p class="ziku">金  额：</p><span>{$order_info.amount}</span></div>
                <div class="dz"><p class="ziku">获得欢乐豆：</p><span>{$order_info.ratio}</span></div>
                <div class="dz"><p class="ziku">支付类型：</p><span><if condition="$order_info['pay_type'] eq 1" >微信<elseif condition="$order_info['pay_type'] eq 2" />支付宝<elseif condition="$order_info['pay_type'] eq 3" />佣金</if></span></div>
                <div class="dz"><p class="ziku">状态：</p><span><if condition="$order_info['status'] eq 100" >待支付<elseif condition="$order_info['status'] eq 200" />已支付<elseif condition="$order_info['status'] eq 300" />退款中<elseif condition="$order_info['status'] eq 400" />退款成功</if></span></div>
                <div class="dz noblord"><p class="ziku">订单时间：</p><span>{$order_info.add_date|date="Y-m-d H:i",###}</span></div>
            </div>
            </if>
            <if condition="$order_type eq 2" >
            <div class="ddlist">
                <div class="dtit">订单信息</div>
                <div class="dz"><p class="ziku">订单号：</p>{$order_info.order_number}</div>
                <div class="dz"><p class="ziku">商品名称：</p>{$order_info.item_name}</div>
                <div class="dz"><p class="ziku">数  量：</p>{$order_info.number}</div>
                <div class="dz"><p class="ziku">支付类型：</p><span><if condition="$order_info['cost_type'] eq 4" >兑换券<else/>欢乐豆</if></span></span></div>
                <div class="dz"><p class="ziku">消耗货币数量：</p><span>{$order_info.cost}</span></div>
                <div class="dz noblord"><p class="ziku">订单时间：</p><span>{$order_info.add_time|date="Y-m-d H:i",###}</span></div>
            </div>
                <if condition="$order_info['type'] eq 1" >
                <div class="ddlist">
                    <div class="dtit">收货信息</div>
                    <div class="dzdv">
                        <span class="name">{$order_info.addr_name}</span> <span class="phone">{$order_info.addr_phone}</span>
                        <span class="dd">{$order_info.address}</span>

                    </div>
                </div>
                <else/>
                        <?php if($order_info["item_type"]==2){?>
                        <div class="ddlist">
                            <div class="dtit">礼品卡信息</div>
                            <div class="dzdv">
                                <span class="dd">卡号：<?php echo $order_info["cardNo"];?></span><br>
                                <span class="dd">卡密：<?php echo $order_info["cardPws"];?></span>
                            </div>
                        </div>
                        <?php }?>
                </if>
<!--            <div class="ddlist">
                <div class="dtit">订单备注</div>
                <div class="dz noblord">备注信息123123123</div>
            </div>
            <div class="ddlist">
                <div class="dtit">物流单号</div>
                <div class="dz noblord">中通：TL123012409-1</div>
            </div>-->
            </if>
        </div>


        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>

    </body></html>