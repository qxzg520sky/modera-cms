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
$email     = setting('site_email', '');
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

<!-- 顶部黑色联系条 -->
<div class="g2-topbar">
  <div class="g2-wrap g2-topbar-in">
    <?php if ($phone): ?>
      <span class="g2-tb-item">
        <svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.6 5 6.4 6.4l2.2-2.2c.3-.3.7-.4 1-.2 2.3.7 4.6 1 6.9 1 .6 0 1 .4 1 1V21c0 .6-.4 1-1 1C10.4 22 2 13.6 2 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 2.3.3 4.6 1 6.9l1 .3.2.7.1 1-.2z" fill="#ff5e14"/></svg>
        <i><?= e(lang('服务热线')) ?></i><b><?= e($phone) ?></b>
      </span>
    <?php endif; ?>
    <?php if ($email): ?>
      <span class="g2-tb-item">
        <svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="#ff5e14" stroke-width="1.8"/><path d="M4 7l8 6 8-6" fill="none" stroke="#ff5e14" stroke-width="1.8"/></svg>
        <i><?= e(lang('邮箱')) ?></i><b><?= e($email) ?></b>
      </span>
    <?php endif; ?>
    <span class="g2-tb-item g2-tb-hours">
      <svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="#ff5e14" stroke-width="1.8"/><path d="M12 7v5l3.5 2" fill="none" stroke="#ff5e14" stroke-width="1.8"/></svg>
      <i><?= e(lang('工作时间')) ?></i><b>Mon - Fri : 08:30 - 18:00</b>
    </span>
    <div class="g2-tb-right"><?= lang_switcher('g2-lang') ?></div>
  </div>
</div>

<!-- 白色主导航 -->
<header class="g2-nav" id="g2Nav">
  <div class="g2-wrap g2-nav-in">
    <a class="g2-logo" href="<?= e(lang_path('/')) ?>">
      <?php if ($logo): ?><img src="<?= e($logo) ?>" alt="<?= e($siteName) ?>">
      <?php else: ?><span class="g2-logo-mark"><b>鼎</b></span><?php endif; ?>
      <?php if (!$logo): ?>
        <span class="g2-logo-txt"><b><?= e($siteName) ?></b><?php if ($slogan): ?><i><?= e($slogan) ?></i><?php endif; ?></span>
      <?php endif; ?>
    </a>

    <button class="g2-burger" type="button" id="g2Burger" aria-expanded="false" aria-label="<?= e(lang('菜单')) ?>">
      <span></span><span></span><span></span>
    </button>

    <ul class="g2-menu" id="g2Menu">
      <li class="on"><a href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a></li>
      <?php foreach ($nav as $c): ?>
        <li class="<?= !empty($c['children']) ? 'has-sub' : '' ?>">
          <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e($c['name']) ?></a>
          <?php if (!empty($c['children'])): ?>
            <ul class="g2-sub">
              <li><a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e(lang('查看全部')) ?></a></li>
              <?php foreach ($c['children'] as $ch): ?>
                <li><a href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?></a></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
      <?php if (!empty($pages)): ?>
        <li class="has-sub">
          <a href="<?= e(lang_path('/p/' . $pages[0]['slug'])) ?>"><?= e(lang('关于我们')) ?></a>
          <ul class="g2-sub">
            <?php foreach ($pages as $p): ?>
              <li><a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </li>
      <?php endif; ?>
      <li><a href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('联系我们')) ?></a></li>
    </ul>

    <div class="g2-nav-act">
      <form class="g2-search" action="<?= e(lang_path('/search')) ?>" method="get">
        <input type="text" name="kw" placeholder="<?= e(lang('搜索')) ?>" value="<?= e((string) ($_GET['kw'] ?? '')) ?>">
        <button type="submit" aria-label="<?= e(lang('搜索')) ?>">
          <svg viewBox="0 0 24 24" width="17" height="17"><circle cx="10.5" cy="10.5" r="6.5" stroke="#152035" stroke-width="2" fill="none"/><path d="M15.5 15.5l4.5 4.5" stroke="#152035" stroke-width="2" fill="none"/></svg>
        </button>
      </form>
      <a class="g2-cta" href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('获取报价')) ?></a>
    </div>
  </div>
</header>

<main class="g2-main">
