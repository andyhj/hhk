 /*
    公用提示框
    title：提示框标题
    content：提示框的提示内容
    _url :传递过来的url地址
    flg  :点击关闭按钮是否刷新页面 true 刷新 false不刷新
    sure_content:传参，下面那个按钮的HTML，比如 是【确定】还是【点击认证】等等
    */
    var make_alert_html =function(title,content,_url,flg,sure_content){
		var move_height = 30;//弹出框移动高度
    	if($("#J_pop_wrap").length>0){
    		$("#J_pop_wrap").remove();//先去除之前的弹出框
    		$("#J_mask").remove();//先去除之前的遮罩层
    	}
    	var _sure_content = typeof(sure_content)=='undefined'?'确定':sure_content;
    	var _alert_html = //提示框代码
		'<div class="pop_wrap" id="J_pop_wrap" style="display:block;width:400px;_width:400px;height:auto;background-color:#fff;min-height:150px;z-index:999999;">'+
			'<div class="pop_title">'+
			'  <span class="f_l">'+title+'</span>'+
			'  <a href="javascript:void(0);" class="close f_r" id="J_close">关闭</a>'+
			'</div>'+
			'<div class="pop_main">'+
				'<div class="pop_content main_c f16" style="font-size:16px;">'+content+				
				'</div>'+
				'<div class="pop_nav align_c">'+
					'<a href="javascript:void(0);" class="nav_sure" id="J_nav_sure">'+_sure_content+'</a>'+				
				'</div>'+
			'</div>'+
		'</div>';
	    common_mask();//调用公用遮罩层		
		
		$(_alert_html).appendTo($('body'));	//添加到body里面
		$("#J_nav_sure").click(function(){
			_sure();
		});
		$("body").focus();	
		$("html").keydown(function(e){
			var flag;
			var e = e||window.event;
			var keycode  =  e.keyCode||e.which; // 按键的keyCode
			if(keycode==13){
				$("html").unbind('keydown');
				_sure();
			}
		});	
		
		$("#J_close").click(function(){
			_close();
		});
		
		var _sure = function(){
			$("#J_pop_wrap").animate({'top': (str_top-move_height)+'px',opacity:'0'}, 200);
			setTimeout(function(){
				if(typeof(_url)=='undefined'||!_url){
					$("#J_pop_wrap").remove();
					$("#J_mask").remove();
				}else{
					$("#J_pop_wrap").remove();
					$("#J_mask").remove();
					window.location.href = _url;
				}	
			},200);
		}
		
		var _close = function(){
			$("#J_pop_wrap").animate({'top': (str_top-move_height)+'px',opacity:'0'}, 200);
			setTimeout(function(){
				if(typeof(flg)=='undefined'||flg ==''){
					$("html").unbind('keydown');
					$("#J_pop_wrap").remove();
					$("#J_mask").remove();
				}else if(flg){
					window.location.href = _url;
				}
			},200);
			/*if(typeof(_url)!='undefined' && _url){
				window.location.href = _url;	
			}	*/
		}
		var newDivHeight = $('#J_pop_wrap').css("height");//弹出框本身的高
		var newDivWidth = $('#J_pop_wrap').css("width");	
		var see_height = $(window).height();  //看到的页面的那部分的高度
		var see_width =   $(window).width();	
		var str_left = (see_width -parseInt(newDivWidth))/2;
		var str_top = (see_height -parseInt(newDivHeight))/2;
	    var scrollTop = document.body.scrollTop || document.documentElement.scrollTop; //滚轮的高度
		
	    if(navigator.userAgent.toLowerCase().indexOf("msie 6.0")!=-1){//如果是IE6
	    	$("#J_pop_wrap").css({
	    		'position':'absolute',
		    	'top':(str_top-move_height+scrollTop)+'px',
		    	'left':str_left+'px',
				'zIndex':999999,
				opacity:0
		    });
		    $("#J_pop_wrap").animate({'top': (str_top+scrollTop)+'px',opacity:'1'}, 200);
	    }else{//非IE6
	    	//alert("str_top:"+str_top+"move_height:"+move_height+"-------str_left:"+str_left);
	    	$("#J_pop_wrap").css({
	    		'position':'fixed',
		    	'top':(str_top-move_height)+'px',
		    	'left':str_left+'px',
				'zIndex':999999,
				opacity:0
		    });
		    //alert($("#J_pop_wrap").css("position")+$("#J_pop_wrap").css("top")+$("#J_pop_wrap").css("left"));
		  setTimeout(function(){
		    $("#J_pop_wrap").animate({'top': str_top+'px',opacity:'1'}, 200);
		  },10);
	    }
	    
    }
/*
		确定/取消 公用框  confirm
		fun1:传过去的回调函数
		title:弹出框的title ，是警告 还是提示， 
		content: [可不传]，在中间显示的提示内容
		first_str:用于输出J_sure_cc 这个按钮的文字内容和功能
		
	*/
	var confirm_cancle =function (fun1,title,content,first_str){
		var move_height = 30;//弹出框移动高度	
		if(typeof content=="undefined"){
			content="";
		}
		var _html = 
			'<div class="pop_wrap" id="J_cc_div" style="left:800px;top:350px;display:block;width:auto;_width:400px;height:auto;background-color:#fff;max-width:500px;min-width:400px;min-height:150px;position:fixed !important;z-index:999999;">'+
				'<div class="pop_title">'+
					'<span class="f_l">'+title+'</span>'+
					'<a href="javascript:void(0);" class="close_btn f_r" id="J_close_cc">关闭</a>'+
				'</div>'+
				'<div class="pop_main">'+
					'<div class="pop_content main_c f16" id="J_content_cc">'+content+'</div>'+					
					'<div class="pop_nav align_c">'+
						'<a href="javascript:void(0);" class="nav_sure" id="J_sure_cc">'+first_str+'</a>'+
						'<a href="javascript:void(0);" class="nav_none" id="J_cancel_cc">取消</a>'+
					'</div>'+					
				'</div>'+
			'</div>';
		common_mask();//调用公用遮罩层
		$(_html).appendTo($('body'));
		var newDivHeight = $('#J_cc_div').css("height");
		var newDivWidth = $('#J_cc_div').css("width");	
		var see_height = $(window).height();  //看到的页面的那部分的高度
		var see_width =   $(window).width();	
		var str_left = (see_width -parseInt(newDivWidth))/2;
		var str_top = (see_height -parseInt(newDivHeight))/2;
	    var scrollTop = document.body.scrollTop || document.documentElement.scrollTop; //滚轮的高度
	    if(str_top<move_height){
        	str_top = move_height;
        }
        if(navigator.userAgent.toLowerCase().indexOf("msie 6.0")!=-1){
            $("#J_cc_div").css('position','absolute');
            str_top = $(document).scrollTop()+str_top;//IE6不支持fixed属性，用absolute的时候需要加上滚轮的高度
        }
        else{
            $("#J_cc_div").css('position','fixed');
        }
	    if(navigator.userAgent.toLowerCase().indexOf("msie 6.0")!=-1){//如果是IE6
	    	$("#J_cc_div").css({
		    	'top':(str_top-move_height)+'px',
		    	'left':str_left+'px',
				'zIndex':999999,
				opacity:0
		    });
		    $("#J_cc_div").animate({'top': (str_top)+'px',opacity:'1'}, 200);
	    }else{//非IE6
	    	$("#J_cc_div").css({
		    	'top':(str_top-move_height)+'px',
		    	'left':str_left+'px',
				'zIndex':999999,
				opacity:0
		    });
		    $("#J_cc_div").animate({'top': str_top+'px',opacity:'1'}, 200);
	    }
	    $("html").keydown(function(e){
			var flag;
			var e = e||window.event;
			var keycode  =  e.keyCode||e.which; // 按键的keyCode
			if(keycode==13){	
				$("html").unbind('keydown');
				$("#J_cc_div").remove();
				$("#J_mask").remove();
				fun1();
			}
		});	
	    $("#J_close_cc,#J_cancel_cc").unbind("click");
	    $("#J_close_cc,#J_cancel_cc").click(function(){
			$("#J_cc_div").remove();
			$("#J_mask").remove();
			return false;
		});	
		$("#J_sure_cc").unbind("click");
		$("#J_sure_cc")	.click(function(){
			$("#J_cc_div").remove();
			$("#J_mask").remove();
			fun1();
		});
		//document.onkeydown=keyDownSearch;  //绑定回车事件
	}

/*
		公用遮罩层(在本js中可用)
	*/
	function common_mask(){
		if($("#J_mask").length>0){
			$("#J_mask").remove();
		}
		var newMask = document.createElement("div");
        newMask.id = 'J_mask';
        newMask.style.position = "absolute";
        newMask.style.zIndex = "999";
        _scrollWidth = Math.max(document.body.scrollWidth, document.documentElement.scrollWidth);
        _scrollHeight = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
        //_scrollWidth = document.body.clientWidth+document.body.scrollLeft
        //_scrollHeight= document.body.clientHeight+document.body.scrollTop //另一种方法
        newMask.style.width = _scrollWidth + "px";
        newMask.style.height = _scrollHeight + "px";
        newMask.style.top = "0px";
        newMask.style.left = "0px";
        newMask.style.background = "#33393C";
        newMask.style.filter = "alpha(opacity=4)";
        newMask.style.filter="progid:DXImageTransform.Microsoft.Alpha(style=4,opacity=25)";//IE的不透明设置
        newMask.style.opacity = "0.40";
        document.body.appendChild(newMask);
	}
   /*
    公用提示框
    title：提示框标题
    content：提示框的提示内容
    _url :传递过来的url地址
    flg  :点击关闭按钮是否刷新页面 true 刷新 false不刷新
    sure_content:传参，下面那个按钮的HTML，比如 是【确定】还是【点击认证】等等
    */
    var make_alert_html =function(title,content,_url,flg,sure_content){
		var move_height = 30;//弹出框移动高度
    	if($("#J_pop_wrap").length>0){
    		$("#J_pop_wrap").remove();//先去除之前的弹出框
    		$("#J_mask").remove();//先去除之前的遮罩层
    	}
    	var _sure_content = typeof(sure_content)=='undefined'?'确定':sure_content;
    	var _alert_html = //提示框代码
		'<div class="pop_wrap" id="J_pop_wrap" style="display:block;width:400px;_width:400px;height:auto;background-color:#fff;min-height:150px;z-index:999999;">'+
			'<div class="pop_title">'+
			'  <span class="f_l">'+title+'</span>'+
			'  <a href="javascript:void(0);" class="close_btn f_r" id="J_close">关闭</a>'+
			'</div>'+
			'<div class="pop_main">'+
				'<div class="pop_content main_c f16" style="font-size:16px;">'+content+				
				'</div>'+
				'<div class="pop_nav align_c">'+
					'<a href="javascript:void(0);" class="nav_sure" id="J_nav_sure">'+_sure_content+'</a>'+				
				'</div>'+
			'</div>'+
		'</div>';
	    common_mask();//调用公用遮罩层		
		
		$(_alert_html).appendTo($('body'));	//添加到body里面
		$("#J_nav_sure").click(function(){
			_sure();
		});
		$("body").focus();	
		$("html").keydown(function(e){
			var flag;
			var e = e||window.event;
			var keycode  =  e.keyCode||e.which; // 按键的keyCode
			if(keycode==13){
				$("html").unbind('keydown');
				_sure();
			}
		});	
		
		$("#J_close").click(function(){
			_close();
		});
		
		var _sure = function(){
			$("#J_pop_wrap").animate({'top': (str_top-move_height)+'px',opacity:'0'}, 200);
			setTimeout(function(){
				if(typeof(_url)=='undefined'||!_url){
					$("#J_pop_wrap").remove();
					$("#J_mask").remove();
				}else{
					$("#J_pop_wrap").remove();
					$("#J_mask").remove();
					window.location.href = _url;
				}	
			},200);
		}
		
		var _close = function(){
			$("#J_pop_wrap").animate({'top': (str_top-move_height)+'px',opacity:'0'}, 200);
			setTimeout(function(){
				if(typeof(flg)=='undefined'||flg ==''){
					$("html").unbind('keydown');
					$("#J_pop_wrap").remove();
					$("#J_mask").remove();
				}else if(flg){
					window.location.href = _url;
				}
			},200);
			/*if(typeof(_url)!='undefined' && _url){
				window.location.href = _url;	
			}	*/
		}
		var newDivHeight = $('#J_pop_wrap').css("height");//弹出框本身的高
		var newDivWidth = $('#J_pop_wrap').css("width");	
		var see_height = $(window).height();  //看到的页面的那部分的高度
		var see_width =   $(window).width();	
		var str_left = (see_width -parseInt(newDivWidth))/2;
		var str_top = (see_height -parseInt(newDivHeight))/2;
	    var scrollTop = document.body.scrollTop || document.documentElement.scrollTop; //滚轮的高度
		
	    if(navigator.userAgent.toLowerCase().indexOf("msie 6.0")!=-1){//如果是IE6
	    	$("#J_pop_wrap").css({
	    		'position':'absolute',
		    	'top':(str_top-move_height+scrollTop)+'px',
		    	'left':str_left+'px',
				'zIndex':999999,
				opacity:0
		    });
		    $("#J_pop_wrap").animate({'top': (str_top+scrollTop)+'px',opacity:'1'}, 200);
	    }else{//非IE6
	    	//alert("str_top:"+str_top+"move_height:"+move_height+"-------str_left:"+str_left);
	    	$("#J_pop_wrap").css({
	    		'position':'fixed',
		    	'top':(str_top-move_height)+'px',
		    	'left':str_left+'px',
				'zIndex':999999,
				opacity:0
		    });
		    //alert($("#J_pop_wrap").css("position")+$("#J_pop_wrap").css("top")+$("#J_pop_wrap").css("left"));
		  setTimeout(function(){
		    $("#J_pop_wrap").animate({'top': str_top+'px',opacity:'1'}, 200);
		  },10);
	    }
	    
    }
   function _ajax_post(_url,_data,fun){
		$.ajax({
			//提交数据的类型 POST GET
			type:"POST",
			url:_url,
			//提交的数据		            
			data:_data,
			cache:false,
			//返回数据的格式
			datatype: "json",             
			success:function(data){
				if(typeof(fun)=='undefined'){
					return false;	
				}				
				fun(data);
			},
			error:function(){
				make_alert_html("提示您","网络繁忙请稍后再试");
			}
		 });
	}

	/*
    公用上传弹窗 
    */
     function make_common_open_html(_alert_html){     
    	var move_height = 30;//弹出框移动高度
    	if($("#J_open_div").length>0){
    		$("#J_open_div").remove();//先去除之前的公用弹窗
    		$("#J_mask").remove();//先去除之前的遮罩层
    	}
	
        common_mask();//调用公用遮罩层

        $(_alert_html).appendTo($('body'));
		var close_tan_btn = $("#J_close_tan");
		if($(".J_close_tan").length>0){
			close_tan_btn = $(".J_close_tan");
		}		
        var newDivHeight = $('#J_open_div').css("height");
        var newDivHeight_2 = $('#J_open_div').outerHeight();
        var newDivWidth = $('#J_open_div').css("width");	
        var see_height = $(window).height();  //看到的页面的那部分的高度
        var see_width =   $(window).width();
        var str_left = (see_width -parseInt(newDivWidth))/2;
        var str_top = (see_height -parseInt(newDivHeight))/2;
        if(str_top<move_height){
        	str_top = move_height;
        }
        
        $("#J_open_div").css('position','fixed');
        close_tan_btn.click(function(){
			common_open_html_close();	
		});
        $("#J_open_div").css({   
        	'top':(str_top-move_height)+'px',
            'left':str_left+'px',         
			'zIndex':99999,
			'opacity':0
        });
        setTimeout(function(){
        	$("#J_open_div").animate({top:str_top+'px',opacity:'1'}, 200); 
        },10);
    }
    var common_open_html_close = function(){
		var move_height = 30;//弹出框移动高度
		var newDivHeight = $('#J_open_div').css("height");
		var see_height = $(window).height();  //看到的页面的那部分的高度
		var str_top = (see_height -parseInt(newDivHeight))/2;
		$("#J_open_div").animate({'top': (str_top-move_height)+'px',opacity:'0'}, 200);
		setTimeout(function(){
			$("#J_open_div").remove();
			$("#J_mask").remove();						
		},200);
	}
	/*价格输出*/	
	function _echo_price(float_price){
		var int_price = parseInt(float_price);
		//var flt_price = parseFloat(float_price).toFixed(2);
		var flt_price = parseFloat(float_price);
		if(int_price==float_price){//如果整型
			return int_price;
		}else{//如果浮点型
			if(flt_price>int_price){//带小数的，且小数不为00
				float_price = flt_price.toFixed(2);
				return float_price;
			}else if(flt_price==int_price){//带小数，且小数为00
				return int_price;
			}
		}
		
	}
	/*错误信息的闪动效果
	传一个节点元素dom
	*/
	var timer;
	var old_dom;
	function tip_error_flashing(dom){		
		var i =0;
		clearInterval(timer);
		if(typeof(old_dom)=='object'){
			old_dom.css("background","");
		}
		old_dom = dom;
		timer = setInterval(function(){
			i++;
			if(i%2==0){
				dom.css("background","yellow");
			}else{
				dom.css("background","");
			}			
			if(i>6){
				clearInterval(timer);
			}
		},100);		
	}