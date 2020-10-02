<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\Request;
use think\facade\View;
use think\facade\Db;
use app\admin\model\admin\Role;
use app\admin\model\admin\Admin as AdminModel;
use app\admin\validate\admin\Admin as AdminValidate;
class Admin extends Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    /**
     * 管理员
     */
    public function index()
    {
        if (Request::isAjax()) {
            $where = [];
            //按用户名
            if ($search = input('get.username')) {
               $where[] = ['username', 'like', "%" . $search . "%"];
            }
            $list = AdminModel::order('id','desc')->where('id','>','1')->withoutField('password,rand_key,delete_time')->where($where)->paginate(Request::get('limit'));
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
            $validate = new AdminValidate;
            if(!$validate->scene('add')->check($data)) 
            $this->jsonApi($validate->getError(),201);
            try {
                $password =  set_password($data['password']);
                AdminModel::create(array_merge($data, [
                    'password' => $password,
                ]));
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
        $admin = AdminModel::find($id);
        if (Request::isAjax()) {
            $data = Request::post();
            $data['id'] = $admin['id'];
            //验证
            $validate = new AdminValidate;
            if(!$validate->scene('edit')->check($data)) 
            $this->jsonApi($validate->getError(),201);
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
        return View::fetch('',[
            'model' => $admin
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
        $admin =  AdminModel::find($id);
        if ($admin->isEmpty()){
            $this->jsonApi('数据不存在',201);
        }
        try{
            $admin->status = $status;
            $admin->token = null;
            $admin->save();
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
        $admin = AdminModel::find($id);
        if($admin){
            try{
                //删除中间表
                Db::table('admin_role')->where('admin_id', $admin['id'])->delete();
                $admin->delete();
                $this->rm();
            }catch (\Exception $e){
                $this->jsonApi('删除失败',201, $e->getMessage());
            }
            $this->jsonApi('删除成功');
        }
    }

    /**
     * 选中删除
     */
    public function del_all()
    {
        $ids = Request::param('ids');
        if (!is_array($ids)){
            $this->jsonApi('参数错误',201);
        }
        try{
            AdminModel::destroy($ids);
            //删除中间表
             Db::table('admin_role')->whereIn('admin_id', $ids)->delete();
             $this->rm();
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201, $e->getMessage());
        }
        $this->jsonApi('删除成功');
    }

    /**
     * 用户分配角色
     */
    public function role()
    {
        $id = Request::param('id');
        $admin = AdminModel::with('roles')->where('id',$id)->find();
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
        if (Request::isAjax()){
            $postRoles = Request::param('roles');
            if(!isset($postRoles))
            $this->jsonApi('至少选择一项',201);
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
        return View::fetch('',[
            'admin' => $admin,
            'roles' => $roles,
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
                        $data = AdminModel::onlyTrashed()->whereIn('id', $ids)->select();
                        foreach($data as $k){
                            $k->restore();
                        }
                    }else{
                        AdminModel::destroy($ids,true);
                    }
                }catch (\Exception $e){
                    $this->jsonApi('操作失败',201, $e->getMessage());
                }
                $this->jsonApi('操作成功');
            }
            $where = [];
            //按用户名
            if ($search = input('get.username')) {
               $where[] = ['username', 'like', "%" . $search . "%"];
            }
            $list = AdminModel::onlyTrashed()->order('id','desc')->withoutField('password,delete_time')->where($where)->paginate(Request::get('limit'));
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => Request::get('limit')]);
        }
        return View::fetch();
    }
}
