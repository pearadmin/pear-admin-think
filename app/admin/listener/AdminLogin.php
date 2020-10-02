<?php

namespace app\admin\listener;

class AdminLogin
{
    public function handle()
    {
        (new \app\admin\model\admin\AdminLog())->record();
    }
}