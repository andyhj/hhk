<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>我的团队</title>
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
        <div class="vipcenter">
            <div class="vipheader" style="height: 125px;">
                <div class="des">
                    <div style="padding: 12px 0;border-bottom: 1px solid #ccc;overflow: auto;color: #fff;">
                        <div class="col-xs-6 text-center" style="border-right: 1px solid #ccc;">
                                <span class="icc">等级：</span>
                                <span class="lzz">{$grade}</span>
                                <if condition="$is_upgrade" ><span  style="font-size: 12px;color: red;padding-left: 8px;" id="updAgency">升级</span></if>
                        </div>
                        <div class="col-xs-6 text-center">
                                <span class="icc">佣金总收入：</span>
                                <span class="lzz">{$award_info.earn}</span>
                        </div>
                    </div>
                    <div  style="padding: 16px 0;overflow: auto;color: #fff;">
                        <div class="col-xs-6 text-center" style="border-right: 1px solid #ccc;">
                                <span class="icc">开心豆数量：</span>
                                <span class="lzz">{$gameUser.coinnum}</span>
                        </div>
                        <div class="col-xs-6 text-center">
                                <span class="icc">我的直系下属：</span>
                                <span class="lzz">{$a_data.direct}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="usercenter  acdv" id="mynavdiv2">
            <div style="padding-left:20px;margin-top:10px;border-bottom: 1px solid #ddd;">我的直系下属</div>
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
        $("#updAgency").click(function(event) {
            $.ajax({
                type: 'get',
                url: '{$upd_agency}',
                success: function(json) {
                    if(json["status"]==200){
                        alert("更新成功");
                        window.location.reload();
                    }else{
                        alert(json["info"]);
                    }
                }
            });
        });
    </script>
</html>