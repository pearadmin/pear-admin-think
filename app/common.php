<?php
use think\facade\Db;
// 应用公共文件

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

if (!function_exists('aes_encrypt')) {
  /**
   *  加密
   */
  function aes_encrypt($data, $key='lock') {
    $data = openssl_encrypt($data, 'aes-128-ecb', base64_decode($key), OPENSSL_RAW_DATA);
    return base64_encode($data);
  }
}

if (!function_exists('aes_decrypt')) {
  /**
   *  解密
   */
  function aes_decrypt($data, $key='lock') {
    $encrypted = base64_decode($data);
    return openssl_decrypt($encrypted, 'aes-128-ecb', base64_decode($key), OPENSSL_RAW_DATA);
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

if (!function_exists('get_config')) {
  /**
   * 获取系统设置
   * @param  string $config 系统设置类型
   * @return string         系统设置内容,不存在输出键数组;
   */
  function get_config($key,$value='')
  {
      $config = (new \app\common\model\SiteConfig)->getKeyValue($key);
      return isset($config[$value])?$config[$value]:$config;
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
      $row = Db::name($tn)->field([$field])->where($where)->order($order)->find();
      return $row === null ? $default : $row[$field];
  }
}

if (!function_exists('set_password')) {
    //密码截取
    function set_password($password)
    {
      return substr(md5($password), 3, -3);
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