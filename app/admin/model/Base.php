<?php
namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;
class Base extends Model
{
    use SoftDelete;
}
