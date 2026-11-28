<?php
include_once __DIR__ . '/_helpers.php';

$siteName  = setting('site_name', '书斋笔记');
$siteSlogan = setting('site_slogan', '慢慢写，认真读');
$siteDesc  = setting('site_description', '关于建站、代码与日常生活的独立博客。');
$logo      = setting('site_logo', '');
$nav       = $nav ?? [];
$pages     = $pages ?? \think\facade\Db::name('page')->where('status', 1)->order('id asc')->select()->toArray();
$curLang   = current_lang();
$isHome    = !isset($cat) && !isset($row) && !isset($kw);
$activeCat = (int) ($cat['id'] ?? 0);
if (!$activeCat && isset($row['category_id'])) {
    $activeCat = (int) $row['category_id'];
}

$szCats = [];
foreach ($nav as $c) {
    if (($c['slug'] ?? '') === 'jobs') {
        continue;
    }
    $szCats[] = $c;
    if (count($szCats) >= 6) {
        break;
    }
}
$aboutPage = null;
foreach ($pages as $p) {
    if (($p['slug'] ?? '') === 'about') {
        $aboutPage = $p;
        break;
    }
}

$seoTitle = $seoTitle ?? $siteName;
$seoDesc  = $seoDesc ?? $siteDesc;
$seoKeys  = $seoKeywords ?? setting('site_keywords', '');
$ogImage  = '';
if (isset($row['id'])) {
    $ogImage = sz_cover_src($row);
} elseif ($logo !== '') {
    $ogImage = sz_abs((string) $logo);
}
$canonical = '';
if (!empty($_SERVER['REQUEST_URI'])) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $canonical = $scheme . '://' . $host . $_SERVER['REQUEST_URI'];
}
?>
<!DOCTYPE html>
<html lang="<?= e(str_replace('_', '-', $curLang ?: 'zh-CN')) ?>" data-lang="<?= e($curLang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=1180">
<?php if ($logo !== ''): ?><link rel="icon" href="<?= e((string) $logo) ?>">
<link rel="apple-touch-icon" href="<?= e((string) $logo) ?>">
<?php endif; ?>
<title><?= e($seoTitle) ?></title>
<meta name="description" content="<?= e($seoDesc) ?>">
<meta name="keywords" content="<?= e($seoKeys) ?>">
<meta name="theme-color" content="#f3eee4">
<?php if ($canonical !== ''): ?><link rel="canonical" href="<?= e($canonical) ?>"><?php endif; ?>
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($seoTitle) ?>">
<meta property="og:description" content="<?= e($seoDesc) ?>">
<meta property="og:type" content="<?= isset($row['id']) ? 'article' : 'website' ?>">
<?php if ($canonical !== ''): ?><meta property="og:url" content="<?= e($canonical) ?>"><?php endif; ?>
<?php if ($ogImage !== ''): ?><meta property="og:image" content="<?= e($ogImage) ?>"><?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<link rel="stylesheet" href="/theme/style.css?v=1.0.0">
<?php if (!empty(multilang_config()['langs'])): ?>
<?php foreach (multilang_config()['langs'] as $lc): ?>
<link rel="alternate" hreflang="<?= e($lc) ?>" href="<?= e(lang_url($lc)) ?>">
<?php endforeach; ?>
<?php endif; ?>
</head>
<body class="sz-body<?= $isHome ? ' is-home' : '' ?>">

<div class="sz-topline">
  <div class="sz-wrap sz-topline-in">
    <span><?= e(sz_t('独立写作 · 慢更新', 'Independent writing · Slow publishing')) ?></span>
    <span class="sz-topline-mid"><?= e(date('Y')) ?> · NO.<?= e(str_pad((string) (date('n')), 2, '0', STR_PAD_LEFT)) ?></span>
    <span><?= e(sz_t('酒泉 · 中国', 'Jiuquan · China')) ?></span>
  </div>
</div>

<header class="sz-head">
  <div class="sz-wrap sz-head-in">
    <a class="sz-brand" href="<?= e(lang_path('/')) ?>">
      <span class="sz-mark" aria-hidden="true"><?= $curLang === 'en' ? 'S' : '书' ?></span>
      <span class="sz-brand-copy">
        <b><?= e($siteName) ?></b>
        <small>READING &amp; WRITING</small>
      </span>
    </a>

    <nav class="sz-nav" aria-label="<?= e(sz_t('主导航', 'Main navigation')) ?>">
      <a href="<?= e(lang_path('/')) ?>"<?= $isHome ? ' class="now"' : '' ?>><?= e(sz_t('首页', 'Home')) ?></a>
      <?php foreach ($szCats as $c): ?>
        <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"<?= $activeCat === (int) $c['id'] ? ' class="now"' : '' ?>><?= e(sz_cat_name($c)) ?></a>
      <?php endforeach; ?>
      <?php if ($aboutPage): ?>
        <a href="<?= e(lang_path('/p/' . $aboutPage['slug'])) ?>"<?= (isset($row['slug']) && $row['slug'] === $aboutPage['slug']) ? ' class="now"' : '' ?>><?= e(ml($aboutPage, 'title')) ?></a>
      <?php endif; ?>
    </nav>

    <div class="sz-head-tools">
      <a class="sz-member" href="/member/center"><?= e(sz_t('会员', 'Member')) ?></a>
      <form class="sz-search" action="<?= e(lang_path('/search')) ?>" method="get" role="search">
        <input type="search" name="kw" value="<?= e($kw ?? '') ?>" placeholder="<?= e(sz_t('搜索文章', 'Search')) ?>" aria-label="<?= e(sz_t('搜索文章', 'Search')) ?>">
        <button type="submit" aria-label="<?= e(sz_t('搜索', 'Search')) ?>">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="M15.5 15.5 21 21"></path></svg>
        </button>
      </form>
      <?= lang_switcher('sz-lang') ?>
    </div>
  </div>
</header>

<div class="sz-rule"><div class="sz-wrap"><span></span><i></i><span></span></div></div>

<main class="sz-main">
