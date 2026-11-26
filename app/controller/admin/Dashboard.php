<?php
declare(strict_types=1);

namespace app\controller\admin;

use think\facade\Db;

class Dashboard extends Base
{
    public function index()
    {
        $stat = [
            'article'  => (int) Db::name('article')->count(),
            'category' => (int) Db::name('category')->count(),
            'page'     => (int) Db::name('page')->count(),
            'plugin'   => (int) Db::name('plugin')->where('enabled', 1)->count(),
        ];

        $recent = Db::name('article')->order('id', 'desc')->limit(8)->select()->toArray();
        $cats   = Db::name('category')->column('name', 'id');
        foreach ($recent as &$row) {
            $row['category_name'] = $cats[$row['category_id']] ?? '未分类';
        }
        unset($row);

        $mysql = '';
        try {
            $rows  = Db::query('select version() as v');
            $mysql = $rows[0]['v'] ?? '';
        } catch (\Throwable $e) {
        }

        $info = [
            'thinkphp' => \think\facade\App::version(),
            'php'      => PHP_VERSION,
            'mysql'    => $mysql,
            'server'   => $_SERVER['SERVER_SOFTWARE'] ?? 'Apache',
            'os'       => PHP_OS,
            'theme'    => \app\service\ThemeManager::current(),
            'root'     => root_path(),
        ];

        return $this->view('dashboard', [
            'active' => 'dashboard',
            'stat'   => $stat,
            'recent' => $recent,
            'info'   => $info,
        ]);
    }
}
