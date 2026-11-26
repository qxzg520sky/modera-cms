<?php
declare(strict_types=1);

namespace plugins\example\controller;

use app\service\PluginManager;
use app\service\View;
use think\facade\Db;

class Admin
{
    public function index()
    {
        $row = Db::name('plugin')->where('name', 'example')->find() ?: [];
        return View::fetch(__DIR__ . '/../view/admin.php', [
            'meta'   => PluginManager::manifest('example'),
            'row'    => $row,
            'config' => $row && !empty($row['config']) ? (json_decode((string) $row['config'], true) ?: []) : [],
        ]);
    }
}
