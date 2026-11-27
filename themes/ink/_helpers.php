<?php
declare(strict_types=1);

if (!function_exists('ink_date')) {
    function ink_date(array $a, string $fmt = 'Y.m.d'): string
    {
        $t = strtotime((string) ($a['create_time'] ?? ''));
        return $t ? date($fmt, $t) : '—';
    }
}

if (!function_exists('ink_summary')) {
    function ink_summary(array $a, int $len = 100): string
    {
        $s = trim((string) ml($a, 'summary'));
        if ($s === '') {
            $s = trim(strip_tags((string) ml($a, 'content')));
        }
        $s = trim((string) preg_replace('/\s+/u', ' ', $s));
        return mb_substr($s, 0, $len, 'UTF-8');
    }
}

if (!function_exists('ink_views')) {
    function ink_views(array $a): string
    {
        $n = (int) ($a['views'] ?? 0);
        return $n > 999 ? number_format($n / 1000, 1) . 'k' : (string) $n;
    }
}

if (!function_exists('ink_cat_map')) {
    function ink_cat_map(): array
    {
        static $map = null;
        if ($map === null) {
            $map = [];
            try {
                foreach (\think\facade\Db::name('category')->where('status', 1)->select()->toArray() as $c) {
                    $map[(int) $c['id']] = (string) $c['name'];
                }
            } catch (\Throwable $e) {
                $map = [];
            }
        }
        return $map;
    }
}

if (!function_exists('ink_cat_name')) {
    function ink_cat_name(array $a): string
    {
        $map = ink_cat_map();
        return $map[(int) ($a['category_id'] ?? 0)] ?? '笔记';
    }
}

if (!function_exists('ink_cat_slug')) {
    function ink_cat_slug(array $a): string
    {
        static $map = null;
        if ($map === null) {
            $map = [];
            try {
                foreach (\think\facade\Db::name('category')->where('status', 1)->select()->toArray() as $c) {
                    $map[(int) $c['id']] = (string) $c['slug'];
                }
            } catch (\Throwable $e) {
                $map = [];
            }
        }
        return $map[(int) ($a['category_id'] ?? 0)] ?? '';
    }
}

if (!function_exists('ink_abs_url')) {
    function ink_abs_url(string $u): string
    {
        if ($u === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $u)) {
            return $u;
        }
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        return $scheme . '://' . $host . '/' . ltrim($u, '/');
    }
}

if (!function_exists('ink_cover')) {
    /**
     * 文章封面：后台上传的 cover 优先；没有 cover 时输出 canvas，由前端按标题自动生成。
     */
    function ink_cover(array $a, string $kind = 'card', bool $eager = false): string
    {
        $cover = trim((string) ($a['cover'] ?? ''));
        $title = (string) ml($a, 'title');
        $summary = ink_summary($a, 76);
        $cat = ink_cat_name($a);
        $date = ink_date($a);
        $seed = (int) ($a['id'] ?? 0) . '|' . (string) ($a['slug'] ?? '') . '|' . $title;
        $no = 'NO.' . str_pad((string) (int) ($a['id'] ?? 0), 2, '0', STR_PAD_LEFT);
        $alt = $title . ' · ' . $cat;
        $loading = $eager ? 'eager' : 'lazy';
        $priority = $eager ? ' fetchpriority="high"' : '';

        $html = '<div class="cover-media ' . ($cover !== '' ? 'has-img' : 'is-auto') . '" data-cover>';
        if ($cover !== '') {
            $html .= '<img class="cover-img" src="' . e($cover) . '" alt="' . e($alt) . '" loading="' . $loading . '" decoding="async"' . $priority . '>';
        }
        $html .= '<canvas class="cover-canvas" width="1600" height="1000"'
            . ' data-cover-title="' . e($title) . '"'
            . ' data-cover-summary="' . e($summary) . '"'
            . ' data-cover-cat="' . e($cat) . '"'
            . ' data-cover-date="' . e($date) . '"'
            . ' data-cover-seed="' . e($seed) . '"'
            . ' data-cover-no="' . e($no) . '"'
            . ' aria-hidden="true">' . e($title) . '</canvas>';
        $html .= '<span class="cover-rule" aria-hidden="true"></span>';
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('ink_card')) {
    function ink_card(array $a, int $index = 0, bool $eager = false): string
    {
        $url = lang_path('/a/' . (int) ($a['id'] ?? 0));
        $title = (string) ml($a, 'title');
        $cat = ink_cat_name($a);
        $date = ink_date($a);
        $summary = ink_summary($a, 96);
        $views = ink_views($a);
        $no = str_pad((string) ($index > 0 ? $index : (int) ($a['id'] ?? 0)), 2, '0', STR_PAD_LEFT);

        return '<article class="post-card">'
            . '<a class="card-cover" href="' . e($url) . '" aria-label="' . e($title) . '">'
            . ink_cover($a, 'card', $eager)
            . '<span class="card-no" aria-hidden="true">NO.' . e($no) . '</span>'
            . '</a>'
            . '<div class="card-body">'
            . '<div class="card-meta"><a class="tag" href="' . e(lang_path('/c/' . ink_cat_slug($a))) . '">' . e($cat) . '</a>'
            . '<span>' . e($date) . '</span><span>' . e($views) . ' ' . e(lang('阅读')) . '</span></div>'
            . '<h3><a href="' . e($url) . '">' . e($title) . '</a></h3>'
            . '<p>' . e($summary) . '</p>'
            . '<a class="read-more" href="' . e($url) . '">' . e(lang('阅读全文')) . '<i aria-hidden="true">↗</i></a>'
            . '</div></article>';
    }
}

if (!function_exists('ink_pager')) {
    function ink_pager(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }
        if (strpos($html, 'class="pager"') !== false) {
            return $html;
        }
        return '<div class="pager">' . $html . '</div>';
    }
}
