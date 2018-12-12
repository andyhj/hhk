<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>收货地址</title>
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1457937316_1758883.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
    </head>
    <body class="huibg">
        <?php include T('Common/nav'); ?>
        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">地址信息</a>
            <button class="topnav" id="open-button"><span class="iconfont icon-1"></span></button>
        </nav>

        <form id="addrForm" accept-charset="utf-8">
            <input name="uid" id="uid" value="{$userInfo.id}" type="hidden">
            <input name="authkey" id="authkey" value="{$userInfo.authkey}" type="hidden">
            <input name="format" id="format" value="json" type="hidden">
            <input name="method" id="method" value="user.updAddress" type="hidden">
            <div class="usercenter accdv">
                <div class="row">
                    <div class="col-md-2">姓名：</div>
                    <div class="col-md-10"> <input name="name" id="name" value="{$address.name}" class="form-control" type="text"></div>
                </div>
                <div class="row">
                    <div class="col-md-2">身份证号码：</div>
                    <div class="col-md-10"> <input name="number" id="number" value="{$address.number}" class="form-control" type="text"></div>
                </div>
                <div class="row">
                    <div class="col-md-2">手机号码：</div>
                    <div class="col-md-10"> <input name="phone" id="phone" value="{$address.phone}" class="form-control" type="text"></div>
                </div>
                <div class="row">
                    <div class="col-md-2">邮编：</div>
                    <div class="col-md-10"> <input name="postcode" id="postcode" value="{$address.postcode}" class="form-control" type="text"></div>
                </div>
                <div class="row">
                    <div class="col-md-2">详细地址：</div>
                    <div class="col-md-10"> <input name="address" id="address" value="{$address.address}" class="form-control" type="text"></div>
                </div>
                <button type="button" class="btnlg" id="updAddr">修 改</button>
            </div>
        </form>
        
        <script type="text/javascript" charset="utf-8" async defer>
            $("#updAddr").click(function(event) {
                $.ajax({
                    type: 'post',
                    url: '<?=API_HOST; ?>/restsrv.php',
                    data: $("form").serialize(),
                    success: function(json) {
                        var data = JSON.parse(json); 
                        if(data[0]["retcode"]==0){
                            alert("更新成功");
                        }else{
                            alert("更新失败");
                        }
                    }
                });
            });
            
        </script>
    
        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>

    </body></html>