<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>奖品信息</title>
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
        <style type="text/css" media="screen">
            .ranking{
                border:1px dashed #8E8A8A;
                width:100%;
                border-radius: 10px;
                margin-top:10px;
                background: #F3F0F0;
            }
            .ranking table{
                width:100%;
                color: #000;
                margin-top: 15px;
                margin-bottom: 15px;
            }
            .ranking td{
                height:30px;
            }
            .ranking td span{
                margin: 15px;
            }
            .ranking td img{
                border-radius: 8px;
                margin: 20px;
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
        </style>
    </head>
    <body>
        <?php include T('Common/nav'); ?>

        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">奖券信息</a>
            <button class="topnav" id="open-button"><span class="iconfont icon-1"></span></button>
        </nav>
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="sp1">
                <ul class="ddlist">
                    <li>
                        <div class="ranking">
                            <table style="margin: 0px;font-size: 14px;">
                                <tr>
                                    <td><span>比赛名称：<?php echo $rankingInfo["name"];?></span></td>
                                </tr>
                                <tr>
                                    <td><span>比赛时间：<?php echo date("Y-m-d H:i:s",$customInfo["start_date"]);?></span></td>
                                </tr>
                                <tr>
                                    <td><span>比赛名次：<?php if($rankingInfo['ranking']){?>第<?php echo $rankingInfo["ranking"];?>名<?php }?></span></td>
                                </tr>
                                <?php if($rankingInfo['prizes1_name']){?>
                                <tr>
                                    <td><span>奖品名称：<?php echo $rankingInfo["prizes1_name"];?></span></td>
                                </tr>
                                <tr>
                                    <td><span>奖品价值：<?php echo $rankingInfo["prizes1_value"];?></span></td>
                                </tr>
                                <?php }?>
                                <?php if($rankingInfo['prizes2_name']){?>
                                <tr>
                                    <td><span>奖品名称：<?php echo $rankingInfo["prizes2_name"];?></span></td>
                                </tr>
                                <tr>
                                    <td><span>奖品价值：<?php echo $rankingInfo["prizes2_value"];?></span></td>
                                </tr>
                                <?php }?>
                                <?php if($rankingInfo['prizes3_name']){?>
                                <tr>
                                    <td><span>奖品名称：<?php echo $rankingInfo["prizes3_name"];?></span></td>
                                </tr>
                                <tr>
                                    <td><span>奖品价值：<?php echo $rankingInfo["prizes3_value"];?></span></td>
                                </tr>
                                <?php }?>
                                <?php if($rankingInfo['prizes4_name']){?>
                                <tr>
                                    <td><span>奖品名称：<?php echo $rankingInfo["prizes4_name"];?></span></td>
                                </tr>
                                <tr>
                                    <td><span>奖品价值：<?php echo $rankingInfo["prizes4_value"];?></span></td>
                                </tr>
                                <?php }?>
                                <?php if($rankingInfo['prizes5_name']){?>
                                <tr>
                                    <td><span>奖品名称：<?php echo $rankingInfo["prizes5_name"];?></span></td>
                                </tr>
                                <tr>
                                    <td><span>奖品价值：<?php echo $rankingInfo["prizes5_value"];?></span></td>
                                </tr>
                                <?php }?>
                                <tr>
                                    <td><span>获奖者：<?php echo $userInfo["nickname"];?></span></td>
                                </tr>
                                <tr>
                                    <td><span>联系电话：<?php echo $rankingInfo["mobile"];?></span></td>
                                </tr>
                                <tr>
                                    <td><span>获奖时间：<?php echo date("Y-m-d H:i:s",$rankingInfo["add_time"]);?></span></td>
                                </tr>
                                <tr>
                                    <td><span>奖品有效期：<?php echo $rankingInfo["period"];?> 天</span></td>
                                </tr>
                                <?php if($rankingInfo['prizes1_name']){?>
                                <tr>
                                    <td><span>状态：<?php echo $rankingInfo["prizes1_title"];?></span></td>
                                </tr>
                                <?php if($rankingInfo['prizes1_exchange']==0){?>
                                <tr>
                                    <td align="center">
                                        <input type="button" id="ff" value="发 放" style="margin: 15px;width: 30%;height: 35px;font-size: 18px;border: 0px;background-color:#fdc158;border-radius: 5px;">
                                    </td>
                                </tr>
                                <?php }?>
                                <?php }?>
                                <?php if($rankingInfo['prizes2_name']){?>
                                <tr>
                                    <td><span>状态：<?php echo $rankingInfo["prizes2_title"];?></span></td>
                                </tr>
                                <?php if($rankingInfo['prizes2_exchange']==0){?>
                                <tr>
                                    <td align="center">
                                        <input type="button" id="ff" value="发 放" style="margin: 15px;width: 30%;height: 35px;font-size: 18px;border: 0px;background-color:#fdc158;border-radius: 5px;">
                                    </td>
                                </tr>
                                <?php }?>
                                <?php }?>
                                <?php if($rankingInfo['prizes3_name']){?>
                                <tr>
                                    <td><span>状态：<?php echo $rankingInfo["prizes3_title"];?></span></td>
                                </tr>
                                <?php if($rankingInfo['prizes3_exchange']==0){?>
                                <tr>
                                    <td align="center">
                                        <input type="button" id="ff" value="发 放" style="margin: 15px;width: 30%;height: 35px;font-size: 18px;border: 0px;background-color:#fdc158;border-radius: 5px;">
                                    </td>
                                </tr>
                                <?php }?>
                                <?php }?>
                                <?php if($rankingInfo['prizes4_name']){?>
                                <tr>
                                    <td><span>状态：<?php echo $rankingInfo["prizes4_title"];?></span></td>
                                </tr>
                                <?php if($rankingInfo['prizes4_exchange']==0){?>
                                <tr>
                                    <td align="center">
                                        <input type="button" id="ff" value="发 放" style="margin: 15px;width: 30%;height: 35px;font-size: 18px;border: 0px;background-color:#fdc158;border-radius: 5px;">
                                    </td>
                                </tr>
                                <?php }?>
                                <?php }?>
                                <?php if($rankingInfo['prizes5_name']){?>
                                <tr>
                                    <td><span>状态：<?php echo $rankingInfo["prizes5_title"];?></span></td>
                                </tr>
                                <?php if($rankingInfo['prizes5_exchange']==0){?>
                                <tr>
                                    <td align="center">
                                        <input type="button" id="ff" value="发 放" style="margin: 15px;width: 30%;height: 35px;font-size: 18px;border: 0px;background-color:#fdc158;border-radius: 5px;">
                                    </td>
                                </tr>
                                <?php }?>
                                <?php }?>
                                <?php if($rankingInfo['join_prizes_name']){?>
                                <tr>
                                    <td><span>状态：<?php echo $rankingInfo["join_prizes_title"];?></span></td>
                                </tr>
                                <?php if($rankingInfo['join_prizes_exchange']==0){?>
                                <tr>
                                    <td align="center">
                                        <input type="button" id="ff" value="发 放" style="margin: 15px;width: 30%;height: 35px;font-size: 18px;border: 0px;background-color:#fdc158;border-radius: 5px;">
                                    </td>
                                </tr>
                                <?php }?>
                                <?php }?>
                            </table>
                        </div>
                    </li>
                    
                    <?php if($userAddress){?>
                    <li>
                        <div class="ranking">
                            <table style="margin: 0px;font-size: 14px;">
                                <tr>
                                    <td height="50"><span style="font-size:18px;color: red">收货信息</span></td>
                                </tr>
                                <tr>
                                    <td><span>收件人：<?php echo $userAddress["name"];?></span></td>
                                </tr>
                                <tr>
                                    <td><span>电  话：<?php echo $userAddress["phone"];?></span></td>
                                </tr>
                                <tr>
                                    <td><span>邮  编：<?php echo $userAddress["postcode"];?></span></td>
                                </tr>
                                <tr>
                                    <td><span>详细地址：<?php echo $userAddress["address"];?></span></td>
                                </tr>
                            </table>
                        </div>
                    </li>
                    <?php }?>
                </ul>
            </div>
        </div>
        <script>
            $("#ff").click(function(event) {
                if(confirm("是否确定发放？")){
                    location="exchangeall.html?r_id=<?php echo $rankingInfo["id"]; ?>&c_id=<?php echo $rankingInfo["c_id"]; ?>";
                }
            });
        </script>
        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>
    </body>
</html>