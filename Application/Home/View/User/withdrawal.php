<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>佣金提现</title>
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
        <style>
            .sub-btnlg {
                width: 120px;
                height: 35px;
                border: 0px;
                background: #FF2626;
                color: #fff;
                font-size: 1.2em;
                border-radius: 5px;
                margin-right: 10px;
                
            }
            .dz{
                margin:10px;
            }
        </style>
    </head>
    <body class="huibg">
        <?php include T('Common/nav'); ?>
        <div class="dingdan">
            <div class="ddlist">
                <div class="dz"><p class="ziku">佣金总收入：</p>{$awardInfo.earn}</div>
                <div class="dz"><p class="ziku">可提取额度：</p>{$awardInfo.amount}</div>
                <div class="dz"><p class="ziku"><input name="amount" id="amount" value="" class="form-control" type="text" placeholder="请输入提取金额"></p></div>
                <div class="dz">
                    <p class="ziku">
                        <button type="button" class="sub-btnlg" id="subAward">提 取</button>
                        <button type="button" class="sub-btnlg" onclick="location='csncode.html'">兑换开心豆</button>
                    </p>
                </div>
                <div style="padding: 6px;color:red;size: 12px">注：资金以银行实际到账为准，如果银行信息填写错误资金将在24小时之内退回账户,如需修改结算账户请在个人中心修改</div>
                
                <if condition="$award_earn_list" >
                <table class="table table-striped" style="margin:5px;height: 50px">
                    <thead>
                        <tr>
                            <th>金额</th>
                            <th>来源</th>
                            <th>时间</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 12px;">
                        <volist name="award_earn_list" id="ael" key="k"> 
                            <tr>
                                <td>{$ael.amount}</td>
                                <td><if condition="$ael['source'] eq 1" >台费<elseif condition="$ael['source'] eq 2" />游戏充值<elseif condition="$ael['source'] eq 3" />代理充值</if></td>
                                <td>{$ael.add_date|date="Y-m-d H:i",###}</td>
                            </tr>
                        </volist>
                    </tbody>
                </table>
                </if>
            </div>
            
        </div>
        <script type="text/javascript" charset="utf-8" async defer>
            $("#subAward").click(function(event) {
                var amount = $("#amount").val();
                var uid = {$uid};
                var authkey = '{$authkey}';
                var numAmount = {$awardInfo.amount};
                var r = /^\+?[1-9][0-9]*$/;　　//正整数  
                if(amount<=0){
                    alert("请输入提取金额");
                    return false;
                }
                if(!r.test(amount)){
                    alert("提取金额为整数");
                    return false;
                }
                if(amount>numAmount){
                    alert("输入的金额大于可提取额度");
                    return false;
                }
                $.ajax({
                    type: 'post',
                    url: '{$subUrl}',
                    data: {uid:uid,authkey:authkey,amount:amount},
                    dataType:'json', 
                    success: function(json) {
                        if(json["status"]==200){
                            alert("提交成功");
                        }else{
                            alert(json["info"]);
                        }
                    }
                });
            });
            
        </script>
        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>

    </body></html>