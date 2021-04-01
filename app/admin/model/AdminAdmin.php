<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\facade\Session;
use think\facade\Cookie;
use think\facade\Request;
use think\Model;
use think\model\concern\SoftDelete;
/**
 * @mixin \think\Model
 */
class AdminAdmin extends Model
{
    use SoftDelete;
    /**
     * 用户登录验证
     */
    public function login(array $data):bool
    {
        $token = rand_string(60);
        //验证用户
        $admin = self::where([
            'username' => trim($data['username']),
            'password' => set_password(trim($data['password'])),
            'status' => 1
         ])->find();
        if($admin){
            //是否记住密码
            Session::set('admin', [
                'id' => $admin->id,
                'token' => $token,
                'menu' => $this->permissions($admin->id)
            ]);
            if (isset($data['remember'])) {
                Cookie::set('hash', aes_encrypt($admin->id.'###'.$token),30 * 86400);
            }else{
                Cookie::set('hash', aes_encrypt($admin->id.'###'.$token),null);
            }
            $admin->token = $token;
            $admin->save();
            // 触发登录成功事件
            event('AdminLog');
            return true;
        }
        return false;
    }
    
    /**
     * 判断是否登录
     * @return bool
     */
    public function isLogin():bool
    {
        $admin = Session::get('admin');
        $hash = Cookie::get('hash');
        if (!$admin && !$hash) return false;
        //判断Session是否存在
        if (!$admin) {
            $hash = explode('###', aes_decrypt($hash));
            if (!isset($hash[1])) return false;
            $info = self::field(true)->where(['id'=>$hash[0],'token'=>$hash[1],'status'=>1])->find();
            if(!$info) return false;
            // 缓存登录信息
            $data = [
                'id' => $info->id,
                'token' => $info->token,
                'menu' => $this->permissions($info->id)
            ];
            Session::set('admin', $data);
            return true;
         }
         //判断Cookie是否存在
         if(!$hash){
            $admin = self::where(['id'=>$admin['id'],'token'=>$admin['token'],'status'=>1])->find();
            if($admin){
                $hash = aes_encrypt($admin->id.'###'.$admin->token);
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
    public function logout():bool
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
     * 管理的直接权限
     * @return \think\model\relation\BelongsToMany
     */
    public function directPermissions()
    {
        return $this->belongsToMany('AdminPermission', 'admin_admin_permission', 'permission_id', 'admin_id');
    }

    /**
     * 用户的所有权限
     */
    public function permissions($id)
    {
        $admin = self::with(['roles.permissions', 'directPermissions'])->findOrEmpty($id)->toArray();
        $permissions = [];
        //超级管理员缓存所有权限
        if ($admin['id'] == 1){
            $perms = AdminPermission::order('sort','asc')->select()->toArray();
            foreach ($perms as $p){
                if($p['status'] == 1){
                    $permissions[$p['id']] =  [
                        'id' => $p['id'],
                        'pid' => $p['pid'],
                        'title' => $p['title'],
                        'href' => Request::server('SCRIPT_NAME').$p['href'],
                        'icon' => $p['icon'],
                        'type' => $p['type'],
                        'sort' => $p['sort']
                    ];
                }
            }
        }else{
             //处理角色权限
             if (isset($admin['roles']) && !empty($admin['roles'])) {
                foreach ($admin['roles'] as $r) {
                    if (isset($r['permissions']) && !empty($r['permissions'])) {
                        foreach ($r['permissions'] as $p) {
                            if($p['status'] == 1){
                                $permissions[$p['id']] = [
                                    'id' => $p['id'],
                                    'pid' => $p['pid'],
                                    'title' => $p['title'],
                                    'href' => Request::server('SCRIPT_NAME').$p['href'],
                                    'icon' => $p['icon'],
                                    'type' => $p['type'],
                                    'sort' => $p['sort']
                                ];
                            }
                        }
                    }
                }
            }
            //处理直接权限
            if (isset($admin['directPermissions']) && !empty($admin['directPermissions'])) {
                foreach ($admin['directPermissions'] as $p) {
                    if($p['status'] == 1){
                        $permissions[$p['id']] = [
                            'id' => $p['id'],
                            'pid' => $p['pid'],
                            'title' => $p['title'],
                            'href' => Request::server('SCRIPT_NAME').$p['href'],
                            'icon' => $p['icon'],
                            'type' => $p['type'],
                            'sort' => $p['sort']
                        ];
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
