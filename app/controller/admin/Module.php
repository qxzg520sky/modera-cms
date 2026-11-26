<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\service\ModuleMarket;
use app\service\PluginManager;
use app\service\ThemeManager;

/**
 * 模块中心：主题 / 插件按需安装（模块未安装时不启用、不出现在前台）
 */
class Module extends Base
{
    public function index()
    {
        $themes  = ThemeManager::scan();
        $plugins = PluginManager::scan();
        $current = ThemeManager::current();

        $records = [];
        foreach ($themes as $t) {
            $records[] = [
                'type'        => 'theme',
                'name'        => $t['name'],
                'title'       => $t['title'],
                'version'     => $t['version'],
                'author'      => $t['author'],
                'description' => $t['description'],
                'enabled'     => (string) $t['name'] === $current,
                'core'        => in_array($t['name'], ModuleMarket::core()['theme'], true),
            ];
        }
        foreach ($plugins as $p) {
            $records[] = [
                'type'        => 'plugin',
                'name'        => $p['name'],
                'title'       => $p['title'],
                'version'     => $p['version'],
                'author'      => $p['author'],
                'description' => $p['description'],
                'enabled'     => (int) $p['enabled'] === 1,
                'core'        => in_array($p['name'], ModuleMarket::core()['plugin'], true),
            ];
        }
        usort($records, function ($a, $b) {
            return [$a['type'], $a['name']] <=> [$b['type'], $b['name']];
        });

        return $this->view('module/index', [
            'active'    => 'module',
            'pageTitle' => '模块中心',
            'records'   => $records,
            'packages'  => ModuleMarket::packages(),
            'storeDir'  => 'modules/store',
        ]);
    }

    public function install()
    {
        $file  = basename((string) $this->request->post('file', ''));
        $enable = (int) $this->request->post('enable', 0) === 1;
        if ($file === '' || !preg_match('/^[A-Za-z0-9_\-\.]+\.zip$/', $file)) {
            return $this->back('/admin/module', '模块包文件名不合法');
        }
        $path = ModuleMarket::storeDir() . DIRECTORY_SEPARATOR . $file;
        if (!is_file($path)) {
            return $this->back('/admin/module', '模块包不存在：' . $file);
        }
        [$ok, $msg] = ModuleMarket::install($path, $enable);
        return $this->back('/admin/module', ($ok ? '' : '安装失败：') . $msg);
    }

    public function upload()
    {
        [$ok, $msg, $file] = ModuleMarket::saveUpload($this->request->file('package'));
        if ($ok && $file !== '') {
            // 上传后直接安装（启用交给用户在插件/模板列表操作）
            $path = ModuleMarket::storeDir() . DIRECTORY_SEPARATOR . $file;
            if (is_file($path)) {
                [$ok2, $msg2] = ModuleMarket::install($path, false);
                return $this->back('/admin/module', $msg . '；' . ($ok2 ? $msg2 : '安装失败：' . $msg2));
            }
        }
        return $this->back('/admin/module', $msg);
    }

    public function remove()
    {
        $file = (string) $this->request->post('file', '');
        [$ok, $msg] = ModuleMarket::removePackage($file);
        return $this->back('/admin/module', ($ok ? '' : '删除失败：') . $msg);
    }
}
