<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>获奖列表</title>
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
            .ranking .i-no{
                margin: 5px;
                font-size: 15px;
                font-weight: bold;
                color: #E24B4B;
            }
            .ranking .i-yes{
                margin: 5px;
                font-size: 15px;
                font-weight: bold;
                color: #716E6E;
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
    <body>
        <?php include T('Common/nav'); ?>

        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">获奖列表</a>
            <button class="topnav" id="open-button"><span class="iconfont icon-1"></span></button>
        </nav>
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="sp1">
                <ul class="ddlist" style="margin-bottom: 45px;">
                    <if condition="$customRanking" >
                    <li>
                    <volist name="customRanking" id="cr" key="k"> 
                        <div class="ranking">
                            <a href="rankinginfo2.html?id={$cr.id}">
                            <table style="margin: 0px;height:0px;">
                                <tr>
                                    <td rowspan="3"><span><if condition="$cr['user_headurl']" ><img src="{$cr.user_headurl}" alt="" width="60" height="60"></if></span></td>
                                    <td>获奖名称：{$cr.user_name}</td>
                                    <td rowspan="3"><i <if condition="!$cr['style']" > class="i-no" <else/> class="i-yes" </if> >{$cr.title}</i></td>
                                </tr>
                                <tr>
                                    <td>比赛名次：<if condition="$cr['ranking']" >第{$cr.ranking}名</if></td>
                                </tr>
                                <tr>
                                    <td>获奖时间：{$cr.add_time|date="Y-m-d H:i",###}</td>
                                </tr>
                            </table>
                            </a>
                        </div>
                    </volist>
                    </li>
                    </if>
                </ul>
            </div>
            <div class="game-tg" id="plff">批量发放</div>
        </div>
        <script>
            $("#plff").click(function(event) {
                if(confirm("是否确定发放？")){
                    location="exchangeall.html?c_id=<?php echo $c_id; ?>";
                }
            });
        </script>
        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>

    </body></html>