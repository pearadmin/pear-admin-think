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
        if ((new \app\admin\model\AdminAdmin)->isLogin()==false) {
            return redirect($request->server('SCRIPT_NAME').'/login/index');
         }
         (new \app\admin\model\AdminAdminLog)->record();
         return $next($request);
    }
}
