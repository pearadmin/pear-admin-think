<?php
namespace app\common\model;

class SiteConfig extends \app\common\model\ModelBase
{
    protected $table = 'site_config';
    protected $deleteTime = false; 

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
}
