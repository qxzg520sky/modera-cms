<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Db;

/**
 * 插件管理：plugins/{name}/plugin.json
 * 插件可自带 路由 / 控制器 / 模板 / 后台菜单，启用即生效，停用即摘除。
 */
class PluginManager
{
    /** 扫描插件目录并同步到数据库 */
    public static function scan(): array
    {
        $dir  = root_path() . 'plugins';
        $list = [];
        foreach (glob($dir . '/*', GLOB_ONLYDIR) as $path) {
            $name = basename($path);
            $json = $path . '/plugin.json';
            if (!is_file($json)) {
                continue;
            }
            $meta = json_decode((string) file_get_contents($json), true) ?: [];
            $row  = [
                'name'        => $name,
                'title'       => $meta['title'] ?? $name,
                'version'     => $meta['version'] ?? '1.0',
                'author'      => $meta['author'] ?? '',
                'description' => $meta['description'] ?? '',
            ];
            $exists = Db::name('plugin')->where('name', $name)->find();
            if ($exists) {
                Db::name('plugin')->where('name', $name)->update($row);
                $row['enabled'] = (int) $exists['enabled'];
            } else {
                Db::name('plugin')->insert($row + ['enabled' => 0, 'config' => '']);
                $row['enabled'] = 0;
            }
            $row['meta'] = $meta;
            $list[] = $row;
        }
        return $list;
    }

    /** 已启用的插件名列表 */
    public static function enabled(): array
    {
        static $cache = null;
        if ($cache === null) {
            try {
                $cache = Db::name('plugin')->where('enabled', 1)->column('name');
            } catch (\Throwable $e) {
                $cache = [];
            }
        }
        return $cache;
    }

    public static function toggle(string $name, int $enabled): bool
    {
        if (!is_dir(root_path() . 'plugins/' . $name)) {
            return false;
        }

        // 启用前先执行插件的 install.php（建表、建字段等）
        if ($enabled) {
            self::install($name);
        } else {
            // 停用前执行插件的 disable.php（清理运行痕迹、恢复站点原始行为）
            self::hook($name, 'disable.php');
        }

        Db::name('plugin')->where('name', $name)->update(['enabled' => $enabled ? 1 : 0]);
        return true;
    }

    /** 执行 plugins/{name}/install.php，返回日志 */
    public static function install(string $name): array
    {
        return self::hook($name, 'install.php');
    }

    /**
     * 执行 uninstall.php、删除插件目录并清理 tp_plugin 记录。
     * 卸载后要重新使用，可在应用市场重新下载安装。
     *
     * @return array{0:bool,1:array<int,string>}
     */
    public static function uninstall(string $name): array
    {
        if (!preg_match('/^[A-Za-z0-9_\-]{2,40}$/', $name)) {
            return [false, ['插件名不合法']];
        }
        if (in_array($name, ['example'], true)) {
            return [false, ['内置示例插件不能卸载']];
        }
        $dir = root_path() . 'plugins' . DIRECTORY_SEPARATOR . $name;
        if (!is_dir($dir)) {
            return [false, ['插件目录不存在：plugins/' . $name]];
        }

        $logs = self::hook($name, 'uninstall.php');
        Db::name('plugin')->where('name', $name)->update(['enabled' => 0]);

        if (!self::removeDir($dir)) {
            return [false, array_merge($logs, ['插件目录删除失败，请检查文件权限或先停用插件'])];
        }
        Db::name('plugin')->where('name', $name)->delete();
        $logs[] = '插件文件已删除：plugins/' . $name;

        return [true, $logs];
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
            if (is_link($path) || is_file($path)) {
                if (!@unlink($path)) {
                    return false;
                }
            } elseif (!self::removeDir($path)) {
                return false;
            }
        }
        return @rmdir($dir);
    }

    /** 运行插件钩子文件 */
    private static function hook(string $name, string $file): array
    {
        $path = root_path() . 'plugins/' . $name . '/' . $file;
        if (!is_file($path)) {
            return [];
        }

        try {
            $ret = include $path;
            return is_array($ret) ? array_values(array_map('strval', $ret)) : [];
        } catch (\Throwable $e) {
            return ['执行 ' . $file . ' 出错：' . $e->getMessage()];
        }
    }

    /** 读取插件清单 */
    public static function manifest(string $name): array
    {
        $json = root_path() . 'plugins/' . $name . '/plugin.json';
        return is_file($json) ? (json_decode((string) file_get_contents($json), true) ?: []) : [];
    }
}
