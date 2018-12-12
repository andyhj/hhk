<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>下载APP</title>
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
    </head>
    <body class="huibg">
        <div style="height: 100%;background: #7703b3;">
            <img style="width: 100%;" src="/src/img/app.jpg" id="download">
        </div>
    </body>
    <script>
        $("#download").click(function(event) {
            var url = "<?php echo $url; ?>";
            var type = "<?php echo $type; ?>";
            if(type=="IOS"){
                alert("暂不支持IOS APP下载");
            }else{
                window.location=url;
            }
        });
    </script>
</html>