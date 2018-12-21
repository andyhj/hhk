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
                padding: 15px 5px;
                padding-right: 13px;
                margin-bottom: -1px;
                border-bottom: 1px solid #ddd;
                height: 50px;
            }
            .tb{
                margin-top: -6px;
                margin-right: 15px;
                width: 35px;
            }
            .kjtd{
                margin-bottom: 0.1rem;
                background-color: #fff;
                height: 0.8rem;
                line-height: 0.8rem;
                font-size: 0.3rem;
            }
            .kjtd img{
                width:0.6rem;
                margin-left: 0.4rem;
                margin-right: 0.1rem;
            }
            .splus{
                float: right;
                font-size: 0.2rem;
                height: 0.6rem;
                line-height: 0.45rem;
                color: #f9843c;
            }
            .splus input{
                border-style:solid;
                border-radius: 1rem;
                border-width:0.01rem;
                width: 1.1rem;
                height: 0.45rem;
                font-size: 0.2rem;
                color: #595757;
                border-color:#595757;
                margin-left: 0.2rem;
            }
        </style>
    </head>
    <body>
        <div class="kjtd"><img src="/src/img/channel/bank_icon.png" alt=""> 银联快捷H</div>
        <div class="usercenter  acdv">
            <div class="details" style="padding: 18px;padding-top: 0px;">
                <ul class="list-ul">
                    <li>
                        银行卡：
                            <span  style="float:right;">
                                <select>
                                    <option value="0">---选择银行卡---</option>
                                    <option value="-1">---添加银行卡---</option>
                                    <option value="-2">---解除银行卡---</option>
                                </select>
                            </span>
                    </li>
                    <li>
                        账单日：
                        <span  style="float:right;">2号</span>
                    </li>
                    <li>
                        还款日：
                        <span  style="float:right;">25号</span>
                    </li>
                    <li>
                        当前费率：
                        <span  style="float:right;">0.65%+1</span>
                    </li>
                </ul>
                <div class="splus">PLUS会员：0.55%+1  成为PLUS会员降低费率 <input type="button" value="去升级"></div>
            </div>
            
        </div>
        <?php include T('Common/footer'); ?>
    </body>
</html>