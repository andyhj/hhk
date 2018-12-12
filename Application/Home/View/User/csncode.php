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
            .sub-btnlg {
                width: 45%;
                height: 30px;
                border: 0px;
                background: #FF2626;
                color: #fff;
                font-size: 15px;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <?php include T('Common/nav'); ?>

        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">佣金兑换列表</a>
            <button class="topnav" id="open-button"><span class="iconfont icon-1"></span></button>
        </nav>
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="sp1">
                <ul class="ddlist">
                    <if condition="$shopList" >
                    <volist name="shopList" id="ol" key="k"> 
                    <li class="red">
                        <table class="tablecolor-777676" style="height:80px">
                            <tr>
                                <td width="60%">
                                    <p class="tx14 padd5">{$ol.name|strip_tags}</p>
                                </td>
                                <td class="imgtd">
                                    <p class="tx16 tablecolor-red name">
                                        <button type="button" class="sub-btnlg" id="exchange" data-id="{$ol.id}">兑换</button>
                                    </p>
                                </td>
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

        <script>
            $("#exchange").click(function(event) {
                var id = $(this).attr("data-id");
                console.log(id);
                if(confirm("确定要兑换吗？")){
                    $.ajax({
                        type: 'get',
                        url: '{$shopUrl}&item_id='+id,
                        dataType:'json', 
                        success: function(json) {
                            if(json["status"]==200){
                                alert("提交成功");
                            }else{
                                alert(json["info"]);
                            }
                        }
                    });
                }
            });
        </script>
        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>

    </body></html>