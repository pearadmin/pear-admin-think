<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

class AdminPhoto extends Model
{
    // 获取列表
    public static function getList()
    {
        $limit = input('get.limit');
        $list = self::order('id','desc')->paginate($limit);
        return ['code'=>0,'data'=>$list->items(),'extend'=>['count' => $list->total(), 'limit' => $limit]];
    }

    public function getTypeAttr($value)
    {
        $type = ['1' => '本地', '2' => '阿里云'];
        return $type[$value];
    }
}