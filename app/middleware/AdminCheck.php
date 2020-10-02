<?php
declare (strict_types = 1);

namespace app\middleware;

class AdminCheck
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        //验证登录
        if ((new \app\admin\model\admin\Admin)->isLogin()==false) {
            return redirect(APP_DS_PHP.'/admin.login/index');
         }
         (new \app\admin\model\admin\AdminLog)->record();
         return $next($request);
    }
}
