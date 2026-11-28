<?php
declare(strict_types=1);

namespace plugins\dbbackup\controller;

use plugins\dbbackup\service\Backup;

/**
 * 数据库备份后台：备份 / 还原 / 下载 / 上传 / 定时设置
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
        return \app\service\View::fetch(__DIR__ . '/../view/' . $tpl . '.php', $data);
    }

    public function index()
    {
        // 后台打开页面时也顺带检查一次“到点没”，避免站点没访客时永远不备份
        Backup::maybeAutoRun();

        return $this->render('index', [
            'active'    => 'plugin-' . md5('/admin/plugin/dbbackup'),
            'pageTitle' => '数据库备份',
            'files'     => Backup::files(),
            'cfg'       => Backup::cfg(),
            'info'      => Backup::dbInfo(),
            'state'     => Backup::readState(),
            'dir'       => Backup::dir(),
        ]);
    }

    /** 立即备份 */
    public function backupNow()
    {
        @set_time_limit(0);
        try {
            $r   = Backup::dump('manual');
            Backup::prune();
            $msg = '备份完成：' . $r['file'] . '（' . Backup::humanSize((int) $r['size'])
                . '，' . (int) $r['tables'] . ' 张表，耗时 ' . $r['seconds'] . ' 秒）';
        } catch (\Throwable $e) {
            $msg = '备份失败：' . $e->getMessage();
        }
        return $this->back('/admin/plugin/dbbackup', $msg);
    }

    /** 按已有文件还原（会先自动快照当前库） */
    public function restore()
    {
        @set_time_limit(0);
        $file = (string) $this->request->post('file', '');
        try {
            $r = Backup::restore($file);
            if ($r['errors']) {
                $msg = '还原完成，执行 ' . $r['executed'] . ' 条语句，其中 ' . count($r['errors'])
                    . ' 条报错：' . implode('；', $r['errors'])
                    . '（还原前快照：' . $r['pre'] . '）';
            } else {
                $msg = '还原成功：执行 ' . $r['executed'] . ' 条语句（还原前已自动快照：' . $r['pre'] . '）';
            }
        } catch (\Throwable $e) {
            $msg = '还原失败：' . $e->getMessage();
        }
        return $this->back('/admin/plugin/dbbackup', $msg);
    }

    /** 上传 .sql 存进备份目录，之后可在列表里点还原 */
    public function upload()
    {
        $file = $this->request->file('sql_file');
        if (!$file) {
            return $this->back('/admin/plugin/dbbackup', '没有收到文件，请选择一个 .sql 备份文件');
        }
        $name = (string) $file->getOriginalName();
        if (!preg_match('/\.sql$/i', $name)) {
            return $this->back('/admin/plugin/dbbackup', '只接受 .sql 文件');
        }
        if ((int) $file->getSize() > 64 * 1024 * 1024) {
            return $this->back('/admin/plugin/dbbackup', '文件超过 64MB，请用 phpMyAdmin 之类工具导入');
        }
        try {
            $saved = Backup::saveUpload((string) $file->getPathname(), $name);
            $msg   = '上传成功：' . $saved . '，在下面的列表里点「还原」即可使用';
        } catch (\Throwable $e) {
            $msg = '上传失败：' . $e->getMessage();
        }
        return $this->back('/admin/plugin/dbbackup', $msg);
    }

    /** 删除备份文件 */
    public function remove()
    {
        $file = (string) $this->request->post('file', '');
        $ok   = Backup::remove($file);
        return $this->back('/admin/plugin/dbbackup', $ok ? '已删除：' . basename($file) : '删除失败，文件可能不存在');
    }

    /** 下载备份 */
    public function download()
    {
        $file = (string) $this->request->get('file', '');
        $path = Backup::path($file);
        if ($path === '') {
            return $this->back('/admin/plugin/dbbackup', '备份文件不存在');
        }
        return response((string) file_get_contents($path))->header([
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . basename($path) . '"',
            'Content-Length'      => (string) filesize($path),
            'Cache-Control'       => 'no-store',
        ]);
    }

    /** 保存定时备份设置 */
    public function settingSave()
    {
        $auto     = (string) $this->request->post('auto', '0') === '1' ? '1' : '0';
        $interval = (int) $this->request->post('interval', 24);
        $keep     = (int) $this->request->post('keep', 7);
        $prefix   = (string) $this->request->post('prefix', 'db');

        $interval = max(1, min(720, $interval));
        $keep     = max(1, min(60, $keep));
        $prefix   = preg_replace('/[^A-Za-z0-9_\-]/', '', $prefix) ?: 'db';

        Backup::saveCfg([
            'auto'     => $auto,
            'interval' => $interval,
            'keep'     => $keep,
            'prefix'   => $prefix,
        ]);

        return $this->back('/admin/plugin/dbbackup', '设置已保存：' . ($auto === '1' ? '自动备份每 ' . $interval . ' 小时一次' : '自动备份已关闭'));
    }
}
