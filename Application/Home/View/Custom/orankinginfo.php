<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>奖品信息</title>
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
        <style type="text/css" media="screen">
            .ranking{
                border:1px dashed #8E8A8A;
                width:100%;
                border-radius: 10px;
                margin-top:10px;
                background: #F3F0F0;
            }
            .ranking table{
                width:100%;
                color: #000;
                margin-top: 15px;
                margin-bottom: 15px;
            }
            .ranking td{
                height:30px;
            }
            .ranking td span{
                margin: 15px;
            }
            .ranking td img{
                border-radius: 8px;
                margin: 20px;
            }
            .ranking .i-no{
                margin: 5px;
                font-size: 15px;
                font-weight: bold;
                color: #E24B4B;
            }
            .ranking .i-yes{
                margin: 5px;
                font-size: 15px;
                font-weight: bold;
                color: #716E6E;
            }
        </style>
        <script>
            function exchange(){
                window.location="<?php echo $customInfo["join_prizes_url"];?>";
            }
        </script>
    </head>
    <body>
        <?php include T('Common/nav'); ?>

        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">奖券信息</a>
            <button class="topnav" id="open-button"><span class="iconfont icon-1"></span></button>
        </nav>
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="sp1">
                <ul class="ddlist">
                    <li>
                        <div class="ranking">
                            <table style="margin: 0px;font-size: 14px;">
                                <tr>
                                    <td><span>比赛名称：<?php echo $customInfo["name"];?></span></td>
                                </tr>
                                <tr>
                                    <td><span>比赛时间：<?php echo date("Y-m-d H:i:s",$customInfo["start_date"]);?></span></td>
                                </tr>
                                <tr>
                                    <td><span>比赛名次：
                                        <?php if($ocustom_id){?>
                                        <?php if($oapply_info['ranking']){?>第<?php echo $oapply_info["ranking"];?>名<?php }?>
                                        <?php }else{?>
                                        <?php if($rankingInfo['ranking']){?>第<?php echo $rankingInfo["ranking"];?>名<?php }?>
                                        <?php }?>
                                        </span></td>
                                </tr>
                                <tr>
                                    <td><span>奖品名称：
                                        <?php if($ocustom_id){?>
                                        参与奖
                                        <?php }else{?>
                                        <?php echo $rankingInfo["prizes_name"];?>
                                        <?php }?>
                                        </span></td>
                                </tr>
                                <tr>
                                    <td><span>奖品价值：
                                        <?php if($ocustom_id){?>
                                        *****
                                        <?php }else{?>
                                        <?php echo $rankingInfo["prizes_value"];?>
                                        <?php }?>
                                        </span></td>
                                </tr>
                                <tr>
                                    <td><span>获奖时间：
                                        <?php if($ocustom_id){?>
                                        <?php echo date("Y-m-d H:i:s",$customInfo["end_date"]);?>
                                        <?php }else{?>
                                        <?php echo date("Y-m-d H:i:s",$rankingInfo["add_time"]);?>
                                        <?php }?>
                                        </span></td>
                                </tr>
                                <?php if($ocustom_id){?>
                                <tr>
                                    <td align="center">
                                        <input type="button" onclick="exchange()" value="领 取" style="margin: 15px;width: 30%;height: 35px;font-size: 18px;border: 0px;background-color: #fdc158;border-radius: 5px;">
                                    </td>
                                </tr>
                                <?php }?>
                                <?php if($rankingInfo["status"==2]){?>
                                <tr>
                                    <td align="center">
                                        <input type="button" id="lq" value="领 取" style="margin: 15px;width: 30%;height: 35px;font-size: 18px;border: 0px;background-color: #fdc158;border-radius: 5px;">
                                    </td>
                                </tr>
                                <?php }?>
                            </table>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <script>
            $("#lq").click(function(){
                $.ajax({
                    type: 'get',
                    url: '<?php echo $lq_url;?>',
                    dataType: 'json',
                    success: function(json) {
                        if(json["status"]==200){
                            alert("领取成功");
                            $("#lq").hide();
                        }else{
                            if(json["status"]==308){
                                window.location='<?php echo $addr_url;?>';
                            }else{
                                alert(json["info"]);
                            }
                        }
                    }
                });
            });
        </script>
        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>
    </body>
</html>