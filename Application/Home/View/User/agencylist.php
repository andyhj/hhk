<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>我的推广列表</title>
        <link rel="stylesheet" href="">
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
        <style>
            .nav-div{
                height: 50px;
                line-height: 50px; 
                margin-left: 20%;
                margin-right: 20%;
                font-size: 20px;
                
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
                height: 77px;
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
        <div class="nav-div">
            <span style="margin-right: 52%;"  id="mynav1"  class="nav-atv">上级</span>
            <span id="mynav2" class="nav-a">下级</span>
        </div>
        <div style="border-bottom: 1px solid #ddd;"></div>
            
        </div>
        <div class="usercenter  acdv" id="mynavdiv1">
            <div class="details" style="padding: 18px;padding-top: 0px;">
                <ul class="list-ul">
                    <if condition="$a_data_up" >
                    <li>
                        <table>
                            <tr>
                                <td  rowspan="3" width="75"><img src="{$a_data_up.headurl}" class="tb"></td>
                                <td class="line-limit-length">{$a_data_up.nickname}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>ID：{$a_data_up.id}</td>
                                <td align="right">{$a_data_up.regtime}</td>
                            </tr>
                            <tr>
                                <td>{$a_data_up.grade}</td>
                                <td></td>
                            </tr>
                        </table>
                    </li>
                    <else/>
                    <div class="nos"><img src="/src/img/h5/no.png"></div>
                    </if>
                </ul>
            </div>
        </div>
        <div class="usercenter  acdv" id="mynavdiv2" style="display: none;">
            <div class="details" style="padding: 18px;padding-top: 0px;">
                <ul class="list-ul">
                    <if condition="$a_data_down" >
                    <volist name="a_data_down" id="ad" key="k"> 
                    <li>
                        <table>
                            <tr>
                                <td  rowspan="3" width="75"><img src="{$ad.headurl}" class="tb"></td>
                                <td><p class="line-limit-length">{$ad.nickname}</p></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>ID：{$ad.id}</td>
                                <td align="right">{$ad.regtime}</td>
                            </tr>
                            <tr>
                                <td>{$ad.grade}</td>
                                <td></td>
                            </tr>
                        </table>
                    </li>
                    </volist>
                    <else/>
                    <div class="nos"><img src="/src/img/h5/no.png"></div>
                    </if>
                </ul>
            </div>
        </div>
    </body>

        <script>
            $("#mynav1").click(function(event) {
                $("#mynav1").removeClass("nav-atv");
                $("#mynav2").removeClass("nav-atv");
                $("#mynav1").removeClass("nav-a");
                $("#mynav2").removeClass("nav-a");
                
                $("#mynav1").addClass("nav-atv");
                $("#mynav2").addClass("nav-a");

                $("#mynavdiv1").show();
                $("#mynavdiv2").hide();
            });
            $("#mynav2").click(function(event) {
                $("#mynav1").removeClass("nav-atv");
                $("#mynav2").removeClass("nav-atv");
                $("#mynav1").removeClass("nav-a");
                $("#mynav2").removeClass("nav-a");
                
                $("#mynav1").addClass("nav-a");
                $("#mynav2").addClass("nav-atv");
                $("#mynavdiv1").hide();
                $("#mynavdiv2").show();
            });
        </script>
</html>