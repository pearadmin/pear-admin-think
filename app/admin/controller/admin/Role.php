<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\Db;
use app\admin\model\admin\Permission;
class Role extends  \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    protected function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\admin\Role;
        $this->validate =  new \app\admin\validate\admin\Role;
    }
    /**
     * 角色
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
            $this->jsonApi($res['msg'],$res['code']);
        }
        return $this->fetch();
    }

     /**
     * 编辑
     */
    public function edit($id)
    { 
        if ($this->isAjax) {
            $data = $this->post;
            if(!$this->validate->scene('edit')->check($data)) 
            $this->jsonApi($this->validate->getError(),201);
            $res = $this->_update($id,$data);
            $this->jsonApi($res['msg'],$res['code']);
        }
        return $this->fetch('',[
            'model' => $this->model->find($id)
        ]);
    }

    /**
     * 删除
     */
    public function del($id)
    {
        $res = $this->_del($id);
        if($res['code']=='200'){
            Db::table('admin_admin_role')->where('role_id', $id)->delete();
            $this->rm();
        }
        $this->jsonApi($res['msg'],$res['code']);
    }

    /**
     * 分配权限
     */
    public function permission($id)
    {
        $role = $this->model->find($id);
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
        if ($this->isAjax){
            $postPermissions = $this->param['permissions']??'';
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
        if ($this->isAjax) {
            if ($this->isPost){
                $res =  $this->_recycle($this->param['ids'],$this->param['type']);
                $this->jsonApi($res['msg'],$res['code']);
            }
            //按用户名
            if ($search = input('get.username')) {
                $this->where[] = ['username', 'like', "%" . $search . "%"];
            }
            $list = $this->model->onlyTrashed()->order('id','desc')->withoutField('delete_time')->where($this->where)->paginate($this->get['limit']);
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => $this->get['limit']]);
        }
        return $this->fetch();
    }
}
