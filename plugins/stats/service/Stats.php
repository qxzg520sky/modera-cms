<?php
declare(strict_types=1);

namespace plugins\stats\service;

use think\facade\Db;

/**
 * 轻量访问统计核心。
 *
 * 设计原则：
 * 1) 不写逐条访问日志，只写「按天 + 页面」「按天 + 访客」「按天 + 页面 + 访客」三种聚合记录；
 * 2) 每次访问最多 3 条带唯一索引的 upsert，靠数据库唯一键去重，不靠 SELECT 再判断；
 * 3) UV 用第一方 cookie 的匿名 visitor id 去重，不存原始 IP；
 * 4) 按保留天数定期清理，默认 90 天。
 */
class Stats
{
    public const PLUGIN = 'stats';
    private const T_PAGE = 'stats_page';
    private const T_VISITOR = 'stats_visitor';
    private const T_PAGE_VISITOR = 'stats_page_visitor';

    public static function defaults(): array
    {
        return [
            'retention' => '90', // 保留天数
            'dnt'       => '1',  // 尊重浏览器 Do Not Track
            'salt'      => '',
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
        if ((string) $cfg['salt'] === '') {
            $cfg['salt'] = substr(sha1(root_path() . '|modera-stats'), 0, 16);
        }
        return $cfg;
    }

    public static function saveCfg(array $data): void
    {
        $cfg = array_merge(self::cfg(), $data);
        try {
            Db::name('plugin')->where('name', self::PLUGIN)->update([
                'config' => json_encode($cfg, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (\Throwable $e) {
        }
    }

    public static function table(string $name): string
    {
        $prefix = '';
        try {
            $prefix = (string) Db::connect('mysql')->getConfig('prefix');
        } catch (\Throwable $e) {
        }
        return $prefix . $name;
    }

    /* ==================== 安装 / 卸载 ==================== */

    public static function install(): array
    {
        $logs = [];
        $p = self::table('');
        $sqls = [
            "CREATE TABLE IF NOT EXISTS `{$p}stats_page` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `day` date NOT NULL,
              `page_key` varchar(160) NOT NULL DEFAULT '',
              `title` varchar(160) NOT NULL DEFAULT '',
              `page_type` varchar(20) NOT NULL DEFAULT 'other',
              `pv` int(10) unsigned NOT NULL DEFAULT 0,
              `uv` int(10) unsigned NOT NULL DEFAULT 0,
              `first_at` int(10) unsigned NOT NULL DEFAULT 0,
              `last_at` int(10) unsigned NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`),
              UNIQUE KEY `day_page` (`day`,`page_key`),
              KEY `page_key` (`page_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

            "CREATE TABLE IF NOT EXISTS `{$p}stats_visitor` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `day` date NOT NULL,
              `visitor` char(16) NOT NULL DEFAULT '',
              `referrer` varchar(255) NOT NULL DEFAULT '',
              `referrer_host` varchar(120) NOT NULL DEFAULT '',
              `landing` varchar(160) NOT NULL DEFAULT '',
              `hits` int(10) unsigned NOT NULL DEFAULT 0,
              `first_at` int(10) unsigned NOT NULL DEFAULT 0,
              `last_at` int(10) unsigned NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`),
              UNIQUE KEY `day_visitor` (`day`,`visitor`),
              KEY `day_host` (`day`,`referrer_host`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

            "CREATE TABLE IF NOT EXISTS `{$p}stats_page_visitor` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `day` date NOT NULL,
              `page_key` varchar(160) NOT NULL DEFAULT '',
              `visitor` char(16) NOT NULL DEFAULT '',
              PRIMARY KEY (`id`),
              UNIQUE KEY `day_page_visitor` (`day`,`page_key`,`visitor`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
        ];
        foreach ($sqls as $sql) {
            Db::execute($sql);
        }
        $logs[] = '统计表 stats_page / stats_visitor / stats_page_visitor 已就绪';

        $cfg = self::cfg();
        if ((string) $cfg['salt'] === '') {
            $cfg['salt'] = bin2hex(random_bytes(8));
        }
        self::saveCfg($cfg);
        $logs[] = '统计配置已初始化（默认保留 90 天，尊重 DNT）';
        self::clearPageCache();
        return $logs;
    }

    public static function uninstall(): array
    {
        $logs = [];
        $p = self::table('');
        foreach (['stats_page_visitor', 'stats_visitor', 'stats_page'] as $t) {
            try {
                Db::execute("DROP TABLE IF EXISTS `{$p}{$t}`");
                $logs[] = $p . $t . ' 已删除';
            } catch (\Throwable $e) {
                $logs[] = $p . $t . ' 删除失败：' . $e->getMessage();
            }
        }
        try {
            Db::name('plugin')->where('name', self::PLUGIN)->update(['config' => '']);
        } catch (\Throwable $e) {
        }
        return $logs;
    }

    /* ==================== 写入 ==================== */

    /**
     * 记录一次页面访问。异常全部吞掉，统计不能影响前台。
     * @param array $ctx path,title,referrer,referrer_host,visitor,day,now
     */
    public static function record(array $ctx): bool
    {
        try {
            $day   = (string) $ctx['day'];
            $path  = (string) $ctx['path'];
            $title = (string) $ctx['title'];
            $type  = (string) $ctx['page_type'];
            $vid   = (string) $ctx['visitor'];
            $ref   = (string) $ctx['referrer'];
            $host  = (string) $ctx['referrer_host'];
            $now   = (int) $ctx['now'];

            $pageTable = self::table(self::T_PAGE);
            $visitTable = self::table(self::T_VISITOR);
            $pvTable = self::table(self::T_PAGE_VISITOR);

            // 1. 页面 PV 自增（首次插入时写入标题）
            Db::execute(
                "INSERT INTO `{$pageTable}` (`day`,`page_key`,`title`,`page_type`,`pv`,`uv`,`first_at`,`last_at`)
                 VALUES (?,?,?,?,1,0,?,?)
                 ON DUPLICATE KEY UPDATE `pv`=`pv`+1, `last_at`=VALUES(`last_at`),
                 `title`=IF(`title`='', VALUES(`title`), `title`)",
                [$day, $path, $title, $type, $now, $now]
            );

            // 2. 访客记录：同一访客当天只一行，hits 累加
            $newVisitor = Db::execute(
                "INSERT INTO `{$visitTable}` (`day`,`visitor`,`referrer`,`referrer_host`,`landing`,`hits`,`first_at`,`last_at`)
                 VALUES (?,?,?,?,?,1,?,?)
                 ON DUPLICATE KEY UPDATE `hits`=`hits`+1, `last_at`=VALUES(`last_at`)",
                [$day, $vid, $ref, $host, $path, $now, $now]
            );

            // 3. 页面 UV 去重：同一访客同一天同一页面只算一次
            $newPageVisitor = Db::execute(
                "INSERT IGNORE INTO `{$pvTable}` (`day`,`page_key`,`visitor`) VALUES (?,?,?)",
                [$day, $path, $vid]
            );
            if ($newPageVisitor > 0) {
                Db::execute(
                    "UPDATE `{$pageTable}` SET `uv`=`uv`+1 WHERE `day`=? AND `page_key`=?",
                    [$day, $path]
                );
            }

            // 约 1/300 的访问顺带清理过期数据，避免每次都跑删除
            if ($newVisitor > 0 && random_int(1, 300) === 1) {
                self::purgeExpired();
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* ==================== 清理 ==================== */

    public static function purgeExpired(): int
    {
        $retention = max(7, min(365, (int) self::cfg()['retention']));
        $cutoff = date('Y-m-d', strtotime('-' . $retention . ' days'));
        $n = 0;
        foreach ([self::T_PAGE, self::T_VISITOR, self::T_PAGE_VISITOR] as $t) {
            try {
                $n += (int) Db::name($t)->where('day', '<', $cutoff)->delete();
            } catch (\Throwable $e) {
            }
        }
        return $n;
    }

    /** 启停统计后清一次页面缓存，避免缓存里留着旧 beacon（装了缓存插件时） */
    public static function clearPageCache(): void
    {
        try {
            if (class_exists('plugins\\cache\\service\\Cache')) {
                \plugins\cache\service\Cache::clear();
            }
        } catch (\Throwable $e) {
        }
    }

    public static function clearAll(): void
    {
        foreach ([self::T_PAGE_VISITOR, self::T_VISITOR, self::T_PAGE] as $t) {
            try {
                Db::execute('DELETE FROM `' . self::table($t) . '`');
            } catch (\Throwable $e) {
            }
        }
    }

    /* ==================== 后台读取 ==================== */

    public static function dashboard(int $days = 30): array
    {
        $days = in_array($days, [7, 30, 90], true) ? $days : 30;
        $end  = date('Y-m-d');
        $start = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));

        $prevEnd   = date('Y-m-d', strtotime($start . ' -1 day'));
        $prevStart = date('Y-m-d', strtotime($prevEnd . ' -' . ($days - 1) . ' days'));

        $rangePv = (int) self::sumPv($start, $end);
        $rangeUv = self::distinctUv($start, $end);
        $prevPv  = (int) self::sumPv($prevStart, $prevEnd);
        $prevUv  = self::distinctUv($prevStart, $prevEnd);

        $today     = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $todayPv   = (int) self::sumPv($today, $today);
        $todayUv   = self::distinctUv($today, $today);
        $yPv       = (int) self::sumPv($yesterday, $yesterday);
        $yUv       = self::distinctUv($yesterday, $yesterday);

        // 每日趋势
        $pvByDay = [];
        foreach (Db::name(self::T_PAGE)->whereBetween('day', [$start, $end])
            ->field('day, SUM(pv) AS pv')->group('day')->select()->toArray() as $r) {
            $pvByDay[(string) $r['day']] = (int) $r['pv'];
        }
        $uvByDay = [];
        foreach (Db::name(self::T_VISITOR)->whereBetween('day', [$start, $end])
            ->field('day, COUNT(DISTINCT visitor) AS uv')->group('day')->select()->toArray() as $r) {
            $uvByDay[(string) $r['day']] = (int) $r['uv'];
        }
        $trend = [];
        for ($i = 0; $i < $days; $i++) {
            $d = date('Y-m-d', strtotime($start . ' +' . $i . ' day'));
            $trend[] = [
                'day'   => $d,
                'label' => date('m-d', strtotime($d)),
                'pv'    => $pvByDay[$d] ?? 0,
                'uv'    => $uvByDay[$d] ?? 0,
            ];
        }

        // 热门页面
        $pages = Db::name(self::T_PAGE)->whereBetween('day', [$start, $end])
            ->field('page_key, MAX(title) AS title, page_type, SUM(pv) AS pv, SUM(uv) AS uv, MAX(last_at) AS last_at')
            ->group('page_key, page_type')->order('pv DESC')->limit(20)->select()->toArray();
        $pages = self::decoratePages($pages);
        $pages = self::applyRangeUv($pages, $start, $end);

        // 文章流量排行
        $articles = Db::name(self::T_PAGE)->whereBetween('day', [$start, $end])
            ->where('page_type', 'article')
            ->field('page_key, MAX(title) AS title, page_type, SUM(pv) AS pv, SUM(uv) AS uv')
            ->group('page_key, page_type')->order('pv DESC')->limit(8)->select()->toArray();
        $articles = self::decoratePages($articles);
        $articles = self::applyRangeUv($articles, $start, $end);

        // 来路
        $sources = Db::name(self::T_VISITOR)->whereBetween('day', [$start, $end])
            ->field('referrer_host, COUNT(DISTINCT visitor) AS uv, SUM(hits) AS pv')
            ->group('referrer_host')->order('uv DESC')->limit(8)->select()->toArray();

        // 最近访客
        $recent = Db::name(self::T_VISITOR)->whereBetween('day', [$start, $end])
            ->field('visitor, landing, referrer_host, hits, last_at')
            ->order('last_at DESC')->limit(10)->select()->toArray();

        return [
            'days'      => $days,
            'start'     => $start,
            'end'       => $end,
            'rangePv'   => $rangePv,
            'rangeUv'   => $rangeUv,
            'prevPv'    => $prevPv,
            'prevUv'    => $prevUv,
            'todayPv'   => $todayPv,
            'todayUv'   => $todayUv,
            'yPv'       => $yPv,
            'yUv'       => $yUv,
            'trend'     => $trend,
            'pages'     => $pages,
            'articles'  => $articles,
            'sources'   => $sources,
            'recent'    => $recent,
            'maxPv'     => max(1, max(array_column($trend, 'pv') ?: [0])),
            'maxUv'     => max(1, max(array_column($trend, 'uv') ?: [0])),
            'totalRows' => self::totalRows(),
            'retention' => (int) self::cfg()['retention'],
        ];
    }

    /** 用页面-访客表计算区间内跨天去重的真实 UV */
    private static function applyRangeUv(array $rows, string $start, string $end): array
    {
        if (!$rows) {
            return $rows;
        }
        $keys = array_column($rows, 'page_key');
        $uvMap = [];
        foreach (Db::name(self::T_PAGE_VISITOR)->whereBetween('day', [$start, $end])
            ->whereIn('page_key', $keys)->field('page_key, COUNT(DISTINCT visitor) AS uv')
            ->group('page_key')->select()->toArray() as $r) {
            $uvMap[(string) $r['page_key']] = (int) $r['uv'];
        }
        foreach ($rows as &$r) {
            if (isset($uvMap[$r['page_key']])) {
                $r['uv'] = $uvMap[$r['page_key']];
            }
        }
        unset($r);
        return $rows;
    }

    private static function sumPv(string $start, string $end): int
    {
        try {
            return (int) Db::name(self::T_PAGE)->whereBetween('day', [$start, $end])->sum('pv');
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private static function distinctUv(string $start, string $end): int
    {
        try {
            $row = Db::name(self::T_VISITOR)->whereBetween('day', [$start, $end])
                ->field('COUNT(DISTINCT visitor) AS uv')->find();
            return (int) ($row['uv'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public static function totalRows(): int
    {
        $n = 0;
        foreach ([self::T_PAGE, self::T_VISITOR, self::T_PAGE_VISITOR] as $t) {
            try {
                $n += (int) Db::name($t)->count();
            } catch (\Throwable $e) {
            }
        }
        return $n;
    }

    /** 用数据库里的最新标题替换客户端标题，并补类型标签 */
    private static function decoratePages(array $rows): array
    {
        $articleIds = [];
        $catSlugs = [];
        $pageSlugs = [];
        foreach ($rows as $r) {
            $key = (string) $r['page_key'];
            if (preg_match('#^/a/(\d+)#', $key, $m)) {
                $articleIds[(int) $m[1]] = (int) $m[1];
            } elseif (preg_match('#^/en/a/(\d+)#', $key, $m)) {
                $articleIds[(int) $m[1]] = (int) $m[1];
            } elseif (preg_match('#^(?:/en)?/c/([^/]+)#', $key, $m)) {
                $catSlugs[$m[1]] = $m[1];
            } elseif (preg_match('#^(?:/en)?/p/([^/]+)#', $key, $m)) {
                $pageSlugs[$m[1]] = $m[1];
            }
        }

        $articleTitles = [];
        if ($articleIds) {
            try {
                foreach (Db::name('article')->whereIn('id', $articleIds)
                    ->field('id,title,title_en')->select()->toArray() as $a) {
                    $articleTitles[(int) $a['id']] = [
                        'zh' => (string) $a['title'],
                        'en' => (string) ($a['title_en'] ?? ''),
                    ];
                }
            } catch (\Throwable $e) {
                try {
                    foreach (Db::name('article')->whereIn('id', $articleIds)
                        ->field('id,title')->select()->toArray() as $a) {
                        $articleTitles[(int) $a['id']] = ['zh' => (string) $a['title'], 'en' => ''];
                    }
                } catch (\Throwable $e2) {
                }
            }
        }

        $catNames = [];
        if ($catSlugs) {
            try {
                foreach (Db::name('category')->whereIn('slug', $catSlugs)
                    ->field('slug,name,name_en')->select()->toArray() as $c) {
                    $catNames[(string) $c['slug']] = [
                        'zh' => (string) $c['name'],
                        'en' => (string) ($c['name_en'] ?? ''),
                    ];
                }
            } catch (\Throwable $e) {
            }
        }

        $pageNames = [];
        if ($pageSlugs) {
            try {
                foreach (Db::name('page')->whereIn('slug', $pageSlugs)
                    ->field('slug,title,title_en')->select()->toArray() as $p) {
                    $pageNames[(string) $p['slug']] = [
                        'zh' => (string) $p['title'],
                        'en' => (string) ($p['title_en'] ?? ''),
                    ];
                }
            } catch (\Throwable $e) {
            }
        }

        foreach ($rows as &$r) {
            $key = (string) $r['page_key'];
            $isEn = str_starts_with($key, '/en');
            $type = (string) $r['page_type'];
            if ($key === '/' || $key === '/en') {
                $r['title'] = $isEn ? 'Home' : '首页';
                $r['page_type'] = 'home';
            } elseif (preg_match('#^(?:/en)?/a/(\d+)#', $key, $m) && isset($articleTitles[(int) $m[1]])) {
                $t = $articleTitles[(int) $m[1]];
                $r['title'] = ($isEn && $t['en'] !== '') ? $t['en'] : $t['zh'];
            } elseif (preg_match('#^(?:/en)?/c/([^/]+)#', $key, $m) && isset($catNames[$m[1]])) {
                $t = $catNames[$m[1]];
                $r['title'] = ($isEn && $t['en'] !== '') ? $t['en'] : $t['zh'];
            } elseif (preg_match('#^(?:/en)?/p/([^/]+)#', $key, $m) && isset($pageNames[$m[1]])) {
                $t = $pageNames[$m[1]];
                $r['title'] = ($isEn && $t['en'] !== '') ? $t['en'] : $t['zh'];
            } elseif ($key === '/search' || $key === '/en/search') {
                $r['title'] = $isEn ? 'Search' : '站内搜索';
            }
            if ($r['title'] === '') {
                $r['title'] = $key;
            }
            $r['type'] = $type;
            $r['pv'] = (int) $r['pv'];
            $r['uv'] = (int) $r['uv'];
        }
        unset($r);
        return $rows;
    }
}
