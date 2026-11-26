<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class Admin extends Model
{
    protected $name = 'admin';
    protected $autoWriteTimestamp = 'datetime';

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function checkPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
