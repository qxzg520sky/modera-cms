<?php
$siteName = setting('site_name', '墨白笔记');
$siteSlogan = setting('site_slogan', '写代码，也写方法');
$siteDesc = setting('site_description', '关于建站、代码与长期主义的独立笔记。');
$logo = setting('site_logo', '');
$nav = $nav ?? [];
$pages = $pages ?? \think\facade\Db::name('page')->where('status', 1)->order('id asc')->select()->toArray();
$curLang = current_lang();
$isHome = !isset($cat) && !isset($row) && !isset($kw);

$activeCatId = (int) ($cat['id'] ?? 0);
if (!$activeCatId && isset($row['category_id'])) {
    $activeCatId = (int) $row['category_id'];
}

$navItems = [['name' => lang('首页'), 'url' => lang_path('/'), 'now' => $isHome]];
$catCount = 0;
foreach ($nav as $c) {
    if (($c['slug'] ?? '') === 'jobs') {
        continue;
    }
    $catCount++;
    if ($catCount > 5) {
        break;
    }
    $navItems[] = [
        'name' => (string) $c['name'],
        'url' => lang_path('/c/' . $c['slug']),
        'now' => $activeCatId > 0 && $activeCatId === (int) ($c['id'] ?? 0),
    ];
}
$aboutPage = null;
foreach ($pages as $pp) {
    if (($pp['slug'] ?? '') === 'about') {
        $aboutPage = $pp;
        break;
    }
}
if ($aboutPage) {
    $navItems[] = [
        'name' => (string) $aboutPage['title'],
        'url' => lang_path('/p/' . $aboutPage['slug']),
        'now' => isset($row['slug']) && (string) $row['slug'] === (string) $aboutPage['slug'],
    ];
}

$seoTitle = $seoTitle ?? $siteName;
$seoDesc = $seoDesc ?? $siteDesc;
$seoKeywords = $seoKeywords ?? setting('site_keywords', '');
$ogType = isset($row['id']) ? 'article' : 'website';
$ogImage = '';
if (isset($row['cover']) && trim((string) $row['cover']) !== '') {
    $ogImage = ink_abs_url((string) $row['cover']);
} elseif ($logo !== '') {
    $ogImage = ink_abs_url((string) $logo);
}
$canonical = '';
if (!empty($_SERVER['REQUEST_URI'])) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $canonical = $scheme . '://' . $host . $_SERVER['REQUEST_URI'];
}
?>
<!DOCTYPE html>
<html lang="<?= e(str_replace('_', '-', $curLang ?: 'zh-CN')) ?>" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<?php if ($logo !== ''): ?><link rel="icon" href="<?= e((string) $logo) ?>">
<link rel="apple-touch-icon" href="<?= e((string) $logo) ?>">
<?php endif; ?>
<title><?= e($seoTitle) ?></title>
<meta name="description" content="<?= e($seoDesc) ?>">
<meta name="keywords" content="<?= e($seoKeywords) ?>">
<meta name="color-scheme" content="light dark">
<meta name="theme-color" content="#f4f1ea" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#111412" media="(prefers-color-scheme: dark)">
<?php if ($canonical !== ''): ?><link rel="canonical" href="<?= e($canonical) ?>"><?php endif; ?>
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($seoTitle) ?>">
<meta property="og:description" content="<?= e($seoDesc) ?>">
<meta property="og:type" content="<?= e($ogType) ?>">
<?php if ($canonical !== ''): ?><meta property="og:url" content="<?= e($canonical) ?>"><?php endif; ?>
<?php if ($ogImage !== ''): ?><meta property="og:image" content="<?= e($ogImage) ?>"><?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<script>
(function () {
  try {
    var saved = localStorage.getItem('ink-theme');
    var dark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    document.documentElement.setAttribute('data-theme', saved || (dark ? 'dark' : 'light'));
    document.documentElement.className += ' js';
  } catch (e) {}
})();
</script>
<link rel="stylesheet" href="/theme/style.css?v=1.0.0">
<?php if (!empty(multilang_config()['langs'])): ?>
<?php foreach (multilang_config()['langs'] as $lc): ?>
<link rel="alternate" hreflang="<?= e($lc) ?>" href="<?= e(lang_url($lc)) ?>">
<?php endforeach; ?>
<?php endif; ?>
</head>
<body class="ink-body<?= $isHome ? ' is-home' : '' ?>">
<a class="skip-link" href="#main"><?= e(lang('跳到正文')) ?></a>

<header class="site-head" data-head>
  <div class="wrap head-in">
    <a class="brand" href="<?= e(lang_path('/')) ?>" aria-label="<?= e($siteName) ?>">
      <span class="brand-mark" aria-hidden="true">M</span>
      <span class="brand-copy">
        <b><?= e($siteName) ?></b>
        <small>JOURNAL / NOTES</small>
      </span>
    </a>

    <nav class="site-nav" id="siteNav" aria-label="<?= e(lang('主导航')) ?>">
      <?php foreach ($navItems as $it): ?>
        <a href="<?= e($it['url']) ?>"<?= $it['now'] ? ' class="now" aria-current="page"' : '' ?>><?= e($it['name']) ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="head-actions">
      <?= lang_switcher('theme-lang') ?>
      <form class="head-search" action="<?= e(lang_path('/search')) ?>" method="get" role="search">
        <input type="search" name="kw" value="<?= e($kw ?? '') ?>" placeholder="<?= e(lang('搜索笔记')) ?>" aria-label="<?= e(lang('搜索笔记')) ?>">
        <button type="submit" aria-label="<?= e(lang('搜索')) ?>">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="M15.5 15.5L21 21"></path></svg>
        </button>
      </form>
      <button class="theme-btn" type="button" data-theme-toggle aria-label="<?= e(lang('切换深浅色')) ?>" title="<?= e(lang('切换深浅色')) ?>">
        <svg class="ico-sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4.2"></circle><path d="M12 2.5v3M12 18.5v3M2.5 12h3M18.5 12h3M5.3 5.3l2.1 2.1M16.6 16.6l2.1 2.1M18.7 5.3l-2.1 2.1M7.4 16.6l-2.1 2.1"></path></svg>
        <svg class="ico-moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 15.2A8.3 8.3 0 0 1 8.8 4a8.4 8.4 0 1 0 11.2 11.2z"></path></svg>
      </button>
      <button class="menu-btn" type="button" data-menu-toggle aria-label="<?= e(lang('打开菜单')) ?>" aria-expanded="false" aria-controls="siteNav">
        <span></span><span></span>
      </button>
    </div>
  </div>
</header>

<main id="main" class="site-main">

