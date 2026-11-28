<?php
declare(strict_types=1);

namespace plugins\market\controller;

use app\service\ModuleMarket;
use app\service\PluginManager;
use app\service\View;
use plugins\market\service\Market;
use think\facade\Db;

class Admin
{
    public function index()
    {
        $meta   = PluginManager::manifest('market');
        $row    = Db::name('plugin')->where('name', 'market')->find() ?: [];
        $config = $this->config($meta, $row);
        $catalog = Market::catalog($config);

        $items = [];
        foreach ($catalog['items'] as $item) {
            $installed = is_dir(ModuleMarket::targetDir((string) $item['type'], (string) $item['name']));
            $item['installed'] = $installed;
            $item['enabled']   = $installed ? ModuleMarket::isEnabled((string) $item['type'], (string) $item['name']) : false;
            $item['core']      = in_array((string) $item['name'], ModuleMarket::core()[$item['type']] ?? [], true);
            $items[] = $item;
        }

        $msg   = (string) ($_GET['msg'] ?? '');
        $error = (string) ($_GET['error'] ?? '');
        if ($catalog['error'] !== '') {
            $error = $catalog['error'];
        }

        return View::fetch(__DIR__ . '/../view/admin.php', [
            'pageTitle' => '应用市场',
            'active'    => 'plugin-' . md5('/admin/plugin/market'),
            'meta'      => $meta,
            'row'       => $row,
            'config'    => $config,
            'catalog'   => $catalog,
            'items'     => $items,
            'msg'       => $msg,
            'error'     => $error,
        ]);
    }

    public function install()
    {
        $type   = (string) input('post.type', '');
        $name   = (string) input('post.name', '');
        $enable = (int) input('post.enable', 0) === 1;

        if (!in_array($type, ['theme', 'plugin'], true) || $name === '') {
            return $this->back('模块参数不完整', true);
        }

        $meta    = PluginManager::manifest('market');
        $row     = Db::name('plugin')->where('name', 'market')->find() ?: [];
        $config  = $this->config($meta, $row);
        $catalog = Market::catalog($config);

        $item = null;
        foreach ($catalog['items'] as $candidate) {
            if ($candidate['type'] === $type && $candidate['name'] === $name) {
                $item = $candidate;
                break;
            }
        }
        if (!$item) {
            return $this->back('应用市场目录里没有该模块：' . $name, true);
        }

        [$ok, $message] = Market::install($item, $enable);
        return $this->back($message, !$ok);
    }

    public function refresh()
    {
        return $this->back('应用市场目录已刷新', false);
    }

    private function config(array $meta, array $row): array
    {
        $stored = [];
        if (!empty($row['config'])) {
            $stored = json_decode((string) $row['config'], true) ?: [];
        }
        $config = [];
        foreach ((array) ($meta['config'] ?? []) as $field) {
            $key = (string) ($field['name'] ?? '');
            if ($key === '') {
                continue;
            }
            $config[$key] = (string) ($stored[$key] ?? ($field['default'] ?? ''));
        }
        return $config;
    }

    private function back(string $message, bool $isError = false): void
    {
        $query = http_build_query($isError ? ['error' => $message] : ['msg' => $message]);
        header('Location: /admin/plugin/market?' . $query);
        exit;
    }
}
