<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\facade\Request;
class AdminPhoto extends  \app\common\controller\AdminBase
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    
    protected function initialize()
    {
        $this->model = new \app\admin\model\AdminPhoto;
    }

    public function index()
    {
          if (Request::isAjax()) {    
            $list = $this->model->order('id','desc')->paginate(Request::get('limit'));
            $this->jsonApi('', 0, $list->items(),['count' => $list->total(), 'limit' => Request::get('limit')]);
        }
        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch();
    }

    public function del()
    {
        $id = Request::param('id');
        try{
            $this->model->del($id);
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201);
        }
        $this->jsonApi('删除成功');
    }

    public function del_all()
    {
        $ids =  Request::param('ids');
        if (!is_array($ids)) $this->jsonApi('参数错误',201);
        try{
            foreach ($ids as $k) {
                $this->model->del($k);
            }
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201);
        }
        $this->jsonApi('删除成功');
    }
}
