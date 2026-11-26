<?php
declare(strict_types=1);

namespace plugins\member\controller;

use plugins\member\service\Excel;
use plugins\member\service\Member;
use think\facade\Db;

/**
 * 企业会员管理后台
 * 管理员拥有全部数据权限；业务员工作台在 Staff 控制器，会员前台在 Front。
 */
class Admin extends \app\controller\admin\Base
{
    protected function render(string $tpl, array $data = []): string
    {
        $flash = session('flash_msg');
        if ($flash !== null && $flash !== '') {
            session('flash_msg', null);
        }
        $data['admin']  = $this->admin;
        $data['flash']  = (string) $flash;
        $data['active'] = $data['active'] ?? '';
        return \app\service\View::fetch(dirname(__DIR__) . '/view/admin/' . $tpl . '.php', $data);
    }

    private function active(string $url): string
    {
        return 'plugin-' . md5($url);
    }

    private function flash(string $msg): void
    {
        session('flash_msg', $msg);
    }

    /* ==================== 总览 ==================== */

    public function dashboard()
    {
        return $this->render('dashboard', [
            'active'    => $this->active('/admin/plugin/member'),
            'pageTitle' => '会员总览',
            'stats'     => Member::stats(),
            'top'       => Member::topMembers(6),
            'logs'      => Member::recentBalanceLogs(8),
            'cfg'       => Member::cfg(),
        ]);
    }

    /* ==================== 会员 ==================== */

    public function members()
    {
        $f = [
            'keyword'  => trim((string) $this->request->get('keyword', '')),
            'level_id' => (int) $this->request->get('level_id', 0),
            'staff_id' => $this->request->get('staff_id', ''),
            'status'   => $this->request->get('status', ''),
        ];
        if ($this->request->get('assigned', '') === '1') $f['assigned'] = 1;
        if ($this->request->get('assigned', '') === '0') $f['unassigned'] = 1;
        $page = max(1, (int) $this->request->get('page', 1));
        $res  = Member::listMembers($f, 0, $page, max(10, (int) Member::cfg()['page_size']));
        return $this->render('members', [
            'active'    => $this->active('/admin/plugin/member/members'),
            'pageTitle' => '会员列表',
            'res'       => $res,
            'f'         => $f,
            'levels'    => Member::levels(),
            'staffs'    => Member::staffList(),
        ]);
    }

    public function memberEdit()
    {
        $id = (int) $this->request->param('id', 0);
        $m  = $id > 0 ? Member::getMember($id) : [];
        return $this->render('member_edit', [
            'active'    => $this->active('/admin/plugin/member/members'),
            'pageTitle' => $id ? '编辑会员' : '新增会员',
            'm'         => $m,
            'levels'    => Member::levels(),
            'staffs'    => Member::staffList(),
            'isNew'     => $id <= 0,
        ]);
    }

    public function memberView()
    {
        $id = (int) $this->request->param('id', 0);
        $m  = Member::getMember($id);
        if (!$m) {
            return $this->back('/admin/plugin/member/members', '会员不存在');
        }
        return $this->render('member_view', [
            'active'    => $this->active('/admin/plugin/member/members'),
            'pageTitle' => '会员详情',
            'm'         => Member::publicMember($m),
            'raw'       => $m,
            'levels'    => Member::levels(),
            'staffs'    => Member::staffList(),
            'balLog'    => Member::memberLogs($id, 'balance', 1, 15),
            'ptsLog'    => Member::memberLogs($id, 'points', 1, 15),
            'addresses' => Member::addresses($id),
            'balTypes'  => Member::BALANCE_TYPES,
            'ptsTypes'  => Member::POINTS_TYPES,
        ]);
    }

    public function memberSave()
    {
        $id = (int) $this->request->post('id', 0);
        $data = [
            'username'    => (string) $this->request->post('username', ''),
            'mobile'      => (string) $this->request->post('mobile', ''),
            'email'       => (string) $this->request->post('email', ''),
            'real_name'   => (string) $this->request->post('real_name', ''),
            'company'     => (string) $this->request->post('company', ''),
            'gender'      => (int) $this->request->post('gender', 0),
            'birthday'    => (string) $this->request->post('birthday', ''),
            'level_id'    => (int) $this->request->post('level_id', 0),
            'staff_id'    => (int) $this->request->post('staff_id', 0),
            'status'      => (int) $this->request->post('status', 1),
            'remark'      => (string) $this->request->post('remark', ''),
            'total_spent' => (float) $this->request->post('total_spent', 0),
        ];
        $pwd = (string) $this->request->post('password', '');
        if ($pwd !== '') $data['password'] = $pwd;

        try {
            if ($id > 0) {
                Member::updateMember($id, $data);
                $this->flash('会员资料已更新');
                return redirect('/admin/plugin/member/member/view/' . $id);
            }
            $data['balance'] = (float) $this->request->post('balance', 0);
            $data['points']  = (int) $this->request->post('points', 0);
            $data['source']  = 'admin';
            $ret = Member::createMember($data, 'admin', (int) ($this->admin['id'] ?? 0));
            $this->flash($ret[1]);
            return redirect('/admin/plugin/member/member/view/' . (int) $ret[2]['id']);
        } catch (\Throwable $e) {
            $this->flash($e->getMessage());
            return redirect('/admin/plugin/member/member/edit' . ($id > 0 ? '/' . $id : ''));
        }
    }

    public function memberAsset()
    {
        $id   = (int) $this->request->post('id', 0);
        $kind = (string) $this->request->post('kind', 'balance');
        $dir  = (string) $this->request->post('direction', 'in');
        $type = (string) $this->request->post('type', 'adjust');
        $biz  = trim((string) $this->request->post('biz_no', ''));
        $remark = (string) $this->request->post('remark', '');
        try {
            if ($kind === 'points') {
                $n = abs((int) $this->request->post('amount', 0));
                if ($n === 0) throw new \InvalidArgumentException('积分不能为 0');
                Member::applyPoints($id, $dir === 'out' ? -$n : $n, $type, [
                    'operator_type' => 'admin', 'operator_id' => (int) ($this->admin['id'] ?? 0),
                    'biz_type' => 'admin', 'biz_no' => $biz, 'remark' => $remark,
                ]);
                $this->flash('积分已' . ($dir === 'out' ? '扣减' : '增加') . ' ' . $n);
            } else {
                $n = abs(round((float) $this->request->post('amount', 0), 2));
                if ($n <= 0) throw new \InvalidArgumentException('金额必须大于 0');
                Member::applyBalance($id, $dir === 'out' ? -$n : $n, $type, [
                    'operator_type' => 'admin', 'operator_id' => (int) ($this->admin['id'] ?? 0),
                    'biz_type' => 'admin', 'biz_no' => $biz, 'remark' => $remark,
                ]);
                $this->flash('余额已' . ($dir === 'out' ? '扣减' : '增加') . ' ' . number_format($n, 2));
            }
        } catch (\Throwable $e) {
            $this->flash($e->getMessage());
        }
        return redirect('/admin/plugin/member/member/view/' . $id);
    }

    public function memberUpgrade()
    {
        $id = (int) $this->request->post('id', 0);
        try {
            $r = Member::maybeUpgrade($id, true);
            $this->flash($r['changed'] ? '已按累计消费升级为：' . ($r['level']['name'] ?? '') : '等级无需调整');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage());
        }
        return redirect('/admin/plugin/member/member/view/' . $id);
    }

    /* ==================== 等级 ==================== */

    public function levels()
    {
        return $this->render('levels', [
            'active'    => $this->active('/admin/plugin/member/levels'),
            'pageTitle' => '会员等级',
            'levels'    => Member::levels(),
        ]);
    }

    public function levelSave()
    {
        $id   = (int) $this->request->post('id', 0);
        $name = mb_substr(trim((string) $this->request->post('name', '')), 0, 50);
        $code = strtolower(trim((string) $this->request->post('code', '')));
        $min  = max(0, round((float) $this->request->post('min_spent', 0), 2));
        $disc = round((float) $this->request->post('discount', 1), 2);
        $rate = max(0, round((float) $this->request->post('points_rate', 0), 2));
        $def  = (int) $this->request->post('is_default', 0) === 1 ? 1 : 0;
        $sort = (int) $this->request->post('sort', 0);
        $st   = (int) $this->request->post('status', 1) === 1 ? 1 : 0;
        $desc = mb_substr(trim((string) $this->request->post('description', '')), 0, 255);

        try {
            if ($name === '') throw new \InvalidArgumentException('等级名称不能为空');
            if (!preg_match('/^[a-z0-9_\-]{2,30}$/', $code)) throw new \InvalidArgumentException('等级标识需为 2-30 位小写字母、数字、下划线或短横线');
            if ($disc <= 0 || $disc > 1) throw new \InvalidArgumentException('折扣需在 0.01-1.00 之间');
            if (Db::name(Member::T_LEVEL)->where('code', $code)->where('id', '<>', $id)->find()) {
                throw new \InvalidArgumentException('等级标识已存在');
            }
            $now = time();
            $fields = [
                'name' => $name, 'code' => $code, 'min_spent' => $min, 'discount' => $disc,
                'points_rate' => $rate, 'is_default' => $def, 'sort' => $sort, 'status' => $st,
                'description' => $desc, 'updated_at' => $now,
            ];
            if ($def === 1) {
                Db::name(Member::T_LEVEL)->update(['is_default' => 0]);
            }
            if ($id > 0) {
                Db::name(Member::T_LEVEL)->where('id', $id)->update($fields);
                $this->flash('等级已更新');
            } else {
                if ((int) Db::name(Member::T_LEVEL)->count() === 0) $fields['is_default'] = 1;
                $fields['created_at'] = $now;
                Db::name(Member::T_LEVEL)->insert($fields);
                $this->flash('等级已添加');
            }
        } catch (\Throwable $e) {
            $this->flash($e->getMessage());
        }
        return redirect('/admin/plugin/member/levels');
    }

    public function levelDelete()
    {
        $id = (int) $this->request->post('id', 0);
        try {
            $lv = Member::level($id);
            if (!$lv) throw new \InvalidArgumentException('等级不存在');
            if ((int) $lv['is_default'] === 1) throw new \InvalidArgumentException('默认等级不能删除');
            if ((int) Db::name(Member::T_MEMBER)->where('level_id', $id)->count() > 0) {
                throw new \InvalidArgumentException('还有会员在用这个等级，先给会员换等级再删');
            }
            Db::name(Member::T_LEVEL)->where('id', $id)->delete();
            $this->flash('等级已删除');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage());
        }
        return redirect('/admin/plugin/member/levels');
    }

    /* ==================== 业务员 ==================== */

    public function staff()
    {
        return $this->render('staff', [
            'active'    => $this->active('/admin/plugin/member/staff'),
            'pageTitle' => '业务员',
            'staffs'    => Member::staffList(),
        ]);
    }

    public function staffEdit()
    {
        $id = (int) $this->request->param('id', 0);
        $s  = [];
        if ($id > 0) {
            $s = Db::name(Member::T_STAFF)->where('id', $id)->find() ?: [];
        }
        return $this->render('staff_edit', [
            'active'    => $this->active('/admin/plugin/member/staff'),
            'pageTitle' => $id ? '编辑业务员' : '新增业务员',
            's'         => $s,
            'isNew'     => $id <= 0,
        ]);
    }

    public function staffSave()
    {
        $id = (int) $this->request->post('id', 0);
        try {
            $ret = Member::staffSave([
                'username'  => (string) $this->request->post('username', ''),
                'real_name' => (string) $this->request->post('real_name', ''),
                'mobile'    => (string) $this->request->post('mobile', ''),
                'password'  => (string) $this->request->post('password', ''),
                'status'    => (int) $this->request->post('status', 1),
                'remark'    => (string) $this->request->post('remark', ''),
            ], $id);
            $this->flash($ret[1]);
        } catch (\Throwable $e) {
            $this->flash($e->getMessage());
        }
        return redirect('/admin/plugin/member/staff');
    }

    public function staffDelete()
    {
        try {
            $ret = Member::staffDelete((int) $this->request->post('id', 0));
            $this->flash($ret[1]);
        } catch (\Throwable $e) {
            $this->flash($e->getMessage());
        }
        return redirect('/admin/plugin/member/staff');
    }

    /* ==================== 日志 ==================== */

    public function logs()
    {
        $kind = (string) $this->request->get('kind', 'balance');
        if (!in_array($kind, ['balance', 'points'], true)) $kind = 'balance';
        $f = [
            'keyword' => trim((string) $this->request->get('keyword', '')),
            'type'    => (string) $this->request->get('type', ''),
        ];
        $page = max(1, (int) $this->request->get('page', 1));
        $res  = Member::adminLogs($kind, $f, $page, 30);
        return $this->render('logs', [
            'active'    => $this->active('/admin/plugin/member/logs'),
            'pageTitle' => $kind === 'points' ? '积分日志' : '余额日志',
            'kind'      => $kind,
            'res'       => $res,
            'f'         => $f,
            'types'     => $kind === 'points' ? Member::POINTS_TYPES : Member::BALANCE_TYPES,
        ]);
    }

    /* ==================== 导入导出 ==================== */

    public function importPage()
    {
        $import = session('member_import_result');
        if ($import !== null) session('member_import_result', null);
        return $this->render('import', [
            'active'    => $this->active('/admin/plugin/member/import'),
            'pageTitle' => '会员导入导出',
            'import'    => $import,
        ]);
    }

    public function doImport()
    {
        $file = $this->request->file('file');
        if (!$file) {
            $this->flash('请选择要导入的 Excel / CSV 文件');
            return redirect('/admin/plugin/member/import');
        }
        $ext = strtolower((string) pathinfo((string) $file->getOriginalName(), PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'csv'], true)) {
            $this->flash('只支持 .xlsx 或 .csv');
            return redirect('/admin/plugin/member/import');
        }
        try {
            $rows = Excel::read($file->getPathname());
            $map  = [
                'mobile' => '手机号', 'real_name' => '姓名', 'username' => '用户名', 'password' => '密码',
                'email' => '邮箱', 'company' => '公司', 'level' => '等级', 'staff' => '业务员',
                'balance' => '余额', 'points' => '积分', 'total_spent' => '累计消费', 'remark' => '备注',
            ];
            $data = Excel::rowsToAssoc($rows, $map);
            if (!$data) {
                throw new \InvalidArgumentException('没有读到数据，请确认第一行是表头');
            }
            if (count($data) > 500) {
                throw new \InvalidArgumentException('单次最多导入 500 行');
            }
            $res = Member::importRows($data, (int) ($this->admin['id'] ?? 0));
            session('member_import_result', $res);
            $this->flash('导入完成：新增 ' . $res['created'] . '，更新 ' . $res['updated'] . '，跳过 ' . $res['skipped']);
        } catch (\Throwable $e) {
            $this->flash('导入失败：' . $e->getMessage());
        }
        return redirect('/admin/plugin/member/import');
    }

    public function export()
    {
        $path = Excel::export('members', Member::exportHeaders(), Member::exportRows());
        $body = (string) file_get_contents($path);
        @unlink($path);
        return response($body, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="members-' . date('Ymd') . '.xlsx"',
            'Cache-Control'       => 'no-store',
        ]);
    }

    public function template()
    {
        $rows = [
            ['13800138000', '张三', 'zhangsan', '', 'zhangsan@example.com', '某某公司', '普通会员', '', '0', '0', '0', '示例行，导入前可删除'],
        ];
        $path = Excel::export('member-import-template', Member::importHeaders(), $rows);
        $body = (string) file_get_contents($path);
        @unlink($path);
        return response($body, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="member-import-template.xlsx"',
            'Cache-Control'       => 'no-store',
        ]);
    }

    /* ==================== 设置 ==================== */

    public function settings()
    {
        return $this->render('settings', [
            'active'    => $this->active('/admin/plugin/member/settings'),
            'pageTitle' => '会员设置',
            'cfg'       => Member::cfg(),
            'stats'     => Member::stats(),
        ]);
    }

    public function settingsSave()
    {
        $in = [
            'enabled'          => (int) $this->request->post('enabled', 0) === 1 ? '1' : '0',
            'allow_register'   => (int) $this->request->post('allow_register', 0) === 1 ? '1' : '0',
            'auto_upgrade'     => (int) $this->request->post('auto_upgrade', 0) === 1 ? '1' : '0',
            'footer_link'      => (int) $this->request->post('footer_link', 0) === 1 ? '1' : '0',
            'register_points'  => (string) max(0, (int) $this->request->post('register_points', 0)),
            'page_size'        => (string) max(10, min(100, (int) $this->request->post('page_size', 20))),
            'api_appid'        => mb_substr(trim((string) $this->request->post('api_appid', '')), 0, 50),
            'api_ip_whitelist' => mb_substr(trim((string) $this->request->post('api_ip_whitelist', '')), 0, 500),
            'points_money_rate'=> (string) max(0, (float) $this->request->post('points_money_rate', 0.01)),
            'default_password' => mb_substr(trim((string) $this->request->post('default_password', '')), 0, 50),
        ];
        $secret = trim((string) $this->request->post('api_secret', ''));
        if ($secret !== '') {
            $in['api_secret'] = $secret;
        }
        if ($in['api_appid'] === '') {
            $this->flash('API AppID 不能为空');
            return redirect('/admin/plugin/member/settings');
        }
        if ($in['default_password'] === '' || strlen($in['default_password']) < 6) {
            $this->flash('默认密码至少 6 位');
            return redirect('/admin/plugin/member/settings');
        }
        Member::saveCfg($in);
        $this->flash('会员设置已保存');
        return redirect('/admin/plugin/member/settings');
    }
}
