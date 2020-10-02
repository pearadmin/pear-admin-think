<?php
namespace app\admin\model\{{$multi}};

use think\Model;
use think\model\concern\SoftDelete;
class {{$multi_name_hump}} extends Model
{
    protected $table = '{{$name}}';
    {{$del}}
}
