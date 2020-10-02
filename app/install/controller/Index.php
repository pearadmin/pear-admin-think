<?php
declare (strict_types = 1);
namespace app\install\controller;

use think\facade\Request;
use think\facade\View;
use think\facade\Db;
use think\exception\HttpResponseException;
use think\Response;
class Index
{
    public function index()
    {
        if (Request::isAjax()) {
            $data = Request::post();
            $username = $data['username'];
            $nickname = $data['nickname'];
            $password = $data['password'];
            if (!preg_match("/^[a-zA-Z]{1}([0-9a-zA-Z]|[._]){4,19}$/", $username)) $this->jsonApi('管理用户名：至少包含5个字符，需以字母开头',201);
            if (!preg_match("/^[\@A-Za-z0-9\!\#\$\%\^\&\*\.\~]{6,22}$/", $password)) $this->jsonApi('登录密码至少包含6个字符。可使用字母，数字和符号',201);
            $dbhost = $data['host'];
            $dbuser = $data['user'];
            $dbpass = $data['pass'];
            $dbport = $data['port'];
            $dbname = $data['name'];
			try { 
				$conn = new \PDO("mysql:host=$dbhost", $dbuser, $dbpass); 
			} catch(\PDOException $e) { 
                $this->jsonApi("数据库信息错误",201); 
            }
            $sql = 'CREATE DATABASE IF NOT EXISTS '.$dbname.' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci'; 
			$conn->exec($sql); 
            $conn = null;
			try { 
				$db = new \PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass); 
			} catch(\PDOException $e) { 
                $this->jsonApi("数据库连接失败",201); 
            }
			$res = self::createTables($db);
			if(!$res) $this->jsonApi("数据表创建失败",201); 
            $pass = set_password($data['password']);
            Db::name('admin_admin')->where('id',1)->update(['username'=>$username,'nickname'=>$nickname,'password'=>$pass]);
            $db = null;
            $db_str = "
            <?php
            return [
                // 默认使用的数据库连接配置
                'default'         => env('database.driver', 'mysql'),
            
                // 自定义时间查询规则
                'time_query_rule' => [],
            
                // 自动写入时间戳字段
                // true为自动识别类型 false关闭
                // 字符串则明确指定时间字段类型 支持 int timestamp datetime date
                'auto_timestamp'  => true,
            
                // 时间字段取出后的默认时间格式
                'datetime_format' => 'Y-m-d H:i:s',
            
                // 数据库连接配置信息
                'connections'     => [
                    'mysql' => [
                        // 数据库类型
                        'type'              => env('database.type', 'mysql'),
                        // 服务器地址
                        'hostname'          => env('database.hostname', '{$dbhost}'),
                        // 数据库名
                        'database'          => env('database.database', '{$dbname}'),
                        // 用户名
                        'username'          => env('database.username', '{$dbuser}'),
                        // 密码
                        'password'          => env('database.password', '{$dbpass}'),
                        // 端口
                        'hostport'          => env('database.hostport', '{$dbport}'),
                        // 数据库连接参数
                        'params'            => [],
                        // 数据库编码默认采用utf8
                        'charset'           => env('database.charset', 'utf8'),
                        // 数据库表前缀
                        'prefix'            => env('database.prefix', ''),
            
                        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
                        'deploy'            => 0,
                        // 数据库读写是否分离 主从式有效
                        'rw_separate'       => false,
                        // 读写分离后 主服务器数量
                        'master_num'        => 1,
                        // 指定从服务器序号
                        'slave_no'          => '',
                        // 是否严格检查字段是否存在
                        'fields_strict'     => true,
                        // 是否需要断线重连
                        'break_reconnect'   => false,
                        // 监听SQL
                        'trigger_sql'       => env('app_debug', true),
                        // 开启字段缓存
                        'fields_cache'      => false,
                        // 字段缓存路径
                        'schema_cache_path' => app()->getRuntimePath() . 'schema' . DIRECTORY_SEPARATOR,
                    ],
            
                    // 更多的数据库配置信息
                ],
            ];            
            ";
            $fp = fopen(root_path()."config/database.php","w");
            $res = fwrite($fp, $db_str);
            fclose($fp);
            if(!$res){
                $this->jsonApi('数据库配置文件创建失败！',201);
            }
            @touch(public_path().'install.lock');
            $this->jsonApi('安装成功');
        }
        return View::fetch();
    }

    private function createTables($db) 
    {
        $sql = file_get_contents('../app/install/data/data.sql');
        if ($sql) {
            $sql_array = preg_split("/;[\r\n]+/", $sql);
            foreach ($sql_array as $k => $v) {
                if (!empty($v)) {
                    if (substr($v, 0, 12) == 'CREATE TABLE') {
                            $name = preg_replace("/^CREATE TABLE `(\w+)` .*/s", "\\1", $v);
                            $msg = "创建数据表{$name}";
                            $res = $db->query($v);
                            if ($res == false) {
                                $this->jsonApi($msg.'失败',201);
                            }
                    } else {
                        $res = $db->query($v);
                        if ($res == false) {
                            $this->jsonApi('数据插入失败',201);
                        }
                    }
                }
            }
        } else {
            return false;
        }
        return true; 
    }

    /**
     * 返回API
     * @access protected
     * @param  string  $msg    提示信息
     * @param  integer $code   状态码
     * @param  array   $data   对应数据
     * @param  array   $extend 扩展字段
     * @param  array   $header HTTP头信息
     * @return void
     * @throws HttpResponseException
     */
    function jsonApi($msg = '', $code = 200, $data = [], $extend = [], $header = [])
    {
        $return = [
            'msg'  => $msg,
            'code' => $code,
        ];
        if (!empty($data)) {
            $return['data'] = $data;
        }
        if (!empty($extend)) {
            foreach ($extend as $k => $v) {
                $return[$k] = $v;
            }
        }
        $response = Response::create($return, 'json')->header($header);
        throw new HttpResponseException($response);
    }
 
}
