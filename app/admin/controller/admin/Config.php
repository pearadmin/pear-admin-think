<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\Db;
use app\admin\model\admin\Photo;
class Config  extends  \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    protected function initialize()
    {
        parent::initialize();
    }
    public function index()
    {
        if ($this->isAjax){
            $data = $this->param;
            foreach ($data as $k => $v) {
                Db::name('admin_config')->where('name', $k)->update(['value'=> $v]);
            }
            $this->jsonApi('保存成功');
        }
        return $this->fetch('', [
           'data' =>  Db::name('admin_config')->column('value', 'name')
        ]);
    }

    public function photo()
    {
        if ($this->isAjax){
            $list = Photo::order('id','desc')->paginate($this->get['limit']);
            $this->jsonApi('', 0, $list->items(),['count' => $list->total(), 'limit' => $this->get['limit']]);
        }
        return $this->fetch();
    }

    public function photoAdd()
    {
        return $this->fetch();
    }

    public function photoDel()
    {
        $id = $this->param['id'];
        try{
            Photo::del($id);
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201);
        }
        $this->jsonApi('删除成功');
    }

    public function photoDelAll()
    {
        $ids =  $this->param['ids'];
        if (!is_array($ids)) $this->jsonApi('参数错误',201);
        try{
            foreach ($ids as $k) {
                Photo::del($k);
            }
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201);
        }
        $this->jsonApi('删除成功');
    }

    public function log()
    {
        if ($this->isAjax){
            if ($search = input('get.uid')) {
                $this->where[] = ['uid', '=',$search];
            }
            $list = (new \app\admin\model\admin\AdminLog)->with('log')->order('id','desc')->where($this->where)->paginate($this->get['limit']);
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => $this->get['limit']]);
        }
        return $this->fetch();
    }

}
