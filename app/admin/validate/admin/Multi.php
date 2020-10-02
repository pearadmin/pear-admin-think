<?php
declare (strict_types = 1);

namespace app\admin\validate\admin;

use think\Validate;

class Multi extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
    protected $rule = [
        'name'=>'require|alpha|unique:admin_multi',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'name.require'=>'多级地址不能为空',
        'name.unique'=>'多级地址已经存在',
        'name.alpha'=>'多级地址只能为英文',
    ];
}