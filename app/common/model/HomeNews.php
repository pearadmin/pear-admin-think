<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
class HomeNews extends Model
{
    use SoftDelete;
    protected $deleteTime = "delete_time";
}
