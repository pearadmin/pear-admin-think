<?php
declare (strict_types = 1);

namespace app\admin\model\admin;

use think\Model;

/**
 * @mixin \think\Model
 */
class Photo extends Model
{
    protected $table = 'admin_photo';
    // 定义时间戳字段名
    public function getTypeAttr($value)
    {
        $type = ['1' => '本地', '2' => '阿里云'];
        return $type[$value];
    }

    public function add($info,$href,$type)
    {
        $data = [
            'name' => $info->getOriginalName(),
            'href' => $href,
            'type' => $type,
            'ext' => $info->getOriginalExtension(),
            'mime' => $info->getOriginalMime(),
            'size' => $info->getSize(),
        ];
        self::create($data);
    }
}