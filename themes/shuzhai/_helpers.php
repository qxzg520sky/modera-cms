<?php
/**
 * 书斋博客模板 · 公共助手
 * 固定左右分栏（主栏 + 侧栏），桌面端不做响应式；手机端使用 mobile/ 独立模板。
 */

if (!function_exists('sz_t')) {
    function sz_t(string $zh, string $en = ''): string
    {
        if ($en === '') {
            $en = $zh;
        }
        return current_lang() === 'en' ? $en : $zh;
    }
}

if (!function_exists('sz_abs')) {
    /** 把任意图片地址补成站点内可用的绝对路径 */
    function sz_abs(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (stripos($url, 'data:') === 0) {
            return '';
        }
        if (stripos($url, 'http://') === 0 || stripos($url, 'https://') === 0 || strpos($url, '//') === 0) {
            return $url;
        }
        if ($url[0] !== '/') {
            $url = '/' . ltrim($url, './');
        }
        return $url;
    }
}

if (!function_exists('sz_content_image')) {
    /** 从正文 HTML 中取第一张可用图片 */
    function sz_content_image(string $html): string
    {
        if ($html === '' || stripos($html, '<img') === false) {
            return '';
        }
        if (preg_match_all('/<img[^>]+src\s*=\s*["\']([^"\']+)["\']/i', $html, $m)) {
            foreach ($m[1] as $src) {
                $src = html_entity_decode(trim($src), ENT_QUOTES, 'UTF-8');
                if ($src === '' || stripos($src, 'data:') === 0) {
                    continue;
                }
                if (preg_match('/\.(svg|gif)(\?|#|$)/i', $src)) {
                    continue;
                }
                return sz_abs($src);
            }
        }
        return '';
    }
}

if (!function_exists('sz_cover_src')) {
    /**
     * 封面优先级：后台上传封面 → 正文第一张图 → 主题内置自动封面（按文章 id 分配）
     */
    function sz_cover_src(array $a): string
    {
        $cover = trim((string) ($a['cover'] ?? ''));
        if ($cover !== '') {
            return sz_abs($cover);
        }
        $inline = sz_content_image((string) ($a['content'] ?? ''));
        if ($inline !== '') {
            return $inline;
        }
        $id = (int) ($a['id'] ?? 0);
        $n = ($id % 6) + 1;
        $file = 'cover' . $n . '.jpg';
        $path = root_path() . 'themes' . DIRECTORY_SEPARATOR . \app\service\ThemeManager::current() . DIRECTORY_SEPARATOR . $file;
        $v = is_file($path) ? (string) filemtime($path) : '1';
        return '/theme/' . $file . '?v=' . $v;
    }
}

if (!function_exists('sz_cover_is_inline')) {
    /** 封面取自正文（正文里已经出现过，详情页不再重复放头图） */
    function sz_cover_is_inline(array $a): bool
    {
        return trim((string) ($a['cover'] ?? '')) === ''
            && sz_content_image((string) ($a['content'] ?? '')) !== '';
    }
}

if (!function_exists('sz_cover')) {
    function sz_cover(array $a, string $class = '', bool $eager = false, string $sizes = ''): string
    {
        $src = sz_cover_src($a);
        if ($src === '') {
            return '';
        }
        $alt = ml($a, 'title');
        $attr = ' class="' . e($class) . '"';
        $attr .= ' src="' . e($src) . '" alt="' . e($alt) . '"';
        $attr .= $eager ? ' loading="eager" decoding="async"' : ' loading="lazy" decoding="async"';
        if ($sizes !== '') {
            $attr .= ' sizes="' . e($sizes) . '"';
        }
        return '<img' . $attr . '>';
    }
}

if (!function_exists('sz_excerpt')) {
    function sz_excerpt(array $a, int $len = 86): string
    {
        $text = trim((string) ml($a, 'summary'));
        if ($text === '') {
            $text = trim((string) ml($a, 'content'));
        }
        $text = strip_tags($text);
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);
        if (mb_strlen($text, 'UTF-8') > $len) {
            $text = mb_substr($text, 0, $len, 'UTF-8') . '…';
        }
        return $text;
    }
}

if (!function_exists('sz_date')) {
    function sz_date(?string $time, bool $withYear = true): string
    {
        $ts = $time ? strtotime($time) : 0;
        if (!$ts) {
            return '';
        }
        if (current_lang() === 'en') {
            return date($withYear ? 'M d, Y' : 'M d', $ts);
        }
        return date($withYear ? 'Y年m月d日' : 'm月d日', $ts);
    }
}

if (!function_exists('sz_reading_time')) {
    function sz_reading_time(array $a): int
    {
        $text = strip_tags((string) ($a['content'] ?? ''));
        $len = mb_strlen($text, 'UTF-8');
        return max(1, (int) ceil($len / 380));
    }
}

if (!function_exists('sz_cat_name')) {
    function sz_cat_name(array $cat): string
    {
        return (string) ml($cat, 'name');
    }
}

if (!function_exists('sz_issue')) {
    /** 刊期号，用文章 id 生成稳定的编号 */
    function sz_issue(array $a): string
    {
        return str_pad((string) (((int) ($a['id'] ?? 1)) % 100), 2, '0', STR_PAD_LEFT);
    }
}
