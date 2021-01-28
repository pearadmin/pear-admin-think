<?php
declare (strict_types = 1);

namespace app\admin\controller;
use think\facade\Db;
use think\facade\Session;
use think\facade\Request;
class Index extends \app\common\controller\AdminBase
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

    //欢迎页
    public function home(){
        return $this->fetch('',[
            'os' => PHP_OS,
            'space' => round((disk_free_space('.')/(1024*1024)),2).'M',
            'addr' => $this->request->server('SERVER_ADDR'),
            'run' =>  $this->request->server('SERVER_SOFTWARE'),
            'php' => PHP_VERSION,
            'php_run' => php_sapi_name(),
            'mysql' => function_exists('mysql_get_server_info')?mysql_get_server_info():Db::query('SELECT VERSION() as mysql_version')[0]['mysql_version'],
            'think' => $this->app->version(),
            'upload' => ini_get('upload_max_filesize'),
            'max' => ini_get('max_execution_time').'秒',
        ]);
    }

    //菜单
    public function menu(){
        $debug =  env('APP_DEBUG');
        $menu = get_tree(Session::get('admin.menu'));
        if($debug==true && Session::get('admin.id')==1){
            $menu[] = [
            "id" => -1,
            "pid" => 0,
            "title" => "CRUD生成",
            "icon" => "layui-icon layui-icon-util",
            "href" => APP_ADMIN."/crud/index",
            "type" => 1,
            ];
        }
        return json($menu);
    }
    
     //修改密码
     public function pass()
     {
         if (Request::post()){
             (new \app\admin\model\AdminAdmin)->where('id',Session::get('admin.id'))->update(['password' => set_password(trim(Request::post('password')))]);
             (new \app\admin\model\AdminAdmin)->logout();
             $this->jsonApi('修改成功',200,'/login/index');
         }
         return $this->fetch();
     }
 
     //清除缓存
     public function cache()
     {        
         $this->rm();
         $this->jsonApi('清理成功');  
     }
 
      /**
      * 通用上传
      */
     public function upload()
     {
         $file = $this->request->file();
         try {
             $this->validate($file,  [
                'file|图片' =>'fileSize:102400,fileExt:jpg,png,gif'
             ]);
             if(get_config('file-type')==2){
                 //阿里云上传
                 $savename = [];
                 foreach($file as $k) {
                     $res = alYunOSS($k, $k->extension());
                     if ($res["code"] == 201){
                         $this->jsonApi('上传失败',201,$res["msg"]);
                     }else{
                         $savename = $res['src'];
                        (new \app\admin\model\AdminPhoto)->add($k,$res['src'],2);
                     }
                 }
             }else{
                 foreach($file as $k) {
                     $savename = '/'. \think\facade\Filesystem::disk('public')->putFile( 'topic', $k);
                     $savename = str_replace("\\","/",$savename);
                     (new \app\admin\model\AdminPhoto)->add($k,$savename,1);
                 }
             }
             $this->jsonApi('上传成功', 0, ['src'=>$savename,'thumb'=>$savename]);
           } catch (\think\exception\ValidateException $e) {
             $this->jsonApi('上传失败',201,$e->getMessage());
         }
     }
}
