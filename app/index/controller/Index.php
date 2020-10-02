<?php
declare (strict_types = 1);

namespace app\index\controller;

use think\Request;

class Index
{
    /**
     * 首页
     */
    public function index()
    {
        $install = is_file(public_path() . '/install.lock')?'<span style="color:#2E5CD5;">欢迎使用</span>':'<a href="/install.php">点击安装</a>';
        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background-image: url(/static/admin/images/background.svg);text-align: center; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;}</style><div style="padding-top: 10rem;"><span style="font-size:30px;color: #5FB878!important;">Pear Admin Thinkphp</span></p><span style="font-size:25px;">'.$install.'</span></div>';
    }

}
