<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\Request;
class Config extends \app\admin\controller\Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    
    protected function initialize()
    {
        $this->model = new \app\common\model\AdminConfig;
    }

   /**
     * 网站设置
     */
    public function index()
    {
        $model = $this->model->getAllValue();
        if (Request::isAjax()) {  
            $data = Request::post();
            $this->model->where('key',$data['formType'])->save(['value'=>json_encode($data)]);
            $this->jsonApi('保存成功');
        }
        return $this->fetch('', [
            'data' =>  $model
        ]);
    }
}
