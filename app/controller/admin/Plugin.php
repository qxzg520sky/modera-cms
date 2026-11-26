<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\service\PluginManager;
use think\facade\Db;

class Plugin extends Base
{
    public function index()
    {
        $list = PluginManager::scan();
        return $this->view('plugin/index', [
            'active'    => 'plugin',
            'pageTitle' => '插件扩展',
            'list'      => $list,
        ]);
    }

    public function toggle()
    {
        $name    = trim((string) $this->request->post('name', ''));
        $enabled = (int) $this->request->post('enabled', 0);
        if ($name === '' || !PluginManager::toggle($name, $enabled)) {
            return $this->back('/admin/plugin', '插件不存在');
        }
        return $this->back('/admin/plugin', $enabled ? '插件已启用' : '插件已停用');
    }

    public function uninstall()
    {
        $name = trim((string) $this->request->post('name', ''));
        if ($name === '' || !is_dir(root_path() . 'plugins/' . $name)) {
            return $this->back('/admin/plugin', '插件不存在');
        }

        [$ok, $logs] = PluginManager::uninstall($name);
        if (!$ok) {
            return $this->back('/admin/plugin', '卸载失败：' . implode('；', $logs));
        }
        $msg = '插件已卸载，插件目录已删除，可在应用市场重新下载安装：' . ($logs ? implode('；', array_slice($logs, 0, 3)) . (count($logs) > 3 ? ' 等 ' . count($logs) . ' 项' : '') : '无需要清理的数据');

        return $this->back('/admin/plugin', $msg);
    }

    public function config()
    {
        $name = trim((string) $this->request->param('name', ''));
        $meta = PluginManager::manifest($name);
        if (!$meta) {
            return $this->back('/admin/plugin', '插件不存在');
        }
        $row    = Db::name('plugin')->where('name', $name)->find();
        $config = [];
        if ($row && !empty($row['config'])) {
            $config = json_decode((string) $row['config'], true) ?: [];
        }
        return $this->view('plugin/config', [
            'active'    => 'plugin',
            'pageTitle' => '插件设置 - ' . ($meta['title'] ?? $name),
            'name'      => $name,
            'meta'      => $meta,
            'config'    => $config,
            'row'       => $row ?: [],
        ]);
    }

    public function save()
    {
        $name   = trim((string) $this->request->post('name', ''));
        $meta   = PluginManager::manifest($name);
        if (!$meta) {
            return $this->back('/admin/plugin', '插件不存在');
        }
        $fields = (array) ($meta['config'] ?? []);
        $data   = [];
        foreach ($fields as $field) {
            $key         = $field['name'] ?? '';
            if ($key === '') {
                continue;
            }
            $data[$key] = (string) $this->request->post($key, '');
        }
        Db::name('plugin')->where('name', $name)->update([
            'config' => json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);
        return $this->back('/admin/plugin', '插件配置已保存');
    }
}
