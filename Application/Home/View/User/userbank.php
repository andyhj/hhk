<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>结算账户</title>
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1457937316_1758883.css">
        <?php include T('Common/header'); ?>
        <?php include T('Common/share'); ?>
    </head>
    <body class="huibg">
        <?php include T('Common/nav'); ?>
        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">结算账户</a>
            <button class="topnav" id="open-button"><span class="iconfont icon-1"></span></button>
        </nav>

        <form id="addrForm" accept-charset="utf-8">
            <input type="hidden" name="uid" value="{$uid}">
            <input type="hidden" name="authkey" value="{$authkey}">
            <div class="usercenter accdv">
                <div class="row">
                    <div class="col-md-2">姓名：</div>
                    <div class="col-md-10"> <input name="name" id="name" value="{$user_bank.name}" class="form-control" type="text"></div>
                </div>
                <div class="row">
                    <div class="col-md-2">卡号：</div>
                    <div class="col-md-10"> <input name="card" id="card" value="{$user_bank.card}" class="form-control" type="text"></div>
                </div>
                <div class="row">
                    <div class="col-md-2">开户行：</div>
                    <div class="col-md-10"> 
                        <select id="bank" name="bank" class="form-control">
                            <option value="">请选择...</option>
                            <option value="BOC-中国银行" <if condition="$user_bank['bank_code'] eq 'BOC'" >selected="selected"</if>>中国银行</option>
                            <option value="ICBC-工商银行" <if condition="$user_bank['bank_code'] eq 'ICBC'" >selected="selected"</if>>工商银行</option>
                            <option value="ABC-农业银行" <if condition="$user_bank['bank_code'] eq 'ABC'" >selected="selected"</if>>农业银行</option>
                            <option value="CCB-建设银行" <if condition="$user_bank['bank_code'] eq 'CCB'" >selected="selected"</if>>建设银行</option>
                            <option value="CMB-招商银行" <if condition="$user_bank['bank_code'] eq 'CMB'" >selected="selected"</if>>招商银行</option>
                            <option value="BOCOM-交通银行" <if condition="$user_bank['bank_code'] eq 'BOCOM'" >selected="selected"</if>>交通银行</option>
                            <option value="PSBC-中国邮政储蓄银行" <if condition="$user_bank['bank_code'] eq 'PSBC'" >selected="selected"</if>>中国邮政储蓄银行</option>
                            <option value="GDB-广东发展银行" <if condition="$user_bank['bank_code'] eq 'GDB'" >selected="selected"</if>>广东发展银行</option>
                            <option value="SDB-深圳发展银行" <if condition="$user_bank['bank_code'] eq 'SDB'" >selected="selected"</if>>深圳发展银行</option>
                            <option value="SPDB-上海浦东发展银行" <if condition="$user_bank['bank_code'] eq 'SPDB'" >selected="selected"</if>>上海浦东发展银行</option>
                            <option value="ZJTLCB-浙江泰隆商业银行" <if condition="$user_bank['bank_code'] eq 'ZJTLCB'" >selected="selected"</if>>浙江泰隆商业银行</option>
                            <option value="CMBC-中国民生银行" <if condition="$user_bank['bank_code'] eq 'CMBC'" >selected="selected"</if>>中国民生银行</option>
                            <option value="CIB-兴业银行" <if condition="$user_bank['bank_code'] eq 'CIB'" >selected="selected"</if>>兴业银行</option>
                            <option value="CITIC-中信银行" <if condition="$user_bank['bank_code'] eq 'CITIC'" >selected="selected"</if>>中信银行</option>
                            <option value="HXB-华夏银行" <if condition="$user_bank['bank_code'] eq 'HXB'" >selected="selected"</if>>华夏银行</option>
                            <option value="CEB-中国光大银行" <if condition="$user_bank['bank_code'] eq 'CEB'" >selected="selected"</if>>中国光大银行</option>
                            <option value="BCCB-北京银行" <if condition="$user_bank['bank_code'] eq 'BCCB'" >selected="selected"</if>>北京银行</option>
                            <option value="BOS-上海银行" <if condition="$user_bank['bank_code'] eq 'BOS'" >selected="selected"</if>>上海银行</option>
                            <option value="TCCB-天津银行" <if condition="$user_bank['bank_code'] eq 'TCCB'" >selected="selected"</if>>天津银行</option>
                            <option value="BODL-大连银行" <if condition="$user_bank['bank_code'] eq 'BODL'" >selected="selected"</if>>大连银行</option>
                            <option value="HCCB-杭州银行" <if condition="$user_bank['bank_code'] eq 'HCCB'" >selected="selected"</if>>杭州银行</option>
                            <option value="NBCB-宁波银行" <if condition="$user_bank['bank_code'] eq 'NBCB'" >selected="selected"</if>>宁波银行</option>
                            <option value="XMCCB-厦门银行" <if condition="$user_bank['bank_code'] eq 'XMCCB'" >selected="selected"</if>>厦门银行</option>
                            <option value="GZCB-广州银行" <if condition="$user_bank['bank_code'] eq 'GZCB'" >selected="selected"</if>>广州银行</option>
                            <option value="PINAN-平安银行" <if condition="$user_bank['bank_code'] eq 'PINAN'" >selected="selected"</if>>平安银行</option>
                            <option value="CZB-浙商银行" <if condition="$user_bank['bank_code'] eq 'CZB'" >selected="selected"</if>>浙商银行</option>
                            <option value="SRCB-上海农村商业银行" <if condition="$user_bank['bank_code'] eq 'SRCB'" >selected="selected"</if>>上海农村商业银行</option>
                            <option value="CQCB-重庆银行" <if condition="$user_bank['bank_code'] eq 'CQCB'" >selected="selected"</if>>重庆银行</option>
                            <option value="JSB-江苏银行" <if condition="$user_bank['bank_code'] eq 'JSB'" >selected="selected"</if>>江苏银行</option>
                            <option value="BJRCB-北京农村商业银行" <if condition="$user_bank['bank_code'] eq 'BJRCB'" >selected="selected"</if>>北京农村商业银行</option>
                            <option value="JNB-济宁银行" <if condition="$user_bank['bank_code'] eq 'JNB'" >selected="selected"</if>>济宁银行</option>
                            <option value="TZB-台州银行" <if condition="$user_bank['bank_code'] eq 'TZB'" >selected="selected"</if>>台州银行</option>
                        </select>
                   </div>
                </div>
                <div class="row">
                    <div class="col-md-2">开户省份：</div>
                    <div class="col-md-10"> 
                        <select id="province" name="province" class="form-control">
                          <option value="">请选择...</option>
                       </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">开户城市：</div>
                    <div class="col-md-10"> 
                        <select id="city" name="city" class="form-control">
                            <option value="">请选择...</option>
                       </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">开户行支行：</div>
                    <div class="col-md-10"> <input name="branch_name" id="branch_name" value="{$user_bank.branch_name}" class="form-control" type="text"></div>
                </div>
                <button type="button" class="btnlg" id="updAddr">更 新</button>
            </div>
        </form>
        <script type="text/javascript" charset="utf-8" async defer>
            $("#updAddr").click(function(event) {
                $.ajax({
                    type: 'post',
                    url: '{$subUrl}',
                    data: $("form").serialize(),
                    dataType: 'json',
                    success: function(json) {
                        if(json["status"]==200){
                            alert("更新成功");
                            location='userinfo';
                        }else{
                            alert(json["info"]);
                        }
                    }
                });
            });
            function province(){
                var pro_option = '<option value="">请选择...</option>';
                var province_code = '{$user_bank.province_code}';
                var city_code = '{$user_bank.city_code}';
                $.ajax({
                    type: 'get',
                    url: '{$pcUrl}',
                    dataType: 'json',
                    success: function(json) {
                        if(json["status"]=="succeed"){
                            var province = json["data"];
                            for(var i=0;i<province.length;i++){
                                if(province[i]["id"]==province_code){
                                    pro_option += '<option value="'+province[i]["id"]+'-'+province[i]["name"]+'" selected="selected">'+province[i]["fullname"]+'</option>';
                                }else{
                                    pro_option += '<option value="'+province[i]["id"]+'-'+province[i]["name"]+'">'+province[i]["fullname"]+'</option>';
                                }
                              }
                            $("#province").html(pro_option);
                        }
                        
                    }
                });
                if(province_code){
                    city(city_code,province_code);
                }
            }
            province();
            $("#province").change(function(event) {
                var province_id = $("#province").val()
                city(0,province_id);
            });

            function city(city_id,province_id){
                var city_option = '<option value="">请选择...</option>';
                $.ajax({
                    type: 'get',
                    url: '{$pcUrl}&id='+province_id,
                    dataType: 'json',
                    success: function(json) {
                        if(json["status"]=="succeed"){
                            var city = json["data"];
                            for(var i=0;i<city.length;i++){
                                if(city_id==city[i]["id"]){
                                    city_option += '<option value="'+city[i]["id"]+'-'+city[i]["name"]+'" selected="selected">'+city[i]["fullname"]+'</option>';
                                }else{
                                    city_option += '<option value="'+city[i]["id"]+'-'+city[i]["name"]+'">'+city[i]["fullname"]+'</option>';
                                }
                              }
                            $("#city").html(city_option);
                        }
                        
                    }
                });
            }
            
        </script>
    
        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>

    </body></html>