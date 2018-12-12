<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>{$gl_info.title}</title>
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
            title: "您的好友<?=$nickname?>邀请你玩游戏",
            desc: "无需下载安装，点击即玩，立即组局，邀请朋友打牌",
            link: "<?=$s_url?>",
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
    </head>
    <body class="huibg">
        
<!--       <div class="usercenter  accdv">
            <div style="text-align: center;size: 12px;">
                <img style="-webkit-user-select: none; max-width: 3750px;" src="{$image}">
            </div>
            <div style="text-align: center;size: 12px;">
                点击右上角分享给朋友
            </div>
            
        </div>-->
        <div style="height: 100%;background: #000;">
            <img style="width: 100%;" src="<?=ADMIN_HOST; ?>{$gl_info.image}">
        </div>
        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>

    </body></html>