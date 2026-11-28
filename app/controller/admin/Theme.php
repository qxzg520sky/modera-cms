<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\service\ThemeManager;

class Theme extends Base
{
    public function index()
    {
        $list = ThemeManager::scan();
        return $this->view('theme/index', [
            'active'    => 'theme',
            'pageTitle' => '模板主题',
            'list'      => $list,
            'current'   => ThemeManager::current(),
        ]);
    }

    public function enable()
    {
        $name = trim((string) $this->request->post('name', ''));
        if ($name === '' || !ThemeManager::enable($name)) {
            return $this->back('/admin/theme', '模板不存在或启用失败');
        }
        return $this->back('/admin/theme', '已切换到模板：' . $name);
    }

    public function uninstall()
    {
        $name = trim((string) $this->request->post('name', ''));
        [$ok, $msg] = ThemeManager::uninstall($name);
        return $this->back('/admin/theme', $ok ? $msg : '卸载失败：' . $msg);
    }
}
