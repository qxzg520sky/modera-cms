<?php
include_once __DIR__ . '/../_helpers.php';

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
$mCats = [];
foreach ($nav as $c) {
    if (($c['slug'] ?? '') === 'jobs') {
        continue;
    }
    $mCats[] = $c;
    if (count($mCats) >= 6) {
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
$ogImage  = isset($row['id']) ? sz_cover_src($row) : ($logo !== '' ? sz_abs((string) $logo) : '');
$canonical = '';
if (!empty($_SERVER['REQUEST_URI'])) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $canonical = $scheme . '://' . (string) ($_SERVER['HTTP_HOST'] ?? 'localhost') . $_SERVER['REQUEST_URI'];
}
?>
<!DOCTYPE html>
<html lang="<?= e(str_replace('_', '-', $curLang ?: 'zh-CN')) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,viewport-fit=cover">
<?php if ($logo !== ''): ?><link rel="icon" href="<?= e((string) $logo) ?>"><?php endif; ?>
<title><?= e($seoTitle) ?></title>
<meta name="description" content="<?= e($seoDesc) ?>">
<meta name="theme-color" content="#f4efe5">
<?php if ($canonical !== ''): ?><link rel="canonical" href="<?= e($canonical) ?>"><?php endif; ?>
<meta property="og:title" content="<?= e($seoTitle) ?>">
<meta property="og:description" content="<?= e($seoDesc) ?>">
<?php if ($ogImage !== ''): ?><meta property="og:image" content="<?= e($ogImage) ?>"><?php endif; ?>
<link rel="stylesheet" href="/theme/style.css?m=1&v=1.0.0">
<?php if (!empty(multilang_config()['langs'])): ?>
<?php foreach (multilang_config()['langs'] as $lc): ?>
<link rel="alternate" hreflang="<?= e($lc) ?>" href="<?= e(lang_url($lc)) ?>">
<?php endforeach; ?>
<?php endif; ?>
</head>
<body class="mz-body<?= $isHome ? ' is-home' : '' ?>">

<header class="mz-head">
  <div class="mz-head-in">
    <a class="mz-brand" href="<?= e(lang_path('/')) ?>">
      <span class="mz-mark"><?= $curLang === 'en' ? 'S' : '书' ?></span>
      <span><b><?= e($siteName) ?></b><small>READING &amp; WRITING</small></span>
    </a>
    <div class="mz-head-act">
      <a class="mz-search-link" href="<?= e(lang_path('/search')) ?>" aria-label="<?= e(sz_t('搜索', 'Search')) ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="M15.5 15.5 21 21"></path></svg>
      </a>
      <button class="mz-menu-btn" type="button" data-mz-menu aria-label="<?= e(sz_t('打开菜单', 'Open menu')) ?>" aria-expanded="false">
        <span></span><span></span>
      </button>
    </div>
  </div>
</header>

<nav class="mz-drawer" id="mzDrawer" aria-label="<?= e(sz_t('手机导航', 'Mobile navigation')) ?>">
  <div class="mz-drawer-head">
    <span><?= e(sz_t('目录', 'Contents')) ?></span>
    <button type="button" data-mz-close aria-label="<?= e(sz_t('关闭', 'Close')) ?>">×</button>
  </div>
  <div class="mz-drawer-links">
    <a href="<?= e(lang_path('/')) ?>"<?= $isHome ? ' class="now"' : '' ?>><?= e(sz_t('首页', 'Home')) ?><em>01</em></a>
    <?php $mi = 2; foreach ($mCats as $c): ?>
      <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"<?= $activeCat === (int) $c['id'] ? ' class="now"' : '' ?>><?= e(sz_cat_name($c)) ?><em><?= e(str_pad((string) $mi++, 2, '0', STR_PAD_LEFT)) ?></em></a>
    <?php endforeach; ?>
    <?php if ($aboutPage): ?>
      <a href="<?= e(lang_path('/p/' . $aboutPage['slug'])) ?>"><?= e(ml($aboutPage, 'title')) ?><em><?= e(str_pad((string) $mi++, 2, '0', STR_PAD_LEFT)) ?></em></a>
    <?php endif; ?>
  </div>
  <form class="mz-drawer-search" action="<?= e(lang_path('/search')) ?>" method="get" role="search">
    <input type="search" name="kw" value="<?= e($kw ?? '') ?>" placeholder="<?= e(sz_t('搜索文章', 'Search posts')) ?>">
    <button type="submit"><?= e(sz_t('搜索', 'Search')) ?></button>
  </form>
  <div class="mz-drawer-lang"><?= lang_switcher('mz-lang') ?></div>
  <div class="mz-drawer-member"><a href="/member/center"><?= e(sz_t('会员中心', 'Member center')) ?> →</a></div>
</nav>
<div class="mz-mask" data-mz-mask></div>

<main class="mz-main">
