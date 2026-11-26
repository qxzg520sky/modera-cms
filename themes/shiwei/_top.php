<?php
$siteName  = setting('site_name', '企业网站');
$logo      = setting('site_logo', '');
$slogan    = setting('site_slogan', '');
$phone     = setting('site_phone', '');
$email     = setting('site_email', '');
$address   = setting('site_address', '');
$seoTitle  = $seoTitle ?? $siteName;
$nav       = $nav ?? [];
$pages     = $pages ?? \think\facade\Db::name('page')->where('status', 1)->order('id asc')->select()->toArray();
$curLang   = current_lang();
$pageTitle = $pageTitle ?? '';

// 当前导航高亮：子栏目归到父栏目
$activeKey = '';
if (!empty($cat['slug'])) {
    $activeKey = (string) $cat['slug'];
} elseif (!empty($row['slug'])) {
    $activeKey = (string) $row['slug'];
} elseif (!empty($pageTitle)) {
    $activeKey = '';
}
$activeMap = ['equipment' => 'product', 'machining' => 'product'];
$activeKey = $activeMap[$activeKey] ?? $activeKey;

$aboutPage   = null;
$contactPage = null;
foreach ($pages as $pp) {
    if ($pp['slug'] === 'about')   { $aboutPage = $pp; }
    if ($pp['slug'] === 'contact') { $contactPage = $pp; }
}
$catBySlug = [];
foreach ($nav as $c) {
    $catBySlug[$c['slug']] = $c;
}
$menuCats = [];
foreach (['solution', 'product', 'service', 'news'] as $slug) {
    if (!empty($catBySlug[$slug])) {
        $menuCats[] = $catBySlug[$slug];
    }
}
$contactUrl = lang_path('/p/' . ($contactPage['slug'] ?? 'contact'));
?>
<!DOCTYPE html>
<html lang="<?= e(str_replace('_', '-', $curLang)) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?= e($seoTitle) ?></title>
<meta name="keywords" content="<?= e($seoKeywords ?? setting('site_keywords', '')) ?>">
<meta name="description" content="<?= e($seoDesc ?? setting('site_description', '')) ?>">
<link rel="stylesheet" href="/theme/style.css?v=1.0.2">
<?php if (!empty(multilang_config()['langs'])): ?>
<?php foreach (multilang_config()['langs'] as $lc): ?>
<link rel="alternate" hreflang="<?= e($lc) ?>" href="<?= e(lang_url($lc)) ?>">
<?php endforeach; ?>
<?php endif; ?>
</head>
<body>

<header class="sw-header" id="swHeader">
  <div class="sw-head">
    <a class="sw-logo" href="<?= e(lang_path('/')) ?>">
      <?php if ($logo): ?>
        <img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>">
      <?php else: ?>
        <span class="sw-logo-mark">S</span>
      <?php endif; ?>
      <span class="sw-logo-txt"><b><?= e($siteName) ?></b><?php if ($slogan): ?><i><?= e($slogan) ?></i><?php endif; ?></span>
    </a>

    <nav class="sw-nav" aria-label="<?= e(lang('菜单')) ?>">
      <a class="<?= $activeKey === '' ? 'on' : '' ?>" href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a>
      <?php if ($aboutPage): ?>
        <a class="<?= $activeKey === 'about' ? 'on' : '' ?>" href="<?= e(lang_path('/p/' . $aboutPage['slug'])) ?>"><?= e(lang('关于我们')) ?></a>
      <?php endif; ?>
      <?php foreach ($menuCats as $c): ?>
        <a class="<?= $activeKey === $c['slug'] ? 'on' : '' ?>" href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
      <?php if ($contactPage): ?>
        <a class="<?= $activeKey === 'contact' ? 'on' : '' ?>" href="<?= e(lang_path('/p/' . $contactPage['slug'])) ?>"><?= e(lang('联系我们')) ?></a>
      <?php endif; ?>
    </nav>

    <form class="sw-search" action="<?= e(lang_path('/search')) ?>" method="get" role="search">
      <input type="text" name="kw" value="<?= e((string) ($_GET['kw'] ?? '')) ?>" placeholder="<?= e(lang('搜索')) ?>" aria-label="<?= e(lang('搜索')) ?>">
      <button type="submit" aria-label="<?= e(lang('搜索')) ?>">
        <svg viewBox="0 0 24 24" width="17" height="17" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.2" fill="none" stroke="currentColor" stroke-width="1.9"/><path d="M15.4 15.4 20 20" fill="none" stroke="currentColor" stroke-width="1.9"/></svg>
      </button>
    </form>

    <?= lang_switcher('sw-lang') ?>

    <button class="sw-burger" type="button" id="swBurger" aria-label="<?= e(lang('打开菜单')) ?>" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>

  <div class="sw-mobile" id="swMobile">
    <div class="sw-mobile-in">
      <a class="<?= $activeKey === '' ? 'on' : '' ?>" href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a>
      <?php if ($aboutPage): ?><a class="<?= $activeKey === 'about' ? 'on' : '' ?>" href="<?= e(lang_path('/p/' . $aboutPage['slug'])) ?>"><?= e(lang('关于我们')) ?></a><?php endif; ?>
      <?php foreach ($menuCats as $c): ?>
        <a class="<?= $activeKey === $c['slug'] ? 'on' : '' ?>" href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
      <?php if ($contactPage): ?><a class="<?= $activeKey === 'contact' ? 'on' : '' ?>" href="<?= e(lang_path('/p/' . $contactPage['slug'])) ?>"><?= e(lang('联系我们')) ?></a><?php endif; ?>
      <div class="sw-mobile-foot">
        <a href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', (string) $phone)) ?>"><?= e(lang('电话')) ?> <?= e($phone) ?></a>
        <?= lang_switcher('sw-lang sw-lang-m') ?>
      </div>
    </div>
  </div>
</header>
<main class="sw-main">

