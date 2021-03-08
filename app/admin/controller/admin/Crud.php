<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\Db;
use think\facade\Request;
class Crud extends \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    
    protected function initialize()
    {
        $this->validate =  new \app\admin\validate\AdminCrud;
    }

   /**
     * Crud
     */
    public function index()
    {
        if (Request::isAjax()) {
            $list = [];
            foreach (Db::getTables() as $k =>$v) {
                $list[] = ['name'=>$v];
            }
            $this->jsonApi('', 0, $list,['count' =>count($list)]);
        }
        return $this->fetch('',[
            'prefix' => config('database.connections.mysql.prefix')
        ]);
    }

    /**
     * 删除
     */
    public function remove()
    {
        $name = substr(Request::param('name'),strlen(config('database.connections.mysql.prefix')));
        //验证
        if(!$this->validate->scene('del')->check(['name'=>$name])) 
        $this->jsonApi($this->validate->getError(),201);
        try {
            Db::query('drop table '.config('database.connections.mysql.prefix').$name);
            if(Request::param('type')){
                try {
                    $head = strstr($name , '_',true);  
                    $foot = substr($name,strlen($head)+1);
                    $foot_hump = underline_hump($foot);
                    $name_hump = underline_hump($name);
                    // 控制器
                    $controller = app_path().'controller'.DS.$head.DS.$foot_hump.'.php';
                    if (file_exists($controller)) unlink($controller);
                    // 模型
                    $model = root_path().'app'.DS.'common'.DS.'model'.DS.$name_hump.'.php';
                    if (file_exists($model)) unlink($model);
                    // 验证器
                    $validate = root_path().'app'.DS.'common'.DS.'validate'.DS.$name_hump.'.php';
                    if (file_exists($validate)) unlink($validate);
                    //删除视图目录
                    $view = root_path().'view'.DS.'admin'.DS.$head.DS.$foot;
                    if (file_exists($view)) delete_dir($view);
                    //删除菜单
                    (new \app\admin\model\AdminPermission)->where('href', 'like', "%" . $head.'.'.$foot . "%")->delete();
                    $this->rm();
                }catch (\Exception $e){
                $this->jsonApi('删除失败',201,$e->getMessage());
                }
                $this->jsonApi('操作成功');
            }
        }catch (\Exception $e){
            $this->jsonApi('删除失败,请手动删除','201');
        }
        $this->jsonApi('操作成功');
    }

    /**
     * 数据表列表
     */
    public function list($name)
    {
        $sql = Db::query('SELECT COLUMN_NAME,IS_NULLABLE,DATA_TYPE,IF(COLUMN_COMMENT = "",COLUMN_NAME,COLUMN_COMMENT) COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_NAME = "' . $name . '"order by ORDINAL_POSITION asc');
        $this->jsonApi('', 0, $sql);
    }

      /**
     * 新增基础表
     */
    public function add_base()
    {
        if (Request::isAjax()) {
            $name = Request::post('name');
            //验证
            if(!$this->validate->scene('base')->check(['name'=>$name])) 
            $this->jsonApi($this->validate->getError(),201);
            $crud = new \app\admin\service\CrudService;
            $data = $crud->base_sql(config('database.connections.mysql.prefix').$name,Request::post('desc'));
            foreach ($data as $k => $v) {
                if (substr($v, 0, 12) == 'CREATE TABLE') {
                    try {
                        Db::execute($v);
                    }catch (\Exception $e){
                        $this->jsonApi('创建失败',201,$e->getMessage());
                        }
                    $this->jsonApi('创建成功');
                }
            }
        }
        return $this->fetch('',[
            'prefix' => config('database.connections.mysql.prefix')
        ]);
    }


    /**
     * 生成
     */
    public function crud($name)
    {
        $data = Db::query('SELECT COLUMN_NAME,IS_NULLABLE,DATA_TYPE,IF(COLUMN_COMMENT = "",COLUMN_NAME,COLUMN_COMMENT) COLUMN_COMMENT 
        FROM information_schema.COLUMNS WHERE TABLE_NAME = "' . $name . '" AND COLUMN_NAME <> "id" order by ORDINAL_POSITION asc');
        if (Request::isAjax()) {
            $crud = new \app\admin\service\CrudService;
            $res = $crud->crud($name,Request::post());
            if($res){
                $this->rm();
                $this->jsonApi('操作成功');
            }
            $this->jsonApi('不可操作',201);
        }
        return $this->fetch('',[
            'data' => $data,

            'permissions' => get_tree((new \app\admin\model\AdminPermission)->order('sort','asc')->select()->toArray()),
            'desc' => Db::query('SELECT TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_NAME = "' . $name . '"')[0]['TABLE_COMMENT']
        ]);
    }

}
