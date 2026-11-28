<?php
include_once __DIR__ . '/_helpers.php';

$latest = \think\facade\Db::name('article')
    ->where('status', 1)
    ->order('is_top desc, id desc')
    ->limit(12)
    ->select()->toArray();
$featured = $latest[0] ?? [];
$posts = array_slice($latest, 1, 8);

$cats = \think\facade\Db::name('category')->where('status', 1)->order('sort asc, id asc')->select()->toArray();
$catCounts = [];
foreach ($cats as $c) {
    $catCounts[(int) $c['id']] = \think\facade\Db::name('article')
        ->where('status', 1)->where('category_id', (int) $c['id'])->count();
}
$totalCount = \think\facade\Db::name('article')->where('status', 1)->count();

$popular = \think\facade\Db::name('article')->where('status', 1)
    ->order('views desc, id desc')->limit(5)->select()->toArray();

$aboutPage = null;
foreach ($pages as $p) {
    if (($p['slug'] ?? '') === 'about') {
        $aboutPage = $p;
        break;
    }
}
$latestDate = '';
if ($featured && !empty($featured['create_time'])) {
    $latestDate = sz_date((string) $featured['create_time']);
}

$seoTitle = setting('site_name', '书斋笔记') . ' · ' . sz_t('独立博客', 'Independent Journal');
$seoDesc = setting('site_description', '');
include __DIR__ . '/_top.php';
?>

<section class="sz-masthead">
  <div class="sz-wrap sz-masthead-in">
    <div class="sz-masthead-kicker">
      <span>JOURNAL</span>
      <i></i>
      <span><?= e(date('Y')) ?></span>
    </div>
    <div class="sz-masthead-copy">
      <h1><?= e(sz_t('写下来，', 'Write it down,')) ?><br><em><?= e(sz_t('留一点余地。', 'leave some room.')) ?></em></h1>
      <p><?= e(setting('site_description', '关于建站、代码与日常生活的独立博客。')) ?></p>
    </div>
    <div class="sz-masthead-side">
      <dl>
        <dt><?= e(sz_t('已刊', 'Published')) ?></dt><dd><?= (int) $totalCount ?></dd>
        <dt><?= e(sz_t('栏目', 'Sections')) ?></dt><dd><?= count($cats) ?></dd>
        <dt><?= e(sz_t('最近更新', 'Last update')) ?></dt><dd class="sm"><?= e($latestDate) ?></dd>
      </dl>
      <p><?= e(sz_t('固定两栏 · 不做自适应 · 手机端独立排版', 'Fixed two columns · No responsive · Separate mobile edition')) ?></p>
    </div>
  </div>
</section>

<div class="sz-wrap sz-layout">
  <div class="sz-main-col">

    <?php if ($featured): ?>
    <article class="sz-lead">
      <a class="sz-lead-media" href="<?= e(lang_path('/a/' . (int) $featured['id'])) ?>">
        <?= sz_cover($featured, 'sz-lead-img', true) ?>
        <span class="sz-lead-issue">NO.<?= e(sz_issue($featured)) ?></span>
      </a>
      <div class="sz-lead-body">
        <div class="sz-meta">
          <span class="sz-cat"><?= e(sz_t('本期主文', 'Lead story')) ?></span>
          <span><?= e(sz_date((string) $featured['create_time'])) ?></span>
          <span><?= e(sz_reading_time($featured)) ?> <?= e(sz_t('分钟', 'min read')) ?></span>
        </div>
        <h2 class="sz-lead-title"><a href="<?= e(lang_path('/a/' . (int) $featured['id'])) ?>"><?= e(ml($featured, 'title')) ?></a></h2>
        <p class="sz-lead-excerpt"><?= e(sz_excerpt($featured, 120)) ?></p>
        <a class="sz-read" href="<?= e(lang_path('/a/' . (int) $featured['id'])) ?>"><?= e(sz_t('阅读全文', 'Read article')) ?><span aria-hidden="true">→</span></a>
      </div>
    </article>
    <?php endif; ?>

    <section class="sz-block">
      <div class="sz-block-head">
        <h2><?= e(sz_t('最新文章', 'Latest posts')) ?></h2>
        <span>RECENT / <?= e(str_pad((string) count($posts), 2, '0', STR_PAD_LEFT)) ?></span>
      </div>

      <div class="sz-post-list">
        <?php foreach ($posts as $i => $a): ?>
        <article class="sz-post">
          <a class="sz-post-media" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
            <?= sz_cover($a, 'sz-post-img') ?>
          </a>
          <div class="sz-post-body">
            <div class="sz-meta">
              <span class="sz-cat"><?= e($cats ? (function ($cid) use ($cats) { foreach ($cats as $cc) { if ((int) $cc['id'] === (int) $cid) { return sz_cat_name($cc); } } return sz_t('随笔', 'Notes'); })($a['category_id']) : sz_t('随笔', 'Notes')) ?></span>
              <span><?= e(sz_date((string) $a['create_time'])) ?></span>
            </div>
            <h3><a href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>"><?= e(ml($a, 'title')) ?></a></h3>
            <p><?= e(sz_excerpt($a, 74)) ?></p>
            <span class="sz-index"><?= e(str_pad((string) ($i + 2), 2, '0', STR_PAD_LEFT)) ?></span>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </section>

    <?php if ($aboutPage): ?>
    <section class="sz-note">
      <span class="sz-note-label"><?= e(sz_t('编者的话', 'A note from the editor')) ?></span>
      <p><?= e(sz_excerpt(['summary' => strip_tags((string) ml($aboutPage, 'content'))], 180)) ?></p>
      <a href="<?= e(lang_path('/p/' . $aboutPage['slug'])) ?>"><?= e(sz_t('关于这个博客', 'About this journal')) ?> →</a>
    </section>
    <?php endif; ?>
  </div>

  <aside class="sz-side">
    <section class="sz-side-card sz-side-about">
      <span class="sz-side-label">ABOUT</span>
      <div class="sz-side-avatar"><?= current_lang() === 'en' ? 'S' : '书' ?></div>
      <h3><?= e(setting('site_name', '书斋笔记')) ?></h3>
      <p><?= e(setting('site_slogan', '慢慢写，认真读')) ?></p>
      <p class="sz-side-text"><?= e(sz_t('一个不追热点的个人博客。写建站、写代码，也写生活里值得记下的小事。', 'A personal journal that ignores the news cycle. Notes on websites, code and small things worth remembering.')) ?></p>
      <?php if ($aboutPage): ?><a class="sz-side-more" href="<?= e(lang_path('/p/' . $aboutPage['slug'])) ?>"><?= e(sz_t('了解更多', 'More about me')) ?> →</a><?php endif; ?>
    </section>

    <section class="sz-side-card">
      <div class="sz-side-head"><h3><?= e(sz_t('热门阅读', 'Most read')) ?></h3><span>POPULAR</span></div>
      <ol class="sz-pop-list">
        <?php foreach ($popular as $i => $p): ?>
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
        <?php foreach ($cats as $c): if (($c['slug'] ?? '') === 'jobs') { continue; } ?>
        <li>
          <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><span><?= e(sz_cat_name($c)) ?></span><em><?= (int) ($catCounts[(int) $c['id']] ?? 0) ?></em></a>
        </li>
        <?php endforeach; ?>
      </ul>
    </section>

    <section class="sz-side-card sz-subscribe">
      <span class="sz-side-label">KEEP IN TOUCH</span>
      <h3><?= e(sz_t('偶尔写一封信', 'An occasional letter')) ?></h3>
      <p><?= e(sz_t('不发推送，不追热点。有新文章时，慢慢告诉你。', 'No push notifications, no trending topics. Just a quiet note when something new is published.')) ?></p>
      <?php $mail = setting('site_email', ''); ?>
      <a class="sz-btn" href="<?= e($mail !== '' ? 'mailto:' . $mail : lang_path('/p/' . ($aboutPage['slug'] ?? 'about'))) ?>"><?= e(sz_t('联系我', 'Say hello')) ?></a>
    </section>

    <section class="sz-side-card sz-side-search">
      <span class="sz-side-label">SEARCH</span>
      <form action="<?= e(lang_path('/search')) ?>" method="get" role="search">
        <input type="search" name="kw" placeholder="<?= e(sz_t('输入关键词', 'Type a keyword')) ?>" aria-label="<?= e(sz_t('搜索文章', 'Search posts')) ?>">
        <button type="submit"><?= e(sz_t('查找', 'Find')) ?></button>
      </form>
    </section>
  </aside>
</div>

<?php include __DIR__ . '/_bottom.php'; ?>
