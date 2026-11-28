<?php
$sideCats = $sideCats ?? \think\facade\Db::name('category')->where('status', 1)->order('sort asc, id asc')->select()->toArray();
$sidePopular = $sidePopular ?? \think\facade\Db::name('article')->where('status', 1)->order('views desc, id desc')->limit(4)->select()->toArray();
$sideAbout = null;
foreach (($pages ?? []) as $p) {
    if (($p['slug'] ?? '') === 'about') {
        $sideAbout = $p;
        break;
    }
}
?>
<aside class="sz-side">
  <section class="sz-side-card sz-side-about">
    <span class="sz-side-label">ABOUT</span>
    <div class="sz-side-avatar"><?= current_lang() === 'en' ? 'S' : '书' ?></div>
    <h3><?= e(setting('site_name', '书斋笔记')) ?></h3>
    <p><?= e(setting('site_slogan', '慢慢写，认真读')) ?></p>
    <p class="sz-side-text"><?= e(sz_t('一个不追热点的个人博客。写建站、写代码，也写生活里值得记下的小事。', 'A personal journal that ignores the news cycle. Notes on websites, code and small things worth remembering.')) ?></p>
    <?php if ($sideAbout): ?><a class="sz-side-more" href="<?= e(lang_path('/p/' . $sideAbout['slug'])) ?>"><?= e(sz_t('了解更多', 'More about me')) ?> →</a><?php endif; ?>
  </section>

  <section class="sz-side-card">
    <div class="sz-side-head"><h3><?= e(sz_t('热门阅读', 'Most read')) ?></h3><span>POPULAR</span></div>
    <ol class="sz-pop-list">
      <?php foreach ($sidePopular as $i => $p): ?>
      <li>
        <span class="sz-pop-no"><?= e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></span>
        <div>
          <a href="<?= e(lang_path('/a/' . (int) $p['id'])) ?>"><?= e(ml($p, 'title')) ?></a>
          <small><?= e((int) $p['views']) ?> <?= e(sz_t('次阅读', 'views')) ?></small>
        </div>
      </li>
      <?php endforeach; ?>
    </ol>
  </section>

  <section class="sz-side-card">
    <div class="sz-side-head"><h3><?= e(sz_t('文章分类', 'Categories')) ?></h3><span>INDEX</span></div>
    <ul class="sz-cat-list">
      <?php foreach ($sideCats as $c): if (($c['slug'] ?? '') === 'jobs') { continue; } ?>
      <li>
        <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>">
          <span><?= e(sz_cat_name($c)) ?></span>
          <em><?= (int) \think\facade\Db::name('article')->where('status', 1)->where('category_id', (int) $c['id'])->count() ?></em>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </section>

  <?php if (empty($sideLean)): ?>
  <section class="sz-side-card sz-subscribe">
    <span class="sz-side-label">KEEP IN TOUCH</span>
    <h3><?= e(sz_t('偶尔写一封信', 'An occasional letter')) ?></h3>
    <p><?= e(sz_t('不发推送，不追热点。有新文章时，慢慢告诉你。', 'No push notifications, no trending topics. Just a quiet note when something new is published.')) ?></p>
    <?php $sideMail = setting('site_email', ''); ?>
    <a class="sz-btn" href="<?= e($sideMail !== '' ? 'mailto:' . $sideMail : lang_path('/p/' . ($sideAbout['slug'] ?? 'about'))) ?>"><?= e(sz_t('联系我', 'Say hello')) ?></a>
  </section>

  <?php endif; ?>
  <?php if (empty($sideLean)): ?>
  <section class="sz-side-card sz-side-search">
    <span class="sz-side-label">SEARCH</span>
    <form action="<?= e(lang_path('/search')) ?>" method="get" role="search">
      <input type="search" name="kw" placeholder="<?= e(sz_t('输入关键词', 'Type a keyword')) ?>" aria-label="<?= e(sz_t('搜索文章', 'Search posts')) ?>">
      <button type="submit"><?= e(sz_t('查找', 'Find')) ?></button>
    </form>
  </section>
  <?php endif; ?>
</aside>
