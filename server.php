<?php
/*
 * @desc 本程序用于将一般的cli程序启动为服务，比起之前的的守护进程实现方案，这个方案更佳优秀，对资源占用极少，
 * 最重要的是，之前的实现方案有一个比较严重的问题：在启动后关闭之前，无法释放资源，这个新的方案完美的解决了这个问题;
 * @param $argv[1] 需要开启为服务的命令
 * @param $argv[2] start/stop,命令停止或开始,默认start
 * 
 * //命令行调用时命令：
 * 启动：php server.php index/test
 * 停止：php server.php index/test
 */

//定义常量
define('ROOT', dirname(__FILE__));
define('LOG_PATH', ROOT.'/Public/logs/server/');

//开始
init();

function init(){
    if(php_sapi_name() != 'cli'){
        exit('非法请求');
    }
    global $argv;
    if(!$argv[1]){
        exit("请传递有效参数\n");
    }
    if(strpos($argv[1], '/') ===false){
        //后期可以建一个表来维护，做一个名称和命令的映射，可以直接传递名称来启动服务
        exit("暂不支持\n");
    }
    if(!$argv[2]){
        $argv[2] = 'start';
    }
    
    $str_command = $argv[1];
    $str_st = $argv[2];
    if($str_st == 'start'){
        $int_interval = @intval($argv[3])?intval($argv[3]):5;//默认5秒;
        start($str_command,$int_interval);
    }
    
    if($str_st == 'stop'){
        stop($str_command);
    }
}

function start($command,$interval = 5){
    $str_root = ROOT;
    $str_command = "php {$str_root}/cli.php {$command}";
    while (true){
        $out = null;
        exec($str_command,$out);
        $str_log_file = str_replace('/','_',$command).'_'.date('Y-m-d').'.log';
        if($out){
            writeLog($str_log_file, implode("\n", $out));
        }
        sleep($interval);
    }
    exit('启动成功');
}

function stop($command){
    $str_exec = "ps -fae | grep 'server.php {$command}'|awk '{print $2}' |xargs kill -9";
    exec($str_exec,$out);
    var_export($out);
}

function writeLog($filename, $message) {

    $file_dir = LOG_PATH;
    $file_dir .= date('Y/m/');
    if (!is_dir($file_dir)) {
        mkdir($file_dir, 0777, true);
        chmod($file_dir, 0777);
    }
    if (!file_exists($file_dir . $filename)) {
        file_put_contents($file_dir . $filename, date('Y-m-d H:i:s') . ' - ' . print_r($message, true) . "\r\n");
        chmod($file_dir . $filename, 0777);
    } else {
        file_put_contents($file_dir . $filename, date('Y-m-d H:i:s') . ' - ' . print_r($message, true) . "\r\n", FILE_APPEND);
    }
}
