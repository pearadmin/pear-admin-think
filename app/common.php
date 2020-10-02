<?php
use think\facade\Db;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use OSS\OssClient;
use OSS\Core\OssException;
// 应用公共文件

/**
 * 发送邮箱
 * @param array $data
 * @param string $addr 地址
 * @param string $title 标题
 * @param string $content 内容
 * @return mixed
 */
function go_mail($addr,$title,$content)
{
    $mail = new PHPMailer(true);
    $data = Db::name('admin_config')->column('value', 'name');
    try {
        $mail->SMTPDebug = 0;                    
        $mail->CharSet = 'utf-8';          
        $mail->isSMTP();                                     
        $mail->Host = $data['smtp-host'];  
        $mail->SMTPAuth = true;                          
        $mail->Username =  $data['smtp-user'];             
        $mail->Password =  $data['smtp-pass'];                  
        $mail->SMTPSecure = 'ssl';                            
        $mail->Port =  $data['smtp-port'];                                
        $mail->setFrom($data['smtp-user'], $data['title']);
        $mail->addAddress($addr);    
        $mail->isHTML(true);                                 
        $mail->Subject = $title;
        $mail->Body    = $content;
    return $mail->send();
        echo 'Message has been sent';
	} catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
    }
}

/**
 *阿里云上传
 */
function alYunOSS($filePath,$Extension){
    $data = Db::name('admin_config')->column('value', 'name');
    $accessKeyId =  $data['file-accessKeyId']; 
    $accessKeySecret = $data['file-accessKeySecret']; 
    $endpoint = $data['file-endpoint'];
    $bucket= $data['file-OssName'];    
    $object = 'upload/'.date("Ymd") .'/'.time() .rand(10000,99999) ."." .$Extension;    // 文件名称
    try{
        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint,true);
        $rel = $ossClient->uploadFile($bucket, $object, $filePath);
            return  ['code' => 200,'src' => $rel["info"]["url"]];
    } catch(OssException $e) {
            return ['code' => 201,'msg' => $e->getMessage()];
    }
}

if (!function_exists('get_config')) {
    /**
     * 获取系统设置
     * @param  string $config 系统设置类型
     * @return string         系统设置内容
     */
    function get_config($config)
    {
        return get_field('admin_config', ['name' => $config], 'value');
    }
}

if (!function_exists('get_field')) {
    /**
     * 获取指定表指定行指定字段
     * @param  string       $tn      完整表名
     * @param  string|array $where   参数数组或者id值
     * @param  string       $field   字段名,默认'name'
     * @param  string       $default 获取失败的默认值,默认''
     * @param  array        $order   排序数组
     * @return string                获取到的内容
     */
    function get_field($tn, $where, $field = 'name', $default = '', $order = ['id' => 'desc'])
    {
        if (!is_array($where)) {
            $where = ['id' => $where];
        }
        $row = Db::table($tn)->field([$field])->where($where)->order($order)->find();
        return $row === null ? $default : $row[$field];
    }
}

if (!function_exists('get_tree')) {
    /**
     * 递归无限级分类权限
     * @param array $data
     * @param int $pid
     * @param string $field1 父级字段
     * @param string $field2 子级关联的父级字段
     * @param string $field3 子级键值
     * @return mixed
     */
    function get_tree($data, $pid = 0, $field1 = 'id', $field2 = 'pid', $field3 = 'children')
    {
        $arr = [];
        foreach ($data as $k => $v) {
            if ($v[$field2] == $pid) {
                $v[$field3] = get_tree($data, $v[$field1]);
                $arr[] = $v;
            }
        }
        return $arr;
    }
}

if (!function_exists('set_password')) {
    //密码截取
    function set_password($password)
    {
      return substr(md5($password), 3, -3);
    }
}

if (!function_exists('md5key')) {
    /**
     *  随机数
     *
     * @param string $length 长度
     * @param string $type   类型
     * @return void
     */
    function md5key($length = '32',$type=4){
        $rand='';
        switch ($type) {
            case '1':
                $randstr= '0123456789';
                break;
            case '2':
                $randstr= 'abcdefghijklmnopqrstuvwxyz';
                break;
            case '3':
                $randstr= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            default:
                $randstr= '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }
        $max = strlen($randstr)-1;
        mt_srand((double)microtime()*1000000);
        for($i=0;$i<$length;$i++) {
        $rand.=$randstr[mt_rand(0,$max)];
        }
        return $rand;
    }
}

if (!function_exists('delete_dir')) {
    /**
     * 遍历删除文件夹所有内容
     * @param  string $dir 要删除的文件夹
     */
    function delete_dir($dir)
    {
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != '.' && $file != '..') {
                $filepath = $dir . '/' . $file;
                if (is_dir($filepath)) {
                    delete_dir($filepath);
                } else {
                    @unlink($filepath);
                }
            }
        }
        closedir($dh);
        @rmdir($dir);
    }
}

if (!function_exists('simple_encrypt')) {
    /**
     * 简单可逆加密
     * @param  string $txtStream 需要加密的数据
     * @param  string $password  键值
     * @return string       加密结果
     */
    function simple_encrypt($txtStream, $password = 'lock')
    {
        //密锁串，不能出现重复字符，内有A-Z,a-z,0-9,/,=,+,_,
        $lockstream = '1234567890/zxcvbnmZXCVBNM=asdfghjklASDFGHJKL+qwertyuiopQWERTYUIOP_';
        //密锁串长度
        $lockLen = strlen($lockstream);
        //随机找一个数字，并从密锁串中找到一个密锁值
        $lockCount = rand(0, $lockLen - 1);
        //截取随机密锁值
        $randomLock = $lockstream[$lockCount];
        //结合随机密锁值生成MD5后的密码
        $password = md5($password . $randomLock);
        //开始对字符串加密
        $txtStream = base64_encode($txtStream);
        $tmpStream = '';
        $i         = 0;
        $j         = 0;
        $k         = 0;
        for ($i = 0; $i < strlen($txtStream); $i++) {
            $k = ($k == strlen($password)) ? 0 : $k;
            $j = (strpos($lockstream, $txtStream[$i]) + $lockCount + ord($password[$k])) % ($lockLen);
            $tmpStream .= $lockstream[$j];
            $k++;
        }
        return $tmpStream . $randomLock;

    }
}

if (!function_exists('simple_decrypt')) {
    /**
     * 简单可逆解密
     * @param  string $txtStream 需要解密的数据
     * @param  string $password  键值
     * @return string       解密结果
     */
    function simple_decrypt($txtStream, $password = 'lock')
    {
        //密锁串，不能出现重复字符，内有A-Z,a-z,0-9,/,=,+,_,
        $lockstream = '1234567890/zxcvbnmZXCVBNM=asdfghjklASDFGHJKL+qwertyuiopQWERTYUIOP_';
        //密锁串长度
        $lockLen = strlen($lockstream);
        //获得字符串长度
        $txtLen = strlen($txtStream);
        //截取随机密锁值
        $randomLock = $txtStream[$txtLen - 1];
        //获得随机密码值的位置
        $lockCount = strpos($lockstream, $randomLock);
        //结合随机密锁值生成MD5后的密码
        $password = md5($password . $randomLock);
        //开始对字符串解密
        $txtStream = substr($txtStream, 0, $txtLen - 1);
        $tmpStream = '';
        $i         = 0;
        $j         = 0;
        $k         = 0;
        for ($i = 0; $i < strlen($txtStream); $i++) {
            $k = ($k == strlen($password)) ? 0 : $k;
            $j = strpos($lockstream, $txtStream[$i]) - $lockCount - ord($password[$k]);
            while ($j < 0) {
                $j = $j + ($lockLen);
            }
            $tmpStream .= $lockstream[$j];
            $k++;
        }
        return base64_decode($tmpStream);
    }
}

if (!function_exists('hump_underline')) {
    /**
     * 驼峰转下划线
     * @param  string $str 需要转换的字符串
     * @return string      转换完毕的字符串
     */
    function hump_underline($str)
    {
        return strtolower(trim(preg_replace('/[A-Z]/', '_\\0', $str), '_'));
    }
}

if (!function_exists('underline_hump')) {
    /**
     * 下划线转驼峰
     * @param  string $str 需要转换的字符串
     * @return string      转换完毕的字符串
     */
    function underline_hump($str)
    {
        return ucfirst(
            preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $str)
        );
    }
}