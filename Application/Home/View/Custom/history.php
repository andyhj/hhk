<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>历史比赛记录</title>
        <link rel="stylesheet" href="">
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
        <style>
            .des .vipsan{
                margin-bottom: 0px;
                line-height: 45px;
            }
            .des i{
                color: red;
                margin-right: 12px;
            }
            .des .text-center{
                text-align: left;
                font-size: 16px;
            }
            .text-center span{
                font-size: 14px;
            }
            .nav-div{
                height: 50px;
                line-height: 50px; 
                margin-left: 20%;
                margin-right: 20%;
                font-size: 20px;
                
            }
            .nav-div span{
                width: 50%;
                
            }
            .nav-atv{
                color: red;
            }
            .nav-a{
                color: #7e7e7e;
            }
            .acdv{
                background: #fff;
                overflow: auto;
                padding: 0px;
            }
            .head{
                width: 45px;
                height: 45px;
                margin: 0px auto;
                border-radius: 100%;
                margin-top: -13px;
            }
            .head img{
                border-radius: 100%;
                width: 100%;
            }
            .list-ul{
                padding-left: 0;
            }
            .list-ul li{
                position: relative;
                display: block;
                padding: 8px 5px;
                padding-right: 13px;
                margin-bottom: -1px;
                border-bottom: 1px solid #ddd;
                height: 90px;
            }
            .list-ul li table{
                width: 100%;
                color: #7e7e7e;
            }
            .tb{
                width: 60px;
            }
            .line-limit-length {
                max-width: 120px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;

            }
        </style>
    </head>
    <body>
        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">历史比赛记录</a>
        </nav>
        <div class="usercenter  acdv" id="mynavdiv2">
            <div class="details" style="padding: 18px;padding-top: 0px;">
                <ul class="list-ul">
                    <if condition="$customList" >
                    <volist name="customList" id="cl" key="k"> 
                        <a href="custom.html?custom_id={$cl.id}">
                            <li>
                                <table>
                                    <tr>
                                        <td><p class="line-limit-length" style="font-weight:bold;font-size: 18px;">{$cl.name}</p></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>类型：{$cl.game_type}</td>
                                        <td align="right">{$cl.start_date|date="Y-m-d H:i",###}</td>
                                    </tr>
                                    <tr>
                                        <td>场次：{$cl.inning}/{$cl.this_inning}</td>
                                        <td></td>
                                    </tr>
                                </table>
                            </li>
                        </a>
                    </volist>
                    <else/>
                    <div class="nos"><img src="/src/img/h5/no.png"></div>
                    </if>
                </ul>
            </div>
        </div>
    </body>
</html>