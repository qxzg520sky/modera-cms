<?php
declare(strict_types=1);

namespace plugins\stats\controller;

use plugins\stats\service\Stats;

/**
 * 访问统计后台：概览 / 设置 / 清理
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
        $data['active'] = $data['active'] ?? 'plugin-' . md5('/admin/plugin/stats');
        return \app\service\View::fetch(__DIR__ . '/../view/' . $tpl . '.php', $data);
    }

    public function index()
    {
        $days = (int) $this->request->get('days', 30);
        $data = Stats::dashboard($days);
        return $this->render('index', [
            'pageTitle' => '访问统计',
            'data'      => $data,
            'cfg'       => Stats::cfg(),
        ]);
    }

    public function save()
    {
        $retention = max(7, min(365, (int) $this->request->post('retention', 90)));
        $dnt = (string) $this->request->post('dnt', '') === '1' ? '1' : '0';
        Stats::saveCfg(['retention' => (string) $retention, 'dnt' => $dnt]);
        return $this->back('/admin/plugin/stats', '统计设置已保存：保留 ' . $retention . ' 天，'
            . ($dnt === '1' ? '尊重 Do Not Track' : '忽略 Do Not Track'));
    }

    public function clear()
    {
        Stats::clearAll();
        return $this->back('/admin/plugin/stats', '全部统计数据已清空（历史 PV/UV 记录一并删除）');
    }

    public function purge()
    {
        $n = Stats::purgeExpired();
        return $this->back('/admin/plugin/stats', '已清理过期数据：删除 ' . $n . ' 条聚合记录');
    }
}
