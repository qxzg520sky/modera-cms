<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class Theme extends Model
{
    protected $name = 'theme';
    protected $autoWriteTimestamp = false;
}
