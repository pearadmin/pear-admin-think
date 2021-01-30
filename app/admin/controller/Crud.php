<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\facade\Db;
use app\admin\model\AdminPermission;
use think\facade\Request;
class Crud extends   \app\common\controller\AdminBase
{
    protected $middleware = ['AdminCheck','AdminPermission'];

    /**
     * 列表
     */
    public function index()
    {
        if (Request::isAjax()) {
            $name = input('get.name');
            $sql = Db::query('SELECT COLUMN_NAME,IS_NULLABLE,DATA_TYPE,IF(COLUMN_COMMENT = "",COLUMN_NAME,COLUMN_COMMENT) COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_NAME = "' . $name . '"order by ORDINAL_POSITION asc');
            $this->jsonApi('', 0, $sql);
        }
        return $this->fetch('',[
            'list' =>Db::getTables()
        ]);
    }

    /**
     * 新增基础表
     */
    public function addBase()
    {
        if (Request::isAjax()) {
            $name = Request::post('name');
            $desc = Request::post('desc');
                $sql = '
DROP TABLE IF EXISTS `'.$name.'`;
CREATE TABLE `'.$name.'` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "id",
`create_time` timestamp NULL DEFAULT NULL COMMENT "更新时间",
`update_time` timestamp NULL DEFAULT NULL COMMENT "创建时间",
`delete_time` timestamp NULL DEFAULT NULL COMMENT "删除时间",
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT="'.$desc.'";
                ';
                $sql_array = preg_split("/;[\r\n]+/", $sql);
                foreach ($sql_array as $k => $v) {
                    if (substr($v, 0, 12) == 'CREATE TABLE') {
                            Db::query($v);
                            $this->jsonApi('创建成功');
                    }
                }
            }
        return $this->fetch();
    }

    /**
     * 删除
     */
    public function del()
    {
        $res = (new \app\admin\service\CrudService)->del(Request::param()); 
        if($res['code']==200) $this->rm();
        $this->jsonApi($res['msg'],$res['code'],$res['data']);
    }
    
    /**
     * 生成
     */
    public function crud($name)
    {
        $sql = Db::query('SELECT COLUMN_NAME,IS_NULLABLE,DATA_TYPE,IF(COLUMN_COMMENT = "",COLUMN_NAME,COLUMN_COMMENT) COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_NAME = "' . $name . '" AND COLUMN_NAME <> "id" order by ORDINAL_POSITION asc');
        if (Request::isAjax()) {
            $res = (new \app\admin\service\CrudService)->crud(Request::param(),$sql); 
            if($res['code']==200) $this->rm();
            $this->jsonApi($res['msg'],$res['code'],$res['data']);
        }
        //表数据
        return $this->fetch('',[
            'info' => $sql,
            'permissions' => get_tree(AdminPermission::order('sort','asc')->select()->toArray()),
            'desc' => Db::query('SELECT TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_NAME = "' . $name . '"')[0]['TABLE_COMMENT']
        ]);
    }

}
