<?php
declare(strict_types=1);

namespace app\model;

use think\Model;
use think\facade\Db;

class Setting extends Model
{
    protected $name = 'setting';
    protected $autoWriteTimestamp = false;

    /** 全部配置（静态缓存）。装了多语言插件后，value_en 等列有值会覆盖中文值 */
    public static function allSettings(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $cache = [];
        try {
            $rows = Db::name('setting')->select()->toArray();
            foreach ($rows as $row) {
                $cache[$row['name']] = $row['value'];
            }

            $lang = function_exists('current_lang') ? current_lang() : 'zh-cn';
            if ($lang !== 'zh-cn') {
                $col = 'value_' . str_replace('-', '_', $lang);
                foreach ($rows as $row) {
                    if (!empty($row[$col])) {
                        $cache[$row['name']] = $row[$col];
                    }
                }
            }
        } catch (\Throwable $e) {
            $cache = [];
        }

        return $cache;
    }

    public static function getValue(string $name, $default = null)
    {
        $all = self::allSettings();
        return array_key_exists($name, $all) ? $all[$name] : $default;
    }

    /** 批量保存 */
    public static function saveMany(array $data): void
    {
        foreach ($data as $name => $value) {
            $exists = Db::name('setting')->where('name', $name)->find();
            if ($exists) {
                Db::name('setting')->where('name', $name)->update(['value' => (string) $value]);
            } else {
                Db::name('setting')->insert(['name' => $name, 'value' => (string) $value, 'group' => 'site']);
            }
        }
    }
}
