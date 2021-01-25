<?php
namespace app\common\validate;

class {{$app}} extends ValidateBase
{
    protected $rule = [{{$rule}}
    ];

    protected $message = [{{$message}}
    ];

    /**
     * 添加
     */
    public function sceneAdd()
    {
        return $this->only([{{$scene}}]);
    }

    /**
     * 编辑
     */
    public function sceneEdit()
    {
        return $this->only([{{$scene}}]);
    }
}
