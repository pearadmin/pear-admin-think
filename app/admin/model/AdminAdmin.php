<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\facade\Session;
use think\facade\Cookie;
use think\facade\Cache;
/**
 * @mixin \think\Model
 */
class AdminAdmin extends  \app\common\model\ModelBase
{
    protected $table = 'admin_admin';
    protected $deleteTime = 'delete_time';
    /**
     * 用户登录验证
     */
    public function login($data)
    {
        $where = [
           'username' => trim($data['username']),
           'password' => set_password(trim($data['password'])),
           'status' => 1
        ];
        $token = md5key(60);
        //验证用户
        $admin = self::where($where)->find();
        if (!$admin) return false;
        //验证成功
        $admin->token = $token;
        $admin->save();
        //是否记住密码
        $hash = aes_encrypt($admin->id.'###'.$admin->password.'###'.$token);
        if (isset($data['remember'])) {
            Cookie::set('hash', $hash,30 * 86400);
        }
        Cookie::set('hash', $hash,null);
        $data = [
            'id' => $admin->id,
            'token' => $token,
            'menu' => $this->permissions($admin->id)
        ];
        Session::set('admin', $data);
        // 触发登录成功事件
        event('AdminLog');
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
            $hash = explode('###', aes_decrypt($hash?$hash:'-'));
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
            $admin = self::field(true)->where(['id'=>$admin['id'],'password'=>$admin['password'],'token'=>$admin['token'],'status'=>1])->find();
            if($admin){
                $hash = aes_encrypt($admin->id.'###'.$admin->password.'###'.$admin->token);
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
        return $this->belongsToMany('AdminRole', 'admin_admin_role', 'role_id', 'admin_id');
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
            $perms = AdminPermission::order('sort','asc')->select()->toArray();
            foreach ($perms as $p){
                if($p['status'] == 1)
                $permissions[$p['id']] =  [
                    'id' => $p['id'],
                    'pid' => $p['pid'],
                    'title' => $p['title'],
                    'href' => APP_ADMIN.$p['href'],
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
                        $permission = AdminPermission::whereIn('id',$r['permissions'])->select()->toArray();
                        foreach ($permission as $p) {
                            if($p['status'] == 1)
                            $permissions[$p['id']] = [
                                'id' => $p['id'],
                                'pid' => $p['pid'],
                                'title' => $p['title'],
                                'href' => APP_ADMIN.$p['href'],
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
