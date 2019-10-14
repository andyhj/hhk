<?php

/**
 * 格式化价格
 * @param unknown $price
 * @return string
 */
function format_price($price) {
    return number_format(round(floatval($price), 2), 2, '.', '');
}

/**
 * 生成指定长度的随机字符串
 * @param unknown $length
 * @return Ambigous <string, number>
 */
function get_rand_str($length, $letters = []) {
    if (!$letters) {
        $letters = [
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
            'q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', 'a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'z', 'x', 'c', 'v', 'b', 'n', 'm',
            'Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', 'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'Z', 'X', 'C', 'V', 'B', 'N', 'M'
        ];
    }
    $rand_str = '';
    $letters_length = count($letters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $rand_str .= $letters[mt_rand(0, $letters_length)];
    }
    return $rand_str;
}
//将下划线命名转换为驼峰式命名
function convertUnderline( $str , $ucfirst = true)
{
    $str = preg_replace('/_([A-Za-z])/e',"strtoupper('$1')",$str);
    return $ucfirst ? ucfirst($str) : $str;
}
/**
* 下划线转驼峰
* 思路:
* step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
* step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
*/
function camelize($uncamelized_words,$separator='_')
{
    $uncamelized_words = $separator. str_replace($separator, " ", strtolower($uncamelized_words));
    return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator );
}
//驼峰命名转下划线命名
function uncamelize($camelCaps,$separator='_')
{
    return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
}
/** 
 * 生成某个范围内的随机时间 
 * @param <type> $begintime  起始时间 格式为 Y-m-d H:i:s 
 * @param <type> $endtime    结束时间 格式为 Y-m-d H:i:s   
 * @param <type> $now         是否是时间戳 格式为 Boolean   
 */  
function randomDate($begintime, $endtime="", $now = false) {
    $begin = strtotime($begintime);  
    $end = $endtime == "" ? time() : strtotime($endtime);
    $timestamp = mt_rand($begin, $end);
    // d($timestamp);
    return $now ?  $timestamp: date("Y-m-d H:i:s", $timestamp);          
}
/**
  +----------------------------------------------------------
 * 生成随机字符串
  +----------------------------------------------------------
 * @param int       $length  要生成的随机字符串长度
 * @param string    $type    随机码类型：0，数字+大写字母；1，数字；2，小写字母；3，大写字母；4，特殊字符；-1，数字+大小写字母+特殊字符
  +----------------------------------------------------------
 * @return string
  +----------------------------------------------------------
 */
function randCode($length = 5, $type = 0) {
    $arr = array(1 => "0123456789", 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|");
    if ($type == 0) {
        array_pop($arr);
        $string = implode("", $arr);
    } else if ($type == "-1") {
        $string = implode("", $arr);
    } else {
        $string = $arr[$type];
    }
    $count = strlen($string) - 1;
    for ($i = 0; $i < $length; $i++) {
        $str[$i] = $string[rand(0, $count)];
        $code .= $str[$i];
    }
    return $code;
}
//把电话号码中间四位替换成*号
function hidtel($phone) {
    $IsWhat = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i', $phone); //固定电话
    if ($IsWhat == 1) {
        return preg_replace_callback('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i', '$1****$2', $phone);
    } else {
        return preg_replace_callback('/(1[358]{1}[0-9])[0-9]{4}([0-9]{4})/i', '$1****$2', $phone);
    }
}

/*
 * 以下检测设备方法
 */

function is_mobile() {
    if (is_app()) {
        return true;
    }
    return(get_device() == 'mobile');
}

function is_tablet() {
    return(get_device() == 'tablet');
}

function is_TV() {
    return(get_device() == 'tv');
}

function is_desktop() {
    return(get_device() == 'desktop');
}

function is_weixin() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }
    return false;
}

function is_app() {
    if (isset($_SERVER['HTTP_CLIENT_DEVICE']) && strpos($_SERVER['HTTP_CLIENT_DEVICE'], 'BeautyPlus') !== false) {
        return true;
    }
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'BeautyPlus') !== false) {
        return true;
    }
    return false;
}

//获取app版本号
function get_app_version() {
    if (!is_app()) {
        return 0;
    }
    if (isset($_SERVER['HTTP_CLIENT_DEVICE'])) {
        $str_version = $_SERVER['HTTP_CLIENT_DEVICE'];
    } else {
        $arr_agent = explode(' ', $_SERVER['HTTP_USER_AGENT']);
        $str_version = array_pop($arr_agent);
    }
    $arr_version = explode('/', $str_version);
    if ($arr_version[0] != 'BeautyPlus') {
        return 0;
    }
    return $arr_version[1];
}

//判断是否可用版本
function is_enable_version($version = '', $only_device = '') {
    if (!$version) {
        return true;
    }
    if ($only_device && in_array($only_device, ['android', 'ios'])) {
        $str_func = 'is_' . $only_device;
        if (!$str_func()) {
            return true;
        }
    }
    $str_this_version = get_app_version();
    $arr_this_version = explode('.', $str_this_version);
    $arr_version = explode('.', $version);
    $bool_return = false;
    if (!$arr_this_version) {
        return false;
    }
    if (intval($arr_this_version[0]) > intval($arr_version[0])) {
        $bool_return = true;
    } else if (intval($arr_this_version[0]) == intval($arr_version[0])) {
        if (intval($arr_this_version[1]) > intval($arr_version[1])) {
            $bool_return = true;
        } else if (intval($arr_this_version[1]) == intval($arr_version[1])) {
            if (intval($arr_this_version[2]) < intval($arr_version[2])) {
                $bool_return = false;
            } else {
                $bool_return = true;
            }
        } else {
            $bool_return = false;
        }
    } else {
        $bool_return = false;
    }
    return $bool_return;
}

//安卓
function is_android() {
    if (preg_match("/android/i", $_SERVER['HTTP_USER_AGENT'])) {
        return true;
    }
    return false;
}

//ios
function is_ios() {
    if (preg_match("/(iPod|iPad|iPhone)/", $_SERVER['HTTP_USER_AGENT'])) {
        return true;
    }
    return false;
}

function get_device() {
    if (is_app()) {
        return 'app';
    }
    if (is_weixin()) {
        return 'weixin';
    }
    $catergorize_tablets_as_desktops = FALSE;  //If TRUE, tablets will be categorized as desktops
    $catergorize_tvs_as_desktops = FALSE;  //If TRUE, smartTVs will be categorized as desktops
    // Category name - In the event the script is already using 'category' in the session variables, you could easily change it by only needing to change this value.
    $category = 'category';

    //Set User Agent = $ua
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $ua = $_SERVER['HTTP_USER_AGENT'];
    } else {
        $ua = '';
    }

    // Check to see if device type is set in query string
    if (isset($_GET["view"])) {
        $view = $_GET["view"];
        // If view=desktop set in your query string
        if ($view == "desktop") {
            $device = "desktop";
        } else if ($view == "tablet") { // If view=tablet set in your query string
            $device = "tablet";
        } else if ($view == "tv") { // If view=tablet set in your query string
            $device = "tv";
        } else if ($view == "mobile") { // If view=mobile set in your query string
            $device = "mobile";
        }
    }// End Query String check
    // If session not yet set, check user agents
    if (!isset($device)) {
        // Check if user agent is a smart TV - http://goo.gl/FocDk
        if ((preg_match('/GoogleTV|SmartTV|Internet.TV|NetCast|NETTV|AppleTV|boxee|Kylo|Roku|DLNADOC|CE\-HTML/i', $ua))) {
            $device = "tv";
        } else if ((preg_match('/Xbox|PLAYSTATION.3|Wii/i', $ua))) { // Check if user agent is a TV Based Gaming Console
            $device = "tv";
        } else if ((preg_match('/iP(a|ro)d/i', $ua)) || (preg_match('/tablet/i', $ua)) && (!preg_match('/RX-34/i', $ua)) || (preg_match('/FOLIO/i', $ua))) { // Check if user agent is a Tablet
            $device = "tablet";
        } else if ((preg_match('/Linux/i', $ua)) && (preg_match('/Android/i', $ua)) && (!preg_match('/Fennec|mobi|HTC.Magic|HTCX06HT|Nexus.One|SC-02B|fone.945/i', $ua))) { // Check if user agent is an Android Tablet
            $device = "tablet";
        } else if ((preg_match('/Kindle/i', $ua)) || (preg_match('/Mac.OS/i', $ua)) && (preg_match('/Silk/i', $ua))) { // Check if user agent is a Kindle or Kindle Fire
            $device = "tablet";
        } else if ((preg_match('/GT-P10|SC-01C|SHW-M180S|SGH-T849|SCH-I800|SHW-M180L|SPH-P100|SGH-I987|zt180|HTC(.Flyer|\_Flyer)|Sprint.ATP51|ViewPad7|pandigital(sprnova|nova)|Ideos.S7|Dell.Streak.7|Advent.Vega|A101IT|A70BHT|MID7015|Next2|nook/i', $ua)) || (preg_match('/MB511/i', $ua)) && (preg_match('/RUTEM/i', $ua))) { // Check if user agent is a pre Android 3.0 Tablet
            $device = "tablet";
        } else if ((preg_match('/BOLT|Fennec|Iris|Maemo|Minimo|Mobi|mowser|NetFront|Novarra|Prism|RX-34|Skyfire|Tear|XV6875|XV6975|Google.Wireless.Transcoder/i', $ua))) { // Check if user agent is unique Mobile User Agent
            $device = "mobile";
        } else if ((preg_match('/Opera/i', $ua)) && (preg_match('/Windows.NT.5/i', $ua)) && (preg_match('/HTC|Xda|Mini|Vario|SAMSUNG\-GT\-i8000|SAMSUNG\-SGH\-i9/i', $ua))) { // Check if user agent is an odd Opera User Agent - http://goo.gl/nK90K
            $device = "mobile";
        } else if ((preg_match('/Windows.(NT|XP|ME|9)/', $ua)) && (!preg_match('/Phone/i', $ua)) || (preg_match('/Win(9|.9|NT)/i', $ua))) { // Check if user agent is Windows Desktop
            $device = "desktop";
        } else if ((preg_match('/Macintosh|PowerPC/i', $ua)) && (!preg_match('/Silk/i', $ua))) { // Check if agent is Mac Desktop
            $device = "desktop";
        } else if ((preg_match('/Linux/i', $ua)) && (preg_match('/X11/i', $ua))) { // Check if user agent is a Linux Desktop
            $device = "desktop";
        } else if ((preg_match('/Solaris|SunOS|BSD/i', $ua))) { // Check if user agent is a Solaris, SunOS, BSD Desktop
            $device = "desktop";
        } else if ((preg_match('/Bot|Crawler|Spider|Yahoo|ia_archiver|Covario-IDS|findlinks|DataparkSearch|larbin|Mediapartners-Google|NG-Search|Snappy|Teoma|Jeeves|TinEye/i', $ua)) && (!preg_match('/Mobile/i', $ua))) { // Check if user agent is a Desktop BOT/Crawler/Spider
            $device = "desktop";
        } else { // Otherwise assume it is a Mobile Device
            $device = "mobile";
        }
    }// End if session not set
    // Categorize Tablets as desktops
    if ($catergorize_tablets_as_desktops && $device == "tablet") {
        $device = "desktop";
    }

    // Categorize TVs as desktops
    if ($catergorize_tvs_as_desktops && $device == "tv") {
        $device = "desktop";
    }

    return $device;
}

//获取uuid
function create_guid($hyphen = '') {
    mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
    $charid = md5(uniqid(rand(), true));
    $uuid = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
    return $uuid;
}

//自适应http、https
function auto_http_protocol($url) {
    if (!$url) {
        return false;
    }
    list($protocol, $turl) = explode('://', $url);
    $protocol = "//";
    return $protocol . $turl;
}

//判断当前是http 、 https
function is_https() {
    $bool_flag = false;
    if (!isset($_SERVER['HTTPS'])) {
        $bool_flag = false;
    }
    if ($_SERVER['HTTPS'] === true) {  //Apache  
        $bool_flag = true;
    } else if (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
        $bool_flag = true;
    } else if ($_SERVER['SERVER_PORT'] == 443) { //其他  
        $bool_flag = true;
    }
    return $bool_flag;
}

/**
 * 获取剩余时间的天，时，分，秒
 * @param unknown $left_time
 */
function formatLeftTime($left_time) {
    $data = [];
    $data['d'] = floor($left_time / 86400); //剩余天
    $left_time -= 86400 * $data['d'];

    $data['h'] = floor($left_time / 3600); //剩余时
    $left_time -= 3600 * $data['h'];

    $data['i'] = floor($left_time / 60); //剩余分
    $left_time -= 60 * $data['i'];

    $data['s'] = $left_time; //剩余秒
    //将数字的格式化
    $data['d'] = (strlen($data['d']) == 1) ? '0' . (String) $data['d'] : (String) $data['d'];
    $data['h'] = (strlen($data['h']) == 1) ? '0' . (String) $data['h'] : (String) $data['h'];
    $data['i'] = (strlen($data['i']) == 1) ? '0' . (String) $data['i'] : (String) $data['i'];
    $data['s'] = (strlen($data['s']) == 1) ? '0' . (String) $data['s'] : (String) $data['s'];

    return $data;
}
function formatDate($date1,$date2){
    $data = [];
    $data['d']=floor(($date1-$date2)/86400);

    $data['h']=floor((($date1-$date2)%86400/3600));

    $data['i']=floor((($date1-$date2)%86400/60)-($data['h']*60));

    $data['s']=floor(($date1-$date2)%86400%60);
    return $data;
}

//根据字符长度截取字符串
function csubstrs($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
    if (empty($str))
        return false;
    $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    /* if(function_exists("mb_substr")){
      $str=mb_substr($str, $start, $length, $charset);
      if(count($match[0])>$length) return $str.'...';
      else return $str;
      } */
    //$slice = join("",array_slice($match[0], $start, $length));
    $tooLong = false;
    $i = 0;
    $j = 0;
    $temp = '';
    $str = $match[0] ? $match[0] : $match;
    $str_count = count($str);
    do {
        if ($str_count <= $j)
            break;
        if (preg_match("/[\x80-\xff]/", $str[$j]))
            $i += 2;
        else
            $i++;
        if ($i > $length) {
            $tooLong = true;
            break;
        }
        $temp .= $str[$j++];
    } while ($i <= $length);
    if ($suffix && $tooLong)
        return $temp . '...';
    return $temp;
}

function _strlen($str, $charset = "utf-8") {
    if (empty($str))
        return false;
    $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $i = 0;
    $str = $match[0] ? $match[0] : $match;
    $str_count = count($str);
    foreach ($str as $v) {
        if (preg_match("/[\x80-\xff]/", $v)) {
            $i += 2;
        } else {
            $i++;
        }
    }
    return $i;
}

function delTrim($str) {//删除空格
    $qian = array(" ", "　", "\t", "\n", "\r");
    $hou = array("", "", "", "", "");
    return str_replace($qian, $hou, $str);
}

function is_post() { //判断是否post请求
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        return true;
    }
    return false;
}
/**
* 发送数据
* @param String $url     请求的地址
* @param Array  $header  自定义的header数据
* @param Array  $content POST的数据
* @return String
*/
function tocurl($url, $header,$content){
    $ch = curl_init();
    if(substr($url,0,5)=='https'){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    $response = curl_exec($ch);
    if($error=curl_error($ch)){
        die($error);
    }
    curl_close($ch);
  //var_dump($response);
    return $response;
}
/**
 * 获取客户端IP地址
 * @param integer $type
 * @return mixed
 */
function getIP() {
    static $realip = NULL;

    if ($realip !== NULL) {
        return $realip;
    }
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { //但如果客户端是使用代理服务器来访问，那取到的就是代理服务器的 IP 地址，而不是真正的客户端 IP 地址。要想透过代理服务器取得客户端的真实 IP 地址，就要使用 $_SERVER["HTTP_X_FORWARDED_FOR"] 来读取。
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr AS $ip) {
                $ip = trim($ip);
                if ($ip != 'unknown') {
                    $realip = $ip;
                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {//HTTP_CLIENT_IP 是代理服务器发送的HTTP头。如果是"超级匿名代理"，则返回none值。同样，REMOTE_ADDR也会被替换为这个代理服务器的IP。
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER['REMOTE_ADDR'])) { //正在浏览当前页面用户的 IP 地址
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = '0.0.0.0';
            }
        }
    } else {
        //getenv环境变量的值
        if (getenv('HTTP_X_FORWARDED_FOR')) {//但如果客户端是使用代理服务器来访问，那取到的就是代理服务器的 IP 地址，而不是真正的客户端 IP 地址。要想透过代理服务器取得客户端的真实 IP 地址
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) { //获取客户端IP
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');  //正在浏览当前页面用户的 IP 地址
        }
    }
    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
    return $realip;
}

/**
 * 邮件发送函数
 */
function sendMail($email, $subject, $content,$title="", $attachment = null) {
    Vendor('PHPMailer.PHPMailerAutoload');
    vendor('PHPMailer.class#PHPMailer');
    vendor('PHPMailer.class#SMTP');
    $mail = new PHPMailer(); //实例化
    $mail->IsSMTP(); // 启用SMTP
    $mail->Host = C('MAIL_HOST'); //smtp服务器的名称（这里以QQ邮箱为例）
    $mail->Port = C('MAIL_PORT'); //端口号
    $mail->SMTPAuth = C('MAIL_SMTPAUTH'); //启用smtp认证
    $mail->Username = C('MAIL_USERNAME'); //你的邮箱名
    $mail->Password = C('MAIL_PASSWORD'); //邮箱密码
    $mail->From = C('MAIL_FROM'); //发件人地址（也就是你的邮箱地址）
    $mail->FromName = C('MAIL_FROMNAME'); //发件人姓名
    $mail->AddAddress($email, $title);
    $mail->WordWrap = 50; //设置每行字符长度
    $mail->IsHTML(C('MAIL_ISHTML')); // 是否HTML格式邮件
    $mail->CharSet = C('MAIL_CHARSET'); //设置邮件编码
    $mail->Subject = $subject; //邮件主题
    $mail->Body = $content; //邮件内容
    $mail->AltBody = "这是一个纯文本的身体在非营利的HTML电子邮件客户端"; //邮件正文不支持HTML的备用显示
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            if (is_array($file)) {
                is_file($file['path']) && $mail->AddAttachment($file['path'], $file['name']);
            } else {
                is_file($file) && $mail->AddAttachment($file);
            }
        }
    } else {
        is_file($attachment) && $mail->AddAttachment($attachment);
    }
    return $mail->Send();
}

/**
 * php curl 请求链接
 * 当$post_data为空时使用GET方式发送
 * @param unknown $url
 * @param string $post_data
 * @return mixed
 */
function curlSend($url, $post_data = "") {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($post_data != "") {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
/**
 * 发送验证码
 *
 * @param [type] $phone
 * @return void
 */
function send_sms($phone,$length=6)
{
    $config = C("SMS_CONFIG");
    $productid = $config['productid'];
    $letters = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $code = get_rand_str($length, $letters);
    $tkey = date('YmdHis');//当前时间
    $content = "【会还款】您好,您的验证码是{$code}。";
    $post_data = array(
        'username' => $config['username'],
        'tkey' => $tkey,
        'password' => md5(md5($config['password']) . $tkey),//加密后密码
        'mobile' => $phone,
        'content' => $content,
        'productid' => $productid,
        'xh' => ''
    );
    $result = send_sms_post($config['url'], $post_data);
    //如果返回的结果是1,xxxxxxxx代表发送短信成功
    $rs = preg_match("/1,\d+/i", $result);
    return $rs ? $code : 0;
}
function send_sms_post($url, $post_data)
{

    // $post_data   = iconv("UTF-8", "UTF-8", $post_data);
    $postdata = http_build_query($post_data);

    $options = array(
        'http' => array(
            'method' => 'POST',//or GET
            'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $postdata,
            'timeout' => 15 * 60 // 超时时间（单位:s）
        )
    );
    $context = stream_context_create($options);

    $result = file_get_contents($url, false, $context);
    return $result;
}
/**
 * 调用接口获取 $ACCESS_TOKEN
 * 微信缓存 7200 秒，这里使用thinkphp的缓存方法
 * @param unknown $APP_ID
 * @param unknown $APP_SECRET
 * @return Ambigous <mixed, Thinkmixed, object>
 */
function get_accesstoken($APP_ID, $APP_SECRET) {
    // $ACCESS_TOKEN = S($APP_ID);
    // if ($ACCESS_TOKEN == false) {
    //     $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $APP_ID . "&secret=" . $APP_SECRET;
    //     $json = curlSend($url);

    //     $data = json_decode($json, true);

    //     S($APP_ID, $data[access_token], 7000);
    //     $ACCESS_TOKEN = S($APP_ID);
    // }

    $url = 'http://hsq.91hefu.com/common/get_access_tokens';
    $access_token = file_get_contents($url);
    return $access_token;
}

/**
 * 微信网页JSSDK  调用接口获取 $jsapi_ticket
 * 微信缓存 7200 秒，这里使用thinkphp的缓存方法
 * @param unknown $ACCESS_TOKEN
 * @return Ambigous <mixed, Thinkmixed, object>
 */
function get_jsapi_ticket($ACCESS_TOKEN) {
    $jsapi_ticket = S($ACCESS_TOKEN);
    //var_dump(S($ACCESS_TOKEN));exit;
    if ($jsapi_ticket == false) {
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $ACCESS_TOKEN . "&type=jsapi";
        $json = curlSend($url);
        $data = json_decode($json, true);

        $aaa = S($ACCESS_TOKEN, $data[ticket], 7000);
        $jsapi_ticket = S($ACCESS_TOKEN);
    }

    return $jsapi_ticket;
}

/**
 * 微信网页JSSDK 获取签名字符串
 * 所有参数名均为小写字符
 * @param unknown $nonceStr 随机字符串
 * @param unknown $timestamp 时间戳
 * @param unknown $jsapi_ticket
 * @param unknown $url 调用JS接口页面的完整URL，不包含#及其后面部分
 */
function get_js_sdk($APP_ID, $APP_SECRET) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== off || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    $argu = array();
    $argu['appId'] = $APP_ID;
    $argu['url'] = $url;
    $argu['nonceStr'] = get_rand_str(16);
    $argu['timestamp'] = time();

    $ACCESS_TOKEN = get_accesstoken($APP_ID, $APP_SECRET);
    $argu['jsapi_ticket'] = get_jsapi_ticket($ACCESS_TOKEN);

    $string = "jsapi_ticket=" . $argu[jsapi_ticket] . "&noncestr=" . $argu[nonceStr] . "&timestamp=" . $argu[timestamp] . "&url=" . $argu[url];
    $argu['signature'] = sha1(trim($string));
    return $argu;
}
function add_log($name,$path,$str,$mode=0777){
    if(!$name||!$path||!$str){
        return false;
    }
    $paths = APP_ROOT."Public/logs/".$path."/".date("Y/m/d");
    $str = date("YmdHis")."：".$str.PHP_EOL;
    if(is_dir($paths)){
        chmod($paths, 0777);
        file_put_contents($paths.'/'.$name, $str,FILE_APPEND);
        chmod($paths.'/'.$name, 0777);
    }else{
        if(mkdir($paths, $mode, true)) {
            chmod($paths, 0777);
            file_put_contents($paths.'/'.$name, $str,FILE_APPEND);
            chmod($paths.'/'.$name, 0777);
        }
    }
}
function add_image($name,$path,$str,$mode=0777){
    if(!$name||!$path||!$str){
        return false;
    }
    $paths = APP_ROOT."Public/file/".$path."/";
    if(is_dir($paths)){
        chmod($paths, 0777);
        file_put_contents($paths.'/'.$name, $str.PHP_EOL,FILE_APPEND);
        chmod($paths.'/'.$name, 0777);
    }else{
        if(mkdir($paths, $mode, true)) {
            chmod($paths, 0777);
            file_put_contents($paths.'/'.$name, $str.PHP_EOL,FILE_APPEND);
            chmod($paths.'/'.$name, 0777);
        }
    }
}
function curl_get($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER,0);
    curl_setopt($ch, CURLOPT_NOBODY,0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    $package = curl_exec($ch);
    $httpinfo = curl_getinfo($ch);
    curl_close($ch);
    return array_merge(array('body'=>$package),array('header'=>$httpinfo));
}

/**
 * 验证AppStore内付
 * @param  string $receipt_data 付款后凭证
 * @return array                验证是否成功
 */
function validate_apple_pay($receipt_data, $sandbox=0){
    /**
     * 21000 App Store不能读取你提供的JSON对象
     * 21002 receipt-data域的数据有问题
     * 21003 receipt无法通过验证
     * 21004 提供的shared secret不匹配你账号中的shared secret
     * 21005 receipt服务器当前不可用
     * 21006 receipt合法，但是订阅已过期。服务器接收到这个状态码时，receipt数据仍然会解码并一起发送
     * 21007 receipt是Sandbox receipt，但却发送至生产系统的验证服务
     * 21008 receipt是生产receipt，但却发送至Sandbox环境的验证服务
     */
    function acurl($receipt_data, $sandbox=0){
        //小票信息
        $POSTFIELDS = array("receipt-data" => $receipt_data);
        $POSTFIELDS = json_encode($POSTFIELDS);

        //正式购买地址 沙盒购买地址
        $url_buy     = "https://buy.itunes.apple.com/verifyReceipt";
        $url_sandbox = "https://sandbox.itunes.apple.com/verifyReceipt";
        $url = $sandbox ? $url_sandbox : $url_buy;
        
        //简单的curl
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    // 验证参数
    if (strlen($receipt_data)<20){
        $result=array(
            'status'=>false,
            'message'=>'非法参数'
            );
        return $result;
    }
    // 请求验证
    $html = acurl($receipt_data, $sandbox);
    $data = json_decode($html,true);
    add_log("apple_pay.log", "pay", "apple验证返回数据：". var_export($data,true));
//    // 如果是沙盒数据 则验证沙盒模式
//    if($data['status']=='21007'){
//        // 请求验证
//        $html = acurl($receipt_data, 1);
//        $data = json_decode($html,true);
//        $data['sandbox'] = '1';
//    }

    if (isset($_GET['debug'])) {
        exit(json_encode($data));
    }

    // 判断是否购买成功
    if(intval($data['status'])===0){
        $result=array(
            'status'=>true,
            'message'=>'购买成功',
            'data'=>$data['receipt']
            );
    }else{
        $result=array(
            'status'=>false,
            'message'=>'购买失败 status:'.$data['status']
            );
    }
    return $result;
}

/**
 * TODO 基础分页的相同代码封装，使前台的代码更少
 * @param $count 要分页的总记录数
 * @param int $pagesize 每页查询条数
 * @return \Think\Page
 */
function getpage($count, $pagesize = 10) {
    $p = new \Think\Page($count, $pagesize);
    $p->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录 第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
    $p->setConfig('prev', '上一页');
    $p->setConfig('next', '下一页');
    $p->setConfig('last', '末页');
    $p->setConfig('first', '首页');
    $p->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
    $p->lastSuffix = false;//最后一页不显示为总页数
    return $p;
}

/**
  +----------------------------------------------------------
 * 功能：检测一个字符串是否是邮件地址格式
  +----------------------------------------------------------
 * @param string $value    待检测字符串
  +----------------------------------------------------------
 * @return boolean
  +----------------------------------------------------------
 */
function is_email($value) {
    return preg_match("/^[0-9a-zA-Z]+(?:[\_\.\-][a-z0-9\-]+)*@[a-zA-Z0-9]+(?:[-.][a-zA-Z0-9]+)*\.[a-zA-Z]+$/i", $value);
}
/**
  +----------------------------------------------------------
 * 加密密码
  +----------------------------------------------------------
 * @param string    $data   待加密字符串
  +----------------------------------------------------------
 * @return string 返回加密后的字符串
 */
function encrypt($data) {
    return md5(C("AUTH_CODE") . md5($data));
}
/**
  +-----------------------------------------------------------------------------------------
 * 删除目录及目录下所有文件或删除指定文件
  +-----------------------------------------------------------------------------------------
 * @param str $path   待删除目录路径
 * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
  +-----------------------------------------------------------------------------------------
 * @return bool 返回删除状态
  +-----------------------------------------------------------------------------------------
 */
function delDirAndFile($path, $delDir = FALSE) {
    $handle = opendir($path);
    if ($handle) {
        while (false !== ( $item = readdir($handle) )) {
            if ($item != "." && $item != "..")
                is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
        }
        closedir($handle);
        if ($delDir)
            return rmdir($path);
    }else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return FALSE;
        }
    }
}
/**
  +----------------------------------------------------------
 * 将一个字符串部分字符用*替代隐藏
  +----------------------------------------------------------
 * @param string    $string   待转换的字符串
 * @param int       $bengin   起始位置，从0开始计数，当$type=4时，表示左侧保留长度
 * @param int       $len      需要转换成*的字符个数，当$type=4时，表示右侧保留长度
 * @param int       $type     转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串
 * @param string    $glue     分割符
  +----------------------------------------------------------
 * @return string   处理后的字符串
  +----------------------------------------------------------
 */
function hideStr($string, $bengin = 0, $len = 4, $type = 0, $glue = "@") {
    if (empty($string))
        return false;
    $array = array();
    if ($type == 0 || $type == 1 || $type == 4) {
        $strlen = $length = mb_strlen($string);
        while ($strlen) {
            $array[] = mb_substr($string, 0, 1, "utf8");
            $string = mb_substr($string, 1, $strlen, "utf8");
            $strlen = mb_strlen($string);
        }
    }
    switch ($type) {
        case 1:
            $array = array_reverse($array);
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i]))
                    $array[$i] = "*";
            }
            $string = implode("", array_reverse($array));
            break;
        case 2:
            $array = explode($glue, $string);
            $array[0] = hideStr($array[0], $bengin, $len, 1);
            $string = implode($glue, $array);
            break;
        case 3:
            $array = explode($glue, $string);
            $array[1] = hideStr($array[1], $bengin, $len, 0);
            $string = implode($glue, $array);
            break;
        case 4:
            $left = $bengin;
            $right = $len;
            $tem = array();
            for ($i = 0; $i < ($length - $right); $i++) {
                if (isset($array[$i]))
                    $tem[] = $i >= $left ? "*" : $array[$i];
            }
            $array = array_chunk(array_reverse($array), $right);
            $array = array_reverse($array[0]);
            for ($i = 0; $i < $right; $i++) {
                $tem[] = $array[$i];
            }
            $string = implode("", $tem);
            break;
        default:
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i]))
                    $array[$i] = "*";
            }
            $string = implode("", $array);
            break;
    }
    return $string;
}/**
 * 生成签名
 * @param  array $Obj 待签名的数据
 * @return string
 */
function getSign($Obj,$key) {
    if (!is_array($Obj)||!$key) {
        $Obj = (array) $Obj;
    }
    foreach ($Obj as $k => $v) {
        $Parameters [$k] = $v;
    }
    // 签名步骤一：按字典序排序参数
    ksort($Parameters);
    $String = formatBizQueryParaMap($Parameters, false);
    //echo '【string1】'.$String.'</br>';
    // 签名步骤二：在string后加入KEY
    $String = $String . "&key=" . $key;
    //echo "【string2】".$String."</br>";die;
    // 签名步骤三：MD5加密

    $String = md5($String);
    //echo "【string3】 ".$String."</br>";
    // 签名步骤四：所有字符转为大写
    $result_ = strtoupper($String);
    //echo "【result】 ".$result_."</br>";

    return $result_;
}
/**
 * 作用：格式化参数，签名过程需要使用
 */
function formatBizQueryParaMap($paraMap, $urlencode) {
    $buff = "";
    if (!is_array($paraMap)) {
        $paraMap = (array) $paraMap;
    }
    ksort($paraMap);
    foreach ($paraMap as $k => $v) {
        if ($urlencode) {
            $v = urlencode($v);
        }
        // $buff .= strtolower($k) . "=" . $v . "&";
        $buff .= $k . "=" . $v . "&";
    }
    $reqPar = '';
    if (strlen($buff) > 0) {
        $reqPar = substr($buff, 0, strlen($buff) - 1);
    }
    return $reqPar;
}

/**
 * 检查签名是否正确
 * @param  array $data 待检查的数据
 * @return boolean
 */
function checkSign($data,$key) {
    $sign = $data['sign'];
    unset($data['sign']);
    return $sign == getSign($data,$key);
}
/**
 * Undocumented function
 *把一个数随机分成n份
 * @param [type] $total  总数
 * @param [type] $num   份数
 * @return void
 */
function roundResolve($total,$num){
    $area = 50; //各份数间允许的最大差值
    
    $average = round($total/$num*100);
    $sum = 0;
    $result = array_fill( 1, $num, 0 );
    
    for( $i = 1; $i < $num; $i++ ){
        //根据已产生的随机数情况，调整新随机数范围，以保证各份间差值在指定范围内
        if( $sum > 0 ){
            $max = 0;
            $min = 0 - round($area/2*100);
        }elseif( $sum < 0 ){
            $min = 0;
            $max = round($area/2*100);
        }else{
            $max = round($area/2*100);
            $min = 0 - round($area/2*100);
        }
        
        //产生各份的份额
        $random = mt_rand( $min, $max );
        $sum += $random;
        $result[$i] = round(($average + $random)/100,2)*100/100;
    }
    
    //最后一份的份额由前面的结果决定，以保证各份的总和为指定值
    $result[$num] = round(($average - $sum)/100,2)*100/100;
    
    $result_sum = array_sum( $result );
    $balance = $total-$result_sum;
    $result_max = max($result);
    $result_min = min($result);
    if($balance>0){
        $subscript = array_search($result_min,$result);
        $result[$subscript]=round(($result[$subscript]+$balance),2)*100/100;
    }
    if($balance<0){
        $subscript = array_search($result_max,$result);
        $result[$subscript]=round(($result[$subscript]-$balance),2)*100/100;
    }
    return $result;
}