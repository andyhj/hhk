<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>佣金提现详情</title>
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
    </head>
    <body class="huibg">
        <?php include T('Common/nav'); ?>

        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">佣金提现详情</a>
            <button class="topnav" id="open-button"><span class="iconfont icon-1"></span></button>
        </nav>

        <div class="dingdan">
            <div class="ddlist">
                <div class="dz"><p class="ziku">订单号：</p>{$awardInfo.order_number}</div>
                <div class="dz"><p class="ziku">名  称：</p><if condition="$awardInfo['type'] eq 1" >提现订单<elseif condition="$awardInfo['type'] eq 2" />兑换欢乐豆订单</if></div>
                <div class="dz"><p class="ziku">提现佣金：</p><span>{$awardInfo.commission}</span></div>
                <div class="dz"><p class="ziku">到账人民币：</p>{$awardInfo.amount}</div>
                <div class="dz"><p class="ziku">提现税费：</p><span>{$awardInfo.tax}</span></div>
                <div class="dz"><p class="ziku">状态：</p><span><if condition="$awardInfo['status'] eq 100" >系统审核中<elseif condition="$awardInfo['status'] eq 200" />提交成功<elseif condition="$awardInfo['status'] eq 300" />银行处理中<elseif condition="$awardInfo['status'] eq 400" />银行资金退回<elseif condition="$awardInfo['status'] eq 500" />提现失败</if></span></div>
<!--                <if condition="$awardInfo['info']" ><div class="dz"><p class="ziku">状态信息：</p><span>{$awardInfo.info}</span></div></if>-->
                <div class="dz noblord"><p class="ziku">订单时间：</p><span>{$awardInfo.add_date|date="Y-m-d H:i",###}</span></div>
            </div>
            <div style="padding: 10px;color:red;size: 12px">注：资金以银行实际到账为准，如果银行信息填写错误资金将在24小时之内退回账户</div>
        </div>


        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>

    </body></html>