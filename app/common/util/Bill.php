<?php
namespace app\common\util;

use Endroid\QrCode\QrCode;
class Bill
{
   /**
    * 海报
    * @param string $file 文件索引
    * @param string $link 链接
    * @param string $addr 海报地址
    * @param string $path 生成位置
    * @param string $position 图像位置
    * @return mixed
    */
    public static function go($file,$link,$addr,$path='play_bill',$position=['100','100'])
    {
        if (!$addr||filter_var($addr, FILTER_VALIDATE_URL) !== false){
             return ['code'=>'201','msg'=>'请配置海报图片,须为本地'];
        }
        if (!file_exists('./'.$path.'/')) mkdir('./'.$path.'/', 0777, true);
        $new_file = './'.$path.'/' .$file . '.png';
        header('Content-Type: image/png');
        $qrCode = new QrCode($link);
        $qrCode->setSize(144);
        $qrCode->setRoundBlockSize(false);
        $qrCode->writeFile($new_file);
        $image = \think\Image::open(public_path().$addr);
        $image->water($new_file,$position)->save(public_path().$new_file);
        return ['msg'=>$new_file];
    }
}