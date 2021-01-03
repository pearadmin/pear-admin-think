<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;
use think\facade\Request;
use think\facade\View;
use think\captcha\facade\Captcha;
class Login extends \app\admin\controller\Base
{
    /**
     * 后台登录
     */
    public function index(){
        $admin = new \app\admin\model\admin\Admin;
        //是否已经登录
        if ($admin->isLogin()){
            return redirect(APP_DS_PHP);
        }
        if (Request::isAjax()){
            //获取数据
            $data = Request::post();
            //验证
            $validate = new \app\admin\validate\admin\Admin;
            if(!$validate->scene('login')->check($data)) 
            $this->jsonApi($validate->getError(),0);
            //是否存储30天
            if(!isset($data['remember'])) 
            $data['remember']=0;
            if (true == $admin->login($data['username'],$data['password'],$data['remember'])){
                $this->jsonApi('登录成功');
            }
            $this->jsonApi('用户名或密码错误',0);
        }
        return View::fetch();
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
        (new \app\admin\model\admin\Admin)->logout();
        $this->jsonApi('退出成功',200,APP_DS_PHP.'/admin.login/index');
    }
}
