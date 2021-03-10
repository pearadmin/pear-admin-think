<?php
declare (strict_types = 1);

namespace app\common\validate;

use think\Validate;
class HomeNews extends Validate
{
    protected $rule = [
        'title' => 'require',
        'status' => 'require',
        'status_ext' => 'require',
    ];

    protected $message = [
        'title.require' => '标题为必填项',
        'status.require' => '状态为必填项',
        'status_ext.require' => '性别:0=男,1=女为必填项',
    ];

    /**
     * 添加
     */
    public function sceneAdd()
    {
        return $this->only(['title','status','status_ext',]);
    }

    /**
     * 编辑
     */
    public function sceneEdit()
    {
        return $this->only(['title','status','status_ext',]);
    }
}
