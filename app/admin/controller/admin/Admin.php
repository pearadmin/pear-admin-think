<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\Db;
use app\admin\model\admin\Role;
class Admin extends  \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    
    protected function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\admin\Admin;
        $this->validate =  new \app\admin\validate\admin\Admin;
    }
    /**
     * 管理员
     */
    public function index()
    {
        if ($this->isAjax) {
            //按用户名
            if ($search = input('get.username')) {
               $this->where[] = ['username', 'like', "%" . $search . "%"];
            }
            $list = $this->model->order('id','desc')->where('id','>','1')->withoutField('password,rand_key,delete_time')->where($this->where)->paginate($this->get['limit']);
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' =>$this->get['limit']]);
        }
        return $this->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->isAjax){
            $data = $this->post;
            //验证
            if(!$this->validate->scene('add')->check($data)) 
            $this->jsonApi($this->validate->getError(),201);
            try {
                $password =  set_password($data['password']);
                $this->model->create(array_merge($data, [
                    'password' => $password,
                ]));
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
        $admin =  $this->model->find($id);
        if ($this->isAjax){
            $data = $this->post;
            $data['id'] = $admin['id'];
            //验证
            if(!$this->validate->scene('edit')->check($data)) 
            $this->jsonApi($this->validate->getError(),201);
            //是否需要修改密码
            if ($data['password']){
                $admin->password = set_password($data['password']);
                $admin->token = null;
            } 
            $admin->username = $data['username'];
            $admin->nickname = $data['nickname'];
            try {
                $admin->save();
            }catch (\Exception $e){
                $this->jsonApi('更新失败',201, $e->getMessage());
            }
            $this->jsonApi('更新成功');
        }
        return $this->fetch('',[
            'model' => $admin
        ]);
    }

    /**
     * 禁用，启用
     */
    public function status()
    {
        $data = [
           'status' => $this->param['status'],
           'token' => null
        ];
        $res = $this->_update($this->param['id'],$data);
        if($res['code']=='200') $this->rm();
        $this->jsonApi($res['msg'],$res['code']);
    }

    /**
     * 删除
     */
    public function del($id)
    {
        $res = $this->_del($id);
        if($res['code']=='200'){
            Db::table('admin_admin_role')->where('admin_id', $id)->delete();
            $this->rm();
        }
        $this->jsonApi($res['msg'],$res['code']);
    }

    /**
     * 选中删除
     */
    public function del_all()
    {
        $ids = $this->param['ids'];
        $res = $this->_delall($ids);
        if($res['code']=='200'){
            Db::table('admin_admin_role')->whereIn('admin_id', $ids)->delete();
            $this->rm();
        }
        $this->jsonApi($res['msg'],$res['code']);
    }

    /**
     * 用户分配角色
     */
    public function role()
    {
        $id = $this->param['id'];
        $admin = $this->model->with('roles')->where('id',$id)->find();
        $roles = Role::select();
        foreach ($roles as $k=>$role){
            if (isset($admin->roles) && !$admin->roles->isEmpty()){
                foreach ($admin->roles as $v){
                    if ($role['id']==$v['id']){
                        $roles[$k]['own'] = true;
                    }
                }
            }
        }
        if ($this->isAjax){
            $postRoles = $this->param['roles']??'';
            if(!$postRoles) $this->jsonApi('至少选择一项',201);
            Db::startTrans();
            try{
                //清除原先的角色
                Db::name('admin_admin_role')->where('admin_id',$id)->delete();
                //添加新的角色
                foreach ($postRoles as $v){
                    Db::name('admin_admin_role')->insert([
                        'admin_id' => $admin['id'],
                        'role_id' => $v,
                    ]);
                }
                Db::commit();
                $this->rm();
            }catch (\Exception $e){
                Db::rollback();
                $this->jsonApi('更新失败',201, $e->getMessage());
            }
            $this->jsonApi('更新成功');
        }
        
        return $this->fetch('',[
            'admin' => $admin,
            'roles' => $roles,
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
            $list = $this->model->onlyTrashed()->order('id','desc')->withoutField('password,delete_time')->where($this->where)->paginate($this->get['limit']);
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => $this->get['limit']]);
        }
        return $this->fetch();
    }
}
