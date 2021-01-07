<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\Request;
use think\facade\View;
use think\facade\Db;
use app\admin\model\admin\Permission as PermissionModel;
use app\admin\validate\admin\Permission as PermissionValidate;
class Permission extends  \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    protected $model = 'app\admin\model\admin\Permission';
    protected $validate =  'app\admin\validate\admin\Permission';
    /**
     * 管理员
     */
    public function index()
    {
        if (Request::isAjax()) {
            $list = PermissionModel::order('id','desc')->select();
            $this->jsonApi('', 0, $list->toArray(),['count' => $list->count()]);
        }
        return View::fetch();
    }

   /**
     * 添加
     */
    public function add()
    {
        $this->_add();
        return View::fetch('', [
            'permissions' => get_tree(PermissionModel::order('sort','asc')->select()->toArray()),
            'multi' => Db::name('admin_multi')->order(['name'])->column('name', 'id'),
        ]);
    }

     /**
     * 编辑
     */
    public function edit($id)
    { 
        $permission = PermissionModel::find($id);
        $this->_edit($id);
        return View::fetch('',[
            'model' => $permission,
            'permissions' => get_tree((PermissionModel::order('sort','asc'))->select()->toArray()),
            'multi' => Db::name('admin_multi')->order(['name'])->column('name', 'id'),
        ]);
    }

    /**
     * 禁用，启用
     */
    public function status()
    {
        $id = Request::param('id');
        $status = Request::param('status');
        if (!in_array($status,[1,2])){
            $this->jsonApi('参数错误',201);
        }
        $permission =  PermissionModel::find($id);
        if ($permission->isEmpty()){
            $this->jsonApi('数据不存在',201);
        }
        try{
            $permission->status = $status;
            $permission->save();
            $this->rm();
        }catch (\Exception $e){
            $this->jsonApi('更新失败',201,$e->getMessage());
        }
        $this->jsonApi('更新成功');
    }

    /**
     * 删除
     */
    public function del($id)
    {
        $permission =  PermissionModel::with('child')->find($id);
        if ($permission->isEmpty()){
            $this->jsonApi('数据不存在',201);
        }
        if (isset($permission->child) && !$permission->child->isEmpty()){
            $this->jsonApi('存在子权限，禁止删除',201);
        }
        try{
            $permission->delete();
            $this->rm();
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201, $e->getMessage());
        }
        $this->jsonApi('删除成功');
    }
  
}
