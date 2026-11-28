<?php
declare(strict_types=1);

namespace plugins\multilang\service;

use think\facade\Db;

/**
 * 多语言插件的字段管理器
 * - 每种语言 → 给 tp_category / tp_article / tp_page / tp_setting 加一组 {字段}_{语言} 列
 * - 卸载 → DROP 掉这些列，数据一起清掉，回到未安装的状态
 */
class Lang
{
    public const PLUGIN = 'multilang';
    public const DEFAULT_LANG = 'zh-cn';

    /** 哪些表、哪些字段需要多语言 */
    public static function fieldMap(): array
    {
        return [
            'category' => [
                'name'            => '栏目名称',
                'seo_title'       => 'SEO 标题',
                'seo_description' => 'SEO 描述',
            ],
            'article' => [
                'title'           => '文章标题',
                'summary'         => '摘要',
                'content'         => '正文',
                'seo_title'       => 'SEO 标题',
                'seo_description' => 'SEO 描述',
            ],
            'page' => [
                'title'           => '页面标题',
                'content'         => '正文',
                'seo_title'       => 'SEO 标题',
                'seo_description' => 'SEO 描述',
            ],
            'setting' => [
                'value' => '配置值',
            ],
        ];
    }

    /** 插件数据库行 */
    public static function row(): array
    {
        try {
            return Db::name('plugin')->where('name', self::PLUGIN)->find() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** 插件配置（语言列表、语言显示名、默认语言） */
    public static function config(): array
    {
        $row = self::row();
        $c   = [];
        if (!empty($row['config'])) {
            $c = json_decode((string) $row['config'], true) ?: [];
        }

        return [
            'langs'   => self::codes(),
            'default' => strtolower((string) ($c['default_lang'] ?? self::DEFAULT_LANG)),
            'names'   => (array) ($c['names'] ?? []),
            'enabled' => (int) ($row['enabled'] ?? 0),
        ];
    }

    /** 语言代码列表（不含默认语言） */
    public static function codes(): array
    {
        $row = self::row();
        $cfg = !empty($row['config']) ? (json_decode((string) $row['config'], true) ?: []) : [];

        return self::parseCodes(
            (string) ($cfg['langs'] ?? 'en'),
            (string) ($cfg['default_lang'] ?? self::DEFAULT_LANG)
        );
    }

    /** 解析 "en, ja" 这类字符串 */
    public static function parseCodes(string $raw, string $default = self::DEFAULT_LANG): array
    {
        $default = strtolower(trim($default)) ?: self::DEFAULT_LANG;
        $langs   = [];

        foreach (preg_split('/[,\s]+/', $raw) as $code) {
            $code = strtolower(trim($code));
            if ($code === '' || $code === $default) {
                continue;
            }
            if (!preg_match('/^[a-z]{2,3}(_[a-z]{2,4})?$/', $code)) {
                continue;
            }
            $langs[] = $code;
        }

        return array_values(array_unique($langs));
    }

    /** 语言代码 → 字段后缀：en → _en */
    public static function suffix(string $code): string
    {
        return '_' . str_replace('-', '_', $code);
    }

    /** 带表前缀的真实表名 */
    public static function table(string $table): string
    {
        $prefix = '';
        try {
            $prefix = (string) Db::connect('mysql')->getConfig('prefix');
        } catch (\Throwable $e) {
        }
        return $prefix . $table;
    }

    private static function pdo(): \PDO
    {
        static $pdo = null;
        if ($pdo instanceof \PDO) {
            return $pdo;
        }
        $c   = Db::connect('mysql')->getConfig();
        $dsn = 'mysql:host=' . $c['hostname'] . ';port=' . $c['hostport'] . ';dbname=' . $c['database'] . ';charset=utf8mb4';
        $pdo = new \PDO($dsn, (string) $c['username'], (string) $c['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);
        return $pdo;
    }

    /** 表里现有列名 */
    public static function columns(string $table): array
    {
        try {
            $cols = self::pdo()->query('SHOW COLUMNS FROM `' . self::table($table) . '`')->fetchAll(\PDO::FETCH_COLUMN);
            return $cols ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** 该语言字段是否已存在（抽查栏目表） */
    public static function hasFields(string $code): bool
    {
        $cols = self::columns('category');
        foreach (self::fieldMap()['category'] as $field => $label) {
            if (!in_array($field . self::suffix($code), $cols, true)) {
                return false;
            }
        }
        return true;
    }

    /** 创建字段：$codes 语言代码数组，返回执行日志 */
    public static function install(array $codes): array
    {
        $logs = [];
        foreach ($codes as $code) {
            foreach (self::fieldMap() as $table => $fields) {
                $cols = self::columns($table);
                foreach ($fields as $field => $label) {
                    $col = $field . self::suffix($code);
                    if (in_array($col, $cols, true)) {
                        continue;
                    }
                    $type = ($field === 'content' || $field === 'value') ? 'LONGTEXT' : 'TEXT';
                    try {
                        self::pdo()->exec('ALTER TABLE `' . self::table($table) . '` ADD COLUMN `' . $col . '` ' . $type . ' NULL');
                        $logs[] = self::table($table) . '.' . $col . ' 已创建';
                    } catch (\Throwable $e) {
                        $logs[] = self::table($table) . '.' . $col . ' 失败：' . $e->getMessage();
                    }
                }
            }
        }
        return $logs ?: ['字段均已存在，无需创建'];
    }

    /** 删除字段（连同数据）。$codes 为空且 $all=false 时删当前配置的语言；$all=true 删全部语言字段 */
    public static function uninstall(array $codes = [], bool $all = false): array
    {
        $want = [];
        if (!$all) {
            if (!$codes) {
                $codes = self::codes();
            }
            // 列名里的语言后缀把 - 换成了 _，比较时统一
            $want = array_map(static fn ($c) => str_replace('-', '_', strtolower($c)), $codes);
        }

        // 兜底：把库里所有 xxx_en 形式的列也扫出来
        $logs = [];
        foreach (self::fieldMap() as $table => $fields) {
            $cols = self::columns($table);
            foreach ($cols as $col) {
                foreach ($fields as $field => $label) {
                    $prefix = $field . '_';
                    if (strpos($col, $prefix) !== 0 || strlen($col) <= strlen($prefix)) {
                        continue;
                    }
                    $code = substr($col, strlen($prefix));
                    if ($want && !in_array(strtolower($code), $want, true)) {
                        continue;
                    }
                    try {
                        self::pdo()->exec('ALTER TABLE `' . self::table($table) . '` DROP COLUMN `' . $col . '`');
                        $logs[] = self::table($table) . '.' . $col . ' 已删除';
                    } catch (\Throwable $e) {
                        $logs[] = self::table($table) . '.' . $col . ' 删除失败：' . $e->getMessage();
                    }
                }
            }
        }
        return $logs ?: ['没有需要删除的字段'];
    }

    /** 保存语言配置并同步字段 */
    public static function saveConfig(string $rawLangs, string $default, array $names): array
    {
        $default = strtolower(trim($default)) ?: self::DEFAULT_LANG;

        $codes = [];
        foreach (preg_split('/[,\s]+/', $rawLangs) as $code) {
            $code = strtolower(trim($code));
            if ($code === '' || $code === $default) {
                continue;
            }
            if (!preg_match('/^[a-z]{2,3}(_[a-z]{2,4})?$/', $code)) {
                continue;
            }
            $codes[] = $code;
        }
        $codes = array_values(array_unique($codes));

        $namesClean = [];
        foreach ($codes as $code) {
            $namesClean[$code] = trim((string) ($names[$code] ?? '')) ?: strtoupper($code);
        }

        $config = json_encode([
            'langs'        => implode(',', $codes),
            'default_lang' => $default,
            'names'        => $namesClean,
        ], JSON_UNESCAPED_UNICODE);

        // 先记下原来的语言（改配置后就查不到旧的了）
        $oldCodes = self::codes();

        Db::name('plugin')->where('name', self::PLUGIN)->update(['config' => $config]);

        // 新语言加字段，被删掉的语言删字段
        $logs = [];

        $added = array_diff($codes, $oldCodes);
        if ($added) {
            $logs = array_merge($logs, self::install(array_values($added)));
        }

        $removed = array_diff($oldCodes, $codes);
        if ($removed) {
            $logs = array_merge($logs, self::uninstall(array_values($removed)));
        }

        // 保证现有语言字段齐全
        foreach ($codes as $code) {
            if (!self::hasFields($code)) {
                $logs = array_merge($logs, self::install([$code]));
            }
        }

        return [$codes, $logs];
    }

    /** 某条记录在某语言下是否已填写 */
    public static function filled(array $row, string $table, string $code): int
    {
        $n = 0;
        foreach (self::fieldMap()[$table] ?? [] as $field => $label) {
            $col = $field . self::suffix($code);
            if (!empty($row[$col])) {
                $n++;
            }
        }
        return $n;
    }
}
