<?php
namespace app\admin\controller\home;

use think\facade\Request;
use think\facade\View;
use think\facade\Db;
use app\admin\model\home\News as NewsModel;
use app\admin\validate\home\News as NewsValidate;
class News extends \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    
    /**
     * 列表
     */
    public function index()
    {
        if (Request::isAjax()) {
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
            $list = NewsModel::order('id','desc')->where($where)->paginate(Request::get('limit'));
            
            //重整数组
            foreach ($list as $k => $v) {
                    $list[$k]['img'] = '<img src="' . $v['img'] . '"/>';
            }
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => Request::get('limit')]);
        }
        return View::fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if (Request::isAjax()) {
            $data = Request::post();
            //验证
            $validate = new NewsValidate;
            if(!$validate->check($data)) 
            $this->jsonApi($validate->getError(),201);
            try {
                NewsModel::create($data);
            }catch (\Exception $e){
                $this->jsonApi('添加失败',201, $e->getMessage());
            }
            $this->jsonApi('添加成功');
        }
        return View::fetch();
    }

    /**
     * 编辑
     */
    public function edit($id)
    { 
        $home = NewsModel::find($id);
        if (Request::isAjax()) {
            $data = Request::post();
            $data['id'] = $home['id'];
            //验证
            $validate = new NewsValidate;
            if(!$validate->scene('edit')->check($data)) 
            $this->jsonApi($validate->getError(),201);
            try {
                $home->save($data);
            }catch (\Exception $e){
                $this->jsonApi('更新失败',201, $e->getMessage());
            }
            $this->jsonApi('更新成功');
        }
        return View::fetch('',[
            'data' => $home
        ]);
    }

    /**
     * 删除
     */
    public function del($id)
    {
        $home = NewsModel::find($id);
        if($home){
            try{
               $home->delete();
            }catch (\Exception $e){
                $this->jsonApi('删除失败',201, $e->getMessage());
            }
            $this->jsonApi('删除成功');
        }
    }

    /**
     * 选中删除
     */
    public function delall()
    {
        $ids = Request::param('ids');
        if (!is_array($ids)){
            $this->jsonApi('参数错误',201);
        }
        try{
            NewsModel::destroy($ids);
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201, $e->getMessage());
        }
        $this->jsonApi('删除成功');
    }


    /**
     * 回收站
     */
    public function recycle()
    {
        if (Request::isAjax()) {
            if (Request::isPost()){
                $ids = Request::param('ids');
                if (!is_array($ids)){
                    $this->jsonApi('参数错误',201);
                }
                try{
                    if(Request::param('type')=='1'){
                        $data = NewsModel::onlyTrashed()->whereIn('id', $ids)->select();
                        foreach($data as $k){
                            $k->restore();
                        }
                    }else{
                        NewsModel::destroy($ids,true);
                    }
                }catch (\Exception $e){
                    $this->jsonApi('操作失败',201, $e->getMessage());
                }
                $this->jsonApi('操作成功');
            }
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
            $list = NewsModel::onlyTrashed()->order('id','desc')->where($where)->paginate(Request::get('limit'));
            
            //重整数组
            foreach ($list as $k => $v) {
                    $list[$k]['img'] = '<img src="' . $v['img'] . '"/>';
            }
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => Request::get('limit')]);
        }
        return View::fetch();
    }
}
