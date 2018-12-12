<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>比赛信息</title>
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
        <script>
            (function (doc, win) {
                var docEl = doc.documentElement, resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize', recalc = function () {
                    var clientWidth = docEl.clientWidth;
                    if (!clientWidth)
                        return;
                    if (clientWidth >= 640) {
                        docEl.style.fontSize = '100px';
                    } else {
                        docEl.style.fontSize = 100 * (clientWidth / 640) + 'px';
                    }
                };
                if (!doc.addEventListener)
                    return;
                win.addEventListener(resizeEvt, recalc, false);
                doc.addEventListener('DOMContentLoaded', recalc, false);
            })(document, window)
        </script>
        <style type="text/css" media="screen">
            .sysm{
                z-index: 55;
                position: absolute;
                top: 78px;
                text-align: center;
                width: 100%;
            }
            .tc{
                z-index: 70;
                position: absolute;
                top: 0px;
                text-align: center;
                width: 100%;
                display: none;
            }
            .des{
                z-index: 50;
                font-size: 0.28rem;
                position: absolute;
                width: 100%;
                line-height: 0.38rem;
                top: 102px;
                text-align: center;
            }
            .ztmc{
                top: 648px;
                font-size: 0.1rem;
                position: absolute;
                width: 100%;
                z-index: 50;
                text-align: center;
            }
            .title{
                top: 4px;
                font-size: 0.45rem;
                position: absolute;
                z-index: 50;
                text-align: center;
                color: #fff;
                width: 100%;
                font-weight:900;
                letter-spacing: 2px;
                text-shadow:#307340 0.03rem 0 0,#307340 0 0.03rem 0,#307340 -0.03rem 0 0,#307340 0 -0.03rem 0,0px 0.07rem #307340;
            }
            .jpxx{
                background-image: url(/src/img/custom/title_bg.png);
                background-repeat:no-repeat; 
                background-size:100% 100%;
                -moz-background-size:100% 100%;
                margin-left: 22%;
                margin-right: 22%;
                height: 1rem;
                font-size: 0.27rem;
                line-height: 0.8rem;
                font-weight:bold;
                color: #fff;
                letter-spacing: 3px;
            }
            .dszt{
                z-index: 55;
                position: absolute;
                top: 324px;
                text-align: center;
                width: 100%;
            }
            .bsdes{
                z-index: 50;
                font-size: 0.28rem;
                position: absolute;
                width: 100%;
                line-height: 0.38rem;
                top: 295px;
            }
            .ljsx{
                z-index: 55;
                position: absolute;
                top: 447px;
                text-align: center;
                width: 100%;
            }
            .ljsxdes{
                z-index: 50;
                font-size: 0.28rem;
                position: absolute;
                width: 100%;
                line-height: 0.38rem;
                top: 401px;
            }
            .ljpz{
                z-index: 55;
                position: absolute;
                top: 445px;
                text-align: center;
                width: 100%;
            }
            .sjhm{
                z-index: 55;
                position: absolute;
                top: 443px;
                width: 70%;
                padding-left: 20%;
            }
            .sjhm input{
                height: 0.6rem;
                width: 3.1rem;
                border-radius:0.1rem;
                border: 0px;
                padding-left: 6px;
                font-size: 0.25rem;
                background-color: #f9fff5;
            }
            .bsbm{
                z-index: 55;
                position: absolute;
                top: 655px;
                width: 100%;
                text-align: center;
                font-size: 0.3rem;
            }
            .tcs{
                z-index: 70;
                position: absolute;
                top: 0px;
                text-align: center;
                width: 100%;
                height: 100%;
                background-color:rgba(0,0,0,0.8);
                display: none;
            }
            .tcs div{
                color:white;
                color: white;
                padding: 10px;
                line-height: 25px;
                letter-spacing: 1.5px;
                font-size: 14px;
            }
            .bmcs{
                background-image: url(/src/img/custom/bm_btn.png);
                width: 50%;
                margin-left: 25%;
                height: 1.3rem;
                margin-top: 2px;
                border: 0px;
                background-repeat:no-repeat; 
                background-size:100% 100%;
                -moz-background-size:100% 100%;
            }
        </style>
    </head>
    <body class="huibg">
        <div>
            <img style="width: 100%;" src="/src/img/custom/bm_bg1.jpg" id="code">
            <div class="tc">
                <img style="width: 100%;" src="/src/img/custom/tc.png" id="code">
            </div>
            <div class="tcs">
                <div style="text-align:right;margin-right: 1%" id="close"><img src="/src/img/custom/close.png" width="30"></div>
                <div style="margin-top:35%;font-size: 0.6rem;">兑换比赛门票</div>
                <div style="margin-top:8%;font-size: 0.3rem;line-height: 0.5rem">本次比赛门票价格为 <?php echo $custom_info["tickets"]; ?> <br>需要 <?php echo ceil($custom_info["tickets"]); ?> <img src="/src/img/custom/zs.png" width="20"> 兑换</div>
                <div style="margin-top:15%;font-size: 0.2rem;line-height: 0.5rem">当前钻石数量：<?php echo $game_user_info["cashpoint"]; ?></div>
                <div><input type="button" value="确 定" style="border: 0px;width: 60%;height: 45px;font-size: 18px;border-radius: 5px;background: #F18C65;" id="qdtj"></div>
            </div>
            <div class="title">
                <div style="margin-left: 15%;margin-right: 15%;"><?php echo $custom_info["name"]; ?></div>
            </div>
            <div class="sysm">
                <div class="jpxx">奖品信息</div>
            </div>
            <div  class="des">
                <div style="margin-left: 5%;margin-right: 5%;background-color: #f9fff5;border-radius:0.1rem;">
                <table style="width:100%;">
                    <tr>
                        <td style="height:0.3rem;"></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td  style="height:0.45rem;">名次</td>
                        <td>奖品名称</td>
                        <td>奖品价值</td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <div style="border-bottom:1.5px solid #85fe1a;width: 94%;margin-left: 3%;"></div>
                        </td>
                    </tr>
                    <?php if($custom_info['prizes1_name']){?>
                    <tr>
                        <td style="height:0.45rem;">冠军</td>
                        <td><?php echo $custom_info["prizes1_name"]; ?></td>
                        <td><?php echo $custom_info["prizes1_value"]; ?>￥</td>
                    </tr>
                    <?php }?>
                    <?php if($custom_info['prizes2_name']){?>
                    <tr>
                        <td style="height:0.42rem;">亚军</td>
                        <td><?php echo $custom_info["prizes2_name"]; ?></td>
                        <td><?php echo $custom_info["prizes2_value"]; ?>￥</td>
                    </tr>
                    <?php }?>
                    <?php if($custom_info['prizes3_name']){?>
                    <tr>
                        <td style="height:0.42rem;">季军</td>
                        <td><?php echo $custom_info["prizes3_name"]; ?></td>
                        <td><?php echo $custom_info["prizes3_value"]; ?>￥</td>
                    </tr>
                    <?php }?>
                    <?php if($custom_info['prizes4_name']){?>
                    <tr>
                        <td style="height:0.42rem;">第四名</td>
                        <td><?php echo $custom_info["prizes4_name"]; ?></td>
                        <td><?php echo $custom_info["prizes4_value"]; ?>￥</td>
                    </tr>
                    <?php }?>
                    <?php if($custom_info['prizes5_name']){?>
                    <tr>
                        <td style="height:0.42rem;">第五名</td>
                        <td><?php echo $custom_info["prizes5_name"]; ?></td>
                        <td><?php echo $custom_info["prizes5_value"]; ?>￥</td>
                    </tr>
                    <?php }?>
                    <?php if($custom_info['join_prizes_name']){?>
                    <tr>
                        <td style="height:0.42rem;">参与奖</td>
                        <td><?php echo $custom_info["join_prizes_name"]; ?></td>
                        <td><?php echo $custom_info["join_prizes_value"]; ?>￥</td>
                    </tr>
                    <?php }?>
                    <tr>
                        <td style="height:0.1rem;"></td>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
                </div>
            </div>
            <div class="dszt">
                <div class="jpxx">比赛信息</div>
            </div>
            <div  class="bsdes">
                <div style="margin-left: 5%;margin-right: 5%;background-color: #f9fff5;border-radius:0.1rem;">
                    <div style="padding-top:7%;padding-left: 0.3rem;">比赛时间：<?php echo date("Y年m月d日 H:i", $custom_info["start_date"]); ?></div>
                    <div style="padding-top:2px;padding-left: 0.3rem;">比赛创建者：<?php echo $cu_info["nickname"]; ?></div>
                    <div style="padding-top:2px;padding-left: 0.3rem;padding-bottom: 0.15rem;">比赛地点：手机、平板</div>
                </div>
            </div>
            <div class="ljsx">
                <div class="jpxx">领奖事项</div>
            </div>
            <div  class="ljsxdes">
                <div style="margin-left: 5%;margin-right: 5%;background-color: #f9fff5;border-radius:0.1rem;">
                    <div style="padding-top:7%;padding-left: 0.3rem;">1.获得名次者需要填写收货地址，以便发奖者送出奖品；</div>
                    <div style="padding-top:2px;padding-left: 0.3rem;">2.发奖动态可在公众号的“个人中心”-“我的奖品”中查看；</div>
                    <div style="padding-top:2px;padding-left: 0.3rem;padding-bottom: 0.15rem;">3.领取或收到奖品后请及时在公众号的“个人中心”-“我的奖品”中核销；
</div>
                </div>
            </div>
            <div  class="bsbm">
                <div>
                    <table width="95%">
                        <tr>
                            <td>
                                扫描右方二维码<br>
                                可查看比赛详情<br>
                                关注公众号后报名参赛
                            </td>
                            <td width="30%"><img src="{$image}"  style="width: 100%;"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <script>
            $("#lqfs").click(function(){
                $(".tc").show();
            });
            $(".tc").click(function(){
                $(".tc").hide();
            });
            $("#qdtj").click(function(){
                //if(confirm("是否确定兑换？")){
                    $("#myform").submit();
                //}
            });
            $("#close").click(function(){
               $(".tcs").hide();
            });
            dw();
            function dw(){
                var height = $(window).height();
                console.log($("#code").height());
                var h = $("#code").height();
                $(".sysm").css("top", h / 8.55);
                $(".dszt").css("top", h / 2.41);
                $(".des").css("top", h / 6.5);
                $(".bsdes").css("top", h / 2.26);
                $(".ztmc").css("top", h / 1.029);
                $(".title").css("top", h /166.75);
                $(".ljpz").css("top", h / 1.755);
                $(".sjhm").css("top", h / 1.5056);
                $(".bsbm").css("top", h / 1.192);
                $(".ljsx").css("top", h / 1.747);
                $(".ljsxdes").css("top", h / 1.663);
            }
            
            $(document).ready(function () {
                setTimeout(dw,500);   //1s 即1000ms 
            });
        </script>
    </body></html>