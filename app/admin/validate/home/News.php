<?php
namespace app\admin\validate\home;

use think\Validate;

class News extends Validate
{
    protected $rule = [
           'title' => 'require',
           'img' => 'require',
    ];
    protected $message = [
            'title.require' => '标题为必填项',
            'img.require' => '缩略图为必填项',
    ];
    protected $scene = [
        'edit' => [
            'title',
            'img',
        ],
    ];
}
