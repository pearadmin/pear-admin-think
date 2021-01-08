<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\Db;
class Permission extends  \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    protected function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\admin\Permission;
        $this->validate =  new \app\admin\validate\admin\Permission;
    }
    /**
     * 管理员
     */
    public function index()
    {
        if ($this->isAjax) {
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
        if ($this->isAjax) {
            $data = $this->post;
            if(!$this->validate->check($data)) 
            $this->jsonApi($this->validate->getError(),201);
            $res = $this->_add($data);
            if($res['code']=='200') $this->rm();
            $this->jsonApi($res['msg'],$res['code']);
        }
        return $this->fetch('', [
            'permissions' => get_tree($this->model->order('sort','asc')->select()->toArray()),
            'multi' => Db::name('admin_multi')->order(['name'])->column('name', 'id'),
        ]);
    }

     /**
     * 编辑
     */
    public function edit($id)
    { 
        $model = $this->model->find($id);
        if ($this->isAjax) {
            $data = $this->post;
            if(!$this->validate->scene('edit')->check($data)) 
            $this->jsonApi($this->validate->getError(),201);
            $res = $this->_update($model['id'],$data);
            if($res['code']=='200') $this->rm();
            $this->jsonApi($res['msg'],$res['code']);
        }
        return $this->fetch('',[
            'model' => $model,
            'permissions' => get_tree(($this->model->order('sort','asc'))->select()->toArray()),
            'multi' => Db::name('admin_multi')->order(['name'])->column('name', 'id'),
        ]);
    }

    /**
     * 禁用，启用
     */
    public function status()
    {
        $id = $this->param['id'];
        $data = [
           'status' => $this->param['status'],
           'token' => null
        ];
        $res = $this->_update($id,$data);
        if($res['code']=='200') $this->rm();
        $this->jsonApi($res['msg'],$res['code']);
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
        $res = $this->_del($model['id']);
        if($res['code']=='200') $this->rm();
        $this->jsonApi($res['msg'],$res['code']);
    }
  
}
