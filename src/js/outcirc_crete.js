 var by_orderid ={/*根据ID获取相关信息和操作*/
    curr_obj:null,
    if_can_send:true,
    now_this_oid:'',
    _init:function(){
        by_orderid._listen_order();
        by_orderid.delete_td();
        by_orderid.jisuan_one_price();
    },
    _listen_order:function(){
        var _order_id = $("#J_input_order_id_tr");
        _order_id.unbind("keyup");
        _order_id.keyup(function(){
            var _this = $(this);                   
            by_orderid.curr_obj = _this;
            if(!isNaN($.trim(_this.val()))){                        
                if(_this.val().length>=7){
                     var osid = $("#J_osid_val").val();
                     var oid = _this.val();
                     by_orderid.now_this_oid = oid;
                    /*请求 1007339*/
                    if(by_orderid.if_can_send){
                        by_orderid.if_can_send = false;
                         $.get('/outcirc/getproductbyorderid?oid='+oid+"&osid="+osid,function (_data){
                            by_orderid._get_success(_data);
                         },'json'); 
                    }
                }

            }else{
                _this.blur();
                make_alert_html("提示","请输入正确的数字");
            }
        });
    },
    _get_success:function(data){/*请求成功，填充数据*/
       by_orderid.if_can_send = true;
       by_orderid.curr_obj.blur();       
        if(data.status!=200){
            make_alert_html("提示",data.info);
            return false;
        }
        if(data.data.length==0){
            make_alert_html("提示","没有相关数据");
            return false;
       }
        by_orderid.show_data(data);
    },
    show_data:function(data){ /*展现数据*/
        // var _siblings = by_orderid.curr_obj.parent().siblings();
        // _siblings.eq(0).find("b").html(data.data[0].sku_code);
        // _siblings.eq(1).find("b").html(data.data[0].product_name);
        // _siblings.eq(2).find("b").html(data.data[0].sku_name);
        // _siblings.eq(3).find(".J_puch_number").val(data.data[0].quantity);
        // _siblings.eq(4).find(".J_puch_price").val(data.data[0].price);
        // _siblings.eq(5).find(".J_total_price").val(_echo_price(parseInt(data.data[0].quantity)*parseFloat(data.data[0].price)));
        // if(data.data.length>1){/*如果数据不只一条*/
        //     by_orderid.more_show_data(data);
        // }
        by_orderid.more_show_data(data);
        by_orderid.jisuan_total_price(); /*新建tr之前计算总价*/
       // var _html = by_orderid.make_new_tr();
        if(by_orderid.curr_obj.closest("tr").next().find(".J_puch_order_id").val()==''){
            return false;
        }
       // $("#puchList thead").append(_html);
        //by_orderid._listen_order();/*绑定keyup*/   
        by_orderid.delete_td();/*绑定删除*/
        by_orderid.jisuan_one_price();            
    },
    more_show_data:function(data){ /*如果数据不只一条*/
        var _html ='';
        for(var i=0,len=data.data.length;i<len;i++){ /* 循环从1 开始 小于长度*/
            var _data = data.data[i];
            var _one_total_price = _echo_price(parseInt(_data.quantity)*parseFloat(_data.price));           
             _html+=
            '<tr class="J_puchlist_tr">'+  
                '<td class="puchList_td"><input class="J_puch_order_id" name="orderid" value="'+by_orderid.now_this_oid+'" readonly="readonly"/></td>'+                        
                '<td class="puchList_td"><b>'+_data.sku_code+'</b></td>'+
                '<td class="puchList_td"><b>'+_data.product_name+'</b></td>'+
                '<td class="puchList_td"><b>'+_data.sku_name+'</b></td>'+
                '<td class="puchList_td"><input class="J_puch_number" name="" value="'+_data.quantity+'" /></td>'+
                '<td class="puchList_td"><input class="J_puch_price" name="" value="'+_data.price+'" /></td>'+
                '<td class="puchList_td"><input class="J_total_price" name="" value="'+_one_total_price+'" readonly="readonly"/></td>'+
                '<td class="puchList_td"><input class="J_sale_name" name="saler_name" value=""  placeholder="输入姓名"/></td>'+
                '<td class="puchList_td"><a href="javascript:;"  class="button-red50 J_delete_this_td">删除</a></td>'+
            '</tr>';
        }
        $("#J_input_order_id_tr").parent().parent().before(_html);  
        $("#J_input_order_id_tr").val('');      
    },
    // make_new_tr:function(){/*输入一个ID之后，继续新建一个新的tr*/
    //      var _html = 
    //      '<tr class="J_puchlist_tr">'+
    //          '<td class="puchList_td"><input class="J_puch_order_id" name="id" value="" placeholder="输入ID"/></td>'+
    //          '<td class="puchList_td"><b></b></td>'+
    //          '<td class="puchList_td"><b></b></td>'+
    //          '<td class="puchList_td"><b></b></td>'+
    //          '<td class="puchList_td"><input class="J_puch_number" name="quantity" value="" /></td>'+
    //          '<td class="puchList_td"><input class="J_puch_price" name="price" value="" /></td>'+
    //          '<td class="puchList_td"><input class="J_total_price" name="" value="" readonly="readonly"/></td>'+
    //          '<td class="puchList_td"><input class="J_sale_name" name="saler_name" value="" placeholder="输入姓名"/></td>'+
    //          '<td class="puchList_td"><a href="javascript:;"  class="button-red50 J_delete_this_td">删除</a></td>'+
    //      '</tr>';
    //      return _html;
    // },
    jisuan_one_price:function(){
        var now = $(".J_puch_number,.J_puch_price");
        now.unbind("keyup'");
        now.keyup(function(){
            var _this = $(this);
            var _obj = _this.closest("tr");
            var one_price = parseInt(_obj.find(".J_puch_number").val())*parseFloat(_obj.find(".J_puch_price").val());
            _obj.find(".J_total_price").val(one_price);
            by_orderid.jisuan_total_price(); /*单个价格变化时 计算总价*/
        });
    },
    jisuan_total_price:function(){
        var _price_obj = $(".J_total_price");
        var _now_price = 0;
        for(var i=0,len=_price_obj.length;i<len;i++){
            var _val = _price_obj.eq(i).val();
            if(_val){
                 _now_price+=parseFloat(_val);
            }                   
        }                
         $("#J_total_price").find("b").html(_now_price);
    },
    delete_td:function(){ /*删除一行*/
        var _del_obj = $(".J_delete_this_td");
        _del_obj.unbind("click");
        _del_obj.click(function(){
            var _this = $(this);
            if($(".J_delete_this_td").length==1){
                make_alert_html("提示",'只有一行了');
                return false;
            }
            if(_this.parent().siblings().eq(1).find("b").html()==''){/*没有输入ID 填入数据的行 不能删*/
                 make_alert_html("提示",'请先输入ID，否则不能删除该行');
                return false;
            }
            _this.closest("tr").remove();
            by_orderid.jisuan_total_price();/* 计算总价*/
        });
    }
}
var next_step = {/*下一步*/
    if_can_send:true,   
 _init:function(){
    next_step._click();
 },
 _click:function(){
    var _step_btn = $("#J_next_step");
    _step_btn.unbind("click");
    _step_btn.click(function(){
        var _this = $(this);
        if(!next_step._check()){
            return false;
        }
        var _data = {};
        var order_id_arry=[],sku_code_arry = [],quantity_arry=[],price_arry=[],saler_name_arry=[];/* ,receiver_name_arry=[] */
        var _puch_tr = $(".J_puchlist_tr");                
        for(var i=0,len=_puch_tr.length;i<len;i++){
            var this_val = _puch_tr.eq(i).find("td");
           if(this_val.eq(0).find("input").val()!=''){/*orderid有值*/
                order_id_arry.push(this_val.eq(0).find("input").val());
                sku_code_arry.push(this_val.eq(1).find("b").html());  
                quantity_arry.push(this_val.eq(4).find("input").val());                     
                price_arry.push(this_val.eq(5).find("input").val()); 
                saler_name_arry.push(this_val.eq(7).find("input").val());                
           }
        }
        _data.order_id = order_id_arry;
        _data.sku_code = sku_code_arry;
        _data.quantity = quantity_arry;
        _data.price = price_arry;
        _data.saler_name = saler_name_arry;
        _data.remark = $("#J_remark").val();
        _data.step = '1';/*step//订单流程为1，非订单流程为2*/
        if(next_step.if_can_send){
            next_step.if_can_send = false;
            _this.html('发送中...');
             _ajax_post('/outcirc/create/?id='+$("#J_id").val(),_data,next_step._success);
        }
    });
 },
 _check:function(){
    var sale_name = $(".J_sale_name"),orderid = $(".J_puch_order_id");
    for(var i=0,len=orderid.length;i<len;i++){
        if(orderid.eq(i).val()!=''){
            if(sale_name.eq(i).val()==''){
                tip_error_flashing(sale_name.eq(i));
                return false;
            }
        }
    }
    return true;
 },
 _success:function(data){     
     next_step.if_can_send = true;
     $("#J_next_step").html('下一步');
    if(data.status!=201){
        make_alert_html("提示",data.info);
        return false;
    }
    var order_detail =$("#J_order_detail"),order_menu_div=  $("#J_order_menu_div");
    order_detail.css("display","none");
    order_menu_div.find("a").eq(0).removeClass("on");
    order_menu_div.find("a").eq(1).addClass("on");
    order_detail.after(next_step.make_html(data));
    next_step.click_back_btn();
    not_order._init(); /*初始化*/
 },
 make_html:function(data){
    var _html = 
    '<div class="form" id="J_not_order_detail">'+
        '<table  class="puchList" id="J_two_tabel">'+
            '<thead>'+
                '<tr>'+                           
                    '<th>选项编码</th>'+
                    '<th>商品名称</th>'+
                    '<th>选项</th>'+
                    '<th>数量</th>'+ 
                    '<th>价格</th>'+                             
                    '<th>领取人</th>'+
                    '<th>操作</th>'+
                '</tr>';
                var total_num = 0,total_price=0;
                for(var i=0,len=data.data.length;i<len;i++){
                    var _data = data.data[i];
                    total_num+=parseInt(_data.quantity);
                    total_price+=parseFloat(_data.price)*parseInt(_data.quantity);
                     _html+=
                    '<tr class="J_not_puchlist_tr">'+                          
                        '<td class="puchList_td"><b>'+_data.sku_code+'</b></td>'+
                        '<td class="puchList_td"><b>'+_data.product_name+'</b></td>'+
                        '<td class="puchList_td"><b>'+_data.sku_name+'</b></td>'+
                        '<td class="puchList_td"><input class="J_two_puch_number" name="" value="'+_data.quantity+'" /></td>'+
                        '<td class="puchList_td"><input class="J_two_one_price" name="" value="'+_data.price+'" /></td>'+
                        '<td class="puchList_td"><input class="J_two_receiver_name" name="" placeholder="填写领取人" /></td>'+
                        '<td class="puchList_td"><a href="javascript:;"  class="button-red50 J_del_this_not_td">删除</a></td>'+
                    '</tr>';
                }               
                _html+=
            '</thead>'+
        '</table>'+
        // '<div class="next_option" id="J_add_new_td">新增</div>'+
        '<div class="num_next_div">'+
            '<div class="message_num" id="J_total_pric_num">成交金额：<b>'+_echo_price(total_price)+'</b></div>'+
            '<div class="next_option" id="J_commit_now_order">提交</div>'+
        '</div>'+
        '<div class="back_button" id="J_back_button">返回</div>'+
    '</div>';
    return _html;
 },
 click_back_btn:function(){
    var back_btn = $("#J_back_button");
    back_btn.unbind("click");
    back_btn.click(function(){
        var _this = $(this);
        var order_detail =$("#J_order_detail"),order_menu_div=  $("#J_order_menu_div");
        order_detail.css("display","");
        $("#J_not_order_detail").css("display","none");
        order_menu_div.find("a").eq(0).addClass("on");
        order_menu_div.find("a").eq(1).removeClass("on");
    });
 }
}
var not_order = { /*非订单明细的相关操作*/
    global_ruku_data:null,
    if_can_send:true,
    _init:function(){
        not_order._del_this_td();
        not_order._listen_num_price();
        not_order.commit_this();
    },   
    _del_this_td:function(){
         var _del_btn = $(".J_del_this_not_td");
        _del_btn.unbind("click");
        _del_btn.click(function(){
            var _this = $(this);
            _this.closest("tr").remove();
            not_order.jisuan_total_price();
        });
    },    
    _listen_num_price:function(){ /*监听数量和价格的变化*/
        var now = $(".J_two_puch_number,.J_two_one_price");
        now.unbind("keyup'");
        now.keyup(function(){            
            not_order.jisuan_total_price();/*单个价格变化时 计算总价*/
        });
    },
    jisuan_total_price:function(){
        var _price_obj = $(".J_two_one_price");
        var _now_price = 0;
        for(var i=0,len=_price_obj.length;i<len;i++){
            var _val = _price_obj.eq(i).val();
            var _val_num = $(".J_two_puch_number").eq(i).val();
            if(_val&&_val_num){                
                _now_price+=parseFloat(_val)*parseInt(_val_num);               
            }        
        } 
        $("#J_total_pric_num").find("b").html(_now_price);
    },
    _check:function(){
        var puch_num = $(".J_two_puch_number"),_price=$(".J_two_one_price");       
        for(var i=0,len=puch_num.length;i<len;i++){
            if($.trim(puch_num.eq(i).val())==''||isNaN(puch_num.eq(i).val())){
                tip_error_flashing(puch_num.eq(i));
                make_alert_html("提示","请输正确的数量");
                return false;
            }
             if($.trim(_price.eq(i).val())==''||isNaN(_price.eq(i).val())){
                tip_error_flashing(_price.eq(i));
                make_alert_html("提示","请输正确的价格");
                return false;
            }
        }
        return true;
    },
    commit_this:function(){
        var not_commit = $("#J_commit_now_order");
        not_commit.unbind("click");
        not_commit.click(function(){
            var _this = $(this);
            if(!not_order._check()){
                return false;
            }
            var _data = {};
            var sku_code_arry = [],quantity_arry=[],price_array=[],receiver_name_array=[];
            var _puch_tr = $(".J_not_puchlist_tr");                
            for(var i=0,len=_puch_tr.length;i<len;i++){
                var this_val = _puch_tr.eq(i).find("td");
               if(this_val.eq(0).find("b").html()!=''){/*orderid有值*/                      
                    sku_code_arry.push(this_val.eq(0).find("b").html());  
                    quantity_arry.push(this_val.eq(3).find("input").val());
                    price_array.push(this_val.eq(4).find("input").val());
                    receiver_name_array.push(this_val.eq(5).find("input").val() );
               }
            }             
            _data.sku_code = sku_code_arry;
            _data.quantity = quantity_arry;
            _data.price = price_array;
            _data.receiver_name = receiver_name_array;
            _data.remark = $("#J_remark").val();
            _data.step = '2';
            if(not_order.if_can_send){
                not_order.if_can_send = false;
                _this.html("发送中...");
                _ajax_post('/outcirc/create/?id='+$("#J_id").val(),_data,not_order.commit_success);
            }            
        });                
    },
    commit_success:function(data){  
        not_order.if_can_send = true;    
        $("#J_commit_now_order").html("提交");  
        not_order.global_ruku_data = data;
         if(data.data&&typeof(data.data)!='undefined'&&data.data.length!=0){//有需要重新入库的数据
            confirm_cancle(not_order.re_storagef,'提示','您有 '+data.data.length+' 个选项需要重新入库，重新入库后才能创建出库明细哦~','重新入库');
         }else{
            if(data.status!=200){
                make_alert_html("提示",data.info);
                return false;
            }
             make_alert_html("提示",data.info,'/outcirc/index.html',true);            
         }
    },
    re_storagef:function(){ /*重新入库*/
        var need_data = not_order.global_ruku_data.data;
        var sku_code_arry=[],product_code_arry=[],product_name_arry=[],sku_name_arry=[],price_arry=[],quantity_arry=[],saler_name_arry=[],receiver_name_arry=[];
        var _data = {};  
        for(var i=0,len=need_data.length;i<len;i++){
            sku_code_arry.push(need_data[i].sku_code);
            product_code_arry.push(need_data[i].product_code);
            product_name_arry.push(need_data[i].product_name);
            sku_name_arry.push(need_data[i].sku_name);
            price_arry.push(need_data[i].price);
            quantity_arry.push(need_data[i].quantity);
            saler_name_arry.push(need_data[i].saler_name);
            receiver_name_arry.push(need_data[i].receiver_name);
        }  
        _data.sku_code = sku_code_arry;
        _data.product_code = product_code_arry;
        _data.product_name = product_name_arry;
        _data.sku_name = sku_name_arry;
        _data.price = price_arry;
        _data.quantity = quantity_arry;
        _data.saler_name = saler_name_arry;
        _data.receiver_name = receiver_name_arry;
        _data.circ_id = $("#J_id").val();
        $("#J_commit_now_order").html("重新入库中...");  
        _ajax_post('/outcirc/reinstock',_data,not_order.re_storagef_success);
    },
    re_storagef_success:function(data){
        $("#J_commit_now_order").html("提交");  
         if(data.status!=200){
            make_alert_html("提示",data.info);
            return false;
        }
         make_alert_html("提示",data.info,'/outcirc/index.html',true);
    }

}
by_orderid._init();
next_step._init();