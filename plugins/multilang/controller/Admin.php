<?php
declare(strict_types=1);

namespace plugins\multilang\controller;

use app\service\View;
use plugins\multilang\service\Lang;
use think\facade\Db;

/**
 * 多语言插件后台
 */
class Admin extends \app\controller\admin\Base
{
    /** 用插件自己的模板渲染，风格沿用后台 admin.css */
    protected function render(string $tpl, array $data = []): string
    {
        $flash = session('flash_msg');
        if ($flash !== null && $flash !== '') {
            session('flash_msg', null);
        }

        $data['admin']   = $this->admin;
        $data['flash']   = (string) $flash;
        $data['active']  = 'plugin-' . md5('/admin/plugin/multilang');
        $data['logs']    = (array) (session('ml_logs') ?: []);
        $data['enabled'] = (int) (Lang::row()['enabled'] ?? 0);

        if ($data['logs']) {
            session('ml_logs', null);
        }

        $file = __DIR__ . '/../view/' . $tpl . '.php';
        return View::fetch($file, $data);
    }

    /** 管理首页 */
    public function index()
    {
        $cfg    = Lang::config();
        $codes  = $cfg['langs'];
        $status = [];
        foreach ($codes as $code) {
            $status[$code] = Lang::hasFields($code);
        }

        $cats  = Db::name('category')->order('sort asc, id asc')->select()->toArray();
        $arts  = Db::name('article')->order('is_top desc, id desc')->select()->toArray();
        $pages = Db::name('page')->order('id asc')->select()->toArray();

        return $this->render('admin', [
            'pageTitle' => '多语言 - 后台',
            'cfg'       => $cfg,
            'codes'     => $codes,
            'status'    => $status,
            'fields'    => Lang::fieldMap(),
            'cats'      => $cats,
            'arts'      => $arts,
            'pages'     => $pages,
        ]);
    }

    /** 保存语言配置（同时自动建/删字段） */
    public function save()
    {
        // 每行一个语言："en English"
        $text = (string) $this->request->post('langs_text', '');

        $codes = [];
        $names = [];
        foreach (preg_split('/[\r\n]+/', $text) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $parts  = preg_split('/\s+/', $line);
            $code   = strtolower($parts[0]);
            $codes[] = $code;
            $names[$code] = $parts[1] ?? strtoupper($code);
        }

        [$codes, $logs] = Lang::saveConfig(implode(',', $codes), 'zh-cn', $names);

        session('ml_logs', $logs);
        return $this->back('/admin/plugin/multilang', '语言设置已保存，共 ' . count($codes) . ' 种外语');
    }

    /** 手动补建字段 */
    public function install()
    {
        $logs = Lang::install(Lang::codes());
        session('ml_logs', $logs);
        return $this->back('/admin/plugin/multilang', '字段已创建');
    }

    /** 删除全部多语言字段（数据一起删） */
    public function drop()
    {
        $logs = Lang::uninstall([], true);
        session('ml_logs', $logs);
        return $this->back('/admin/plugin/multilang', '多语言字段已全部删除，内容回到中文单语状态');
    }

    /** 编辑某条内容的多语言版本 */
    public function edit(string $type = 'article', int $id = 0)
    {
        $map = ['category' => 'category', 'article' => 'article', 'page' => 'page'];
        if (!isset($map[$type])) {
            return $this->back('/admin/plugin/multilang', '类型不正确');
        }

        $row = Db::name($type)->where('id', $id)->find();
        if (!$row) {
            return $this->back('/admin/plugin/multilang', '内容不存在');
        }

        $cfg = Lang::config();

        return $this->render('edit', [
            'pageTitle' => '编辑多语言内容',
            'type'      => $type,
            'row'       => $row,
            'codes'     => $cfg['langs'],
            'names'     => $cfg['names'],
            'fields'    => Lang::fieldMap()[$type] ?? [],
        ]);
    }

    /** 保存多语言内容 */
    public function saveContent()
    {
        $type = (string) $this->request->post('type', 'article');
        if (!in_array($type, ['category', 'article', 'page'], true)) {
            return $this->back('/admin/plugin/multilang', '类型不正确');
        }

        $id = (int) $this->request->post('id', 0);
        $row = Db::name($type)->where('id', $id)->find();
        if (!$row) {
            return $this->back('/admin/plugin/multilang', '内容不存在');
        }

        $codes = Lang::codes();
        $data  = [];
        foreach ($codes as $code) {
            foreach (Lang::fieldMap()[$type] ?? [] as $field => $label) {
                $key  = $field . '_' . $code;
                $post = $this->request->post($key, null);
                if ($post === null) {
                    continue;
                }
                $data[$field . Lang::suffix($code)] = (string) $post;
            }
        }

        if (!$data) {
            return $this->back('/admin/plugin/multilang', '没有可保存的字段');
        }

        Db::name($type)->where('id', $id)->update($data);

        return $this->back('/admin/plugin/multilang', '多语言内容已保存');
    }
}
