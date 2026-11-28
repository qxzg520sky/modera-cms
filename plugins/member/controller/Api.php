<?php
declare(strict_types=1);

namespace plugins\member\controller;

use app\BaseController;
use plugins\member\service\Member;
use think\facade\Cache;

/**
 * 会员开放 API
 *
 * 所有业务接口要求 HMAC-SHA256 签名，供商城/ERP/其它系统服务端调用。
 * 会员中心自己的页面不走这里，走 Front / Staff 的独立会话。
 */
class Api extends BaseController
{
    public function health()
    {
        if ($r = $this->guard()) return $r;
        try {
            $ready = (bool) \think\facade\Db::query("SHOW TABLES LIKE '" . Member::table('member') . "'");
            return json(['code' => 0, 'msg' => 'ok', 'data' => [
                'plugin'  => 'member',
                'version' => '1.0.0',
                'ready'   => $ready,
                'time'    => date('c'),
            ]]);
        } catch (\Throwable $e) {
            return json(['code' => 500, 'msg' => '数据库未就绪'], 500);
        }
    }

    public function memberGet()
    {
        if ($r = $this->guard()) return $r;
        try {
            $id     = (int) $this->param('member_id', 0);
            $mobile = trim((string) $this->param('mobile', ''));
            $m      = $id > 0 ? Member::getMember($id) : ($mobile !== '' ? Member::findByMobile($mobile) : []);
            if (!$m) {
                return $this->fail('会员不存在', 404);
            }
            return $this->ok(['member' => Member::publicMember($m)]);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function memberRegister()
    {
        if ($r = $this->guard()) return $r;
        try {
            $data = $this->body();
            $ret  = Member::register([
                'mobile'    => (string) ($data['mobile'] ?? ''),
                'username'  => (string) ($data['username'] ?? ''),
                'password'  => (string) ($data['password'] ?? ''),
                'real_name' => (string) ($data['real_name'] ?? ''),
                'email'     => (string) ($data['email'] ?? ''),
                'company'   => (string) ($data['company'] ?? ''),
            ]);
            return $this->ok(['member' => $ret[2]], '注册成功');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function memberVerify()
    {
        if ($r = $this->guard()) return $r;
        try {
            $data = $this->body();
            $m    = Member::findByAccount((string) ($data['account'] ?? ''));
            if (!$m || !password_verify((string) ($data['password'] ?? ''), (string) $m['password_hash'])) {
                return $this->fail('账号或密码错误', 401);
            }
            if ((int) $m['status'] !== 1) {
                return $this->fail('账号已停用', 403);
            }
            return $this->ok(['member' => Member::publicMember($m)]);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function memberUpdate()
    {
        if ($r = $this->guard()) return $r;
        try {
            $data = $this->body();
            $id   = (int) ($data['member_id'] ?? 0);
            $ret  = Member::updateProfile($id, [
                'real_name' => (string) ($data['real_name'] ?? ''),
                'email'     => (string) ($data['email'] ?? ''),
                'company'   => (string) ($data['company'] ?? ''),
                'gender'    => (int) ($data['gender'] ?? 0),
                'birthday'  => (string) ($data['birthday'] ?? ''),
            ]);
            return $this->ok(['member' => Member::publicMember(Member::getMember($id))], $ret[1]);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function assets()
    {
        if ($r = $this->guard()) return $r;
        try {
            $id = (int) $this->param('member_id', 0);
            $m  = Member::getMember($id);
            if (!$m) {
                return $this->fail('会员不存在', 404);
            }
            return $this->ok(['member' => Member::publicMember($m)]);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function discount()
    {
        if ($r = $this->guard()) return $r;
        try {
            $id     = (int) $this->param('member_id', 0);
            $amount = (float) $this->param('amount', 0);
            return $this->ok(Member::discountFor($id, $amount));
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function balanceChange()
    {
        if ($r = $this->guard()) return $r;
        try {
            $id      = (int) $this->param('member_id', 0);
            $amount  = abs(round((float) $this->param('amount', 0), 2));
            $dir     = (string) $this->param('direction', 'in');
            $type    = (string) $this->param('type', 'adjust');
            $bizNo   = trim((string) $this->param('biz_no', ''));
            $remark  = (string) $this->param('remark', '');
            if ($amount <= 0) {
                return $this->fail('amount 必须大于 0', 400);
            }
            if (!isset(Member::BALANCE_TYPES[$type])) {
                return $this->fail('type 不合法', 400);
            }
            $delta = $dir === 'out' ? -$amount : $amount;
            $ret   = Member::applyBalance($id, $delta, $type, [
                'operator_type' => 'api',
                'biz_type'      => (string) $this->param('biz_type', 'mall'),
                'biz_no'        => $bizNo,
                'remark'        => $remark,
            ]);
            return $this->ok($ret, '余额已变动');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function pointsChange()
    {
        if ($r = $this->guard()) return $r;
        try {
            $id     = (int) $this->param('member_id', 0);
            $change = (int) $this->param('points', $this->param('change', 0));
            $type   = (string) $this->param('type', 'adjust');
            $bizNo  = trim((string) $this->param('biz_no', ''));
            $remark = (string) $this->param('remark', '');
            if ($change === 0) {
                return $this->fail('points 不能为 0', 400);
            }
            if (!isset(Member::POINTS_TYPES[$type])) {
                return $this->fail('type 不合法', 400);
            }
            $ret = Member::applyPoints($id, $change, $type, [
                'operator_type' => 'api',
                'biz_type'      => (string) $this->param('biz_type', 'mall'),
                'biz_no'        => $bizNo,
                'remark'        => $remark,
            ]);
            return $this->ok($ret, '积分已变动');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function spendRecord()
    {
        if ($r = $this->guard()) return $r;
        try {
            $id      = (int) $this->param('member_id', 0);
            $amount  = round((float) $this->param('amount', 0), 2);
            $orderNo = trim((string) $this->param('order_no', ''));
            $ret     = Member::addSpend($id, $amount, $orderNo, ['operator_type' => 'api']);
            return $this->ok($ret, '累计消费已记录');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function addressList()
    {
        if ($r = $this->guard()) return $r;
        try {
            $id = (int) $this->param('member_id', 0);
            if (!Member::getMember($id)) {
                return $this->fail('会员不存在', 404);
            }
            return $this->ok(['list' => Member::addresses($id)]);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function addressSave()
    {
        if ($r = $this->guard()) return $r;
        try {
            $data = $this->body();
            $id   = (int) ($data['member_id'] ?? 0);
            $ret  = Member::saveAddress($id, $data);
            return $this->ok(['id' => $ret[2]], $ret[1]);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function addressDelete()
    {
        if ($r = $this->guard()) return $r;
        try {
            $data = $this->body();
            $ret  = Member::deleteAddress((int) ($data['member_id'] ?? 0), (int) ($data['address_id'] ?? 0));
            return $this->ok([], $ret[1]);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function orders()
    {
        if ($r = $this->guard()) return $r;
        try {
            $id   = (int) $this->param('member_id', 0);
            $page = max(1, (int) $this->param('page', 1));
            $size = min(50, max(1, (int) $this->param('size', 10)));
            return $this->ok(Member::orders($id, $page, $size));
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    /* ==================== 签名 ==================== */

    private function guard()
    {
        try {
            $this->verifySign();
            return null;
        } catch (\Throwable $e) {
            return json(['code' => 401, 'msg' => '签名校验失败：' . $e->getMessage(), 'data' => null], 401);
        }
    }

    private function verifySign(): void
    {
        $cfg = Member::cfg();
        if ((string) $cfg['enabled'] !== '1') {
            throw new \RuntimeException('会员插件已关闭');
        }
        $secret = (string) $cfg['api_secret'];
        $appid  = (string) $cfg['api_appid'];
        if ($secret === '' || $appid === '') {
            throw new \RuntimeException('API 密钥未配置');
        }

        $ip = (string) $this->request->ip();
        $white = array_filter(array_map('trim', explode(',', (string) $cfg['api_ip_whitelist'])));
        if ($white && !in_array($ip, $white, true)) {
            throw new \RuntimeException('IP 不在白名单：' . $ip);
        }

        $reqAppid = (string) $this->request->header('x-member-appid', '');
        $ts       = (int) $this->request->header('x-member-timestamp', '0');
        $nonce    = (string) $this->request->header('x-member-nonce', '');
        $sign     = strtolower((string) $this->request->header('x-member-sign', ''));

        if ($reqAppid === '' || $reqAppid !== $appid) {
            throw new \RuntimeException('appid 不匹配');
        }
        if ($ts <= 0 || abs(time() - $ts) > 300) {
            throw new \RuntimeException('时间戳已过期');
        }
        if (!preg_match('/^[A-Za-z0-9]{8,64}$/', $nonce)) {
            throw new \RuntimeException('nonce 不合法');
        }
        if ($sign === '') {
            throw new \RuntimeException('缺少签名');
        }
        $raw = (string) $this->request->getContent();
        $expected = hash_hmac('sha256', $appid . "\n" . $ts . "\n" . $nonce . "\n" . $raw, $secret);
        if (!hash_equals($expected, $sign)) {
            throw new \RuntimeException('签名不正确');
        }

        $nonceKey = 'member_api_nonce_' . substr(md5($appid . '|' . $nonce), 0, 32);
        if (Cache::has($nonceKey)) {
            throw new \RuntimeException('nonce 已使用，疑似重放');
        }
        Cache::set($nonceKey, 1, 300);

        // 简单限流：每个应用每分钟最多 120 次
        $rateKey = 'member_api_rate_' . date('YmdHi') . '_' . substr(md5($appid), 0, 12);
        $count   = (int) Cache::get($rateKey, 0);
        if ($count >= 120) {
            throw new \RuntimeException('请求过于频繁，请稍后重试');
        }
        Cache::set($rateKey, $count + 1, 70);
    }

    private function body(): array
    {
        $raw = (string) $this->request->getContent();
        if ($raw !== '') {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                return $json;
            }
        }
        return (array) $this->request->post();
    }

    private function param(string $key, $default = null)
    {
        $body = $this->body();
        if (array_key_exists($key, $body)) {
            return $body[$key];
        }
        return $this->request->param($key, $default);
    }

    private function ok($data = [], string $msg = 'ok')
    {
        return json(['code' => 0, 'msg' => $msg, 'data' => $data]);
    }

    private function fail(string $msg, int $code = 400)
    {
        return json(['code' => $code, 'msg' => $msg, 'data' => null], $code >= 400 && $code < 600 ? $code : 400);
    }
}
