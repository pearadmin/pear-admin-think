<?php
namespace app\admin\controller\{{$multi}};

use think\facade\View;
class {{$multi_name_hump}} extends \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    protected $model = 'app\admin\model\{{$multi}}\{{$multi_name_hump}}';
    protected $validate =  'app\admin\validate\{{$multi}}\{{$multi_name_hump}}';
    
    /**
     * 列表
     */
    public function index()
    {
        $where = [];
        {{$search}}
        $this->_list($where);
        return View::fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        $this->_add();
        return View::fetch();
    }

    /**
     * 编辑
     */
    public function edit($id)
    { 
        $model = new $this->model();
        $this->_edit($id);
        return View::fetch('',[
            'data' => $model->find($id)
        ]);
    }

    /**
     * 删除
     */
    public function del($id)
    {
       $this->_del($id);
    }

    /**
     * 选中删除
     */
    public function delall()
    {
        $this->_delall();
    }


    /**
     * 回收站
     */
    public function recycle()
    {
        $where = [];
        {{$search}}
        $this->_recycle($where);
        return View::fetch();
    }
}
