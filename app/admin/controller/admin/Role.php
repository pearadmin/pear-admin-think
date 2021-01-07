<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\Request;
use think\facade\View;
use think\facade\Db;
use app\admin\model\admin\Permission;
use app\admin\model\admin\Role as RoleModel;
use app\admin\validate\admin\Role as RoleValidate;
class Role extends  \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    protected $model = 'app\admin\model\admin\Role';
    protected $validate =  'app\admin\validate\admin\Role';
    /**
     * 角色
     */
    public function index()
    {
        $this->_list();
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
            'model' => $model->find($id)
        ]);
    }

    /**
     * 删除
     */
    public function del($id)
    {
        $role = RoleModel::find($id);
        if($role){
            try{
                //删除中间表
                Db::table('admin_admin_role')->where('role_id', $role['id'])->delete();
                $role->delete();
                $this->rm();
            }catch (\Exception $e){
                $this->jsonApi('删除失败',201, $e->getMessage());
            }
            $this->jsonApi('删除成功');
        }
    }

    /**
     * 分配权限
     */
    public function permission($id)
    {
        $role = RoleModel::find($id);
        $permissions = Permission::order('sort','asc')->select();
        foreach ($permissions as $permission){
            if ($role['permissions']){
                foreach (explode(',',$role['permissions']) as $p){
                    if ($permission->id==$p){
                        $permission->own = true;
                    }
                }
            }
        }
        $permissions = get_tree($permissions->toArray());
        if (Request::isAjax()){
            $postPermissions = Request::param('permissions');
            if(!isset($postPermissions))
            $this->jsonApi('至少选择一项',201);
            try{
                $role->permissions = implode(",",$postPermissions);
                $role->save();
                $this->rm();
            }catch (\Exception $e){
                $this->jsonApi('更新失败',201, $e->getMessage());
            }
            $this->jsonApi('更新成功');
        }
        return View::fetch('',[
            'permissions' => $permissions,
            'role' => $role,
        ]);
    }

    /**
     * 回收站
     */
    public function recycle()
    {
        $this->_recycle();
        return View::fetch();
    }
}
