<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use app\model\Admin;
use app\service\View;

class Login extends BaseController
{
    public function index()
    {
        if ($this->request->session('admin_id')) {
            return redirect('/admin');
        }
        return View::admin('login', [
            'siteName' => setting('site_name', '企业管理系统'),
            'error'    => '',
        ]);
    }

    public function doLogin()
    {
        $username = trim((string) $this->request->post('username', ''));
        $password = (string) $this->request->post('password', '');

        if ($username === '' || $password === '') {
            return View::admin('login', ['siteName' => setting('site_name', '企业管理系统'), 'error' => '请输入账号和密码']);
        }

        $admin = Admin::where('username', $username)->find();
        if (!$admin || !Admin::checkPassword($password, (string) $admin['password'])) {
            return View::admin('login', ['siteName' => setting('site_name', '企业管理系统'), 'error' => '账号或密码不正确']);
        }
        if ((int) $admin['status'] !== 1) {
            return View::admin('login', ['siteName' => setting('site_name', '企业管理系统'), 'error' => '该账号已被禁用']);
        }

        $admin->save([
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_ip'   => $this->request->ip(),
        ]);

        session('admin_id', (int) $admin['id']);
        session('admin_name', (string) $admin['username']);

        return redirect('/admin');
    }

    public function logout()
    {
        session('admin_id', null);
        session('admin_name', null);
        return redirect('/admin/login');
    }
}
