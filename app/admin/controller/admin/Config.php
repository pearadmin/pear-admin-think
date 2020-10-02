<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\admin\model\admin\Photo;
class Config  extends Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    
    public function index()
    {
        if (Request::isAjax()){
            $data = Request::param();
            foreach ($data as $k => $v) {
                Db::name('admin_config')->where('name', $k)->update(['value'=> $v]);
            }
            $this->jsonApi('保存成功');
        }
        return View::fetch('', [
            'data' =>  Db::name('admin_config')->column('value', 'name')
            ]);
    }

    public function photo()
    {
        if (Request::isAjax()){
            $list = Photo::order('id','desc')->paginate(Request::get('limit'));
            $this->jsonApi('', 0, $list->items(),['count' => $list->total(), 'limit' => Request::get('limit')]);
        }
        return View::fetch();
    }

    public function photoAdd()
    {
        return View::fetch();
    }

    public function photoDel()
    {
        $id = Request::param('id');
        try{
            $photo =  Photo::find($id);
            if($photo['type']=='阿里云'){
                alYunDel($photo['href']);
            }else{
                //删除本地文件
                $path = '../public'.$photo['href'];
                if (file_exists($path)) unlink($path);
            }
            $photo->delete();
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201);
        }
        $this->jsonApi('删除成功');
    }

    public function photoDelAll()
    {
        $ids = Request::param('ids');
        if (!is_array($ids)){
            $this->jsonApi('参数错误',201);
        }
        try{
            foreach ($ids as $k) {
                $photo =  Photo::where('id',$k)->find();
                if($photo['type']=='阿里云'){
                    alYunDel($photo['href']);
                }else{
                    //删除本地文件
                    $path = '../public'.$photo['href'];
                    if (file_exists($path)) unlink($path);
                }
                $photo->delete();
            }
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201);
        }
        $this->jsonApi('删除成功');
    }

    public function log()
    {
        if (Request::isAjax()){
            $where = [];
            if ($search = input('get.uid')) {
               $where[] = ['uid', '=',$search];
            }
            $list = (new \app\admin\model\admin\AdminLog)->with('log')->order('id','desc')->where($where)->paginate(Request::get('limit'));
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => Request::get('limit')]);
        }
        return View::fetch();
    }

}
