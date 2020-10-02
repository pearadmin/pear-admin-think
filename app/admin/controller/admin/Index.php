<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;
use think\facade\App;
use think\facade\Request;
use think\facade\Db;
use think\facade\View;
use think\facade\Session;
class Index extends Base
{
    protected $middleware = ['AdminCheck'];

    //首页
    public function index()
    {
        return View::fetch('',[
            "APP_DS_PHP" => APP_DS_PHP
        ]);
    }

    //欢迎页
    public function home(){
        return View::fetch('',[
            'os'=>PHP_OS,
            'space'=>round((disk_free_space('.')/(1024*1024)),2).'M',
            'addr'=>Request::server('SERVER_ADDR'),
            'run'=> Request::server('SERVER_SOFTWARE'),
            'php'=>PHP_VERSION,
            'php_run'=> php_sapi_name(),
            'mysql'=> function_exists('mysql_get_server_info')?mysql_get_server_info():Db::query('SELECT VERSION() as mysql_version')[0]['mysql_version'],
            'think'=> App::version(),
            'upload'=>ini_get('upload_max_filesize'),
            'max'=>ini_get('max_execution_time').'秒',
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
            "href" => APP_DS_PHP."/admin.crud/index",
            "type" => 1,
            ];
        }
        return json($menu);
    }

    //修改密码
    public function pass()
    {
        if (Request::post()){
            (new \app\admin\model\admin\Admin)->where('id',Session::get('admin.id'))->update(['password' => set_password(trim(Request::post('password')))]);
            (new \app\admin\model\admin\Admin)->logout();
            $this->jsonApi('修改成功',200,APP_DS_PHP.'/admin.login/index');
        }
        return View::fetch();
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
        $file = Request::file();
        try {
            $type = get_config('file-type');
            if($type==2){
                //阿里云上传
                validate(['image'=>'filesize:10240|fileExt:jpg|image:200,200,jpg'])
                ->check($file);
                $savename = [];
                foreach($file as $k) {
                    $res = alYunOSS($k, $k->extension());
                    if ($res["code"] == 201){
                        $this->jsonApi('上传失败',201,$res["msg"]);
                    }else{
                        $savename = $res['src'];
                        $up = new \app\admin\model\admin\Photo;
                        $up->add($k,$res['src'],2);
                    }
                }
            }else{
                validate(['image'=>'filesize:10240|fileExt:jpg|image:200,200,jpg'])
                ->check($file);
                foreach($file as $k) {
                    $savename = '/'. \think\facade\Filesystem::disk('public')->putFile( 'topic', $k);
                    $up = new \app\admin\model\admin\Photo;
                    $savename = str_replace("\\","/",$savename);
                    $up->add($k,$savename,1);
                }
            }
            $this->jsonApi('上传成功', 200, ['src'=>$savename,'thumb'=>$savename]);
        } catch (think\exception\ValidateException $e) {
            $this->jsonApi('上传失败',201,$e->getMessage());
        }
    }
}
