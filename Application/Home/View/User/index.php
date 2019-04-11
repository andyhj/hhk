<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>个人中心</title>
        <link rel="stylesheet" type="text/css" href="/src/css/style.css">
        <?php include T('Common/header');?>
        <style>
            .des {
                background-color: #fff;
            }
            .des .vipsan{
                margin-bottom: 0px;
                line-height: 0.7rem;
                margin-left: 0.3rem;
                margin-right: 0.3rem;
            }
            .des .text-left{
                text-align: left;
                font-size: 0.25rem;
            }
            .des .text-right{
                text-align: right;
                font-size: 0.25rem;
            }
            .iccs img{
                width: 0.35rem;
                margin-right: 0.2rem;
            }
            .icc img{
                height: 0.25rem;
            }
            .col-xs-6 {
                width: 50%;
                position: relative;
                min-height: 0.1rem;
                padding-right: 0.15rem;
                padding-left: 0.15rem;
            }
        </style>
    </head>
    <body class="huibg">
        <div class="vipcenter">
            <div class="vipheader">
                <a href="userinfo.html">
                    <div class="touxiang"><if condition="$rows['tx']" ><img src="{$rows['tx']}"></if></div>
                    <div class="name">{$rows.name} <if condition="$rows['is_plus']" ><img src="/src/img/user/plus.png"style="width:0.8rem;"></if></div>
                    <if condition="$rows['is_plus']" ><div class="gztt">会员到限：{$rows.dq_date}</div></if>
                </a>
            </div>
            <div class="des">
<!--                <div class="vipsan">
                    <a href="">
                    <div class="col-xs-6 text-left">
                        <span class="iccs"><img src="/src/img/user/card.png" alt=""></span>
                        <span class="lzz">我的银行卡</span>
                    </div>
                    <div class="col-xs-6 text-right">
                        <span class="icc"><img src="/src/img/user/jt.png" alt=""></span>
                    </div>
                    </a>
                </div>-->
                <div class="vipsan">
                    <a href="">
                    <div class="col-xs-6 text-left">
                        <span class="iccs"><img src="/src/img/user/bzzz.png" alt=""></span>
                        <span class="lzz">保证资质</span>
                    </div>
                    <div class="col-xs-6 text-right">
                        <span class="icc"><img src="/src/img/user/jt.png" alt=""></span>
                    </div>
                    </a>
                </div>
                <div class="vipsan">
                    <a href="">
                    <div class="col-xs-6 text-left">
                        <span class="iccs"><img src="/src/img/user/flsx.png" alt=""></span>
                        <span class="lzz">费率事项</span>
                    </div>
                    <div class="col-xs-6 text-right">
                        <span class="icc"><img src="/src/img/user/jt.png" alt=""></span>
                    </div>
                    </a>
                </div>
<!--                <div class="vipsan">
                    <a href="">
                    <div class="col-xs-6 text-left">
                        <span class="iccs"><img src="/src/img/user/jyjl.png" alt=""></span>
                        <span class="lzz">交易记录</span>
                    </div>
                    <div class="col-xs-6 text-right">
                        <span class="icc"><img src="/src/img/user/jt.png" alt=""></span>
                    </div>
                    </a>
                </div>-->
                <div class="vipsan">
                    <a href="plus.html">
                    <div class="col-xs-6 text-left">
                        <span class="iccs"><img src="/src/img/user/plusjb.png" alt=""></span>
                        <span class="lzz">PLUS会员</span>
                    </div>
                    <div class="col-xs-6 text-right">
                        <span class="icc"><img src="/src/img/user/jt.png" alt=""></span>
                    </div>
                    </a>
                </div>
<!--                <div class="vipsan clear-border">
                    <a href="">
                    <div class="col-xs-6 text-left">
                        <span class="iccs"><img src="/src/img/user/lb.png" alt=""></span>
                        <span class="lzz">邀请好礼</span>
                    </div>
                    <div class="col-xs-6 text-right">
                        <span class="icc"><img src="/src/img/user/jt.png" alt=""></span>
                    </div>
                    </a>
                </div>-->
            </div>
        </div>
        <?php include T('Common/footer'); ?>
    </body>
</html>