<?php include __DIR__ . '/_top.php'; ?>
<?php
$catName = $cat['name'] ?? $pageTitle;
$heroImg = !empty($articles[0]['cover']) ? $articles[0]['cover'] : '/uploads/workshop.jpg';
?>
<section class="g2-banner" style="background-image:url('<?= e($heroImg) ?>')">
  <div class="g2-wrap">
    <span class="g2-eyebrow"><?= e(lang('我们的业务')) ?></span>
    <h1><?= e($catName) ?></h1>
    <div class="g2-crumb">
      <a href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a>
      <i>/</i>
      <span><?= e($catName) ?></span>
    </div>
  </div>
</section>

<section class="g2-list">
  <div class="g2-wrap">
    <?php if (!empty($subNav)): ?>
      <div class="g2-cattabs">
        <?php foreach ($subNav as $ch): ?>
          <a href="<?= e(lang_path('/c/' . $ch['slug'])) ?>" class="<?= ($ch['slug'] === ($cat['slug'] ?? '')) ? 'on' : '' ?>"><?= e($ch['name']) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="g2-list-head">
      <div>
        <h2><?= e($catName) ?></h2>
        <?php if (!empty($cat['seo_description'])): ?><p><?= e(mb_substr(strip_tags((string) $cat['seo_description']), 0, 80)) ?></p><?php endif; ?>
      </div>
      <span class="g2-count"><?= e(lang('共')) ?> <?= (int) ($total ?? count($articles)) ?> <?= e(lang('条')) ?></span>
    </div>

    <?php if ($articles): ?>
      <div class="g2-card-grid">
        <?php foreach ($articles as $a): ?>
          <a class="g2-card" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
            <span class="g2-card-img">
              <?php if ($a['cover']): ?>
                <img src="<?= e($a['cover']) ?>" alt="<?= e(ml($a, 'title')) ?>">
              <?php else: ?>
                <span class="g2-ph"><?= e(mb_substr(ml($a, 'title'), 0, 6)) ?></span>
              <?php endif; ?>
            </span>
            <div class="g2-card-body">
              <h3><?= e(ml($a, 'title')) ?></h3>
              <p><?= e(mb_substr(strip_tags((string) ml($a, 'summary')), 0, 70)) ?></p>
              <span class="g2-card-foot">
                <i><?= e(date('Y-m-d', strtotime((string) $a['create_time']))) ?></i>
                <em><?= e(lang('查看详情')) ?> →</em>
              </span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="g2-empty"><?= e(lang('暂无内容')) ?></div>
    <?php endif; ?>

    <?php if (!empty($pager)): ?>
      <div class="g2-pager"><?= $pager ?></div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
