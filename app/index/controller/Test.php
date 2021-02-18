<?php
declare (strict_types = 1);

namespace app\index\controller;

use think\facade\Request;
use app\common\service\MailService;
use app\common\service\PlaybillService;
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

    /**
     * 生成海报
     */
    public function play_bill()
    {
        if (Request::isAjax()){
            $bg = get_config('web','bg');
            $res = PlaybillService::go('10001',Request::post('link'),$bg);
            $this->jsonApi($res['msg'],$res['code']);
        }
        return $this->fetch('',[
            'site' => get_config('web')
        ]);
    }

}
