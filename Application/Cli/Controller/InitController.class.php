<?php
namespace Cli\Controller;
use Think\Controller;
class InitController extends Controller{
    public function __construct() {
        parent::__construct();
    }
    public function getHeaders(){
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if ('HTTP_' == substr($key, 0, 5)) {
                $headers[str_replace('_', '-', substr($key, 5))] = $value;
            }
        }
        return $headers;
    }
    public function getRawBody()
    {
        $str_raw_body = file_get_contents('php://input');
        $arr_raw_body = json_decode($str_raw_body,true);
        if($arr_raw_body){
            return $arr_raw_body;
        }
        return $str_raw_body;
    }
    public function getGameType($type){
        if(!$type){
            return false;
        }
        $game_type = ["1"=>"开心斗地主","2"=>"炸金花","3"=>"牛牛","4"=>"德州扑克","5"=>"四川麻将","6"=>"中国象棋"];
        return $game_type[$type];
    }
}
