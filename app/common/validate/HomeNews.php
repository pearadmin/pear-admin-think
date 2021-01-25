<?php
namespace app\common\validate;

class HomeNews extends ValidateBase
{
    protected $rule = [
           'title' => 'require',
           'img' => 'require',
    ];

    protected $message = [
            'title.require' => '标题为必填项',
            'img.require' => '缩略图为必填项',
    ];

    /**
     * 添加
     */
    public function sceneAdd()
    {
        return $this->only(['title','img',]);
    }

    /**
     * 编辑
     */
    public function sceneEdit()
    {
        return $this->only(['title','img',]);
    }
}
