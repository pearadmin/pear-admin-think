<?php
declare (strict_types = 1);

namespace app\admin\model\admin;

use think\Model;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Cache;
use think\model\concern\SoftDelete;
/**
 * @mixin \think\Model
 */
class Admin extends Model
{
    protected $table = 'admin_admin';
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    /**
     * 用户登录验证
     */
    public function login($username = '', $password = '', $remember = '')
    {
        $data = [
           'username' => trim($username),
           'password' => set_password(trim($password)),
           'status' => 1
        ];
        $token = md5key(60);
        //验证用户
        $admin = self::where($data)->find();
        if (!$admin) return false;
        //验证成功
        $admin->token = $token;
        $admin->save();
        //是否记住密码
        $hash = simple_encrypt($admin->id.'###'.$admin->password.'###'.$token);
        if ($remember==1) {
            Cookie::set('hash', $hash,30 * 86400);
        } else {
            Cookie::set('hash', $hash,null);
        }
        // 缓存信息
        $data = [
            'id' => $admin->id,
            'username' => $admin->username,
            'nickname' => $admin->nickname,
            'password' => $admin->password,
            'token' => $token,
            'menu' => $this->permissions($admin->id)
        ];
        Session::set('admin', $data);
        // 触发登录成功事件
        event('AdminLogin');
        return true;
    }
    
    /**
     * 判断是否登录
     * @return bool|array
     */
    public function isLogin()
    {
        $admin = Session::get('admin');
        $hash = Cookie::get('hash');
        if (!$admin && !$hash) return false;
        //判断Session是否存在
        if (!$admin) {
            $hash = explode('###', simple_decrypt($hash?$hash:'-'));
            if (!isset($hash[1]) && !isset($hash[2])) return false;
            $info = self::field(true)->where(['id'=>$hash[0],'password'=>$hash[1],'token'=>$hash[2],'status'=>1])->find();
            if(!$info) return false;
            // 缓存登录信息
            $data = [
                'id' => $info->id,
                'username' => $info->username,
                'nickname' => $info->nickname,
                'password' => $info->password,
                'token' => $info->token,
                'menu' => $this->permissions($info->id)
            ];
            Session::set('admin', $data);
            return true;
         }
         //判断Cookie是否存在
         if(!$hash){
            $info = self::field(true)->where(['id'=>$admin['id'],'password'=>$admin['password'],'token'=>$admin['token'],'status'=>1])->find();
            if($info){
                $hash = simple_encrypt($info->id.'###'.$info->password.'###'.$info->token);
                Cookie::set('hash', $hash,null);
               return true;
            }
         }
        return true;
    }

     /**
     * 退出登陆
     * @return bool
     */
    public function logout()
    {
        Session::delete('admin');
        Cookie::delete('hash');
        return true;
    }
    
    /**
     * 管理拥有的角色
     */
    public function roles()
    {
        return $this->belongsToMany('Role', 'admin_admin_role', 'role_id', 'admin_id');
    }

    /**
     * 用户的所有权限
     */
    public function permissions($id)
    {
        $admin = self::with('roles')->find($id);
        $permissions = [];
        //超级管理员缓存所有权限
        if ($admin['id'] == 1){
            $perms = Permission::order('sort','asc')->select();
            foreach ($perms as $p){
                if($p['status'] == 1)
                $permissions[$p['id']] = [
                    'id' => $p['id'],
                    'pid' => $p['pid'],
                    'title' => $p['title'],
                    'href' => APP_DS_PHP.$p['href'],
                    'icon' => 'layui-icon '.$p['icon'],
                    'type' => $p['type'],
                    'sort' => $p['sort']
                ];
            }
        }else{
            //处理权限
            if (isset($admin['roles']) && !empty($admin['roles'])) {
                foreach ($admin['roles'] as $r) {
                    if ($r['permissions']) {
                        $permission = Permission::whereIn('id',$r['permissions'])->select();
                        foreach ($permission as $p) {
                            if($p['status'] == 1)
                            $permissions[$p['id']] = [
                                'id' => $p['id'],
                                'pid' => $p['pid'],
                                'title' => $p['title'],
                                'href' => APP_DS_PHP.$p['href'],
                                'icon' => 'layui-icon '.$p['icon'],
                                'type' => $p['type'],
                                'sort' => $p['sort']
                            ];
                        }
                    }
                }
            }
            $key =  array_column( $permissions, 'sort');
            array_multisort($key, SORT_ASC, $permissions);
        }
        //合并权限为用户的最终权限
        return $permissions;
    }
}
