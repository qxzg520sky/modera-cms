<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class Page extends Model
{
    protected $name = 'page';
    protected $autoWriteTimestamp = 'datetime';
}
