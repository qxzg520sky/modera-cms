<?php
declare(strict_types=1);

namespace app\controller\admin;

use think\facade\Db;

class Page extends Base
{
    public function index()
    {
        $rows = Db::name('page')->order('id desc')->select()->toArray();
        return $this->view('page/index', [
            'active'    => 'page',
            'pageTitle' => '单页管理',
            'rows'      => $rows,
        ]);
    }

    public function edit()
    {
        $id  = (int) $this->request->param('id', 0);
        $row = $id ? Db::name('page')->where('id', $id)->find() : [];
        if ($id && !$row) {
            return $this->back('/admin/page', '单页不存在');
        }
        return $this->view('page/edit', [
            'active'    => 'page',
            'pageTitle' => $id ? '编辑单页' : '新建单页',
            'row'       => $row ?: [],
        ]);
    }

    public function save()
    {
        $id   = (int) $this->request->post('id', 0);
        $data = [
            'slug'            => trim((string) $this->request->post('slug', '')),
            'title'           => trim((string) $this->request->post('title', '')),
            'content'         => (string) $this->request->post('content', ''),
            'template'        => trim((string) $this->request->post('template', '')),
            'status'          => (int) $this->request->post('status', 1),
            'seo_title'       => trim((string) $this->request->post('seo_title', '')),
            'seo_keywords'    => trim((string) $this->request->post('seo_keywords', '')),
            'seo_description' => trim((string) $this->request->post('seo_description', '')),
            'update_time'     => date('Y-m-d H:i:s'),
        ];

        if ($data['title'] === '' || $data['slug'] === '') {
            return $this->back('/admin/page', '标题和别名都必须填写');
        }

        $exists = Db::name('page')->where('slug', $data['slug'])->where('id', '<>', $id)->find();
        if ($exists) {
            return $this->back('/admin/page', '别名已被占用：' . $data['slug']);
        }

        if ($id) {
            Db::name('page')->where('id', $id)->update($data);
            return $this->back('/admin/page', '单页已更新');
        }
        $data['create_time'] = date('Y-m-d H:i:s');
        Db::name('page')->insert($data);
        return $this->back('/admin/page', '单页已创建');
    }

    public function delete()
    {
        $id = (int) $this->request->post('id', 0);
        if ($id) {
            Db::name('page')->where('id', $id)->delete();
        }
        return $this->back('/admin/page', '已删除');
    }
}
