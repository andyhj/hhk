<?php
header("Content-type:text/html;charset='UTF-8");
define("filePath", APP_ROOT . 'cert/helipay/'); //.pfx和.cer文件放置的地址
define("pfxFileName", "hhq.pfx"); //.pfx文件名
define("password", "hsq105"); //.pfx文件的密码
define("cerFileName", "helipay.cer"); //.cer文件名

/* 实现.pfx文件转为.pem文件 */
//$file = filePath . pfxFileName;
//$results = [];
//$worked = openssl_pkcs12_read(file_get_contents($file), $results, password);
//$certificateCApem = $file . '.pem';
//@file_put_contents($certificateCApem, $results);
//
///* 实现.cer文件转为.pem文件 */
//$certificateCAcer = filePath . cerFileName;
//$certificateCAcerContent = file_get_contents($certificateCAcer);
//$certificateCApem = filePath . cerFileName . '.pem';
//file_put_contents($certificateCApem, $certificateCAcerContent);

class Rsa {
    /* 实现加签功能 */

    function genSign($signFormString) {
        $priKey = file_get_contents(filePath . pfxFileName . '.pem');
        $res = openssl_get_privatekey($priKey);
        openssl_sign($signFormString, $sign, $res, OPENSSL_ALGO_MD5);
        openssl_free_key($res);
        $sign = base64_encode($sign);
        return $sign;
    }

    /* 实现验签功能 */

    function verSign($signFormString, $sign) {
        $pubKey = file_get_contents(filePath . cerFileName . '.pem');
        $res = openssl_get_publickey($pubKey);
        $result = (bool) openssl_verify($signFormString, base64_decode($sign), $res, OPENSSL_ALGO_MD5);
        openssl_free_key($res);
        if ($result) {
            return "true";
        } else {
            return "false";
        }
    }

    /* 实现公钥加密功能 */

    function rsaEnc($keyStr) {
        $res = file_get_contents(filePath . cerFileName . '.pem');
        $public_key = openssl_pkey_get_public($res);
        openssl_public_encrypt(str_pad($keyStr, 256, "\0", STR_PAD_LEFT), $encrypted, $public_key, OPENSSL_NO_PADDING);
        $jiami = base64_encode($encrypted);
        return $jiami;
    }

    /**
     * curl post请求
     * @param type $url
     * @param type $data
     */
    public function curlPost($url,$data){
        if(!$url||!$data){
            return false;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        $this->add_log("helipay.log", "helipay", "请求response：". var_export($response, true));
        if (curl_errno($ch)) {
            $this->add_log("helipay.log", "helipay", "请求curl_errno：". var_export($ch, true));
            return false; 
        }
        curl_close($ch);
        return $response;
    }
    public function add_log($name, $path, $str, $mode = 0777) {
        if (!$name || !$path || !$str) {
            return false;
        }
        $paths = BASE_PATH . "log/" . $path . "/" . date("Y/m/d");
        $str = date("YmdHis") . "：" . $str . PHP_EOL;
        if (is_dir($paths)) {
            chmod($paths, 0777);
            file_put_contents($paths . '/' . $name, $str, FILE_APPEND);
            chmod($paths . '/' . $name, 0777);
        } else {
            if (mkdir($paths, $mode, true)) {
                chmod($paths, 0777);
                file_put_contents($paths . '/' . $name, $str, FILE_APPEND);
                chmod($paths . '/' . $name, 0777);
            }
        }
    }
}
?>