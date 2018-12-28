<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
//禁止浏览器访问
if(php_sapi_name() != 'cli'){
    die('非法访问！');
}
// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',false);
define('__APP__', '');
//系统根目录
define('APP_ROOT',dirname(__FILE__).'/');
define('HTTP_HOST','');
define('CDN_HOST','');
define('API_HOST','');
define('ADMIN_HOST','');
//定义模块
define('APP_MODE','cli');
//定义模块
define('BIND_MODULE','Cli');
// 定义应用目录
define('APP_PATH',dirname(__FILE__).'/Application/');

// 引入ThinkPHP入口文件
require dirname(__FILE__).'/ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单