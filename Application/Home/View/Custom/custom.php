<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>我的比赛</title>
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header'); ?>
        
        <script>
            wx.config({
                debug: false,
                appId: '<?=$wx_config['appId']?>',
                timestamp: "<?=$wx_config['timestamp']?>",
                nonceStr: '<?=$wx_config['nonceStr']?>',
                signature: '<?=$wx_config['signature']?>',
                jsApiList: [
                    'onMenuShareTimeline',
                    'onMenuShareAppMessage',
                    'hideMenuItems'
                ]
            });
            wx.ready(function () {
                var shareData64 = {
                    title: "<?=$custom_info['name']?>",
                    desc: "您的好友 <?=$userInfo['nickname']?> 邀请您打比赛",
                    link: "<?=$wx_share_url?>",
                    imgUrl: "<?=CDN_HOST; ?>/images/share/lobby/gameicon.png",
                    success: function () { 
                        //location=''
                        // 用户确认分享后执行的回调函数

                    },

                    cancel: function () { 

                        // 用户取消分享后执行的回调函数

                    }
                };
                wx.onMenuShareAppMessage(shareData64);
                wx.onMenuShareTimeline(shareData64);
            });

        </script>
        <style>
            .ddlist .custom p{
                font-size: 14px;
                margin: 5px 10px 5px 10px;
            }
            .button{
                    background: red;
                    border-color: red;
                    height: 35px;
                    line-height: 0px;
                    font-size: 17px;
            }
            .sysm{
                z-index: 50;
                border: 1px solid #EFECEC;
                border-radius: 5px;
                width: 150px;
                height: 50px;
                line-height: 20px;
                font-size: 16px;
                background: #fff;
                position: absolute;
                right: 8px;
                top: 42px;
                padding-top: 15px;
            }
            .sub input{
                border: 0px;
                width: 200px;
                height: 40px;
                font-size: 18px;
                border-radius: 5px;
                background: #F18C65;
            }
            .verify{
                width:100%;
                background: #F3F0F0;
                text-align: center;
                height: 40px;
                line-height: 40px;
                color: red;
            }
            .verify-des{
                border:1px dashed #8E8A8A;
                width:96%;
                border-radius: 5px;
                margin:5px 0 5px 2%;
                background: #F3F0F0;
                padding: 10px;
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
            }
            .cus-but{
                background-color: #fdc158;
                border-radius: 5px;
                height: 35px;
                border:0px;
                width: 30%;
            }
        </style>
    </head>
    <body class="huibg" style="background: #fff;">

        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">我的比赛</a>
            <button class="topnav" id="open-sysm"><span class="iconfont icon-1"></span></button>
            <div class="sysm" id="sysm" style="display: none">
                <p id="cjsm">创建说明</p>
            </div>
        </nav>
        <div id="myTabContent" class="tab-content" style="margin-bottom:25px;">
            <div class="tab-pane fade active in" id="sp1">
                    <?php if($custom_info){?>
                    <?php if($custom_info["audit_status"]!=1){ ?>
                        <div class="verify"><?php if($custom_info["audit_status"]==0){ echo "审 核 中";}elseif($custom_info["audit_status"]==2){echo "审核未通过";}?>
                        </div>
                        <?php if($custom_info["audit_status"]==2){ ?>
                        <div class="verify-des"><?php echo $custom_info["audit_info"]; ?></div>
                        <?php } ?>
                    <?php }else{ ?>
                        <div class="verify">已报名：<?php echo $custom_apply_count; ?> 人&emsp;&nbsp;预估收入：<?php $yjsr= ($custom_info["tickets"]-$custom_info["welfare"]-$custom_info["charge"]-($custom_info["tickets"]*0.01))*$custom_apply_count;echo $yjsr>0?$yjsr:0; ?> 元
                        </div>
                    <?php } ?>
                    <div class="ddlist">
                        <div class="custombg">
                            <input type="hidden" name="id" id="id" value="<?php echo $custom_info["id"]; ?>">
                            <table style="margin:0 0 0 0px;">
                                <tr>
                                    <td>
                                        <ul class="f-red-ul" style="font-size: 16px;">
<!--                                            <li>比赛模式</li>-->
                                            <li>比赛名称</li>
                                            <li>游戏类型</li>
                                            <li>场&emsp;&emsp;次</li>
                                            <li>创建者</li>
                                            <li>联系方式</li>
                                            <li>身份证</li>
<!--                                            <li>银行卡号</li>-->
                                            <li>姓&emsp;&emsp;名</li>
                                            <li><?php if($custom_info["type"]==1){echo "开赛人数";}else{echo "轮&emsp;&emsp;数";} ?></li>
                                            <li>开赛时间</li>
                                        </ul>
                                    </td>
                                    <td>
                                        <ul style="list-style: none;text-align: right;margin: 15px 10px 10px 0px;font-size: 16px;">
<!--                                            <li><?php if($custom_info["type"]==1){echo "淘汰模式";}else{echo "轮数模式";} ?></li>-->
                                            <li><?php echo $custom_info["name"]; ?></li>
                                            <li><?php echo $custom_info["game_type"]; ?></li>
                                            <li><?php echo $custom_info["inning"]; ?></li>
                                            <li><?php echo $cu_info["nickname"]; ?></li>
                                            <li><?php echo $cu_info["mobile"]?$cu_info["mobile"]:$cu_info["phone"]; ?></li>
                                            <li><?php echo $cu_info["card_id"]; ?></li>
                                            <li><?php echo $cu_info["bank_card"]; ?></li>
<!--                                            <li><?php echo $cu_info["card_name"]; ?></li>-->
                                            <li><?php echo $custom_info["number"]; ?></li>
                                            <li><?php echo date("Y-m-d H:i:s",$custom_info["start_date"]); ?></li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <div style="border-bottom:2px dashed #dcd9d9;width: 85%"></div>
                                    </td>
                                </tr>
                                <?php if($custom_info['prizes1_name']){?>
                                <tr>
                                    <td colspan="2">
                                        <ul class="f-yellow-ul" style="margin-right: 50px;font-size: 18px;">
                                            <li>冠军奖品</li>
                                        </ul>
                                        <p style="margin-left: 50px;font-size: 12px;margin-right: 35px;">奖品名称：<?php echo $custom_info["prizes1_name"]; ?></p>
                                        <p style="margin-left: 50px;font-size: 12px;margin-right: 35px;">奖品价值：<?php echo $custom_info["prizes1_value"]; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <div style="border-bottom:2px dashed #dcd9d9;width: 85%"></div>
                                    </td>
                                </tr>
                                <?php }?>
                                <?php if($custom_info['prizes2_name']){?>
                                <tr>
                                    <td colspan="2">
                                        <ul class="f-blue-ul" style="margin-right: 50px;font-size: 18px;">
                                            <li>亚军奖品</li>
                                        </ul>
                                        <p style="margin-left: 50px;font-size: 12px;margin-right: 35px;">奖品名称：<?php echo $custom_info["prizes2_name"]; ?></p>
                                        <p style="margin-left: 50px;font-size: 12px;margin-right: 35px;">奖品价值：<?php echo $custom_info["prizes2_value"]; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <div style="border-bottom:2px dashed #dcd9d9;width: 85%"></div>
                                    </td>
                                </tr>
                                <?php }?>
                                <?php if($custom_info['prizes3_name']){?>
                                <tr>
                                    <td colspan="2">
                                        <ul class="f-red-ul" style="margin-right: 50px;font-size: 18px;">
                                            <li>季军奖品</li>
                                        </ul>
                                        <p style="margin-left: 50px;font-size: 12px;margin-right: 35px;">奖品名称：<?php echo $custom_info["prizes3_name"]; ?></p>
                                        <p style="margin-left: 50px;font-size: 12px;margin-right: 35px;">奖品价值：<?php echo $custom_info["prizes3_value"]; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <div style="border-bottom:2px dashed #dcd9d9;width: 85%"></div>
                                    </td>
                                </tr>
                                <?php }?>
                                <?php if($custom_info['prizes4_name']){?>
                                <tr>
                                    <td colspan="2">
                                        <ul class="f-red-ul" style="margin-right: 50px;font-size: 18px;">
                                            <li>第四名奖品</li>
                                        </ul>
                                        <p style="margin-left: 50px;font-size: 12px;margin-right: 35px;">奖品名称：<?php echo $custom_info["prizes4_name"]; ?></p>
                                        <p style="margin-left: 50px;font-size: 12px;margin-right: 35px;">奖品价值：<?php echo $custom_info["prizes4_value"]; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <div style="border-bottom:2px dashed #dcd9d9;width: 85%"></div>
                                    </td>
                                </tr>
                                <?php }?>
                                <?php if($custom_info['prizes5_name']){?>
                                <tr>
                                    <td colspan="2">
                                        <ul class="f-red-ul" style="margin-right: 50px;font-size: 18px;">
                                            <li>第五名奖品</li>
                                        </ul>
                                        <p style="margin-left: 50px;font-size: 12px;margin-right: 35px;">奖品名称：<?php echo $custom_info["prizes5_name"]; ?></p>
                                        <p style="margin-left: 50px;font-size: 12px;margin-right: 35px;">奖品价值：<?php echo $custom_info["prizes5_value"]; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <div style="border-bottom:2px dashed #dcd9d9;width: 85%"></div>
                                    </td>
                                </tr>
                                <?php }?>
                                <?php if($custom_info['join_prizes_name']){?>
                                <tr>
                                    <td colspan="2">
                                        <ul class="f-red-ul" style="margin-right: 50px;font-size: 18px;">
                                            <li>参与奖奖品</li>
                                        </ul>
                                        <p style="margin-left: 50px;font-size: 12px;margin-right: 35px;">奖品名称：<?php echo $custom_info["join_prizes_name"]; ?></p>
                                        <p style="margin-left: 50px;font-size: 12px;margin-right: 35px;">奖品价值：<?php echo $custom_info["join_prizes_value"]; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <div style="border-bottom:2px dashed #dcd9d9;width: 85%"></div>
                                    </td>
                                </tr>
                                <?php }?>
                                <tr>
                                    <td>
                                        <ul class="f-red-ul">
                                            <li>奖品有效期</li>
                                            <li>比赛ID</li>
                                        </ul>
                                    </td>
                                    <td>
                                        <ul style="list-style: none;text-align: right;margin: 15px 10px 10px 15px;font-size: 18px;">
                                            <li><?php echo $custom_info["period"]; ?>天</li>
                                            <li><?php echo $custom_info["id"]; ?></li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <div style="border-bottom:1px solid #dcd9d9;width: 97%"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="center">
                                        <p style="height: 35px;margin: 20px;text-align: center">
                                        <?php if($custom_info["audit_status"]==2){ ?>
                                            <input type="button" id="updcustom" class="cus-but" value="修 改">
                                                <?php } ?>
                                        <?php if($is_exchange==0&&$custom_info["is_del"]==0){ ?>
                                            <input type="button" id="del"  class="cus-but" value="删  除">
                                                <?php } ?>
                                        
                                    </p>
                                    </td>
                                </tr>
                            </table>
                        
                        </div>
                        <?php if($custom_info["audit_status"]==1){ ?>
                        <?php if($custom_info["status"]==0&&$custom_info["is_del"]==0){ ?>
                        <div class="game-tg" id="code">
                            去 推 广
                        </div>
                        <?php } ?>
                        <div class="game-tg" id="bmlb">
                            报名列表
                        </div>
                        <?php } ?>
                        <?php if($custom_info["status"]==2&&$custom_info["is_send"]==1){ ?>
                        <div class="game-tg" id="hjlb">
                            获奖列表
                        </div>
                        <?php } ?>
                    </div>
                    <?php }else{?>
<!--                    <div style="padding-top: 30px;padding-bottom: 30px;text-align: center;" class="sub">
                            <input type="button" onclick="location='addcustom.html'" value="添加比赛">
                    </div>-->
                    <?php }?>
            </div>
        </div>



        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>

<script  type="text/javascript" charset="utf-8" async defer>
    $("#del").click(function(event) {
        if (confirm("是否要删除？")) {
            location="delcustom.html?custom_id="+$("#id").val();
        }
    });
    $("#code").click(function(event) {
        location='<?php echo $code; ?>';
    });
    $("#updcustom").click(function(event) {
        location="addcustom.html?custom_id="+$("#id").val();
    });
    $("#bmlb").click(function(event) {
        location="apply.html?c_id=<?php echo $custom_info["id"]; ?>";
    });
    $("#hjlb").click(function(event) {
        location="rankingdes.html?c_id=<?php echo $custom_info["id"]; ?>";
    });
    $("#open-sysm").click(function(event) {
        var show = $('#sysm').css('display');
        if (show=='none') {
            $("#sysm").css('display','block'); 
        }else{
            $("#sysm").css('display','none'); 
        }
        
    });
    $("#cjsm").click(function(event) {
        location="instruction.html";
        
    });
    $("#lsbs").click(function(event) {
        location="history.html";
        
    });
</script>
    </body></html>