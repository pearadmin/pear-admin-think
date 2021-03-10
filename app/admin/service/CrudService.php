<?php
declare (strict_types = 1);

namespace app\admin\service;

class CrudService
{
    //表名
    protected $table_name;
    //表名驼峰
    protected $table_name_hump;
    //提交参数
    protected $data;
    //次数
    protected $data_count;
    
    /**
     * 基础表
     */
    public function base_sql($name,$desc)
    {
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
        return preg_split("/;[\r\n]+/", $sql);
    }

    /**
     * CRUD
     * @param  string  $name    完整表名
     * @param  integer $data   参数
     */
    public function crud($name,$data)
    {
        //表名
        $this->table_name = substr($name,strlen(config('database.connections.mysql.prefix')));
        // 表名转驼峰
        $this->table_name_hump = underline_hump($this->table_name);
        //验证
        $validate = new \app\admin\validate\AdminCrud;
        if(!$validate->scene('del')->check(['name'=>$this->table_name])) return false;
        //参数
        $this->data = $data;
        //头
        $this->data['head'] = strstr($this->table_name , '_',true);
        //尾
        $this->data['foot'] = substr($this->table_name,strlen( $this->data['head'])+1);
        //头转驼峰
        $this->data['head_hump'] = underline_hump($this->data['head']);
        //尾转驼峰
        $this->data['foot_hump'] = underline_hump($this->data['foot']);
        //次数
        $this->data_count = count($this->data['name']);
        //构造选中参数
        for ($i=0; $i < $this->data_count; $i++) { 
            $this->data['list'][$i] = isset($this->data['list'][$i])??false;
            $this->data['search'][$i] = isset($this->data['search'][$i])??false;
            $this->data['form'][$i] = isset($this->data['form'][$i])??false;
        }
        $crud = [
            self::getController(), 
            self::getModel(),
            self::getValidate(),
            self::getIndexHtml(),
            self::getAddHtml(),
            self::getEditHtml(),
            self::getRecycleHtml(),
        ];
        if($this->data['menu']!=''){
            $path = '/'.$this->data['head'].'.'.$this->data['foot'].'/';
            (new \app\admin\model\AdminPermission)->make_menu($path,$this->data['ename'],$this->data['menu']);
        }
        foreach ($crud as $v) {
            @mkdir(dirname($v[0]), 0755, true);
            @file_put_contents($v[0], $v[1]);
        }
        return true;
    }

    /**
     * 控制器
     */
    public function getController()
    {
        $file = app_path().'controller'.DS.$this->data['head'].DS.$this->data['foot_hump'].'.php';
        $search = '';
        for ($i=0; $i <$this->data_count; $i++) { 
            if($this->data['search'][$i]){
                if(strstr($this->data['name'][$i],"time")){
                    $search .= '
            //按'.$this->data['desc'][$i].'查找
            $start = input("get.'.$this->data['name'][$i].'-start");
            $end = input("get.'.$this->data['name'][$i].'-end");
            if ($start && $end) {
                $this->where[]=["'.$this->data['name'][$i].'","between",[$start,date("Y-m-d",strtotime("$end +1 day"))]];
            }';
                }else{
            $search .= '
            //按'.$this->data['desc'][$i].'查找
            if ($'.$this->data['name'][$i].' = input("'.$this->data['name'][$i].'")) {
                $this->where[] = ["'.$this->data['name'][$i].'", "like", "%" . $'.$this->data['name'][$i].' . "%"];
            }';
                }
            }
        }
        $content = str_replace(['{{$head}}','{{$foot}}','{{$foot_hump}}','{{$table_name_hump}}','{{$search}}'], 
        [$this->data['head'],$this->data['foot'],$this->data['foot_hump'],$this->table_name_hump,$search], 
        file_get_contents(root_path().'extend'.DS.'tpl'.DS.'controller.php.tpl'));
        return [$file, $content];
    }

    public function getModel()
    {
        $file = root_path().'app'.DS.'common'.DS.'model'.DS.$this->table_name_hump.'.php';
        $del = 'protected $deleteTime = false;';
        for ($i=0; $i <$this->data_count; $i++) { 
            //软删除字段
            if ($this->data['name'][$i] == 'delete_time'){
                $del = ' protected $deleteTime = "delete_time";';
            }
        }
        $content = str_replace(['{{$table_name_hump}}','{{$del}}'],
        [$this->table_name_hump,$del], 
        file_get_contents(root_path().'extend'.DS.'tpl'.DS.'model.php.tpl'));
        return [$file, $content];
    }

    public function getValidate()
    {
        $file = root_path().'app'.DS.'common'.DS.'validate'.DS.$this->table_name_hump.'.php';
        $rule    = '';
        $message = '';
        $scene   = '';
        for ($i=0; $i <$this->data_count; $i++) { 
            if ($this->data['null'][$i] == 'NO' && $this->data['formType'][$i]!="4") {
                $rule .= '
        \'' . $this->data['name'][$i] . '\' => \'require';
                $message .= '
        \'' . $this->data['name'][$i] . '.require\' => \'' . $this->data['desc'][$i] . '为必填项\',';
            if (in_array($this->data['type'][$i], ['int', 'decimal', 'float', 'double'])) {
                $rule .= '|number';
                $message .= '
        \'' . $this->data['name'][$i] . '.number\' => \'' . $this->data['desc'][$i] . '需为数字\',';
            }
                $rule .= '\',';
                $scene .= '\'' . $this->data['name'][$i] . '\',';
            }
        }
        $content = str_replace(['{{$table_name_hump}}', '{{$rule}}', '{{$message}}', '{{$scene}}'],
        [$this->table_name_hump, $rule, $message, $scene], 
        file_get_contents(root_path().'extend'. DS .'tpl'. DS .'validate.php.tpl'));
        return [$file, $content];
    }

    public function getIndexHtml()
    {
        $file = root_path().'view'.DS.'admin'.DS.$this->data['head'].DS.$this->data['foot'].DS.'index.html';
        $searchs = '';
        $searchs_js = '';
        $list = '';
        $status = '';
        $status_js = '';
        for ($i=0; $i <$this->data_count; $i++) { 
            //搜索
            if($this->data['search'][$i]){
                if(strstr($this->data['name'][$i],"time")){
                $searchs .= '   
                <div class="layui-form-item layui-inline">
                    <label class="layui-form-label">'.$this->data['desc'][$i].'</label>
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input" id="'.$this->data['name'][$i].'-start" name="'.$this->data['name'][$i].'-start" placeholder="开始时间" autocomplete="off">
                    </div>
                </div>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input" id="'.$this->data['name'][$i].'-end" name="'.$this->data['name'][$i].'-end" placeholder="结束时间" autocomplete="off">
                </div>';
                $searchs_js .= ' 
                    laydate.render({
                        elem: "#'.$this->data['name'][$i].'-start"
                    });
                    laydate.render({
                        elem: "#'.$this->data['name'][$i].'-end"
                    })';
                }else{
                    $searchs .= '   
                <div class="layui-form-item layui-inline">
                    <label class="layui-form-label">'.$this->data['desc'][$i].'</label>
                    <div class="layui-input-inline">
                        <input type="text" name="'.$this->data['name'][$i].'" placeholder="" class="layui-input">
                    </div>
                </div>';
                }
            }
           //列表
           if($this->data['list'][$i]){
               if($this->data['formType'][$i]=="4"){
                $list .= '{
                        field: "'.$this->data['name'][$i].'",
                        title: "'.$this->data['desc'][$i].'",
                        unresize: "true",
                        align: "center",
                        templet:"#'.$this->data['name'][$i].'"
                    }, ';

            $status .='
            <script type="text/html" id="'.$this->data['name'][$i].'">
                <input type="checkbox" name="'.$this->data['name'][$i].'" value="{{d.id}}" lay-skin="switch" lay-text="启用|禁用" lay-filter="'.$this->data['name'][$i].'" {{# if(d.'.$this->data['name'][$i].'==1){ }} checked {{# } }}>
            </script>';

            $status_js .='
            form.on("switch('.$this->data['name'][$i].')", function(data) {
                var status = data.elem.checked?1:2;
                var id = this.value;
                var load = layer.load();
                $.post(MODULE_PATH + "status",{'.$this->data['name'][$i].':status,id:id},function (res) {
                    layer.close(load);
                    //判断有没有权限
                    if(res && res.code==999){
                        layer.msg(res.msg, {
                            icon: 5,
                            time: 2000, 
                        })
                        return false;
                    }else if (res.code==200){
                        layer.msg(res.msg,{icon:1,time:1500})
                    } else {
                        layer.msg(res.msg,{icon:2,time:1500},function () {
                            $(data.elem).prop("checked",!$(data.elem).prop("checked"));
                            form.render()
                        })
                    }
                })
            });'
            ;
               }else{
                $list .= '{
                        field: "'.$this->data['name'][$i].'",
                        title: "'.$this->data['desc'][$i].'",
                        unresize: "true",
                        align: "center"
                    }, ';
               }
            }
        }
        $content = str_replace(['{{$ename}}','{{$head}}','{{$foot}}','{{$searchs}}','{{$searchs_js}}','{{$list}}','{{$status}}','{{$status_js}}'], 
        [$this->data['ename'],$this->data['head'],$this->data['foot'],$searchs,$searchs_js,$list,$status,$status_js], 
        file_get_contents(root_path().'extend'. DS .'tpl'. DS .'view.index.html.tpl'));
        return [$file, $content];
    }
    
    public function getAddHtml()
    {
        $file = root_path().'view'.DS.'admin'.DS.$this->data['head'].DS.$this->data['foot'].DS.'add.html';
        $columns = '';
        $contentjs = '';
        $content = '';
        for ($i=0; $i <$this->data_count; $i++) {
            if($this->data['form'][$i]&&$this->data['formType'][$i]!='4'){
                $columns .= '
            <div class="layui-form-item">
                <label class="layui-form-label">
                    ' . $this->data['desc'][$i] . '
                </label>
                <div class="layui-input-block">
                    ';
                $lay_verify = '';
                switch ($this->data['formType'][$i]) {
                    case '5':
                        if($this->data['null'][$i] === 'NO') {
                            $lay_verify = ' lay-verify="required ';
                        }
                        $columns .= '<textarea class="layui-textarea"' . $lay_verify . ' name="' . $this->data['name'][$i] . '" ></textarea>';
                        break;
                    case '3':
                        if ($this->data['null'] === 'NO') {
                            $lay_verify = ' lay-verify="uploadimg"';
                        }
                        $columns .= '<button class="pear-btn pear-btn-primary pear-btn-sm upload-image" type="button">
                            <i class="fa fa-image">
                            </i>
                            上传图片
                        </button>
                        <input' . $lay_verify . ' name="' .  $this->data['name'][$i]  . '" type="hidden"/>
                        <div class="upload-image">
                            <span>
                            </span>
                            <img class="upload-image" src=""/>
                        </div>';
                        break;
                    case '2':
                        $columns .= '<textarea id="' .$this->data['name'][$i] . '" name="' . $this->data['name'][$i] . '" type="text/plain" style="width:100%;margin-bottom:20px;"></textarea>';
                        $contentjs .= '
                        var '.$this->data['name'][$i].'  = layedit.build("'.$this->data['name'][$i].'", {
                        height: 400 //设置编辑器的高度
                        });';
                        $content .= '
                            data.field.'.$this->data['name'][$i].'=layedit.getContent('.$this->data['name'][$i].');
                        ';    
                        break;
                    default:
                        if($this->data['null'][$i] === 'NO') {
                            $lay_verify = ' lay-verify="required ';
                            if (in_array($this->data['type'][$i], ['int', 'decimal', 'float', 'double'])) {
                                $lay_verify .= '|number';
                            }
                            $lay_verify .= '"';
                        }
                        $columns .= '<input type="text" class="layui-input layui-form-danger"' . $lay_verify . ' name="' . $this->data['name'][$i] . '" type="text"/>';
                        break;
                }
                $columns .= '
                </div>
            </div>';
            }
        }
        $content = str_replace(['{{$columns}}','{{$contentjs}}','{{$content}}'], 
        [$columns,$contentjs,$content], 
        file_get_contents(root_path().'extend'. DS .'tpl'. DS .'view.add.html.tpl'));
        return [$file, $content];
    }

    public function getEditHtml()
    {
        $file = root_path().'view'.DS.'admin'.DS.$this->data['head'].DS.$this->data['foot'].DS.'edit.html';
        $columns = '';
        $contentjs = '';
        $content = '';
        for ($i=0; $i <$this->data_count; $i++) { 
            if($this->data['form'][$i]&&$this->data['formType'][$i]!='4'){
                $columns .= '
            <div class="layui-form-item">
                <label class="layui-form-label">
                    ' . $this->data['desc'][$i] . '
                </label>
                <div class="layui-input-block">
                    ';
                $lay_verify = '';
                switch ($this->data['formType'][$i]) {
                    case '5':
                        if($this->data['null'][$i] === 'NO') {
                            $lay_verify = ' lay-verify="required ';
                        }
                        $columns .= '<textarea class="layui-textarea"' . $lay_verify . ' name="' . $this->data['name'][$i] . '" >{$model[\'' . $this->data['name'][$i] . '\']}</textarea>';
                        break;
                    case '3':
                        if ($this->data['null'] === 'NO') {
                            $lay_verify = ' lay-verify="uploadimg"';
                        }
                        $columns .= '<button class="pear-btn pear-btn-primary pear-btn-sm upload-image" type="button">
                            <i class="fa fa-image">
                            </i>
                            上传图片
                        </button>
                        <input' . $lay_verify . ' name="' .  $this->data['name'][$i]  . '" type="hidden" value="{$model[\'' . $this->data['name'][$i] . '\']}"/>
                        <div class="upload-image">
                            <span>
                            </span>
                            <img class="upload-image" src="{$model[\'' . $this->data['name'][$i] . '\']}"/>
                        </div>';
                        break;
                    case '2':
                        $columns .= '<textarea id="' .$this->data['name'][$i] . '" name="' . $this->data['name'][$i] . '" type="text/plain" style="width:100%;margin-bottom:20px;">{$model[\'' . $this->data['name'][$i] . '\']}</textarea>';
                        $contentjs .= '
                        var '.$this->data['name'][$i].'  = layedit.build("'.$this->data['name'][$i].'", {
                        height: 400 //设置编辑器的高度
                        });';
                        $content .= '
                            data.field.'.$this->data['name'][$i].'=layedit.getContent('.$this->data['name'][$i].');
                        ';    
                        break;
                    default:
                        if($this->data['null'][$i] === 'NO') {
                            $lay_verify = ' lay-verify="required ';
                            if (in_array($this->data['type'][$i], ['int', 'decimal', 'float', 'double'])) {
                                $lay_verify .= '|number';
                            }
                            $lay_verify .= '"';
                        }
                        $columns .= '<input type="text" class="layui-input layui-form-danger"' . $lay_verify . ' name="' . $this->data['name'][$i] . '" type="text" value="{$model[\'' . $this->data['name'][$i] . '\']}"/>';
                        break;
                }
                $columns .= '
                </div>
            </div>';
            }
        }
        $content = str_replace(['{{$columns}}','{{$contentjs}}','{{$content}}'], 
        [$columns,$contentjs,$content], 
        file_get_contents(root_path().'extend'. DS .'tpl'. DS .'view.edit.html.tpl'));
        return [$file, $content];
    }

    public function getRecycleHtml()
    {
        $file = root_path().'view'.DS.'admin'.DS.$this->data['head'].DS.$this->data['foot'].DS.'recycle.html';
        $searchs = '';
        $searchs_js = '';
        $list = '';
        for ($i=0; $i <$this->data_count; $i++) { 
            //搜索
            if($this->data['search'][$i]){
                if(strstr($this->data['name'][$i],"time")){
                $searchs .= '   
                <div class="layui-form-item layui-inline">
                    <label class="layui-form-label">'.$this->data['desc'][$i].'</label>
                    <div class="layui-input-inline">
                        <input type="text" class="layui-input" id="'.$this->data['name'][$i].'-start" name="'.$this->data['name'][$i].'-start" placeholder="开始时间" autocomplete="off">
                    </div>
                </div>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input" id="'.$this->data['name'][$i].'-end" name="'.$this->data['name'][$i].'-end" placeholder="结束时间" autocomplete="off">
                </div>';
                $searchs_js .= ' 
                    laydate.render({
                        elem: "#'.$this->data['name'][$i].'-start"
                    });
                    laydate.render({
                        elem: "#'.$this->data['name'][$i].'-end"
                    })';
                }else{
                    $searchs .= '   
                <div class="layui-form-item layui-inline">
                    <label class="layui-form-label">'.$this->data['desc'][$i].'</label>
                    <div class="layui-input-inline">
                        <input type="text" name="'.$this->data['name'][$i].'" placeholder="" class="layui-input">
                    </div>
                </div>';
                }
            }
           //列表
           if($this->data['list'][$i]){
            $list .= '{
                        field: "'.$this->data['name'][$i].'",
                        title: "'.$this->data['desc'][$i].'",
                        unresize: "true",
                        align: "center"
                    }, ';
            }
        }
        $content = str_replace(['{{$ename}}','{{$head}}','{{$foot}}','{{$searchs}}','{{$searchs_js}}','{{$list}}'], 
        [$this->data['ename'],$this->data['head'],$this->data['foot'],$searchs,$searchs_js,$list], 
        file_get_contents(root_path().'extend'. DS .'tpl'. DS .'view.recycle.html.tpl'));
        return [$file, $content];
    }
}