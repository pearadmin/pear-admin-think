<?php
declare (strict_types = 1);

namespace app\admin\model;

/**
 * @mixin \think\Model
 */
class AdminRole extends \app\common\model\ModelBase
{
    protected $table = 'admin_role';
    protected $deleteTime = 'delete_time';
}
