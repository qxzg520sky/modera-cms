<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Db;

/**
 * 模块中心：主题 / 插件以 zip 模块包的形式「下载 → 安装 → 启用」。
 * 模块包放在 modules/store/（也可以用后台上传），zip 内是一个以模块名命名的目录，
 * 目录里放 theme.json（主题）或 plugin.json（插件）。
 */
class ModuleMarket
{
    /** 模块仓库目录 */
    public static function storeDir(): string
    {
        $dir = root_path() . 'modules' . DIRECTORY_SEPARATOR . 'store';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        return $dir;
    }

    /** 内置模块（安装时自带的，不允许卸载） */
    public static function core(): array
    {
        return ['theme' => ['default'], 'plugin' => ['example']];
    }

    /**
     * 扫描模块仓库里的 zip 包
     * @return array<int,array>
     */
    public static function packages(): array
    {
        $list = [];
        foreach (glob(self::storeDir() . DIRECTORY_SEPARATOR . '*.zip') ?: [] as $zip) {
            $meta = self::read($zip);
            if (!$meta) {
                $list[] = [
                    'file'   => basename($zip),
                    'type'   => 'unknown',
                    'name'   => basename($zip, '.zip'),
                    'title'  => basename($zip, '.zip'),
                    'version' => '',
                    'author' => '',
                    'description' => '无法识别的模块包（缺少 theme.json / plugin.json）',
                    'size'   => filesize($zip),
                    'installed' => false,
                    'enabled' => false,
                    'error'  => true,
                ];
                continue;
            }
            $meta['file'] = basename($zip);
            $meta['size'] = filesize($zip);
            $meta['installed'] = is_dir(self::targetDir($meta['type'], $meta['name']));
            $meta['enabled'] = $meta['installed'] ? self::isEnabled($meta['type'], $meta['name']) : false;
            $list[] = $meta;
        }
        usort($list, function ($a, $b) {
            return strcmp(($a['type'] ?? '') . ($a['name'] ?? ''), ($b['type'] ?? '') . ($b['name'] ?? ''));
        });
        return $list;
    }

    /** 读取 zip 内清单 */
    public static function read(string $zipPath): ?array
    {
        if (!is_file($zipPath) || !class_exists('ZipArchive')) {
            return null;
        }
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return null;
        }
        $manifestName = null;
        $manifestPath = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!$name) {
                continue;
            }
            $name = str_replace('\\', '/', $name);
            if (preg_match('#^(?:([^/]+)/)?(theme|plugin)\.json$#i', $name, $m)) {
                $manifestName = strtolower($m[2]);
                $manifestPath = $name;
                break;
            }
        }
        if (!$manifestPath) {
            $zip->close();
            return null;
        }
        $json = (string) $zip->getFromName($manifestPath);
        $zip->close();
        $meta = json_decode($json, true);
        if (!is_array($meta)) {
            return null;
        }
        $dirName = null;
        if (preg_match('#^([^/]+)/#', $manifestPath, $m)) {
            $dirName = $m[1];
        }
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) ($meta['name'] ?? $dirName ?? ''));
        if ($name === '') {
            return null;
        }
        return [
            'type'        => $manifestName,
            'name'        => $name,
            'title'       => (string) ($meta['title'] ?? $name),
            'version'     => (string) ($meta['version'] ?? '1.0'),
            'author'      => (string) ($meta['author'] ?? ''),
            'description' => (string) ($meta['description'] ?? ''),
        ];
    }

    public static function targetDir(string $type, string $name): string
    {
        return root_path() . ($type === 'theme' ? 'themes' : 'plugins') . DIRECTORY_SEPARATOR . $name;
    }

    public static function isEnabled(string $type, string $name): bool
    {
        try {
            if ($type === 'theme') {
                return (string) \app\model\Setting::getValue('theme', 'default') === $name;
            }
            return (bool) Db::name('plugin')->where('name', $name)->where('enabled', 1)->find();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** 安装模块包；$enable=true 时安装后立即启用 */
    public static function install(string $zipPath, bool $enable = false): array
    {
        if (!class_exists('ZipArchive')) {
            return [false, 'PHP 未安装 Zip 扩展，无法解压模块包'];
        }
        $meta = self::read($zipPath);
        if (!$meta) {
            return [false, '模块包无效：缺少 theme.json / plugin.json'];
        }
        $type = $meta['type'];
        $name = $meta['name'];
        if (!preg_match('/^[A-Za-z0-9_\-]{2,40}$/', $name)) {
            return [false, '模块名不合法'];
        }
        $target = self::targetDir($type, $name);
        if (is_dir($target)) {
            return [false, '模块「' . $meta['title'] . '」已经安装，无需重复安装'];
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return [false, '无法打开模块包'];
        }
        // 防目录穿越
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = str_replace('\\', '/', (string) $zip->getNameIndex($i));
            if (str_contains($entry, '..') || str_starts_with($entry, '/')) {
                $zip->close();
                return [false, '模块包包含非法路径'];
            }
        }

        $tmp = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'module_tmp_' . uniqid();
        if (!is_dir($tmp)) {
            @mkdir($tmp, 0777, true);
        }
        $zip->extractTo($tmp);
        $zip->close();

        // zip 里模块目录（以模块名命名）；如果直接是散文件，则整个 tmp 视作模块
        $src = $tmp . DIRECTORY_SEPARATOR . $name;
        if (!is_dir($src)) {
            $candidates = glob($tmp . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
            $src = $candidates ? $candidates[0] : $tmp;
        }
        if (!is_dir($src)) {
            self::removeDir($tmp);
            return [false, '模块包结构不正确'];
        }

        $parent = dirname($target);
        if (!is_dir($parent)) {
            @mkdir($parent, 0777, true);
        }
        if (!@rename($src, $target)) {
            // 跨盘等情况改为复制
            if (!self::copyDir($src, $target)) {
                self::removeDir($tmp);
                return [false, '写入模块目录失败，请检查写权限'];
            }
        }
        self::removeDir($tmp);

        // 注册到数据库 / 执行插件安装钩子
        $logs = [];
        try {
            if ($type === 'theme') {
                ThemeManager::scan();
                if ($enable) {
                    ThemeManager::enable($name);
                }
            } else {
                PluginManager::scan();
                $logs = PluginManager::install($name);
                if ($enable) {
                    PluginManager::toggle($name, 1);
                }
            }
        } catch (\Throwable $e) {
            return [false, '模块已解压，但注册时出错：' . $e->getMessage()];
        }

        $msg = '模块「' . $meta['title'] . '」安装成功' . ($enable ? '并已启用' : '，请到' . ($type === 'theme' ? '模板主题' : '插件扩展') . '里启用');
        if ($logs) {
            $msg .= '；' . implode('；', array_slice($logs, 0, 3));
        }
        return [true, $msg];
    }

    /** 保存后台上传的模块包，成功返回 [true, 提示, 包文件名] */
    public static function saveUpload($file): array
    {
        if (!$file) {
            return [false, '没有收到文件', ''];
        }
        $original = (string) $file->getOriginalName();
        if (strtolower(pathinfo($original, PATHINFO_EXTENSION)) !== 'zip') {
            return [false, '只支持 .zip 模块包', ''];
        }
        $tmpPath = $file->getPathname();
        $meta = self::read($tmpPath);
        if (!$meta) {
            return [false, '模块包无效：缺少 theme.json / plugin.json', ''];
        }
        $save = self::storeDir() . DIRECTORY_SEPARATOR . $meta['type'] . '-' . $meta['name'] . '.zip';
        if (!@copy($tmpPath, $save)) {
            return [false, '模块包保存失败，请检查 modules/store 写权限', ''];
        }
        return [true, '模块包已上传：' . basename($save), basename($save)];
    }

    /** 删除未安装的模块包 */
    public static function removePackage(string $file): array
    {
        $file = basename($file);
        if (!preg_match('/^[A-Za-z0-9_\-\.]+\.zip$/', $file)) {
            return [false, '文件名不合法'];
        }
        $path = self::storeDir() . DIRECTORY_SEPARATOR . $file;
        $meta = self::read($path);
        if ($meta && is_dir(self::targetDir($meta['type'], $meta['name']))) {
            return [false, '该模块已经安装，不能删除模块包'];
        }
        if (!is_file($path) || !@unlink($path)) {
            return [false, '删除失败'];
        }
        return [true, '模块包已删除'];
    }

    private static function copyDir(string $src, string $dst): bool
    {
        if (!is_dir($dst) && !@mkdir($dst, 0777, true)) {
            return false;
        }
        foreach (scandir($src) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $from = $src . DIRECTORY_SEPARATOR . $item;
            $to   = $dst . DIRECTORY_SEPARATOR . $item;
            if (is_dir($from)) {
                if (!self::copyDir($from, $to)) {
                    return false;
                }
            } elseif (!@copy($from, $to)) {
                return false;
            }
        }
        return true;
    }

    private static function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                self::removeDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
