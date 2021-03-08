<?php
declare (strict_types = 1);

namespace app\admin\controller;
use think\captcha\facade\Captcha;
use think\facade\Request;
class Login extends Base
{
    /**
     * 初始化
     */
    protected function initialize()
    {
        $this->model = new \app\admin\model\AdminAdmin;
        $this->validate =  new \app\admin\validate\AdminAdmin;
    }

    /**
     * 后台登录
     */
    public function index(){
        //是否已经登录
        if ($this->model->isLogin()){
            return redirect(Request::server('SCRIPT_NAME'));
        }
        if (Request::isAjax()){
            //获取数据
            $data = Request::param();
            //验证
            if(!$this->validate->scene('login')->check($data)) 
            $this->jsonApi($this->validate->getError(),0);
            if (true == $this->model->login($data)){
                $this->jsonApi('登录成功');
            }
            $this->jsonApi('用户名或密码错误',0);
        }
        return $this->fetch();
    }

    /**
     * 验证码
     */
    public function verify(){
        ob_clean();
        return Captcha::create();   
    }

    /**
     * 退出登陆
     */
    public function logout(){
        $this->model->logout();
        $this->jsonApi('退出成功',200,Request::server('SCRIPT_NAME').'/login/index');
    }
}
