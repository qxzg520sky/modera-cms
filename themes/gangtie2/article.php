<?php include __DIR__ . '/_top.php'; ?>
<?php
$heroImg = $row['cover'] ?: '/uploads/workshop.jpg';
$catName = $cat['name'] ?? $pageTitle;
?>
<section class="g2-banner" style="background-image:url('<?= e($heroImg) ?>')">
  <div class="g2-wrap">
    <span class="g2-eyebrow"><?= e($catName) ?></span>
    <h1><?= e(ml($row, 'title')) ?></h1>
    <div class="g2-crumb">
      <a href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a>
      <i>/</i>
      <?php if (!empty($cat['slug'])): ?>
        <a href="<?= e(lang_path('/c/' . $cat['slug'])) ?>"><?= e($catName) ?></a><i>/</i>
      <?php endif; ?>
      <span><?= e(lang('正文')) ?></span>
    </div>
  </div>
</section>

<section class="g2-article">
  <div class="g2-wrap">
    <article class="g2-art-card">
      <div class="g2-art-meta">
        <span><?= e(date('Y-m-d', strtotime((string) $row['create_time']))) ?></span>
        <?php if (!empty($row['author'])): ?><span><?= e($row['author']) ?></span><?php endif; ?>
        <span><?= e(lang('阅读')) ?> <?= (int) $row['views'] ?></span>
      </div>
      <h1 class="g2-sr"><?= e(ml($row, 'title')) ?></h1>
      <?php if (!empty($row['summary'])): ?>
        <p class="g2-art-lead"><?= e(ml($row, 'summary')) ?></p>
      <?php endif; ?>
      <?php if (!empty($row['cover'])): ?>
        <div class="g2-art-cover"><img src="<?= e($row['cover']) ?>" alt="<?= e(ml($row, 'title')) ?>"></div>
      <?php endif; ?>
      <div class="g2-art-body">
        <?= ml($row, 'content') ?>
      </div>
    </article>

    <div class="g2-art-nav">
      <?php if (!empty($prev)): ?>
        <a href="<?= e(lang_path('/a/' . (int) $prev['id'])) ?>"><i><?= e(lang('上一篇')) ?></i><b><?= e(ml($prev, 'title')) ?></b></a>
      <?php else: ?><span class="g2-art-nav-empty"></span><?php endif; ?>
      <?php if (!empty($next)): ?>
        <a href="<?= e(lang_path('/a/' . (int) $next['id'])) ?>"><i><?= e(lang('下一篇')) ?></i><b><?= e(ml($next, 'title')) ?></b></a>
      <?php else: ?><span class="g2-art-nav-empty"></span><?php endif; ?>
    </div>

    <div class="g2-art-cta">
      <div>
        <span class="g2-eyebrow"><?= e(lang('需要方案与报价')) ?></span>
        <h3><?= e(lang('把工况告诉我们，工程师给方案')) ?></h3>
      </div>
      <div class="g2-art-cta-btns">
        <a class="g2-btn g2-btn-org" href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('联系我们')) ?></a>
        <a class="g2-btn g2-btn-line" href="tel:<?= e(preg_replace('/[^0-9\-]/', '', (string) setting('site_phone', ''))) ?>"><?= e(setting('site_phone', '')) ?></a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
