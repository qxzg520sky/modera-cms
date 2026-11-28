<?php
$siteName  = setting('site_name', '企业网站');
$logo      = setting('site_logo', '');
$phone     = setting('site_phone', '');
$email     = setting('site_email', '');
$address   = setting('site_address', '');
$seoTitle  = $seoTitle ?? $siteName;
$nav       = $nav ?? [];
$pages     = $pages ?? \think\facade\Db::name('page')->where('status', 1)->order('id asc')->select()->toArray();
$curLang   = current_lang();

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
    'service'   => 'service',
    'about'     => 'about',
    'contact'   => 'contact',
];
$activeKey = $activeMap[$activeKey] ?? $activeKey;

$aboutPage = $contactPage = null;
foreach ($pages as $pp) {
    if ($pp['slug'] === 'about')   { $aboutPage = $pp; }
    if ($pp['slug'] === 'contact') { $contactPage = $pp; }
}

// 主导航：首页 + 栏目（招聘放页脚） + 关于/联系
$navItems = [];
$navItems[] = ['name' => lang('首页'), 'url' => lang_path('/'), 'key' => 'home', 'now' => $isHome];
foreach ($nav as $c) {
    if (($c['slug'] ?? '') === 'jobs') {
        continue;
    }
    $navItems[] = [
        'name' => (string) $c['name'],
        'url'  => lang_path('/c/' . $c['slug']),
        'key'  => (string) $c['slug'],
        'now'  => $activeKey === (string) $c['slug'],
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
<link rel="stylesheet" href="/theme/style.css?v=1.0.3">
<script src="/theme/jquery.js"></script>
<?php if (!empty(multilang_config()['langs'])): ?>
<?php foreach (multilang_config()['langs'] as $lc): ?>
<link rel="alternate" hreflang="<?= e($lc) ?>" href="<?= e(lang_url($lc)) ?>">
<?php endforeach; ?>
<?php endif; ?>
</head>
<body class="wk-body<?= $isHome ? ' wk-home' : '' ?>">

<div class="main">
<div class="header<?= $isHome ? '' : ' h_fix' ?>">
  <div class="head clearfix">
    <div class="logo">
      <a class="wk-logo" href="<?= e(lang_path('/')) ?>" aria-label="<?= e($siteName) ?>">
        <?php if ($logo): ?>
          <img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>">
        <?php else: ?>
          <img src="/theme/logo.png" alt="<?= e($siteName) ?>">
        <?php endif; ?>
        <span class="wk-logo-name"><?= e($siteName) ?></span>
      </a>
    </div>
    <div class="nav_m">
      <div class="n_icon"><?= e(lang('导航栏')) ?></div>
      <ul class="nav clearfix gt-menu">
        <?php foreach ($navItems as $it): ?>
          <li class="<?= $it['now'] ? 'now' : '' ?>"><a href="<?= e($it['url']) ?>"><?= e($it['name']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="wk-tools">
      <?= lang_switcher('wk-lang') ?>
      <form class="search" action="<?= e(lang_path('/search')) ?>" method="get" role="search">
        <input type="text" name="kw" value="<?= e($kw ?? '') ?>" placeholder="<?= e(lang('搜索')) ?>" aria-label="<?= e(lang('搜索')) ?>">
        <input type="submit" value="" aria-label="<?= e(lang('搜索')) ?>">
      </form>
    </div>
    <button class="wk-menu-btn" type="button" aria-label="<?= e(lang('打开菜单')) ?>" aria-expanded="false"><span></span><span></span><span></span></button>
  </div>
</div>



