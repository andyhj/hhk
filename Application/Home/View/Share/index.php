<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>开心逗棋牌</title>
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
    </head>
    <body class="huibg">
        <div style="height: 100%;background: #000;">
<!--            <img style="width: 100%;" src="/src/img/share/1.jpg">
            <img style="width: 100%;" src="/src/img/share/2.jpg">
            <img style="width: 100%;" src="/src/img/share/3.jpg">
            <img style="width: 100%;" src="/src/img/share/4.jpg">
            <img style="width: 100%;" src="/src/img/share/5.jpg">
            <img style="width: 100%;" src="/src/img/share/6.jpg">-->
            <?php if($return_status==200){ ?>
                <!--<a href="<?php echo $game_login_url; ?>"><img style="width: 100%;" src="/src/img/share/8.jpg"></a>-->
            <?php }elseif($return_status==113){ ?>
                <!--<img style="width: 100%;" src="/src/img/share/7.jpg">-->
            <?php } ?>
            <img style="width: 100%;" src="/src/img/01.jpg">
        </div>
    </body>
</html>