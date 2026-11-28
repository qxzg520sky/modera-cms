<?php
if (!function_exists('theme_en_filter')) {
    function theme_en_filter(string $html): string {
        static $map = null;
        if ($map === null) { $map = include __DIR__ . '/_en.php'; }
        return preg_replace_callback('/>([^<>]*)</u', static function (array $m) use ($map): string {
            $text = $m[1];
            $trim = trim($text);
            if ($trim === '' || !preg_match('/\p{Han}/u', $trim)) { return $m[0]; }
            if (!isset($map[$trim])) { return $m[0]; }
            $pos = strpos($text, $trim);
            return '>' . substr($text, 0, $pos) . $map[$trim] . substr($text, $pos + strlen($trim)) . '<';
        }, $html);
    }
}
if (current_lang() === 'en' && !isset($GLOBALS['__themeOb'])) {
    ob_start('theme_en_filter');
    $GLOBALS['__themeOb'] = ob_get_level();
}
?>
<?php
$siteName = setting('site_name', '企业网站');
$phone = setting('site_phone', '');
$email = setting('site_email', '');
$address = setting('site_address', '');
$seoTitle = $seoTitle ?? $siteName;
$nav = $nav ?? [];
$pages = $pages ?? \think\facade\Db::name('page')->where('status', 1)->order('id asc')->select()->toArray();
$isHome = !isset($cat) && !isset($row);
$activeKey = '';
if (!empty($cat['slug'])) {
    $activeKey = (string) $cat['slug'];
} elseif (!empty($row['slug'])) {
    $activeKey = (string) $row['slug'];
}
$activeMap = ['equipment' => 'product', 'machining' => 'product'];
$activeKey = $activeMap[$activeKey] ?? $activeKey;

$aboutPage = $contactPage = null;
foreach ($pages as $pp) {
    if (($pp['slug'] ?? '') === 'about') { $aboutPage = $pp; }
    if (($pp['slug'] ?? '') === 'contact') { $contactPage = $pp; }
}

$navItems = [];
$navItems[] = ['name' => '首页', 'en' => 'HOME', 'url' => lang_path('/'), 'key' => 'home', 'now' => $isHome];
foreach ($nav as $c) {
    if (($c['slug'] ?? '') === 'jobs') { continue; }
    $navItems[] = [
        'name' => (string) $c['name'],
        'en' => strtoupper((string) ($c['slug'] ?? '')),
        'url' => lang_path('/c/' . $c['slug']),
        'key' => (string) $c['slug'],
        'now' => $activeKey === (string) $c['slug'],
    ];
}
if ($aboutPage) {
    $navItems[] = ['name' => $aboutPage['title'], 'en' => 'ABOUT', 'url' => lang_path('/p/' . $aboutPage['slug']), 'key' => 'about', 'now' => $activeKey === 'about'];
}
if ($contactPage) {
    $navItems[] = ['name' => $contactPage['title'], 'en' => 'CONTACT', 'url' => lang_path('/p/' . $contactPage['slug']), 'key' => 'contact', 'now' => $activeKey === 'contact'];
}
$contactUrl = $contactPage ? lang_path('/p/' . $contactPage['slug']) : lang_path('/search');
?>
<!DOCTYPE html>
<html lang="<?= e(str_replace('_', '-', current_lang() ?: 'zh-CN')) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?= e($seoTitle) ?></title>
<meta name="keywords" content="<?= e($seoKeywords ?? setting('site_keywords', '')) ?>">
<meta name="description" content="<?= e($seoDesc ?? setting('site_description', '')) ?>">
<link rel="stylesheet" href="/theme/style.css?v=2.0.0">
<?php if (!empty(multilang_config()['langs'])): ?>
<?php foreach (multilang_config()['langs'] as $lc): ?>
<link rel="alternate" hreflang="<?= e($lc) ?>" href="<?= e(lang_url($lc)) ?>">
<?php endforeach; ?>
<?php endif; ?>
</head>
<body class="cms-theme theme-dianti<?= $isHome ? ' home' : '' ?>">

<div class="topline">
  <div class="wrap topline-in">
    <span>FUJI ELEVATOR · 专业电梯定制服务商</span>
    <div class="g2-tb-right"><?php if ($phone): ?><span class="top-tel">服务热线 <b><?= e($phone) ?></b></span><?php endif; ?><span class="top-year">SINCE 2009</span></div>
  </div>
</div>

<header class="site-header">
  <div class="wrap header-in">
    <a class="brand" href="<?= e(lang_path('/')) ?>" aria-label="<?= e($siteName) ?>">
      <span class="brand-mark"><i></i><i></i><i></i></span>
      <span class="brand-text"><b><?= e($siteName) ?></b><span>FUJI ELEVATOR · 安全抵达每一层</span></span>
    </a>
    <nav class="nav" aria-label="主导航">
      <?php foreach ($navItems as $it): ?>
        <a class="<?= $it['now'] ? 'active' : '' ?>" href="<?= e($it['url']) ?>"><span><?= e($it['name']) ?></span><em><?= e($it['en']) ?></em></a>
      <?php endforeach; ?>
    </nav>
    <div class="header-side">
      <?= lang_switcher('theme-lang') ?>
      <a class="header-cta" href="<?= e($contactUrl) ?>">获取方案 <span class="arrow">↗</span></a>
      <button class="menu-btn" type="button" aria-label="打开菜单" aria-expanded="false"><span></span><span></span><span></span></button>
    </div>
  </div>
  <div class="mobile-nav">
    <div class="mobile-nav-head"><span>菜单 / MENU</span><button class="mobile-close" type="button" aria-label="关闭菜单">×</button></div>
    <?php foreach ($navItems as $it): ?>
      <a href="<?= e($it['url']) ?>"><span><?= e($it['name']) ?></span><em><?= e($it['en']) ?></em></a>
    <?php endforeach; ?>
    <?php if ($phone): ?><a class="m-tel" href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', $phone)) ?>"><?= e($phone) ?></a><?php endif; ?>
  </div>
</header>
<main>
