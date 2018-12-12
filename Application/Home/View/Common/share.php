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
            title: "开心逗棋牌",
            desc: "无需下载安装，点击即玩，立即组局，邀请朋友打牌",
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