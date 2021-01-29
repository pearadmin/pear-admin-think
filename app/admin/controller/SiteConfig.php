<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\facade\Request;
class SiteConfig extends  \app\common\controller\AdminBase
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    /**
     * 初始化
     */
    protected function initialize()
    {
        $this->model = new \app\common\model\SiteConfig;
    }

    /**
     * 网站设置
     */
    public function web()
    {
        $model = $this->model->where('key','web')->field('value')->find();
        if (Request::isAjax()) {    
            $model->save(['value'=>Request::post()]);
            $this->jsonApi('保存成功');
        }
        return $this->fetch('', [
            'data' =>  $model['value']
        ]);
    }

    /**
     * 邮箱设置
     */
    public function email()
    {
        $model = $this->model->where('key','email')->field('value')->find();
        if (Request::isAjax()) {    
            $model->save(['value'=>Request::post()]);
            $this->jsonApi('保存成功');
        }
        return $this->fetch('', [
            'data' =>  $model['value']
        ]);
    }

    /**
     * 上传设置
     */
    public function file()
    {
        $model = $this->model->where('key','file')->field('value')->find();
        if (Request::isAjax()) {    
            $model->save(['value'=>Request::post()]);
            $this->jsonApi('保存成功');
        }
        return $this->fetch('', [
            'data' =>  $model['value']
        ]);
    }
}
