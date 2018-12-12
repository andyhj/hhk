<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>个人中心</title>
        <link rel="stylesheet" href="">
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
        <style>
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
        </style>
    </head>
    <body class="huibg">
        <?php include T('Common/nav'); ?>
        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">个人中心</a>
            <button class="topnav" id="open-button"><span class="iconfont icon-1"></span></button>
        </nav>


        <div class="usercenter  acdv">
            <div class="details" style="padding: 18px;padding-top: 0px;">
                <ul class="list-ul">
                    <li>
                        <img src="/src/img/h5/userinfo/info.png" class="tb">
                        个人信息：
                    <if condition="$userInfo['headurl']" ><span  style="float:right;"><div class="head"><img src="{$userInfo.headurl}"></div></span></if>
                    </li>
                    <li>
                        <img src="/src/img/h5/userinfo/id.png" class="tb">
                        用户ID：
                        <span  style="float:right;"><if condition="$userInfo['id']" >{$userInfo.id}</span><else />----</if></span>
                    </li>
                    <li>
                        <img src="/src/img/h5/userinfo/name.png" class="tb">
                        用户名：
                        <span  style="float:right;"><if condition="$userInfo['username']" >{$userInfo.username}<else />----</if></span>
                    </li>
                    <li>
                        <img src="/src/img/h5/userinfo/nick.png" class="tb">
                        用户昵称：
                        <span  style="float:right;"><if condition="$userInfo['nickname']" >{$userInfo.nickname}<else />----</if></span>
                    </li>
                    <li>
                        <img src="/src/img/h5/userinfo/maill.png" class="tb">
                        邮箱：
                        <span  style="float:right;"><if condition="$userInfo['email']" >{$userInfo.email}<else />----</if></span>
                    </li>
                    <li>
                        <img src="/src/img/h5/userinfo/sax.png" class="tb">
                        性别：
                        <span  style="float:right;"><if condition="$userInfo['gender'] eq 1" >男<elseif condition="$userInfo['gender'] eq 2" />女<else /> 未知</if></span>
                    </li>
                    <li>
                        <img src="/src/img/h5/userinfo/province.png" class="tb">
                        省份：
                        <span  style="float:right;"><if condition="$userInfo['province']" >{$userInfo.province}<else />----</if></span>
                    </li>
                    <li>
                        <img src="/src/img/h5/userinfo/city.png" class="tb">
                        城市：
                        <span  style="float:right;"><if condition="$userInfo['city']" >{$userInfo.city}<else />----</if></span>
                    </li>
                    <li>
                        <img src="/src/img/h5/userinfo/grade.png" class="tb">
                        等级：
                        <span  style="float:right;"><if condition="$userInfo['city']" >{$grade_name}<else />----</if></span>
                    </li>
                    <li>
                        <img src="/src/img/h5/userinfo/cms.png" class="tb">
                        开心豆数量：
                        <span  style="float:right;"><if condition="$userInfo['city']" >{$gameUser.coinnum}<else />----</if></span>
                    </li>
                    <li>
                        <img src="/src/img/h5/userinfo/sbd.png" class="tb">
                        我的同级下属：
                        <span  style="float:right;"><if condition="$a_data['equative']" >{$a_data.equative}<else />----</if></span>
                    </li>
                    <li>
                        <img src="/src/img/h5/userinfo/sbd.png" class="tb">
                        我的直系下属：
                        <span  style="float:right"><a href="agencylist.html" style="text-decoration: underline;margin-left: 35px;"><img src="/src/img/h5/userinfo/jt.png" height="18px"></a></span><span  style="float:right;"><if condition="$a_data['direct']" >{$a_data.direct}<else />----</if></span>
                    </li>
                    <li>
                        <img src="/src/img/h5/userinfo/sbd.png" class="tb">
                        我的旁系下属：
                        <span  style="float:right;"><if condition="$a_data['offshoot']" >{$a_data.offshoot}<else />----</if></span>
                    </li>
                </ul>
                <ul class="list-ul">
                    <if condition="$user_bank" >
                    <li>
                        <a class="navbar-tit center-block" style="color: #FF2626;font-size: 18px;">结算账户</a>
                    </li>
                    <li>
                        姓名：
                    <if condition="$user_bank['name']" >{$user_bank.name}</if>
                    </li>
                    <li>
                        卡号：
                    <if condition="$user_bank['card']" >{$user_bank.card}</if>
                    </li>
                    <li>
                        开户行：
                    <if condition="$user_bank['bank']" >{$user_bank.bank}</if>
                    </li>
                    <li>
                        开户省份：
                    <if condition="$user_bank['province']" >{$user_bank.province}</if>
                    </li>
                    <li>
                        开户城市：
                    <if condition="$user_bank['city']" >{$user_bank.city}</if>
                    <li>
                        开户行支行：
                    <if condition="$user_bank['branch_name']" >{$user_bank.branch_name}</if>
                    </li>
                    </if>
                    <li>
                        <button type="button" class="btn btn-danger btn-block btn-lg" onclick="location='userbank'">更新结算账户</button>
                    </li>
                </ul>
            </div>
            
        </div>

        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>

    </body></html>