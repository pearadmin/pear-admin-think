<?php
declare (strict_types = 1);

namespace app\index\controller;

class Base extends \app\common\controller\AdminBase
{
    protected function initialize()
    {
        $this->assign('site',get_config('web'));
    }
}
