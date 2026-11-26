<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class Article extends Model
{
    protected $name = 'article';
    protected $autoWriteTimestamp = 'datetime';
}
