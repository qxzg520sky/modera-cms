<?php include __DIR__ . '/_top.php'; ?>

<section class="msec">
  <div class="mcrumb"><a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a> / <?= e($cat['name']) ?></div>
  <div class="msec-head left"><h2><?= e($cat['name']) ?></h2><span><?= (int) $total ?> <?= e(lang('条')) ?></span></div>
  <?php if (!empty($subNav)): ?>
    <div class="mcat-tabs">
      <?php foreach ($subNav as $s): ?>
        <a class="mcat-tab <?= (int) ($s['id'] ?? 0) === (int) ($cat['id'] ?? 0) ? 'on' : '' ?>"
          href="<?= e(lang_path('/c/' . $s['slug'])) ?>"><?= e($s['name']) ?></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
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
    <?php if (!$articles): ?><div class="mempty"><?= e(lang('该栏目暂无内容')) ?></div><?php endif; ?>
  </div>
  <?php if (!empty($pager)): ?><div class="mpager"><?= $pager ?></div><?php endif; ?>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
