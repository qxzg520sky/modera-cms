<?php
$siteName  = setting('site_name', '企业网站');
$logo      = setting('site_logo', '');
$slogan    = setting('site_slogan', '');
$seoTitle  = $seoTitle ?? $siteName;
$nav       = $nav ?? [];
$pages     = $pages ?? \think\facade\Db::name('page')->where('status', 1)->order('id asc')->select()->toArray();
$curLang   = current_lang();
$pageTitle = $pageTitle ?? '';
$phone     = setting('site_phone', '');
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

<div class="gt-top">
  <div class="gt-wrap gt-top-in">
    <a class="gt-logo" href="<?= e(lang_path('/')) ?>">
      <?php if ($logo): ?><img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>"><?php else: ?><span class="gt-logo-mark">钢</span><?php endif; ?>
      <span class="gt-logo-txt">
        <b><?= e($siteName) ?></b>
        <?php if ($slogan): ?><i><?= e($slogan) ?></i><?php endif; ?>
      </span>
    </a>
    <div class="gt-hotline">
      <svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.6 5 6.4 6.4l2.2-2.2c.3-.3.7-.4 1-.2 2.3.7 4.6 1 6.9 1 .6 0 1 .4 1 1V21c0 .6-.4 1-1 1C10.4 22 2 13.6 2 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 2.3.3 4.6 1 6.9 1 .3.2.7.1 1-.2z" fill="#0887e7"/></svg>
      <span class="gt-hotline-txt"><?= e(lang('全国服务热线')) ?><strong><?= e($phone) ?></strong></span>
    </div>
  </div>
</div>

<div class="gt-nav" id="gtNav">
  <div class="gt-wrap gt-nav-in">
    <button class="gt-nav-toggle" type="button" id="gtNavToggle" aria-expanded="false">
      <svg viewBox="0 0 24 24" width="18" height="18"><path d="M3 6h18M3 12h18M3 18h18" stroke="#fff" stroke-width="2" fill="none"/></svg>
      <?= e(lang('网站栏目')) ?>
    </button>
    <ul class="gt-menu" id="gtMenu">
      <li class="on"><a href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a></li>
      <?php foreach ($nav as $c): ?>
        <li class="<?= !empty($c['children']) ? 'has-sub' : '' ?>">
          <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e($c['name']) ?></a>
          <?php if (!empty($c['children'])): ?>
            <div class="gt-sub">
              <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e(lang('查看全部')) ?></a>
              <?php foreach ($c['children'] as $ch): ?>
                <a href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
      <?php if (!empty($pages)): ?>
        <li class="has-sub">
          <a href="<?= e(lang_path('/p/' . $pages[0]['slug'])) ?>"><?= e(lang('关于我们')) ?></a>
          <div class="gt-sub">
            <?php foreach ($pages as $p): ?>
              <a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?></a>
            <?php endforeach; ?>
          </div>
        </li>
      <?php endif; ?>
      <li class="has-sub">
        <a href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('联系我们')) ?></a>
        <div class="gt-sub">
          <a href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('联系方式')) ?></a>
          <a href="<?= e(lang_path('/search')) ?>"><?= e(lang('站内搜索')) ?></a>
        </div>
      </li>
    </ul>
    <form class="gt-search" action="<?= e(lang_path('/search')) ?>" method="get">
      <input type="text" name="kw" placeholder="<?= e(lang('输入关键词搜索')) ?>" value="<?= e((string) ($_GET['kw'] ?? '')) ?>">
      <button type="submit" aria-label="<?= e(lang('搜索')) ?>">
        <svg viewBox="0 0 24 24" width="16" height="16"><circle cx="10" cy="10" r="6.5" stroke="#fff" stroke-width="2" fill="none"/><path d="M15 15l5 5" stroke="#fff" stroke-width="2" fill="none"/></svg>
      </button>
    </form>
    <?= lang_switcher('gt-lang') ?>
  </div>
</div>

<main class="gt-main">
