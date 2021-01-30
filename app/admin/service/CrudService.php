<?php
namespace app\admin\service;

use think\exception\ValidateException;
use think\Validate;
use think\facade\Db;
class CrudService
{
    protected static $crud;
    /**
     * 验证数据
     */
    private static function validate($file)
    {
        $v = new Validate();
        $v->rule([
            'name' =>'notIn:admin_admin,admin_admin_log,admin_admin_role,site_config,admin_permission,admin_photo,admin_role'
         ]);
        return $v->failException(true)->check($file);
    }

   /**
    *删除
    */
    static function del($data){
        try {
            self::validate($data);
        } catch (\think\exception\ValidateException $e) {
            return ['msg'=>'系统内置禁止操作','code'=>201,'data'=>$e->getMessage()];
        }
        if($data['type']=='true') Db::query('drop table '.$data["name"].'');
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
            (new \app\admin\model\AdminPermission)->where('href', 'like', "%" . $name . "%")->delete();
         }catch (\Exception $e){
            return ['msg'=>'删除失败','code'=>201,'data'=>$e->getMessage()];
         }
         return ['msg'=>'操作成功','code'=>200,'data'=>null];
    }


   /**
    *Crud
    */
    static function Crud($data,$sql){
        try {
            self::validate($data);
        } catch (\think\exception\ValidateException $e) {
            return ['msg'=>'系统内置禁止操作','code'=>201,'data'=>$e->getMessage()];
        }
        $array = array_merge($data['sql-edit']??[],$data['sql-photo']??[]);
        if (count($array) != count(array_unique($array)))   return ['msg'=>'特殊字段重复设置','code'=>201,'data'=>null];
        //构造crud
        self::crudData($data,$sql);
        $crud = [
            self::getController(), 
            self::getModel(),
            self::getValidate(), 
            self::getAddHtml(),
            self::getEditHtml(), 
            self::getIndexHtml(),
            self::getIndexJs(),
            self::getRecycleHtml(),
            self::getRecycleJs()
        ];
        if(isset( self::$crud['menu'])){
            $permission = new \app\admin\model\AdminPermission;
            $menu = $permission->create(self::$crud['menu']);
            if($menu){
                self::$crud['menu']['pid'] = $menu['id'];
                //添加
                self::$crud['menu']['title'] = '添加'. self::$crud['cname'];
                self::$crud['menu']['href'] = '/'.self::$crud['head'].'_'.self::$crud['tail'].'/'.'add';
                $permission->create(self::$crud['menu']);
                //编辑
                self::$crud['menu']['title'] = '编辑'. self::$crud['cname'];
                self::$crud['menu']['href'] = '/'.self::$crud['head'].'_'.self::$crud['tail'].'/'.'edit';
                $permission->create(self::$crud['menu']);
                //删除
                self::$crud['menu']['title'] = '删除'. self::$crud['cname'];
                self::$crud['menu']['href'] = '/'.self::$crud['head'].'_'.self::$crud['tail'].'/'.'del';
                $permission->create(self::$crud['menu']);
                //选中删除
                self::$crud['menu']['title'] = '选中删除'. self::$crud['cname'];
                self::$crud['menu']['href'] = '/'.self::$crud['head'].'_'.self::$crud['tail'].'/'.'delall';
                $permission->create(self::$crud['menu']);
                //回收站
                self::$crud['menu']['title'] = '回收站'. self::$crud['cname'];
                self::$crud['menu']['href'] = '/'.self::$crud['head'].'_'.self::$crud['tail'].'/'.'recycle';
                $permission->create(self::$crud['menu']);
            }
        }
        try {
            foreach ($crud as $v) {
                @mkdir(dirname($v[0]), 0755, true);
                @file_put_contents($v[0], $v[1]);
            }
        }catch (\Exception $e){
            return ['msg'=>'操作失败','code'=>201,'data'=>$e->getMessage()];
        }
        return ['msg'=>'操作成功','code'=>200,'data'=>null];
   }

   static function crudData($data,$sql)
   {
       // 完整表名
       self::$crud['name'] = $data['name'];
       // 中文名称
       self::$crud['cname'] = $data['cname'];
       // 表字段数据
       self::$crud['info'] = $sql;
       // 头
       self::$crud['head'] = strstr(self::$crud['name'] , '_',true);
       //尾
       self::$crud['tail'] = substr(self::$crud['name'],strlen(self::$crud['head'])+1);
       // 表名尾转驼峰
       self::$crud['heads'] = underline_hump(self::$crud['head']);
       // 表名尾转驼峰
       self::$crud['tails'] = underline_hump(self::$crud['tail']);
       // 控制器，模型，验证器文件名称
       self::$crud['app'] =  self::$crud['heads'].self::$crud['tails'];
       // 菜单自动生成
       if($data['menu-type'] == '1'){
           self::$crud['menu'] = [
               'pid' => $data['menu-pid'],
               'title' => self::$crud['cname'].'列表',
               'href' => '/'.self::$crud['head'].'_'.self::$crud['tail'].'/'.'index',
               'icon' => $data['menu-icon'],
               'sort' => $data['menu-sort']
           ];
       }
       //字段设置
       self::$crud['edit'] = $data['sql-edit']??[];
       self::$crud['photo'] = $data['sql-photo']??[];
       self::$crud['search'] = $data['sql-search']??[];
   }

   static function getController()
   {
       $file = app_path().'controller'.DS.self::$crud['app'].'.php';
       $search = '';
       foreach(self::$crud['search'] as $k=>$v){
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
       $content = str_replace(['{{$app}}','{{$search}}'], [self::$crud['app'],$search], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'controller.php.tpl'));
       return [$file, $content];
   }

   static function getModel()
   {
       $file = root_path().'app'.DS.'common'.DS.'model'.DS.self::$crud['app'].'.php';
       $del = '';
       foreach (self::$crud['info'] as $k) {
           //软删除字段
           if ($k['COLUMN_NAME'] == 'delete_time'){
               $del = 'protected $deleteTime = "delete_time";';
           }else{
               $del = 'protected $deleteTime = false;';
           }
       }
       $content = str_replace(['{{$name}}', '{{$app}}', '{{$del}}'], [self::$crud['name'], self::$crud['app'],$del], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'model.php.tpl'));
       return [$file, $content];
   }

   static function getValidate()
   {
       $file = root_path().'app'.DS.'common'.DS.'validate'.DS.self::$crud['app'].'.php';
       $rule    = '';
       $message = '';
       $scene   = '';
       foreach (self::$crud['info'] as $k) {
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
       $content = str_replace(['{{$name}}', '{{$app}}', '{{$rule}}', '{{$message}}', '{{$scene}}'], [self::$crud['name'], self::$crud['app'], $rule, $message, $scene], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'validate.php.tpl'));
       return [$file, $content];
   }

   static function getAddHtml()
   {
       $file = root_path().'view'.DS.'admin'.DS.self::$crud['name'].DS."add.html";
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
       foreach (self::$crud['info'] as $k) {
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
               if(!empty(self::$crud['photo'])){
                   foreach(self::$crud['photo'] as $p){
                       $_photo[]= explode('###',$p)[0];
                   }
               }
               //判断编辑器
               $_edit = [];
               if(!empty(self::$crud['edit'])){
                   foreach(self::$crud['edit'] as $e){
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

   static function getEditHtml()
   {
       $file = root_path().'view'.DS.'admin'.DS.self::$crud['name'].DS."edit.html";
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
       foreach (self::$crud['info'] as $k) {
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
               if(!empty(self::$crud['photo'])){
                   foreach(self::$crud['photo'] as $p){
                       $_photo[]= explode('###',$p)[0];
                   }
               }
               //判断编辑器
               $_edit = [];
               if(!empty(self::$crud['edit'])){
                   foreach(self::$crud['edit'] as $e){
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

   static function getIndexHtml()
   {
       $file = root_path().'view'.DS.'admin'.DS.self::$crud['name'].DS."index.html";
       $searchs = '';
           if(!empty(self::$crud['search'])){
               $searchs .= ' <div class="layui-card">
               <div class="layui-card-body">
                   <form class="layui-form" action="">
                   <div class="layui-form-item">';
               foreach(self::$crud['search'] as $p){
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
       $content = str_replace(['{{$name}}','{{searchs}}'],  [self::$crud['name'],$searchs], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'view.index.html.tpl'));
       return [$file, $content];
   }

   static function getIndexJs()
   {
       $file = public_path().'static'.DS.'admin'.DS.'js'.DS.self::$crud['name'].DS."index.js";
       $columns = '';
       foreach (self::$crud['info'] as $k) {
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
       if(!empty(self::$crud['search'])){
           foreach(self::$crud['search'] as $p){
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
       $content = str_replace(['{{$name}}','{{$cname}}','{{$columns}}','{{$searchs}}'], [self::$crud['name'],self::$crud['cname'],$columns,$searchs], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'index.js.tpl'));
       return [$file, $content];
   }

   static function getRecycleHtml()
   {
       $file = root_path().'view'.DS.'admin'.DS.self::$crud['name'].DS."recycle.html";
       $searchs = '';
           if(!empty(self::$crud['search'])){
               $searchs .= ' <div class="layui-card">
               <div class="layui-card-body">
                   <form class="layui-form" action="">
                   <div class="layui-form-item">';
               foreach(self::$crud['search'] as $p){
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
       $content = str_replace(['{{$name}}','{{searchs}}'],  [self::$crud['name'],$searchs], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'view.recycle.html.tpl'));
       return [$file, $content];
   }

   static function getRecycleJs()
   {
       $file = public_path().'static'.DS.'admin'.DS.'js'.DS.self::$crud['name'].DS."recycle.js";
       $columns = '';
       foreach (self::$crud['info'] as $k) {
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
       foreach(self::$crud['search'] as $p){
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
       $content = str_replace(['{{$name}}', '{{$cname}}', '{{$columns}}','{{$searchs}}'], [self::$crud['name'],self::$crud['cname'],$columns,$searchs], file_get_contents(root_path().'extend'. DS .'tpl'. DS .'recycle.js.tpl'));
       return [$file, $content];
   }
}