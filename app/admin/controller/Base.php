<?php
declare (strict_types = 1);

namespace app\admin\controller;
use think\App;
use think\exception\ValidateException;
use think\Validate;
use think\facade\View;
class Base 
{
    use \app\common\traits\Base;

    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    //定义请求
    protected $get;

    protected $post;

    protected $param;

    protected $isPost;

    protected $isAjax;
    
    protected $isMobile;
    
    // 模型
    protected $model;

    // 模型
    protected $where;

    // 验证
    protected $validate;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
        // 控制器初始化
        $this->initialize();
    }
    

    // 初始化
    protected function initialize()
    {
        //初始化请求
        $this->get = $this->request->get();
        $this->post = $this->request->post();
        $this->param = $this->request->param();
        $this->isAjax = $this->request->isAjax();
        $this->isPost = $this->request->isPost();
        $this->isMobile = $this->request->isMobile();
    }

    //页面渲染 
    protected function fetch($template = '',$data = [])
    {
        return View::fetch($template,$data);
    }

    //添加数据 
    protected function _add($data)
    {
        try {
            $this->model->create($data);
        }catch (\Exception $e){
            return ['msg'=>'添加失败'.$e->getMessage(),'code'=>'201'];
        }
        return ['msg'=>'添加成功','code'=>'200'];
    } 

    //更新数据 
    protected function _update($id,$data)
    {
        $model =  $this->model->find($id);
        if ($model->isEmpty()) return ['msg'=>'数据不存在','code'=>'201'];
        try{
            $model->save($data);
        }catch (\Exception $e){
            return ['msg'=>'更新失败'.$e->getMessage(),'code'=>'201'];
        }
        return ['msg'=>'更新成功','code'=>'200'];
    }

    //删除数据 
    protected function _del($id)
    {
        $model = $this->model->find($id);
        if ($model->isEmpty()) return ['msg'=>'数据不存在','code'=>'201'];
        try{
            $model->delete();
        }catch (\Exception $e){
            return ['msg'=>'更新失败'.$e->getMessage(),'code'=>'201'];
        }
        return ['msg'=>'删除成功','code'=>'200'];
    }

    //删除所选数据 
    protected function _delall($ids)
    {
        if (!is_array($ids)) return ['msg'=>'参数错误','code'=>'201'];
        try{
            $this->model->destroy($ids);
        }catch (\Exception $e){
            return ['msg'=>'删除失败'.$e->getMessage(),'code'=>'201'];
        }
        return ['msg'=>'删除成功','code'=>'200'];
    }

    //回收站
    protected function _recycle($ids,$type)
    {
        if (!is_array($ids)) return ['msg'=>'参数错误','code'=>'201'];
        try{
            if($type=='1'){
                $data = $this->model->onlyTrashed()->whereIn('id', $ids)->select();
                foreach($data as $k){
                    $k->restore();
                }
            }else{
                $this->model->destroy($ids,true);
            }
        }catch (\Exception $e){
            return ['msg'=>'删除失败'.$e->getMessage(),'code'=>'201'];
        }
        return ['msg'=>'删除成功','code'=>'200'];
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

}
