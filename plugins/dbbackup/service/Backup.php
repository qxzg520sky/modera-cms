<?php
declare(strict_types=1);

namespace plugins\dbbackup\service;

use think\facade\Db;

/**
 * 数据库备份 / 还原
 * - dump()     纯 PHP 生成 .sql（不依赖 mysqldump，PHP 环境禁用 exec 也能跑）
 * - restore()  先自动快照当前库，再逐条执行 SQL
 * - maybeAutoRun() 定时自动备份：前台/后台有请求时按间隔触发，不依赖系统计划任务
 */
class Backup
{
    const PLUGIN = 'dbbackup';

    public static function defaults(): array
    {
        return [
            'auto'     => '1',   // 1 开启定时自动备份
            'interval' => 24,    // 小时
            'keep'     => 7,     // 自动备份保留份数
            'prefix'   => 'db',  // 文件名前缀
        ];
    }

    public static function cfg(): array
    {
        $base = self::defaults();
        try {
            $row = Db::name('plugin')->where('name', self::PLUGIN)->find();
            $c   = json_decode((string) ($row['config'] ?? ''), true) ?: [];
            foreach ($base as $k => $v) {
                if (array_key_exists($k, $c)) {
                    $base[$k] = $c[$k];
                }
            }
        } catch (\Throwable $e) {
        }
        return $base;
    }

    public static function saveCfg(array $c): void
    {
        $cfg = array_merge(self::defaults(), $c);
        Db::name('plugin')->where('name', self::PLUGIN)->update([
            'config' => json_encode($cfg, JSON_UNESCAPED_UNICODE),
        ]);
        self::writeState(self::readState());
    }

    /* ==================== 目录与状态 ==================== */

    public static function dir(): string
    {
        $dir = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'dbbackup';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    private static function stateFile(): string
    {
        return self::dir() . DIRECTORY_SEPARATOR . 'state.json';
    }

    public static function readState(): array
    {
        $f = self::stateFile();
        if (!is_file($f)) {
            return ['last' => 0];
        }
        $j = json_decode((string) @file_get_contents($f), true);
        return is_array($j) ? $j : ['last' => 0];
    }

    public static function writeState(array $s): void
    {
        @file_put_contents(self::stateFile(), json_encode($s, JSON_UNESCAPED_UNICODE));
    }

    public static function path(string $file): string
    {
        $file = basename(str_replace('\\', '/', $file));
        if (!preg_match('/^[A-Za-z0-9_\-\.]+\.sql$/', $file)) {
            return '';
        }
        $p = self::dir() . DIRECTORY_SEPARATOR . $file;
        return is_file($p) ? $p : '';
    }

    /* ==================== 安装 / 卸载 ==================== */

    public static function install(): array
    {
        self::dir();
        if (self::readState() === ['last' => 0] || !is_file(self::stateFile())) {
            self::writeState(['last' => 0]);
        }
        return ['备份目录已准备：runtime/dbbackup（' . self::dir() . '）'];
    }

    public static function uninstall(): array
    {
        $logs = [];
        $dir  = self::dir();
        // 只清运行状态，备份 .sql 文件一律保留（卸载不能把用户的备份一起删掉）
        foreach (['state.json', '.lock'] as $f) {
            $p = $dir . DIRECTORY_SEPARATOR . $f;
            if (is_file($p)) {
                @unlink($p);
            }
        }
        Db::name('plugin')->where('name', self::PLUGIN)->update(['config' => '']);
        $keep   = count(self::files());
        $logs[] = '插件配置已清空，保留备份文件 ' . $keep . ' 份（' . $dir . '）';
        return $logs;
    }

    /* ==================== 数据库信息 ==================== */

    public static function dbName(): string
    {
        return (string) config('database.connections.mysql.database');
    }

    private static function prefix(): string
    {
        return (string) config('database.connections.mysql.prefix');
    }

    public static function tables(): array
    {
        $rows = Db::query('SHOW TABLES');
        $out  = [];
        foreach ($rows as $r) {
            $v = array_values($r)[0] ?? '';
            if ($v !== '') {
                $out[] = (string) $v;
            }
        }
        sort($out);
        return $out;
    }

    public static function dbInfo(): array
    {
        $db = self::dbName();
        try {
            $r = Db::query("SELECT COUNT(*) AS t, ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS mb
                FROM information_schema.tables WHERE table_schema = '" . addslashes($db) . "'");
            return [
                'name'   => $db,
                'tables' => (int) ($r[0]['t'] ?? 0),
                'size'   => (float) ($r[0]['mb'] ?? 0),
                'charset' => 'utf8mb4',
            ];
        } catch (\Throwable $e) {
            return ['name' => $db, 'tables' => 0, 'size' => 0, 'charset' => 'utf8mb4'];
        }
    }

    /* ==================== 备份 ==================== */

    /** 生成一份备份，返回 ['file'=>..., 'size'=>..., 'tables'=>..., 'seconds'=>...] */
    public static function dump(string $reason = 'manual'): array
    {
        @set_time_limit(0);
        $dir    = self::dir();
        $cfg    = self::cfg();
        $prefix = (string) ($cfg['prefix'] ?: 'db');
        $name   = $prefix . '_' . date('Ymd_His') . '_' . $reason . '.sql';
        $path   = $dir . DIRECTORY_SEPARATOR . $name;

        $db     = self::dbName();
        $tables = self::tables();
        $start  = microtime(true);

        $fp = @fopen($path, 'wb');
        if (!$fp) {
            throw new \RuntimeException('无法写入备份文件，请检查 runtime/dbbackup 目录是否可写');
        }

        fwrite($fp, "-- 数据库备份\n");
        fwrite($fp, "-- 数据库：" . $db . "\n");
        fwrite($fp, "-- 时间：" . date('Y-m-d H:i:s') . "\n");
        fwrite($fp, "-- 来源：" . $reason . "\n");
        fwrite($fp, "-- 表数量：" . count($tables) . "\n");
        fwrite($fp, "SET NAMES utf8mb4;\n");
        fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        $tblPrefix = self::prefix();
        foreach ($tables as $t) {
            $bare = ($tblPrefix !== '' && strpos($t, $tblPrefix) === 0) ? substr($t, strlen($tblPrefix)) : $t;

            $create = Db::query('SHOW CREATE TABLE `' . $t . '`');
            $ddl    = (string) ($create[0]['Create Table'] ?? '');
            if ($ddl === '') {
                continue;
            }

            fwrite($fp, "-- 表：" . $t . "\n");
            fwrite($fp, 'DROP TABLE IF EXISTS `' . $t . "`;\n");
            fwrite($fp, rtrim($ddl, ";\n ") . ";\n\n");

            $offset = 0;
            $chunk  = 300;
            while (true) {
                $rows = Db::name($bare)->limit($offset, $chunk)->select()->toArray();
                if (!$rows) {
                    break;
                }
                $cols   = array_keys($rows[0]);
                $colSql = '`' . implode('`,`', $cols) . '`';
                $vals   = [];
                foreach ($rows as $r) {
                    $one = [];
                    foreach ($cols as $c) {
                        $one[] = self::quote($r[$c] ?? null);
                    }
                    $vals[] = '(' . implode(',', $one) . ')';
                }
                fwrite($fp, 'INSERT INTO `' . $t . '` (' . $colSql . ') VALUES ' . implode(',', $vals) . ";\n");
                $offset += count($rows);
                if (count($rows) < $chunk) {
                    break;
                }
            }
            fwrite($fp, "\n");
        }

        fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fp);
        @chmod($path, 0644);

        return [
            'file'    => $name,
            'size'    => (int) @filesize($path),
            'tables'  => count($tables),
            'seconds' => round(microtime(true) - $start, 2),
        ];
    }

    /** SQL 值转义（mysqldump 风格，不依赖 PDO） */
    private static function quote($v): string
    {
        if ($v === null) {
            return 'NULL';
        }
        $s = (string) $v;
        $s = str_replace(
            ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
            ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
            $s
        );
        return "'" . $s . "'";
    }

    /* ==================== 备份列表 ==================== */

    public static function files(): array
    {
        $dir = self::dir();
        $out = [];
        foreach (glob($dir . DIRECTORY_SEPARATOR . '*.sql') ?: [] as $p) {
            $name = basename($p);
            $reason = 'manual';
            if (preg_match('/_auto\.sql$/', $name)) {
                $reason = 'auto';
            } elseif (preg_match('/_pre\.sql$/', $name)) {
                $reason = 'pre';
            } elseif (preg_match('/_uploaded\.sql$/', $name)) {
                $reason = 'upload';
            }

            $head = (string) @file_get_contents($p, false, null, 0, 400);
            $tables = 0;
            if (preg_match('/-- 表数量：(\d+)/u', $head, $m)) {
                $tables = (int) $m[1];
            }
            $db = '';
            if (preg_match('/-- 数据库：([^\n]+)/u', $head, $m)) {
                $db = trim($m[1]);
            }

            $out[] = [
                'file'   => $name,
                'reason' => $reason,
                'size'   => (int) @filesize($p),
                'time'   => (int) @filemtime($p),
                'tables' => $tables,
                'db'     => $db,
            ];
        }
        usort($out, static function ($a, $b) {
            return $b['time'] <=> $a['time'];
        });
        return $out;
    }

    public static function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    /* ==================== 还原 ==================== */

    public static function restore(string $file): array
    {
        @set_time_limit(0);
        $path = self::path($file);
        if ($path === '') {
            throw new \RuntimeException('备份文件不存在：' . $file);
        }

        // 还原前先给当前库拍个快照，万一还原失败还能退回去
        $pre = self::dump('pre');

        $raw = (string) file_get_contents($path);
        if ($raw === '') {
            throw new \RuntimeException('备份文件是空的');
        }

        // 去掉整行的 -- 注释（我们的 dump 里字符串内的换行都已转义，行内不会出现真换行）
        $raw = preg_replace('/^--.*$/m', '', $raw) ?? $raw;

        $stmts = self::splitStatements($raw);
        $ok    = 0;
        $errors = [];
        foreach ($stmts as $sql) {
            if (trim($sql) === '') {
                continue;
            }
            try {
                Db::execute($sql);
                $ok++;
            } catch (\Throwable $e) {
                $errors[] = mb_substr(trim(preg_replace('/\s+/', ' ', $sql)), 0, 80) . ' → ' . $e->getMessage();
                if (count($errors) >= 5) {
                    break;
                }
            }
        }

        return [
            'executed' => $ok,
            'errors'   => $errors,
            'pre'      => $pre['file'],
        ];
    }

    /** 按分号切 SQL，跳过引号/反引号里的分号，处理反斜杠转义 */
    private static function splitStatements(string $sql): array
    {
        $out  = [];
        $buf  = '';
        $len  = strlen($sql);
        $in   = false;
        $q    = '';

        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];

            if ($in) {
                $buf .= $ch;
                if ($ch === '\\' && $i + 1 < $len) {
                    $buf .= $sql[++$i];
                    continue;
                }
                if ($ch === $q) {
                    $in = false;
                }
                continue;
            }

            if ($ch === "'" || $ch === '"' || $ch === '`') {
                $in  = true;
                $q   = $ch;
                $buf .= $ch;
                continue;
            }

            if ($ch === ';') {
                $t = trim($buf);
                if ($t !== '') {
                    $out[] = $t;
                }
                $buf = '';
                continue;
            }

            $buf .= $ch;
        }

        $t = trim($buf);
        if ($t !== '') {
            $out[] = $t;
        }
        return $out;
    }

    public static function remove(string $file): bool
    {
        $p = self::path($file);
        if ($p === '') {
            return false;
        }
        return (bool) @unlink($p);
    }

    /** 上传的 .sql 存进备份目录，返回文件名 */
    public static function saveUpload(string $tmp, string $originName): string
    {
        $base = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originName);
        if (!preg_match('/\.sql$/i', $base)) {
            $base .= '.sql';
        }
        if (preg_match('/^(db|bk)_/', $base) === 0) {
            $base = 'bk_' . $base;
        }
        $name = date('Ymd_His') . '_uploaded_' . ltrim($base, '_');
        $name = preg_replace('/_+/', '_', $name);
        $dest = self::dir() . DIRECTORY_SEPARATOR . $name;
        if (!@move_uploaded_file($tmp, $dest)) {
            throw new \RuntimeException('上传文件保存失败，请检查 runtime/dbbackup 目录权限');
        }
        return $name;
    }

    /* ==================== 保留策略 ==================== */

    public static function prune(): void
    {
        $cfg  = self::cfg();
        $keep = max(1, (int) $cfg['keep']);
        $auto = [];
        foreach (self::files() as $f) {
            if ($f['reason'] === 'auto') {
                $auto[] = $f;
            }
        }
        foreach (array_slice($auto, $keep) as $f) {
            self::remove($f['file']);
        }
    }

    /* ==================== 定时自动备份 ==================== */

    /**
     * 由前台钩子 / 后台页面触发：到点就备一份。
     * 平时只读一个 state.json，不查库，开销可以忽略。
     */
    public static function maybeAutoRun(): void
    {
        $state = self::readState();
        $last  = (int) ($state['last'] ?? 0);
        $cfg   = self::cfg();

        if ((string) $cfg['auto'] !== '1') {
            return;
        }
        $interval = max(1, (int) $cfg['interval']) * 3600;
        if ($last > 0 && (time() - $last) < $interval) {
            return;
        }

        $lockPath = self::dir() . DIRECTORY_SEPARATOR . '.lock';
        $fp       = @fopen($lockPath, 'c');
        if (!$fp) {
            return;
        }
        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            fclose($fp);
            return;
        }

        try {
            // 二次确认，避免并发重复备份
            $state = self::readState();
            if ((int) ($state['last'] ?? 0) > 0 && (time() - (int) $state['last']) < $interval) {
                return;
            }
            self::dump('auto');
            self::prune();
            self::writeState(['last' => time(), 'error' => '']);
        } catch (\Throwable $e) {
            self::writeState(['last' => (int) ($state['last'] ?? 0), 'error' => $e->getMessage()]);
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }
}
