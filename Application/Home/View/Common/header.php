<script src="/src/js/jquery.js"></script>
<!--<script src="/src/js/bootstrap.js"></script>-->
<script src="/src/js/date.js"></script>
<!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous"><!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<!--[if IE]>
<script src="js/html5.js"></script>
<![endif]-->
<script src="https://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
<style>
    .nos{
        width: 50%;
        margin: 25%;
    }
    .nos img{
        width: 35%;
        margin-top: 25%;
    }
</style>
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