<?php
declare (strict_types = 1);

namespace app\index\controller;

use think\facade\View;
class Index
{
    /**
     * 首页
     */
    public function index()
    {
        return View::fetch();
    }
}
