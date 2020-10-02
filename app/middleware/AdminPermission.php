<?php
declare (strict_types = 1);

namespace app\middleware;

use think\facade\Session;
class AdminPermission
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
        //超级管理员不需要验证
        $admin = Session::get('admin');
        if ($admin['id'] == 1){
            return $next($request);
        }
        //验证权限
        $url = $request->baseUrl(); 
        $href = array_column(Session::get('admin.menu'), 'href');
        if (!in_array($url, $href)) {
            if ($request->isAjax()) {
                return json(['code'=>999,'msg'=>'权限不足']);
            } else {
                exit('<div style="text-align: center;"><h1>权限不足</h1></div>');
            }
         }
        return $next($request);
    }
}
