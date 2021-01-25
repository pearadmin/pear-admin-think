<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class AdminPermission extends Model
{
    protected $table = 'admin_permission';
    /**
     * 子权限
     */
    public function child()
    {
        return $this->hasMany('AdminPermission','pid','id');
    }
}
