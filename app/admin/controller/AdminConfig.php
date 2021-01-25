<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\facade\Db;
use think\facade\Request;
use app\admin\model\AdminPhoto;
class AdminConfig extends  \app\common\controller\AdminBase
{
    protected $middleware = ['AdminCheck','AdminPermission'];

    /**
     * 设置
     */
    public function index()
    {
        if (Request::isAjax()) {    
            $data = Request::post();
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
          if (Request::isAjax()) {    
            $list = AdminPhoto::order('id','desc')->paginate(Request::get('limit'));
            $this->jsonApi('', 0, $list->items(),['count' => $list->total(), 'limit' => Request::get('limit')]);
        }
        return $this->fetch();
    }

    public function photoAdd()
    {
        return $this->fetch();
    }

    public function photoDel()
    {
        $id = Request::param('id');
        try{
            AdminPhoto::del($id);
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201);
        }
        $this->jsonApi('删除成功');
    }

    public function photoDelAll()
    {
        $ids =  Request::param('ids');
        if (!is_array($ids)) $this->jsonApi('参数错误',201);
        try{
            foreach ($ids as $k) {
                AdminPhoto::del($k);
            }
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201);
        }
        $this->jsonApi('删除成功');
    }

    public function log()
    {
        if (Request::isAjax()) {    
            if ($search = input('get.uid')) {
                $this->where[] = ['uid', '=',$search];
            }
            $list = (new \app\admin\model\AdminAdminLog)->with('log')->order('id','desc')->where($this->where)->paginate(Request::get('limit'));
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => Request::get('limit')]);
        }
        return $this->fetch();
    }
}
