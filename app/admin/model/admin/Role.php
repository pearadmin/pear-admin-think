<?php
declare (strict_types = 1);

namespace app\admin\model\admin;

use think\Model;
use think\model\concern\SoftDelete;
/**
 * @mixin \think\Model
 */
class Role extends Model
{
    protected $table = 'admin_role';
    use SoftDelete;
    protected $deleteTime = 'delete_time';
}
