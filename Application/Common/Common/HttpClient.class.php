<?php
namespace Common\Common;
/* Version 0.9, 6th April 2003 - Simon Willison ( http://simon.incutio.com/ )
   Manual: http://scripts.incutio.com/httpclient/
*/

class HttpClient {

    public $buffer = null;      // buffer 获取返回的字符串
    public $referer = null;     // referer 设置 HTTP_REFERER 的网址
    public $response = null;    // response 服务器响应的 header 信息
    public $request = null;     // request 发送到服务器的 header 信息
    private $args = null;

    public static function init(&$instanceof, $args = array()) {
        return $instanceof = new self($args);
    }

    private function __construct($args = array()) {

        if(!is_array($args)) $args = array();
        $this->args = $args;
        if(!empty($this->args['debugging'])) {
            ob_end_clean();
            set_time_limit(0);
            header('Content-Type: text/plain; charset=utf-8');
        }

    }

    public function get($url, $data = null, $cookie = null) {

        $parse = parse_url($url);
        $url .= isset($parse['query']) ? '&'. $data : ( $data ? '?'. $data : '' );
        $host = $parse['host'];

        $header  = 'Host: '. $host. "\\r\\n";
        $header .= 'Connection: close'. "\\r\\n";
        $header .= 'Accept: */*'. "\\r\\n";
        $header .= 'User-Agent: '. ( isset($this->args['userAgent']) ? $this->args['userAgent'] : $_SERVER['HTTP_USER_AGENT'] ). "\\r\\n";
        $header .= 'DNT: 1'. "\\r\\n";
        if($cookie) $header .= 'Cookie: '. $cookie. "\\r\\n";
        if($this->referer) $header .= 'Referer: '. $this->referer. "\\r\\n";

        $options = array();
        $options['http']['method'] = 'GET';
        $options['http']['header'] = $header;

        $response = get_headers($url);
        $this->request = $header;
        $this->response = implode("\\r\\n", $response);
        $context = stream_context_create($options);
        return $this->buffer = file_get_contents($url, false, $context);

    }

    public function post($url, $data = null, $cookie = null) {

        $parse = parse_url($url);
        $host = $parse['host'];

        $header  = 'Host: '. $host. "\\r\\n";
        $header .= 'Connection: close'. "\\r\\n";
        $header .= 'Accept: */*'. "\\r\\n";
        $header .= 'User-Agent: '. ( isset($this->args['userAgent']) ? $this->args['userAgent'] : $_SERVER['HTTP_USER_AGENT'] ). "\\r\\n";
        $header .= 'Content-Type: application/x-www-form-urlencoded'. "\\r\\n";
        $header .= 'DNT: 1'. "\\r\\n";
        if($cookie) $header .= 'Cookie: '. $cookie. "\\r\\n";
        if($this->referer) $header .= 'Referer: '. $this->referer. "\\r\\n";
        if($data) $header .= 'Content-Length: '. strlen($data). "\\r\\n";

        $options = array();
        $options['http']['method'] = 'POST';
        $options['http']['header'] = $header;
        if($data) $options['http']['content'] = $data;

        $response = get_headers($url);
        $this->request = $header;
        $this->response = implode("\\r\\n", $response);
        $context = stream_context_create($options);
        return $this->buffer = file_get_contents($url, false, $context);

    }

}

?>