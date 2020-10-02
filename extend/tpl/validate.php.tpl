<?php
namespace app\admin\validate\{{$multi}};

use think\Validate;

class {{$multi_name_hump}} extends Validate
{
    protected $rule = [{{$rule}}
    ];
    protected $message = [{{$message}}
    ];
    protected $scene = [
        'edit' => [{{$scene}}
        ],
    ];
}
