<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\Request;
use app\common\service\AdminRole as S;
use app\common\model\AdminRole as M;

class Role extends \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];

    // 列表
    public function index(){return $this->getAuto($this->fetch(),M::getList());}

    // 添加
    public function add(){return $this->getAuto($this->fetch(),S::goAdd(Request::post()));}

    // 编辑
    public function edit($id){return $this->getAuto($this->fetch('',['model' => M::find($id)]),S::goEdit(Request::post(),$id));}

    // 删除
    public function remove($id){return $this->getJson(S::goRemove($id));}

    // 用户分配直接权限
    public function permission($id){return $this->getAuto($this->fetch('',M::getPermission($id)),S::goPermission(Request::post('permissions'),$id));}

    // 回收站
    public function recycle(){return $this->getAuto($this->fetch(),S::goRecycle());}
    
}
