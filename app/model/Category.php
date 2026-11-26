<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class Category extends Model
{
    protected $name = 'category';
    protected $autoWriteTimestamp = 'datetime';

    /** 树形列表 */
    public static function tree(): array
    {
        $rows = self::where('status', 1)->order('sort asc, id asc')->select()->toArray();
        return self::buildTree($rows);
    }

    public static function buildTree(array $rows, int $pid = 0): array
    {
        $tree = [];
        foreach ($rows as $row) {
            if ((int) $row['pid'] === $pid) {
                $children = self::buildTree($rows, (int) $row['id']);
                if ($children) {
                    $row['children'] = $children;
                }
                $tree[] = $row;
            }
        }
        return $tree;
    }
}
