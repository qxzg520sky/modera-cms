<?php
$siteName  = setting('site_name', '书斋笔记');
$siteSlogan = setting('site_slogan', '慢慢写，认真读');
$siteDesc  = setting('site_description', '关于建站、代码与日常生活的独立博客。');
$email     = setting('site_email', '');
$icp       = setting('site_icp', '');
$copyright = setting('site_copyright', '© ' . date('Y') . ' ' . $siteName);
$nav       = $nav ?? [];
$pages     = $pages ?? [];
$footCats  = [];
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

<footer class="sz-foot">
  <div class="sz-wrap sz-foot-grid">
    <div class="sz-foot-brand">
      <a href="<?= e(lang_path('/')) ?>" class="sz-foot-logo">
        <span class="sz-mark"><?= current_lang() === 'en' ? 'S' : '书' ?></span>
        <b><?= e($siteName) ?></b>
      </a>
      <p><?= e($siteDesc) ?></p>
      <small><?= e($siteSlogan) ?> · SINCE <?= e(date('Y')) ?></small>
    </div>
    <div class="sz-foot-col">
      <h4><?= e(sz_t('栏目', 'Sections')) ?></h4>
      <?php foreach ($footCats as $c): ?>
        <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e(sz_cat_name($c)) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="sz-foot-col">
      <h4><?= e(sz_t('页面', 'Pages')) ?></h4>
      <?php foreach ($pages as $p): ?>
        <a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e(ml($p, 'title')) ?></a>
      <?php endforeach; ?>
      <a href="<?= e(lang_path('/search')) ?>"><?= e(sz_t('搜索', 'Search')) ?></a>
    </div>
    <div class="sz-foot-col sz-foot-note">
      <h4><?= e(sz_t('通讯', 'Contact')) ?></h4>
      <?php if ($email !== ''): ?><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a><?php endif; ?>
      <p><?= e(sz_t('在这里记录建站、代码与日常。文章可以慢慢写，读者可以慢慢读。', 'Notes on building, code and everyday life. Take your time.')) ?></p>
    </div>
  </div>
  <div class="sz-wrap sz-foot-meta">
    <span><?= e($copyright) ?></span>
    <span>
      <?php if ($icp !== ''): ?><a href="https://beian.miit.gov.cn" target="_blank" rel="nofollow noopener"><?= e($icp) ?></a><?php endif; ?>
      <em>SHUZHAI / FIXED TWO-COLUMN</em>
    </span>
  </div>
</footer>

<?= plugin_hook('front_footer') ?>
<script src="/theme/theme.js?v=1.0.0" defer></script>
<?php if (current_lang() === 'en'): ?>
<script>
(function () {
  var M = {
    '首页': 'Home', '搜索文章': 'Search posts', '搜索': 'Search', '栏目': 'Sections', '页面': 'Pages',
    '通讯': 'Contact', '阅读全文': 'Read article', '继续阅读': 'Continue reading', '上一篇': 'Previous',
    '下一篇': 'Next', '相关文章': 'Related', '热门文章': 'Popular', '最新文章': 'Latest',
    '分类': 'Categories', '归档': 'Archive', '关于': 'About', '返回': 'Back'
  };
  function fix() {
    document.querySelectorAll('a,span,em,button,h1,h2,h3,h4,p,li').forEach(function (el) {
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
