<?php
declare (strict_types = 1);

namespace app\admin\controller;

use think\facade\Db;
use app\admin\model\AdminPermission;
use think\facade\Request;
class Crud extends   \app\common\controller\AdminBase
{
    protected $middleware = ['AdminCheck','AdminPermission'];

    /**
     * 列表
     */
    public function index()
    {
        if (Request::isAjax()) {
            $name = input('get.name');
            $sql = Db::query('SELECT COLUMN_NAME,IS_NULLABLE,DATA_TYPE,IF(COLUMN_COMMENT = "",COLUMN_NAME,COLUMN_COMMENT) COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_NAME = "' . $name . '"order by ORDINAL_POSITION asc');
            $this->jsonApi('', 0, $sql);
        }
        return $this->fetch('',[
            'list' =>Db::getTables()
        ]);
    }

    /**
     * 新增基础表
     */
    public function addBase()
    {
        if (Request::isAjax()) {
            $name = Request::post('name');
            $desc = Request::post('desc');
                $sql = '
DROP TABLE IF EXISTS `'.$name.'`;
CREATE TABLE `'.$name.'` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "id",
`create_time` timestamp NULL DEFAULT NULL COMMENT "更新时间",
`update_time` timestamp NULL DEFAULT NULL COMMENT "创建时间",
`delete_time` timestamp NULL DEFAULT NULL COMMENT "删除时间",
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT="'.$desc.'";
                ';
                $sql_array = preg_split("/;[\r\n]+/", $sql);
                foreach ($sql_array as $k => $v) {
                    if (substr($v, 0, 12) == 'CREATE TABLE') {
                            Db::query($v);
                            $this->jsonApi('创建成功');
                    }
                }
            }
        return $this->fetch();
    }

    /**
     * 删除
     */
    public function del()
    {
        $data = Request::post();
        //验证
        try {
            $this->validate($data,  [
               'name' =>'notIn:admin_admin,admin_admin_log,admin_admin_role,site_config,admin_permission,admin_photo,admin_role'
            ]);
        }catch (\Exception $e){
            $this->jsonApi('系统内置禁止操作',201);
        }
        if($data['type']==true){
            Db::query('drop table '.$data["name"].'');
        }
        try {
            // 完整表名
            $name = $data['name'];
            // 表名尾转驼峰
            $head = underline_hump(strstr($name , '_',true));   
            // 表名尾转驼峰
            $tail = underline_hump(substr($name,strlen($head)+1));
            // 控制器，模型，验证器文件名称
            $app = $head.$tail;
            // 控制器
            $controller = app_path().'controller'.DS.$app.'.php';
            if (file_exists($controller)) unlink($controller);
            // 模型
            $model = root_path().'app'.DS.'common'.DS.'model'.DS.$app.'.php';
            if (file_exists($model)) unlink($model);
            // 验证器
            $validate = root_path().'app'.DS.'common'.DS.'validate'.DS.$app.'.php';
            if (file_exists($validate)) unlink($validate);
            //删除视图目录
            $view = root_path().'view'.DS.'admin'.DS.$name;
            if (file_exists($view)) delete_dir($view);
            //删除JS目录
            $js = public_path().'static'.DS.'admin'.DS.'js'.DS.$name;
            if (file_exists($js)) delete_dir($js);
             //删除菜单
             AdminPermission::where('href', 'like', "%" . $name . "%")->delete();
             //清空缓存
            $this->rm();
         }catch (\Exception $e){
             $this->jsonApi('删除失败',201, $e->getMessage());
         }
         $this->jsonApi('删除成功');
    }
    /**
     * 生成
     */
    public function crud($name)
    {
        $sql = Db::query('SELECT COLUMN_NAME,IS_NULLABLE,DATA_TYPE,IF(COLUMN_COMMENT = "",COLUMN_NAME,COLUMN_COMMENT) COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_NAME = "' . $name . '" AND COLUMN_NAME <> "id" order by ORDINAL_POSITION asc');
        if (Request::isAjax()) {
            $data = Request::param();
            //验证
            try {
                $this->validate($data,  [
                   'name' =>'notIn:admin_admin,admin_admin_log,admin_admin_role,admin_config,admin_permission,admin_photo,admin_role'
                ]);
            }catch (\Exception $e){
                $this->jsonApi('系统内置禁止操作',201);
            }
            $array = array_merge($data['sql-edit']??[],$data['sql-photo']??[]);
            if (count($array) != count(array_unique($array)))  $this->jsonApi('特殊字段重复设置', 201);
            //构造crud
            $this->crudData($data,$sql);
            $crud = [
                $this->getController(), 
                $this->getModel(),
                $this->getValidate(), 
                $this->getAddHtml(),
                $this->getEditHtml(), 
                $this->getIndexHtml(),
                $this->getIndexJs(),
                $this->getRecycleHtml(),
                $this->getRecycleJs()
            ];
            if(isset( $this->crud['menu'])){
                $menu = AdminPermission::create($this->crud['menu']);
                if($menu){
                     $this->crud['menu']['pid'] = $menu['id'];
                     //添加
                     $this->crud['menu']['title'] = '添加'. $this->crud['cname'];
                     $this->crud['menu']['href'] = '/'.$this->crud['head'].'_'.$this->crud['tail'].'/'.'add';
                     AdminPermission::create($this->crud['menu']);
                     //编辑
                     $this->crud['menu']['title'] = '编辑'. $this->crud['cname'];
                     $this->crud['menu']['href'] = '/'.$this->crud['head'].'_'.$this->crud['tail'].'/'.'edit';
                     AdminPermission::create($this->crud['menu']);
                     //删除
                     $this->crud['menu']['title'] = '删除'. $this->crud['cname'];
                     $this->crud['menu']['href'] = '/'.$this->crud['head'].'_'.$this->crud['tail'].'/'.'del';
                     AdminPermission::create($this->crud['menu']);
                     //选中删除
                     $this->crud['menu']['title'] = '选中删除'. $this->crud['cname'];
                     $this->crud['menu']['href'] = '/'.$this->crud['head'].'_'.$this->crud['tail'].'/'.'delall';
                     AdminPermission::create($this->crud['menu']);
                     //回收站
                     $this->crud['menu']['title'] = '回收站'. $this->crud['cname'];
                     $this->crud['menu']['href'] = '/'.$this->crud['head'].'_'.$this->crud['tail'].'/'.'recycle';
                     AdminPermission::create($this->crud['menu']);
                     $this->rm();
                  }
            }
            try {
                foreach ($crud as $v) {
                    @mkdir(dirname($v[0]), 0755, true);
                    @file_put_contents($v[0], $v[1]);
                }
            }catch (\Exception $e){
                $this->jsonApi('操作失败',201, $e->getMessage());
            }
            $this->jsonApi('操作成功');
        }
        //表数据
        return $this->fetch('',[
            'info' => $sql,
            'permissions' => get_tree(AdminPermission::order('sort','asc')->select()->toArray()),
            'desc' => Db::query('SELECT TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_NAME = "' . $name . '"')[0]['TABLE_COMMENT']
        ]);
    }

    private function crudData($data,$sql)
    {
        // 完整表名
        $this->crud['name'] = $data['name'];
        // 中文名称
        $this->crud['cname'] = $data['cname'];
        // 表字段数据
        $this->crud['info'] = $sql;
        // 头
        $this->crud['head'] = strstr($this->crud['name'] , '_',true);
        //尾
        $this->crud['tail'] = substr($this->crud['name'],strlen($this->crud['head'])+1);
        // 表名尾转驼峰
        $this->crud['heads'] = underline_hump($this->crud['head']);
        // 表名尾转驼峰
        $this->crud['tails'] = underline_hump($this->crud['tail']);
        // 控制器，模型，验证器文件名称
        $this->crud['app'] =  $this->crud['heads'].$this->crud['tails'];
        // 菜单自动生成
        if($data['menu-type'] == '1'){
            $this->crud['menu'] = [
                'pid' => $data['menu-pid'],
                'title' => $this->crud['cname'].'列表',
                'href' => '/'.$this->crud['head'].'_'.$this->crud['tail'].'/'.'index',
                'icon' => $data['menu-icon'],
                'sort' => $data['menu-sort']
            ];
        }
        //字段设置
        $this->crud['edit'] = $data['sql-edit']??[];
        $this->crud['photo'] = $data['sql-photo']??[];
        $this->crud['search'] = $data['sql-search']??[];
    }

    private function getController()
    {
        $file = app_path().'controller'.DS.$this->crud['app'].'.php';
        $search = '';
        foreach($this->crud['search'] as $k=>$v){
        $i = explode('###',$v);
        if(strstr($i[0],"time")){
            $search .= '
            //按'.$i[1].'查找
            $start = input("get.'.$i[0].'-start");
            $end = input("get.'.$i[0].'-end");
            if ($start && $end) {
                $this->where[]=["'.$i[0].'","between",[$start,date("Y-m-d",strtotime("$end +1 day"))]];
            }';
            }else{
            $search .= '
            //按'.$i[1].'查找
            if ($'.$i[0].' = input("'.$i[0].'")) {
                $this->where[] = ["'.$i[0].'", "like", "%" . $'.$i[0].' . "%"];
            }';
        }
        }
        $content = str_replace(['{{$app}}','{{$search}}'], [$this->crud['app'],$search], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'controller.php.tpl'));
        return [$file, $content];
    }

    private function getModel()
    {
        $file = root_path().'app'.DS.'common'.DS.'model'.DS.$this->crud['app'].'.php';
        $del = '';
        foreach ($this->crud['info'] as $k) {
            //软删除字段
            if ($k['COLUMN_NAME'] == 'delete_time'){
                $del = 'protected $deleteTime = "delete_time";';
            }else{
                $del = 'protected $deleteTime = false;';
            }
        }
        $content = str_replace(['{{$name}}', '{{$app}}', '{{$del}}'], [$this->crud['name'], $this->crud['app'],$del], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'model.php.tpl'));
        return [$file, $content];
    }

    private function getValidate()
    {
        $file = root_path().'app'.DS.'common'.DS.'validate'.DS.$this->crud['app'].'.php';
        $rule    = '';
        $message = '';
        $scene   = '';
        foreach ($this->crud['info'] as $k) {
            if (!in_array($k['COLUMN_NAME'], ['create_time', 'update_time','delete_time'])) {
            //判断状态
            if ($k['IS_NULLABLE'] === 'NO') {
                    $rule .= '
           \'' . $k['COLUMN_NAME'] . '\' => \'require';
                    $message .= '
            \'' . $k['COLUMN_NAME'] . '.require\' => \'' . $k['COLUMN_COMMENT'] . '为必填项\',';
            if (in_array($k['DATA_TYPE'], ['int', 'decimal', 'float', 'double'])) {
                $rule .= '|number';
                $message .= '
            \'' . $k['COLUMN_NAME'] . '.number\' => \'' . $k['COLUMN_COMMENT'] . '需为数字\',';
            }
                    $rule .= '\',';
                    $scene .= '\'' . $k['COLUMN_NAME'] . '\',';
                }
            }
        }
        $content = str_replace(['{{$name}}', '{{$app}}', '{{$rule}}', '{{$message}}', '{{$scene}}'], [$this->crud['name'], $this->crud['app'], $rule, $message, $scene], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'validate.php.tpl'));
        return [$file, $content];
    }

    private function getAddHtml()
    {
        $file = root_path().'view'.DS.'admin'.DS.$this->crud['name'].DS."add.html";
        $columns = '';
        $contentjs = '    
        layedit.set({
            uploadImage: {
                url: "{:app_admin()}/index/upload"
            }
        });
        //建立编辑器'
        ;
        $content = '';
        foreach ($this->crud['info'] as $k) {
            if (!in_array($k['COLUMN_NAME'], ['create_time', 'update_time','delete_time'])) {
                $columns .= '
            <div class="layui-form-item">
                <label class="layui-form-label">
                    ' . $k['COLUMN_COMMENT'] . '
                </label>
                <div class="layui-input-block">
                    ';
                $lay_verify = '';
                //判断图片
                $_photo = [];
                if(!empty($this->crud['photo'])){
                    foreach($this->crud['photo'] as $p){
                        $_photo[]= explode('###',$p)[0];
                    }
                }
                //判断编辑器
                $_edit = [];
                if(!empty($this->crud['edit'])){
                    foreach($this->crud['edit'] as $e){
                        $_edit[]= explode('###',$e)[0];
                    }
                } 
                if (in_array($k['COLUMN_NAME'], $_photo)) {
                    if ($k['IS_NULLABLE'] === 'NO') {
                        $lay_verify = ' lay-verify="uploadimg"';
                    }
                    $columns .= '<button class="layui-btn layui-btn-sm upload-image" type="button">
                        <i class="fa fa-image">
                        </i>
                        上传图片
                    </button>
                    <input' . $lay_verify . ' name="' .  $k['COLUMN_NAME'] . '" type="hidden"/>
                    <div class="upload-image">
                        <span>
                        </span>
                        <img class="upload-image" src=""/>
                    </div>';

                }elseif(in_array($k['COLUMN_NAME'], $_edit)) {

                    $columns .= '<textarea id="' .$k['COLUMN_NAME'] . '" name="' . $k['COLUMN_NAME'] . '" type="text/plain" style="width:100%;margin-bottom:20px;"></textarea>';
                    
                    $contentjs .= '
                    var '.$k['COLUMN_NAME'].'  = layedit.build("'.$k['COLUMN_NAME'].'", {
                    height: 400 //设置编辑器的高度
                    });';

                    $content .= '
                        data.field.'.$k['COLUMN_NAME'].'=layedit.getContent('.$k['COLUMN_NAME'].');
                    ';

                }else{
                    if($k['IS_NULLABLE'] === 'NO') {
                        $lay_verify = ' lay-verify="required ';
                        if (in_array($k['DATA_TYPE'], ['int', 'decimal', 'float', 'double'])) {
                            $lay_verify .= '|number';
                        }
                        $lay_verify .= '"';
                    }
                    $columns .= '<input type="text" class="layui-input layui-form-danger"' . $lay_verify . ' name="' . $k['COLUMN_NAME'] . '" type="text"/>';
                }
                $columns .= '
                </div>
            </div>';
            }
        }
        $content = str_replace(['{{$columns}}','{{$contentjs}}','{{$content}}'], [$columns,$contentjs,$content], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'view.add.html.tpl'));
        return [$file, $content];
    }

    private function getEditHtml()
    {
        $file = root_path().'view'.DS.'admin'.DS.$this->crud['name'].DS."edit.html";
        $columns = '';
        $contentjs = '    
        layedit.set({
            uploadImage: {
                url: "{:app_admin()}/index/upload"
            }
        });
        //建立编辑器'
        ;
        $content = '';
        foreach ($this->crud['info'] as $k) {
            if (!in_array($k['COLUMN_NAME'], ['create_time', 'update_time','delete_time'])) {
                $columns .= '
    <div class="layui-form-item">
        <label class="layui-form-label">
            ' . $k['COLUMN_COMMENT'] . '
        </label>
        <div class="layui-input-block">
            ';
                $lay_verify = '';
                //判断图片
                $_photo = [];
                if(!empty($this->crud['photo'])){
                    foreach($this->crud['photo'] as $p){
                        $_photo[]= explode('###',$p)[0];
                    }
                }
                //判断编辑器
                $_edit = [];
                if(!empty($this->crud['edit'])){
                    foreach($this->crud['edit'] as $e){
                        $_edit[]= explode('###',$e)[0];
                    }
                } 
               if (in_array($k['COLUMN_NAME'], $_photo)) {
                   if($k['IS_NULLABLE'] === 'NO') {
                       $lay_verify = ' lay-verify="uploadimg"';
                   }
                   $columns .= '<button class="layui-btn layui-btn-sm upload-image" type="button">
                   <i class="fa fa-image">
                   </i>
                   上传图片
               </button>
               <input' . $lay_verify . ' name="' . $k['COLUMN_NAME'] . '" type="hidden" value="{$data[\'' . $k['COLUMN_NAME'] . '\']}"/>
               <div class="upload-image">
                   <span>
                   </span>
                   <img class="upload-image" src="{$data[\'' . $k['COLUMN_NAME'] . '\']}"/>
               </div>';

               }elseif (in_array($k['COLUMN_NAME'], $_edit)) {
                   
                    $columns .= '<textarea id="' .$k['COLUMN_NAME'] . '" name="' . $k['COLUMN_NAME'] . '" type="text/plain" style="width:100%;margin-bottom:20px;">{$data[\'' . $k['COLUMN_NAME'] . '\']}</textarea>';
                
                    $contentjs .=  '
                    var '.$k['COLUMN_NAME'].'  = layedit.build("'.$k['COLUMN_NAME'].'", {
                    height: 400 //设置编辑器的高度
                    });';

                    $content .= '
                        data.field.'.$k['COLUMN_NAME'].' =layedit.getContent('.$k['COLUMN_NAME'].');
                    ';

               }else{
                    if($k['IS_NULLABLE'] === 'NO') {
                        $lay_verify = ' lay-verify="required ';
                        if (in_array($k['DATA_TYPE'], ['int', 'decimal', 'float', 'double'])) {
                            $lay_verify .= '|number';
                        }
                        $lay_verify .= '"';
                    }
                    $columns .= '<input type="text" class="layui-input layui-form-danger"' . $lay_verify . ' name="' . $k['COLUMN_NAME'] . '" type="text" value="{$data[\'' . $k['COLUMN_NAME'] . '\']}"/>';
               }
               $columns .= '
               </div>
           </div>';
           }
       }
        $content = str_replace(['{{$columns}}','{{$contentjs}}','{{$content}}'], [$columns,$contentjs,$content],  file_get_contents(root_path().'extend'. DS .'tpl'. DS .'view.edit.html.tpl'));
        return [$file, $content];
    }

    private function getIndexHtml()
    {
        $file = root_path().'view'.DS.'admin'.DS.$this->crud['name'].DS."index.html";
        $searchs = '';
            if(!empty($this->crud['search'])){
                $searchs .= ' <div class="layui-card">
                <div class="layui-card-body">
                    <form class="layui-form" action="">
                    <div class="layui-form-item">';
                foreach($this->crud['search'] as $p){
                    $_search= explode('###',$p);
                    if(strstr($_search[0],"time")){
                        $searchs .= '   
                        <div class="layui-form-item layui-inline">
                            <label class="layui-form-label">'.$_search[1].'</label>
                            <div class="layui-input-inline">
                            <input type="text" class="layui-input" id="'.$_search[0].'-start" name="'.$_search[0].'-start" placeholder="开始时间" autocomplete="off">
                        </div>
                        <div class="layui-input-inline">
                            <input type="text" class="layui-input" id="'.$_search[0].'-end" name="'.$_search[0].'-end" placeholder="结束时间" autocomplete="off">
                        </div>
                    </div>';
                       }else{
                        $searchs .= '   
                        <div class="layui-form-item layui-inline">
                            <label class="layui-form-label">'.$_search[1].'</label>
                            <div class="layui-input-inline">
                                <input type="text" name="'.$_search[0].'" placeholder="" class="layui-input">
                            </div>
                        </div>';
                       }
                    }
                    $searchs .= '     
                    <div class="layui-form-item layui-inline">
                        <button class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="query">
                            <i class="layui-icon layui-icon-search"></i>
                            查询
                        </button>
                        <button type="reset" class="pear-btn pear-btn-md">
                            <i class="layui-icon layui-icon-refresh"></i>
                            重置
                        </button>
                        </div>
                </form>
            </div>
        </div>';
        }
        $content = str_replace(['{{$name}}','{{searchs}}'],  [$this->crud['name'],$searchs], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'view.index.html.tpl'));
        return [$file, $content];
    }

    private function getIndexJs()
    {
        $file = public_path().'static'.DS.'admin'.DS.'js'.DS.$this->crud['name'].DS."index.js";
        $columns = '';
        foreach ($this->crud['info'] as $k) {
        if (!in_array($k['COLUMN_NAME'], ['delete_time'])) {
            $columns .= '{
                field: \'' . $k['COLUMN_NAME'] . '\',
                title: \'' . $k['COLUMN_COMMENT'] . '\',
                unresize: true,
                align: \'center\'
            }, ';
            }
        }
        $searchs = '';
        if(!empty($this->crud['search'])){
            foreach($this->crud['search'] as $p){
                $_search= explode('###',$p);
                if(strstr($_search[0],"time")){
                $searchs .= ' 
                laydate.render({
                    elem: "#'.$_search[0].'-start"
                });
                laydate.render({
                    elem: "#'.$_search[0].'-end"
                })';
                }
            }
        }
        $content = str_replace(['{{$name}}','{{$cname}}','{{$columns}}','{{$searchs}}'], [$this->crud['name'],$this->crud['cname'],$columns,$searchs], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'index.js.tpl'));
        return [$file, $content];
    }

    private function getRecycleHtml()
    {
        $file = root_path().'view'.DS.'admin'.DS.$this->crud['name'].DS."recycle.html";
        $searchs = '';
            if(!empty($this->crud['search'])){
                $searchs .= ' <div class="layui-card">
                <div class="layui-card-body">
                    <form class="layui-form" action="">
                    <div class="layui-form-item">';
                foreach($this->crud['search'] as $p){
                    $_search= explode('###',$p);
                    if(strstr($_search[0],"time")){
                    $searchs .= '   
                    <div class="layui-form-item layui-inline">
                        <label class="layui-form-label">'.$_search[1].'</label>
                        <div class="layui-input-inline">
                        <input type="text" class="layui-input" id="'.$_search[0].'-start" name="'.$_search[0].'-start" placeholder="开始时间" autocomplete="off">
                    </div>
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input" id="'.$_search[0].'-end" name="'.$_search[0].'-end" placeholder="结束时间" autocomplete="off">
                    </div>
                </div>';
                    }else{
                    $searchs .= '   
                    <div class="layui-form-item layui-inline">
                        <label class="layui-form-label">'.$_search[1].'</label>
                        <div class="layui-input-inline">
                            <input type="text" name="'.$_search[0].'" placeholder="" class="layui-input">
                        </div>
                    </div>';
                    }
                    }
                    $searchs .= '     
                    <div class="layui-form-item layui-inline">
                        <button class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="query">
                            <i class="layui-icon layui-icon-search"></i>
                            查询
                        </button>
                        <button type="reset" class="pear-btn pear-btn-md">
                            <i class="layui-icon layui-icon-refresh"></i>
                            重置
                        </button>
                        </div>
                </form>
            </div>
        </div>';
        }
        $content = str_replace(['{{$name}}','{{searchs}}'],  [$this->crud['name'],$searchs], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'view.recycle.html.tpl'));
        return [$file, $content];
    }

    private function getRecycleJs()
    {
        $file = public_path().'static'.DS.'admin'.DS.'js'.DS.$this->crud['name'].DS."recycle.js";
        $columns = '';
        foreach ($this->crud['info'] as $k) {
        if (!in_array($k['COLUMN_NAME'], ['delete_time'])) {
            $columns .= '{
                field: \'' . $k['COLUMN_NAME'] . '\',
                title: \'' . $k['COLUMN_COMMENT'] . '\',
                unresize: true,
                align: \'center\'
            }, ';
            }
        }
        $searchs = '';
        foreach($this->crud['search'] as $p){
            $_search= explode('###',$p);
            if(strstr($_search[0],"time")){
            $searchs .= ' 
            laydate.render({
                elem: "#'.$_search[0].'-start"
            });
            laydate.render({
                elem: "#'.$_search[0].'-end"
            })';
            }
        }
        $content = str_replace(['{{$name}}', '{{$cname}}', '{{$columns}}','{{$searchs}}'], [$this->crud['name'],$this->crud['cname'],$columns,$searchs], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'recycle.js.tpl'));
        return [$file, $content];
    }


}
