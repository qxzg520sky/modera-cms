<?php
include_once __DIR__ . '/../_helpers.php';

$latest = \think\facade\Db::name('article')->where('status', 1)->order('is_top desc, id desc')->limit(12)->select()->toArray();
$featured = $latest[0] ?? [];
$posts = array_slice($latest, 1, 8);
$cats = \think\facade\Db::name('category')->where('status', 1)->order('sort asc, id asc')->select()->toArray();
$popular = \think\facade\Db::name('article')->where('status', 1)->order('views desc, id desc')->limit(5)->select()->toArray();
$totalCount = \think\facade\Db::name('article')->where('status', 1)->count();
$aboutPage = null;
foreach ($pages as $p) {
    if (($p['slug'] ?? '') === 'about') {
        $aboutPage = $p;
        break;
    }
}
$seoTitle = setting('site_name', '书斋笔记') . ' · ' . sz_t('手机版', 'Mobile edition');
$seoDesc = setting('site_description', '');
include __DIR__ . '/_top.php';
?>

<section class="mz-intro">
  <div class="mz-intro-kicker"><span>MOBILE EDITION</span><i></i><span><?= e(date('Y')) ?></span></div>
  <h1><?= e(sz_t('把文章，放进口袋。', 'Essays, pocket-sized.')) ?></h1>
  <p><?= e(setting('site_description', '关于建站、代码与日常生活的独立博客。')) ?></p>
  <div class="mz-stats">
    <span><b><?= (int) $totalCount ?></b><?= e(sz_t('篇文章', 'posts')) ?></span>
    <span><b><?= count($cats) ?></b><?= e(sz_t('个栏目', 'sections')) ?></span>
    <span><b>01</b><?= e(sz_t('份手机刊', 'mobile issue')) ?></span>
  </div>
</section>

<?php if ($featured): ?>
<article class="mz-hero">
  <a class="mz-hero-media" href="<?= e(lang_path('/a/' . (int) $featured['id'])) ?>">
    <?= sz_cover($featured, 'mz-hero-img', true) ?>
    <span class="mz-hero-tag"><?= e(sz_t('头条', 'Lead')) ?></span>
  </a>
  <div class="mz-hero-body">
    <div class="mz-meta">
      <span class="mz-cat"><?= e(sz_t('本期主文', 'Lead story')) ?></span>
      <span><?= e(sz_date((string) $featured['create_time'], false)) ?></span>
    </div>
    <h2><a href="<?= e(lang_path('/a/' . (int) $featured['id'])) ?>"><?= e(ml($featured, 'title')) ?></a></h2>
    <p><?= e(sz_excerpt($featured, 66)) ?></p>
    <a class="mz-read" href="<?= e(lang_path('/a/' . (int) $featured['id'])) ?>"><?= e(sz_t('读这篇', 'Read this')) ?><span>→</span></a>
  </div>
</article>
<?php endif; ?>

<section class="mz-section">
  <div class="mz-section-head">
    <h2><?= e(sz_t('最新文章', 'Latest')) ?></h2>
    <span><?= e(str_pad((string) count($posts), 2, '0', STR_PAD_LEFT)) ?> / NEW</span>
  </div>
  <div class="mz-post-list">
    <?php foreach ($posts as $i => $a): ?>
    <article class="mz-post">
      <div class="mz-post-main">
        <div class="mz-meta"><span><?= e(sz_date((string) $a['create_time'], false)) ?></span><span><?= e(sz_reading_time($a)) ?> <?= e(sz_t('分钟', 'min')) ?></span></div>
        <h3><a href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>"><?= e(ml($a, 'title')) ?></a></h3>
        <p><?= e(sz_excerpt($a, 44)) ?></p>
      </div>
      <a class="mz-post-thumb" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>"><?= sz_cover($a, 'mz-thumb-img') ?></a>
      <span class="mz-post-no"><?= e(str_pad((string) ($i + 2), 2, '0', STR_PAD_LEFT)) ?></span>
    </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="mz-section">
  <div class="mz-section-head">
    <h2><?= e(sz_t('热门阅读', 'Most read')) ?></h2>
    <span>POPULAR</span>
  </div>
  <ol class="mz-pop">
    <?php foreach ($popular as $i => $p): ?>
    <li>
      <span><?= e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></span>
      <div><a href="<?= e(lang_path('/a/' . (int) $p['id'])) ?>"><?= e(ml($p, 'title')) ?></a><small><?= e((int) $p['views']) ?> <?= e(sz_t('次阅读', 'views')) ?></small></div>
    </li>
    <?php endforeach; ?>
  </ol>
</section>

<section class="mz-section">
  <div class="mz-section-head">
    <h2><?= e(sz_t('文章分类', 'Sections')) ?></h2>
    <span>INDEX</span>
  </div>
  <div class="mz-cats">
    <?php foreach ($cats as $c): if (($c['slug'] ?? '') === 'jobs') { continue; } ?>
    <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><span><?= e(sz_cat_name($c)) ?></span><em><?= (int) \think\facade\Db::name('article')->where('status', 1)->where('category_id', (int) $c['id'])->count() ?></em></a>
    <?php endforeach; ?>
  </div>
</section>

<?php if ($aboutPage): ?>
<section class="mz-about">
  <span class="mz-label">ABOUT</span>
  <div class="mz-about-avatar"><?= current_lang() === 'en' ? 'S' : '书' ?></div>
  <h3><?= e(setting('site_name', '书斋笔记')) ?></h3>
  <p><?= e(sz_excerpt(['summary' => strip_tags((string) ml($aboutPage, 'content'))], 100)) ?></p>
  <a href="<?= e(lang_path('/p/' . $aboutPage['slug'])) ?>"><?= e(sz_t('关于这个博客', 'About this journal')) ?> →</a>
</section>
<?php endif; ?>

<?php include __DIR__ . '/_bottom.php'; ?>
