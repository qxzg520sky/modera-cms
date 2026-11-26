<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\model\Admin;
use think\facade\Db;

class User extends Base
{
    public function index()
    {
        $rows = Db::name('admin')->order('id asc')->select()->toArray();
        return $this->view('user/index', [
            'active'    => 'user',
            'pageTitle' => '管理员',
            'rows'      => $rows,
        ]);
    }

    public function edit()
    {
        $id  = (int) $this->request->param('id', 0);
        $row = $id ? Db::name('admin')->where('id', $id)->find() : [];
        if ($id && !$row) {
            return $this->back('/admin/user', '账号不存在');
        }
        return $this->view('user/edit', [
            'active'    => 'user',
            'pageTitle' => $id ? '编辑管理员' : '新增管理员',
            'row'       => $row ?: [],
        ]);
    }

    public function save()
    {
        $id       = (int) $this->request->post('id', 0);
        $username = trim((string) $this->request->post('username', ''));
        $password = (string) $this->request->post('password', '');
        $nickname = trim((string) $this->request->post('nickname', ''));
        $status   = (int) $this->request->post('status', 1);

        if ($username === '') {
            return $this->back('/admin/user', '账号不能为空');
        }
        $exists = Db::name('admin')->where('username', $username)->where('id', '<>', $id)->find();
        if ($exists) {
            return $this->back('/admin/user', '账号已存在');
        }

        if ($id) {
            $data = ['username' => $username, 'nickname' => $nickname, 'status' => $status];
            if ($password !== '') {
                $data['password'] = Admin::hashPassword($password);
            }
            Db::name('admin')->where('id', $id)->update($data);
            return $this->back('/admin/user', '账号已更新');
        }

        if (strlen($password) < 6) {
            return $this->back('/admin/user', '密码至少 6 位');
        }
        Db::name('admin')->insert([
            'username'    => $username,
            'password'    => Admin::hashPassword($password),
            'nickname'    => $nickname !== '' ? $nickname : $username,
            'status'      => $status,
            'create_time' => date('Y-m-d H:i:s'),
        ]);
        return $this->back('/admin/user', '账号已创建');
    }

    public function delete()
    {
        $id = (int) $this->request->post('id', 0);
        if ($id && $id !== (int) $this->request->session('admin_id')) {
            if (Db::name('admin')->count() > 1) {
                Db::name('admin')->where('id', $id)->delete();
                return $this->back('/admin/user', '已删除');
            }
            return $this->back('/admin/user', '至少保留一个管理员账号');
        }
        return $this->back('/admin/user', '不能删除当前登录账号');
    }
}
