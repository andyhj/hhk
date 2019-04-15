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
            select{
                width: auto;
                padding: 0 2%;
                margin: 0;
            }
            option{
                text-align:center;
            }
        </style>
    </head>
    <body>
        <div class="kjtd"><img src="/src/img/channel/bank_icon.png" alt=""> <?php echo $channel_moblie_info["title"];?></div>
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
                        <?php if(!$is_plus){?> 
                        当前费率：<?php echo ($fee*100).'%+'.$close_rate;?> 
                        <span  style="float:right;"><input type="button" value="升级PLUS" class="sjplus"></span>
                        <?php }else{?>
                        当前费率：
                        <span  style="float:right;"><?php echo $fee.'%+'.$close_rate;?> </span>
                        <?php }?>
                    </li>
                    <?php if(!$is_plus){?> 
                    <li style="border-bottom:0px;height: 43px;padding-top: 9px;">
                        <span  class="splus">PLUS会员：<?php echo ($channel_info["plus_user_fee"]*100).'%+'.(int)$channel_info["plus_user_close_rate"];?>   成为PLUS会员降低费率</span>
                    </li>
                    <?php }?>
                    <li style="    border-bottom: 0px;height: 3px;background-color: #eeeeee;padding: 0px;margin: 0px;width: 110%;left: -5%;">
                        
                    </li>
                    <li>
                        计划金额：
                        <span  style="float:right;"><input type="text" value="" id="amount" name="amount" placeholder="输入金额" style="text-align: right;"></span>
                    </li>
                    <li>
                        计划期数：
                            <span  style="float:right;">
                                <select id="periods" name="periods" >
                                    <option value="0">---选择期数---</option>
                                    <option value="6"> 6 期</option>
                                    <option value="12"> 12期</option>
                                    <option value="24"> 24期</option>
                                </select>
                            </span>
                    </li>
                    <li>
                        每期还款金额：
                        <span  style="float:right;" id="p_amount"></span>
                    </li>
                    <li>
                        每期手续费：
                        <span  style="float:right;" id="p_fee"></span>
                    </li>
                    <li>
                        手续费总额：
                        <span  style="float:right;" id="p_amount_count"></span>
                    </li>
                    <li style="border-bottom: 0px;height: 3px;background-color: #eeeeee;padding: 0px;margin: 0px;width: 110%;left: -5%;">
                        
                    </li>
                    <li style="border-bottom:0px;height: 43px;padding-top: 9px;display:none" id="plus_fee">
                        
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
                    location.href = "<?php echo $add_card_url; ?>";
                    return;
                }
                if (id == -2) {
                    location.href = "<?php echo $cart_url; ?>";
                    return;
                } else {
                    $.ajax({
                        url: "<?php echo $getcard_url; ?>",
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
            
            $("#amount").change(function () {
                var amount = $(this).val();
                var fee = <?php echo $fee;?> ;
                var close_rate = <?php echo $close_rate;?> ;
                if(amount){
                    if(amount<2000){
                        alert("金额不能低于2000");
                        $("#amount").val("");
                        return false;
                    }
                    var p_amount6=(amount/6).toFixed(2);//6期扣款额度
                    var p_amount12=(amount/12).toFixed(2);//12期扣款额度
                    var p_amount24=(amount/24).toFixed(2);//12期扣款额度
                    var p_fee6 = (parseFloat(p_amount6)+(p_amount6*fee+close_rate)).toFixed(2); //6期手续费
                    var p_fee12 = (parseFloat(p_amount12)+(p_amount12*fee+close_rate)).toFixed(2); //6期手续费
                    var p_fee24 = (parseFloat(p_amount24)+(p_amount24*fee+close_rate)).toFixed(2); //6期手续费
                    var p_html = '<option value="0">---选择期数---</option>'+
                                    '<option value="6"> 6 期 × 每期 '+p_fee6+'</option>'+
                                    '<option value="12"> 12期 × 每期 '+p_fee12+'</option>'+
                                    '<option value="24"> 24期 × 每期 '+p_fee24+'</option>';
                    $("#periods").html(p_html);
                    $("#p_amount").html();
                    $("#p_fee").html();
                    $("#p_amount_count").html();
                }
            });
            $("#periods").change(function () {
                var amount = $("#amount").val();
                var periods = $(this).val();
                var fee = <?php echo $fee;?> ;
                var is_plus = <?php echo $is_plus;?> ;
                var close_rate = <?php echo $close_rate;?> ;
                if(amount&&periods!=0){
                    if(amount<2000){
                        alert("金额不能低于2000");
                        $("#amount").val("");
                        return false;
                    }
                    periods = parseInt(periods);
                    var p_amount=(amount/periods).toFixed(2);//扣款额度
                    var p_fee = (p_amount*fee+close_rate).toFixed(2); //每期手续费
                    $("#p_amount").html(p_amount);
                    $("#p_fee").html(p_fee);
                    $("#p_amount_count").html((p_fee*periods).toFixed(2));
                    if(!is_plus){
                        var plus_fee = <?php echo $channel_info["plus_user_fee"];?>;
                        var plus_user_close_rate = <?php echo $channel_info["plus_user_close_rate"];?>;
                        var p_plus_fee = (p_amount*plus_fee+plus_user_close_rate).toFixed(2); //plus每期手续费
                        var html = '<span  class="splus" style="font-size: 0.26rem;">PLUS会员手续费总额：'+((p_plus_fee*periods).toFixed(2))+'</span>';
                        $("#plus_fee").show();
                        $("#plus_fee").html(html);
                    }
                }
            });
            var _lock = false;
            $("#save").click(function(){
                if(_lock){
                    alert('正在提交....');
                    return false;
                }
                _lock = true;
                var c_id = <?php echo $c_id;?>;
                var b_id = $("#bc_id").val();
                var amount = $("#amount").val();
                var periods = $("#periods").val();
                if(b_id<1){
                    _lock = false;
                    alert("请选择银行卡");
                    return false;
                }
                if(!amount){
                    _lock = false;
                    alert("请输入金额");
                    return false;
                }
                if(amount<2000){
                    _lock = false;
                    alert("金额不能低于2000");
                    return false;
                }
                if(periods==0){
                    _lock = false;
                    alert("请选择期数");
                    return false;
                }
                $.ajax({
                    url: "<?php echo $add_plan_url; ?>",
                    data: {c_id: c_id,b_id:b_id,amount:amount,periods:periods},
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        if (data.status == 200) {
                            alert("生成计划成功");
                            location='<?php echo U("index/plan/index");?>';
                        } else {
                            _lock = false;
                            alert(data.info);
                        }
                    }
                });
            });
            $(".sjplus").click(function(){
                location='<?php echo U("index/user/plus");?>';
            });
        </script>
    </body>
</html>