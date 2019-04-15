<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>我的银行卡</title>
        <?php include T('Common/header'); ?>
        <style type="text/css" media="screen">
            body{
                background-color: #eeeeee;
            }
            .list-ul{
                padding-left: 0;
                padding-top: 0.08rem;
            }
            .list-ul li{
                position: relative;
                display: block;
                padding-left: 0.22rem;
                padding-right: 0.22rem;
                padding-top: 0.1rem;
                padding-bottom: 0.1rem;
                margin-bottom: 0.1rem;
                color: #fff;
                border-radius: 0.1rem;
            }
            .list-ul table{
                width: 100%;
            }
            .list-ul table td{
                height: 0.25rem;
                font-size: 0.2rem;
            }
            .plan-des{
                width:100%;
                letter-spacing: 2px;
                height: 1.35rem;
                position: fixed;
            }
            .ui{
                 width:100%;
                 list-style: none;
                 margin:0;
                 padding:0;
                 margin-left: 5%;
             }
            .ui li{
                width:30%;
                height:1rem;
                float: left;/*该处换为display:inline-block;同样效果*/
                text-align: center;
            }
            .tab-cot{
                background-repeat:no-repeat; 
                background-size:100% 100%;
                -moz-background-size:100% 100%;
                width: 96%;
                margin-left: 2%;
                height: 7.75rem;
            }
            .tab-cot div{
                display: none;
            }
            .tab-cot .active{
                display:block;
            }
            .but{
                border-radius: 0.3rem;
                border: 0;
                height: 0.55rem;
                font-size: 0.2rem;
                letter-spacing: 0.04rem;
                width: 1.3rem;
                line-height: 0.55rem;
                opacity: 0.7; /*整个按钮的不透明度，会影响到文字，0完全透明，1完全不透明*/
                background: rgba(0, 0, 0, 0.7);    /* 仅调节背景的色彩，并加上不透明度，此例为70%不透明的白色 */  
            }
        </style>
    </head>
    <body>       
        <div class="plan-des">
            <div id="myTabContent" class="tab-cot plan">
                <div class="tab-pane fade active in " id="sp1" >
                    <?php if($bank_card_list&&!empty($bank_card_list)){?>
                    <ul class="list-ul" style="margin-bottom: 0px;">
                        <?php foreach ($bank_card_list as $k=>$v){?>
                        <li <?php if($k%2==0){ echo 'style="background-color: #dd4446"';}else{echo 'style="background-color: #4c7be5"';}?>>
                            <table>
                                <tr>
                                    <td>
                                        <span style="font-size: 0.30rem;color: #fff;font-weight:bold;"><?php echo $v['bank_name']?></span>
                                    </td>
                                    <td rowspan="3"  style="text-align: right;font-size: 0.3rem;">
                                        <input type="button" value="解除绑定" class="but" data-id='<?php echo $v['id']?>'>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <?php echo $v['user_name']?>|信用卡
                                    </td>
                                </tr>
                                <tr style="height: 0.7rem">
                                    <td style="font-size: 0.30rem;font-weight:bold;">
                                        <?php echo substr_replace($v['card_no'], ' **** **** ', 4, -4); ?>
                                    </td>
                                </tr>
                            </table>
                        </li>
                        <?php }?>
                    </ul>
                    <?php }else{?>
                    <?php }?>
                    <ul class="list-ul" style="margin-bottom: 0px;">
                        <li style="background-color: #fff;height:1.5rem;line-height: 1.3rem;" id="add_card">
                            <table>
                                <tr>
                                    <td  style="width:1rem;text-align: center;">
                                        <img src="/src/img/add_card.png" style="width:0.5rem">
                                    </td>
                                    <td rowspan="3"  style="font-size: 0.3rem;color: #000;">
                                        添加信用卡
                                    </td>
                                </tr>
                            </table>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
<?php include T('Common/footer'); ?>
        
        <script>
            //选择付款账户
            $("#add_card").click(function () {
                location.href = "<?php echo $add_card_url; ?>";
            });
            var _lock = false;
            $(".but").click(function(){
                if(_lock){
                    alert('正在提交....');
                    return false;
                }
                if(!confirm('是否解绑？')){
                    return false;
                }
                _lock = true;
                var id = $(this).attr('data-id');
                var c_code = "<?php echo $c_code; ?>";
                $.ajax({
                    url: "<?php echo $del_cart_url; ?>",
                    data: {id: id,c_code:c_code},
                    type: 'post',
                    dataType: 'json',
                    success: function (data) {
                        if (data.status == 200) {
                            alert("解绑成功");
                            window.location.reload();
                        } else {
                            _lock = false;
                            alert(data.info);
                        }
                    }
                });
            });
        </script>
    </body></html>