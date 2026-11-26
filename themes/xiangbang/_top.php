<?php
include __DIR__ . '/_helpers.php';

$siteName  = setting('site_name', '企业网站');
$slogan    = (string) setting('site_slogan', '');
$logo      = (string) setting('site_logo', '');
$phone     = (string) setting('site_phone', '');
$email     = (string) setting('site_email', '');
$address   = (string) setting('site_address', '');
$seoTitle  = $seoTitle ?? $siteName;
$nav       = $nav ?? [];
$pages     = $pages ?? \think\facade\Db::name('page')->where('status', 1)->order('id asc')->select()->toArray();
$curLang   = current_lang();

$isHome = !isset($cat) && !isset($row);

$aboutPage = $contactPage = $jobsPage = null;
foreach ($pages as $pp) {
    if (($pp['slug'] ?? '') === 'about')   { $aboutPage = $pp; }
    if (($pp['slug'] ?? '') === 'contact') { $contactPage = $pp; }
    if (($pp['slug'] ?? '') === 'jobs')    { $jobsPage = $pp; }
}

$activeSlug = '';
if (!empty($cat['slug'])) {
    $activeSlug = (string) $cat['slug'];
    if ((int) ($cat['pid'] ?? 0) > 0) {
        foreach ($nav as $top) {
            if ((int) $top['id'] === (int) $cat['pid']) {
                $activeSlug = (string) $top['slug'];
                break;
            }
        }
    }
} elseif (!empty($row['slug']) && isset($row['content'])) {
    $activeSlug = (string) $row['slug'];
}

$contactUrl = $contactPage ? xb_page_url((string) $contactPage['slug']) : lang_path('/');
$shopEnabled = false;
try {
    $shopEnabled = (bool) \think\facade\Db::name('plugin')->where('name', 'shop')->where('enabled', 1)->find();
} catch (\Throwable $e) {
}
$shopUrl = '/shop';
$shopNow = str_starts_with(trim((string) request()->pathinfo(), '/'), 'shop');
$navItems = [];
$navItems[] = ['name' => lang('首页'), 'url' => lang_path('/'), 'now' => $isHome];
foreach ($nav as $c) {
    if (($c['slug'] ?? '') === 'jobs') {
        continue;
    }
    $navItems[] = [
        'name' => (string) ml($c, 'name'),
        'url'  => xb_cat_url((string) $c['slug']),
        'now'  => $activeSlug === (string) $c['slug'],
    ];
}
if ($shopEnabled) {
    array_splice($navItems, 2, 0, [[
        'name' => lang('商城', '商城'),
        'url'  => $shopUrl,
        'now'  => $shopNow,
    ]]);
}
if ($aboutPage) {
    $navItems[] = [
        'name' => (string) ml($aboutPage, 'title'),
        'url'  => xb_page_url((string) $aboutPage['slug']),
        'now'  => $activeSlug === (string) $aboutPage['slug'],
    ];
}
if ($contactPage) {
    $navItems[] = [
        'name' => (string) ml($contactPage, 'title'),
        'url'  => $contactUrl,
        'now'  => $activeSlug === (string) $contactPage['slug'],
    ];
}
$brandLogo = $logo !== '' ? $logo : '/theme/logo.png';
?>
<!DOCTYPE html>
<html lang="<?= e(str_replace('_', '-', $curLang ?: 'zh-CN')) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?= e($seoTitle) ?></title>
<meta name="keywords" content="<?= e($seoKeywords ?? setting('site_keywords', '')) ?>">
<meta name="description" content="<?= e($seoDesc ?? setting('site_description', '')) ?>">
<meta name="theme-color" content="#15251b">
<link rel="icon" href="<?= e($brandLogo) ?>">
<link rel="stylesheet" href="/theme/style.css?v=1.1.4">
<noscript><style>[data-reveal]{opacity:1;transform:none}</style></noscript>
<?php if (!empty(multilang_config()['langs'])): ?>
<?php foreach (multilang_config()['langs'] as $lc): ?>
<link rel="alternate" hreflang="<?= e($lc) ?>" href="<?= e(lang_url($lc)) ?>">
<?php endforeach; ?>
<?php endif; ?>
</head>
<body class="<?= $isHome ? 'xb-home' : 'xb-sub' ?>">

<header class="xb-header" id="xbHeader">
  <div class="xb-container xb-header-inner">
    <a class="xb-brand" href="<?= e(lang_path('/')) ?>" aria-label="<?= e($siteName) ?>">
      <img src="<?= e($brandLogo) ?>" alt="<?= e($siteName) ?>">
      <span class="xb-brand-copy">
        <strong><?= e($siteName) ?></strong>
        <?php if ($slogan !== ''): ?><span><?= e($slogan) ?></span><?php endif; ?>
      </span>
    </a>

    <nav class="xb-nav" aria-label="<?= e(lang('主导航', '主导航')) ?>">
      <?php foreach ($navItems as $it): ?>
        <a href="<?= e($it['url']) ?>" class="<?= $it['now'] ? 'on' : '' ?>"<?= $it['now'] ? ' aria-current="page"' : '' ?>><?= e($it['name']) ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="xb-header-tools">
      <div class="g2-tb-right xb-member-slot"></div>
      <?= lang_switcher('xb-lang') ?>
      <?php if ($phone !== ''): ?>
      <a class="xb-head-phone" href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', $phone)) ?>">
        <span><?= e(lang('服务热线', '服务热线')) ?></span>
        <strong><?= e($phone) ?></strong>
      </a>
      <?php endif; ?>
      <a class="xb-head-cta" href="<?= e($contactUrl) ?>"><?= e(lang('获取方案', '获取方案')) ?></a>
    </div>

    <button class="xb-menu-btn" type="button" aria-label="<?= e(lang('打开菜单', '打开菜单')) ?>" aria-expanded="false" aria-controls="xbMobileNav">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<div class="xb-mobile tz-mobile-in" id="xbMobileNav" aria-hidden="true">
  <nav aria-label="<?= e(lang('移动端导航', '移动端导航')) ?>">
    <?php foreach ($navItems as $i => $it): ?>
      <a href="<?= e($it['url']) ?>" class="<?= $it['now'] ? 'on' : '' ?>"><i><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></i><?= e($it['name']) ?></a>
    <?php endforeach; ?>
    <?php if ($jobsPage): ?>
      <a href="<?= e(xb_page_url((string) $jobsPage['slug'])) ?>"><i><?= str_pad((string) (count($navItems) + 1), 2, '0', STR_PAD_LEFT) ?></i><?= e(ml($jobsPage, 'title')) ?></a>
    <?php endif; ?>
  </nav>
  <div class="xb-mobile-foot tz-mobile-foot">
    <?php if ($phone !== ''): ?><a href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', $phone)) ?>"><?= e($phone) ?></a><?php endif; ?>
    <p><?= e(lang('周一至周日 08:00–18:00', '周一至周日 08:00–18:00')) ?></p>
  </div>
</div>

<main id="xbMain">
