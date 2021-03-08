<?php
declare (strict_types = 1);
namespace app\common\model;

use think\Model;

class AdminConfig extends Model
{
    /**
     * 获取器: 转义数组格式
     */
    public function getValueAttr($value)
    {
        return json_decode($value, true);
    }

    /**
     * 修改器: 转义成json格式
     */
    public function setValueAttr($value)
    {
        return json_encode($value);
    }

    /**
     * 获取指定键
     */
    public static function getKeyValue($key)
    {
        $config = self::where('key',$key)->field('value')->find();
        return $config['value'];
    }

    /**
     * 获取所有键值
     */
    public static function getAllValue()
    {
        $config = self::field('key,value')->select()->toArray();
        return array_column($config,'value','key');
    }
}
