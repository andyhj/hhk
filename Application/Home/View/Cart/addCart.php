<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>添加银行卡</title>
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
            .splus{
                float: right;
                font-size: 0.2rem;
                height: 0.6rem;
                line-height: 0.45rem;
                color: #f9843c;
            }
            .sjplus{
                border-style:solid;
                border-radius: 0.1rem;
                border-width:0.01rem;
                width: 1.5rem;
                height: 0.55rem;
                line-height: 0.5rem;
                font-size: 0.25rem;
                color: #595757;
                border-color:#595757;
                margin-left: 0.2rem;
            }
            #save{
                width: 3.5rem;
                height: 1.2rem;
                margin: 0 auto;
                color: #fff;
                font-size: 0.35rem;
                line-height: 1.1rem;
                background-position: 50%;
                background-size: 100%;
                background-image: url(/src/img/channel/bt_bg.5ca0d26.png);
            }
            .channel_submit{
                margin-bottom: 1rem;
            }
        </style>
    </head>
    <body>
        <div class="usercenter  acdv" style="margin-bottom: 0.2rem;">
            <div class="details" style="padding: 18px;padding-top: 0px;padding-bottom: 1px;">
                <ul class="list-ul" style="margin-bottom: 0px;">
                    <li>
                        持卡人：
                        <span  style="float:right;">张三</span>
                    </li>
                    <li>
                        卡号：
                        <span  style="float:right;"><input type="text" value="" placeholder="银行卡卡号" style="text-align: right;"></span>
                    </li>
                    <li>
                        CVN2：
                        <span  style="float:right;"><input type="text" value="" placeholder="信用卡背后3位CVN2" style="text-align: right;"></span>
                    </li>
                    <li>
                        有效期：
                        <span  style="float:right;"><input type="text" value="" placeholder="示例:09/15 输入0915" style="text-align: right;"></span>
                    </li>
                    <li>
                        发卡银行：
                            <span  style="float:right;">
                                <select>
                                    <option value="0">---选择发卡行---</option>
                                    <option value="中国银行">中国银行</option>
                                    <option value="招商银行">招商银行</option>
                                    <option value="平安银行">平安银行</option>
                                    <option value="中信银行">中信银行</option>
                                    <option value="交通银行">交通银行</option>
                                    <option value="兴业银行">兴业银行</option>
                                    <option value="广发银行">广发银行</option>
                                    <option value="上海银行">上海银行</option>
                                    <option value="华夏银行">华夏银行</option>
                                    <option value="宁波银行">宁波银行</option>
                                    <option value="包商银行">包商银行</option>
                                    <option value="广州银行">广州银行</option>
                                    <option value="中国工商银行">中国工商银行</option>
                                    <option value="中国农业银行">中国农业银行</option>
                                    <option value="中国建设银行">中国建设银行</option>
                                    <option value="中国民生银行">中国民生银行</option>
                                    <option value="中国光大银行">中国光大银行</option>
                                    <option value="中国邮政储蓄银行">中国邮政储蓄银行</option>
                                    <option value="上海浦东发展银行">上海浦东发展银行</option>
                                </select>
                            </span>
                    </li>
                    <li>
                        账单日：
                        <span  style="float:right;"><input type="text" value="" placeholder="输入账单日" style="text-align: right;"></span>
                    </li>
                    <li>
                        还款日：
                        <span  style="float:right;"><input type="text" value="" placeholder="输入还款日" style="text-align: right;"></span>
                    </li>
                    <li>
                        手机号码：
                        <span  style="float:right;"><input type="text" value="" placeholder="银行预留手机号" style="text-align: right;"></span>
                    </li>
                    <li style="border-bottom:0px;">
                        验证码：<span  style="float:right;"><input type="text" value="" placeholder="请输入验证码" style="text-align: right; width: 2rem; height: 0.7rem;"><input type="button" value="发送验证码" class="sjplus"></span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="channel_submit"><div id="save">确定添加</div></div>
        <?php include T('Common/footer'); ?>
    </body>
</html>