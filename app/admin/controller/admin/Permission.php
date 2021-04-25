<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\Request;
use app\common\service\AdminPermission as S;
use app\common\model\AdminPermission as M;

class Permission extends \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    
    // 列表
    public function index(){return $this->getAuto($this->fetch(),M::getList());}

    // 添加
    public function add(){return $this->getAuto($this->fetch('',['permissions' => get_tree(M::order('sort','asc')->select()->toArray())]),S::goAdd(Request::post()));}

    // 编辑
    public function edit($id){return $this->getAuto($this->fetch('',M::getFind($id)),S::goEdit(Request::post(),$id));}

    // 状态
    public function status($id){return $this->getJson(S::goStatus(Request::post('status'),$id));}

    // 删除
    public function remove($id){return $this->getJson(S::goRemove($id));}
}

