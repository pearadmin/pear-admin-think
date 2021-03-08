<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;
use app\common\service\OssService;
/**
 * @mixin \think\Model
 */
class AdminPhoto extends Model
{
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

    static public function del($id)
    {
        $photo =  self::find($id);
        if($photo['type']=='阿里云'){
            OssService::alYunDel($photo['href']);
        }else{
            //删除本地文件
            $path = '../public'.$photo['href'];
            if (file_exists($path)) unlink($path);
        }
        $photo->delete();
    }
}