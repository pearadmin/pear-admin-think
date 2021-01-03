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
    /**
     * 角色
     */
    public function index()
    {
        if (Request::isAjax()) {
            $list = RoleModel::order('id','desc')->withoutField('permissions,delete_time')->paginate(Request::get('limit'));
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => Request::get('limit')]);
        }
        return View::fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if (Request::isAjax()) {
            $data = Request::post();
            //验证
            $validate = new RoleValidate;
            if(!$validate->check($data)) 
            $this->jsonApi($validate->getError(),201);
            try {
                RoleModel::create($data);
            }catch (\Exception $e){
                $this->jsonApi('添加失败',201, $e->getMessage());
            }
            $this->jsonApi('添加成功');
        }
        return View::fetch();
    }

     /**
     * 编辑
     */
    public function edit($id)
    { 
        $role = RoleModel::find($id);
        if (Request::isAjax()) {
            $data = Request::post();
            $data['id'] = $role['id'];
            //验证
            $validate = new RoleValidate;
            if(!$validate->check($data)) 
            $this->jsonApi($validate->getError(),201);
            try {
                $role->name = $data['name'];
                $role->desc = $data['desc'];
                $role->save();
            }catch (\Exception $e){
                $this->jsonApi('更新失败',201, $e->getMessage());
            }
            $this->jsonApi('更新成功');
        }
        return View::fetch('',[
            'model' => $role
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
        if (Request::isAjax()) {
            if (Request::isPost()){
                $ids = Request::param('ids');
                if (!is_array($ids)){
                    $this->jsonApi('参数错误',201);
                }
                try{
                    if(Request::param('type')=='1'){
                        $data = RoleModel::onlyTrashed()->whereIn('id', $ids)->select();
                        foreach($data as $k){
                            $k->restore();
                        }
                    }else{
                        RoleModel::destroy($ids,true);
                    }
                }catch (\Exception $e){
                    $this->jsonApi('操作失败',201, $e->getMessage());
                }
                $this->jsonApi('操作成功');
            }
            $list = RoleModel::onlyTrashed()->order('id','desc')->withoutField('delete_time')->paginate(Request::get('limit'));
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => Request::get('limit')]);
        }
        return View::fetch();
    }
}
