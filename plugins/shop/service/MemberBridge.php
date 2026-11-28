<?php
declare(strict_types=1);

namespace plugins\shop\service;

use think\facade\Db;
use think\facade\Log;

/**
 * 会员插件桥接层。
 *
 * 只允许两种方式访问会员能力：
 * 1. service：调用 plugins\member\service\Member 的公开服务方法（同站点推荐，性能好）。
 * 2. api：调用会员插件 /member/api/*，使用 HMAC-SHA256 签名。
 *
 * 禁止在本类或商城任何地方写 SQL 访问 member_* 表。
 */
class MemberBridge
{
    public static function enabled(): bool
    {
        try {
            return in_array('member', \app\service\PluginManager::enabled(), true)
                && class_exists(\plugins\member\service\Member::class);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function current(): array
    {
        if (!self::enabled()) return [];
        try {
            $m = \plugins\member\service\Member::currentMember();
            return $m ? \plugins\member\service\Member::publicMember($m) : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function currentId(): int
    {
        $m = self::current();
        return (int) ($m['id'] ?? 0);
    }

    public static function get(int $memberId): array
    {
        if ($memberId <= 0) return [];
        return self::run('member/get', ['member_id' => $memberId], function () use ($memberId) {
            $m = \plugins\member\service\Member::getMember($memberId);
            return $m ? \plugins\member\service\Member::publicMember($m) : [];
        }, 'member');
    }

    public static function discount(int $memberId, float $amount): array
    {
        return self::run('discount', ['member_id' => $memberId, 'amount' => $amount], function () use ($memberId, $amount) {
            return \plugins\member\service\Member::discountFor($memberId, $amount);
        });
    }

    public static function addresses(int $memberId): array
    {
        return self::run('address/list', ['member_id' => $memberId], function () use ($memberId) {
            return \plugins\member\service\Member::addresses($memberId);
        }, 'list');
    }

    public static function balanceChange(int $memberId, float $delta, string $type, string $bizNo, string $remark = ''): array
    {
        $delta = round($delta, 2);
        if (abs($delta) < 0.001) return [];
        if (self::mode() === 'api') {
            return self::api('balance/change', [
                'member_id' => $memberId,
                'amount' => abs($delta),
                'direction' => $delta > 0 ? 'in' : 'out',
                'type' => $type,
                'biz_type' => 'shop',
                'biz_no' => $bizNo,
                'remark' => $remark,
            ]);
        }
        return \plugins\member\service\Member::applyBalance($memberId, $delta, $type, [
            'operator_type' => 'api',
            'biz_type' => 'shop',
            'biz_no' => $bizNo,
            'remark' => $remark,
        ]);
    }

    public static function pointsChange(int $memberId, int $delta, string $type, string $bizNo, string $remark = ''): array
    {
        if ($delta === 0) return [];
        if (self::mode() === 'api') {
            return self::api('points/change', [
                'member_id' => $memberId,
                'points' => $delta,
                'type' => $type,
                'biz_type' => 'shop',
                'biz_no' => $bizNo,
                'remark' => $remark,
            ]);
        }
        return \plugins\member\service\Member::applyPoints($memberId, $delta, $type, [
            'operator_type' => 'api',
            'biz_type' => 'shop',
            'biz_no' => $bizNo,
            'remark' => $remark,
        ]);
    }

    public static function addSpend(int $memberId, float $amount, string $orderNo): array
    {
        if (self::mode() === 'api') {
            return self::api('spend/record', [
                'member_id' => $memberId,
                'amount' => round($amount, 2),
                'order_no' => $orderNo,
            ]);
        }
        return \plugins\member\service\Member::addSpend($memberId, $amount, $orderNo, ['operator_type' => 'api']);
    }

    public static function staffId(array $member): int
    {
        return (int) ($member['staff_id'] ?? 0);
    }

    private static function mode(): string
    {
        $cfg = Shop::cfg();
        $mode = (string) ($cfg['member_mode'] ?? 'service');
        return $mode === 'api' ? 'api' : 'service';
    }

    /**
     * @param string $path      member/api 下的路径，如 member/get
     * @param array  $payload
     * @param callable $service 服务模式执行器
     * @param string $dataKey   响应 data 中要取的键，空则取整个 data
     */
    private static function run(string $path, array $payload, callable $service, string $dataKey = ''): array
    {
        if (!self::enabled()) {
            throw new \RuntimeException('会员插件未启用，商城无法读取会员信息');
        }
        if (self::mode() === 'api') {
            $data = self::api($path, $payload);
        } else {
            $data = $service();
        }
        if (is_array($data) && $dataKey !== '' && isset($data[$dataKey]) && is_array($data[$dataKey])) {
            return $data[$dataKey];
        }
        return is_array($data) ? $data : [];
    }

    private static function api(string $path, array $payload): array
    {
        $cfg = Shop::cfg();
        $base = rtrim((string) ($cfg['member_api_base'] ?? ''), '/');
        if ($base === '') {
            $base = rtrim((string) request()->domain(), '/');
        }
        $memberCfg = [];
        try {
            $memberCfg = \plugins\member\service\Member::cfg();
        } catch (\Throwable $e) {
        }
        $appid  = (string) ($cfg['member_appid'] ?? ($memberCfg['api_appid'] ?? 'modera-mall'));
        $secret = (string) ($cfg['member_secret'] ?? '');
        if ($secret === '') $secret = (string) ($memberCfg['api_secret'] ?? '');
        if ($appid === '' || $secret === '') {
            throw new \RuntimeException('商城未配置会员 API AppID/Secret');
        }

        $raw = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $ts = time();
        $nonce = bin2hex(random_bytes(12));
        $sign = hash_hmac('sha256', $appid . "\n" . $ts . "\n" . $nonce . "\n" . $raw, $secret);
        $url = $base . '/member/api/' . ltrim($path, '/');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $raw,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Member-Appid: ' . $appid,
                'X-Member-Timestamp: ' . $ts,
                'X-Member-Nonce: ' . $nonce,
                'X-Member-Sign: ' . $sign,
            ],
        ]);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $err !== '') {
            Log::error('[shop] member api timeout: ' . $path . ' ' . $err);
            throw new \RuntimeException('会员接口请求失败，请稍后核对订单状态');
        }
        $json = json_decode((string) $body, true);
        if (!is_array($json)) {
            throw new \RuntimeException('会员接口返回格式错误');
        }
        if ((int) ($json['code'] ?? -1) !== 0) {
            throw new \RuntimeException((string) ($json['msg'] ?? '会员接口业务失败'), $code);
        }
        $data = $json['data'] ?? [];
        return is_array($data) ? $data : [];
    }
}
