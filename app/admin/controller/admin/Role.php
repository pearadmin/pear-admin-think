<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\Db;
use think\facade\Request;
class Role extends \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    
    protected function initialize()
    {
        $this->model = new \app\admin\model\AdminRole;
        $this->validate =  new \app\admin\validate\AdminRole;
    }
    /**
     * 角色
     */
    public function index()
    {
        if (Request::isAjax()) {    
            $list = $this->model->order('id','desc')->paginate(Request::get('limit'));
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => Request::get('limit')]);
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
        return $this->fetch();
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
            'model' => $model
        ]);
    }

    /**
     * 删除
     */
    public function remove($id)
    {
        $model = $this->model->find($id);
        if ($model->isEmpty()) $this->jsonApi('数据不存在',201);
        try{
            $model->delete();
            Db::name('admin_admin_role')->where('role_id', $id)->delete();
            Db::name('admin_role_permission')->where('role_id', $id)->delete();
            $this->rm();
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201,$e->getMessage());
        }
        $this->jsonApi('删除成功');
    }

    /**
     * 分配权限
     */
    public function permission($id)
    {
        $role = $this->model->with('permissions')->find($id);
        $permissions = (new \app\admin\model\AdminPermission)->order('sort','asc')->select();
        foreach ($permissions as $permission){
            if (isset($role->permissions) && !$role->permissions->isEmpty()){
                foreach ($role->permissions as $p){
                    if ($permission->id==$p->id){
                        $permission->own = true;
                    }
                }
            }
        }
        $permissions = get_tree($permissions->toArray());
        if (Request::isAjax()){
            $postPermissions = Request::post('permissions');
            if(!$postPermissions) $this->jsonApi('至少选择一项',201);
            try{
                Db::name('admin_role_permission')->where('role_id',$id)->delete();
                foreach ($postPermissions as $p){
                    Db::name('admin_role_permission')->insert([
                        'role_id' => $id,
                        'permission_id' => $p,
                    ]);
                }
                $this->rm();
            }catch (\Exception $e){
                $this->jsonApi('更新失败',201, $e->getMessage());
            }
            $this->jsonApi('更新成功');
        }
        return $this->fetch('',[
            'permissions' => $permissions,
            'role' => $role,
        ]);
    }

    /**
     * 回收站
     */
    public function recycle()
    {
        if (Request::isAjax()){
            if (Request::isPost()){
                $ids = Request::param('ids');
                if (!is_array($ids)) return ['msg'=>'参数错误','code'=>'201'];
                try{
                    if(Request::param('type')){
                        $data = $this->model->onlyTrashed()->whereIn('id', $ids)->select();
                        foreach($data as $k){
                            $k->restore();
                        }
                    }else{
                        $this->model->destroy($ids,true);
                    }
                }catch (\Exception $e){
                    $this->jsonApi('删除失败',201,$e->getMessage());
                }
                $this->jsonApi('删除成功');
            }
            $list = $this->model->onlyTrashed()->order('id','desc')->paginate(Request::get('limit'));
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => Request::get('limit')]);
        }
        return $this->fetch();
    }
}
