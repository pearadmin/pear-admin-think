<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\facade\Db;
use think\facade\Session;
use think\facade\Request;
use app\common\service\UploadService;
class Index extends Base
{
    protected $middleware = ['AdminCheck'];
    
    /**
     * 首页
     */
    public function index()
    {
        return $this->fetch('',[
            'nickname'  => get_field('admin_admin',Session::get('admin.id'),'nickname')
        ]);
    }

     //清除缓存
     public function cache()
     {        
         $this->rm();
         $this->jsonApi('清理成功');  
     }

    //菜单
    public function menu(){
        $menu = get_tree(Session::get('admin.menu'));
        if(env('APP_DEBUG')==true && Session::get('admin.id')==1){
            $menu[] = [
                "id" => -1,
                "pid" => 0,
                "title" => "CRUD",
                "icon" => "layui-icon layui-icon-util",
                "href" => Request::server('SCRIPT_NAME')."/admin.crud/index",
                "type" => 1,
            ];
        }
        return json($menu);
    }

    //欢迎页
    public function home(){
        return $this->fetch('',[
            'os' => PHP_OS,
            'space' => round((disk_free_space('.')/(1024*1024)),2).'M',
            'addr' =>$_SERVER['HTTP_HOST'],
            'run' =>  Request::server('SERVER_SOFTWARE'),
            'php' => PHP_VERSION,
            'php_run' => php_sapi_name(),
            'mysql' => function_exists('mysql_get_server_info')?mysql_get_server_info():Db::query('SELECT VERSION() as mysql_version')[0]['mysql_version'],
            'think' => $this->app->version(),
            'upload' => ini_get('upload_max_filesize'),
            'max' => ini_get('max_execution_time').'秒',
        ]);
    }

     //修改密码
     public function pass()
     {
         if ($data = Request::post()){
            $validate =  new \app\admin\validate\AdminAdmin;
            if(!$validate->scene('pass')->check($data)) 
            $this->jsonApi($validate->getError(),0);
            $admin = new \app\admin\model\AdminAdmin;
            $admin->where('id',Session::get('admin.id'))->update(['password' => set_password(trim($data['password']))]);
            $admin->logout();
            $this->jsonApi('修改成功',200,Request::server('SCRIPT_NAME').'/login/index');
         }
         return $this->fetch();
     }

     /**
      * 通用上传
      */
      public function upload()
      {
         $res = UploadService::commonFile($this->request->file());
         $this->jsonApi($res['msg'],$res['code'],$res['data']);
      }

}
