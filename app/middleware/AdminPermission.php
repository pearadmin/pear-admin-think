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
                exit('
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                    <title>哎呀！权限不足！</title>
                    <style type="text/css">
                        html,body,div,ul,ol,li,dl,dt,dd,h1,h2,h3,h4,h5,h6,p {
                            margin: 0;
                            padding: 0;
                        }
                        h2 {
                            font-size: 30px;
                            color: #3a3a3a;
                            padding-bottom: 10px;
                        }
                        .message_tips {
                            text-align: center;
                            margin-top: 75px;
                        }
                        </style>
                    </head>
                    <body>
                        <div class="message_tips">
                            <h2>哎呀！权限不足！</h2>
                        </div>
                </body>
                </html>');
            }
         }
        return $next($request);
    }
}
