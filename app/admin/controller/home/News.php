<?php
namespace app\admin\controller\home;

class News extends \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
      protected function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\home\News;
        $this->validate =  new \app\admin\validate\home\News;
    }
    
    /**
     * 列表
     */
    public function index()
    {
        if ($this->isAjax) {
            
            //按标题查找
            if ($title = input("title")) {
                $this->where[] = ["title", "like", "%" . $title . "%"];
            }
            //按创建时间查找
            $start = input("get.create_time-start");
            $end = input("get.create_time-end");
            if ($start && $end) {
                $this->where[]=["create_time","between",[$start,date("Y-m-d",strtotime("$end +1 day"))]];
            }
            $list = $this->model->order('id','desc')->where($this->where)->paginate($this->get['limit']);
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' =>$this->get['limit']]);
        }
        return $this->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->isAjax) {
            $data = $this->post;
            if(!$this->validate->check($data)) 
            $this->jsonApi($this->validate->getError(),201);
            $res = $this->_add($data);
            $this->jsonApi($res['msg'],$res['code']);
        }
        return $this->fetch();
    }

    /**
     * 编辑
     */
    public function edit($id)
    { 
        if ($this->isAjax) {
            $data = $this->post;
            if(!$this->validate->scene('edit')->check($data)) 
            $this->jsonApi($this->validate->getError(),201);
            $res = $this->_update($id,$data);
            $this->jsonApi($res['msg'],$res['code']);
        }
        return $this->fetch('',[
            'data' => $this->model->find($id)
        ]);
    }

    /**
     * 删除
     */
    public function del($id)
    {
        $res = $this->_del($id);
        $this->jsonApi($res['msg'],$res['code']);
    }

    /**
     * 选中删除
     */
    public function delall()
    {
        $ids = $this->param['ids'];
        $res = $this->_delall($ids);
        $this->jsonApi($res['msg'],$res['code']);
    }


    /**
     * 回收站
     */
    public function recycle()
    {
        if ($this->isAjax) {
            if ($this->isPost){
                $res =  $this->_recycle($this->param['ids'],$this->param['type']);
                $this->jsonApi($res['msg'],$res['code']);
            }
            
            //按标题查找
            if ($title = input("title")) {
                $this->where[] = ["title", "like", "%" . $title . "%"];
            }
            //按创建时间查找
            $start = input("get.create_time-start");
            $end = input("get.create_time-end");
            if ($start && $end) {
                $this->where[]=["create_time","between",[$start,date("Y-m-d",strtotime("$end +1 day"))]];
            }
            $list =  $this->model->onlyTrashed()->order('id','desc')->where($this->where)->paginate($this->get['limit']);
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => $this->get['limit']]);
        }
        return $this->fetch();
    }
}
