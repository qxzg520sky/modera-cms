<?php
declare(strict_types=1);

namespace plugins\shop\service;

use think\facade\Db;

/**
 * 商城基础服务：配置、建表、统计、公共字段与文案。
 * 本类不读取任何 member_* 表。
 */
class Shop
{
    public const PLUGIN = 'shop';

    public const T_CATEGORY = 'shop_category';
    public const T_PRODUCT  = 'shop_product';
    public const T_SKU      = 'shop_sku';
    public const T_CART     = 'shop_cart';
    public const T_ORDER    = 'shop_order';
    public const T_ITEM     = 'shop_order_item';
    public const T_AFTER    = 'shop_after_sale';
    public const T_COUPON   = 'shop_coupon';
    public const T_COUPON_M = 'shop_coupon_member';
    public const T_LOG      = 'shop_order_log';

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

    public static function defaults(): array
    {
        return [
            'enabled'            => '1',
            'shop_name'          => '企业商城',
            'shop_notice'        => '会员专属价，余额与积分可直接抵扣。',
            'page_size'          => '12',
            'points_deduction'   => '1',
            'points_rate'        => '100',   // 100 积分抵 1 元
            'points_max_percent' => '30',    // 最多抵订单金额的 30%
            'balance_pay'        => '1',
            'discount_stack'     => '1',     // 会员折扣与优惠券是否叠加
            'auto_close_minutes' => '30',
            'warn_stock'         => '5',
            'member_mode'        => 'service', // service=调用 Member 服务类；api=走签名 API
            'member_api_base'    => '',
            'member_appid'       => 'modera-mall',
            'member_secret'      => '',
            'api_appid'          => 'shop-client',
            'api_secret'         => '',
            'api_ip_whitelist'   => '',
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
        if ($row && !empty($row['config'])) {
            $json = json_decode((string) $row['config'], true);
            if (is_array($json)) {
                $cfg = array_merge($cfg, $json);
            }
        }
        return $cfg;
    }

    public static function saveCfg(array $patch): void
    {
        $cfg = array_merge(self::cfg(), $patch);
        $json = json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $row = self::row();
        if ($row) {
            Db::name('plugin')->where('name', self::PLUGIN)->update(['config' => $json]);
        } else {
            Db::name('plugin')->insert([
                'name' => self::PLUGIN,
                'title' => '企业商城',
                'version' => '1.0.0',
                'author' => '某某实业',
                'description' => '独立商城插件，会员能力全部通过会员插件服务/API 调用。',
                'enabled' => 0,
                'config' => $json,
            ]);
        }
    }

    public static function install(): array
    {
        $logs = [];
        $pdo  = self::pdo();
        $pfx  = (string) Db::connect('mysql')->getConfig('prefix');
        $file = dirname(__DIR__) . '/sql/install.sql';
        if (!is_file($file)) {
            return ['安装 SQL 文件缺失'];
        }
        $sql = str_replace('__PREFIX__', $pfx, (string) file_get_contents($file));
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);
        foreach (preg_split('/;\s*\n/', $sql) as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '' || str_starts_with($stmt, '--')) {
                continue;
            }
            try {
                $pdo->exec($stmt);
            } catch (\Throwable $e) {
                $logs[] = '建表失败：' . $e->getMessage();
            }
        }

        $cfg = self::cfg();
        if (empty($cfg['api_secret'])) {
            $cfg['api_secret'] = bin2hex(random_bytes(16));
        }
        if ($cfg['member_api_base'] === '' || !str_contains((string) $cfg['member_api_base'], '://')) {
            $domain = '';
            try { $domain = rtrim((string) request()->domain(), '/'); } catch (\Throwable $e) {}
            $cfg['member_api_base'] = str_contains($domain, '://') ? $domain : '';
        }
        self::saveCfg($cfg);

        // 演示数据：仅当商品表为空时写入，避免重复安装产生重复商品
        try {
            if ((int) Db::name(self::T_PRODUCT)->count() === 0) {
                $cid = (int) Db::name(self::T_CATEGORY)->insertGetId([
                    'pid' => 0, 'name' => '企业礼品', 'slug' => 'gift',
                    'sort' => 100, 'status' => 1, 'created_at' => time(), 'updated_at' => time(),
                ]);
                $pid = (int) Db::name(self::T_PRODUCT)->insertGetId([
                    'category_id' => $cid,
                    'title' => '企业定制礼盒（演示商品）',
                    'subtitle' => '支持多规格，演示会员折扣、余额与积分抵扣',
                    'cover' => '',
                    'images' => '[]',
                    'detail' => '<p>这是安装时写入的演示商品，正式使用前可在后台修改或下架。</p>',
                    'price' => '100.00',
                    'market_price' => '128.00',
                    'stock' => 50,
                    'sales' => 0,
                    'status' => 1,
                    'is_hot' => 1,
                    'sort' => 100,
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);
                Db::name(self::T_SKU)->insertAll([
                    ['product_id' => $pid, 'sku_code' => 'DEMO-RED', 'spec_json' => json_encode(['颜色' => '中国红'], JSON_UNESCAPED_UNICODE), 'price' => '100.00', 'market_price' => '128.00', 'stock' => 30, 'warn_stock' => 5, 'status' => 1, 'created_at' => time(), 'updated_at' => time()],
                    ['product_id' => $pid, 'sku_code' => 'DEMO-BLUE', 'spec_json' => json_encode(['颜色' => '商务蓝'], JSON_UNESCAPED_UNICODE), 'price' => '110.00', 'market_price' => '138.00', 'stock' => 20, 'warn_stock' => 5, 'status' => 1, 'created_at' => time(), 'updated_at' => time()],
                ]);
                $logs[] = '已写入演示分类、商品和 SKU';
            }
        } catch (\Throwable $e) {
            $logs[] = '演示数据写入失败：' . $e->getMessage();
        }

        $logs[] = '商城插件安装完成';
        return $logs;
    }

    public static function uninstall(): array
    {
        $logs = [];
        $pdo  = self::pdo();
        $pfx  = (string) Db::connect('mysql')->getConfig('prefix');
        foreach ([
            self::T_LOG, self::T_AFTER, self::T_ITEM, self::T_ORDER,
            self::T_COUPON_M, self::T_COUPON, self::T_CART, self::T_SKU,
            self::T_PRODUCT, self::T_CATEGORY,
        ] as $t) {
            try {
                $pdo->exec('DROP TABLE IF EXISTS `' . $pfx . $t . '`');
                $logs[] = '已删除 ' . $pfx . $t;
            } catch (\Throwable $e) {
                $logs[] = '删除 ' . $t . ' 失败：' . $e->getMessage();
            }
        }
        return $logs;
    }

    public static function stats(): array
    {
        $todayStart = strtotime('today');
        return [
            'products' => (int) Db::name(self::T_PRODUCT)->count(),
            'on_sale'  => (int) Db::name(self::T_PRODUCT)->where('status', 1)->count(),
            'orders'   => (int) Db::name(self::T_ORDER)->count(),
            'today_orders' => (int) Db::name(self::T_ORDER)->where('created_at', '>=', $todayStart)->count(),
            'paid_amount' => round((float) Db::name(self::T_ORDER)->where('pay_status', 1)->where('status', '<>', 'refunded')->sum('payable_amount'), 2),
            'pending'  => (int) Db::name(self::T_ORDER)->where('status', 'pending_payment')->count(),
            'after_sales' => (int) Db::name(self::T_AFTER)->where('status', 'pending')->count(),
            'low_stock' => (int) Db::name(self::T_SKU)->where('status', 1)->where('stock', '<=', Db::raw('warn_stock'))->count(),
        ];
    }

    public static function lowStock(int $limit = 8): array
    {
        return Db::name(self::T_SKU)->alias('s')
            ->join(self::table(self::T_PRODUCT) . ' p', 'p.id=s.product_id')
            ->where('s.status', 1)
            ->where('s.stock', '<=', Db::raw('s.warn_stock'))
            ->field('s.*,p.title')
            ->order('s.stock asc,s.id desc')
            ->limit($limit)
            ->select()->toArray();
    }

    public static function log(int $orderId, string $orderNo, string $action, string $content, string $operatorType = 'system', int $operatorId = 0): void
    {
        Db::name(self::T_LOG)->insert([
            'order_id' => $orderId,
            'order_no' => $orderNo,
            'action' => $action,
            'operator_type' => $operatorType,
            'operator_id' => $operatorId,
            'content' => mb_substr($content, 0, 500),
            'created_at' => time(),
        ]);
    }

    public static function orderNo(): string
    {
        return date('YmdHis') . str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function afterSaleNo(): string
    {
        return 'AS' . date('YmdHis') . str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public static function orderStatusText(string $status): string
    {
        return [
            'pending_payment' => '待付款',
            'paying'          => '支付处理中',
            'paid'            => '已付款',
            'shipped'         => '已发货',
            'completed'       => '已完成',
            'closed'          => '已关闭',
            'refunded'        => '已退款',
        ][$status] ?? $status;
    }

    public static function afterStatusText(string $status): string
    {
        return [
            'pending' => '待审核',
            'approved' => '已同意',
            'rejected' => '已拒绝',
            'refunded' => '已退款',
        ][$status] ?? $status;
    }

    public static function specText(string $json): string
    {
        $arr = json_decode($json, true);
        if (!is_array($arr)) return $json;
        $out = [];
        foreach ($arr as $k => $v) $out[] = $k . ':' . $v;
        return implode(' / ', $out);
    }
}
