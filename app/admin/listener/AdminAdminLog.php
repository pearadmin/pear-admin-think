<?php
declare (strict_types = 1);
namespace app\admin\listener;

class AdminAdminLog
{
    public function handle()
    {
        (new \app\admin\model\AdminAdminLog())->record();
    }
}