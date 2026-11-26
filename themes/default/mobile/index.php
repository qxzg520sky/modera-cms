<?php include __DIR__ . '/_top.php'; ?>

<section class="mhero">
  <h1><?= e($slogan ?: $siteName) ?></h1>
  <p><?= e(setting('site_description', '')) ?></p>
  <div class="mhero-btns">
    <a class="mbtn primary" href="<?= e(lang_path('/p/about')) ?>"><?= e(lang('了解我们')) ?></a>
    <a class="mbtn" href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('联系我们')) ?></a>
  </div>
</section>

<section class="msec">
  <div class="msec-head"><h2><?= e(lang('核心业务')) ?></h2><span><?= e(lang('共')) ?> <?= count($nav) ?> <?= e(lang('个栏目')) ?></span></div>
  <div class="mgrid">
    <?php foreach (array_slice($nav, 0, 6) as $c): ?>
      <a class="mtile" href="<?= e(lang_path('/c/' . $c['slug'])) ?>">
        <h3><?= e($c['name']) ?></h3>
        <p><?= e(mb_substr((string) ($c['seo_description'] ?? ''), 0, 28)) ?></p>
        <?php if (!empty($c['children'])): ?>
          <span class="mtile-sub"><?= e(implode(' · ', array_column($c['children'], 'name'))) ?></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="msec">
  <div class="msec-head"><h2><?= e(lang('最新动态')) ?></h2><span><?= date('Y-m-d') ?></span></div>
  <div class="mlist">
    <?php foreach ($articles as $a): ?>
      <a class="mitem" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
        <div class="mitem-time"><?= e(date('m-d', strtotime((string) $a['create_time']))) ?></div>
        <div class="mitem-body">
          <h3><?= e($a['title']) ?></h3>
          <p><?= e(mb_substr(strip_tags((string) $a['summary']), 0, 46)) ?></p>
        </div>
      </a>
    <?php endforeach; ?>
    <?php if (!$articles): ?><div class="mempty"><?= e(lang('暂无内容')) ?></div><?php endif; ?>
  </div>
  <?php if (!empty($nav[0])): ?><a class="mmore" href="<?= e(lang_path('/c/' . $nav[0]['slug'])) ?>"><?= e(lang('查看全部动态 →')) ?></a><?php endif; ?>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
