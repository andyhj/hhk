<?php
namespace Cli\Controller;
use Common\Common\Redis;
class IndexController extends InitController {
    public function index(){
        echo "this is index";
    }
    public function updRobot(){
        $m_redis = new Redis();
        $data = $m_redis->hgetall("global.data");
        $a = $m_redis->get("winratea");
        $b = $m_redis->get("winrateb");
        if(!$a){
            $a=3;
        }
        if(!$b){
            $b=0.8;
        }
        $winrate = round((rand(0,100)/$a/100)+$b,4);
        $data["winrate"] = $winrate;
        $m_redis->hmset("global.data", $data);
    }
}