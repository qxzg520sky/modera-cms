<?php
declare(strict_types=1);

namespace plugins\slider\service;

use think\facade\Db;

/**
 * 轮播图广告插件核心服务：建表 / 删表 / 前台数据 / 排序
 */
class Slider
{
    public const PLUGIN  = 'slider';
    public const T_SLIDE = 'plugin_slider';
    public const MAX     = 12;

    public static function table(): string
    {
        $prefix = '';
        try {
            $prefix = (string) Db::connect('mysql')->getConfig('prefix');
        } catch (\Throwable $e) {
        }
        return $prefix . self::T_SLIDE;
    }

    public static function pdo(): \PDO
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

    /* ==================== 安装 / 卸载 ==================== */

    public static function install(): array
    {
        $logs = [];
        $pfx  = self::prefix();

        self::pdo()->exec("CREATE TABLE IF NOT EXISTS `{$pfx}plugin_slider` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(120) NOT NULL DEFAULT '',
            `subtitle` varchar(160) NOT NULL DEFAULT '',
            `summary` varchar(255) NOT NULL DEFAULT '',
            `image` varchar(255) NOT NULL DEFAULT '',
            `link` varchar(255) NOT NULL DEFAULT '',
            `link_text` varchar(40) NOT NULL DEFAULT '',
            `link2` varchar(255) NOT NULL DEFAULT '',
            `link2_text` varchar(40) NOT NULL DEFAULT '',
            `target` varchar(10) NOT NULL DEFAULT '_self',
            `title_en` varchar(160) NOT NULL DEFAULT '',
            `subtitle_en` varchar(160) NOT NULL DEFAULT '',
            `summary_en` varchar(255) NOT NULL DEFAULT '',
            `link_text_en` varchar(40) NOT NULL DEFAULT '',
            `link2_text_en` varchar(40) NOT NULL DEFAULT '',
            `sort` int(11) NOT NULL DEFAULT '0',
            `status` tinyint(4) NOT NULL DEFAULT '1',
            `created_at` int(10) unsigned NOT NULL DEFAULT '0',
            `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `status_sort` (`status`,`sort`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='轮播图广告'");
        $logs[] = '广告表 ' . $pfx . 'plugin_slider 就绪';

        // 首次启用时给几条示例，直接用站内已有文章封面，启用后前台马上能看到效果
        $count = 0;
        try {
            $count = (int) Db::name(self::T_SLIDE)->count();
        } catch (\Throwable $e) {
        }
        if ($count === 0) {
            $logs[] = self::seed() ? '已生成 3 条示例广告（可直接编辑或删除）' : '暂无示例数据（站内没有带封面的文章）';
        } else {
            $logs[] = '已有 ' . $count . ' 条广告，保留不动';
        }
        return $logs;
    }

    public static function uninstall(): array
    {
        $logs = [];
        $pfx  = self::prefix();
        try {
            self::pdo()->exec("DROP TABLE IF EXISTS `{$pfx}plugin_slider`");
            $logs[] = $pfx . 'plugin_slider 已删除';
        } catch (\Throwable $e) {
            $logs[] = '删表失败：' . $e->getMessage();
        }
        try {
            Db::name('plugin')->where('name', self::PLUGIN)->update(['config' => '']);
            $logs[] = '插件配置已清空';
        } catch (\Throwable $e) {
            $logs[] = '配置清理失败：' . $e->getMessage();
        }
        return $logs;
    }

    private static function prefix(): string
    {
        try {
            return (string) Db::connect('mysql')->getConfig('prefix');
        } catch (\Throwable $e) {
            return 'tp_';
        }
    }

    /** 用站内文章封面生成 3 条示例广告（优先产品/装备/加工这类业务内容，避开新闻和招聘） */
    private static function seed(): bool
    {
        $skipIds = [];
        try {
            $skipIds = Db::name('category')->whereIn('slug', ['news', 'jobs'])->column('id');
        } catch (\Throwable $e) {
        }

        try {
            $q = Db::name('article')
                ->where('status', 1)
                ->where('cover', '<>', '')
                ->order('is_top desc, id desc')
                ->limit(3);
            if ($skipIds) {
                $q->whereNotIn('category_id', $skipIds);
            }
            $arts = $q->select()->toArray();
        } catch (\Throwable $e) {
            return false;
        }
        if (!$arts) {
            try {
                $arts = Db::name('article')->where('status', 1)->where('cover', '<>', '')
                    ->order('id desc')->limit(3)->select()->toArray();
            } catch (\Throwable $e) {
                return false;
            }
        }
        if (!$arts) {
            return false;
        }
        $cats = Db::name('category')->column('name', 'id');
        $now  = time();
        foreach ($arts as $i => $a) {
            Db::name(self::T_SLIDE)->insert([
                'title'     => mb_substr((string) $a['title'], 0, 60),
                'subtitle'  => (string) ($cats[(int) $a['category_id']] ?? ''),
                'summary'   => mb_substr(trim(strip_tags((string) $a['summary'])), 0, 80),
                'image'     => (string) $a['cover'],
                'link'      => '/a/' . (int) $a['id'],
                'link_text' => '查看详情',
                'sort'      => $i + 1,
                'status'    => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        return true;
    }

    /* ==================== 数据 ==================== */

    /** 后台列表：全部条目 */
    public static function all(): array
    {
        try {
            return Db::name(self::T_SLIDE)->order('sort asc, id asc')->select()->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** 前台：启用中的广告，最多 MAX 条 */
    public static function front(): array
    {
        try {
            $rows = Db::name(self::T_SLIDE)
                ->where('status', 1)
                ->order('sort asc, id asc')
                ->limit(self::MAX)
                ->select()->toArray();
        } catch (\Throwable $e) {
            return [];
        }

        $items = [];
        foreach ($rows as $row) {
            if ((string) $row['image'] === '') {
                continue;
            }
            $items[] = self::normalize($row);
        }
        return $items;
    }

    /** 统一成主题能直接渲染的结构 */
    public static function normalize(array $row): array
    {
        $link  = trim((string) $row['link']);
        $link2 = trim((string) $row['link2']);

        // 站内链接自动补语言前缀；外链原样
        if ($link !== '' && $link[0] === '/' && function_exists('lang_path')) {
            $link = lang_path($link);
        }
        if ($link2 !== '' && $link2[0] === '/' && function_exists('lang_path')) {
            $link2 = lang_path($link2);
        }

        $t = function (string $field, string $default = '') use ($row): string {
            $v = function_exists('ml') ? (string) ml($row, $field) : (string) ($row[$field] ?? '');
            return trim($v) !== '' ? $v : $default;
        };

        return [
            'id'        => (int) $row['id'],
            'eyebrow'   => $t('subtitle'),
            'title'     => $t('title'),
            'summary'   => $t('summary'),
            'image'     => (string) $row['image'],
            'link'      => $link,
            'link_text' => $t('link_text', '查看详情'),
            'link2'     => $link2,
            'link2_text'=> $t('link2_text', '更多栏目'),
            'target'    => ((string) $row['target'] === '_blank') ? '_blank' : '_self',
        ];
    }

    /** 重排 sort 为 1..N（删除或乱序后调用） */
    public static function renumber(): void
    {
        $ids = Db::name(self::T_SLIDE)->order('sort asc, id asc')->column('id');
        foreach ($ids as $k => $sid) {
            if ((int) $sid > 0) {
                Db::name(self::T_SLIDE)->where('id', (int) $sid)->update(['sort' => $k + 1]);
            }
        }
    }

    /** 上移 / 下移：整体重排序号，保证顺序稳定 */
    public static function move(int $id, string $dir): bool
    {
        $ids = Db::name(self::T_SLIDE)->order('sort asc, id asc')->column('id');
        $i   = array_search($id, array_map('intval', $ids), true);
        if ($i === false) {
            return false;
        }
        $j = $dir === 'up' ? $i - 1 : $i + 1;
        if ($j < 0 || $j >= count($ids)) {
            return false;
        }
        $tmp       = $ids[$i];
        $ids[$i]   = $ids[$j];
        $ids[$j]   = $tmp;
        foreach ($ids as $k => $sid) {
            Db::name(self::T_SLIDE)->where('id', (int) $sid)->update(['sort' => $k + 1]);
        }
        return true;
    }
}
