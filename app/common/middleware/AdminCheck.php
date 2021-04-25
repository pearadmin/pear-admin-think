<?php
declare (strict_types = 1);

namespace app\common\middleware;

use app\common\service\AdminAdmin as S;

class AdminCheck
{
    /**
     * 处理请求
     */
    public function handle($request, \Closure $next)
    {
        return S::isLogin()?$next($request):redirect($request->root().'/login/index');
    }
}
