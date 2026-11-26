<?php include __DIR__ . '/_top.php'; ?>
<?php $pageTitle = ml($row, 'title'); ?>
<section class="g2-banner g2-banner-sm" style="background-image:url('/uploads/showroom.jpg')">
  <div class="g2-wrap">
    <span class="g2-eyebrow"><?= e(lang('关于我们')) ?></span>
    <h1><?= e($pageTitle) ?></h1>
    <div class="g2-crumb">
      <a href="<?= e(lang_path('/')) ?>"><?= e(lang('网站首页')) ?></a>
      <i>/</i>
      <span><?= e($pageTitle) ?></span>
    </div>
  </div>
</section>

<section class="g2-page">
  <div class="g2-wrap g2-page-in">
    <aside class="g2-page-aside">
      <div class="g2-page-nav">
        <h4><?= e(lang('页面导航')) ?></h4>
        <ul>
          <?php foreach (($pages ?? []) as $p): ?>
            <li class="<?= ($p['slug'] === $row['slug']) ? 'on' : '' ?>"><a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="g2-page-contact">
        <h4><?= e(lang('联系方式')) ?></h4>
        <p><em><?= e(lang('电话')) ?></em><b><?= e(setting('site_phone', '')) ?></b></p>
        <p><em><?= e(lang('邮箱')) ?></em><span><?= e(setting('site_email', '')) ?></span></p>
        <p><em><?= e(lang('地址')) ?></em><span><?= e(setting('site_address', '')) ?></span></p>
        <a class="g2-btn g2-btn-org" href="tel:<?= e(preg_replace('/[^0-9\-]/', '', (string) setting('site_phone', ''))) ?>"><?= e(lang('立即拨打')) ?></a>
      </div>
    </aside>
    <article class="g2-art-card g2-page-card">
      <h1 class="g2-sr"><?= e($pageTitle) ?></h1>
      <div class="g2-art-body">
        <?= ml($row, 'content') ?>
      </div>
    </article>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
