<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>我的奖券</title>
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
        <style type="text/css" media="screen">
            .ranking{
                border:2px dashed #8E8A8A;
                width:100%;
                border-radius: 15px;
                margin-top:10px;
                background: #F3F0F0;
            }
            .ranking table{
                width:100%;
                color: #000;
            }
            .ranking td{
                height:30px;
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
            .ddlist li {
                background: #eee;
                margin-bottom: 0px;
                padding: 0px 5px 0px 5px;
                font-size: 0.8em;
            }
        </style>
    </head>
    <body class="huibg">
        <?php include T('Common/nav'); ?>

        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">我的奖券列表</a>
            <button class="topnav" id="open-button"><span class="iconfont icon-1"></span></button>
        </nav>
        
        <ul id="myTab" class="nav nav-tabs">
            <li class="active" style="width: 31%;margin-left: 2.5%;"><a href="#sp1" data-toggle="tab">未领取</a></li>
            <li class="" style="width: 31%;margin-left: 1%;"><a href="#sp2" data-toggle="tab">已领取</a></li>
            <li class="" style="width: 31%;margin-left: 1%;"><a href="#sp3" data-toggle="tab">已过期</a></li>
        </ul>
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="sp1">
                <ul class="ddlist">
                    <if condition="$custom_ranking2" >
                    <li>
                    <volist name="custom_ranking2" id="cr" key="k"> 
                        <a href="rankinginfo.html?id={$cr.id}">
                            <table style="margin:0 0 0 0px;height: 98px;" class="jj-bg">
                                <tr>
                                    <td rowspan="3" width="30%" align="center"><span><if condition="$cr['headurl']" ><img src="{$cr.headurl}" alt="" width="70" height="70"></if></span></td>
                                    <td width="42%">比赛名称：{$cr.name}</td>
                                    <td rowspan="3" width="28%" align="center"><i  class="i-no"></i></td>
                                </tr>
                                <tr>
                                    <td>比赛名次：<if condition="$cr['ranking']" >第{$cr.ranking}名</if></td>
                                </tr>
                                <tr>
                                    <td>获奖时间：{$cr.add_time|date="Y-m-d",###}</td>
                                </tr>
                            </table>
                        </a>
                    </volist>
                    </li>
                    <else/>
                    <div class="nos"><img src="/src/img/h5/no.png"></div>
                    </if>
                </ul>
            </div>
            
            <div class="tab-pane fade" id="sp2">
                <ul class="ddlist">
                    <if condition="$custom_ranking3" >
                    <li>
                    <volist name="custom_ranking3" id="cr" key="k"> 
                        <a href="rankinginfo.html?id={$cr.id}">
                            <table style="margin:0 0 0 0px;" class="jj-bg">
                                <tr>
                                    <td rowspan="3" width="30%" align="center"><span><if condition="$cr['headurl']" ><img src="{$cr.headurl}" alt="" width="70" height="70"></if></span></td>
                                    <td width="42%">比赛名称：{$cr.name}</td>
                                    <td rowspan="3" width="28%" align="center"><i  class="i-no"><img src="/src/img/h5/exchange.png" alt="" width="60" height="50"></i></td>
                                </tr>
                                <tr>
                                    <td>比赛名次：<if condition="$cr['ranking']" >第{$cr.ranking}名</if></td>
                                </tr>
                                <tr>
                                    <td>获奖时间：{$cr.add_time|date="Y-m-d",###}</td>
                                </tr>
                            </table>
                        </a>
                    </volist>
                    </li>
                    <else/>
                    <div class="nos"><img src="/src/img/h5/no.png"></div>
                    </if>
                </ul>
            </div>
            
            <div class="tab-pane fade" id="sp3">
                <ul class="ddlist">
                    <if condition="$custom_ranking1" >
                    <li>
                    <volist name="custom_ranking1" id="cr" key="k"> 
                        <a href="rankinginfo.html?id={$cr.id}">
                            <table style="margin:0 0 0 0px;" class="jj-bg">
                                <tr>
                                    <td rowspan="3" width="30%" align="center"><span><if condition="$cr['headurl']" ><img src="{$cr.headurl}" alt="" width="70" height="70"></if></span></td>
                                    <td width="42%">比赛名称：{$cr.name}</td>
                                    <td rowspan="3" width="28%" align="center"><i  class="i-no"><img src="/src/img/h5/overdue.png" alt="" width="60" height="50"></i></td>
                                </tr>
                                <tr>
                                    <td>比赛名次：<if condition="$cr['ranking']" >第{$cr.ranking}名</if></td>
                                </tr>
                                <tr>
                                    <td>获奖时间：{$cr.add_time|date="Y-m-d",###}</td>
                                </tr>
                            </table>
                        </a>
                    </volist>
                    </li>
                    <else/>
                    <div class="nos"><img src="/src/img/h5/no.png"></div>
                    </if>
                </ul>
            </div>
        </div>



        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>

    </body></html>