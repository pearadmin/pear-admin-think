<?php
namespace app\admin\controller\home;

use think\facade\View;
class News extends \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    protected $model = 'app\admin\model\home\News';
    protected $validate =  'app\admin\validate\home\News';
    
    /**
     * 列表
     */
    public function index()
    {
        $where = [];
        
        //按标题查找
        if ($title = input("title")) {
            $where[] = ["title", "like", "%" . $title . "%"];
        }
        //按更新时间查找
        $start = input("get.create_time-start");
        $end = input("get.create_time-end");
        if ($start && $end) {
            $where[]=["create_time","between",[$start,date("Y-m-d",strtotime("$end +1 day"))]];
        }
        $this->_list($where);
        return View::fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        $this->_add();
        return View::fetch();
    }

    /**
     * 编辑
     */
    public function edit($id)
    { 
        $model = new $this->model();
        $this->_edit($id);
        return View::fetch('',[
            'data' => $model->find($id)
        ]);
    }

    /**
     * 删除
     */
    public function del($id)
    {
       $this->_del($id);
    }

    /**
     * 选中删除
     */
    public function delall()
    {
        $this->_delall();
    }


    /**
     * 回收站
     */
    public function recycle()
    {
        $where = [];
        
        //按标题查找
        if ($title = input("title")) {
            $where[] = ["title", "like", "%" . $title . "%"];
        }
        //按更新时间查找
        $start = input("get.create_time-start");
        $end = input("get.create_time-end");
        if ($start && $end) {
            $where[]=["create_time","between",[$start,date("Y-m-d",strtotime("$end +1 day"))]];
        }
        $this->_recycle($where);
        return View::fetch();
    }
}
