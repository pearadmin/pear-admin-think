<?php
declare (strict_types = 1);

namespace app\admin\controller;
use think\facade\Session;
use think\exception\HttpResponseException;
use think\Response;
use think\facade\Request;
class Base
{
    // 初始化
    protected function initialize()
    {
        parent::initialize();
    }

    //数据列表 
    protected function _list($where=[])
    {
        if (Request::isAjax()) {
            $limit = Request::get('limit');
            $list = (new $this->model())->order('id','desc')->where($where)->paginate($limit);
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => $limit]);
        }
    } 

    //添加数据 
    protected function _add()
    {
        if (Request::isAjax()) {
            $data = Request::post();
            $validate =  new $this->validate();
            if(!$validate->check($data)) 
            $this->jsonApi($validate->getError(),201);
            try {
                (new $this->model())->create($data);
            }catch (\Exception $e){
                $this->jsonApi('添加失败',201,$e->getMessage());
            }
            $this->jsonApi('添加成功');
        }
    } 

    //更新数据 
    protected function _edit($id)
    {
        if (Request::isAjax()) {
            $data = Request::post();
            $data['id'] = $id;
            $validate =  new $this->validate();
            if(!$validate->scene('edit')->check($data)) 
            $this->jsonApi($validate->getError(),201);
            try {
                (new $this->model())->update($data);
            }catch (\Exception $e){
                $this->jsonApi('更新失败',201,$e->getMessage());
            }
            $this->jsonApi('更新成功');
        }
    }

    //删除数据 
    protected function _del($id)
    {

        $model = (new $this->model())->find($id);
        if($model){
            try{
               $model->delete();
            }catch (\Exception $e){
                $this->jsonApi('删除失败',201, $e->getMessage());
            }
            $this->jsonApi('删除成功');
        }
    }

    //更新全部数据 
    protected function _delall()
    {
        $ids = Request::param('ids');
        if (!is_array($ids)){
            $this->jsonApi('参数错误',201);
        }
        try{
            (new $this->model())->destroy($ids);
        }catch (\Exception $e){
            $this->jsonApi('删除失败',201, $e->getMessage());
        }
        $this->jsonApi('删除成功');
    }

    //回收站
    protected function _recycle($where=[])
    {

        if (Request::isAjax()) {
            $model =  new $this->model();
            $limit = Request::get('limit');
            if (Request::isPost()){
                $ids = Request::param('ids');
                if (!is_array($ids)){
                    $this->jsonApi('参数错误',201);
                }
                try{
                    if(Request::param('type')=='1'){
                        $data =  $model->onlyTrashed()->whereIn('id', $ids)->select();
                        foreach($data as $k){
                            $k->restore();
                        }
                    }else{
                        $model->destroy($ids,true);
                    }
                }catch (\Exception $e){
                    $this->jsonApi('操作失败',201, $e->getMessage());
                }
                $this->jsonApi('操作成功');
            }
            $list = $model->onlyTrashed()->order('id','desc')->withoutField('delete_time')->where($where)->paginate($limit);
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => $limit]);
        }
    }

    //清除缓存
    protected function rm()
    {
        delete_dir(root_path().'runtime');
        Session::clear();
    }

    /**
     * 返回API
     * @access protected
     * @param  string  $msg    提示信息
     * @param  integer $code   状态码
     * @param  array   $data   对应数据
     * @param  array   $extend 扩展字段
     * @param  array   $header HTTP头信息
     * @return void
     * @throws HttpResponseException
     */
    protected  function jsonApi($msg = '', $code = 200, $data = [], $extend = [], $header = [])
    {
        $return = [
            'msg'  => $msg,
            'code' => $code,
        ];
        if (!empty($data)) {
            $return['data'] = $data;
        }
        if (!empty($extend)) {
            foreach ($extend as $k => $v) {
                $return[$k] = $v;
            }
        }
        $response = Response::create($return, 'json')->header($header);
        throw new HttpResponseException($response);
    }

}
