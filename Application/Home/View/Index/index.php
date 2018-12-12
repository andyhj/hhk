<!DOCTYPE HTML>
<html>

<head>
    <meta charset="utf-8">
    <title>方言手游</title>
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
            margin: 0;
            height: 100%;
        }
    </style>
</head>

<body>
    <div style="margin: auto;width: 100%;height: 100%;" class="egret-player"
         data-entry-class="Main"
         data-orientation="landscape"
         data-scale-mode="showAll"
         data-frame-rate="30"
         data-content-width="1136"
         data-content-height="640"
         data-multi-fingered="2"
         data-show-fps="false" data-show-log="true"
         data-show-fps-style="x:0,y:0,size:12,textColor:0xffffff,bgAlpha:0.9">
    </div>
<script src="https://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
<script>
    var loadScript = function (list, callback) {
        var loaded = 0;
        var loadNext = function () {
            loadSingleScript(list[loaded], function () {
                loaded++;
                if (loaded >= list.length) {
                    callback();
                }
                else {
                    loadNext();
                }
            })
        };
        loadNext();
    };

    var loadSingleScript = function (src, callback) {
        
        var s = document.createElement('script');
        s.async = false;
        s.src = '<?=CDN_HOST; ?>/'+src;
        s.addEventListener('load', function () {
            s.parentNode.removeChild(s);
            s.removeEventListener('load', arguments.callee, false);
            callback();
        }, false);
        document.body.appendChild(s);
    };

    var xhr = new XMLHttpRequest();
    xhr.open('GET', '<?=CDN_HOST; ?>/manifest.json?v=' + Math.random(), true);
    xhr.addEventListener("load", function () {
        var manifest = JSON.parse(xhr.response);
        var list = manifest.initial.concat(manifest.game);
        loadScript(list, function () {
            /**
             * {
             * "renderMode":, //Engine rendering mode, "canvas" or "webgl"
             * "audioType": 0 //Use the audio type, 0: default, 2: web audio, 3: audio
             * "antialias": //Whether the anti-aliasing is enabled in WebGL mode, true: on, false: off, defaults to false
             * "calculateCanvasScaleFactor": //a function return canvas scale factor
             * }
             **/
            egret.runEgret({ renderMode: "webgl", audioType: 0, calculateCanvasScaleFactor:function(context) {
                var backingStore = context.backingStorePixelRatio ||
                    context.webkitBackingStorePixelRatio ||
                    context.mozBackingStorePixelRatio ||
                    context.msBackingStorePixelRatio ||
                    context.oBackingStorePixelRatio ||
                    context.backingStorePixelRatio || 1;
                return (window.devicePixelRatio || 1) / backingStore;
            }});
        });
    });
    xhr.send(null);

    //游戏获取参数
    window.getgameparams = function () {
        //alert(srvresobj);
        var paramobj = new Object();
        //paramobj.uid = "0";
        paramobj.uid = "{$game_uid}";
        paramobj.sid = "{$auth_key}";
        //paramobj.wwapiUrl = "http://192.168.1.16/happyapi/restsrv.php";
        paramobj.wwapiUrl = "<?=API_HOST; ?>/restsrv.php";
        // paramobj.wwapiUrl = "http://happyapi.wdougame.com/restsrv.php";
        //paramobj.cdnUrl = "http://192.168.1.16/happyqp/";
        paramobj.cdnUrl = "<?=CDN_HOST; ?>/";
        paramobj.avatarurl = "";
        paramobj.version = "{$version}";
        paramobj.pf = 1;
        paramobj.releaseType = 2;
        return paramobj;
    }
    window.close_window = function () {
        wx.closeWindow();
    }
    //微信登陆
    window.wxlogin = function () {
        location.href = "{$wxlogin_url}";
    }
    //退出登录
    window.logout = function () {
        location.href = "{$logout}";
    }
    window.refresh = function () {
        //location.reload();
    }
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
                                strs=result.split(","); //字符分割 
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
    window.removeBg = function () {
        if (window['closeLoadingView']) {
            window['closeLoadingView']();
        }
    }
    window.setProgress = function (tips, total) {

    }
</script>
</body>

</html>
