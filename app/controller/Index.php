<?php
declare(strict_types=1);

namespace app\controller;

use app\BaseController;
use app\service\View;
use think\facade\Db;

class Index extends BaseController
{
    /**
     * 按当前语言把字段值替换成对应语言（插件未启用/没填时自动回落中文）
     */
    private function langify(array $rows, array $fields): array
    {
        foreach ($rows as &$row) {
            if (!is_array($row)) {
                continue;
            }
            foreach ($fields as $field) {
                if (array_key_exists($field, $row)) {
                    $row[$field] = ml($row, $field);
                }
            }
        }
        unset($row);
        return $rows;
    }

    /** 导航栏目树（顶级栏目带 children），已按当前语言处理 */
    private function nav(): array
    {
        $cats = Db::name('category')
            ->where('status', 1)
            ->where('type', '<>', 'link')
            ->order('sort asc, id asc')
            ->select()
            ->toArray();

        $cats = $this->langify($cats, ['name', 'seo_description', 'seo_title', 'seo_keywords']);

        $byId = [];
        foreach ($cats as $c) {
            $c['children'] = [];
            $byId[(int) $c['id']] = $c;
        }

        // 子栏目挂到父栏目下；父栏目不可用时自己升为顶级，避免丢链接
        foreach (array_keys($byId) as $id) {
            $pid = (int) $byId[$id]['pid'];
            if ($pid > 0 && isset($byId[$pid])) {
                $byId[$pid]['children'][] = $byId[$id];
            } elseif ($pid > 0) {
                $byId[$id]['pid'] = 0;
            }
        }

        $tree = [];
        foreach ($byId as $c) {
            if ((int) $c['pid'] === 0) {
                $tree[] = $c;
            }
        }
        return $tree;
    }

    public function index()
    {
        $articles = Db::name('article')
            ->where('status', 1)
            ->order('is_top desc, id desc')
            ->limit(6)
            ->select()
            ->toArray();
        $articles = $this->langify($articles, ['title', 'summary', 'content', 'seo_title', 'seo_keywords', 'seo_description']);

        $pages = Db::name('page')->where('status', 1)->select()->toArray();
        $pages = $this->langify($pages, ['title', 'content']);

        return View::theme('index', [
            'nav'         => $this->nav(),
            'articles'    => $articles,
            'pages'       => $pages,
            'seoTitle'    => setting('site_name', '企业网站'),
            'seoKeywords' => setting('site_keywords', ''),
            'seoDesc'     => setting('site_description', ''),
        ]);
    }

    public function lists(string $slug = '')
    {
        $cat = Db::name('category')->where('slug', $slug)->where('status', 1)->find();
        if (!$cat) {
            return redirect(lang_path('/'));
        }
        $cat = $this->langify([$cat], ['name', 'seo_title', 'seo_keywords', 'seo_description'])[0];

        $page = max(1, (int) $this->request->get('page', 1));
        $size = (int) setting('article_page_size', 10);
        $size = $size > 0 ? $size : 10;

        // 父栏目自动聚合子栏目文章
        $catIds = array_merge([$cat['id']], Db::name('category')->where('pid', $cat['id'])->column('id'));
        $build  = static fn () => Db::name('article')->whereIn('category_id', $catIds)->where('status', 1);
        $total  = (int) $build()->count();
        $rows   = $build()->order('is_top desc, id desc')->page($page, $size)->select()->toArray();
        $rows   = $this->langify($rows, ['title', 'summary', 'content']);

        // 同级栏目切换条：当前栏目所在的那一组（父栏目 + 它的子栏目），只有一个栏目时不显示
        $rootId = (int) $cat['pid'];
        if ($rootId > 0) {
            $root = Db::name('category')->where('id', $rootId)->find();
            $root = $root ? $this->langify([$root], ['name'])[0] : $cat;
        } else {
            $root = $cat;
        }
        $subNav = [];
        if ($root) {
            $subNav[] = ['id' => (int) $root['id'], 'name' => $root['name'], 'slug' => $root['slug']];
            $kids     = Db::name('category')->where('pid', $root['id'])->where('status', 1)->order('sort asc, id asc')->select()->toArray();
            $kids     = $this->langify($kids, ['name']);
            foreach ($kids as $k) {
                $subNav[] = ['id' => (int) $k['id'], 'name' => $k['name'], 'slug' => $k['slug']];
            }
        }
        if (count($subNav) < 2) {
            $subNav = [];
        }

        return View::theme('list', [
            'nav'         => $this->nav(),
            'cat'         => $cat,
            'subNav'      => $subNav,
            'articles'    => $rows,
            'total'       => $total,
            'pager'       => pager_html($page, $total, $size, lang_path('/c/' . $slug)),
            'seoTitle'    => ($cat['seo_title'] ?: $cat['name']) . ' - ' . setting('site_name', ''),
            'seoKeywords' => $cat['seo_keywords'],
            'seoDesc'     => $cat['seo_description'],
        ]);
    }

    public function show($key = 0)
    {
        $key = trim((string) $key);

        // 新链接统一走数字 id；旧缓存里的 /a/{slug} 兼容并 301 到规范地址
        if ($key !== '' && !ctype_digit($key)) {
            $hit = Db::name('article')->where('slug', $key)->where('status', 1)->find();
            if (!$hit) {
                return redirect(lang_path('/'));
            }
            return redirect(lang_path('/a/' . (int) $hit['id']), 301);
        }

        $id  = (int) $key;
        $row = Db::name('article')->where('id', $id)->where('status', 1)->find();
        if (!$row) {
            return redirect(lang_path('/'));
        }
        Db::name('article')->where('id', $id)->inc('views')->update();
        $row = $this->langify([$row], ['title', 'summary', 'content', 'seo_title', 'seo_keywords', 'seo_description'])[0];

        $cat  = Db::name('category')->where('id', $row['category_id'])->find();
        $cat  = $cat ? $this->langify([$cat], ['name'])[0] : null;
        $prev = Db::name('article')->where('status', 1)->where('id', '<', $id)->order('id desc')->find();
        $next = Db::name('article')->where('status', 1)->where('id', '>', $id)->order('id asc')->find();
        $prev = $prev ? $this->langify([$prev], ['title'])[0] : null;
        $next = $next ? $this->langify([$next], ['title'])[0] : null;

        return View::theme('article', [
            'nav'         => $this->nav(),
            'row'         => $row,
            'cat'         => $cat,
            'prev'        => $prev,
            'next'        => $next,
            'seoTitle'    => ($row['seo_title'] ?: $row['title']) . ' - ' . setting('site_name', ''),
            'seoKeywords' => $row['seo_keywords'],
            'seoDesc'     => $row['seo_description'] ?: $row['summary'],
        ]);
    }

    public function page(string $slug = '')
    {
        $row = Db::name('page')->where('slug', $slug)->where('status', 1)->find();
        if (!$row) {
            return redirect(lang_path('/'));
        }
        $row = $this->langify([$row], ['title', 'content', 'seo_title', 'seo_keywords', 'seo_description'])[0];

        return View::theme('page', [
            'nav'         => $this->nav(),
            'row'         => $row,
            'seoTitle'    => ($row['seo_title'] ?: $row['title']) . ' - ' . setting('site_name', ''),
            'seoKeywords' => $row['seo_keywords'],
            'seoDesc'     => $row['seo_description'],
        ]);
    }

    public function search()
    {
        $kw   = trim((string) $this->request->get('kw', ''));
        $page = max(1, (int) $this->request->get('page', 1));
        $size = 10;

        $build = static fn () => Db::name('article')->where('status', 1)->whereLike('title', '%' . $kw . '%');
        $total = $kw === '' ? 0 : (int) $build()->count();
        $rows  = $kw === '' ? [] : $build()->order('id desc')->page($page, $size)->select()->toArray();
        $rows  = $this->langify($rows, ['title', 'summary']);

        return View::theme('list', [
            'nav'         => $this->nav(),
            'subNav'      => [],
            'cat'         => ['name' => lang('搜索') . '：' . $kw, 'slug' => '', 'seo_title' => '', 'seo_keywords' => '', 'seo_description' => ''],
            'articles'    => $rows,
            'total'       => $total,
            'pager'       => $kw === '' ? '' : pager_html($page, $total, $size, lang_path('/search?kw=' . urlencode($kw))),
            'seoTitle'    => lang('搜索') . ' - ' . setting('site_name', ''),
            'seoKeywords' => $kw,
            'seoDesc'     => '',
        ]);
    }

    /** 输出当前主题内的静态文件：/theme/style.css */
    public function asset(string $file = '')
    {
        $file = basename(str_replace('\\', '/', $file));
        // 兼容旧页面缓存里的 cover-1.jpg / cover_1.jpg，统一映射到 cover1.jpg
        $file = preg_replace('/^cover[-_](\d+)\.(jpg|jpeg|png)$/i', 'cover$1.$2', $file);
        if (!preg_match('/^[\w\-\.]+\.(css|js|png|jpg|jpeg|gif|svg|webp)$/i', $file)) {
            return response('', 404);
        }
        $theme = \app\service\ThemeManager::current();
        $path  = root_path() . 'themes/' . $theme . '/' . $file;
        if ($this->request->get('m')) {
            $mPath = root_path() . 'themes/' . $theme . '/mobile/' . $file;
            if (is_file($mPath)) {
                $path = $mPath;
            }
        }
        if (!is_file($path)) {
            return response('', 404);
        }
        $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = [
            'css' => 'text/css', 'js' => 'application/javascript', 'png' => 'image/png',
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif',
            'svg' => 'image/svg+xml', 'webp' => 'image/webp',
        ][$ext] ?? 'application/octet-stream';

        return response((string) file_get_contents($path), 200, ['Content-Type' => $mime]);
    }

    /**
     * 后台静态资源：部分服务器未放行 public/static 直连，这里兜底输出
     */
    public function adminAsset(string $file = 'admin.css')
    {
        $file = basename(str_replace('\\', '/', $file));
        if (!preg_match('/^[\w\-\.]+\.(css|js|png|jpg|jpeg|gif|svg|webp|woff|woff2)$/i', $file)) {
            return response('', 404);
        }
        $path = public_path() . 'static' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . $file;
        if (!is_file($path)) {
            return response('', 404);
        }
        $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = [
            'css' => 'text/css', 'js' => 'application/javascript', 'png' => 'image/png',
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif',
            'svg' => 'image/svg+xml', 'webp' => 'image/webp',
            'woff' => 'font/woff', 'woff2' => 'font/woff2',
        ][$ext] ?? 'application/octet-stream';
        return response((string) file_get_contents($path), 200, ['Content-Type' => $mime]);
    }
}
