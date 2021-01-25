<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\facade\Db;
use app\admin\model\AdminPermission;
use think\facade\Request;
class AdminRole extends  \app\common\controller\AdminBase
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
    public function del($id)
    {
        $model = $this->model->find($id);
        if ($model->isEmpty()) $this->jsonApi('数据不存在',201);
        try{
            $model->delete();
            Db::table('admin_admin_role')->where('role_id', $id)->delete();
            $this->rm();
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201,$e->getMessage());
        }
        $this->jsonApi('删除成功');
    }

    /**
     * 用户分配角色
     */
    public function permission($id)
    {
        $role = $this->model->find($id);
        $permissions = AdminPermission::order('sort','asc')->select();
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
            $postPermissions = Request::post('permissions')??'';
            if(!$postPermissions) $this->jsonApi('至少选择一项',201);
            try{
                $role->permissions = implode(",",$postPermissions);
                $role->save();
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
                    if(Request::param('type')=='1'){
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
            $list = $this->model->onlyTrashed()->order('id','desc')->withoutField('delete_time')->where($this->where)->paginate(Request::get('limit'));
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => Request::get('limit')]);
        }
        return $this->fetch();
    }
}
