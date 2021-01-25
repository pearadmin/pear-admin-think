<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;
//定义ADMIN常量
define("APP_ADMIN",'/admin.php');
//定义分隔符
define('DS', DIRECTORY_SEPARATOR);

require __DIR__ . '/../vendor/autoload.php';
// 执行HTTP应用并响应
$http = (new App())->http;


$response = $http->name('admin')->run();

$response->send();

$http->end($response);
