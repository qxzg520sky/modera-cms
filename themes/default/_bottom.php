</main>
<footer class="ftr">
  <div class="wrap ftr-in">
    <div>
      <div class="ftr-name"><?= e(setting('site_name', '')) ?></div>
      <div class="ftr-line"><?= e(setting('site_address', '')) ?></div>
      <div class="ftr-line"><?= e(lang('电话')) ?>：<?= e(setting('site_phone', '')) ?>　<?= e(lang('邮箱')) ?>：<?= e(setting('site_email', '')) ?></div>
    </div>
    <div class="ftr-right">
      <div class="ftr-line"><?= e(setting('site_copyright', '© ' . date('Y') . ' ' . setting('site_name', ''))) ?></div>
      <div class="ftr-line"><a href="https://beian.miit.gov.cn" target="_blank" rel="nofollow"><?= e(setting('site_icp', '')) ?></a></div>
    </div>
  </div>
</footer>
<?= plugin_hook('front_footer') ?>
</body>
</html>
