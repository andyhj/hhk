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
        <link rel="stylesheet" href="">
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header');?>
        <?php include T('Common/share'); ?>
        <style>
            .des .vipsan{
                margin-bottom: 0px;
                line-height: 45px;
            }
            .des i{
                color: red;
                margin-right: 12px;
            }
            .des .text-center{
                text-align: left;
                font-size: 16px;
            }
        </style>
    </head>
    <body class="huibg">
        <div class="vipcenter">
            <div class="vipheader">
                <a href="userinfo.html">
                    <div class="touxiang"><if condition="$userInfo['headurl']" ><img src="{$userInfo.headurl}"></if></div>
                    <div class="name">{$userInfo.nickname}</div>
                    <div class="gztt"></div>
                </a>
            </div>
            <div class="vipsan">
                <div class="col-xs-4 text-center"><a href="instruction.html"><h4>等级</h4><p>{$grade}</p></a></div>
                <div class="col-xs-4 text-center"><a href="withdrawal.html"><h4>佣金总收入</h4><p>{$award_info.earn}</p></a></div>
                <div class="col-xs-4 text-center"><a href="withdrawal.html"><h4>可提取佣金</h4><p>{$award_info.amount}</p></a></div>
            </div>
            <div class="des">
                <div class="vipsan">
                    <div class="col-xs-6 text-center">
                        <a href="userinfo.html">
                            <span class="icc"><img src="/src/img/h5/info.png" alt=""></span>
                            <span class="lzz">个人中心</span>
                        </a>
                    </div>
                    <div class="col-xs-6 text-center">
                        <a href="order.html">
                            <span class="icc"><img src="/src/img/h5/order.png" alt=""></span>
                            <span class="lzz">订单中心</span>
                        </a>
                    </div>
                </div>
                <div class="vipsan">
                    <div class="col-xs-6 text-center">
                        <a href="extract.html">
                            <span class="icc"><img src="/src/img/h5/order.png" alt=""></span>
                            <span class="lzz">提现记录</span>
                        </a>
                    </div>
                    <div class="col-xs-6 text-center">
                        <a href="address.html">
                            <span class="icc"><img src="/src/img/h5/address.png" alt=""></span>
                            <span class="lzz">收货地址</span>
                        </a>
                    </div>
                </div>
                <div class="vipsan">
                    <div class="col-xs-6 text-center">
                        <a href="generalize.html">
                            <span class="icc"><img src="/src/img/h5/code.png" alt=""></span>
                            <span class="lzz">我的推广</span>
                        </a>
                    </div>
                    <div class="col-xs-6 text-center">
                        <a href="team.html">
                            <span class="icc"><img src="/src/img/h5/info.png" alt=""></span>
                            <span class="lzz">我的团队</span>
                        </a>
                    </div>
                </div>
                <div class="vipsan">
                    <div class="col-xs-6 text-center">
                        <a href="{$custom}custom/cuslist.html">
                            <span class="icc"><img src="/src/img/h5/game.png" alt=""></span>
                            <span class="lzz">我的比赛</span>
                        </a>
                    </div>
                    <div class="col-xs-6 text-center">
                        <a href="{$custom}custom/vouchers.html">
                            <span class="icc"><img src="/src/img/h5/prize.png" alt=""></span>
                            <span class="lzz">我的奖品</span>
                        </a>
                    </div>
                </div>
                <div class="vipsan">
                    <div class="col-xs-6 text-center">
                        <a href="{$custom}custom/history.html">
                            <span class="icc"><img src="/src/img/h5/game.png" alt=""></span>
                            <span class="lzz">历史比赛</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>