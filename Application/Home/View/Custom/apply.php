<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>报名列表</title>
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
        <style type="text/css" media="screen">
            .ranking{
                border:1px dashed #8E8A8A;
                width:100%;
                border-radius: 10px;
                margin-bottom:10px;
                background: #F3F0F0;
            }
            .ranking table{
                width:100%;
                color: #000;
            }
            .ranking td{
                height:25px;
            }
            .ranking td span{
                margin: 10px;
            }
            .ranking td img{
                border-radius: 8px;
            }
            .verify{
                width:100%;
                background: #F3F0F0;
                text-align: center;
                height: 40px;
                line-height: 40px;
                color: red;
            }
            .game-tg{
                background-color: #fdc158;
                border-radius: 10px;
                width:96%;
                letter-spacing: 2px;
                margin-left: 2%;
                margin-top: 10px;
                height: 45px;
                line-height: 45px;
                font-size: 18px;
                text-align: center;
                position: fixed;
                bottom: 5px;
            }
        </style>
    </head>
    <body class="huibg">
        <?php include T('Common/nav'); ?>

        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">报名列表</a>
            <button class="topnav" id="open-button"><span class="iconfont icon-1"></span></button>
        </nav>
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="sp1">
                <div class="verify">当前报名总人数：<?php echo $custom_apply_count; ?> 人</div>
                <ul class="ddlist" style="margin-bottom: 45px;">
                    <if condition="$customApply" >
                    <li>
                    <volist name="customApply" id="cr" key="k"> 
                        <div class="ranking">
                            <table style="margin: 0px;height:0px;">
                                <tr>
                                    <td rowspan="3" width="85"><span><if condition="$cr['user_headurl']" ><img src="{$cr.user_headurl}" alt="" width="60" height="60"></if></span></td>
                                    <td>昵称：{$cr.user_name}</td>
                                <tr>
                                    <td>性别：<if condition="$cr['ranking']" >{$cr.gender_name}</if></td>
                                </tr>
                                <tr>
                                    <td>报名时间：{$cr.applytime|date="Y-m-d H:i",###}</td>
                                </tr>
                            </table>
                        </div>
                    </volist>
                    </li>
                    </if>
                </ul>
            </div>
            <div class="game-tg" id="excel">导出用户信息</div>
        </div>



        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>

    <script>
        $("#excel").click(function(){
            var url = "{$export}";
            window.location=url;
        });
    </script>
    </body></html>