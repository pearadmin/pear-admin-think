<?php
declare (strict_types = 1);

namespace app\admin\controller;
use think\facade\Session;
class Base
{
    use \app\common\traits\Base;

    //清除缓存
    protected function rm()
    {
        delete_dir(root_path().'runtime');
        Session::clear();
    }

}
