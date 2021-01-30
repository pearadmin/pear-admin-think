<?php
declare (strict_types = 1);

namespace app\index\controller;

use think\facade\Request;
use app\common\service\MailService;

class Test extends Base
{

    /**
     * 发送邮件
     */
    public function email()
    {
        if (Request::isAjax()){
            $res = MailService::go(Request::post('email'),'你有新邮件','测试内容');
            $this->jsonApi($res['msg'],$res['code']);
        }
        return $this->fetch('',[
            'site' => get_config('web')
        ]);
    }

}
