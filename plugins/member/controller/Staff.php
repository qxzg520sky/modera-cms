<?php
declare(strict_types=1);

namespace plugins\member\controller;

use app\BaseController;
use app\service\View;
use plugins\member\service\Member;

/**
 * 业务员工作台：独立登录态
 * 所有查询强制 staff_id = 当前登录业务员，越权 id 直接拒绝。
 */
class Staff extends BaseController
{
    public function login()
    {
        if (Member::currentStaff()) {
            return redirect('/member/staff');
        }
        return $this->render('staff_login', ['title' => '业务员登录', 'login' => true]);
    }

    public function doLogin()
    {
        try {
            Member::staffLogin((string) $this->request->post('username', ''), (string) $this->request->post('password', ''));
            return redirect('/member/staff');
        } catch (\Throwable $e) {
            session('member_staff_flash', $e->getMessage());
            return redirect('/member/staff/login');
        }
    }

    public function logout()
    {
        Member::staffLogout();
        return redirect('/member/staff/login');
    }

    public function index()
    {
        $staff = Member::currentStaff();
        if (!$staff) {
            return redirect('/member/staff/login');
        }
        $kw   = trim((string) $this->request->get('keyword', ''));
        $page = max(1, (int) $this->request->get('page', 1));
        $res  = Member::listMembers(['keyword' => $kw], (int) $staff['id'], $page, 20);
        $stats = [
            'members' => Member::staffMemberCount((int) $staff['id']),
            'balance' => round((float) \think\facade\Db::name(Member::T_MEMBER)->where('staff_id', (int) $staff['id'])->sum('balance'), 2),
            'points'  => (int) \think\facade\Db::name(Member::T_MEMBER)->where('staff_id', (int) $staff['id'])->sum('points'),
            'today'   => (int) \think\facade\Db::name(Member::T_MEMBER)->where('staff_id', (int) $staff['id'])->where('created_at', '>=', strtotime('today'))->count(),
        ];
        return $this->render('staff_workbench', [
            'title' => '业务员工作台',
            'staff' => $staff,
            'res'   => $res,
            'kw'    => $kw,
            'stats' => $stats,
        ]);
    }

    public function member()
    {
        $staff = Member::currentStaff();
        if (!$staff) {
            return redirect('/member/staff/login');
        }
        $id = (int) $this->request->param('id', 0);
        $m  = Member::getMember($id);
        if (!$m || (int) $m['staff_id'] !== (int) $staff['id']) {
            session('member_staff_flash', '只能查看自己名下的会员');
            return redirect('/member/staff');
        }
        $kind = (string) $this->request->get('kind', 'balance');
        if (!in_array($kind, ['balance', 'points'], true)) $kind = 'balance';
        $page = max(1, (int) $this->request->get('page', 1));
        $logs = Member::memberLogs($id, $kind, $page, 12);
        return $this->render('staff_member', [
            'title' => '会员详情',
            'staff' => $staff,
            'member'=> Member::publicMember($m),
            'raw'   => $m,
            'kind'  => $kind,
            'res'   => $logs,
        ]);
    }

    public function remark()
    {
        $staff = Member::currentStaff();
        if (!$staff) {
            return redirect('/member/staff/login');
        }
        $id = (int) $this->request->post('id', 0);
        $m  = Member::getMember($id);
        if (!$m || (int) $m['staff_id'] !== (int) $staff['id']) {
            session('member_staff_flash', '只能维护自己名下的会员');
            return redirect('/member/staff');
        }
        try {
            Member::updateMember($id, ['remark' => (string) $this->request->post('remark', '')]);
            session('member_staff_flash', '跟进备注已保存');
        } catch (\Throwable $e) {
            session('member_staff_flash', $e->getMessage());
        }
        return redirect('/member/staff/member/' . $id);
    }

    private function render(string $tpl, array $data = []): string
    {
        $flash = session('member_staff_flash');
        if ($flash !== null && $flash !== '') {
            session('member_staff_flash', null);
        }
        $data['siteName'] = setting('site_name', '企业官网');
        $data['flash']    = (string) $flash;
        return View::fetch(dirname(__DIR__) . '/view/front/' . $tpl . '.php', $data);
    }
}
