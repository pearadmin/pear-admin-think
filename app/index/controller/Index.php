<?php
declare (strict_types = 1);

namespace app\index\controller;

class Index extends Base
{
    /**
     * 首页
     */
    public function index()
    {
       return $this->fetch();
    }
}
