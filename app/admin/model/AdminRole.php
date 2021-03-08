<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;
/**
 * @mixin \think\Model
 */
class AdminRole extends Model
{
    use SoftDelete;
    /**
     * 角色所有的权限
     * @return \think\model\relation\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany('AdminPermission','admin_role_permission','permission_id','role_id');
    }
}
