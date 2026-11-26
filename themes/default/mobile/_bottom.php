</main>

<footer class="mfoot">
  <div class="mfoot-name"><?= e(setting('site_name', '')) ?></div>
  <div class="mfoot-line"><?= e(setting('site_address', '')) ?></div>
  <div class="mfoot-line"><?= e(lang('电话')) ?> <?= e(setting('site_phone', '')) ?>　<?= e(lang('邮箱')) ?> <?= e(setting('site_email', '')) ?></div>
  <div class="mfoot-line"><?= e(setting('site_copyright', '© ' . date('Y') . ' ' . setting('site_name', ''))) ?></div>
  <?php if (setting('site_icp', '')): ?>
    <div class="mfoot-line"><a href="https://beian.miit.gov.cn" target="_blank" rel="nofollow"><?= e(setting('site_icp', '')) ?></a></div>
  <?php endif; ?>
  <div class="mfoot-switch"><a href="<?= e(lang_path('/')) ?>?mobile=0"><?= e(lang('电脑版')) ?></a></div>
</footer>

<?php if (setting('site_phone', '')): ?>
<div class="mdock">
  <a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a>
  <a href="tel:<?= e(setting('site_phone', '')) ?>" class="primary"><?= e(lang('电话咨询')) ?></a>
</div>
<?php endif; ?>

<?= plugin_hook('front_footer') ?>
</body>
</html>
