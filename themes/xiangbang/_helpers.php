<?php
/** 象邦自然系模板 · 公共辅助函数 */

if (!function_exists('xb_cover')) {
    function xb_cover(array $a, string $fallback = '/theme/pagebg.jpg'): string
    {
        $c = trim((string) ml($a, 'cover'));
        return $c !== '' ? $c : $fallback;
    }
}

if (!function_exists('xb_title')) {
    function xb_title(array $a): string
    {
        return trim((string) ml($a, 'title'));
    }
}

if (!function_exists('xb_summary')) {
    function xb_summary(array $a, int $len = 80): string
    {
        $s = trim((string) ml($a, 'summary'));
        if ($s === '') {
            $s = trim(strip_tags((string) ml($a, 'content')));
        }
        return mb_strlen($s, 'UTF-8') > $len ? mb_substr($s, 0, $len, 'UTF-8') . '…' : $s;
    }
}

if (!function_exists('xb_date')) {
    function xb_date(array $a, string $fmt = 'Y.m.d'): string
    {
        $t = strtotime((string) ($a['create_time'] ?? ''));
        return $t ? date($fmt, $t) : '';
    }
}

if (!function_exists('xb_article_url')) {
    function xb_article_url(array $a): string
    {
        return lang_path('/a/' . (int) ($a['id'] ?? 0));
    }
}

if (!function_exists('xb_cat_url')) {
    function xb_cat_url(string $slug): string
    {
        return lang_path('/c/' . $slug);
    }
}

if (!function_exists('xb_page_url')) {
    function xb_page_url(string $slug): string
    {
        return lang_path('/p/' . $slug);
    }
}

if (!function_exists('xb_first_image')) {
    function xb_first_image(string $html, string $fallback = '/theme/hero.jpg'): string
    {
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
            return $m[1];
        }
        return $fallback;
    }
}

if (!function_exists('xb_paragraphs')) {
    function xb_paragraphs(string $html, int $limit = 3): array
    {
        preg_match_all('/<p[^>]*>(.*?)<\/p>/iu', $html, $m);
        $out = [];
        foreach ($m[1] as $p) {
            $text = trim(strip_tags($p));
            if ($text !== '') {
                $out[] = $text;
            }
            if (count($out) >= $limit) {
                break;
            }
        }
        return $out;
    }
}

if (!function_exists('xb_split_slogan')) {
    function xb_split_slogan(string $slogan): array
    {
        $parts = preg_split('/\s*[·•|｜]\s*/u', $slogan, 2);
        if ($parts && count($parts) >= 2 && trim($parts[1]) !== '') {
            return [trim($parts[0]), trim($parts[1])];
        }
        $slogan = trim($slogan);
        if ($slogan === '') {
            return ['让每一面墙', '自由呼吸'];
        }
        if (mb_strlen($slogan, 'UTF-8') > 10) {
            $half = (int) ceil(mb_strlen($slogan, 'UTF-8') / 2);
            return [mb_substr($slogan, 0, $half, 'UTF-8'), mb_substr($slogan, $half, null, 'UTF-8')];
        }
        return [$slogan, ''];
    }
}

if (!function_exists('xb_page_hero')) {
    /** 内页头图：$crumbs = [['name'=>'首页','url'=>'/'], ...] */
    function xb_page_hero(string $title, string $desc = '', array $crumbs = [], string $eyebrow = ''): void
    {
        ?>
        <section class="xb-page-hero">
          <div class="xb-container xb-page-hero-inner">
            <?php if ($crumbs): ?>
            <div class="xb-crumb">
              <?php foreach ($crumbs as $i => $c): ?>
                <?php if ($i > 0): ?><span>/</span><?php endif; ?>
                <?php if (!empty($c['url'])): ?><a href="<?= e($c['url']) ?>"><?= e($c['name']) ?></a><?php else: ?><span><?= e($c['name']) ?></span><?php endif; ?>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if ($eyebrow !== ''): ?><div class="xb-page-eyebrow"><?= e($eyebrow) ?></div><?php endif; ?>
            <h1 class="xb-page-title"><?= e($title) ?></h1>
            <?php if ($desc !== ''): ?><p class="xb-page-desc"><?= e($desc) ?></p><?php endif; ?>
          </div>
        </section>
        <?php
    }
}

if (!function_exists('xb_section_head')) {
    function xb_section_head(string $eyebrow, string $title, string $desc = ''): void
    {
        ?>
        <div class="xb-section-head" data-reveal>
          <div>
            <span class="xb-eyebrow"><?= e($eyebrow) ?></span>
            <h2 class="xb-section-title"><?= $title ?></h2>
            <?php if ($desc !== ''): ?><p class="xb-section-desc"><?= e($desc) ?></p><?php endif; ?>
          </div>
        </div>
        <?php
    }
}
