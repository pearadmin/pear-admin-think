<?php
namespace app\admin\controller;
use think\facade\Request;
class {{$app}} extends \app\common\controller\AdminBase
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    
    protected function initialize()
    {
        $this->model = new \app\common\model\{{$app}};
        $this->validate =  new \app\common\validate\{{$app}};
    }
    
    /**
     * 列表
     */
    public function index()
    {
        if (Request::isAjax()) {
            {{$search}}
            $list = $this->model->order('id','desc')->where($this->where)->paginate(Request::get('limit'));
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => Request::get('limit')]);
        }
        return $this->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if (Request::isAjax()){
            $data = Request::post();
            //验证
            if(!$this->validate->scene('add')->check($data)) 
            $this->jsonApi($this->validate->getError(),201);
            try {
                $this->model->create($data);
            }catch (\Exception $e){
                $this->jsonApi('添加失败',201, $e->getMessage());
            }
            $this->jsonApi('添加成功');
        }
        return $this->fetch();
    }

    /**
     * 编辑
     */
    public function edit($id)
    { 
        $model =  $this->model->find($id);
        if (Request::isAjax()){
            $data = Request::post();
            $data['id'] = $model['id'];
            //验证
            if(!$this->validate->scene('edit')->check($data)) 
            $this->jsonApi($this->validate->getError(),201);
            try {
                $model->save($data);
            }catch (\Exception $e){
                $this->jsonApi('更新失败',201, $e->getMessage());
            }
            $this->jsonApi('更新成功');
        }
        return $this->fetch('',[
            'data' => $model
        ]);
    }

    /**
     * 删除
     */
    public function del($id)
    {
        $model = $this->model->find($id);
        if ($model->isEmpty()) $this->jsonApi('数据不存在',201);
        try{
            $model->delete();
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201,$e->getMessage());
        }
        $this->jsonApi('删除成功');
    }

    /**
     * 选中删除
     */
    public function delall()
    {
        $ids = Request::post('ids');
        if (!is_array($ids)) $this->jsonApi('参数错误',201);
        try{
            $this->model->destroy($ids);
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201,$e->getMessage());
        }
        $this->jsonApi('删除成功');
    }


    /**
     * 回收站
     */
    public function recycle()
    {
        if (Request::isAjax()){
            if (Request::isPost()){
                $ids = Request::param('ids');
                if (!is_array($ids)) return ['msg'=>'参数错误','code'=>'201'];
                try{
                    if(Request::param('type')=='1'){
                        $data = $this->model->onlyTrashed()->whereIn('id', $ids)->select();
                        foreach($data as $k){
                            $k->restore();
                        }
                    }else{
                        $this->model->destroy($ids,true);
                    }
                }catch (\Exception $e){
                    $this->jsonApi('删除失败',201,$e->getMessage());
                }
                $this->jsonApi('删除成功');
            }
            {{$search}}
            $list = $this->model->onlyTrashed()->order('id','desc')->withoutField('delete_time')->where($this->where)->paginate(Request::get('limit'));
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => Request::get('limit')]);
        }
        return $this->fetch();
    }
}
