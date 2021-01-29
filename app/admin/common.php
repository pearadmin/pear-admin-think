<?php
// 应用公共文件

//获取常量
if (!function_exists('app_admin')) {
    function app_admin()
    {
        return APP_ADMIN;
    }
}