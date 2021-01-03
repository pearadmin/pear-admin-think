<?php
namespace app\admin\model\home;

use think\Model;
use think\model\concern\SoftDelete;
class News extends Model
{
    protected $table = 'home_news';
     
            use SoftDelete;
            protected $deleteTime = "delete_time";
            
}
