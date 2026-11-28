<?php
declare(strict_types=1);

namespace plugins\kefu\service;

use think\facade\Db;

/**
 * 在线客服插件核心服务：配置、建表/删表、坐席、在线判断、未读数
 */
class Kefu
{
    public const PLUGIN     = 'kefu';
    public const T_STAFF    = 'kefu_staff';
    public const T_SESSION  = 'kefu_session';
    public const T_MESSAGE  = 'kefu_message';

    /* 坐席类型：在线(通用) / 微信 / 企业微信 / QQ / 电话 / 邮箱 / 链接 */
    public const TYPES = [
        'online' => '在线客服',
        'wechat' => '微信',
        'wecom'  => '企业微信',
        'qq'     => 'QQ',
        'phone'  => '电话',
        'email'  => '邮箱',
        'link'   => '自定义链接',
    ];

    public static function table(string $t): string
    {
        $prefix = '';
        try {
            $prefix = (string) Db::connect('mysql')->getConfig('prefix');
        } catch (\Throwable $e) {
        }
        return $prefix . $t;
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

    /** 全部配置默认值（后台设置页按这个渲染） */
    public static function defaults(): array
    {
        return [
            'enabled'         => '1',
            'btn_text'        => '在线客服',
            'btn_icon'        => '💬',
            'position'        => 'right',
            'theme'           => '#2563eb',
            'title'           => '在线客服',
            'subtitle'        => '产品选型 · 报价 · 技术支持',
            'welcome'         => '您好，欢迎咨询甘肃鼎成实业。产品选型、报价、技术支持都可以直接问我们，工作日上班时间有客服在线。',
            'offline_tip'     => '当前为非工作时间，客服暂不在线。您可以先留言，我们会在下一个工作日主动联系您。',
            'weekdays'        => '1,2,3,4,5',
            'work_start'      => '08:30',
            'work_end'        => '18:00',
            'auto_reply'      => '已收到您的留言，客服会尽快回复。如果是紧急问题，可以直接拨打页面上的电话。',
            'require_contact' => '1',
            'poll'            => '5',
            'max_len'         => '500',
            'allow_chat'      => '1',
            'show_staff'      => '1',
            'show_qr'         => '1',
            'float_gap'       => '24',
            'guest_default'   => '访客',
        ];
    }

    /** 插件在 tp_plugin 表里的那一行 */
    public static function row(): array
    {
        $row = Db::name('plugin')->where('name', self::PLUGIN)->find();
        return $row ?: [];
    }

    /** 合并默认值后的配置 */
    public static function cfg(): array
    {
        $cfg = self::defaults();
        $row = self::row();
        if (!empty($row['config'])) {
            $j = json_decode((string) $row['config'], true);
            if (is_array($j)) {
                $cfg = array_merge($cfg, $j);
            }
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

    /** 坐席列表 */
    public static function staff(bool $onlyEnabled = true): array
    {
        try {
            $q = Db::name(self::T_STAFF);
            if ($onlyEnabled) {
                $q->where('status', 1);
            }
            return $q->order('sort asc, id asc')->select()->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** 现在是否在工作时间内 */
    public static function onlineNow(?array $cfg = null): bool
    {
        $cfg  = $cfg ?: self::cfg();
        $days = array_filter(array_map('intval', explode(',', (string) $cfg['weekdays'])));
        if ($days && !in_array((int) date('N'), $days, true)) {
            return false;
        }
        $now    = date('H:i');
        $start  = substr((string) $cfg['work_start'], 0, 5) ?: '00:00';
        $end    = substr((string) $cfg['work_end'], 0, 5) ?: '23:59';
        if ($start <= $end) {
            return $now >= $start && $now <= $end;
        }
        // 跨零点，例如 20:00-02:00
        return $now >= $start || $now <= $end;
    }

    public static function onlineText(?array $cfg = null): string
    {
        return self::onlineNow($cfg) ? '在线 · 通常几分钟内回复' : '离线 · 可先留言';
    }

    /** 后台未读统计 */
    public static function stats(): array
    {
        try {
            return [
                'staff'       => (int) Db::name(self::T_STAFF)->count(),
                'sessions'    => (int) Db::name(self::T_SESSION)->count(),
                'unread'      => (int) Db::name(self::T_SESSION)->sum('admin_unread'),
                'today'       => (int) Db::name(self::T_SESSION)->where('created_at', '>=', strtotime('today'))->count(),
                'messages'    => (int) Db::name(self::T_MESSAGE)->count(),
            ];
        } catch (\Throwable $e) {
            return ['staff' => 0, 'sessions' => 0, 'unread' => 0, 'today' => 0, 'messages' => 0];
        }
    }

    /** 建表 + 默认数据（重复调用安全） */
    public static function install(): array
    {
        $logs = [];
        $pdo  = self::pdo();
        $pfx  = (string) Db::connect('mysql')->getConfig('prefix');

        $pdo->exec("CREATE TABLE IF NOT EXISTS `{$pfx}kefu_staff` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL DEFAULT '',
            `title` varchar(60) NOT NULL DEFAULT '',
            `type` varchar(20) NOT NULL DEFAULT 'online',
            `account` varchar(255) NOT NULL DEFAULT '',
            `qr` varchar(255) NOT NULL DEFAULT '',
            `avatar` varchar(255) NOT NULL DEFAULT '',
            `intro` varchar(255) NOT NULL DEFAULT '',
            `sort` int(11) NOT NULL DEFAULT '0',
            `status` tinyint(4) NOT NULL DEFAULT '1',
            `created_at` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='在线客服-坐席'");
        $logs[] = '坐席表 ' . $pfx . 'kefu_staff 就绪';

        $pdo->exec("CREATE TABLE IF NOT EXISTS `{$pfx}kefu_session` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `token` varchar(64) NOT NULL DEFAULT '',
            `name` varchar(50) NOT NULL DEFAULT '',
            `contact` varchar(120) NOT NULL DEFAULT '',
            `page` varchar(255) NOT NULL DEFAULT '',
            `ip` varchar(64) NOT NULL DEFAULT '',
            `ua` varchar(255) NOT NULL DEFAULT '',
            `status` tinyint(4) NOT NULL DEFAULT '0',
            `unread` int(11) NOT NULL DEFAULT '0',
            `admin_unread` int(11) NOT NULL DEFAULT '0',
            `created_at` int(10) unsigned NOT NULL DEFAULT '0',
            `updated_at` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `token` (`token`),
            KEY `updated_at` (`updated_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='在线客服-会话'");
        $logs[] = '会话表 ' . $pfx . 'kefu_session 就绪';

        $pdo->exec("CREATE TABLE IF NOT EXISTS `{$pfx}kefu_message` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `session_id` int(10) unsigned NOT NULL DEFAULT '0',
            `role` varchar(10) NOT NULL DEFAULT 'visitor',
            `content` text,
            `is_read` tinyint(4) NOT NULL DEFAULT '0',
            `created_at` int(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `session_id` (`session_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='在线客服-消息'");
        $logs[] = '消息表 ' . $pfx . 'kefu_message 就绪';

        // 默认坐席（仅当表为空时写入，重复启用不会重复插）
        if ((int) Db::name(self::T_STAFF)->count() === 0) {
            $now = time();
            Db::name(self::T_STAFF)->insertAll([
                [
                    'name' => '客服小鼎', 'title' => '售前咨询 · 产品选型', 'type' => 'phone',
                    'account' => '0937-2866888', 'intro' => '工作日 08:30-18:00 在线，可直接拨打',
                    'sort' => 0, 'status' => 1, 'created_at' => $now,
                ],
                [
                    'name' => '技术支持', 'title' => '安装调试 · 售后维保', 'type' => 'email',
                    'account' => 'service@dingcheng.com', 'intro' => '设备故障、备件申请请发邮件',
                    'sort' => 1, 'status' => 1, 'created_at' => $now,
                ],
                [
                    'name' => '企业微信', 'title' => '加企微，发图纸更快', 'type' => 'online',
                    'account' => '', 'intro' => '按下单按钮进入在线对话，客服实时回复',
                    'sort' => 2, 'status' => 1, 'created_at' => $now,
                ],
            ]);
            $logs[] = '已写入 3 个示例坐席（可在后台改）';
        }

        // 默认配置
        $row = self::row();
        if (empty($row['config'])) {
            Db::name('plugin')->where('name', self::PLUGIN)->update([
                'config' => json_encode(self::defaults(), JSON_UNESCAPED_UNICODE),
            ]);
            $logs[] = '默认配置已写入';
        }

        return $logs;
    }

    /** 删表 + 清配置 */
    public static function uninstall(): array
    {
        $logs = [];
        $pdo  = self::pdo();
        $pfx  = (string) Db::connect('mysql')->getConfig('prefix');
        foreach (['kefu_message', 'kefu_session', 'kefu_staff'] as $t) {
            try {
                $pdo->exec("DROP TABLE IF EXISTS `{$pfx}{$t}`");
                $logs[] = $pfx . $t . ' 已删除';
            } catch (\Throwable $e) {
                $logs[] = $pfx . $t . ' 删除失败：' . $e->getMessage();
            }
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
