<?php
declare(strict_types=1);

namespace plugins\cache\service;

use think\facade\Db;
use think\Request;
use think\Response;

/**
 * 缓存优化插件核心：
 * 1) 首页 / 文章页 HTML 静态缓存（拦截在中间件里，命中直接吐文件，不查库不渲染）
 * 2) 静态资源过期头（改写 public/.htaccess 里的独立区块，只作用于 /uploads 与 /static）
 * 3) 清理缓存、后台写操作自动失效
 */
class Cache
{
    public const PLUGIN = 'cache';

    /** public/.htaccess 里的区块标记，卸载/关闭时整块移除 */
    private const MARK_BEGIN = '# BEGIN cache-plugin';
    private const MARK_END   = '# END cache-plugin';

    public static function defaults(): array
    {
        return [
            'home'       => '1',   // 首页缓存（启用插件即生效，可在后台关）
            'article'    => '1',   // 文章页缓存
            'ttl'        => '30',  // 分钟
            'assets'     => '1',   // 静态资源过期头
            'asset_days' => '365', // 天
        ];
    }

    public static function cfg(): array
    {
        $cfg = self::defaults();
        try {
            $row = Db::name('plugin')->where('name', self::PLUGIN)->find() ?: [];
            if (!empty($row['config'])) {
                $j = json_decode((string) $row['config'], true);
                if (is_array($j)) {
                    $cfg = array_merge($cfg, $j);
                }
            }
        } catch (\Throwable $e) {
        }
        return $cfg;
    }

    public static function saveCfg(array $data): void
    {
        $cfg = array_merge(self::cfg(), $data);
        Db::name('plugin')->where('name', self::PLUGIN)->update([
            'config' => json_encode($cfg, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /* ==================== 目录 / 文件 ==================== */

    public static function dir(): string
    {
        return root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'pagecache';
    }

    public static function ensureDir(): bool
    {
        $dir = self::dir();
        return is_dir($dir) || @mkdir($dir, 0777, true);
    }

    public static function files(): array
    {
        $dir = self::dir();
        if (!is_dir($dir)) {
            return [];
        }
        $out = [];
        foreach ((array) glob($dir . DIRECTORY_SEPARATOR . '*.html') as $f) {
            if (is_file($f)) {
                $out[] = $f;
            }
        }
        return $out;
    }

    public static function stats(): array
    {
        $files = self::files();
        $size  = 0;
        foreach ($files as $f) {
            $size += (int) @filesize($f);
        }
        return ['count' => count($files), 'size' => $size];
    }

    /** 清空页面缓存，返回 [删除数, 释放字节] */
    public static function clear(): array
    {
        $files = self::files();
        $size  = 0;
        $n     = 0;
        foreach ($files as $f) {
            $size += (int) @filesize($f);
            if (@unlink($f)) {
                $n++;
            }
            @unlink($f . '.json');
        }
        return [$n, $size];
    }

    /** 后台列表用：最近的缓存条目（路径 / 类型 / 时间 / 大小） */
    public static function entries(int $limit = 20): array
    {
        $dir = self::dir();
        if (!is_dir($dir)) {
            return [];
        }
        $out = [];
        foreach ((array) glob($dir . DIRECTORY_SEPARATOR . '*.html') as $f) {
            if (!is_file($f)) {
                continue;
            }
            $meta = [];
            if (is_file($f . '.json')) {
                $meta = (array) json_decode((string) @file_get_contents($f . '.json'), true);
            }
            $out[] = [
                'path' => (string) ($meta['path'] ?? ''),
                'type' => (string) ($meta['type'] ?? ''),
                'time' => (int) @filemtime($f),
                'size' => (int) @filesize($f),
            ];
        }
        usort($out, static function ($a, $b) {
            return $b['time'] <=> $a['time'];
        });
        return array_slice($out, 0, max(1, $limit));
    }

    /* ==================== 静态资源过期头 ==================== */

    public static function htaccessPath(): string
    {
        return root_path() . 'public' . DIRECTORY_SEPARATOR . '.htaccess';
    }

    public static function syncHtaccess(): string
    {
        $file = self::htaccessPath();
        if (!is_file($file) || !is_writable($file)) {
            return 'public/.htaccess 不可写，跳过过期头写入';
        }
        $raw   = (string) file_get_contents($file);
        $clean = self::stripBlock($raw);
        $cfg   = self::cfg();
        $block = '';

        if ((string) $cfg['assets'] === '1') {
            $days = max(1, min(3650, (int) $cfg['asset_days']));
            $secs = $days * 86400;
            // mod_expires 没装，改用 mod_headers 直接写 Expires / Cache-Control
            $block = self::MARK_BEGIN . "\n"
                . "<IfModule mod_headers.c>\n"
                . "  # 图片/字体/脚本/媒体：长缓存，到期前不再回源（只作用于 /uploads 与 /static）\n"
                . "  <If \"%{REQUEST_URI} =~ m#^/(uploads|static)/#\">\n"
                . "    Header set Cache-Control \"public, max-age={$secs}, immutable\"\n"
                . "    Header set Expires \"Wed, 31 Dec 2037 23:55:55 GMT\"\n"
                . "    Header unset Pragma\n"
                . "  </If>\n"
                . "</IfModule>\n"
                . self::MARK_END . "\n";
        }

        $new = rtrim($clean) . "\n\n" . $block;
        $tmp = $file . '.tmp';
        if (@file_put_contents($tmp, $new) === false || !@rename($tmp, $file)) {
            @unlink($tmp);
            return 'public/.htaccess 写入失败（检查目录权限）';
        }
        return (string) $cfg['assets'] === '1'
            ? '静态资源过期头已写入 /uploads 与 /static（' . (int) $cfg['asset_days'] . ' 天）'
            : '静态资源过期头已移除';
    }

    private static function stripBlock(string $raw): string
    {
        $pos  = strpos($raw, self::MARK_BEGIN);
        $endS = strpos($raw, self::MARK_END);
        if ($pos === false || $endS === false) {
            return $raw;
        }
        $end = $endS + strlen(self::MARK_END);
        // 顺带吃掉标记后面那个换行
        if (substr($raw, $end, 2) === "\r\n") {
            $end += 2;
        } elseif (substr($raw, $end, 1) === "\n") {
            $end += 1;
        }
        return substr($raw, 0, $pos) . substr($raw, $end);
    }

    /** 插件被停用时调用（不清配置，方便再启用）：清页面缓存 + 摘掉过期头区块 */
    public static function disableCleanup(): array
    {
        $logs = [];
        $ret  = self::clear();
        $logs[] = '停用清理：删除 ' . $ret[0] . ' 个页面缓存文件';

        $file = self::htaccessPath();
        if (!is_file($file) || !is_writable($file)) {
            $logs[] = '.htaccess 不可写，跳过（如仍有过期头请手动删除 cache-plugin 区块）';
            return $logs;
        }
        $raw = (string) file_get_contents($file);
        if (strpos($raw, self::MARK_BEGIN) === false) {
            $logs[] = '.htaccess 里没有过期头区块，无需移除';
            return $logs;
        }
        $tmp = $file . '.tmp';
        if (@file_put_contents($tmp, rtrim(self::stripBlock($raw)) . "\n") !== false && @rename($tmp, $file)) {
            $logs[] = '已移除 .htaccess 里的静态资源过期头';
        } else {
            @unlink($tmp);
            $logs[] = '.htaccess 写入失败，过期头区块可能残留';
        }
        return $logs;
    }

    /* ==================== 请求判断 ==================== */

    /** 当前请求属于哪种可缓存页面：home / article / ''（不缓存） */
    public static function pageType(Request $request, array $cfg): string
    {
        if (!$request->isGet()) {
            return '';
        }
        $qs = (string) $request->server('QUERY_STRING', '');
        if ($qs !== '') {
            // Apache 重写会补一个内部参数 s=/路径，这个不算用户参数
            parse_str($qs, $q);
            unset($q['s']);
            if ($q) {
                return ''; // 真正带参数的一律不缓存，避免 ?v= 把老页面钉死
            }
        }

        $path = '/' . trim((string) $request->pathinfo(), '/');
        if ($path === '//' || $path === '') {
            $path = '/';
        }

        // 首页（含语言前缀 /en）
        $isHome = in_array($path, ['/', '/en', '/en/'], true);
        if ($isHome) {
            return (string) $cfg['home'] === '1' ? 'home' : '';
        }

        // 文章页 /a/12 与 /en/a/12
        if (preg_match('#^(?:/en)?/a/(\d+)/?$#', $path)) {
            return (string) $cfg['article'] === '1' ? 'article' : '';
        }

        return '';
    }

    public static function cacheFile(string $path): string
    {
        return self::dir() . DIRECTORY_SEPARATOR . md5($path) . '.html';
    }

    /** 命中则返回 Response，未命中返回 null */
    public static function hit(string $path, array $cfg): ?Response
    {
        $file = self::cacheFile($path);
        if (!is_file($file)) {
            return null;
        }
        $ttl = (int) $cfg['ttl'] * 60;
        $mtime = (int) @filemtime($file);
        if ($ttl <= 0 || $mtime + $ttl < time()) {
            return null;
        }
        $html = (string) @file_get_contents($file);
        if (strlen($html) < 512) {
            return null;
        }
        return response($html, 200, [
            'Content-Type'  => 'text/html; charset=utf-8',
            'X-Page-Cache'  => 'HIT',
            'Age'           => (string) max(0, time() - $mtime),
            'Cache-Control' => 'public, max-age=' . max(0, $ttl - (time() - $mtime)),
        ]);
    }

    public static function put(string $path, string $html, string $type = ''): bool
    {
        if (!self::ensureDir() || strlen($html) < 512) {
            return false;
        }
        $file = self::cacheFile($path);
        $tmp  = $file . '.' . getmypid() . '.tmp';
        if (@file_put_contents($tmp, $html) === false) {
            return false;
        }
        if (!@rename($tmp, $file)) {
            @unlink($tmp);
            return false;
        }
        @file_put_contents($file . '.json', json_encode([
            'path' => $path,
            'type' => $type,
            'time' => time(),
        ], JSON_UNESCAPED_UNICODE));
        return true;
    }

    /* ==================== 安装 / 卸载 ==================== */

    public static function install(): array
    {
        $logs = [];
        $logs[] = self::ensureDir() ? '缓存目录 runtime/pagecache 就绪' : '缓存目录创建失败';
        $logs[] = self::syncHtaccess();
        return $logs;
    }

    public static function uninstall(): array
    {
        $logs = [];
        $ret  = self::clear();
        $logs[] = '已删除 ' . $ret[0] . ' 个页面缓存文件';
        $dir   = self::dir();
        if (is_dir($dir)) {
            @rmdir($dir);
            $logs[] = is_dir($dir) ? '缓存目录非空（有别的文件），未删除' : '缓存目录已删除';
        }

        // 摘掉 .htaccess 里的过期头区块
        $file = self::htaccessPath();
        if (is_file($file) && is_writable($file)) {
            $raw = (string) file_get_contents($file);
            $new = rtrim(self::stripBlock($raw)) . "\n";
            @file_put_contents($file, $new);
            $logs[] = 'public/.htaccess 里的过期头配置已移除';
        }

        try {
            Db::name('plugin')->where('name', self::PLUGIN)->update(['config' => '']);
            $logs[] = '插件配置已清空';
        } catch (\Throwable $e) {
            $logs[] = '配置清理失败：' . $e->getMessage();
        }
        return $logs;
    }
}
