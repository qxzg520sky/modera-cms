<?php
declare(strict_types=1);

namespace app\controller\admin;

use think\facade\Db;

class Article extends Base
{
    public function index()
    {
        $kw    = trim((string) $this->request->get('kw', ''));
        $catId = (int) $this->request->get('cat', 0);
        $page  = max(1, (int) $this->request->get('page', 1));
        $size  = (int) setting('article_page_size', 10);
        $size  = $size > 0 ? $size : 10;

        $build = static function () use ($kw, $catId) {
            $q = Db::name('article');
            if ($kw !== '') {
                $q->whereLike('title', '%' . $kw . '%');
            }
            if ($catId > 0) {
                $q->where('category_id', $catId);
            }
            return $q;
        };

        $total = (int) $build()->count();
        $rows  = $build()->order('is_top desc, id desc')->page($page, $size)->select()->toArray();
        $cats  = Db::name('category')->column('name', 'id');

        foreach ($rows as &$row) {
            $row['category_name'] = $cats[$row['category_id']] ?? '未分类';
        }
        unset($row);

        $baseUrl = '/admin/article?kw=' . urlencode($kw) . '&cat=' . $catId;

        return $this->view('article/index', [
            'active'   => 'article',
            'pageTitle'=> '文章管理',
            'rows'     => $rows,
            'cats'     => Db::name('category')->order('sort asc, id asc')->select()->toArray(),
            'kw'       => $kw,
            'catId'    => $catId,
            'total'    => $total,
            'pager'    => pager_html($page, $total, $size, $baseUrl),
        ]);
    }

    public function edit()
    {
        $id  = (int) $this->request->param('id', 0);
        $row = $id ? Db::name('article')->where('id', $id)->find() : [];
        if ($id && !$row) {
            return $this->back('/admin/article', '文章不存在');
        }
        return $this->view('article/edit', [
            'active'    => 'article',
            'pageTitle' => $id ? '编辑文章' : '发布文章',
            'row'       => $row ?: [],
            'cats'      => Db::name('category')->order('sort asc, id asc')->select()->toArray(),
        ]);
    }

    public function save()
    {
        $id   = (int) $this->request->post('id', 0);
        $data = [
            'category_id'     => (int) $this->request->post('category_id', 0),
            'title'           => trim((string) $this->request->post('title', '')),
            'slug'            => trim((string) $this->request->post('slug', '')),
            'cover'           => trim((string) $this->request->post('cover', '')),
            'summary'         => trim((string) $this->request->post('summary', '')),
            'content'         => (string) $this->request->post('content', ''),
            'author'          => trim((string) $this->request->post('author', '')),
            'sort'            => (int) $this->request->post('sort', 0),
            'status'          => (int) $this->request->post('status', 1),
            'is_top'          => (int) $this->request->post('is_top', 0),
            'seo_title'       => trim((string) $this->request->post('seo_title', '')),
            'seo_keywords'    => trim((string) $this->request->post('seo_keywords', '')),
            'seo_description' => trim((string) $this->request->post('seo_description', '')),
            'update_time'     => date('Y-m-d H:i:s'),
        ];

        if ($data['title'] === '') {
            return $this->back('/admin/article', '标题不能为空');
        }

        if ($id) {
            Db::name('article')->where('id', $id)->update($data);
            return $this->back('/admin/article', '文章已更新');
        }

        $data['create_time'] = date('Y-m-d H:i:s');
        Db::name('article')->insert($data);
        return $this->back('/admin/article', '文章已发布');
    }

    public function delete()
    {
        $id = (int) $this->request->post('id', 0);
        if ($id) {
            Db::name('article')->where('id', $id)->delete();
        }
        return $this->back('/admin/article', '已删除');
    }
}
