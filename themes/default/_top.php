<?php
$siteName   = setting('site_name', '企业网站');
$logo       = setting('site_logo', '');
$slogan     = setting('site_slogan', '');
$seoTitle   = $seoTitle ?? $siteName;
$nav        = $nav ?? [];
$pages      = $pages ?? \think\facade\Db::name('page')->where('status', 1)->order('id asc')->select()->toArray();
$curLang    = current_lang();
?>
<!DOCTYPE html>
<html lang="<?= e(str_replace('_', '-', $curLang)) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($seoTitle) ?></title>
<meta name="keywords" content="<?= e($seoKeywords ?? setting('site_keywords', '')) ?>">
<meta name="description" content="<?= e($seoDesc ?? setting('site_description', '')) ?>">
<link rel="stylesheet" href="/theme/style.css">
<?php foreach (multilang_config()['langs'] as $lc): ?>
<link rel="alternate" hreflang="<?= e($lc) ?>" href="<?= e(lang_url($lc)) ?>">
<?php endforeach; ?>
</head>
<body>
<header class="hdr">
  <div class="wrap hdr-in">
    <a class="brand" href="<?= e(lang_path('/')) ?>">
      <?php if ($logo): ?><img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>">
      <?php else: ?><span class="brand-mark">企</span><?php endif; ?>
      <span class="brand-txt">
        <b><?= e($siteName) ?></b>
        <?php if ($slogan): ?><i><?= e($slogan) ?></i><?php endif; ?>
      </span>
    </a>
    <nav class="nav">
      <a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a>
      <?php foreach ($nav as $c): ?>
        <?php if (!empty($c['children'])): ?>
          <div class="nav-sub-wrap">
            <a class="nav-sub-toggle" href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e($c['name']) ?><span class="caret">▾</span></a>
            <div class="nav-sub">
              <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e(lang('查看全部')) ?></a>
              <?php foreach ($c['children'] as $ch): ?>
                <a href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php else: ?>
          <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e($c['name']) ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php if (!empty($pages)): ?>
        <div class="nav-sub-wrap">
          <a class="nav-sub-toggle" href="<?= e(lang_path('/p/' . $pages[0]['slug'])) ?>"><?= e(lang('关于公司')) ?><span class="caret">▾</span></a>
          <div class="nav-sub">
            <?php foreach ($pages as $p): ?>
              <a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </nav>
    <?= lang_switcher() ?>
    <a class="tel" href="tel:<?= e(setting('site_phone', '')) ?>"><?= e(setting('site_phone', '联系我们')) ?></a>
  </div>
</header>
<main class="main">
