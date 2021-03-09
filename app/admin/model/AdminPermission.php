<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class AdminPermission extends Model
{
    /**
     * 子权限
     */
    public function child()
    {
        return $this->hasMany('AdminPermission','pid','id');
    }

    public function make_menu($path,$name,$pid)
    {
        $data = [
            'pid' => $pid,
            'title' => $name,
            'href' =>$path.'index',
        ];
        $menu = self::create(array_merge($data, [
            'icon'=>'layui-icon layui-icon-fire'
        ]));
        $crud = [
            'add' => "新增",
            'edit' => "修改",
            'remove' => "删除",
            'batchRemove' => "批量删除",
            'recycle' => "回收站"
        ];
        $data['pid'] = $menu['id'];
        foreach ($crud as $k=>$v) {
            $data['title'] = $v.$name;
            $data['href'] = $path.$k;
            self::create($data);
        }
    }
}
