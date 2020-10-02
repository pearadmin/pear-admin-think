<?php
declare (strict_types = 1);

namespace app\admin\model\admin;

use think\Model;

/**
 * @mixin \think\Model
 */
class Permission extends Model
{
    protected $table = 'admin_permission';
    /**
     * 子权限
     */
    public function child()
    {
        return $this->hasMany('Permission','pid','id');
    }
}
