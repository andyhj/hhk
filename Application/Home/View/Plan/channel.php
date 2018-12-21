<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>通道列表</title>
        <link rel="stylesheet" href="/src/css/main.f23740f4.css">
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
            body{
                background-color: #eeeeee;
            }
        </style>
    </head>
    <body>
        <div class="channel_body">
            <div class="channel_title">选择通道</div>
            <div class="channel_list" id="channel_list">
                <?php foreach ($channels as $k => $channel): ?>
                    <div class="item">
                        <div class="title">
                            <span class="icon"><img src="/src/img/channel/bank_icon.png" alt=""></span>
                            <span><?php echo $channel['title']; ?></span>
                        </div>
                        <div class="detail">
                            <span><?php echo $channel['quota']; ?></span>
                            <span></span>
                        </div>
                        <div class="time">
                            <span>普通会员：<?php echo ($channel['channel_info']["user_fee"]*100)."%+".(int)$channel['channel_info']["user_close_rate"]; ?></span>
                            <span class="rate">PLUS会员：<?php echo ($channel['channel_info']["plus_user_fee"]*100)."%+".(int)$channel['channel_info']["plus_user_close_rate"]; ?></span>
                        </div>
                        <div class="tips"><span class="tips_info"><?php echo $channel['prompt']; ?></span><span class="tips_more"></span></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php include T('Common/footer'); ?>
    </body>
<script>
    var channelList = document.querySelector('#channel_list');
    channelList.onclick = function (event) {
        event = event || window.event
        var target = event.target || event.srcElement

        if (target.classList.contains('tips_more')) {
            var moreInfo = target.parentNode.firstChild
            if (moreInfo.classList.contains('unfold')) {
                moreInfo.classList.remove('unfold')
            } else {
                moreInfo.classList.add('unfold')
            }
        }
    }
    
</script>
</html>