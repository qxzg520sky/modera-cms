<?php
declare(strict_types=1);

namespace app\service;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * 主程序在线更新
 *
 * 只更新程序核心文件，不碰 .env、uploads、themes、plugins、modules、vendor 和数据库。
 * 每次正式更新前自动打包备份到 runtime/update/，支持回滚最近一次更新。
 */
class Updater
{
    private string $root;
    private string $dir;
    private string $caFile = '';

    /** 默认更新范围，可被 version.json 的 update.include 覆盖 */
    private const DEFAULT_INCLUDE = [
        'app',
        'route',
        'config',
        'extend',
        'error',
        'public/static/admin',
        'version.json',
    ];

    private const DEFAULT_EXCLUDE = [
        'config/update.php',
    ];

    public function __construct(string $root = '')
    {
        if ($root === '') {
            $root = function_exists('root_path') ? root_path() : dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
        }
        $this->root = rtrim(str_replace('\\', '/', $root), '/');
        $this->dir  = $this->root . '/runtime/update';
        $this->ensureDir($this->dir);

        // Windows 版 PHP 常常没有配置 CA；优先用内置官方 CA 包，其次才用 php.ini 的路径。
        // 宝塔 open_basedir 通常不允许访问 /etc/ssl/...，不能直接 is_file() 探测系统路径。
        $iniCa = (string) (ini_get('curl.cainfo') ?: ini_get('openssl.cafile'));
        $ca    = '';
        foreach ([__DIR__ . '/cacert.pem', $iniCa] as $candidate) {
            if ($candidate !== '' && @is_file($candidate) && @is_readable($candidate)) {
                $ca = $candidate;
                break;
            }
        }
        $this->caFile = $ca;
    }

    public function root(): string
    {
        return $this->root;
    }

    /** 本地版本与最近一次在线更新状态 */
    public function current(): array
    {
        $meta  = $this->meta();
        $state = $this->readJson($this->dir . '/state.json', []);
        return [
            'name'          => (string) ($meta['name'] ?? 'modera-cms'),
            'version'       => (string) ($meta['version'] ?? '未知'),
            'release'       => (string) ($meta['release'] ?? ''),
            'revision'      => (string) ($state['revision'] ?? ''),
            'updated_at'    => (string) ($state['updated_at'] ?? ''),
            'mirror'        => (string) ($state['mirror'] ?? ''),
            'branch'        => (string) ($state['branch'] ?? ''),
            'backup'        => (string) ($state['backup'] ?? ''),
            'backup_size'   => (int) ($state['backup_size'] ?? 0),
            'previous'      => $state['previous'] ?? null,
        ];
    }

    /** 读取并校验更新设置 */
    public function settings(): array
    {
        $meta = $this->meta();
        $repo = (array) ($meta['repo'] ?? []);
        $default = [
            'mirror'  => 'github',
            'branch'  => (string) ($repo['branch'] ?? 'main'),
            'github'  => (string) ($repo['github'] ?? 'qxzg520sky/modera-cms'),
            'gitee'   => (string) ($repo['gitee'] ?? '51iqx/modera-cms'),
        ];
        $saved = $this->readJson($this->dir . '/settings.json', []);
        return $this->sanitizeSettings(array_merge($default, is_array($saved) ? $saved : []));
    }

    public function saveSettings(array $input): array
    {
        $settings = $this->sanitizeSettings(array_merge($this->settings(), $input));
        $this->writeJson($this->dir . '/settings.json', $settings);
        return $settings;
    }

    private function sanitizeSettings(array $s): array
    {
        $mirror = strtolower((string) ($s['mirror'] ?? 'github'));
        if (!in_array($mirror, ['github', 'gitee'], true)) {
            $mirror = 'github';
        }
        $branch = trim((string) ($s['branch'] ?? 'main'));
        if ($branch === '' || !preg_match('/^[A-Za-z0-9._\/-]{1,80}$/', $branch) || str_contains($branch, '..')) {
            $branch = 'main';
        }
        foreach (['github', 'gitee'] as $k) {
            $repo = trim((string) ($s[$k] ?? ''));
            if (!preg_match('#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#', $repo)) {
                $repo = $k === 'github' ? 'qxzg520sky/modera-cms' : '51iqx/modera-cms';
            }
            $s[$k] = $repo;
        }
        $s['mirror'] = $mirror;
        $s['branch'] = $branch;
        return $s;
    }

    /** 检查远端最新提交和版本，不写任何文件 */
    public function remote(array $opt = []): array
    {
        $s      = $this->sanitizeSettings(array_merge($this->settings(), $opt));
        $repo   = $s[$s['mirror']];
        $branch = $s['branch'];
        $mirror = $s['mirror'];

        $archive = $this->archiveUrl($mirror, $repo, $branch);
        $rawVer  = $this->rawUrl($mirror, $repo, $branch, 'version.json');

        $remoteVersion = null;
        try {
            $json = $this->fetchJson($rawVer, 10, true);
            if (is_array($json)) {
                $remoteVersion = [
                    'version' => (string) ($json['version'] ?? ''),
                    'release' => (string) ($json['release'] ?? ''),
                    'notes'   => (string) ($json['notes'] ?? ''),
                ];
            }
        } catch (Throwable $e) {
            // 没有 version.json 不影响更新，继续用提交号判断
        }

        if ($remoteVersion === null) {
            try {
                $api = $mirror === 'github'
                    ? 'https://api.github.com/repos/' . $repo . '/contents/version.json?ref=' . rawurlencode($branch)
                    : 'https://gitee.com/api/v5/repos/' . $repo . '/contents/version.json?ref=' . rawurlencode($branch);
                $j = $this->fetchJson($api, 10, true);
                if (is_array($j) && !empty($j['content'])) {
                    $text = base64_decode(str_replace(["\n", "\r"], '', (string) $j['content']));
                    $json = json_decode((string) $text, true);
                    if (is_array($json)) {
                        $remoteVersion = [
                            'version' => (string) ($json['version'] ?? ''),
                            'release' => (string) ($json['release'] ?? ''),
                            'notes'   => (string) ($json['notes'] ?? ''),
                        ];
                    }
                }
            } catch (Throwable $e) {
                // raw 域名被拦截时用 API 兜底；都失败则继续用提交号
            }
        }

        $commit = [];
        if ($mirror === 'github') {
            try {
                $api = 'https://api.github.com/repos/' . $repo . '/commits/' . rawurlencode($branch);
                $j   = $this->fetchJson($api, 15, true);
                if (is_array($j)) {
                    $commit = [
                        'sha'     => (string) ($j['sha'] ?? ''),
                        'date'    => (string) ($j['commit']['committer']['date'] ?? $j['commit']['author']['date'] ?? ''),
                        'message' => trim((string) ($j['commit']['message'] ?? '')),
                    ];
                }
            } catch (Throwable $e) {
                // API 限流时仍然可以更新，下面用压缩包响应头代替
            }
        } elseif ($mirror === 'gitee') {
            try {
                $api = 'https://gitee.com/api/v5/repos/' . $repo . '/branches/' . rawurlencode($branch);
                $j   = $this->fetchJson($api, 15, true);
                if (is_array($j) && !empty($j['commit'])) {
                    $c = $j['commit'];
                    $commit = [
                        'sha'     => (string) ($c['sha'] ?? ''),
                        'date'    => (string) ($c['commit']['committer']['date'] ?? $c['commit']['author']['date'] ?? ''),
                        'message' => trim((string) ($c['commit']['message'] ?? '')),
                    ];
                }
            } catch (Throwable $e) {
                // 无 token 或限流时退回收缩包响应头
            }
        }

        $head = [];
        try {
            $head = $this->head($archive, 20);
        } catch (Throwable $e) {
            $head = ['error' => $e->getMessage()];
        }

        $revision = $commit['sha'] ?? '';
        if ($revision === '') {
            $revision = trim((string) ($head['etag'] ?? ''), " \t\n\r\0\x0B\"");
        }
        if ($revision === '') {
            $revision = (string) ($head['last_modified'] ?? '');
        }

        $local  = $this->current();
        $latest = $remoteVersion['version'] ?? '';
        $available = null;
        $commitDate = substr((string) ($commit['date'] ?? ''), 0, 10);
        $release = (string) ($local['release'] ?? '');
        $downgrade = preg_match('/^\d{4}-\d{2}-\d{2}$/', $release)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $commitDate)
            && $commitDate < $release;
        if ($latest !== '' && $local['version'] !== '未知') {
            $available = version_compare($latest, $local['version'], '>');
            if (!$available && $revision !== '' && $local['revision'] !== '') {
                $available = $revision !== $local['revision'];
            }
        } elseif ($revision !== '') {
            $available = $local['revision'] === '' ? true : ($revision !== $local['revision']);
        }
        if ($downgrade) {
            $available = false;
        }

        $result = [
            'mirror'           => $mirror,
            'repo'             => $repo,
            'branch'           => $branch,
            'archive_url'      => $archive,
            'archive_status'   => (int) ($head['status'] ?? 0),
            'archive_size'     => (int) ($head['size'] ?? 0),
            'revision'         => $revision,
            'commit'           => $commit,
            'version'          => $remoteVersion,
            'update_available' => $available,
            'downgrade'        => $downgrade,
            'checked_at'       => date('Y-m-d H:i:s'),
        ];
        if (!empty($head['error'])) {
            $result['error'] = $head['error'];
        }
        $this->writeJson($this->dir . '/last_check.json', $result);
        return $result;
    }

    /**
     * 拒绝用比本地更旧的远端覆盖：防止远端分支落后时把本地功能“更新”掉
     */
    private function guardDowngrade(array $remote): void
    {
        $local = $this->current();
        $remoteVersion = (string) ($remote['version']['version'] ?? '');
        $localVersion  = (string) ($local['version'] ?? '');
        if ($remoteVersion !== '' && $localVersion !== '' && $localVersion !== '未知'
            && version_compare($remoteVersion, $localVersion, '<')) {
            throw new RuntimeException('远端版本 v' . $remoteVersion . ' 低于本地 v' . $localVersion . '，已拒绝降级');
        }

        $release = (string) ($local['release'] ?? '');
        $commitDate = substr((string) ($remote['commit']['date'] ?? ''), 0, 10);
        $mirrorName = (string) ($remote['mirror'] ?? '远端');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $release)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $commitDate)
            && $commitDate < $release) {
            throw new RuntimeException('远端最新提交（' . $commitDate . '）早于本地版本发布日期（' . $release . '），已拒绝降级。请先把新代码推送到 ' . $mirrorName);
        }
    }

    /**
     * 执行更新
     * @param bool $dryRun 只下载并列出会发生变化的文件，不写入
     */
    public function apply(array $opt = [], bool $dryRun = false): array
    {
        $s = $this->saveSettings($opt);
        $lock = fopen($this->dir . '/update.lock', 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            throw new RuntimeException('已有更新任务正在进行，请稍后再试');
        }

        $stamp = date('Ymd-His');
        $zipFile = $this->dir . '/download-' . $stamp . '.zip';
        $stage   = $this->dir . '/stage-' . $stamp;
        $backup  = $this->dir . '/backup-' . $stamp . '.zip';
        $log     = [];
        $result  = [];

        try {
            $remote = $this->remote($s);
            if (!empty($remote['error']) && (int) $remote['archive_status'] === 0) {
                throw new RuntimeException('无法访问更新源：' . $remote['error']);
            }
            if ((int) $remote['archive_status'] !== 200) {
                throw new RuntimeException('更新源返回 HTTP ' . (int) $remote['archive_status']);
            }

            $log[] = '更新源：' . $s['mirror'] . ' / ' . $s[$s['mirror']] . ' / ' . $s['branch'];
            if (!$dryRun) {
                $this->guardDowngrade($remote);
            }
            $this->download($remote['archive_url'], $zipFile, 120 * 1024 * 1024);
            $log[] = '源码包下载完成：' . $this->humanSize((int) filesize($zipFile));

            $this->extract($zipFile, $stage, $log);
            $source = $this->detectSourceRoot($stage);
            if ($source === '') {
                throw new RuntimeException('源码包结构异常：没有找到 app/ 与 route/app.php');
            }
            $log[] = '源码包解压完成：' . basename($source);

            $changed = $this->collectChanges($source);
            $log[] = '待更新文件：' . count($changed) . ' 个';
            foreach (array_slice(array_keys($changed), 0, 80) as $rel) {
                $log[] = '  ' . $rel;
            }
            if (count($changed) > 80) {
                $log[] = '  ……其余 ' . (count($changed) - 80) . ' 个文件略';
            }

            $newVersion = '';
            $verFile = $source . '/version.json';
            if (is_file($verFile)) {
                $vj = json_decode((string) file_get_contents($verFile), true);
                $newVersion = is_array($vj) ? (string) ($vj['version'] ?? '') : '';
            }

            if ($dryRun) {
                $log[] = '试运行结束：未写入任何文件。';
                $result = [
                    'ok'      => true,
                    'dry_run' => true,
                    'changed' => count($changed),
                    'files'   => array_keys($changed),
                    'version' => $newVersion,
                    'remote'  => $remote,
                    'log'     => $log,
                ];
                $this->writeJson($this->dir . '/last_result.json', $result);
                $this->removeDir($stage);
                @unlink($zipFile);
                return $result;
            }

            if (!$changed) {
                $log[] = '远端文件与本地一致，无需更新。';
                $this->writeJson($this->dir . '/last_result.json', [
                    'ok' => true, 'dry_run' => false, 'changed' => 0,
                    'version' => $newVersion, 'remote' => $remote, 'log' => $log,
                ]);
                $this->removeDir($stage);
                @unlink($zipFile);
                return $this->readJson($this->dir . '/last_result.json', []);
            }

            $oldState = $this->readJson($this->dir . '/state.json', []);
            $backupInfo = $this->backup($backup);
            $log[] = '已备份 ' . $backupInfo['count'] . ' 个文件到 runtime/update/' . basename($backup);

            try {
                $this->copyChanges($changed);
            } catch (Throwable $e) {
                $log[] = '写入失败，开始自动回滚：' . $e->getMessage();
                try {
                    $this->restoreFromZip($backup);
                    $log[] = '自动回滚完成。';
                } catch (Throwable $re) {
                    $log[] = '自动回滚失败：' . $re->getMessage();
                }
                throw new RuntimeException('更新失败：' . $e->getMessage(), 0, $e);
            }

            $newState = [
                'version'     => $newVersion !== '' ? $newVersion : ($this->current()['version'] ?? ''),
                'revision'    => (string) ($remote['revision'] ?? ''),
                'mirror'      => $s['mirror'],
                'branch'      => $s['branch'],
                'updated_at'  => date('Y-m-d H:i:s'),
                'backup'      => 'runtime/update/' . basename($backup),
                'backup_size' => (int) filesize($backup),
                'previous'    => $oldState ?: null,
            ];
            $this->writeJson($this->dir . '/state.json', $newState);
            $this->clearCompiledCache();

            $log[] = '更新完成，共写入 ' . count($changed) . ' 个文件。';
            $log[] = '已清理编译缓存；如页面异常，可在本页回滚。';
            $result = [
                'ok'      => true,
                'dry_run' => false,
                'changed' => count($changed),
                'version' => $newVersion,
                'backup'  => $newState['backup'],
                'remote'  => $remote,
                'log'     => $log,
            ];
            $this->writeJson($this->dir . '/last_result.json', $result);
            $this->cleanup();
            return $result;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
            @unlink($zipFile);
        }
    }

    /** 回滚最近一次正式更新 */
    public function rollback(): array
    {
        $state = $this->readJson($this->dir . '/state.json', []);
        $rel = (string) ($state['backup'] ?? '');
        if ($rel === '') {
            throw new RuntimeException('没有可回滚的更新备份');
        }
        $file = $this->root . '/' . ltrim(str_replace('\\', '/', $rel), '/');
        if (!is_file($file)) {
            throw new RuntimeException('备份文件不存在：' . $rel);
        }

        $lock = fopen($this->dir . '/update.lock', 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            throw new RuntimeException('已有更新任务正在进行，请稍后再试');
        }
        try {
            $log = ['从 ' . $rel . ' 回滚'];
            $this->restoreFromZip($file);
            $log[] = '文件已恢复。';

            $prev = $state['previous'] ?? null;
            if (is_array($prev)) {
                $this->writeJson($this->dir . '/state.json', $prev);
            } else {
                $restored = $this->readJson($this->dir . '/state.json', []);
                $restored['updated_at'] = '';
                $restored['revision'] = '';
                $restored['backup'] = '';
                $restored['backup_size'] = 0;
                $this->writeJson($this->dir . '/state.json', $restored);
            }
            $this->clearCompiledCache();
            $log[] = '编译缓存已清理。';
            $result = ['ok' => true, 'log' => $log, 'backup' => $rel];
            $this->writeJson($this->dir . '/last_result.json', $result);
            return $result;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /** 最近一次检查结果，用于页面直接展示 */
    public function lastCheck(): array
    {
        return $this->readJson($this->dir . '/last_check.json', []);
    }

    public function lastResult(): array
    {
        return $this->readJson($this->dir . '/last_result.json', []);
    }

    // ------------------------------------------------------------------

    private function meta(): array
    {
        return $this->readJson($this->root . '/version.json', []);
    }

    private function includePaths(): array
    {
        $meta = $this->meta();
        $inc = $meta['update']['include'] ?? self::DEFAULT_INCLUDE;
        if (!is_array($inc) || !$inc) {
            $inc = self::DEFAULT_INCLUDE;
        }
        $clean = [];
        foreach ($inc as $p) {
            $p = trim(str_replace('\\', '/', (string) $p), '/');
            if ($p === '' || str_contains($p, '..') || str_starts_with($p, '.')) {
                continue;
            }
            $clean[] = $p;
        }
        return $clean ?: self::DEFAULT_INCLUDE;
    }

    private function excludePaths(): array
    {
        $meta = $this->meta();
        $exc = $meta['update']['exclude'] ?? self::DEFAULT_EXCLUDE;
        return is_array($exc) ? array_values(array_filter(array_map('strval', $exc))) : self::DEFAULT_EXCLUDE;
    }

    private function isExcluded(string $rel): bool
    {
        foreach ($this->excludePaths() as $p) {
            $p = trim(str_replace('\\', '/', $p), '/');
            if ($p !== '' && ($rel === $p || str_starts_with($rel, $p . '/'))) {
                return true;
            }
        }
        return false;
    }

    private function collectChanges(string $source): array
    {
        $changes = [];
        $exclude = $this->excludePaths();
        foreach ($this->includePaths() as $rel) {
            $src = $source . '/' . $rel;
            if (is_file($src)) {
                if (!$this->isExcluded($rel)) {
                    $target = $this->root . '/' . $rel;
                    if (!is_file($target) || hash_file('sha256', $src) !== hash_file('sha256', $target)) {
                        $changes[$rel] = $src;
                    }
                }
                continue;
            }
            if (!is_dir($src)) {
                continue;
            }
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($it as $file) {
                if (!$file->isFile() || $file->isLink()) {
                    continue;
                }
                $full = str_replace('\\', '/', $file->getPathname());
                $sub  = ltrim(substr($full, strlen(str_replace('\\', '/', $source))), '/');
                if ($this->isExcluded($sub)) {
                    continue;
                }
                $target = $this->root . '/' . $sub;
                if (!is_file($target) || hash_file('sha256', $full) !== hash_file('sha256', $target)) {
                    $changes[$sub] = $full;
                }
            }
        }
        return $changes;
    }

    private function copyChanges(array $changes): void
    {
        $done = [];
        try {
            foreach ($changes as $rel => $src) {
                $target = $this->root . '/' . $rel;
                $this->ensureDir(dirname($target));
                $tmp = $target . '.modera-new';
                $old = $target . '.modera-old';
                if (!@copy($src, $tmp)) {
                    throw new RuntimeException('无法写入临时文件：' . $rel);
                }
                $hadOld = is_file($target);
                if ($hadOld && !@rename($target, $old)) {
                    // Windows 上偶发占用，退回直接覆盖
                    if (!@copy($src, $target)) {
                        @unlink($tmp);
                        throw new RuntimeException('文件被占用或不可写：' . $rel);
                    }
                    @unlink($tmp);
                } else {
                    if (!@rename($tmp, $target)) {
                        if ($hadOld) {
                            @rename($old, $target);
                        }
                        @unlink($tmp);
                        throw new RuntimeException('替换文件失败：' . $rel);
                    }
                    if ($hadOld) {
                        @unlink($old);
                    }
                }
                $done[] = $rel;
            }
        } catch (Throwable $e) {
            throw $e;
        }
    }

    private function backup(string $zipFile): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('无法创建备份压缩包');
        }
        $count = 0;
        foreach ($this->includePaths() as $rel) {
            $path = $this->root . '/' . $rel;
            if (is_file($path)) {
                $zip->addFile($path, $rel);
                $count++;
                continue;
            }
            if (!is_dir($path)) {
                continue;
            }
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($it as $file) {
                if (!$file->isFile()) {
                    continue;
                }
                $full = str_replace('\\', '/', $file->getPathname());
                $sub  = ltrim(substr($full, strlen($this->root)), '/');
                if ($this->isExcluded($sub)) {
                    continue;
                }
                $zip->addFile($full, $sub);
                $count++;
            }
        }
        $zip->close();
        if (!is_file($zipFile)) {
            throw new RuntimeException('备份压缩包创建失败');
        }
        return ['file' => $zipFile, 'count' => $count, 'size' => (int) filesize($zipFile)];
    }

    private function restoreFromZip(string $zipFile): void
    {
        $stage = $this->dir . '/restore-' . date('Ymd-His') . '-' . mt_rand(1000, 9999);
        $this->ensureDir($stage);
        $zip = new ZipArchive();
        if ($zip->open($zipFile) !== true) {
            throw new RuntimeException('无法打开备份压缩包');
        }
        $this->validateEntries($zip);
        if ($zip->extractTo($stage) !== true) {
            $zip->close();
            throw new RuntimeException('备份解压失败');
        }
        $zip->close();

        $changes = [];
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($stage, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($it as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $full = str_replace('\\', '/', $file->getPathname());
            $rel  = ltrim(substr($full, strlen(str_replace('\\', '/', $stage))), '/');
            if (!$this->isExcluded($rel)) {
                $changes[$rel] = $full;
            }
        }
        $this->copyChanges($changes);
        $this->removeDir($stage);
    }

    private function clearCompiledCache(): void
    {
        foreach (['cache', 'temp'] as $sub) {
            $dir = $this->root . '/runtime/' . $sub;
            if (is_dir($dir)) {
                $this->clearDir($dir);
            }
        }
    }

    private function cleanup(): void
    {
        $backups = glob($this->dir . '/backup-*.zip') ?: [];
        rsort($backups);
        foreach (array_slice($backups, 3) as $old) {
            @unlink($old);
        }
        $stages = glob($this->dir . '/stage-*') ?: [];
        rsort($stages);
        foreach (array_slice($stages, 2) as $d) {
            $this->removeDir($d);
        }
        foreach (glob($this->dir . '/download-*.zip') ?: [] as $f) {
            if (filemtime($f) < time() - 3600) {
                @unlink($f);
            }
        }
    }

    private function extract(string $zipFile, string $stage, array &$log): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CHECKCONS) !== true) {
            throw new RuntimeException('源码包不是有效的 ZIP 文件');
        }
        $this->validateEntries($zip);
        $num = $zip->numFiles;
        if ($num > 20000) {
            $zip->close();
            throw new RuntimeException('源码包文件数量异常：' . $num);
        }
        $this->ensureDir($stage);
        if ($zip->extractTo($stage) !== true) {
            $zip->close();
            throw new RuntimeException('源码包解压失败');
        }
        $zip->close();
        $log[] = '压缩包条目：' . $num;
    }

    private function validateEntries(ZipArchive $zip): void
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = str_replace('\\', '/', (string) $zip->getNameIndex($i));
            if ($name === '') {
                continue;
            }
            if (str_starts_with($name, '/') || preg_match('#^[A-Za-z]:#', $name) || str_contains($name, '../')) {
                throw new RuntimeException('压缩包包含非法路径：' . $name);
            }
        }
    }

    private function detectSourceRoot(string $stage): string
    {
        if (is_dir($stage . '/app') && is_file($stage . '/route/app.php')) {
            return str_replace('\\', '/', $stage);
        }
        foreach (glob($stage . '/*', GLOB_ONLYDIR) ?: [] as $d) {
            if (is_dir($d . '/app') && is_file($d . '/route/app.php')) {
                return str_replace('\\', '/', $d);
            }
        }
        return '';
    }

    private function download(string $url, string $target, int $maxBytes): void
    {
        $fp = fopen($target, 'wb');
        if (!$fp) {
            throw new RuntimeException('无法创建下载临时文件');
        }
        $received = 0;
        $tooBig = false;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT        => 300,
            CURLOPT_USERAGENT      => 'Modera-CMS-Updater/1.0',
            CURLOPT_SSL_VERIFYPEER => $this->caFile !== '',
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => $this->caFile,
            CURLOPT_WRITEFUNCTION  => function ($ch, $data) use ($fp, &$received, &$tooBig, $maxBytes) {
                $len = strlen($data);
                $received += $len;
                if ($received > $maxBytes) {
                    $tooBig = true;
                    return 0;
                }
                return fwrite($fp, $data);
            },
        ]);
        $ok  = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);
        if ($tooBig) {
            @unlink($target);
            throw new RuntimeException('源码包超过 120MB，已中止');
        }
        if (!$ok || $code !== 200) {
            @unlink($target);
            throw new RuntimeException('下载失败：HTTP ' . $code . ($err !== '' ? ' · ' . $err : ''));
        }
        if (!is_file($target) || filesize($target) < 1024) {
            @unlink($target);
            throw new RuntimeException('下载到的源码包为空');
        }
    }

    private function fetchJson(string $url, int $timeout = 10, bool $silent404 = false): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_USERAGENT      => 'Modera-CMS-Updater/1.0',
            CURLOPT_SSL_VERIFYPEER => $this->caFile !== '',
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => $this->caFile,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 404 && $silent404) {
            return null;
        }
        if ($body === false || $code !== 200) {
            throw new RuntimeException('请求失败：HTTP ' . $code . ($err !== '' ? ' · ' . $err : ''));
        }
        $json = json_decode((string) $body, true);
        return is_array($json) ? $json : null;
    }

    private function head(string $url, int $timeout = 20): array
    {
        $ch = curl_init($url);
        $headers = [];
        curl_setopt_array($ch, [
            CURLOPT_NOBODY         => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_USERAGENT      => 'Modera-CMS-Updater/1.0',
            CURLOPT_SSL_VERIFYPEER => $this->caFile !== '',
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => $this->caFile,
            CURLOPT_HEADERFUNCTION => function ($ch, $line) use (&$headers) {
                $pos = strpos($line, ':');
                if ($pos !== false) {
                    $headers[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos + 1));
                }
                return strlen($line);
            },
        ]);
        $ok   = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!$ok || $code < 200 || $code >= 400) {
            throw new RuntimeException('HTTP ' . $code . ($err !== '' ? ' · ' . $err : ''));
        }
        return [
            'status'        => $code,
            'size'          => (int) ($headers['content-length'] ?? 0),
            'etag'          => (string) ($headers['etag'] ?? ''),
            'last_modified' => (string) ($headers['last-modified'] ?? ''),
        ];
    }

    private function archiveUrl(string $mirror, string $repo, string $branch): string
    {
        if ($mirror === 'gitee') {
            return 'https://gitee.com/' . $repo . '/repository/archive/' . rawurlencode($branch) . '.zip';
        }
        return 'https://codeload.github.com/' . $repo . '/zip/refs/heads/' . rawurlencode($branch);
    }

    private function rawUrl(string $mirror, string $repo, string $branch, string $file): string
    {
        if ($mirror === 'gitee') {
            return 'https://gitee.com/' . $repo . '/raw/' . rawurlencode($branch) . '/' . $file;
        }
        return 'https://raw.githubusercontent.com/' . $repo . '/' . rawurlencode($branch) . '/' . $file;
    }

    private function readJson(string $file, array $default = []): array
    {
        if (!is_file($file)) {
            return $default;
        }
        $data = json_decode((string) file_get_contents($file), true);
        return is_array($data) ? $data : $default;
    }

    private function writeJson(string $file, array $data): void
    {
        $this->ensureDir(dirname($file));
        $tmp = $file . '.tmp';
        if (file_put_contents($tmp, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)) === false) {
            throw new RuntimeException('无法写入文件：' . $file);
        }
        if (!@rename($tmp, $file)) {
            @unlink($file);
            if (!@rename($tmp, $file)) {
                throw new RuntimeException('无法保存文件：' . $file);
            }
        }
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('无法创建目录：' . $dir);
        }
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $f) {
            $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
        }
        @rmdir($dir);
    }

    private function clearDir(string $dir): void
    {
        foreach (glob($dir . '/*') ?: [] as $f) {
            is_dir($f) ? $this->removeDir($f) : @unlink($f);
        }
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
