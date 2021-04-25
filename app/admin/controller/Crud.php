<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\facade\Db;
use app\common\util\Crud as U;

class Crud extends Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    
    // 系统配置
    public function index(){return $this->getAuto($this->fetch('',['prefix' => config('database.connections.mysql.prefix')]),U::getTable());}

    // 列表
    public function list($name){return $this->getJson(['code'=>0,'data'=>Db::getFields($name)]);}

    // 新增
    public function add(){return $this->getAuto($this->fetch('',['prefix' => config('database.connections.mysql.prefix')]),U::goAdd());}

    // 新增
    public function crud($name){return $this->getAuto($this->fetch('',U::getCrud($name)),U::goCrud($name));}

    // 删除
    public function remove($name){return $this->getJson(U::goRemove($name));}
}
