<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
class ModelBase extends Model
{
    use SoftDelete;
}
