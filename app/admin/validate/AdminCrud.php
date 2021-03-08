<?php
declare (strict_types = 1);

namespace app\admin\validate;

use think\Validate;

class AdminCrud extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
    protected $rule = [
        'name|数据表名称' => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.notIn' => '默认禁止操作',
        'name.alphaDash'=>'仅支持英文数字下划线',
    ];

    /**
     * 创建基础表
     */
    public function sceneBase()
    {
        return $this->only(['name'])->append('name', 'alphaDash');
    }

    /**
     * 删除
     */
    public function sceneDel()
    {
        return $this->only(['name'])->append('name',
        'notIn:admin_admin,admin_admin_log,admin_admin_permission,admin_admin_role,admin_config,admin_crud,admin_permission,admin_photo,admin_role,admin_role_permission');
    }

}
