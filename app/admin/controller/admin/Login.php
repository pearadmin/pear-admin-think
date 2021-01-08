<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;
use think\captcha\facade\Captcha;
class Login extends \app\admin\controller\Base
{
    protected function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\admin\Admin;
        $this->validate =  new \app\admin\validate\admin\Admin;
    }

    /**
     * 后台登录
     */
    public function index(){
        //是否已经登录
        if ($this->model->isLogin()){
            return redirect(APP_DS_PHP);
        }
        if ($this->isAjax){
            //获取数据
            $data = $this->post;
            //验证
            if(!$this->validate->scene('login')->check($data)) 
            $this->jsonApi($this->validate->getError(),0);
            //是否存储30天
            if(!isset($data['remember'])) $data['remember'] = 0;
            if (true == $this->model->login($data['username'],$data['password'],$data['remember'])){
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
        $this->jsonApi('退出成功',200,APP_DS_PHP.'/admin.login/index');
    }
}
