<?php
declare(strict_types=1);

namespace app\service;

use app\model\Theme;
use think\facade\Db;

/**
 * 模板（主题）管理：themes/{name}/theme.json
 */
class ThemeManager
{
    /** 当前启用主题 */
    public static function current(): string
    {
        static $theme = null;
        if ($theme !== null) {
            return $theme;
        }
        $theme = (string) \app\model\Setting::getValue('theme', 'default');
        if (!is_dir(root_path() . 'themes/' . $theme)) {
            $theme = 'default';
        }
        return $theme;
    }

    /** 扫描 themes 目录并同步到数据库 */
    public static function scan(): array
    {
        $dir  = root_path() . 'themes';
        $list = [];
        foreach (glob($dir . '/*', GLOB_ONLYDIR) as $path) {
            $name = basename($path);
            $json = $path . '/theme.json';
            $meta = is_file($json) ? (json_decode((string) file_get_contents($json), true) ?: []) : [];
            $row  = [
                'name'        => $name,
                'title'       => $meta['title'] ?? $name,
                'version'     => $meta['version'] ?? '1.0',
                'author'      => $meta['author'] ?? '',
                'description' => $meta['description'] ?? '',
                'screenshot'  => is_file($path . '/screenshot.png') ? 'themes/' . $name . '/screenshot.png' : '',
            ];
            $exists = Db::name('theme')->where('name', $name)->find();
            if ($exists) {
                Db::name('theme')->where('name', $name)->update($row);
            } else {
                Db::name('theme')->insert($row);
            }
            $row['enabled'] = ((string) \app\model\Setting::getValue('theme', 'default') === $name) ? 1 : 0;
            $list[] = $row;
        }
        return $list;
    }

    /** 启用主题 */
    public static function enable(string $name): bool
    {
        if (!is_dir(root_path() . 'themes/' . $name)) {
            return false;
        }
        \app\model\Setting::saveMany(['theme' => $name]);
        Db::name('theme')->where('enabled', 1)->update(['enabled' => 0]);
        Db::name('theme')->where('name', $name)->update(['enabled' => 1]);
        return true;
    }

    /**
     * 卸载主题：删除 themes/{name} 目录并清理 tp_theme 记录。
     * 内置主题与当前启用主题不允许卸载。
     */
    public static function uninstall(string $name): array
    {
        $name = trim($name);
        if (!preg_match('/^[A-Za-z0-9_\-]{2,40}$/', $name)) {
            return [false, '主题名不合法'];
        }
        if (in_array($name, ['default'], true)) {
            return [false, '默认模板为内置主题，不能卸载'];
        }
        if (self::current() === $name) {
            return [false, '当前正在使用的模板不能卸载，请先切换到其他模板'];
        }
        $dir = root_path() . 'themes/' . $name;
        if (!is_dir($dir)) {
            return [false, '模板目录不存在：themes/' . $name];
        }
        if (!self::removeDir($dir)) {
            return [false, '删除模板目录失败，请检查文件权限'];
        }
        Db::name('theme')->where('name', $name)->delete();
        return [true, '模板「' . $name . '」已卸载'];
    }

    private static function removeDir(string $dir): bool
    {
        if (!is_dir($dir)) {
            return true;
        }
        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                if (!self::removeDir($path)) {
                    return false;
                }
            } elseif (!@unlink($path)) {
                return false;
            }
        }
        return @rmdir($dir);
    }
}
