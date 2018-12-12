<!DOCTYPE HTML>
<html>

<head>
	<meta charset="utf-8">
	<title>开心逗棋牌</title>
	<meta name="viewport" content="width=device-width,initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="full-screen" content="true" />
	<meta name="screen-orientation" content="portrait" />
	<meta name="x5-fullscreen" content="true" />
	<meta name="360-fullscreen" content="true" />
	<style> 
        html, body {
            -ms-touch-action: none;
            background: #888888;
            padding: 0;
            border: 0;
            margin: auto;
            height: 100%;
        }
		.egret-player {
			background-image: url(<?=CDN_HOST; ?>/img/bg.png);
		}
		.canvasId1 {
			position: absolute;
			top: 0;
			bottom: 0;
			left:0;
			right:0;
			margin: auto;
			z-index: 1;
            height: 100%;
		}
		.canvasId2 {
			position: absolute;
			top: 0;
			bottom: 0;
			left:0;
			right:0;
			margin: auto;
			z-index: 1;
            height: 100%;
		}
    </style>
<!--这个标签为通过egret提供的第三方库的方式生成的 javascript 文件。删除 modules_files 标签后，库文件加载列表将不会变化，请谨慎操作！-->
<!--modules_files_start-->
	<script egret="lib" src="<?=CDN_HOST; ?>/fk/libs/modules/egret/egret.min.js"></script>
	<script egret="lib" src="<?=CDN_HOST; ?>/fk/libs/modules/egret/egret.web.min.js"></script>
	<script egret="lib" src="<?=CDN_HOST; ?>/fk/libs/modules/eui/eui.min.js"></script>
	<script egret="lib" src="<?=CDN_HOST; ?>/fk/libs/modules/res/res.min.js"></script>
	<script egret="lib" src="<?=CDN_HOST; ?>/fk/libs/modules/tween/tween.min.js"></script>
	<script egret="lib" src="<?=CDN_HOST; ?>/fk/libs/modules/game/game.min.js"></script>
	<script egret="lib" src="<?=CDN_HOST; ?>/fk/libs/modules/socket/socket.min.js"></script>
	<script egret="lib" src="<?=CDN_HOST; ?>/fk/libs/modules/dragonBones/dragonBones.min.js"></script>
	<script egret="lib" src="<?=CDN_HOST; ?>/fk/libs/modules/protobuf/protobuf.min.js"></script>
	<script egret="lib" src="<?=CDN_HOST; ?>/fk/libs/modules/jszip/jszip.min.js"></script>
        <script src="https://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
	<!--modules_files_end-->

<!--这个标签为不通过egret提供的第三方库的方式使用的 javascript 文件，请将这些文件放在libs下，但不要放在modules下面。-->
<!--other_libs_files_start-->
<!--other_libs_files_end-->

<!--这个标签会被替换为项目中所有的 javascript 文件。删除 game_files 标签后，项目文件加载列表将不会变化，请谨慎操作！-->
<!--game_files_start-->
	<script src="<?=CDN_HOST; ?>/fk/main.min.js?v={$version}"></script>
	<!--game_files_end-->
<script type="text/javascript">
		var etControl = {};
		etControl.process = function (config) {
			/*需要放在html中的body标签后面使用本控件*/
			var count = 0;
			/*更新进度条*/
			this.updateProcess = function (tips, total) {
				++count;
				drawPercent(count / total * 100);
			};
		}
                
    </script>
</head>
<body onpageshow="onpageshow()" onresize="onresize()">
<canvas id="canvasId" width="1136" height="640" class="canvasId1" style="width:1136px;height:640px;"></canvas>
<div id="egret-player" style="margin: auto;width: 100%;height: 100%;" class="egret-player" data-entry-class="Main" data-orientation="auto" data-scale-mode="showAll" data-frame-rate="30" data-content-width="1136"
data-content-height="640" data-show-paint-rect="false" data-multi-fingered="2" data-show-fps="false" data-show-log="false"
data-show-fps-style="x:0,y:0,size:12,textColor:0xffffff,bgAlpha:0.9">
</div>
<script type="text/javascript">
	/*需要放在body标签后面，然后在适当的位置调用updateProcess方法*/
	var p = new etControl.process();
</script>
<script>
		window.testPlayerId = 100001;//100002 100639  100716 100752 100962  100986 100987
		window.testGuideStatus = 1;//0 还没创建 1 已经存在
                window.roomid = {$roomid};
                window.roomcode = "{$roomcode}";
                window.gameType = {$gameType};
                window.modeType = {$modeType};
		window.onerror = handleErr;
                
                wx.config({
                    debug: false,
                    appId: '{$wx_config[appId]}',
                    timestamp: "{$wx_config[timestamp]}",
                    nonceStr: '{$wx_config[nonceStr]}',
                    signature: '{$wx_config[signature]}',
                    jsApiList: [
                        'onMenuShareTimeline',
                        'onMenuShareAppMessage',
                        'scanQRCode'
                    ]
                });
                wx.ready(function () {
                    ready_share();
                });

                
                wx.error(function (res) {
                    //alert(res.errMsg);
                });

		function handleErr(msg, url, l, l2, error) {
			GlobalFunction.catchError(msg, url, l, l2, error);
			return true;
		}
                window.ready_share = function()
                {
                    app_share({$roomid},{$roomcode},"{$share_title}","{$share_des}","<?=CDN_HOST; ?>/images/share/lobby/gameicon.png",{$gameType},{$modeType});
                }
                window.app_share = function(roomid,roomcode,wxtitle,wxdesc,imgUrl,gameType,modetype)
                {
                    var shareData64 = {
                        title: wxtitle,
                        desc: wxdesc,
                        link: "{$game_url}{$game_uid}-"+roomid+"-"+roomcode+"-"+gameType+"-"+modetype+".html",
                        imgUrl: imgUrl,
                        success: function () { 
                            //location=''
                            // 用户确认分享后执行的回调函数

                        },

                        cancel: function () { 

                            // 用户取消分享后执行的回调函数

                        }
                    };
                    wx.onMenuShareAppMessage(shareData64);
                    wx.onMenuShareTimeline(shareData64);
                }
                window.close_window = function()
                {
                    wx.closeWindow();
                }
                window.scan = function()
                {
                    wx.scanQRCode({
                        desc: 'scanQRCode desc',
                        needResult: 1, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
                        scanType: ["qrCode","barCode"], // 可以指定扫二维码还是一维码，默认二者都有
                        success: function (res) {				    
                                if(res.errMsg == 'scanQRCode:ok'){
                                    var result = res.resultStr; // 当needResult 为 1 时，扫码返回的结果
                                    var strs= new Array(); //定义一数组
                                        if(result.indexOf(",")!=-1){
                                            ReceiveJS.scan.callBackUrl(strs);
                                            //return strs;
                                        }else{
                                            ReceiveJS.scan.callBackUrl(result);
                                            //return result;
                                        }
                                    }
                            }
                    });
                }
		//游戏获取参数
		window.getgameparams = function()
		{
			//alert(srvresobj);
			var paramobj = new Object();
			//paramobj.uid = "0";
			paramobj.uid = "{$game_uid}";
			paramobj.sid = "{$auth_key}";
			//paramobj.wwapiUrl = "https://192.168.1.16/happyapi/restsrv.php";
			paramobj.wwapiUrl = "<?=API_HOST; ?>/restsrv.php";
			//paramobj.cdnUrl = "https://192.168.1.16/happyqpres/";
			paramobj.cdnUrl = "<?=CDN_HOST; ?>/fk/";
			paramobj.avatarurl = "";
			paramobj.version = "{$version}";
                        paramobj.roomid = window.roomid;
                        paramobj.roomcode = window.roomcode + "";
                        paramobj.gameType = window.gameType;
                        paramobj.modeType = window.modeType;
                        paramobj.pf = 1;
			return paramobj;
		}
		//微信登陆
		window.wxlogin = function()
		{
			location.href = "{$wxlogin_url}";
		}
		function refresh() {
			location.reload();
		}
		var isHengPing = true;
		var ww = 0, wh = 0;
		var  canvas  =  document.getElementById("canvasId");
		
		var  ctx1  =  canvas.getContext("2d");
		var image1 = new Image();
		var image2 = new Image();
		var image3 = new Image();

		// polyfill 提供了这个方法用来获取设备的 pixel ratio
		var getPixelRatio = function(context) {
			var backingStore = context.backingStorePixelRatio ||
				context.webkitBackingStorePixelRatio ||
				context.mozBackingStorePixelRatio ||
				context.msBackingStorePixelRatio ||
				context.oBackingStorePixelRatio ||
				context.backingStorePixelRatio || 1;
		
			return (window.devicePixelRatio || 1) / backingStore;
		};

		var ratio = getPixelRatio(ctx1);
		var bili = 1;
		function intiCanvas() {
			ww = window.innerWidth;
			wh = window.innerHeight;
			if (ww > wh) {
				isHengPing = true;
				if (ww * 0.563 >= wh) {
					ww = Math.floor(wh * 1136 / 640);
				} else {
					wh = Math.floor(ww * 640 / 1136);
				}
				bili = ww / 1136;
			} else {
				isHengPing = false;
				if (wh * 0.563 > ww) {
					wh = Math.floor(ww * 1136 / 640);
				} else {
					ww = Math.floor(wh * 640 / 1136);
				}
				bili = wh / 1136;
			}
			scaseCanvas(ratio);
		}
		function scaseCanvas(num){
			if (isHengPing) {
				canvas.setAttribute("style","width:"+ww+"px;height:"+wh+"px;");
				canvas.setAttribute('width', ww*num);
				canvas.setAttribute('height', wh*num);
				canvas.setAttribute("class", "canvasId1");
				canvas.style.width = ww;////////////重点  
				canvas.style.height = wh;////////////重点  
			} else {
				canvas.setAttribute("style","width:"+ww+"px;height:"+wh+"px;");
				canvas.setAttribute('width', ww*num);
				canvas.setAttribute('height', wh*num);
				canvas.setAttribute("class", "canvasId2");
				canvas.style.height = wh;////////////重点  
				canvas.style.width = ww;////////////重点  
			}
		}
		function onImageLoad1(image1) {
			image1.onload  =  function  () {
				canDraw = true;
				drawIamge1();
			}
		}
		function drawIamge1(){
			intiCanvas();
			if (isHengPing) {
				var drawW = image1.width*bili*ratio;
				var drawH = image1.height*bili*ratio;
				var ph = wh*ratio;
				//ctx1.drawImage(image1, 0, 0, image1.width, image1.height, (canvas.width-(drawW-drawW*0.1))*0.5 , ph*0.78-ph*0.12-drawH, drawW, drawH);
				ctx1.drawImage(image1, 0, 0, image1.width, image1.height, 0 , 0, drawW, drawH);
				ctx1.restore();
			} else {

				var xpos = canvas.width / 2 - canvas.height/2;
				var ypos = canvas.height/2-canvas.width/2;

				var drawW = image1.width*bili*ratio;
				var drawH = image1.height*bili*ratio;
				ctx1.translate(canvas.width / 2, canvas.height / 2);
				ctx1.rotate(90 * Math.PI / 180);//旋转-90度
				ctx1.translate(-canvas.width / 2, -canvas.height / 2);
				//ctx1.drawImage(image1,xpos+(canvas.height-(drawW-drawW*0.1))*0.5 , ypos+canvas.width*0.75-canvas.width*0.15-drawH , drawW, drawH);
				ctx1.drawImage(image1,xpos , ypos , drawW, drawH);
				ctx1.restore();
			}
			canDraw1 = true;
			drawIamge2(image2,100,false);
		}
		function drawIamge2(image,percent,isclear){
			if(image.height==0){
				return ;
			}
			percent = percent / 100;
			var drawY = 0;
			if(!isclear){
				//drawY = 28;
			}
			if (canDraw1) {
				if (isHengPing) {
					var drawW = image.width*bili*ratio;
					//var drawH = (image.height-drawY)*bili*ratio;
					var drawH = image.height*bili*ratio;
					var ph = wh*ratio;
					if(isclear){
						//ctx1.clearRect(Math.floor((canvas.width-drawW)*0.5),  Math.floor(ph*0.85-drawH), Math.floor(drawW* percent) , Math.floor(drawH));
					}
					//ctx1.drawImage(image, 0, drawY, Math.floor(image.width*percent), image.height-drawY, Math.floor((canvas.width-drawW)*0.5),  Math.floor(ph*0.85-drawH), Math.floor(drawW* percent) , Math.floor(drawH) );
					ctx1.drawImage(image, 0, drawY, Math.floor(image.width*percent), image.height-3, Math.floor((canvas.width-drawW)*0.5),  Math.floor(ph*0.85-drawH)-20, Math.floor(drawW* percent) , Math.floor(drawH) );
					ctx1.restore();
				} else {
					var xpos = canvas.width / 2 - canvas.height/2;
					var ypos = canvas.height/2-canvas.width/2;
					var drawW = image.width*bili*ratio;
					var imageH =  image.height-drawY;
					var drawH = imageH*bili*ratio;
					var ph = wh*ratio;
					if(isclear){
						//ctx1.clearRect(Math.floor(xpos+(canvas.height-drawW)*0.5) ,  Math.floor(ypos+canvas.width*0.8-drawH), Math.floor(drawW* percent) , Math.floor(drawH));
					}
				   	//ctx1.drawImage(image, 0, drawY, Math.floor(image.width*percent), imageH,Math.floor(xpos+(canvas.height-drawW)*0.5) ,  Math.floor(ypos+canvas.width*0.8-drawH), Math.floor(drawW* percent) , Math.floor(drawH));
					ctx1.drawImage(image, 0, drawY, Math.floor(image.width*percent), imageH,Math.floor(xpos+(canvas.height-drawW)*0.5) ,  Math.floor(ypos+canvas.width*0.8-drawH)+20, Math.floor(drawW* percent) , Math.floor(drawH));
					ctx1.restore();
				}
			}
		}
		function onImageLoad3(image) {
			image.onload  =  function  () {
				egret.runEgret({ renderMode: "canvas", audioType: 0 });
			}
		}
		var canDraw1 = false;
		function removeBg() {
			if (canvas && canvas.parentNode) {
				canvas.parentNode.removeChild(canvas);
			}
		}
		function drawPercent(percent) {
			percent = Math.floor(percent);
			if(percent>=100){
				percent = 100;
			}
			drawIamge2(image2,100,true);
			drawIamge2(image3,percent,false);
		}
		function onLoading() {
			intiCanvas();
				image1.src  =  "<?=CDN_HOST; ?>/img/loginBack.png?v=1";
				image2.src  =  "<?=CDN_HOST; ?>/img/lodingbg.png?v=1";
				image3.src  =  "<?=CDN_HOST; ?>/img/loding.png?v=1";
			onImageLoad1(image1);
			onImageLoad3(image3);
		}

		function setProgress(tips, total) {
			p.updateProcess(tips, total);
		}
		function onpageshow() {
			intiCanvas();
			onLoading();
			window.onorientationchange = drawIamge1;
		}
		function onresize(){
			intiCanvas();
			drawIamge1();
		}
	</script>
</body>

</html>

