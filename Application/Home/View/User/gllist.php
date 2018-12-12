<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>我的推广列表</title>
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
                margin-top: 10px;
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
            .ddlist {
                padding-bottom: 15px;
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
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="sp1">
                <ul class="ddlist">
                    <if condition="$gl_list" >
                    <li>
                    <volist name="gl_list" id="cr" key="k"> 
                        <a href="glinfo.html?id={$cr.id}">
                            <table style="margin:0 0 0 0px;height: 135px;" class="jj-bg">
                                <tr height="60">
                                    <td width="30%" align="center"><span><if condition="$cr['s_img']" ><img src="<?=ADMIN_HOST; ?>{$cr.s_img}" alt="" width="70" height="70" style="margin-top: 15px;"></if></span></td>
                                    <td width="70%" style="font-size:18px;font-weight: bold;">{$cr.title}</td>
                                </tr>
                                <tr>
                                    <td height="40" width="30%" align="center"><span style="margin-top: -10px;">开心娱乐</span></td>
                                    <td width="70%">{$cr.add_date|date="Y-m-d",###}</td>
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