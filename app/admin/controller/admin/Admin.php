<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\Request;
use think\facade\Db;
use app\common\service\AdminAdmin as S;
use app\common\model\AdminAdmin as M;
class Admin extends  \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];

    // 列表
    public function index(){return $this->getAuto($this->fetch(),M::getList());}

    // 添加
    public function add(){return $this->getAuto($this->fetch(),S::goAdd(Request::post()));}

    // 编辑
    public function edit($id){return $this->getAuto($this->fetch('',['model' => M::find($id)]),S::goEdit(Request::post(),$id));}

    // 状态
    public function status($id){return $this->getJson(S::goStatus(Request::post('status'),$id));}

    // 删除
    public function remove($id){return $this->getJson(S::goRemove($id));}

    // 批量删除
    public function batchRemove(){return $this->getJson(S::goBatchRemove(Request::post('ids')));}

    // 用户分配角色
    public function role($id){return $this->getAuto($this->fetch('',M::getRole($id)),S::goRole(Request::post('roles'),$id));}

    // 用户分配直接权限
    public function permission($id){return $this->getAuto($this->fetch('',M::getPermission($id)),S::goPermission(Request::post('permissions'),$id));}

    // 回收站
    public function recycle(){return $this->getAuto($this->fetch(),S::goRecycle());}

    // 用户日志
    public function log(){return $this->getAuto($this->fetch(),M::getLog());}

    // 清空日志
    public function removeLog(){return $this->getJson(Db::name('admin_admin_log')->delete(true));}

}
