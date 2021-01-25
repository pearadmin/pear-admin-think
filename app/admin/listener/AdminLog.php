<?php

namespace app\admin\listener;

class AdminLog
{
    public function handle()
    {
        (new \app\admin\model\AdminAdminLog())->record();
    }
}