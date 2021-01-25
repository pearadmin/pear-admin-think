<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\facade\Db;
use think\facade\Request;
class AdminPermission extends  \app\common\controller\AdminBase
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    
    protected function initialize()
    {
        $this->model = new \app\admin\model\AdminPermission;
        $this->validate =  new \app\admin\validate\AdminPermission;
    }
    /**
     * 权限
     */
    public function index()
    {
        if (Request::isAjax()) {    
            $list = $this->model->order('id','desc')->select();
            $this->jsonApi('', 0, $list->toArray(),['count' => $list->count()]);
        }
        return $this->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if (Request::isAjax()){
            $data = Request::post();
            //验证
            if(!$this->validate->check($data)) 
            $this->jsonApi($this->validate->getError(),201);
            try {
                $this->model->create($data);
            }catch (\Exception $e){
                $this->jsonApi('添加失败',201, $e->getMessage());
            }
            $this->jsonApi('添加成功');
        }
        return $this->fetch('',[
            'permissions' => get_tree($this->model->order('sort','asc')->select()->toArray())
        ]);
    }

     /**
     * 编辑
     */
    public function edit($id)
    { 
        $model =  $this->model->find($id);
        if (Request::isAjax()){
            $data = Request::post();
            $data['id'] = $model['id'];
            //验证
            if(!$this->validate->check($data)) 
            $this->jsonApi($this->validate->getError(),201);
            try {
                $model->save($data);
            }catch (\Exception $e){
                $this->jsonApi('更新失败',201, $e->getMessage());
            }
            $this->jsonApi('更新成功');
        }
        return $this->fetch('',[
            'model' => $model,
            'permissions' => get_tree(($this->model->order('sort','asc'))->select()->toArray())
        ]);
    }

    /**
     * 禁用，启用
     */
    public function status($id)
    {
        $model =  $this->model->find($id);
        if ($model->isEmpty()) $this->jsonApi('数据不存在',201);
        try{
            $model->status = Request::post('status');
            $model->save();
        }catch (\Exception $e){
            $this->jsonApi('更新失败',201,$e->getMessage());
        }
        $this->rm();
        $this->jsonApi('更新成功');
    }
    /**
     * 删除
     */
    public function del($id)
    {
        $model =  $this->model->with('child')->find($id);
        if (isset($model->child) && !$model->child->isEmpty()){
            $this->jsonApi('存在子权限，禁止删除',201);
        }
        try{
            $model->delete();
            $this->rm();
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201,$e->getMessage());
        }
        $this->jsonApi('删除成功');
    }
}
