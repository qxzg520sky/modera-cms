<?php
declare(strict_types=1);

namespace app\controller;

use app\BaseController;
use app\service\Installer;
use app\service\View;

/**
 * 前台安装向导 /install
 * 文件刚上传、数据库还没装时，全站请求都会被 NeedInstall 中间件带到本页。
 */
class Install extends BaseController
{
    public function index()
    {
        if (Installer::isInstalled()) {
            return View::fetch(root_path() . 'app/view/install_done.php', [
                'installed' => true,
                'site'      => (string) (function_exists('setting') ? setting('site_name', '') : ''),
            ]);
        }

        $checks = Installer::checks();

        $db = [
            'host'   => (string) env('DB_HOST', '127.0.0.1'),
            'port'   => (string) env('DB_PORT', '3306'),
            'name'   => (string) env('DB_NAME', ''),
            'user'   => (string) env('DB_USER', 'root'),
            'pass'   => (string) env('DB_PASS', ''),
            'prefix' => (string) env('DB_PREFIX', 'tp_'),
        ];

        return View::fetch(root_path() . 'app/view/install.php', [
            'checks'    => $checks,
            'canInstall' => !Installer::checksFailed($checks),
            'db'        => $db,
            'error'     => '',
            'old'       => [],
        ]);
    }

    public function doInstall()
    {
        if (Installer::isInstalled()) {
            return redirect('/install');
        }

        $checks = Installer::checks();
        if (Installer::checksFailed($checks)) {
            return View::fetch(root_path() . 'app/view/install.php', [
                'checks'     => $checks,
                'canInstall' => false,
                'db'         => ['host' => '127.0.0.1', 'port' => '3306', 'name' => '', 'user' => 'root', 'pass' => '', 'prefix' => 'tp_'],
                'error'      => '环境检测未通过，请先解决红色项目再安装',
                'old'        => [],
            ]);
        }

        $db = [
            'host'   => trim((string) $this->request->post('db_host', '127.0.0.1')),
            'port'   => trim((string) $this->request->post('db_port', '3306')),
            'name'   => trim((string) $this->request->post('db_name', '')),
            'user'   => trim((string) $this->request->post('db_user', 'root')),
            'pass'   => (string) $this->request->post('db_pass', ''),
            'prefix' => trim((string) $this->request->post('db_prefix', 'tp_')),
        ];
        $siteName = trim((string) $this->request->post('site_name', '我的企业网站'));
        $slogan   = trim((string) $this->request->post('site_slogan', '专业 · 高效 · 值得信赖'));
        $username = trim((string) $this->request->post('username', ''));
        $password = (string) $this->request->post('password', '');
        $password2 = (string) $this->request->post('password2', '');
        $autoCreate = (bool) $this->request->post('auto_create', 1);

        $old = [
            'site_name'   => $siteName,
            'site_slogan' => $slogan,
            'username'    => $username,
        ];

        $fail = function (string $msg, array $db, array $old) use ($checks) {
            return View::fetch(root_path() . 'app/view/install.php', [
                'checks'     => $checks,
                'canInstall' => true,
                'db'         => $db,
                'error'      => $msg,
                'old'        => $old,
            ]);
        };

        if (!preg_match('/^[A-Za-z0-9_\-]{3,20}$/', $db['prefix'] !== '' ? $db['prefix'] : 'tp_')) {
            return $fail('表前缀只能包含字母、数字、下划线和短横线', $db, $old);
        }
        if ($db['name'] === '') {
            return $fail('请填写数据库名', $db, $old);
        }
        if ($siteName === '') {
            return $fail('请填写站点名称', $db, $old);
        }
        if (!preg_match('/^[A-Za-z0-9_\-]{3,20}$/', $username)) {
            return $fail('管理员账号 3-20 位，只允许字母、数字、下划线和短横线', $db, $old);
        }
        if (strlen($password) < 6) {
            return $fail('管理员密码至少 6 位', $db, $old);
        }
        if ($password !== $password2) {
            return $fail('两次输入的密码不一致', $db, $old);
        }

        try {
            // 1. 建库/连通性
            Installer::prepareDatabase($db, $autoCreate);

            // 2. 写 .env（供后续请求使用）
            Installer::writeEnv($db);

            // 3. 当前请求改用新库连接
            Installer::useDatabase($db);

            // 4. 建表 + 最小数据（默认模板 + 演示插件）
            Installer::install([
                'username'    => $username,
                'password'    => $password,
                'nickname'    => $username,
                'site_name'   => $siteName,
                'site_slogan' => $slogan,
            ]);
        } catch (\Throwable $e) {
            Installer::clearLock();
            return $fail('安装失败：' . $e->getMessage(), $db, $old);
        }

        session('install_ok', [
            'username' => $username,
            'site'     => $siteName,
        ]);
        return redirect('/install');
    }

    public function done()
    {
        return redirect('/install');
    }
}
