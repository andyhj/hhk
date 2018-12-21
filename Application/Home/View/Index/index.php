<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>首页</title>
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
            
            .add{
                width:100%;
                letter-spacing: 2px;
                height: 1.35rem;
                text-align: center;
                position: fixed;
                bottom: 15%;
            }
            .add img{
                width: 3.5rem;
            }
        </style>
    </head>
    <body>
        <div style="width:100%;overflow:hidden;height: 100vh">
            <img src="/src/img/home/bg.jpg" style="width: 100%;display:block;">
        </div>
        <div style="position:absolute;top:2%;left:80%; ">
            <img src="/src/img/home/cpjs.png" style="width:1rem;">
        </div>
        <div class="add">
            <div><img src="/src/img/home/addjh.png"></div>
        </div>
        <?php include T('Common/footer'); ?>
    </body>
</html>