<?php include __DIR__ . '/_top.php'; ?>

<section class="hero">
  <div class="wrap">
    <h1><?= e($slogan ?: $siteName) ?></h1>
    <p><?= e(setting('site_description', lang('专业、可靠、值得信赖的企业服务平台'))) ?></p>
    <a class="hero-btn" href="<?= e(lang_path('/p/about')) ?>"><?= e(lang('了解更多')) ?></a>
  </div>
</section>

<section class="wrap sec">
  <div class="sec-head"><h2><?= e(lang('核心业务')) ?></h2><p><?= e(lang('我们提供的服务与解决方案')) ?></p></div>
  <div class="grid">
    <?php foreach (array_slice($nav, 0, 6) as $c): ?>
      <a class="tile" href="<?= e(lang_path('/c/' . $c['slug'])) ?>">
        <div class="tile-ico">◆</div>
        <h3><?= e($c['name']) ?></h3>
        <p><?= e($c['seo_description'] ?: lang('查看该栏目下的全部内容')) ?></p>
        <?php if (!empty($c['children'])): ?>
          <span class="tile-sub"><?= e(implode(' · ', array_column($c['children'], 'name'))) ?></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="wrap sec">
  <div class="sec-head"><h2><?= e(lang('最新动态')) ?></h2><p><?= e(lang('公司新闻与行业资讯')) ?></p></div>
  <div class="news">
    <?php foreach ($articles as $a): ?>
      <a class="news-item" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
        <div class="news-date"><?= e(date('m-d', strtotime((string) $a['create_time']))) ?></div>
        <div class="news-body">
          <h3><?= e($a['title']) ?></h3>
          <p><?= e(mb_substr(strip_tags((string) $a['summary']), 0, 80)) ?></p>
        </div>
        <div class="news-more">→</div>
      </a>
    <?php endforeach; ?>
    <?php if (!$articles): ?><div class="empty"><?= e(lang('暂无内容，请到后台发布文章')) ?></div><?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
