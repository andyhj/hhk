<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>扫一扫</title>
        <?php include T('Common/header'); ?>
        <script>
        function scan(){			
		wx.config({
		    debug: false,
                    appId: '<?=$wx_config['appId']?>',
                    timestamp: "<?=$wx_config['timestamp']?>",
                    nonceStr: '<?=$wx_config['nonceStr']?>',
                    signature: '<?=$wx_config['signature']?>',
		    jsApiList: ['scanQRCode']
		});
		
 		wx.scanQRCode({
		    desc: 'scanQRCode desc',
		    needResult: 1, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
		    scanType: ["qrCode","barCode"], // 可以指定扫二维码还是一维码，默认二者都有
		    success: function (res) {				    
			    if(res.errMsg == 'scanQRCode:ok'){
			    	var result = res.resultStr; // 当needResult 为 1 时，扫码返回的结果
			    	var strs= new Array(); //定义一数组
                                    
                                    //alert(strs);
                                    if(result.indexOf(",")!=-1){
                                        strs=result.split(","); //字符分割 
                                        $('#kddh').val(strs[1]);
                                    }else{
                                        $('#kddh').val(result);
                                    }
				}
			}
		});
	}
    </script>
    </head>
    <body class="huibg">
        <div style="height: 100%;">
            <input type="text" value="" id="kddh"><input type="button" value="扫一扫" onclick="scan()">
        </div>
        <div id="jg"></div>
    </body>
</html>