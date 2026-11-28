<?php
declare(strict_types=1);

namespace plugins\member\service;

use think\facade\Db;

/**
 * 企业会员管理核心服务
 *
 * 设计原则：
 * 1. 商城/其它插件只通过 MemberApi（HTTP 签名）或本类公开方法调用，不直接读写 member_* 表；
 * 2. 余额、积分每次变动都在同一事务内写日志，保留变动前后值、操作人、业务单号；
 * 3. 会员、业务员、管理员三种身份各自独立登录，业务员按 staff_id 限定数据范围。
 */
class Member
{
    public const PLUGIN  = 'member';
    public const T_MEMBER   = 'member';
    public const T_LEVEL    = 'member_level';
    public const T_BALANCE  = 'member_balance_log';
    public const T_POINTS   = 'member_points_log';
    public const T_ADDRESS  = 'member_address';
    public const T_STAFF    = 'member_staff';

    public const BALANCE_TYPES = [
        'init'     => '初始余额',
        'recharge' => '充值',
        'consume'  => '消费扣款',
        'refund'   => '退款返还',
        'withdraw' => '提现扣减',
        'adjust'   => '手动调整',
    ];

    public const POINTS_TYPES = [
        'init'    => '初始积分',
        'gift'    => '积分赠送',
        'consume' => '积分抵扣',
        'deduct'  => '积分扣减',
        'refund'  => '积分退回',
        'adjust'  => '手动调整',
    ];

    public const OPERATOR_TYPES = [
        'system' => '系统',
        'admin'  => '管理员',
        'staff'  => '业务员',
        'member' => '会员本人',
        'api'    => '接口',
    ];

    public static function table(string $name): string
    {
        $prefix = '';
        try {
            $prefix = (string) Db::connect('mysql')->getConfig('prefix');
        } catch (\Throwable $e) {
        }
        return $prefix . $name;
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

    /* ==================== 配置 ==================== */

    public static function defaults(): array
    {
        return [
            'enabled'          => '1',
            'allow_register'   => '1',
            'register_points'  => '100',
            'auto_upgrade'     => '1',
            'default_password' => 'Member@123456',
            'page_size'        => '20',
            'api_appid'        => 'modera-mall',
            'api_secret'       => '',
            'api_ip_whitelist' => '',
            'footer_link'      => '1',
            'points_money_rate'=> '0.01',
        ];
    }

    public static function row(): array
    {
        $row = Db::name('plugin')->where('name', self::PLUGIN)->find();
        return $row ?: [];
    }

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

    /* ==================== 安装 / 卸载 ==================== */

    public static function install(): array
    {
        $logs = [];
        $pdo  = self::pdo();
        $pfx  = (string) Db::connect('mysql')->getConfig('prefix');

        $pdo->exec("CREATE TABLE IF NOT EXISTS `{$pfx}member` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `member_no` varchar(32) NOT NULL DEFAULT '',
            `username` varchar(50) NOT NULL DEFAULT '',
            `mobile` varchar(20) NOT NULL DEFAULT '',
            `email` varchar(100) NOT NULL DEFAULT '',
            `password_hash` varchar(255) NOT NULL DEFAULT '',
            `real_name` varchar(50) NOT NULL DEFAULT '',
            `company` varchar(100) NOT NULL DEFAULT '',
            `gender` tinyint(4) NOT NULL DEFAULT '0',
            `birthday` date DEFAULT NULL,
            `avatar` varchar(255) NOT NULL DEFAULT '',
            `level_id` int(10) unsigned NOT NULL DEFAULT '0',
            `staff_id` int(10) unsigned NOT NULL DEFAULT '0',
            `balance` decimal(12,2) NOT NULL DEFAULT '0.00',
            `points` int(11) NOT NULL DEFAULT '0',
            `total_spent` decimal(12,2) NOT NULL DEFAULT '0.00',
            `order_count` int(11) NOT NULL DEFAULT '0',
            `status` tinyint(4) NOT NULL DEFAULT '1',
            `source` varchar(20) NOT NULL DEFAULT 'admin',
            `remark` varchar(255) NOT NULL DEFAULT '',
            `register_ip` varchar(45) NOT NULL DEFAULT '',
            `last_login_at` int(11) NOT NULL DEFAULT '0',
            `last_login_ip` varchar(45) NOT NULL DEFAULT '',
            `created_at` int(11) NOT NULL DEFAULT '0',
            `updated_at` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_member_no` (`member_no`),
            UNIQUE KEY `uk_username` (`username`),
            UNIQUE KEY `uk_mobile` (`mobile`),
            KEY `idx_level` (`level_id`),
            KEY `idx_staff` (`staff_id`),
            KEY `idx_status` (`status`),
            KEY `idx_spent` (`total_spent`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='会员主表'");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `{$pfx}member_level` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL DEFAULT '',
            `code` varchar(30) NOT NULL DEFAULT '',
            `min_spent` decimal(12,2) NOT NULL DEFAULT '0.00',
            `discount` decimal(5,2) NOT NULL DEFAULT '1.00',
            `points_rate` decimal(8,2) NOT NULL DEFAULT '0.00',
            `is_default` tinyint(4) NOT NULL DEFAULT '0',
            `sort` int(11) NOT NULL DEFAULT '0',
            `status` tinyint(4) NOT NULL DEFAULT '1',
            `description` varchar(255) NOT NULL DEFAULT '',
            `created_at` int(11) NOT NULL DEFAULT '0',
            `updated_at` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_code` (`code`),
            KEY `idx_spent` (`min_spent`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='会员等级'");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `{$pfx}member_balance_log` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `member_id` int(10) unsigned NOT NULL DEFAULT '0',
            `change_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
            `before_balance` decimal(12,2) NOT NULL DEFAULT '0.00',
            `after_balance` decimal(12,2) NOT NULL DEFAULT '0.00',
            `type` varchar(20) NOT NULL DEFAULT 'adjust',
            `biz_type` varchar(30) NOT NULL DEFAULT '',
            `biz_no` varchar(64) DEFAULT NULL,
            `operator_type` varchar(20) NOT NULL DEFAULT 'system',
            `operator_id` int(10) unsigned NOT NULL DEFAULT '0',
            `remark` varchar(255) NOT NULL DEFAULT '',
            `ip` varchar(45) NOT NULL DEFAULT '',
            `created_at` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_biz_no` (`biz_no`),
            KEY `idx_member` (`member_id`),
            KEY `idx_time` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='余额变动日志'");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `{$pfx}member_points_log` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `member_id` int(10) unsigned NOT NULL DEFAULT '0',
            `change_points` int(11) NOT NULL DEFAULT '0',
            `before_points` int(11) NOT NULL DEFAULT '0',
            `after_points` int(11) NOT NULL DEFAULT '0',
            `type` varchar(20) NOT NULL DEFAULT 'adjust',
            `biz_type` varchar(30) NOT NULL DEFAULT '',
            `biz_no` varchar(64) DEFAULT NULL,
            `operator_type` varchar(20) NOT NULL DEFAULT 'system',
            `operator_id` int(10) unsigned NOT NULL DEFAULT '0',
            `remark` varchar(255) NOT NULL DEFAULT '',
            `ip` varchar(45) NOT NULL DEFAULT '',
            `created_at` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_biz_no` (`biz_no`),
            KEY `idx_member` (`member_id`),
            KEY `idx_time` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='积分变动日志'");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `{$pfx}member_address` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `member_id` int(10) unsigned NOT NULL DEFAULT '0',
            `receiver` varchar(50) NOT NULL DEFAULT '',
            `mobile` varchar(20) NOT NULL DEFAULT '',
            `province` varchar(50) NOT NULL DEFAULT '',
            `city` varchar(50) NOT NULL DEFAULT '',
            `district` varchar(50) NOT NULL DEFAULT '',
            `detail` varchar(255) NOT NULL DEFAULT '',
            `postcode` varchar(10) NOT NULL DEFAULT '',
            `is_default` tinyint(4) NOT NULL DEFAULT '0',
            `created_at` int(11) NOT NULL DEFAULT '0',
            `updated_at` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `idx_member` (`member_id`),
            KEY `idx_default` (`member_id`,`is_default`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='会员收货地址'");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `{$pfx}member_staff` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL DEFAULT '',
            `password_hash` varchar(255) NOT NULL DEFAULT '',
            `real_name` varchar(50) NOT NULL DEFAULT '',
            `mobile` varchar(20) NOT NULL DEFAULT '',
            `role` varchar(20) NOT NULL DEFAULT 'sales',
            `status` tinyint(4) NOT NULL DEFAULT '1',
            `remark` varchar(255) NOT NULL DEFAULT '',
            `created_at` int(11) NOT NULL DEFAULT '0',
            `updated_at` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_username` (`username`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='业务员账号'");

        self::seedLevels();
        self::ensureConfig();
        $logs[] = '会员数据表已就绪（6 张）';
        $logs[] = '默认会员等级已检查/初始化';
        $logs[] = 'API 密钥与插件配置已检查';
        return $logs;
    }

    public static function uninstall(): array
    {
        $logs = [];
        $pdo  = self::pdo();
        $pfx  = (string) Db::connect('mysql')->getConfig('prefix');
        foreach (['member_balance_log', 'member_points_log', 'member_address', 'member_staff', 'member', 'member_level'] as $t) {
            $pdo->exec("DROP TABLE IF EXISTS `{$pfx}{$t}`");
        }
        try {
            Db::name('plugin')->where('name', self::PLUGIN)->update(['config' => '']);
        } catch (\Throwable $e) {
        }
        $logs[] = '会员插件 6 张表已删除，插件配置已清空';
        return $logs;
    }

    private static function seedLevels(): void
    {
        try {
            $count = (int) Db::name(self::T_LEVEL)->count();
            if ($count > 0) {
                // 确保有且只有一个默认等级
                if ((int) Db::name(self::T_LEVEL)->where('is_default', 1)->count() === 0) {
                    $first = Db::name(self::T_LEVEL)->order('min_spent asc, id asc')->find();
                    if ($first) {
                        Db::name(self::T_LEVEL)->where('id', (int) $first['id'])->update(['is_default' => 1]);
                    }
                }
                return;
            }
            $now  = time();
            $rows = [
                ['name' => '普通会员', 'code' => 'normal', 'min_spent' => '0.00', 'discount' => '1.00', 'points_rate' => '0.00', 'is_default' => 1, 'sort' => 10, 'status' => 1, 'description' => '注册即享会员价', 'created_at' => $now, 'updated_at' => $now],
                ['name' => '银卡会员', 'code' => 'silver', 'min_spent' => '1000.00', 'discount' => '0.98', 'points_rate' => '1.00', 'is_default' => 0, 'sort' => 20, 'status' => 1, 'description' => '累计消费满 1000 元', 'created_at' => $now, 'updated_at' => $now],
                ['name' => '金卡会员', 'code' => 'gold', 'min_spent' => '5000.00', 'discount' => '0.95', 'points_rate' => '1.50', 'is_default' => 0, 'sort' => 30, 'status' => 1, 'description' => '累计消费满 5000 元', 'created_at' => $now, 'updated_at' => $now],
                ['name' => '黑卡会员', 'code' => 'black', 'min_spent' => '20000.00', 'discount' => '0.90', 'points_rate' => '2.00', 'is_default' => 0, 'sort' => 40, 'status' => 1, 'description' => '累计消费满 20000 元', 'created_at' => $now, 'updated_at' => $now],
            ];
            Db::name(self::T_LEVEL)->insertAll($rows);
        } catch (\Throwable $e) {
        }
    }

    private static function ensureConfig(): void
    {
        $cfg  = self::defaults();
        $row  = Db::name('plugin')->where('name', self::PLUGIN)->find();
        $cur  = $row && !empty($row['config']) ? (json_decode((string) $row['config'], true) ?: []) : [];
        $cfg  = array_merge($cfg, $cur);
        if (empty($cfg['api_secret'])) {
            $cfg['api_secret'] = bin2hex(random_bytes(16));
        }
        if ($row) {
            Db::name('plugin')->where('name', self::PLUGIN)->update(['config' => json_encode($cfg, JSON_UNESCAPED_UNICODE)]);
        }
    }

    /* ==================== 等级 ==================== */

    public static function levels(bool $onlyEnabled = false): array
    {
        $q = Db::name(self::T_LEVEL);
        if ($onlyEnabled) {
            $q->where('status', 1);
        }
        return $q->order('min_spent asc, sort asc, id asc')->select()->toArray();
    }

    public static function level(int $id): array
    {
        if ($id <= 0) {
            return [];
        }
        return Db::name(self::T_LEVEL)->where('id', $id)->find() ?: [];
    }

    public static function defaultLevelId(): int
    {
        $row = Db::name(self::T_LEVEL)->where('is_default', 1)->where('status', 1)->find();
        if (!$row) {
            $row = Db::name(self::T_LEVEL)->where('status', 1)->order('min_spent asc, id asc')->find();
        }
        return $row ? (int) $row['id'] : 0;
    }

    public static function levelForSpent(float $spent): array
    {
        $rows = self::levels(true);
        $hit  = [];
        foreach ($rows as $row) {
            if ($spent + 0.001 >= (float) $row['min_spent']) {
                $hit = $row;
            }
        }
        return $hit ?: self::level(self::defaultLevelId());
    }

    public static function maybeUpgrade(int $memberId, bool $force = false): array
    {
        if (!$force && (string) self::cfg()['auto_upgrade'] !== '1') {
            return ['changed' => false, 'reason' => 'auto_upgrade_off'];
        }
        $m = self::getMember($memberId);
        if (!$m) {
            throw new \InvalidArgumentException('会员不存在');
        }
        $target = self::levelForSpent((float) $m['total_spent']);
        if (!$target) {
            return ['changed' => false, 'reason' => 'no_level'];
        }
        $current = self::level((int) $m['level_id']);
        if ((int) $target['id'] === (int) $m['level_id']) {
            return ['changed' => false, 'level' => $target];
        }
        if ($current && (float) $target['min_spent'] <= (float) $current['min_spent']) {
            return ['changed' => false, 'level' => $current, 'reason' => 'not_higher'];
        }
        Db::name(self::T_MEMBER)->where('id', $memberId)->update([
            'level_id'   => (int) $target['id'],
            'updated_at' => time(),
        ]);
        return ['changed' => true, 'from' => $current, 'level' => $target];
    }

    /* ==================== 会员 ==================== */

    public static function getMember(int $id): array
    {
        if ($id <= 0) {
            return [];
        }
        return Db::name(self::T_MEMBER)->where('id', $id)->find() ?: [];
    }

    public static function findByMobile(string $mobile): array
    {
        return Db::name(self::T_MEMBER)->where('mobile', trim($mobile))->find() ?: [];
    }

    public static function findByAccount(string $account): array
    {
        $account = trim($account);
        if ($account === '') {
            return [];
        }
        return Db::name(self::T_MEMBER)->where(function ($q) use ($account) {
            $q->where('mobile', $account)->whereOr('username', $account)->whereOr('email', $account);
        })->find() ?: [];
    }

    public static function publicMember(array $m): array
    {
        if (!$m) {
            return [];
        }
        $lv = self::level((int) ($m['level_id'] ?? 0));
        return [
            'id'          => (int) $m['id'],
            'member_no'   => (string) ($m['member_no'] ?? ''),
            'username'    => (string) ($m['username'] ?? ''),
            'mobile'      => (string) ($m['mobile'] ?? ''),
            'email'       => (string) ($m['email'] ?? ''),
            'real_name'   => (string) ($m['real_name'] ?? ''),
            'company'     => (string) ($m['company'] ?? ''),
            'gender'      => (int) ($m['gender'] ?? 0),
            'birthday'    => (string) ($m['birthday'] ?? ''),
            'remark'      => (string) ($m['remark'] ?? ''),
            'avatar'      => (string) ($m['avatar'] ?? ''),
            'status'      => (int) ($m['status'] ?? 0),
            'level_id'    => (int) ($m['level_id'] ?? 0),
            'level_name'  => (string) ($lv['name'] ?? '普通会员'),
            'discount'    => (float) ($lv['discount'] ?? 1),
            'balance'     => round((float) ($m['balance'] ?? 0), 2),
            'points'      => (int) ($m['points'] ?? 0),
            'total_spent' => round((float) ($m['total_spent'] ?? 0), 2),
            'order_count' => (int) ($m['order_count'] ?? 0),
            'staff_id'    => (int) ($m['staff_id'] ?? 0),
            'created_at'  => (int) ($m['created_at'] ?? 0),
            'last_login_at' => (int) ($m['last_login_at'] ?? 0),
        ];
    }

    public static function currentMember(): array
    {
        $id = (int) session('member_id');
        if ($id <= 0) {
            return [];
        }
        $m = self::getMember($id);
        if (!$m || (int) $m['status'] !== 1) {
            session('member_id', null);
            return [];
        }
        return $m;
    }

    public static function loginMember(string $account, string $password): array
    {
        $m = self::findByAccount($account);
        if (!$m || !password_verify($password, (string) $m['password_hash'])) {
            throw new \InvalidArgumentException('账号或密码错误');
        }
        if ((int) $m['status'] !== 1) {
            throw new \InvalidArgumentException('账号已被停用，请联系管理员');
        }
        Db::name(self::T_MEMBER)->where('id', (int) $m['id'])->update([
            'last_login_at' => time(),
            'last_login_ip' => self::ip(),
            'updated_at'    => time(),
        ]);
        session('member_id', (int) $m['id']);
        return self::getMember((int) $m['id']);
    }

    public static function logout(): void
    {
        session('member_id', null);
    }

    public static function register(array $data): array
    {
        $cfg = self::cfg();
        if ((string) $cfg['allow_register'] !== '1') {
            throw new \InvalidArgumentException('当前未开放会员注册');
        }
        $data['source'] = 'register';
        $data['gift_points'] = (int) $cfg['register_points'];
        return self::createMember($data, 'member', 0);
    }

    public static function createMember(array $data, string $operatorType = 'admin', int $operatorId = 0): array
    {
        $now      = time();
        $mobile   = trim((string) ($data['mobile'] ?? ''));
        $username = trim((string) ($data['username'] ?? ''));
        $realName = mb_substr(trim((string) ($data['real_name'] ?? '')), 0, 50);
        $email    = mb_substr(trim((string) ($data['email'] ?? '')), 0, 100);
        $company  = mb_substr(trim((string) ($data['company'] ?? '')), 0, 100);
        $remark   = mb_substr(trim((string) ($data['remark'] ?? '')), 0, 255);
        $source   = mb_substr(trim((string) ($data['source'] ?? 'admin')), 0, 20);
        $genderValue = (int) ($data['gender'] ?? 0);
        $gender   = in_array($genderValue, [0, 1, 2], true) ? $genderValue : 0;
        $birthday = trim((string) ($data['birthday'] ?? ''));

        if (!preg_match('/^[0-9+\-\s]{6,20}$/', $mobile)) {
            throw new \InvalidArgumentException('手机号格式不正确');
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('邮箱格式不正确');
        }
        if ($birthday !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
            $birthday = '';
        }
        if ($username === '') {
            $username = 'm' . strtolower(bin2hex(random_bytes(4)));
        }
        if (!preg_match('/^[A-Za-z0-9_\-\.]{3,50}$/', $username)) {
            throw new \InvalidArgumentException('用户名只能使用字母、数字、下划线、点、短横线');
        }

        $password = (string) ($data['password'] ?? '');
        if ($password === '') {
            $password = (string) self::cfg()['default_password'];
        }
        if (strlen($password) < 6) {
            throw new \InvalidArgumentException('密码至少 6 位');
        }

        $levelId = (int) ($data['level_id'] ?? 0);
        if ($levelId <= 0 || !self::level($levelId)) {
            $levelId = self::defaultLevelId();
        }
        $staffId = (int) ($data['staff_id'] ?? 0);
        if ($staffId > 0 && !Db::name(self::T_STAFF)->where('id', $staffId)->where('status', 1)->find()) {
            $staffId = 0;
        }

        $balance    = round((float) ($data['balance'] ?? 0), 2);
        $points     = (int) ($data['points'] ?? 0);
        if ($points < 0) {
            throw new \InvalidArgumentException('初始积分不能为负数');
        }
        $giftPoints = (int) ($data['gift_points'] ?? 0);
        $totalSpent = round((float) ($data['total_spent'] ?? 0), 2);
        $status     = isset($data['status']) ? ((int) $data['status'] === 1 ? 1 : 0) : 1;

        if (self::findByMobile($mobile)) {
            throw new \InvalidArgumentException('该手机号已注册');
        }
        if (Db::name(self::T_MEMBER)->where('username', $username)->find()) {
            throw new \InvalidArgumentException('用户名已存在');
        }

        Db::startTrans();
        try {
            $id = (int) Db::name(self::T_MEMBER)->insertGetId([
                'member_no'    => self::nextMemberNo(),
                'username'     => $username,
                'mobile'       => $mobile,
                'email'        => $email,
                'password_hash'=> password_hash($password, PASSWORD_DEFAULT),
                'real_name'    => $realName,
                'company'      => $company,
                'gender'       => $gender,
                'birthday'     => $birthday !== '' ? $birthday : null,
                'level_id'     => $levelId,
                'staff_id'     => $staffId,
                'balance'      => 0,
                'points'       => 0,
                'total_spent'  => $totalSpent,
                'order_count'  => 0,
                'status'       => $status,
                'source'       => $source,
                'remark'       => $remark,
                'register_ip'  => self::ip(),
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
            if ($id <= 0) {
                throw new \RuntimeException('会员写入失败');
            }

            if (abs($balance) > 0.001) {
                Db::name(self::T_MEMBER)->where('id', $id)->update(['balance' => $balance]);
                self::writeBalanceLog($id, $balance, 0, $balance, 'init', 'create', 'INIT-BAL-' . $id, $operatorType, $operatorId, '开户初始余额');
            }
            $ptsAfter = 0;
            if ($points !== 0) {
                $ptsAfter = $points;
                Db::name(self::T_MEMBER)->where('id', $id)->update(['points' => $ptsAfter]);
                self::writePointsLog($id, $points, 0, $ptsAfter, 'init', 'create', 'INIT-PTS-' . $id, $operatorType, $operatorId, '开户初始积分');
            }
            if ($giftPoints > 0) {
                $before = $ptsAfter;
                $ptsAfter += $giftPoints;
                Db::name(self::T_MEMBER)->where('id', $id)->update(['points' => $ptsAfter]);
                self::writePointsLog($id, $giftPoints, $before, $ptsAfter, 'gift', 'register', 'REG-PTS-' . $id, $operatorType, $operatorId, '注册赠送积分');
            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }

        $m = self::getMember($id);
        return [true, '会员创建成功', self::publicMember($m)];
    }

    public static function updateMember(int $id, array $data): array
    {
        $m = self::getMember($id);
        if (!$m) {
            throw new \InvalidArgumentException('会员不存在');
        }
        $allowed = [];
        foreach (['username', 'mobile', 'email', 'real_name', 'company', 'avatar'] as $k) {
            if (array_key_exists($k, $data)) {
                $allowed[$k] = mb_substr(trim((string) $data[$k]), 0, $k === 'avatar' ? 255 : 100);
            }
        }
        if (array_key_exists('mobile', $allowed)) {
            if (!preg_match('/^[0-9+\-\s]{6,20}$/', $allowed['mobile'])) {
                throw new \InvalidArgumentException('手机号格式不正确');
            }
            if ($allowed['mobile'] !== $m['mobile'] && self::findByMobile($allowed['mobile'])) {
                throw new \InvalidArgumentException('手机号已被其它会员使用');
            }
        }
        if (array_key_exists('username', $allowed)) {
            if (!preg_match('/^[A-Za-z0-9_\-\.]{3,50}$/', $allowed['username'])) {
                throw new \InvalidArgumentException('用户名格式不正确');
            }
            if ($allowed['username'] !== $m['username'] && Db::name(self::T_MEMBER)->where('username', $allowed['username'])->find()) {
                throw new \InvalidArgumentException('用户名已被使用');
            }
        }
        if (array_key_exists('email', $allowed) && $allowed['email'] !== '' && !filter_var($allowed['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('邮箱格式不正确');
        }
        if (array_key_exists('gender', $data)) {
            $g = (int) $data['gender'];
            $allowed['gender'] = in_array($g, [0, 1, 2], true) ? $g : 0;
        }
        if (array_key_exists('birthday', $data)) {
            $b = trim((string) $data['birthday']);
            $allowed['birthday'] = ($b !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $b)) ? $b : null;
        }
        if (array_key_exists('level_id', $data)) {
            $lv = (int) $data['level_id'];
            $allowed['level_id'] = self::level($lv) ? $lv : self::defaultLevelId();
        }
        if (array_key_exists('staff_id', $data)) {
            $allowed['staff_id'] = max(0, (int) $data['staff_id']);
        }
        if (array_key_exists('status', $data)) {
            $allowed['status'] = (int) $data['status'] === 1 ? 1 : 0;
        }
        if (array_key_exists('remark', $data)) {
            $allowed['remark'] = mb_substr(trim((string) $data['remark']), 0, 255);
        }
        if (array_key_exists('total_spent', $data)) {
            $allowed['total_spent'] = max(0, round((float) $data['total_spent'], 2));
        }
        if (!empty($data['password'])) {
            $pwd = (string) $data['password'];
            if (strlen($pwd) < 6) {
                throw new \InvalidArgumentException('密码至少 6 位');
            }
            $allowed['password_hash'] = password_hash($pwd, PASSWORD_DEFAULT);
        }
        if (!$allowed) {
            return [false, '没有可更新的字段', self::publicMember($m)];
        }
        $allowed['updated_at'] = time();
        Db::name(self::T_MEMBER)->where('id', $id)->update($allowed);
        self::maybeUpgrade($id);
        return [true, '会员资料已更新', self::publicMember(self::getMember($id))];
    }

    public static function changePassword(int $id, string $old, string $new, bool $checkOld = true): array
    {
        $m = self::getMember($id);
        if (!$m) {
            throw new \InvalidArgumentException('会员不存在');
        }
        if ($checkOld && !password_verify($old, (string) $m['password_hash'])) {
            throw new \InvalidArgumentException('原密码不正确');
        }
        if (strlen($new) < 6) {
            throw new \InvalidArgumentException('新密码至少 6 位');
        }
        Db::name(self::T_MEMBER)->where('id', $id)->update([
            'password_hash' => password_hash($new, PASSWORD_DEFAULT),
            'updated_at'    => time(),
        ]);
        return [true, '密码已修改'];
    }

    public static function updateProfile(int $id, array $data): array
    {
        $fields = [];
        foreach (['real_name', 'email', 'company', 'avatar'] as $k) {
            if (array_key_exists($k, $data)) {
                $fields[$k] = mb_substr(trim((string) $data[$k]), 0, $k === 'avatar' ? 255 : 100);
            }
        }
        if (isset($fields['email']) && $fields['email'] !== '' && !filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('邮箱格式不正确');
        }
        if (array_key_exists('gender', $data)) {
            $g = (int) $data['gender'];
            $fields['gender'] = in_array($g, [0, 1, 2], true) ? $g : 0;
        }
        if (array_key_exists('birthday', $data)) {
            $b = trim((string) $data['birthday']);
            $fields['birthday'] = ($b !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $b)) ? $b : null;
        }
        if (!$fields) {
            return [false, '没有可保存的资料'];
        }
        $fields['updated_at'] = time();
        Db::name(self::T_MEMBER)->where('id', $id)->update($fields);
        return [true, '资料已保存'];
    }

    private static function nextMemberNo(): string
    {
        do {
            $no = 'M' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));
        } while (Db::name(self::T_MEMBER)->where('member_no', $no)->find());
        return $no;
    }

    /* ==================== 余额 / 积分 ==================== */

    public static function applyBalance(int $memberId, float $delta, string $type, array $opt = []): array
    {
        $delta = round($delta, 2);
        if (abs($delta) < 0.001) {
            throw new \InvalidArgumentException('变动金额不能为 0');
        }
        if (!isset(self::BALANCE_TYPES[$type])) {
            throw new \InvalidArgumentException('余额变动类型不合法');
        }
        $operatorType = (string) ($opt['operator_type'] ?? 'system');
        $operatorId   = (int) ($opt['operator_id'] ?? 0);
        $bizType      = mb_substr((string) ($opt['biz_type'] ?? ''), 0, 30);
        $bizNo        = trim((string) ($opt['biz_no'] ?? ''));
        $remark       = mb_substr((string) ($opt['remark'] ?? ''), 0, 255);

        if (in_array($type, ['consume', 'refund', 'withdraw'], true) && $bizNo === '') {
            throw new \InvalidArgumentException('消费、退款、提现必须传业务单号 biz_no');
        }

        Db::startTrans();
        try {
            $m = Db::name(self::T_MEMBER)->where('id', $memberId)->lock(true)->find();
            if (!$m) {
                throw new \InvalidArgumentException('会员不存在');
            }
            if ((int) $m['status'] !== 1) {
                throw new \InvalidArgumentException('会员已停用，不能变动资产');
            }
            if ($bizNo !== '' && Db::name(self::T_BALANCE)->where('biz_no', $bizNo)->find()) {
                throw new \InvalidArgumentException('业务单号已处理：' . $bizNo);
            }
            $before = round((float) $m['balance'], 2);
            $after  = round($before + $delta, 2);
            if ($after < 0) {
                throw new \InvalidArgumentException('余额不足，当前余额 ' . number_format($before, 2));
            }
            Db::name(self::T_MEMBER)->where('id', $memberId)->update([
                'balance'    => $after,
                'updated_at' => time(),
            ]);
            self::writeBalanceLog($memberId, $delta, $before, $after, $type, $bizType, $bizNo !== '' ? $bizNo : null, $operatorType, $operatorId, $remark);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
        return ['member_id' => $memberId, 'before' => $before, 'change' => $delta, 'after' => $after];
    }

    public static function applyPoints(int $memberId, int $delta, string $type, array $opt = []): array
    {
        if ($delta === 0) {
            throw new \InvalidArgumentException('变动积分不能为 0');
        }
        if (!isset(self::POINTS_TYPES[$type])) {
            throw new \InvalidArgumentException('积分变动类型不合法');
        }
        $operatorType = (string) ($opt['operator_type'] ?? 'system');
        $operatorId   = (int) ($opt['operator_id'] ?? 0);
        $bizType      = mb_substr((string) ($opt['biz_type'] ?? ''), 0, 30);
        $bizNo        = trim((string) ($opt['biz_no'] ?? ''));
        $remark       = mb_substr((string) ($opt['remark'] ?? ''), 0, 255);

        if (in_array($type, ['consume', 'deduct', 'refund'], true) && $bizNo === '') {
            throw new \InvalidArgumentException('积分抵扣、扣减、退回必须传业务单号 biz_no');
        }

        Db::startTrans();
        try {
            $m = Db::name(self::T_MEMBER)->where('id', $memberId)->lock(true)->find();
            if (!$m) {
                throw new \InvalidArgumentException('会员不存在');
            }
            if ((int) $m['status'] !== 1) {
                throw new \InvalidArgumentException('会员已停用，不能变动资产');
            }
            if ($bizNo !== '' && Db::name(self::T_POINTS)->where('biz_no', $bizNo)->find()) {
                throw new \InvalidArgumentException('业务单号已处理：' . $bizNo);
            }
            $before = (int) $m['points'];
            $after  = $before + $delta;
            if ($after < 0) {
                throw new \InvalidArgumentException('积分不足，当前积分 ' . $before);
            }
            Db::name(self::T_MEMBER)->where('id', $memberId)->update([
                'points'     => $after,
                'updated_at' => time(),
            ]);
            self::writePointsLog($memberId, $delta, $before, $after, $type, $bizType, $bizNo !== '' ? $bizNo : null, $operatorType, $operatorId, $remark);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
        return ['member_id' => $memberId, 'before' => $before, 'change' => $delta, 'after' => $after];
    }

    public static function addSpend(int $memberId, float $amount, string $orderNo = '', array $opt = []): array
    {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new \InvalidArgumentException('消费金额必须大于 0');
        }
        if ($orderNo === '') {
            throw new \InvalidArgumentException('累计消费必须传订单号 order_no');
        }
        $operatorType = (string) ($opt['operator_type'] ?? 'api');
        $operatorId   = (int) ($opt['operator_id'] ?? 0);
        $remark       = mb_substr((string) ($opt['remark'] ?? '订单消费入账'), 0, 255);

        Db::startTrans();
        try {
            $m = Db::name(self::T_MEMBER)->where('id', $memberId)->lock(true)->find();
            if (!$m) {
                throw new \InvalidArgumentException('会员不存在');
            }
            if ((int) $m['status'] !== 1) {
                throw new \InvalidArgumentException('会员已停用');
            }
            $beforeSpent = round((float) $m['total_spent'], 2);
            $afterSpent  = round($beforeSpent + $amount, 2);
            Db::name(self::T_MEMBER)->where('id', $memberId)->update([
                'total_spent' => $afterSpent,
                'order_count' => (int) $m['order_count'] + 1,
                'updated_at'  => time(),
            ]);
            $levelResult = ['changed' => false];
            if ((string) self::cfg()['auto_upgrade'] === '1') {
                $target = self::levelForSpent($afterSpent);
                $current = self::level((int) $m['level_id']);
                if ($target && (!$current || (float) $target['min_spent'] > (float) $current['min_spent'])) {
                    if ((int) $target['id'] !== (int) $m['level_id']) {
                        Db::name(self::T_MEMBER)->where('id', $memberId)->update(['level_id' => (int) $target['id']]);
                        $levelResult = ['changed' => true, 'from' => $current, 'level' => $target];
                    }
                }
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
        $m = self::getMember($memberId);
        return [
            'member_id'    => $memberId,
            'order_no'     => $orderNo,
            'amount'       => $amount,
            'before_spent' => $beforeSpent,
            'after_spent'  => $afterSpent,
            'level'        => self::publicMember($m)['level_name'] ?? '',
            'level_changed'=> (bool) ($levelResult['changed'] ?? false),
        ];
    }

    public static function discountFor(int $memberId, float $amount = 0): array
    {
        $m = self::publicMember(self::getMember($memberId));
        if (!$m) {
            throw new \InvalidArgumentException('会员不存在');
        }
        $rate = round((float) $m['discount'], 2);
        if ($rate <= 0 || $rate > 1) {
            $rate = 1.0;
        }
        return [
            'member_id' => $memberId,
            'level_name'=> $m['level_name'],
            'discount'  => $rate,
            'amount'    => round($amount, 2),
            'payable'   => round($amount * $rate, 2),
            'saved'     => round($amount * (1 - $rate), 2),
        ];
    }

    private static function writeBalanceLog(int $memberId, float $change, float $before, float $after, string $type, string $bizType, ?string $bizNo, string $operatorType, int $operatorId, string $remark): void
    {
        Db::name(self::T_BALANCE)->insert([
            'member_id'     => $memberId,
            'change_amount' => round($change, 2),
            'before_balance'=> round($before, 2),
            'after_balance' => round($after, 2),
            'type'          => $type,
            'biz_type'      => $bizType,
            'biz_no'        => $bizNo,
            'operator_type' => $operatorType,
            'operator_id'   => $operatorId,
            'remark'        => $remark,
            'ip'            => self::ip(),
            'created_at'    => time(),
        ]);
    }

    private static function writePointsLog(int $memberId, int $change, int $before, int $after, string $type, string $bizType, ?string $bizNo, string $operatorType, int $operatorId, string $remark): void
    {
        Db::name(self::T_POINTS)->insert([
            'member_id'     => $memberId,
            'change_points' => $change,
            'before_points' => $before,
            'after_points'  => $after,
            'type'          => $type,
            'biz_type'      => $bizType,
            'biz_no'        => $bizNo,
            'operator_type' => $operatorType,
            'operator_id'   => $operatorId,
            'remark'        => $remark,
            'ip'            => self::ip(),
            'created_at'    => time(),
        ]);
    }

    private static function ip(): string
    {
        try {
            return (string) request()->ip();
        } catch (\Throwable $e) {
            return '';
        }
    }

    /* ==================== 查询 / 列表 ==================== */

    public static function listMembers(array $filter = [], int $staffId = 0, int $page = 1, int $size = 20): array
    {
        $size = max(1, min(100, $size));
        $page = max(1, $page);
        $q    = self::memberQuery($filter, $staffId);
        $total = (int) (clone $q)->count();
        $rows  = $q->order('id desc')->limit(($page - 1) * $size, $size)->select()->toArray();

        $levels = [];
        foreach (self::levels() as $lv) {
            $levels[(int) $lv['id']] = $lv;
        }
        $staff = [];
        foreach (self::staffList() as $s) {
            $staff[(int) $s['id']] = $s;
        }
        foreach ($rows as &$row) {
            $lv = $levels[(int) $row['level_id']] ?? [];
            $row['level_name'] = (string) ($lv['name'] ?? '普通会员');
            $row['discount']   = (float) ($lv['discount'] ?? 1);
            $row['staff_name'] = (string) ($staff[(int) $row['staff_id']]['real_name'] ?? '');
        }
        unset($row);

        return [
            'list'  => $rows,
            'total' => $total,
            'page'  => $page,
            'pages' => (int) ceil($total / $size),
            'size'  => $size,
        ];
    }

    private static function memberQuery(array $filter, int $staffId)
    {
        $q = Db::name(self::T_MEMBER);
        if ($staffId > 0) {
            $q->where('staff_id', $staffId);
        }
        $kw = trim((string) ($filter['keyword'] ?? ''));
        if ($kw !== '') {
            $q->where(function ($qq) use ($kw) {
                $qq->whereLike('member_no', '%' . $kw . '%')
                    ->whereOr('username', 'like', '%' . $kw . '%')
                    ->whereOr('mobile', 'like', '%' . $kw . '%')
                    ->whereOr('real_name', 'like', '%' . $kw . '%')
                    ->whereOr('company', 'like', '%' . $kw . '%');
            });
        }
        if (!empty($filter['level_id'])) {
            $q->where('level_id', (int) $filter['level_id']);
        }
        if (isset($filter['status']) && $filter['status'] !== '') {
            $q->where('status', (int) $filter['status']);
        }
        if (isset($filter['staff_id']) && $filter['staff_id'] !== '' && $staffId === 0) {
            $q->where('staff_id', (int) $filter['staff_id']);
        }
        if (!empty($filter['assigned'])) {
            $q->where('staff_id', '>', 0);
        }
        if (!empty($filter['unassigned'])) {
            $q->where('staff_id', 0);
        }
        return $q;
    }

    public static function memberLogs(int $memberId, string $kind = 'balance', int $page = 1, int $size = 20): array
    {
        $table = $kind === 'points' ? self::T_POINTS : self::T_BALANCE;
        $size  = max(1, min(100, $size));
        $page  = max(1, $page);
        $q     = Db::name($table)->where('member_id', $memberId);
        $total = (int) (clone $q)->count();
        $rows  = $q->order('id desc')->limit(($page - 1) * $size, $size)->select()->toArray();
        foreach ($rows as &$row) {
            $row['type_text'] = $kind === 'points'
                ? (self::POINTS_TYPES[$row['type']] ?? $row['type'])
                : (self::BALANCE_TYPES[$row['type']] ?? $row['type']);
            $row['operator_text'] = self::OPERATOR_TYPES[$row['operator_type']] ?? $row['operator_type'];
        }
        unset($row);
        return ['list' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int) ceil($total / $size), 'size' => $size];
    }

    public static function adminLogs(string $kind = 'balance', array $filter = [], int $page = 1, int $size = 30): array
    {
        $table = $kind === 'points' ? self::T_POINTS : self::T_BALANCE;
        $size  = max(1, min(100, $size));
        $page  = max(1, $page);
        $q     = Db::name($table);
        if (!empty($filter['member_id'])) {
            $q->where('member_id', (int) $filter['member_id']);
        }
        $kw = trim((string) ($filter['keyword'] ?? ''));
        if ($kw !== '') {
            $ids = Db::name(self::T_MEMBER)->where(function ($qq) use ($kw) {
                $qq->whereLike('mobile', '%' . $kw . '%')
                    ->whereOr('member_no', 'like', '%' . $kw . '%')
                    ->whereOr('real_name', 'like', '%' . $kw . '%')
                    ->whereOr('username', 'like', '%' . $kw . '%');
            })->column('id');
            if (!$ids) {
                return ['list' => [], 'total' => 0, 'page' => 1, 'pages' => 0, 'size' => $size];
            }
            $q->whereIn('member_id', $ids);
        }
        if (!empty($filter['type'])) {
            $q->where('type', (string) $filter['type']);
        }
        $total = (int) (clone $q)->count();
        $rows  = $q->order('id desc')->limit(($page - 1) * $size, $size)->select()->toArray();
        foreach ($rows as &$row) {
            $row['type_text'] = $kind === 'points'
                ? (self::POINTS_TYPES[$row['type']] ?? $row['type'])
                : (self::BALANCE_TYPES[$row['type']] ?? $row['type']);
            $row['operator_text'] = self::OPERATOR_TYPES[$row['operator_type']] ?? $row['operator_type'];
            $row['member'] = self::getMember((int) $row['member_id']);
        }
        unset($row);
        return ['list' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int) ceil($total / $size), 'size' => $size];
    }

    public static function stats(): array
    {
        try {
            $levels = self::levels();
            $dist = [];
            foreach ($levels as $lv) {
                $dist[] = [
                    'name'  => (string) $lv['name'],
                    'count' => (int) Db::name(self::T_MEMBER)->where('level_id', (int) $lv['id'])->count(),
                ];
            }
            return [
                'members'   => (int) Db::name(self::T_MEMBER)->count(),
                'today'     => (int) Db::name(self::T_MEMBER)->where('created_at', '>=', strtotime('today'))->count(),
                'balance'   => round((float) Db::name(self::T_MEMBER)->sum('balance'), 2),
                'points'    => (int) Db::name(self::T_MEMBER)->sum('points'),
                'spent'     => round((float) Db::name(self::T_MEMBER)->sum('total_spent'), 2),
                'unassigned'=> (int) Db::name(self::T_MEMBER)->where('staff_id', 0)->count(),
                'disabled'  => (int) Db::name(self::T_MEMBER)->where('status', 0)->count(),
                'staff'     => (int) Db::name(self::T_STAFF)->where('status', 1)->count(),
                'levels'    => $dist,
            ];
        } catch (\Throwable $e) {
            return ['members' => 0, 'today' => 0, 'balance' => 0, 'points' => 0, 'spent' => 0, 'unassigned' => 0, 'disabled' => 0, 'staff' => 0, 'levels' => []];
        }
    }

    public static function pager(int $page, int $pages, string $base = ''): string
    {
        if ($pages < 2) {
            return '';
        }
        $page = max(1, min($pages, $page));
        $item = function (int $p, string $label, bool $on = false) use ($base): string {
            $url = htmlspecialchars($base . 'page=' . $p, ENT_QUOTES, 'UTF-8');
            $cls = $on ? ' class="on"' : '';
            return '<a' . $cls . ' href="' . $url . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
        };
        $out = [];
        if ($page > 1) {
            $out[] = $item($page - 1, '上一页');
        }
        $start = max(1, $page - 2);
        $end   = min($pages, $page + 2);
        if ($start > 1) {
            $out[] = $item(1, '1');
            if ($start > 2) $out[] = '<span class="muted">…</span>';
        }
        for ($i = $start; $i <= $end; $i++) {
            $out[] = $item($i, (string) $i, $i === $page);
        }
        if ($end < $pages) {
            if ($end < $pages - 1) $out[] = '<span class="muted">…</span>';
            $out[] = $item($pages, (string) $pages);
        }
        if ($page < $pages) {
            $out[] = $item($page + 1, '下一页');
        }
        return implode('', $out);
    }

    public static function discountText(float $discount): string
    {
        if ($discount >= 1) {
            return '无折扣';
        }
        $v = rtrim(rtrim(number_format($discount * 10, 1, '.', ''), '0'), '.');
        return $v . ' 折';
    }

    public static function topMembers(int $limit = 5): array
    {
        $rows = Db::name(self::T_MEMBER)->order('total_spent desc, id desc')->limit($limit)->select()->toArray();
        foreach ($rows as &$row) {
            $lv = self::level((int) ($row['level_id'] ?? 0));
            $row['level_name'] = (string) ($lv['name'] ?? '普通会员');
        }
        unset($row);
        return $rows;
    }

    public static function recentBalanceLogs(int $limit = 8): array
    {
        $rows = Db::name(self::T_BALANCE)->order('id desc')->limit($limit)->select()->toArray();
        foreach ($rows as &$row) {
            $row['type_text'] = self::BALANCE_TYPES[$row['type']] ?? $row['type'];
            $row['operator_text'] = self::OPERATOR_TYPES[$row['operator_type']] ?? $row['operator_type'];
            $row['member'] = self::getMember((int) $row['member_id']);
        }
        unset($row);
        return $rows;
    }

    /* ==================== 地址 ==================== */

    public static function addresses(int $memberId): array
    {
        return Db::name(self::T_ADDRESS)->where('member_id', $memberId)
            ->order('is_default desc, id desc')->select()->toArray();
    }

    public static function saveAddress(int $memberId, array $data): array
    {
        $id       = (int) ($data['id'] ?? 0);
        $receiver = mb_substr(trim((string) ($data['receiver'] ?? '')), 0, 50);
        $mobile   = trim((string) ($data['mobile'] ?? ''));
        $province = mb_substr(trim((string) ($data['province'] ?? '')), 0, 50);
        $city     = mb_substr(trim((string) ($data['city'] ?? '')), 0, 50);
        $district = mb_substr(trim((string) ($data['district'] ?? '')), 0, 50);
        $detail   = mb_substr(trim((string) ($data['detail'] ?? '')), 0, 255);
        $postcode = mb_substr(trim((string) ($data['postcode'] ?? '')), 0, 10);
        $default  = (int) ($data['is_default'] ?? 0) === 1 ? 1 : 0;

        if ($receiver === '' || $mobile === '' || $detail === '') {
            throw new \InvalidArgumentException('收货人、手机号、详细地址不能为空');
        }
        if (!preg_match('/^[0-9+\-\s]{6,20}$/', $mobile)) {
            throw new \InvalidArgumentException('收货手机号格式不正确');
        }
        if ($id > 0 && !Db::name(self::T_ADDRESS)->where('id', $id)->where('member_id', $memberId)->find()) {
            throw new \InvalidArgumentException('地址不存在');
        }

        Db::startTrans();
        try {
            if ($default === 1) {
                Db::name(self::T_ADDRESS)->where('member_id', $memberId)->update(['is_default' => 0]);
            }
            $fields = [
                'member_id' => $memberId,
                'receiver'  => $receiver,
                'mobile'    => $mobile,
                'province'  => $province,
                'city'      => $city,
                'district'  => $district,
                'detail'    => $detail,
                'postcode'  => $postcode,
                'is_default'=> $default,
                'updated_at'=> time(),
            ];
            if ($id > 0) {
                Db::name(self::T_ADDRESS)->where('id', $id)->where('member_id', $memberId)->update($fields);
            } else {
                // 第一条地址自动设为默认
                if ((int) Db::name(self::T_ADDRESS)->where('member_id', $memberId)->count() === 0) {
                    $fields['is_default'] = 1;
                }
                $fields['created_at'] = time();
                Db::name(self::T_ADDRESS)->insert($fields);
                $id = (int) Db::name(self::T_ADDRESS)->getLastInsID();
            }
            if ($default === 0) {
                $has = (int) Db::name(self::T_ADDRESS)->where('member_id', $memberId)->where('is_default', 1)->count();
                if ($has === 0) {
                    Db::name(self::T_ADDRESS)->where('id', $id)->where('member_id', $memberId)->update(['is_default' => 1]);
                }
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
        return [true, '地址已保存', $id];
    }

    public static function deleteAddress(int $memberId, int $id): array
    {
        $row = Db::name(self::T_ADDRESS)->where('id', $id)->where('member_id', $memberId)->find();
        if (!$row) {
            throw new \InvalidArgumentException('地址不存在');
        }
        Db::name(self::T_ADDRESS)->where('id', $id)->delete();
        if ((int) $row['is_default'] === 1) {
            $next = Db::name(self::T_ADDRESS)->where('member_id', $memberId)->order('id desc')->find();
            if ($next) {
                Db::name(self::T_ADDRESS)->where('id', (int) $next['id'])->update(['is_default' => 1]);
            }
        }
        return [true, '地址已删除'];
    }

    /* ==================== 业务员 ==================== */

    public static function staffList(bool $onlyEnabled = false): array
    {
        $q = Db::name(self::T_STAFF);
        if ($onlyEnabled) {
            $q->where('status', 1);
        }
        $rows = $q->order('status desc, id asc')->select()->toArray();
        foreach ($rows as &$row) {
            $row['member_count'] = (int) Db::name(self::T_MEMBER)->where('staff_id', (int) $row['id'])->count();
        }
        unset($row);
        return $rows;
    }

    public static function staffSave(array $data, int $id = 0): array
    {
        $username = trim((string) ($data['username'] ?? ''));
        $realName = mb_substr(trim((string) ($data['real_name'] ?? '')), 0, 50);
        $mobile   = trim((string) ($data['mobile'] ?? ''));
        $remark   = mb_substr(trim((string) ($data['remark'] ?? '')), 0, 255);
        $status   = (int) ($data['status'] ?? 1) === 1 ? 1 : 0;
        $password = (string) ($data['password'] ?? '');

        if (!preg_match('/^[A-Za-z0-9_\-\.]{3,50}$/', $username)) {
            throw new \InvalidArgumentException('业务员账号需为 3-50 位字母、数字、下划线、点或短横线');
        }
        if ($realName === '') {
            throw new \InvalidArgumentException('业务员姓名不能为空');
        }
        if ($id > 0) {
            $exists = Db::name(self::T_STAFF)->where('id', $id)->find();
            if (!$exists) {
                throw new \InvalidArgumentException('业务员不存在');
            }
        }
        if (Db::name(self::T_STAFF)->where('username', $username)->where('id', '<>', $id)->find()) {
            throw new \InvalidArgumentException('业务员账号已存在');
        }

        $fields = [
            'username'   => $username,
            'real_name'  => $realName,
            'mobile'     => $mobile,
            'role'       => 'sales',
            'status'     => $status,
            'remark'     => $remark,
            'updated_at' => time(),
        ];
        if ($password !== '') {
            if (strlen($password) < 6) {
                throw new \InvalidArgumentException('密码至少 6 位');
            }
            $fields['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }
        if ($id > 0) {
            Db::name(self::T_STAFF)->where('id', $id)->update($fields);
            return [true, '业务员已更新'];
        }
        if ($password === '') {
            throw new \InvalidArgumentException('新增业务员必须设置密码');
        }
        $fields['created_at'] = time();
        Db::name(self::T_STAFF)->insert($fields);
        return [true, '业务员已添加'];
    }

    public static function staffDelete(int $id): array
    {
        $row = Db::name(self::T_STAFF)->where('id', $id)->find();
        if (!$row) {
            throw new \InvalidArgumentException('业务员不存在');
        }
        if ((int) Db::name(self::T_MEMBER)->where('staff_id', $id)->count() > 0) {
            throw new \InvalidArgumentException('该业务员名下还有会员，请先重新分配后再删除');
        }
        Db::name(self::T_STAFF)->where('id', $id)->delete();
        return [true, '业务员已删除'];
    }

    public static function staffLogin(string $username, string $password): array
    {
        $s = Db::name(self::T_STAFF)->where('username', trim($username))->find();
        if (!$s || !password_verify($password, (string) $s['password_hash'])) {
            throw new \InvalidArgumentException('账号或密码错误');
        }
        if ((int) $s['status'] !== 1) {
            throw new \InvalidArgumentException('账号已停用');
        }
        session('member_staff_id', (int) $s['id']);
        return $s;
    }

    public static function currentStaff(): array
    {
        $id = (int) session('member_staff_id');
        if ($id <= 0) {
            return [];
        }
        $s = Db::name(self::T_STAFF)->where('id', $id)->find();
        if (!$s || (int) $s['status'] !== 1) {
            session('member_staff_id', null);
            return [];
        }
        return $s;
    }

    public static function staffLogout(): void
    {
        session('member_staff_id', null);
    }

    public static function staffMemberCount(int $staffId): int
    {
        return (int) Db::name(self::T_MEMBER)->where('staff_id', $staffId)->count();
    }

    /* ==================== 导入 / 导出 ==================== */

    public static function exportRows(): array
    {
        $levels = [];
        foreach (self::levels() as $lv) {
            $levels[(int) $lv['id']] = $lv['name'];
        }
        $staff = [];
        foreach (self::staffList() as $s) {
            $staff[(int) $s['id']] = $s['real_name'];
        }
        $rows = Db::name(self::T_MEMBER)->order('id desc')->select()->toArray();
        $out  = [];
        foreach ($rows as $m) {
            $out[] = [
                $m['member_no'],
                $m['mobile'],
                $m['username'],
                $m['real_name'],
                $m['email'],
                $m['company'],
                $levels[(int) $m['level_id']] ?? '',
                $staff[(int) $m['staff_id']] ?? '',
                number_format((float) $m['balance'], 2, '.', ''),
                (string) (int) $m['points'],
                number_format((float) $m['total_spent'], 2, '.', ''),
                (int) $m['status'] === 1 ? '正常' : '停用',
                $m['created_at'] ? date('Y-m-d H:i', (int) $m['created_at']) : '',
                $m['remark'],
            ];
        }
        return $out;
    }

    public static function exportHeaders(): array
    {
        return ['会员编号', '手机号', '用户名', '姓名', '邮箱', '公司', '等级', '业务员', '余额', '积分', '累计消费', '状态', '注册时间', '备注'];
    }

    public static function importHeaders(): array
    {
        return ['手机号*', '姓名', '用户名', '密码', '邮箱', '公司', '等级', '业务员', '余额', '积分', '累计消费', '备注'];
    }

    /**
     * @param array $rows 每行是“按表头顺序”的数组或关联数组
     */
    public static function importRows(array $rows, int $operatorId = 0): array
    {
        $res = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];
        $levels = self::levels();
        $levelMap = [];
        foreach ($levels as $lv) {
            $levelMap[mb_strtolower(trim((string) $lv['name']))] = (int) $lv['id'];
            $levelMap[mb_strtolower(trim((string) $lv['code']))] = (int) $lv['id'];
        }
        $staffs = self::staffList();
        $staffMap = [];
        foreach ($staffs as $s) {
            $staffMap[mb_strtolower(trim((string) $s['username']))] = (int) $s['id'];
            $staffMap[mb_strtolower(trim((string) $s['real_name']))] = (int) $s['id'];
        }
        $defaultPassword = (string) self::cfg()['default_password'];

        foreach ($rows as $i => $row) {
            $line = $i + 2; // 表头占第 1 行
            try {
                $mobile = trim((string) ($row['mobile'] ?? ''));
                if ($mobile === '') {
                    $res['skipped']++;
                    continue;
                }
                $existing = self::findByMobile($mobile);
                if ($existing) {
                    $upd = [
                        'real_name' => trim((string) ($row['real_name'] ?? '')),
                        'email'     => trim((string) ($row['email'] ?? '')),
                        'company'   => trim((string) ($row['company'] ?? '')),
                        'remark'    => trim((string) ($row['remark'] ?? '')),
                    ];
                    if ($upd['real_name'] === '') unset($upd['real_name']);
                    if ($upd['email'] === '') unset($upd['email']);
                    if ($upd['company'] === '') unset($upd['company']);
                    if ($upd['remark'] === '') unset($upd['remark']);
                    $lvName = mb_strtolower(trim((string) ($row['level'] ?? '')));
                    if ($lvName !== '' && isset($levelMap[$lvName])) {
                        $upd['level_id'] = $levelMap[$lvName];
                    }
                    $stName = mb_strtolower(trim((string) ($row['staff'] ?? '')));
                    if ($stName !== '' && isset($staffMap[$stName])) {
                        $upd['staff_id'] = $staffMap[$stName];
                    }
                    self::updateMember((int) $existing['id'], $upd);
                    $res['updated']++;
                    continue;
                }

                $levelId = 0;
                $lvName  = mb_strtolower(trim((string) ($row['level'] ?? '')));
                if ($lvName !== '' && isset($levelMap[$lvName])) {
                    $levelId = $levelMap[$lvName];
                }
                $staffId = 0;
                $stName  = mb_strtolower(trim((string) ($row['staff'] ?? '')));
                if ($stName !== '' && isset($staffMap[$stName])) {
                    $staffId = $staffMap[$stName];
                }
                $data = [
                    'mobile'      => $mobile,
                    'real_name'   => trim((string) ($row['real_name'] ?? '')),
                    'username'    => trim((string) ($row['username'] ?? '')),
                    'password'    => trim((string) ($row['password'] ?? '')) ?: $defaultPassword,
                    'email'       => trim((string) ($row['email'] ?? '')),
                    'company'     => trim((string) ($row['company'] ?? '')),
                    'level_id'    => $levelId,
                    'staff_id'    => $staffId,
                    'balance'     => round((float) ($row['balance'] ?? 0), 2),
                    'points'      => (int) ($row['points'] ?? 0),
                    'total_spent' => round((float) ($row['total_spent'] ?? 0), 2),
                    'remark'      => trim((string) ($row['remark'] ?? '')),
                    'source'      => 'import',
                    'status'      => 1,
                ];
                self::createMember($data, 'admin', $operatorId);
                $res['created']++;
            } catch (\Throwable $e) {
                $res['errors'][] = '第 ' . $line . ' 行：' . $e->getMessage();
            }
        }
        return $res;
    }

    /* ==================== 订单桥接（给会员中心用） ==================== */

    /**
     * 商城插件只要提供 plugins\mall\service\OrderBridge::listByMember($memberId,$page,$size)
     * 即可自动在会员中心显示订单；会员插件不碰商城订单表。
     */
    public static function orders(int $memberId, int $page = 1, int $size = 10): array
    {
        $candidates = [
            'plugins\\mall\\service\\OrderBridge',
            'plugins\\shop\\service\\OrderBridge',
        ];
        foreach ($candidates as $class) {
            if (class_exists($class) && method_exists($class, 'listByMember')) {
                $ret = $class::listByMember($memberId, $page, $size);
                if (is_array($ret)) {
                    return array_merge(['list' => [], 'total' => 0, 'page' => $page, 'connected' => true], $ret);
                }
            }
        }
        return ['list' => [], 'total' => 0, 'page' => $page, 'pages' => 0, 'connected' => false, 'message' => '商城订单接口未接入'];
    }
}
