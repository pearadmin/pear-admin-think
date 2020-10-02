<?php
namespace app\admin\controller\{{$multi}};

use think\facade\Request;
use think\facade\View;
use think\facade\Db;
use app\admin\model\{{$multi}}\{{$multi_name_hump}} as {{$multi_name_hump}}Model;
use app\admin\validate\{{$multi}}\{{$multi_name_hump}} as {{$multi_name_hump}}Validate;
class {{$multi_name_hump}} extends Base
{

    /**
     * 列表
     */
    public function index()
    {
        if (Request::isAjax()) {
            $where = [];
            {{$search}}
            $list = {{$multi_name_hump}}Model::order('id','desc')->where($where)->paginate(Request::get('limit'));
            {{$list}}
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
            $validate = new {{$multi_name_hump}}Validate;
            if(!$validate->check($data)) 
            $this->jsonApi($validate->getError(),201);
            try {
                {{$multi_name_hump}}Model::create($data);
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
        ${{$multi}} = {{$multi_name_hump}}Model::find($id);
        if (Request::isAjax()) {
            $data = Request::post();
            $data['id'] = ${{$multi}}['id'];
            //验证
            $validate = new {{$multi_name_hump}}Validate;
            if(!$validate->scene('edit')->check($data)) 
            $this->jsonApi($validate->getError(),201);
            try {
                ${{$multi}}->save($data);
            }catch (\Exception $e){
                $this->jsonApi('更新失败',201, $e->getMessage());
            }
            $this->jsonApi('更新成功');
        }
        return View::fetch('',[
            'data' => ${{$multi}}
        ]);
    }

    /**
     * 删除
     */
    public function del($id)
    {
        ${{$multi}} = {{$multi_name_hump}}Model::find($id);
        if(${{$multi}}){
            try{
               ${{$multi}}->delete();
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
            {{$multi_name_hump}}Model::destroy($ids);
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
                        $data = {{$multi_name_hump}}Model::onlyTrashed()->whereIn('id', $ids)->select();
                        foreach($data as $k){
                            $k->restore();
                        }
                    }else{
                        {{$multi_name_hump}}Model::destroy($ids,true);
                    }
                }catch (\Exception $e){
                    $this->jsonApi('操作失败',201, $e->getMessage());
                }
                $this->jsonApi('操作成功');
            }
            $where = [];
            {{$search}}
            $list = {{$multi_name_hump}}Model::onlyTrashed()->order('id','desc')->where($where)->paginate(Request::get('limit'));
            {{$list}}
            $this->jsonApi('', 0, $list->items(), ['count' => $list->total(), 'limit' => Request::get('limit')]);
        }
        return View::fetch();
    }
}
