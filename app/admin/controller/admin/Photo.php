<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\Request;
use app\common\service\AdminPhoto as S;
use app\common\model\AdminPhoto as M;

class Photo extends \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];

    // 列表
    public function index(){return $this->getAuto($this->fetch(),M::getList());}

    // 添加单图
    public function addPhoto(){return $this->fetch();}

    // 添加多图
    public function addPhotos(){return $this->fetch();}

    // 删除
    public function remove($id){return $this->getJson(S::goRemove($id));}

    // 批量删除
    public function batchRemove(){return $this->getJson(S::goBatchRemove(Request::post('ids')));}
}
