<?php include __DIR__ . '/_top.php'; ?>

<section class="wrap sec">
  <div class="crumb"><a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a> / <?= e($cat['name']) ?></div>
  <div class="sec-head left"><h2><?= e($cat['name']) ?></h2><p><?= e(lang('共')) ?> <?= (int) $total ?> <?= e(lang('条内容')) ?></p></div>

  <?php if (!empty($subNav)): ?>
    <div class="cat-tabs">
      <?php foreach ($subNav as $s): ?>
        <a class="cat-tab <?= (int) ($s['id'] ?? 0) === (int) ($cat['id'] ?? 0) ? 'on' : '' ?>"
          href="<?= e(lang_path('/c/' . $s['slug'])) ?>"><?= e($s['name']) ?></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="news">
    <?php foreach ($articles as $a): ?>
      <a class="news-item" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
        <div class="news-date"><?= e(date('m-d', strtotime((string) $a['create_time']))) ?></div>
        <div class="news-body">
          <h3><?= e($a['title']) ?></h3>
          <p><?= e(mb_substr(strip_tags((string) $a['summary']), 0, 100)) ?></p>
        </div>
        <div class="news-more">→</div>
      </a>
    <?php endforeach; ?>
    <?php if (!$articles): ?><div class="empty"><?= e(lang('暂无内容')) ?></div><?php endif; ?>
  </div>
  <?php if (!empty($pager)): ?><div class="pager"><?= $pager ?></div><?php endif; ?>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
