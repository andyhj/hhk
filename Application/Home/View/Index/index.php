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
            .cpjs-des{
                position:absolute;
                top:0px;
                background-color: #fff;
                filter:alpha(Opacity=90);
                -moz-opacity:0.9;
                opacity: 0.9;
                color: #000;
                width: 100%;
                display: none;
            }
            .cpjs-des .content{
                padding:0 0.3rem;
                font-size: 0.3rem;
                line-height: 0.4rem;
                letter-spacing:0.03rem;
                overflow-y: auto;
                height: 80vh;
                margin-top: 1rem;
                margin-bottom: 0.8rem;
            }
            .close{
                position: fixed;
                top: 0px;
                width:100%;
                text-align: right;
                z-index: 200;
                filter:alpha(Opacity=100);
                -moz-opacity:1;
                opacity: 1;
            }
            .close img{
                margin: 0.15rem;
            }
        </style>
    </head>
    <body>
        <div style="width:100%;overflow:hidden;height: 100vh">
            <img src="/src/img/home/bg.jpg" style="width: 100%;display:block;">
        </div>
        <div style="position:absolute;top:2%;left:80%; " id="cpjs">
            <img src="/src/img/home/cpjs.png" style="width:1rem;">
        </div>
        <div class="add" onclick="javascript:window.location.href='<?php echo $channel;?>'">
            <div><img src="/src/img/home/addjh.png"></div>
        </div>
        <div class="cpjs-des">
            <div class="close">
                <img src="/src/img/close1.png" style="width:0.8rem;">
            </div>
            <div class="content">
                &nbsp;&nbsp;&nbsp;&nbsp;去年年底，饿了么启动外卖包材“安心计划”，联合一次性餐盒生产企业、餐饮企业、中国包装联合会对一次性餐盒进行审核筛选，相继发布两批外卖包装“安心名录”。对于入选“安心名录”的企业，饿了么将联合相关部门对其定期复核审查，对市场流通的一次性餐盒进行质量和安全指标盲抽检测，不合格产品会直接移出名录。<br>

&nbsp;&nbsp;&nbsp;&nbsp;与前两批不同，网络订餐供应链联盟对新入选名录的企业和产品提出了更高要求。材料方面，除了聚丙烯塑料、纸和金属材质外，新增了可完全生物降解的聚乳酸类型产品和可重复使用的无纺布袋产品；品类方面，除了传统的外卖打包盒、打包杯，新增加了一次性刀叉勺、吸管、一次性筷子、送餐袋等产品。<br>

&nbsp;&nbsp;&nbsp;&nbsp;饿了么数据显示，平台先后在北京、上海、广州、深圳、杭州、武汉、福州、金华等地对一次性餐饮具、筷子的质量和安全指标进行自查抽检，在184批次一次性餐饮具中，有17批次不合格，49批次一次性筷子全部合格。
<br>

&nbsp;&nbsp;&nbsp;&nbsp;饿了么数据显示，平台先后在北京、上海、广州、深圳、杭州、武汉、福州、金华等地对一次性餐饮具、筷子的质量和安全指标进行自查抽检，在184批次一次性餐饮具中，有17批次不合格，49批次一次性筷子全部合格。
<br>

&nbsp;&nbsp;&nbsp;&nbsp;饿了么数据显示，平台先后在北京、上海、广州、深圳、杭州、武汉、福州、金华等地对一次性餐饮具、筷子的质量和安全指标进行自查抽检，在184批次一次性餐饮具中，有17批次不合格，49批次一次性筷子全部合格。
            </div>
        </div>
        <?php include T('Common/footer'); ?>
    </body>
    <script>
        $("#cpjs").click(function(){
            //$(".cpjs-des").show(500);
        });
        $(".close").click(function(){
            $(".cpjs-des").hide(500);
        });
    </script>
</html>