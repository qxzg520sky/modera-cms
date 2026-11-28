<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\service\Updater;

class Update extends Base
{
    public function index()
    {
        $u = new Updater();
        return $this->view('update/index', [
            'active'     => 'update',
            'pageTitle'  => '在线更新',
            'current'    => $u->current(),
            'settings'   => $u->settings(),
            'lastCheck'  => $u->lastCheck(),
            'lastResult' => $u->lastResult(),
        ]);
    }

    public function check()
    {
        $u = new Updater();
        try {
            $saved = $u->saveSettings($this->options());
            $remote = $u->remote($saved);
            $rev = (string) ($remote['commit']['sha'] ?? $remote['revision'] ?? '');
            $msg = '检查完成：远端 ' . ($rev !== '' ? substr($rev, 0, 10) : '未知提交');
            if (!empty($remote['version']['version'])) {
                $msg .= '，版本 ' . $remote['version']['version'];
            }
            return $this->back('/admin/update', $msg);
        } catch (\Throwable $e) {
            return $this->back('/admin/update', '检查失败：' . $e->getMessage());
        }
    }

    public function run()
    {
        @set_time_limit(600);
        @ignore_user_abort(true);
        $u = new Updater();
        $dry = (int) $this->request->post('dry_run', 0) === 1;
        try {
            $r = $u->apply($this->options(), $dry);
            $msg = $dry
                ? '试运行完成：将更新 ' . (int) $r['changed'] . ' 个文件，未写入'
                : '更新完成：写入 ' . (int) $r['changed'] . ' 个文件，已自动备份';
            return $this->back('/admin/update', $msg);
        } catch (\Throwable $e) {
            return $this->back('/admin/update', '更新失败：' . $e->getMessage());
        }
    }

    public function rollback()
    {
        @set_time_limit(600);
        $u = new Updater();
        try {
            $r = $u->rollback();
            return $this->back('/admin/update', '回滚完成，程序文件已恢复到上次更新前');
        } catch (\Throwable $e) {
            return $this->back('/admin/update', '回滚失败：' . $e->getMessage());
        }
    }

    private function options(): array
    {
        $mirror = strtolower((string) $this->request->post('mirror', 'github'));
        if (!in_array($mirror, ['github', 'gitee'], true)) {
            $mirror = 'github';
        }
        return [
            'mirror' => $mirror,
            'branch' => trim((string) $this->request->post('branch', 'main')),
        ];
    }
}
