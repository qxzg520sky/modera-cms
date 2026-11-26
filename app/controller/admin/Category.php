<?php
declare(strict_types=1);

namespace app\controller\admin;

use think\facade\Db;

class Category extends Base
{
    public function index()
    {
        $rows = Db::name('category')->order('sort asc, id asc')->select()->toArray();

        return $this->view('category/index', [
            'active'    => 'category',
            'pageTitle' => '栏目管理',
            'rows'      => $rows,
        ]);
    }

    public function edit()
    {
        $id  = (int) $this->request->param('id', 0);
        $row = $id ? Db::name('category')->where('id', $id)->find() : [];
        if ($id && !$row) {
            return $this->back('/admin/category', '栏目不存在');
        }
        return $this->view('category/edit', [
            'active'    => 'category',
            'pageTitle' => $id ? '编辑栏目' : '新建栏目',
            'row'       => $row ?: [],
            'all'       => Db::name('category')->where('id', '<>', $id)->order('sort asc, id asc')->select()->toArray(),
        ]);
    }

    public function save()
    {
        $id   = (int) $this->request->post('id', 0);
        $data = [
            'pid'             => (int) $this->request->post('pid', 0),
            'name'            => trim((string) $this->request->post('name', '')),
            'slug'            => trim((string) $this->request->post('slug', '')),
            'type'            => in_array($this->request->post('type'), ['list', 'page', 'link'], true) ? $this->request->post('type') : 'list',
            'link'            => trim((string) $this->request->post('link', '')),
            'template'        => trim((string) $this->request->post('template', '')),
            'sort'            => (int) $this->request->post('sort', 0),
            'status'          => (int) $this->request->post('status', 1),
            'seo_title'       => trim((string) $this->request->post('seo_title', '')),
            'seo_keywords'    => trim((string) $this->request->post('seo_keywords', '')),
            'seo_description' => trim((string) $this->request->post('seo_description', '')),
            'update_time'     => date('Y-m-d H:i:s'),
        ];

        if ($data['name'] === '') {
            return $this->back('/admin/category', '栏目名称不能为空');
        }
        if ($data['slug'] === '') {
            return $this->back('/admin/category', '栏目别名（slug）不能为空，用于前台 URL');
        }
        if ($data['pid'] === $id) {
            $data['pid'] = 0;
        }

        $exists = Db::name('category')->where('slug', $data['slug'])->where('id', '<>', $id)->find();
        if ($exists) {
            return $this->back('/admin/category', '别名已被占用：' . $data['slug']);
        }

        if ($id) {
            Db::name('category')->where('id', $id)->update($data);
            return $this->back('/admin/category', '栏目已更新');
        }
        $data['create_time'] = date('Y-m-d H:i:s');
        Db::name('category')->insert($data);
        return $this->back('/admin/category', '栏目已创建');
    }

    public function delete()
    {
        $id = (int) $this->request->post('id', 0);
        if ($id) {
            Db::name('category')->where('id', $id)->delete();
            Db::name('category')->where('pid', $id)->update(['pid' => 0]);
            Db::name('article')->where('category_id', $id)->update(['category_id' => 0]);
        }
        return $this->back('/admin/category', '已删除');
    }
}
