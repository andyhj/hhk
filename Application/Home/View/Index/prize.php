<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <title>中奖啦</title>
        <script>
            (function (doc, win) {
                var docEl = doc.documentElement, resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize', recalc = function () {
                    var clientWidth = docEl.clientWidth;
                    if (!clientWidth)
                        return;
                    if (clientWidth >= 640) {
                        docEl.style.fontSize = '100px';
                    } else {
                        docEl.style.fontSize = 100 * (clientWidth / 640) + 'px';
                    }
                };
                if (!doc.addEventListener)
                    return;
                win.addEventListener(resizeEvt, recalc, false);
                doc.addEventListener('DOMContentLoaded', recalc, false);
            })(document, window)
        </script>
        <style type="text/css" media="screen">
            .ztmc{
                top: 5rem;
                font-size: 0.35rem;
                position: absolute;
                width: 100%;
                z-index: 50;
                text-align: center;
                margin-left: -8px;
            }
        </style>
    </head>
    <body>
        <div class="ztmc"><?=$num ?> 积分</div>
        <img src="/src/img/zj.png" alt=""  style="width: 100%" id="zj">
    </body>
</html>