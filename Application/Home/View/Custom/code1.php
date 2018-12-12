<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?php echo $custom_info["name"]; ?></title>
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
                z-index: 50;
                position: absolute;
                top: 295px;
                text-align: center;
                width: 100%;
            }
            .des{
                margin: 2%;
                z-index: 50;
                font-size: 0.25rem;
                position: absolute;
                width: 100%;
                line-height: 0.35rem;
                top: 350px;
            }
            .ztmc{
                top: 536px;
                font-size: 0.15rem;
                position: absolute;
                width: 100%;
                z-index: 50;
                margin: 2%;
            }
            .title{
                top: 50px;
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
            .fj{
                top: 50px;
                font-size: 0.45rem;
                position: absolute;
                z-index: 40;
                text-align: center;
                color: #fff;
                width: 20%;
                left: 100%;
            }
        </style>
    </head>
    <body class="huibg">
        <div>
            <div class="fj" id="target"><img src="/src/img/custom/01.png"></div>
            <img style="width: 100%;" src="/src/img/custom/tg_code.jpg" id="code">
            <div class="title">
                <div style="margin-left: 15%;margin-right: 15%;"><?php echo $custom_info["name"]; ?></div>
            </div>
            <div class="sysm">
                <img style="width: 27%;" src="{$image}" >
            </div>
            <div  class="des">
                比赛时间：<?php echo date("Y年m月d日 H:i", $custom_info["start_date"]); ?><br>
                比赛地点：带网络连接的手机或平板<br>
                比赛规则：<br>
                &emsp;&emsp;&emsp;&emsp;1.到达开赛时间准时开始比赛；<br>
                &emsp;&emsp;&emsp;&emsp;2.比赛按常规斗地主游戏规则进行；<br>
                &emsp;&emsp;&emsp;&emsp;3.比赛内设淘汰分，并随时间而增长；<br>
                &emsp;&emsp;&emsp;&emsp;4.开始比赛时给予一定积分，当积分为<br>
                &emsp;&emsp;&emsp;&emsp;&ensp; 零或低于淘汰分时被淘汰；<br>
                &emsp;&emsp;&emsp;&emsp;5.当剩余人数少于6人时比赛结束；<br>
            </div>
            <div  class="ztmc">
                <span>主办：<?php echo $cu_info["nickname"]; ?></span>
                <span style="float:right;margin-right: 4%;">承办：深圳开心娱乐科技有限公司</span>
            </div>
        </div>
        
        <script>
            function dw(){
                var height = $(window).height();
                console.log(height);
                var h = $("#code").height();
                $(".sysm").css("top", h / 2.27);
                $(".des").css("top", h / 1.5);
                $(".ztmc").css("top", h / 1.053);
                $(".title").css("top", h / 13.34);
                $(".fj").css("top", h / 7);
            }
            
            $(document).ready(function () {
                setTimeout(dw,500);   //1s 即1000ms 
            });
            var i = 2;
            var j = 0;
            var d = 1;
            window.onload = function() {
            var e = target;
            var win = document.documentElement || document.body;
            
            e.style.left = win.clientWidth + "px";
            function intern() {
                var width = e.clientWidth;
                var height = e.clientHeight;
                var left = parseFloat(e.style.left);
                var top = parseFloat(e.style.top);
                var windowWidth = win.clientWidth;
                var windowHeight = win.clientHeight;
                var leftc = windowWidth - i;
                if(leftc<-100){
                    e.style.left = win.clientWidth + "px";
                    i = 2;
                }else{
                    i = i+2;
                    e.style.left = leftc + "px";
                }
            }
            //intern();
            setInterval(intern, 20);
            $("#target").click(function(){
                intern();
            });
            function bk(){
                if(d%2==0){
                        $(".fj").html('<img src="/src/img/custom/01.png">');
                }else{
                        $(".fj").html('<img src="/src/img/custom/02.png">');
                }
                d++;
                var dds=window.setTimeout(bk,200);
            }
            bk();
        };
        </script>
    </body></html>