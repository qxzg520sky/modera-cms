<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use app\service\View;

/**
 * 后台控制器基类：登录态、模板渲染、提示跳转
 */
class Base extends BaseController
{
    /** @var array 当前管理员 */
    protected $admin = [];

    protected function initialize()
    {
        $id = (int) $this->request->session('admin_id');
        if ($id) {
            $row = \app\model\Admin::find($id);
            $this->admin = $row ? $row->toArray() : [];
        }
    }

    protected function view(string $tpl, array $data = []): string
    {
        $flash = session('flash_msg');
        if ($flash !== null && $flash !== '') {
            session('flash_msg', null);
        }
        $data['admin']  = $this->admin;
        $data['flash']  = (string) $flash;
        $data['active'] = $data['active'] ?? '';
        return View::admin($tpl, $data);
    }

    protected function back(string $url, string $msg = '')
    {
        if ($msg !== '') {
            session('flash_msg', $msg);
        }
        return redirect($url);
    }

    protected function isPost(): bool
    {
        return $this->request->isPost();
    }
}
