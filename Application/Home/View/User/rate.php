<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>PLUS会员</title>
        <?php include T('Common/header'); ?>
        <script>
            wx.config({
                debug: false,
                appId: '<?= $wx_config['appId'] ?>',
                timestamp: "<?= $wx_config['timestamp'] ?>",
                nonceStr: '<?= $wx_config['nonceStr'] ?>',
                signature: '<?= $wx_config['signature'] ?>',
                jsApiList: [
                    'onMenuShareTimeline',
                    'onMenuShareAppMessage',
                    'hideMenuItems'
                ]
            });
            wx.ready(function () {
                var shareData64 = {
                    title: "<?= $custom_info['name'] ?>",
                    desc: "",
                    link: "<?= $wx_share_url ?>",
                    imgUrl: "<?= CDN_HOST; ?>/images/share/lobby/gameicon.png",
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
            .cpjs{
                position:absolute;
                top:0.1rem;
            }
            .cpjs-bg{
                width:94%;
                margin-left: 3%;
                height: 7.2rem;
            }
            .task{
                position:absolute;
                top:7.42rem;
            }
            .task-bg{
                width:94%;
                margin-left: 3%;
                height: 2.5rem;
            }
            .des{
                position:absolute;
                top:0;
                z-index: 10;
                width: 100%;
                height: 4rem;
            }
        </style>
    </head>
    <body style="color:#595757;">
        <div style="width:100%;overflow:hidden;height: 100vh">
            <img src="/src/img/plus/plus_bg.jpg" style="width: 100%;display:block;">
        </div>
        <div class="cpjs">
            <div><img src="/src/img/plus/task.png" class="task-bg"></div>
            <div class="des">
                <div style="text-align:center;margin-top:10%;">普通会员：0.70%+1</div>
                <div style="text-align:center;margin-top: 0.2rem;">PLUS会员：0.55%+1</div>
            </div>
        </div>
        <?php include T('Common/footer'); ?>
    </body>
</html>