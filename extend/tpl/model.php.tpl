<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
class {{$table_name_hump}} extends Model
{
    use SoftDelete;
   {{$del}}
}
