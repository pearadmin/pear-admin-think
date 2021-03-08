<?php
declare (strict_types = 1);

namespace app\common\validate;

use think\Validate;
class {{$table_name_hump}} extends Validate
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
