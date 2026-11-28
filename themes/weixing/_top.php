<?php
$siteName = setting('site_name', '企业网站');
$logo = setting('site_logo', '');
$phone = setting('site_phone', '');
$email = setting('site_email', '');
$address = setting('site_address', '');
$seoTitle = $seoTitle ?? $siteName;
$nav = $nav ?? [];
$pages = $pages ?? \think\facade\Db::name('page')->where('status', 1)->order('id asc')->select()->toArray();
$curLang = current_lang();
$isHome = !isset($cat) && !isset($row);

$activeKey = '';
if (!empty($cat['slug'])) {
    $activeKey = (string) $cat['slug'];
} elseif (!empty($row['slug'])) {
    $activeKey = (string) $row['slug'];
}
$activeMap = [
    'equipment' => 'product',
    'machining' => 'product',
    'service' => 'service',
    'about' => 'about',
    'contact' => 'contact',
];
$activeKey = $activeMap[$activeKey] ?? $activeKey;

$aboutPage = $contactPage = null;
foreach ($pages as $pp) {
    if (($pp['slug'] ?? '') === 'about') { $aboutPage = $pp; }
    if (($pp['slug'] ?? '') === 'contact') { $contactPage = $pp; }
}

$navItems = [];
$navItems[] = ['name' => lang('首页'), 'url' => lang_path('/'), 'key' => 'home', 'now' => $isHome];
foreach ($nav as $c) {
    if (($c['slug'] ?? '') === 'jobs') { continue; }
    $navItems[] = [
        'name' => (string) $c['name'],
        'url' => lang_path('/c/' . $c['slug']),
        'key' => (string) $c['slug'],
        'now' => $activeKey === (string) $c['slug'],
    ];
}
if ($aboutPage) {
    $navItems[] = ['name' => $aboutPage['title'], 'url' => lang_path('/p/' . $aboutPage['slug']), 'key' => 'about', 'now' => $activeKey === 'about'];
}
if ($contactPage) {
    $navItems[] = ['name' => $contactPage['title'], 'url' => lang_path('/p/' . $contactPage['slug']), 'key' => 'contact', 'now' => $activeKey === 'contact'];
}
?>
<!DOCTYPE html>
<html lang="<?= e(str_replace('_', '-', $curLang ?: 'zh-CN')) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?= e($seoTitle) ?></title>
<meta name="keywords" content="<?= e($seoKeywords ?? setting('site_keywords', '')) ?>">
<meta name="description" content="<?= e($seoDesc ?? setting('site_description', '')) ?>">
<link rel="stylesheet" href="/theme/style.css?v=1.0.0">
<?php if (!empty(multilang_config()['langs'])): ?>
<?php foreach (multilang_config()['langs'] as $lc): ?>
<link rel="alternate" hreflang="<?= e($lc) ?>" href="<?= e(lang_url($lc)) ?>">
<?php endforeach; ?>
<?php endif; ?>
</head>
<body class="wx-body<?= $isHome ? ' wx-home' : '' ?>">

<div class="wx-top">
  <div class="wx-inner wx-top-in">
    <a class="wx-logo" href="<?= e(lang_path('/')) ?>" aria-label="<?= e($siteName) ?>">
      <?php if ($logo): ?>
        <img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>">
      <?php else: ?>
        <img src="/theme/logo.png" alt="<?= e($siteName) ?>">
      <?php endif; ?>
      <span><?= e($siteName) ?></span>
    </a>
    <div class="wx-tools">
      <?= lang_switcher('wx-lang') ?>
      <form class="wx-search" action="<?= e(lang_path('/search')) ?>" method="get" role="search">
        <input type="text" name="kw" value="<?= e($kw ?? '') ?>" placeholder="<?= e(lang('搜索')) ?>" aria-label="<?= e(lang('搜索')) ?>">
        <button type="submit" aria-label="<?= e(lang('搜索')) ?>"></button>
      </form>
      <button class="wx-menu-btn" type="button" aria-label="<?= e(lang('打开菜单')) ?>" aria-expanded="false"><span></span><span></span><span></span></button>
    </div>
  </div>
</div>

<nav class="wx-nav" aria-label="<?= e(lang('主导航')) ?>">
  <div class="wx-inner">
    <?php foreach ($navItems as $it): ?>
      <a class="<?= $it['now'] ? 'now' : '' ?>" href="<?= e($it['url']) ?>"><?= e($it['name']) ?></a>
    <?php endforeach; ?>
  </div>
</nav>

<main class="wx-main">
