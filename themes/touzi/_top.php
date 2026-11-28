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

$activeKey = '';
if (!empty($cat['slug'])) {
    $activeKey = (string) $cat['slug'];
} elseif (!empty($row['slug'])) {
    $activeKey = (string) $row['slug'];
}
$activeMap = [
    'equipment' => 'product',
    'machining' => 'product',
    'solution'  => 'product',
    'service'   => 'product',
    'about'     => 'about',
    'contact'   => 'about',
];
$activeKey = $activeMap[$activeKey] ?? $activeKey;

$aboutPage = $contactPage = $culturePage = null;
foreach ($pages as $pp) {
    if ($pp['slug'] === 'about')   { $aboutPage = $pp; }
    if ($pp['slug'] === 'contact') { $contactPage = $pp; }
    if ($pp['slug'] === 'culture') { $culturePage = $pp; }
}

$catBySlug = [];
foreach ($nav as $c) {
    $catBySlug[$c['slug']] = $c;
}
$productCat  = $catBySlug['product'] ?? null;
$solutionCat = $catBySlug['solution'] ?? null;
$newsCat     = $catBySlug['news'] ?? null;
$serviceCat  = $catBySlug['service'] ?? null;
$jobsCat     = $catBySlug['jobs'] ?? null;
$aboutUrl    = lang_path('/p/' . ($aboutPage['slug'] ?? 'about'));
$contactUrl  = lang_path('/p/' . ($contactPage['slug'] ?? 'contact'));
$cultureUrl  = lang_path('/p/' . ($culturePage['slug'] ?? 'culture'));
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
<body class="tz-body">

<header class="tz-header" id="tzHeader">
  <div class="tz-container tz-head-in">
    <a class="tz-logo" href="<?= e(lang_path('/')) ?>" aria-label="<?= e($siteName) ?>">
      <?php if ($logo): ?>
        <img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>">
      <?php else: ?>
        <span class="tz-logo-badge">M</span>
      <?php endif; ?>
      <span class="tz-logo-txt">
        <b><?= e($siteName) ?></b>
        <?php if ($slogan): ?><i><?= e($slogan) ?></i><?php endif; ?>
      </span>
    </a>

    <nav class="tz-nav" aria-label="<?= e(lang('菜单')) ?>">
      <a class="<?= $activeKey === '' ? 'on' : '' ?>" href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a>
      <?php if ($productCat): ?>
        <a class="<?= $activeKey === 'product' ? 'on' : '' ?>" href="<?= e(lang_path('/c/' . $productCat['slug'])) ?>"><?= e(lang('业务介绍')) ?></a>
      <?php endif; ?>
      <?php if ($newsCat): ?>
        <div class="tz-nav-drop">
          <a class="<?= $activeKey === 'news' ? 'on' : '' ?>" href="<?= e(lang_path('/c/news')) ?>"><?= e(lang('媒体中心')) ?><i class="tz-caret"></i></a>
          <div class="tz-drop-menu">
            <a href="<?= e(lang_path('/c/news')) ?>"><?= e(lang('新闻动态')) ?></a>
            <a href="<?= e(lang_path('/#perspective')) ?>"><?= e(lang('集团视角')) ?></a>
          </div>
        </div>
        <a class="<?= $activeKey === 'perspective' ? 'on' : '' ?>" href="<?= e(lang_path('/#perspective')) ?>"><?= e(lang('集团视角')) ?></a>
      <?php endif; ?>
      <a class="<?= $activeKey === 'culture' ? 'on' : '' ?>" href="<?= e($cultureUrl) ?>"><?= e(lang('集团学院')) ?></a>
      <?php if ($jobsCat): ?>
        <a class="<?= $activeKey === 'jobs' ? 'on' : '' ?>" href="<?= e(lang_path('/c/jobs')) ?>"><?= e(lang('招贤纳士')) ?></a>
      <?php endif; ?>
      <div class="tz-nav-drop">
        <a class="<?= $activeKey === 'about' ? 'on' : '' ?>" href="<?= e($aboutUrl) ?>"><?= e(lang('关于我们')) ?><i class="tz-caret"></i></a>
        <div class="tz-drop-menu">
          <a href="<?= e($aboutUrl) ?>"><?= e(lang('公司介绍')) ?></a>
          <a href="<?= e(lang_path('/#presence')) ?>"><?= e(lang('企业风采')) ?></a>
          <a href="<?= e($contactUrl) ?>"><?= e(lang('联系我们')) ?></a>
        </div>
      </div>
    </nav>

    <?= lang_switcher('tz-lang') ?>

    <button class="tz-burger" type="button" id="tzBurger" aria-label="<?= e(lang('打开菜单')) ?>" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>

  <div class="tz-mobile" id="tzMobile">
    <div class="tz-mobile-in">
      <a class="<?= $activeKey === '' ? 'on' : '' ?>" href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a>
      <?php if ($productCat): ?><a class="<?= $activeKey === 'product' ? 'on' : '' ?>" href="<?= e(lang_path('/c/product')) ?>"><?= e(lang('业务介绍')) ?></a><?php endif; ?>
      <?php if ($solutionCat): ?><a href="<?= e(lang_path('/c/solution')) ?>"><?= e(lang('解决方案')) ?></a><?php endif; ?>
      <?php if ($newsCat): ?><a class="<?= $activeKey === 'news' ? 'on' : '' ?>" href="<?= e(lang_path('/c/news')) ?>"><?= e(lang('新闻动态')) ?></a><?php endif; ?>
      <a href="<?= e(lang_path('/#perspective')) ?>"><?= e(lang('集团视角')) ?></a>
      <a class="<?= $activeKey === 'culture' ? 'on' : '' ?>" href="<?= e($cultureUrl) ?>"><?= e(lang('集团学院')) ?></a>
      <?php if ($jobsCat): ?><a class="<?= $activeKey === 'jobs' ? 'on' : '' ?>" href="<?= e(lang_path('/c/jobs')) ?>"><?= e(lang('招贤纳士')) ?></a><?php endif; ?>
      <a class="<?= $activeKey === 'about' ? 'on' : '' ?>" href="<?= e($aboutUrl) ?>"><?= e(lang('关于我们')) ?></a>
      <a href="<?= e($contactUrl) ?>"><?= e(lang('联系我们')) ?></a>
      <div class="tz-mobile-foot">
        <a href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', (string) $phone)) ?>"><?= e(lang('电话')) ?> <?= e($phone) ?></a>
        <?= lang_switcher('tz-lang tz-lang-m') ?>
      </div>
    </div>
  </div>
</header>
<main class="tz-main">
