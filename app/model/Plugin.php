<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class Plugin extends Model
{
    protected $name = 'plugin';
    protected $autoWriteTimestamp = false;
}
