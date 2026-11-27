<?php
$siteName = setting('site_name', '墨白笔记');
$siteSlogan = setting('site_slogan', '写代码，也写方法');
$siteDesc = setting('site_description', '关于建站、代码与长期主义的独立笔记。');
$email = setting('site_email', '');
$icp = setting('site_icp', '');
$copyright = setting('site_copyright', '© ' . date('Y') . ' ' . $siteName . ' 版权所有');
$nav = $nav ?? [];
$pages = $pages ?? [];
$footCats = [];
foreach ($nav as $c) {
    if (($c['slug'] ?? '') === 'jobs') {
        continue;
    }
    $footCats[] = $c;
    if (count($footCats) >= 5) {
        break;
    }
}
?>
</main>

<footer class="site-foot">
  <div class="wrap foot-top">
    <div class="foot-brand">
      <a href="<?= e(lang_path('/')) ?>" class="foot-logo">
        <span class="brand-mark">M</span>
        <span><?= e($siteName) ?></span>
      </a>
      <p><?= e($siteDesc) ?></p>
      <small>JOURNAL / NOTES · <?= e(date('Y')) ?></small>
    </div>
    <div class="foot-col">
      <h3><?= e(lang('栏目')) ?></h3>
      <?php foreach ($footCats as $c): ?>
        <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="foot-col">
      <h3><?= e(lang('页面')) ?></h3>
      <?php foreach ($pages as $p): ?>
        <a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="foot-col foot-contact">
      <h3><?= e(lang('联系')) ?></h3>
      <?php if ($email !== ''): ?><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><?php endif; ?>
      <a href="<?= e(lang_path('/search')) ?>"><?= e(lang('搜索')) ?></a>
      <p><?= e($siteSlogan) ?></p>
    </div>
  </div>
  <div class="wrap foot-meta">
    <span><?= e($copyright) ?></span>
    <span class="foot-meta-right">
      <?php if ($icp !== ''): ?><a href="https://beian.miit.gov.cn" target="_blank" rel="nofollow noopener"><?= e($icp) ?></a><?php endif; ?>
      <span class="foot-theme">INK THEME</span>
    </span>
  </div>
</footer>

<button class="to-top" type="button" data-to-top aria-label="<?= e(lang('回到顶部')) ?>">
  <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5M5.5 11.5L12 5l6.5 6.5"></path></svg>
</button>

<?= plugin_hook('front_footer') ?>
<script src="/theme/theme.js?v=1.0.0" defer></script>
</body>
</html>
