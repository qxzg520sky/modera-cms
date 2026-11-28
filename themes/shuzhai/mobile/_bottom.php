<?php
$siteName  = setting('site_name', '书斋笔记');
$siteSlogan = setting('site_slogan', '慢慢写，认真读');
$email     = setting('site_email', '');
$icp       = setting('site_icp', '');
$copyright = setting('site_copyright', '© ' . date('Y') . ' ' . $siteName);
?>
</main>

<footer class="mz-foot">
  <div class="mz-foot-brand">
    <span class="mz-mark"><?= current_lang() === 'en' ? 'S' : '书' ?></span>
    <div><b><?= e($siteName) ?></b><small><?= e($siteSlogan) ?></small></div>
  </div>
  <p class="mz-foot-desc"><?= e(sz_t('固定两栏的桌面版，和这一份专门为手机重排的版本。', 'A fixed two-column desktop edition, plus this hand-made mobile edition.')) ?></p>
  <div class="mz-foot-links">
    <a href="<?= e(lang_path('/')) ?>"><?= e(sz_t('首页', 'Home')) ?></a>
    <a href="<?= e(lang_path('/search')) ?>"><?= e(sz_t('搜索', 'Search')) ?></a>
    <?php if ($email !== ''): ?><a href="mailto:<?= e($email) ?>"><?= e(sz_t('联系', 'Contact')) ?></a><?php endif; ?>
    <a href="?mobile=0"><?= e(sz_t('电脑版', 'Desktop')) ?></a>
  </div>
  <div class="mz-foot-meta">
    <span><?= e($copyright) ?></span>
    <?php if ($icp !== ''): ?><a href="https://beian.miit.gov.cn" target="_blank" rel="nofollow noopener"><?= e($icp) ?></a><?php endif; ?>
  </div>
</footer>

<button class="mz-top" type="button" data-mz-top aria-label="<?= e(sz_t('回到顶部', 'Back to top')) ?>">↑</button>

<?= plugin_hook('front_footer') ?>
<script src="/theme/theme.js?m=1&v=1.0.0" defer></script>
<?php if (current_lang() === 'en'): ?>
<script>
(function () {
  var M = {
    '首页': 'Home', '搜索': 'Search', '联系': 'Contact', '电脑版': 'Desktop', '目录': 'Contents',
    '正文': 'Article', '上一篇': 'Previous', '下一篇': 'Next', '相关阅读': 'Related',
    '热门阅读': 'Most read', '文章分类': 'Categories', '最新文章': 'Latest', '搜索文章': 'Search posts'
  };
  function fix() {
    document.querySelectorAll('a,span,em,button,p,li,h2,h3,h4').forEach(function (el) {
      if (el.children.length) { return; }
      var t = (el.textContent || '').trim();
      if (t && M[t]) { el.textContent = M[t]; }
    });
  }
  setTimeout(fix, 200);
  setTimeout(fix, 700);
})();
</script>
<?php endif; ?>
</body>
</html>
