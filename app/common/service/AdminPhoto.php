<?php
declare (strict_types = 1);

namespace app\common\service;

use app\common\model\AdminPhoto as M;

class AdminPhoto
{
    // 删除
    public static function goRemove($id)
    {
        try{
            self::del($id);
        }catch (\Exception $e){
            return ['msg'=>'操作失败'.$e->getMessage(),'code'=>201];
        }
    }
    
    // 删除
    public static function goBatchRemove($ids)
    {
        if (!is_array($ids)) return ['msg'=>'数据不存在','code'=>201];
        try{
            foreach ($ids as $k) {
                self::del($k);
            }
        }catch (\Exception $e){
            return ['msg'=>'操作失败'.$e->getMessage(),'code'=>201];
        }
    }

    // 添加
    public static function add($info,$href,$type)
    {
        M::create([
            'name' => $info->getOriginalName(),
            'href' => $href,
            'type' => $type,
            'ext' => $info->getOriginalExtension(),
            'mime' => $info->getOriginalMime(),
            'size' => $info->getSize(),
        ]);
    }

    // 删除
    public static function del($id)
    {
        $photo =  M::find($id);
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
