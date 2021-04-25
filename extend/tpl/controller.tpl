<?php
declare (strict_types = 1);

namespace app\admin\controller\{{$left}};

use think\facade\Request;
use app\common\service\{{$table_hump}} as S;
use app\common\model\{{$table_hump}} as M;

class {{$right_hump}} extends  \app\admin\controller\Base
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

    // 回收站
    public function recycle(){return $this->getAuto($this->fetch(),S::goRecycle());}

}
