<?php
declare (strict_types = 1);

namespace app\admin\controller\admin;

use think\facade\Request;
use think\facade\View;
use think\facade\Db;
use app\admin\model\admin\Permission;
class Crud extends Base
{
    protected $middleware = ['AdminCheck','AdminPermission'];
    
    /**
     * 列表
     */
    public function index()
    {
        if (Request::isAjax()) {
            $name = input('get.name');
            $list = Db::query('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME NOT IN ("admin_admin","admin_admin_role","admin_admin_log","admin_multi","admin_role","admin_permission","admin_config","admin_photo","USER_PRIVILEGES") AND TABLE_NAME LIKE "' . $name . '%"');
            $this->jsonApi('', 0, $list);
        }
        return View::fetch('',[
            'list' => Db::name('admin_multi')->order(['name'])->column('name')
        ]);
    }

    /**
     * 新增多级地址
     */
    public function addMulti()
    {
        if (Request::isAjax()){
            $data['name'] = Request::post('name');
            if($data['name']=="admin") 
            $this->jsonApi('admin禁止添加',201);
            $validate = new \app\admin\validate\admin\Multi;
            if(!$validate->check($data)) 
            $this->jsonApi($validate->getError(),201);
            try {
                 Db::name('admin_multi')->save($data);
                 //创建控制器目录
                 @mkdir(app_path().DS.'controller'.DS.$data['name']);
                 //创建模型目录
                 @mkdir(app_path().DS.'model'.DS.$data['name']);
                 //创建验证器目录
                 @mkdir(app_path().DS.'validate'.DS.$data['name']);
                 //创建视图目录
                 @mkdir(root_path().'view'.DS.'admin'.DS.$data['name']);
                 //创建JS目录
                 if(!is_dir(public_path().'static'.DS.'admin'.DS.'multi')) @mkdir(public_path().'static'.DS.'admin'.DS.'multi');
                 @mkdir(public_path().'static'.DS.'admin'.DS.'multi'.DS.$data['name']);
                 //创建基础控制器
                 @file_put_contents(app_path().DS.'controller'.DS.$data['name'].DS.'/Base.php', 
                 str_replace(['{{$app}}'], [$data['name']], file_get_contents(root_path().'extend'.DS.'tpl'.DS.'base.php.tpl')));
             }catch (\Exception $e){
                 $this->jsonApi('添加失败',201, $e->getMessage());
             }
             $this->jsonApi('添加成功');
        }
        return View::fetch();
    }

    /**
     * 删除多级地址
     */
    public function delMulti()
    {
        if (Request::isAjax()){
            $name = Request::post('name');
            if($name=="admin") 
            $this->jsonApi('admin禁止删除',201);
            try {
                //删除控制器目录
                 delete_dir(app_path().DS.'controller'.DS.$name);
                 //删除模型目录
                 delete_dir(app_path().DS.'model'.DS.$name);
                 //删除验证器目录
                 delete_dir(app_path().DS.'validate'.DS.$name);
                 //删除视图目录
                 delete_dir(root_path().'view'.DS.'admin'.DS.$name);
                 //删除JS目录
                 delete_dir(public_path().'static'.DS.'admin'.DS.'multi'.DS.$name);
                 //删除菜单
                 Permission::where('multi',$name)->delete();
                  //清空缓存
                 $this->rm();
                 Db::name('admin_multi')->where('name',$name)->delete();
             }catch (\Exception $e){
                 $this->jsonApi('删除失败',201, $e->getMessage());
             }
             $this->jsonApi('删除成功');
        }
    }

    /**
     * 新增基础表
     */
    public function addBase()
    {
        if (Request::isAjax()){
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
        return View::fetch();
    }

    /**
     * 生成
     */
    public function crud($name)
    {
        $sql = Db::query('SELECT COLUMN_NAME,IS_NULLABLE,DATA_TYPE,IF(COLUMN_COMMENT = "",COLUMN_NAME,COLUMN_COMMENT) COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_NAME = "' . $name . '" AND COLUMN_NAME <> "id"');
        foreach ($sql as $k) {
            $info[] = [
                'name' => $k['COLUMN_NAME'],
                'null' => $k['IS_NULLABLE'],
                'desc' => $k['COLUMN_COMMENT'],
                'type' => $k['DATA_TYPE'],
            ];
        }
        if (Request::isAjax()){
            $data = Request::param();
            $array = array_merge($data['sql-edit']??[],$data['sql-photo']??[]);
            if (count($array) != count(array_unique($array)))  $this->jsonApi('特殊字段重复设置', 201);
            // 完整表名
            $this->name = $data['name'];
            // 中文名称
            $this->cname = $data['cname'];
            // 表字段数据
            $this->info = $info;
            // 多级地址
            $this->multi = strstr($this->name , '_',true);
            //去除多级地址
            $this->multi_name = substr($this->name,strlen($this->multi)+1);
            // 表名转驼峰
            $this->multi_name_hump = underline_hump($this->multi_name);
            // 控制器，模型，验证器文件名称
            $this->name_php =  $this->multi_name_hump . '.php';
            // 菜单自动生成
            $this->menu_type = $data['menu-type'];
            if($this->menu_type == '1'){
                $this->menu_data = [
                    'pid' => $data['menu-pid'],
                    'title' => $this->cname.'列表',
                    'href' => '/'.$this->multi.'.'.$this->multi_name.'/'.'index',
                    'icon' => $data['menu-icon'],
                    'sort' => $data['menu-sort'],
                    'multi' => $this->multi,
                ];
            }
            //字段设置
            $this->edit = $data['sql-edit']??[];
            $this->photo = $data['sql-photo']??[];
            $this->search = $data['sql-search']??[];
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
            if(isset($this->menu_data)){
                $menu = Permission::create($this->menu_data);
                if($menu){
                     $this->menu_data['pid'] = $menu['id'];
                     //添加
                     $this->menu_data['title'] = '添加'. $this->cname;
                     $this->menu_data['href'] = '/'.$this->multi.'.'.$this->multi_name.'/'.'add';
                     Permission::create($this->menu_data);
                     //编辑
                     $this->menu_data['title'] = '编辑'. $this->cname;
                     $this->menu_data['href'] = '/'.$this->multi.'.'.$this->multi_name.'/'.'edit';
                     Permission::create($this->menu_data);
                     //删除
                     $this->menu_data['title'] = '删除'. $this->cname;
                     $this->menu_data['href'] = '/'.$this->multi.'.'.$this->multi_name.'/'.'del';
                     Permission::create($this->menu_data);
                     //选中删除
                     $this->menu_data['title'] = '选中删除'. $this->cname;
                     $this->menu_data['href'] = '/'.$this->multi.'.'.$this->multi_name.'/'.'delall';
                     Permission::create($this->menu_data);
                     //回收站
                     $this->menu_data['title'] = '回收站'. $this->cname;
                     $this->menu_data['href'] = '/'.$this->multi.'.'.$this->multi_name.'/'.'recycle';
                     Permission::create($this->menu_data);
                     $this->rm();
                  }
            }
            try {
                foreach ($crud as $v) {
                    @mkdir(dirname($v[0], 0755, true));
                    @file_put_contents($v[0], $v[1]);
                }
            }catch (\Exception $e){
                $this->jsonApi('操作失败',201, $e->getMessage());
            }
            $this->jsonApi('操作成功');
        }
        //表数据
        return View::fetch('',[
            'info' => $info,
            'permissions' => get_tree(Permission::order('sort','asc')->select()->toArray()),
            'desc' => Db::query('SELECT TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_NAME = "' . $name . '"')[0]['TABLE_COMMENT']
        ]);
    }


    private function getController()
    {
        $file = app_path().'controller'.DS.$this->multi.DS.$this->name_php;
        $list = '
            //重整数组
            foreach ($list as $k => $v) {';
                foreach($this->photo as $k=>$v){
                    $i = explode('###',$v);
                    $list .= '
                    $list[$k][\'' . $i[0] . '\'] = \'<img src="\' . $v[\'' . $i[0] . '\'] . \'"/>\';';
                }
        $list .= '
            }';
        $search = '';
        foreach($this->search as $k=>$v){
            $i = explode('###',$v);
            if(strstr($i[0],"time")){
                $search .= '    
                //按'.$i[1].'查找
                $start = input("get.'.$i[0].'-start");
                $end = input("get.'.$i[0].'-end");
                if ($start && $end) {
                    $where[]=["'.$i[0].'","between",[$start,date("Y-m-d",strtotime("$end +1 day"))]];
                 }';
               }else{
                $search .= '    
                //按'.$i[1].'查找
                if ($'.$i[0].' = input("'.$i[0].'")) {
                    $where[] = ["'.$i[0].'", "like", "%" . $'.$i[0].' . "%"];
                }';
               }
        }
        $content = str_replace(['{{$multi}}', '{{$multi_name_hump}}','{{$list}}','{{$search}}'], [ $this->multi, $this->multi_name_hump,$list,$search], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'controller.php.tpl'));
        return [$file, $content];
    }

    private function getModel()
    {
        $file = app_path().'model'.DS.$this->multi.DS.$this->name_php;
        $del = '';
            foreach ($this->info as $k) {
            //软删除字段
            if ($k['name'] == 'delete_time'){
            $del = ' 
            use SoftDelete;
            protected $deleteTime = "delete_time";
            '
            ;
            }
        }
        $content = str_replace(['{{$multi}}', '{{$multi_name_hump}}', '{{$name}}','{{$del}}'], [$this->multi, $this->multi_name_hump, $this->name,$del], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'model.php.tpl'));
        return [$file, $content];
    }

    private function getValidate()
    {
        $file = app_path().'validate'.DS.$this->multi.DS.$this->name_php;
        $rule    = '';
        $message = '';
        $scene   = '';
        foreach ($this->info as $k) {
            if (!in_array($k['name'], ['create_time', 'update_time','delete_time'])) {
            //判断状态
            if ($k['null'] === 'NO') {
                    $rule .= '
           \'' . $k['name'] . '\' => \'require';
                    $message .= '
            \'' . $k['name'] . '.require\' => \'' . $k['desc'] . '为必填项\',';
            if (in_array($k['type'], ['int', 'decimal', 'float', 'double'])) {
                $rule .= '|number';
                $message .= '
            \'' . $k['name'] . '.number\' => \'' . $k['desc'] . '需为数字\',';
            }
                    $rule .= '\',';
                    $scene .= '
            \'' . $k['name'] . '\',';
                }
            }
        }
        $content = str_replace(['{{$multi}}', '{{$multi_name_hump}}', '{{$rule}}', '{{$message}}', '{{$scene}}'], [$this->multi, $this->multi_name_hump, $rule, $message, $scene], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'validate.php.tpl'));
        return [$file, $content];
    }

    private function getAddHtml()
    {
        $file = root_path().'view'.DS.'admin'.DS.$this->multi.DS.$this->multi_name.DS."add.html";
        $columns = '';
        $contentjs = '    
        layedit.set({
            uploadImage: {
                url: "../admin.index/upload"
            }
        });
        //建立编辑器'
        ;
        $content = '';
        foreach ($this->info as $k) {
            if (!in_array($k['name'], ['create_time', 'update_time','delete_time'])) {
                $columns .= '
            <div class="layui-form-item">
                <label class="layui-form-label">
                    ' . $k['desc'] . '
                </label>
                <div class="layui-input-block">
                    ';
                $lay_verify = '';
                //判断图片
                $_photo = [];
                if(!empty($this->photo)){
                    foreach($this->photo as $p){
                        $_photo[]= explode('###',$p)[0];
                    }
                }
                //判断编辑器
                $_edit = [];
                if(!empty($this->edit)){
                    foreach($this->edit as $e){
                        $_edit[]= explode('###',$e)[0];
                    }
                } 
                if (in_array($k['name'], $_photo)) {
                    if ($k['null'] === 'NO') {
                        $lay_verify = ' lay-verify="uploadimg"';
                    }
                    $columns .= '<button class="layui-btn layui-btn-sm upload-image" type="button">
                        <i class="fa fa-image">
                        </i>
                        上传图片
                    </button>
                    <input' . $lay_verify . ' name="' .  $k['name'] . '" type="hidden"/>
                    <div class="upload-image">
                        <span>
                        </span>
                        <img class="upload-image" src=""/>
                    </div>';

                }elseif(in_array($k['name'], $_edit)) {
            $columns .= '<textarea id="' .$k['name'] . '" name="' . $k['name'] . '" type="text/plain" style="width:100%;margin-bottom:20px;"></textarea>';
            $contentjs .=  '   
            var '.$k['name'].'  = layedit.build("'.$k['name'].'", {
            height: 400 //设置编辑器的高度
            });';
            $content .= '
            data.field.'.$k['name'].'=layedit.getContent('.$k['name'].');
            ';
                }else{
                    if($k['null'] === 'NO') {
                        $lay_verify = ' lay-verify="required ';
                        if (in_array($k['type'], ['int', 'decimal', 'float', 'double'])) {
                            $lay_verify .= '|number';
                        }
                        $lay_verify .= '"';
                    }
                    $columns .= '<input type="text" class="layui-input layui-form-danger"' . $lay_verify . ' name="' . $k['name'] . '" type="text"/>';
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
        $file = root_path().'view'.DS.'admin'.DS.$this->multi.DS.$this->multi_name.DS."edit.html";
        $columns = '';
        $contentjs = '    
        layedit.set({
            uploadImage: {
                url: "../admin.index/upload"
            }
        });
        //建立编辑器'
        ;
        $content = '';
        foreach ($this->info as $k) {
            if (!in_array($k['name'], ['create_time', 'update_time','delete_time'])) {
                $columns .= '
    <div class="layui-form-item">
        <label class="layui-form-label">
            ' . $k['desc'] . '
        </label>
        <div class="layui-input-block">
            ';
                $lay_verify = '';
                //判断图片
                $_photo = [];
                if(!empty($this->photo)){
                    foreach($this->photo as $p){
                        $_photo[]= explode('###',$p)[0];
                    }
                }
                //判断编辑器
                $_edit = [];
                if(!empty($this->edit)){
                    foreach($this->edit as $e){
                        $_edit[]= explode('###',$e)[0];
                    }
                } 
               if (in_array($k['name'], $_photo)) {
                   if($k['null'] === 'NO') {
                       $lay_verify = ' lay-verify="uploadimg"';
                   }
                   $columns .= '<button class="layui-btn layui-btn-sm upload-image" type="button">
                   <i class="fa fa-image">
                   </i>
                   上传图片
               </button>
               <input' . $lay_verify . ' name="' . $k['name'] . '" type="hidden" value="{$data[\'' . $k['name'] . '\']}"/>
               <div class="upload-image">
                   <span>
                   </span>
                   <img class="upload-image" src="{$data[\'' . $k['name'] . '\']}"/>
               </div>';

               }elseif (in_array($k['name'], $_edit)) {
                $columns .= '<textarea id="' .$k['name'] . '" name="' . $k['name'] . '" type="text/plain" style="width:100%;margin-bottom:20px;">{$data[\'' . $k['name'] . '\']}</textarea>';
                $contentjs .=  '   
                var '.$k['name'].'  = layedit.build("'.$k['name'].'", {
                height: 400 //设置编辑器的高度
                });';
                $content .= '
                data.field.'.$k['name'].' =layedit.getContent('.$k['name'].');
                ';
               }else{
                    if($k['null'] === 'NO') {
                        $lay_verify = ' lay-verify="required ';
                        if (in_array($k['type'], ['int', 'decimal', 'float', 'double'])) {
                            $lay_verify .= '|number';
                        }
                        $lay_verify .= '"';
                    }
                    $columns .= '<input type="text" class="layui-input layui-form-danger"' . $lay_verify . ' name="' . $k['name'] . '" type="text" value="{$data[\'' . $k['name'] . '\']}"/>';
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
        $file = root_path().'view'.DS.'admin'.DS.$this->multi.DS.$this->multi_name.DS."index.html";
            $searchs = '';
            if(!empty($this->search)){
                $searchs .= ' <div class="layui-card">
                <div class="layui-card-body">
                    <form class="layui-form" action="">
                    <div class="layui-form-item">';
                foreach($this->search as $p){
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
        $content = str_replace(['{{$multi_name}}', '{{$multi}}','{{searchs}}'], [$this->multi_name, $this->multi,$searchs], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'view.index.html.tpl'));
        return [$file, $content];
    }

    private function getIndexJs()
    {
        $file = public_path().'static'.DS.'admin'.DS.'multi'.DS.$this->multi.DS.$this->multi_name.DS."index.js";
        $columns = '';
        foreach ($this->info as $k) {
        if (!in_array($k['name'], ['delete_time'])) {
            $columns .= '{
                field: \'' . $k['name'] . '\',
                title: \'' . $k['desc'] . '\',
                unresize: true,
                align: \'center\'
            }, ';
            }
        }
        $searchs = '';
        if(!empty($this->search)){
            foreach($this->search as $p){
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
        $content = str_replace(['{{$multi_name}}', '{{$multi}}', '{{$columns}}','{{$cname}}','{{$searchs}}'], [$this->multi_name, $this->multi,$columns,$this->cname,$searchs], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'multi.index.js.tpl'));
        return [$file, $content];
    }

    private function getRecycleHtml()
    {
        $file = root_path().'view'.DS.'admin'.DS.$this->multi.DS.$this->multi_name.DS."recycle.html";
            $searchs = '';
            if(!empty($this->search)){
                $searchs .= ' <div class="layui-card">
                <div class="layui-card-body">
                    <form class="layui-form" action="">
                    <div class="layui-form-item">';
                foreach($this->search as $p){
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
        $content = str_replace(['{{$multi_name}}', '{{$multi}}','{{searchs}}'], [$this->multi_name, $this->multi,$searchs], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'view.recycle.html.tpl'));
        return [$file, $content];
    }

    private function getRecycleJs()
    {
        $file = public_path().'static'.DS.'admin'.DS.'multi'.DS.$this->multi.DS.$this->multi_name.DS."recycle.js";
        $columns = '';
        foreach ($this->info as $k) {
        if (!in_array($k['name'], ['delete_time'])) {
            $columns .= '{
                field: \'' . $k['name'] . '\',
                title: \'' . $k['desc'] . '\',
                unresize: true,
                align: \'center\'
            }, ';
            }
        }
        $searchs = '';
        foreach($this->search as $p){
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
        $content = str_replace(['{{$multi_name}}', '{{$multi}}', '{{$columns}}','{{$cname}}','{{$searchs}}'], [$this->multi_name, $this->multi,$columns,$this->cname,$searchs], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'multi.recycle.js.tpl'));
        return [$file, $content];
    }


}
