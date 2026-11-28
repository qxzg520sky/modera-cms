<?php
declare(strict_types=1);

namespace plugins\member\controller;

use app\BaseController;
use app\service\View;
use plugins\member\service\Member;

/**
 * 会员中心前台（会员客户登录态）
 * 只允许操作当前 session 对应的 member_id，任何 id 参数都不能越权。
 */
class Front extends BaseController
{
    public function asset(string $file = '')
    {
        $allow = [
            'member.css' => 'text/css; charset=utf-8',
            'admin.css'  => 'text/css; charset=utf-8',
        ];
        if (!isset($allow[$file])) {
            return response('', 404);
        }
        $path = dirname(__DIR__) . '/asset/' . $file;
        if (!is_file($path)) {
            return response('', 404);
        }
        return response((string) file_get_contents($path))
            ->header(['Content-Type' => $allow[$file], 'Cache-Control' => 'public, max-age=3600']);
    }

    public function status()
    {
        $m = Member::currentMember();
        if (!$m) {
            return json(['ok' => 1, 'logged' => false])->header(['Cache-Control' => 'no-store, max-age=0']);
        }
        $pm = Member::publicMember($m);
        return json([
            'ok' => 1,
            'logged' => true,
            'name' => (string) (($pm['real_name'] ?? '') ?: ($pm['username'] ?? '') ?: '会员'),
            'level' => (string) ($pm['level_name'] ?? ''),
            'balance' => number_format((float) ($pm['balance'] ?? 0), 2, '.', ''),
            'points' => (int) ($pm['points'] ?? 0),
        ])->header(['Cache-Control' => 'no-store, max-age=0']);
    }

    public function index()
    {
        if (Member::currentMember()) {
            return redirect('/member/center');
        }
        return redirect('/member/login');
    }

    public function login()
    {
        if (Member::currentMember()) {
            return redirect('/member/center');
        }
        return $this->render('login', ['title' => '会员登录', 'nav' => 'login']);
    }

    public function doLogin()
    {
        $account  = trim((string) $this->request->post('account', ''));
        $password = (string) $this->request->post('password', '');
        try {
            Member::loginMember($account, $password);
            return redirect('/member/center');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage());
            return redirect('/member/login');
        }
    }

    public function register()
    {
        if ((string) Member::cfg()['allow_register'] !== '1') {
            return $this->render('register', ['title' => '会员注册', 'nav' => 'register', 'closed' => true]);
        }
        return $this->render('register', ['title' => '会员注册', 'nav' => 'register', 'closed' => false]);
    }

    public function doRegister()
    {
        try {
            $ret = Member::register([
                'mobile'    => (string) $this->request->post('mobile', ''),
                'username'  => (string) $this->request->post('username', ''),
                'password'  => (string) $this->request->post('password', ''),
                'real_name' => (string) $this->request->post('real_name', ''),
                'email'     => (string) $this->request->post('email', ''),
                'company'   => (string) $this->request->post('company', ''),
                'source'    => 'register',
            ]);
            session('member_id', (int) ($ret[2]['id'] ?? 0));
            return redirect('/member/center');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage());
            return redirect('/member/register');
        }
    }

    public function logout()
    {
        Member::logout();
        return redirect('/member/login');
    }

    public function center()
    {
        $m = Member::currentMember();
        if (!$m) {
            return redirect('/member/login');
        }
        $pub    = Member::publicMember($m);
        $balLog = Member::memberLogs((int) $m['id'], 'balance', 1, 5);
        $ptsLog = Member::memberLogs((int) $m['id'], 'points', 1, 5);
        $orders = Member::orders((int) $m['id'], 1, 3);
        $addr   = Member::addresses((int) $m['id']);
        return $this->render('center', [
            'title'    => '会员中心',
            'nav'      => 'center',
            'member'   => $pub,
            'raw'      => $m,
            'balLog'   => $balLog['list'],
            'ptsLog'   => $ptsLog['list'],
            'orders'   => $orders,
            'addresses'=> $addr,
        ]);
    }

    public function address()
    {
        $m = Member::currentMember();
        if (!$m) return redirect('/member/login');
        $list = Member::addresses((int) $m['id']);
        $edit = [];
        $id   = (int) $this->request->get('id', 0);
        if ($id > 0) {
            foreach ($list as $a) {
                if ((int) $a['id'] === $id) {
                    $edit = $a;
                    break;
                }
            }
        }
        return $this->render('address', [
            'title' => '收货地址', 'nav' => 'address',
            'member'=> Member::publicMember($m), 'addresses' => $list, 'editing' => $edit,
        ]);
    }

    public function addressSave()
    {
        $m = Member::currentMember();
        if (!$m) return redirect('/member/login');
        try {
            Member::saveAddress((int) $m['id'], [
                'id'        => (int) $this->request->post('id', 0),
                'receiver'  => (string) $this->request->post('receiver', ''),
                'mobile'    => (string) $this->request->post('mobile', ''),
                'province'  => (string) $this->request->post('province', ''),
                'city'      => (string) $this->request->post('city', ''),
                'district'  => (string) $this->request->post('district', ''),
                'detail'    => (string) $this->request->post('detail', ''),
                'postcode'  => (string) $this->request->post('postcode', ''),
                'is_default'=> (int) $this->request->post('is_default', 0),
            ]);
            $this->flash('地址已保存');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage());
        }
        return redirect('/member/address');
    }

    public function addressDelete()
    {
        $m = Member::currentMember();
        if (!$m) return redirect('/member/login');
        try {
            Member::deleteAddress((int) $m['id'], (int) $this->request->post('id', 0));
            $this->flash('地址已删除');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage());
        }
        return redirect('/member/address');
    }

    public function logs()
    {
        $m = Member::currentMember();
        if (!$m) return redirect('/member/login');
        $kind = (string) $this->request->get('kind', 'balance');
        if (!in_array($kind, ['balance', 'points'], true)) $kind = 'balance';
        $page = max(1, (int) $this->request->get('page', 1));
        $res  = Member::memberLogs((int) $m['id'], $kind, $page, 15);
        return $this->render('logs', [
            'title' => $kind === 'points' ? '积分明细' : '余额明细',
            'nav'   => 'logs', 'member' => Member::publicMember($m),
            'kind'  => $kind, 'res' => $res,
        ]);
    }

    public function profile()
    {
        $m = Member::currentMember();
        if (!$m) return redirect('/member/login');
        return $this->render('profile', [
            'title' => '会员资料', 'nav' => 'profile',
            'member'=> Member::publicMember($m), 'raw' => $m,
        ]);
    }

    public function profileSave()
    {
        $m = Member::currentMember();
        if (!$m) return redirect('/member/login');
        try {
            Member::updateProfile((int) $m['id'], [
                'real_name' => (string) $this->request->post('real_name', ''),
                'email'     => (string) $this->request->post('email', ''),
                'company'   => (string) $this->request->post('company', ''),
                'gender'    => (int) $this->request->post('gender', 0),
                'birthday'  => (string) $this->request->post('birthday', ''),
            ]);
            $this->flash('资料已保存');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage());
        }
        return redirect('/member/profile');
    }

    public function password()
    {
        $m = Member::currentMember();
        if (!$m) return redirect('/member/login');
        try {
            $new  = (string) $this->request->post('password', '');
            $new2 = (string) $this->request->post('password2', '');
            if ($new !== $new2) {
                throw new \InvalidArgumentException('两次输入的新密码不一致');
            }
            Member::changePassword((int) $m['id'], (string) $this->request->post('old_password', ''), $new, true);
            $this->flash('密码已修改');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage());
        }
        return redirect('/member/profile');
    }

    public function orders()
    {
        $m = Member::currentMember();
        if (!$m) return redirect('/member/login');
        $page = max(1, (int) $this->request->get('page', 1));
        $res  = Member::orders((int) $m['id'], $page, 10);
        return $this->render('orders', [
            'title' => '我的订单', 'nav' => 'orders',
            'member'=> Member::publicMember($m), 'orders' => $res,
        ]);
    }

    /* ==================== 渲染 / 提示 ==================== */

    private function render(string $tpl, array $data = []): string
    {
        $flash = session('member_flash');
        if ($flash !== null && $flash !== '') {
            session('member_flash', null);
        }
        $data['siteName'] = setting('site_name', '企业官网');
        $data['flash']    = (string) $flash;
        $data['nav']      = $data['nav'] ?? '';
        return View::fetch(dirname(__DIR__) . '/view/front/' . $tpl . '.php', $data);
    }

    private function flash(string $msg): void
    {
        session('member_flash', $msg);
    }
}
