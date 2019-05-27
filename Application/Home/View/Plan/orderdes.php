<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <title>订单状态</title>
        <?php include T('Common/header'); ?>
        <style>
            body{
                font: 12px/22px Verdana, Geneva, sans-serif;
            }
            .title {
                text-align: center;
                margin-top: 15%;
                color: #ec7b7b;
                font-size: 20px;
            }
            .button {
                text-align: center;
                margin-top: 15%;
            }
            .button input{
                width: 45%;
                height: 35px;
                border-radius: 5px;
                border: 0px;
                background-color: #ec7b7b;
                color: #fff;
                font-size: 15px;
                letter-spacing: 2px;
            }
            .ad{
                margin-top: 10%;
                text-align: center;
            }
            .ad div{
                color: #868383;
            }
        </style>
    </head>
    <body>
        <div class="wrap">
            <div class="mainBody">
                <div class="title">
                    <?php if($type==1): ?>
                    消费成功
                    <?php else: ?>
                    还款成功
                    <?php endif; ?>
                </div>
                <div class="button">
                    <input type="button" value="返回首页" onclick="location='<?php echo $home; ?>'"> <input type="button" value="查看详情" onclick="location='<?php echo $plandes; ?>'">
                </div>
            </div>
            <div class="ad">
                <div>广告</div>
                <iframe src="<?php echo $ad_url; ?>" width="300" height="250" scrolling="no" frameborder="0" />
            </div>
        </div>
    </body>
</html>
