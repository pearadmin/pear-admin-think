<?php
namespace app\common\service;

use OSS\OssClient;
use OSS\Core\OssException;
use think\exception\ValidateException;
class UploadService
{

   /**
    *通用上传
    */
    static function commonFile($file,$path = 'common'){
        try {
            validate(['file'=>[
                'fileSize' => 410241024,
                'fileExt' => 'jpg,jpeg,png,bmp,gif',
                'fileMime' => 'image/jpeg,image/png,image/gif', 
            ]])->check(['file' => $file]);
        } catch (\think\exception\ValidateException $e) {
            return ['msg'=>'上传失败','code'=>201,'data'=>$e->getMessage()];
        }
        foreach($file as $k) {
            if(get_config('file','file-type')==2){
                //阿里云上传
                $res = OssService::alYunOSS($k, $k->extension(),$path);
                if ($res["code"] == 201) return ['msg'=>'上传失败','code'=>201,'data'=>$res["msg"]];
                $name = $res['src'];
                $type = 2;
            }else{
                $savename = '/'. \think\facade\Filesystem::disk('public')->putFile($path, $k);
                $name = str_replace("\\","/",$savename);
                $type = 1;
            }
            (new \app\admin\model\AdminPhoto)->add($k,$name,$type);
        }
        return ['msg'=>'上传成功','code'=>0,'data'=>['src'=>$name,'thumb'=>$name]];
   }
}