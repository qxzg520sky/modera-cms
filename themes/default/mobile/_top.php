<?php
$siteName = setting('site_name', '企业网站');
$logo     = setting('site_logo', '');
$slogan   = setting('site_slogan', '');
$seoTitle = $seoTitle ?? $siteName;
$nav      = $nav ?? [];
$pages    = $pages ?? \think\facade\Db::name('page')->where('status', 1)->order('id asc')->select()->toArray();
$phone    = setting('site_phone', '');
$curLang  = current_lang();
?>
<!DOCTYPE html>
<html lang="<?= e(str_replace('_', '-', $curLang)) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="format-detection" content="telephone=no">
<title><?= e($seoTitle) ?></title>
<meta name="keywords" content="<?= e($seoKeywords ?? setting('site_keywords', '')) ?>">
<meta name="description" content="<?= e($seoDesc ?? setting('site_description', '')) ?>">
<link rel="stylesheet" href="/theme/style.css?m=1">
<?php foreach (multilang_config()['langs'] as $lc): ?>
<link rel="alternate" hreflang="<?= e($lc) ?>" href="<?= e(lang_url($lc)) ?>">
<?php endforeach; ?>
</head>
<body class="mbody">

<header class="mtop">
  <input type="checkbox" id="mnav" class="mnav-toggle" hidden>
  <a class="mbrand" href="<?= e(lang_path('/')) ?>">
    <?php if ($logo): ?><img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>">
    <?php else: ?><span class="mbrand-txt"><?= e($siteName) ?></span><?php endif; ?>
  </a>
  <label for="mnav" class="mburger" aria-label="<?= e(lang('打开菜单')) ?>"><i></i><i></i><i></i></label>

  <nav class="mnav">
    <div class="mnav-head"><?= e($siteName) ?></div>
    <a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a>
    <?php foreach ($nav as $c): ?>
      <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e($c['name']) ?></a>
      <?php if (!empty($c['children'])): ?>
        <div class="mnav-sub">
          <?php foreach ($c['children'] as $ch): ?>
            <a href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
    <?php if (!empty($pages)): ?>
      <div class="mnav-split"></div>
      <?php foreach ($pages as $p): ?>
        <a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?></a>
      <?php endforeach; ?>
    <?php endif; ?>
    <?php if (!empty(multilang_config()['langs'])): ?>
      <div class="mnav-lang"><?= lang_switcher('mlang') ?></div>
    <?php endif; ?>
    <?php if ($phone): ?>
      <div class="mnav-foot"><a href="tel:<?= e($phone) ?>"><?= e(lang('拨打')) ?> <?= e($phone) ?></a></div>
    <?php endif; ?>
  </nav>
  <label for="mnav" class="mmask" aria-hidden="true"></label>
</header>

<main class="mmain">
