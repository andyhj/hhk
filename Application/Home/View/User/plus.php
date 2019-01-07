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
                z-index: 20;
                width: 100%;
            }
        </style>
    </head>
    <body style="color:#595757;">
        <div style="width:100%;overflow:hidden;height: 100vh">
            <img src="/src/img/plus/plus_bg.jpg" style="width: 100%;display:block;">
        </div>
        <div class="cpjs">
            <div><img src="/src/img/plus/content.png" class="cpjs-bg"></div>
            <div class="des">
                <div style="text-align:center;margin-top:8%;"><img src="/src/img/plus/plus_card.png" style="width: 60%;"></div>
                <div style="text-align:center;margin-top: 0.2rem;">（20元/月）</div>
                <div style="text-align:center;margin-top: 0.2rem;"><img src="/src/img/plus/button.png" style="width: 3rem;"></div>
                <div style="margin-top: 0.2rem;width: 90%;border-bottom: 1px solid #e1e1e1;margin-left: 5%;"></div>
                <div style="text-align:center;margin-top: 0.2rem;">使用说明</div>
                <div style="margin-top: 0.1rem;padding-left: 6%;font-size: 0.2rem;">购买后立即生效，请在有效期内使用；</div>
                <div style="margin-top: 0.06rem;padding-left: 6%;font-size: 0.2rem;">仅限本人使用，转发无效；</div>
                <div style="margin-top: 0.06rem;padding-left: 6%;font-size: 0.2rem;">PLUS会员服务费率为0.55%+1，不与其它同享；</div>
                <div style="margin-top: 0.06rem;padding-left: 6%;font-size: 0.2rem;">最终解释权归会还款所有。</div>
            </div>
        </div>
        <?php if($user_vip_log_info1){ ?>
        <div class="task">
            <div><img src="/src/img/plus/task.png" class="task-bg"></div>
            <div class="des">
                <div style="margin-top:0.2rem;padding-left: 5.5%;">领取福利（<?php echo $user_vip_log_count;?>）</div>
                <?php if($user_vip_log_info){ ?>
                    <div style="margin-top: 0.2rem;padding-left: 6.5%;font-size: 0.2rem;">
                        <span>
                            <?php echo $user_vip_log_info["type_name"]."(".$user_vip_log_info["vip_m"]."个月)";?><br>
                            领取有效期：<?php echo date("Y-m-d",$user_vip_log_info["add_time"]);?> - <?php echo $user_vip_log_info["end_time"]?date("Y-m-d",$user_vip_log_info["end_time"]):"永久";?>
                        </span>
                        <span style="float: right;margin-right: 6.5%;margin-top: -0.3rem;" id="lq" data-id="<?php echo $user_vip_log_info["id"];?>">
                            <img src="/src/img/plus/get.png" style="width:1.3rem;">
                        </span>
                    </div>
                <?php }else{ ?>
                    <div style="margin-top: 0.2rem;padding-left: 6.5%;font-size: 0.2rem;">
                        <span>
                            <?php echo $user_vip_log_info1["type_name"]."(".$user_vip_log_info1["vip_m"]."个月)";?><br>
                            领取有效期：<?php echo date("Y-m-d",$user_vip_log_info1["add_time"]);?> - <?php echo $user_vip_log_info1["end_time"]?date("Y-m-d",$user_vip_log_info1["end_time"]):"永久";?>
                        </span>
                        <span style="float: right;margin-right: 6.5%;margin-top: -0.3rem;">
                            <?php if($user_vip_log_info1["status"]){?>
                                <img src="/src/img/plus/ylq.png" style="width:1.3rem;">
                            <?php }else{?>
                                <?php if($user_vip_log_info1["end_time"]< time()){?>
                                <img src="/src/img/plus/gq.png" style="width:1.3rem;">
                                <?php }?>
                            <?php }?>
                        </span>
                    </div>
                <?php } ?>
                <div style="margin-top: 0.18rem;width: 90%;border-bottom: 1px solid #e1e1e1;margin-left: 5%;"></div>
                <div style="margin-top: 0.1rem;text-align:center;font-size: 0.18rem;color: #9c9c9c;">查看更多记录</div>
                <div style="margin-top: 0.02rem;text-align:center;font-size: 0.18rem;color: #9c9c9c;"  onclick="javascript:window.location.href='<?php echo U('index/user/plusdes',['id'=>$v['id']]);?>'"><img src="/src/img/plus/zk.png" style="width:0.3rem;"></div>
            </div>
        </div>
        <?php } ?>
        <?php include T('Common/footer'); ?>
    </body>
    <script>
        var _lock = false;
        $("#lq").click(function(){
            if(_lock){
                alert('正在提交....');
                return false;
            }
            _lock = true;
            var id = $(this).attr("data-id");
            console.log(id);
            $.ajax({
                    url: "<?php echo $getPlus; ?>",
                    data: {id:id},
                    type: 'post',
                    dataType: 'json',
                    success: function (data) {
                        if (data.status == 200) {
                            alert("领取成功");
                            location='<?php echo U("index/user/plus");?>';
                        } else {
                            _lock = false;
                            alert(data.info);
                        }
                    }
                });
        });
    </script>
</html>