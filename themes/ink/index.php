<?php
include __DIR__ . '/_helpers.php';
$seoTitle = trim((string) setting('site_name', '')) . ' · 博客与笔记';
$seoDesc = (string) setting('site_description', '');
include __DIR__ . '/_top.php';

$latest = \think\facade\Db::name('article')->where('status', 1)
    ->order('is_top desc, create_time desc, id desc')->limit(10)->select()->toArray();
$totalCount = (int) \think\facade\Db::name('article')->where('status', 1)->count();
$cats = \think\facade\Db::name('category')->where('status', 1)->where('pid', 0)
    ->order('sort asc, id asc')->select()->toArray();
$catCounts = [];
foreach (\think\facade\Db::name('article')->where('status', 1)->field('category_id, COUNT(*) AS n')
    ->group('category_id')->select()->toArray() as $r) {
    $catCounts[(int) $r['category_id']] = (int) $r['n'];
}

$featured = $latest ? $latest[0] : null;
$posts = $latest ? array_slice($latest, 1, 6) : [];
$latestYear = $latest ? date('Y', strtotime((string) ($latest[0]['create_time'] ?? 'now'))) : date('Y');
?>
<section class="home-hero wrap">
  <div class="hero-copy">
    <p class="eyebrow"><span>JOURNAL</span><i></i><?= e($latestYear) ?></p>
    <h1>把复杂的技术，<br>写成<em>可复用</em>的笔记。</h1>
    <p class="hero-lede"><?= e($siteDesc) ?></p>
    <div class="hero-actions">
      <a class="btn btn-ink" href="#latest"><?= e(lang('开始阅读')) ?><span aria-hidden="true">↓</span></a>
      <?php if ($aboutPage): ?>
        <a class="btn btn-quiet" href="<?= e(lang_path('/p/' . $aboutPage['slug'])) ?>"><?= e(lang('关于本站')) ?><span aria-hidden="true">↗</span></a>
      <?php endif; ?>
    </div>
    <div class="hero-stats">
      <div><b><?= $totalCount ?></b><span><?= e(lang('篇文章')) ?></span></div>
      <div><b><?= count($cats) ?></b><span><?= e(lang('个栏目')) ?></span></div>
      <div><b><?= e($latestYear) ?></b><span><?= e(lang('持续更新')) ?></span></div>
    </div>
  </div>

  <?php if ($featured): ?>
  <div class="hero-feature reveal">
    <a class="feature-cover" href="<?= e(lang_path('/a/' . (int) $featured['id'])) ?>" aria-label="<?= e((string) ml($featured, 'title')) ?>">
      <?= ink_cover($featured, 'hero', true) ?>
    </a>
    <div class="feature-copy">
      <p class="feature-kicker"><span>FEATURED</span><?= e(lang('本期推荐')) ?></p>
      <div class="card-meta">
        <a class="tag" href="<?= e(lang_path('/c/' . ink_cat_slug($featured))) ?>"><?= e(ink_cat_name($featured)) ?></a>
        <span><?= e(ink_date($featured)) ?></span>
        <span><?= e(ink_views($featured)) ?> <?= e(lang('阅读')) ?></span>
      </div>
      <h2><a href="<?= e(lang_path('/a/' . (int) $featured['id'])) ?>"><?= e((string) ml($featured, 'title')) ?></a></h2>
      <p><?= e(ink_summary($featured, 112)) ?></p>
    </div>
  </div>
  <?php else: ?>
  <div class="hero-feature hero-empty">
    <div class="empty-cover" aria-hidden="true"><span>INK</span></div>
    <div class="feature-copy"><p><?= e(lang('还没有文章，去后台发布第一篇笔记。')) ?></p></div>
  </div>
  <?php endif; ?>
</section>

<?php if ($cats): ?>
<section class="category-rail">
  <div class="wrap rail-in">
    <span class="rail-label"><?= e(lang('分类')) ?></span>
    <div class="rail-chips">
      <?php foreach ($cats as $c): ?>
        <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>" class="chip">
          <?= e($c['name']) ?><small><?= (int) ($catCounts[(int) $c['id']] ?? 0) ?></small>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="home-latest wrap" id="latest">
  <div class="section-head">
    <div>
      <p class="eyebrow"><span>LATEST</span><i></i><?= e(lang('最近更新')) ?></p>
      <h2><?= e(lang('最新笔记')) ?></h2>
    </div>
    <p class="section-note"><?= e(lang('按时间倒序，记录建站、代码与长期主义。')) ?></p>
  </div>

  <?php if ($posts): ?>
  <div class="post-grid">
    <?php foreach ($posts as $i => $a): ?>
      <?= ink_card($a, $i + 1, $i === 0) ?>
    <?php endforeach; ?>
  </div>
  <?php elseif (!$featured): ?>
    <div class="empty-state">
      <p class="eyebrow"><span>EMPTY</span><i></i>NO NOTES YET</p>
      <h2><?= e(lang('这里还很安静。')) ?></h2>
      <p><?= e(lang('登录后台发布文章，封面留空时会自动生成一张高质量的默认封面。')) ?></p>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <p class="eyebrow"><span>ONE NOTE</span><i></i>KEEP WRITING</p>
      <h2><?= e(lang('第一篇笔记已经上线。')) ?></h2>
      <p><?= e(lang('再发布几篇，首页就会形成完整的文章流。')) ?></p>
    </div>
  <?php endif; ?>
</section>

<section class="closing wrap">
  <div class="closing-line"></div>
  <p><span>慢一点，写得更久一点。</span><small>WRITE SLOWLY, KEEP GOING.</small></p>
</section>
<?php include __DIR__ . '/_bottom.php'; ?>
