<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>制定计划</title>
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
                margin-bottom: 3px;
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
            .sjplus{
                border-style:solid;
                border-radius: 1rem;
                border-width:0.01rem;
                width: 1.3rem;
                height: 0.45rem;
                line-height: 0.44rem;
                font-size: 0.2rem;
                color: #f9843c;
                border-color:#f9843c;
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
        <div class="kjtd"><img src="/src/img/channel/bank_icon.png" alt=""> 银联快捷H</div>
        <div class="usercenter  acdv" style="margin-bottom: 0.2rem;">
            <div class="details" style="padding: 18px;padding-top: 0px;padding-bottom: 1px;">
                <ul class="list-ul" style="margin-bottom: 0px;">
                    <li>
                        银行卡：
                            <span  style="float:right;">
                                <select id="bc_id" name="bc_id">
                                    <option value="0">------选择银行卡------</option>
                                    <?php foreach ($bank_card_list as $k=>$v){?>
                                        <option  value="<?php echo $v['id']?>">
                                            <?php echo $v['bank_name'] . substr_replace($v['card_no'],'****',4,-4);?>
                                        </option>
                                    <?php }?>
                                    <option value="-1">------添加银行卡------</option>
                                    <option value="-2">------解除银行卡------</option>
                                </select>
                            </span>
                    </li>
                    <li>
                        账单日：
                        <span  style="float:right;" id="bill"></span>
                    </li>
                    <li>
                        还款日：
                        <span  style="float:right;" id="repayment"></span>
                    </li>
                    <li>
                        当前费率：0.65%+1 
                        <span  style="float:right;"><input type="button" value="升级PLUS" class="sjplus"></span>
                    </li>
                    <li style="border-bottom:0px;height: 43px;padding-top: 9px;">
                        <span  class="splus">PLUS会员：0.55%+1  成为PLUS会员降低费率</span>
                    </li>
                    <li style="    border-bottom: 0px;height: 3px;background-color: #eeeeee;padding: 0px;margin: 0px;width: 110%;left: -5%;">
                        
                    </li>
                    <li>
                        计划金额：
                        <span  style="float:right;"><input type="text" value="" placeholder="输入金额" style="text-align: right;"></span>
                    </li>
                    <li>
                        计划期数：
                            <span  style="float:right;">
                                <select>
                                    <option value="0">---选择期数---</option>
                                    <option value="6"> 6期</option>
                                    <option value="12"> 12期</option>
                                    <option value="24"> 24期</option>
                                </select>
                            </span>
                    </li>
                    <li>
                        每期还款金额：
                        <span  style="float:right;">1024</span>
                    </li>
                    <li>
                        每期手续费：
                        <span  style="float:right;">21</span>
                    </li>
                    <li>
                        手续费总额：
                        <span  style="float:right;">512</span>
                    </li>
                    <li style="    border-bottom: 0px;height: 3px;background-color: #eeeeee;padding: 0px;margin: 0px;width: 110%;left: -5%;">
                        
                    </li>
                    <li style="border-bottom:0px;height: 43px;padding-top: 9px;">
                        <span  class="splus" style="font-size: 0.26rem;">PLUS会员手续费总额：412</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="channel_submit"><div id="save">提交计划</div></div>
        <?php include T('Common/footer'); ?>
        <script>
            //选择付款账户
            $("#bc_id").change(function () {
                var id = $(this).val();
                if (id == 0) {
                    $("#bill").html("");
                    $("#repayment").html("");
                    return;
                }
                if (id == -1) {
                    location.href = "<?php echo $add_cart_url; ?>";
                    return;
                }
                if (id == -2) {
                    return;
                } else {
                    $.ajax({
                        url: "<?php echo $getcart_url; ?>",
                        data: {id: id,c_code:'<?php echo $c_code; ?>'},
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            if (data.status == 200) {
                                $("#bill").html(data.data.bill+"号");
                                $("#repayment").html(data.data.repayment+"号");
                            } else {
                                alert(data.info);
                            }
                        }
                    });
                }
            });
        </script>
    </body>
</html>