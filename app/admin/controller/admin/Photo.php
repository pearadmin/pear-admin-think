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
    public function index(){return $this->getAuto($this->fetch(),M::getPath());}

    // 创建文件夹
    public function add(){return $this->getAuto($this->fetch(),S::goAdd());}
    
    // 删除文件夹
    public function Del($name){return $this->getJson(S::goDel($name));}

    // 列表
    public function list($name){return $this->getJson(M::getList($name));}

    // 添加单图
    public function addPhoto($name){return $this->fetch('',['name'=>$name]);}

    // 添加多图
    public function addPhotos($name){return $this->fetch('',['name'=>$name]);}

    // 删除
    public function remove($id){return $this->getJson(S::goRemove($id));}

    // 批量删除
    public function batchRemove(){return $this->getJson(S::goBatchRemove(Request::post('ids')));}
}
