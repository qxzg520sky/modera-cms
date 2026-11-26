<?php include __DIR__ . '/_top.php'; ?>

<section class="gt-pagehero<?= empty($cat['cover']) ? '' : '' ?>">
  <div class="gt-wrap">
    <h1><?= e($cat['name']) ?></h1>
    <div class="gt-crumb">
      <a href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a>
      <span>/</span>
      <b><?= e($cat['name']) ?></b>
    </div>
  </div>
</section>

<div class="gt-wrap gt-sec gt-cols">
  <?php include __DIR__ . '/_aside.php'; ?>

  <section class="gt-list-wrap">
    <?php if (!empty($subNav)): ?>
      <div class="gt-cattabs">
        <?php foreach ($subNav as $s): ?>
          <a class="gt-cattab <?= (int) ($s['id'] ?? 0) === (int) ($cat['id'] ?? 0) ? 'on' : '' ?>"
             href="<?= e(lang_path('/c/' . $s['slug'])) ?>"><?= e($s['name']) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="gt-list-head">
      <h2><?= e($cat['name']) ?></h2>
      <p><?= e(lang('共')) ?> <?= (int) $total ?> <?= e(lang('条内容')) ?><?= $cat['seo_description'] ? '　' . e(mb_substr(strip_tags((string) $cat['seo_description']), 0, 60)) : '' ?></p>
    </div>

    <div class="gt-list">
      <?php foreach ($articles as $a): ?>
        <a class="gt-li" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
          <div class="gt-li-img">
            <?php if ($a['cover']): ?><img src="<?= e($a['cover']) ?>" alt="<?= e($a['title']) ?>" loading="lazy">
            <?php else: ?><span class="gt-ph"><?= e(mb_substr($a['title'], 0, 2)) ?></span><?php endif; ?>
          </div>
          <div class="gt-li-txt">
            <h3><?= e($a['title']) ?></h3>
            <p><?= e(mb_substr(strip_tags((string) $a['summary']), 0, 110)) ?></p>
            <div class="gt-li-meta">
              <span><?= e(date('Y-m-d', strtotime((string) $a['create_time']))) ?></span>
              <span><?= e(lang('阅读')) ?> <?= (int) $a['views'] ?></span>
            </div>
          </div>
          <span class="gt-li-arrow">→</span>
        </a>
      <?php endforeach; ?>
      <?php if (!$articles): ?>
        <div class="gt-empty"><?= e(lang('暂无内容，请到后台发布')) ?></div>
      <?php endif; ?>
    </div>

    <?php if (!empty($pager)): ?><div class="gt-pager"><?= $pager ?></div><?php endif; ?>
  </section>
</div>

<?php include __DIR__ . '/_bottom.php'; ?>
