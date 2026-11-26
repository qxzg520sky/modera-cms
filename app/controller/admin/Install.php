<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;

/**
 * 兼容旧入口：/admin/install 统一跳到前台安装向导 /install
 */
class Install extends BaseController
{
    public function index()
    {
        return redirect('/install');
    }

    public function doInstall()
    {
        return redirect('/install');
    }
}
