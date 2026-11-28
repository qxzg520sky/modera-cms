<?php include __DIR__ . '/_top.php'; ?>
<?php
$row  = $row ?? [];
$cat  = $cat ?? null;
$prev = $prev ?? null;
$next = $next ?? null;
$content = (string) ($row['content'] ?? '');
$cover = trim((string) ($row['cover'] ?? ''));
$showCover = $cover !== '' && ($content === '' || strpos($content, $cover) === false);
$date = xb_date($row, 'Y.m.d');
$catName = $cat ? (string) ml($cat, 'name') : lang('文章详情', '文章详情');
$catSlug = (string) ($cat['slug'] ?? '');
$crumbs = [['name' => lang('首页', '首页'), 'url' => lang_path('/')]];
if ($cat) {
    $crumbs[] = ['name' => $catName, 'url' => xb_cat_url($catSlug)];
}
$crumbs[] = ['name' => lang('详情', '详情')];
xb_page_hero((string) xb_title($row), '', $crumbs, $catName);

$related = [];
if ($cat) {
    $related = \think\facade\Db::name('article')
        ->where('category_id', (int) $cat['id'])
        ->where('status', 1)
        ->where('id', '<>', (int) ($row['id'] ?? 0))
        ->order('is_top desc, id desc')
        ->limit(4)
        ->select()
        ->toArray();
}
?>

<section class="xb-section xb-article-section">
  <div class="xb-container xb-article-layout">
    <article class="xb-article" data-reveal>
      <div class="xb-article-meta">
        <span class="xb-article-cat"><?= e($catName) ?></span>
        <?php if ($date !== ''): ?><span><?= e($date) ?></span><?php endif; ?>
        <span><?= e(lang('阅读', '阅读')) ?> <?= (int) ($row['views'] ?? 0) ?></span>
      </div>
      <?php if ($showCover): ?>
        <figure class="xb-article-cover"><img src="<?= e($cover) ?>" alt="<?= e(xb_title($row)) ?>"></figure>
      <?php endif; ?>
      <div class="xb-article-content"><?= $content ?></div>

      <div class="xb-prevnext">
        <?php if ($prev): ?>
          <a href="<?= e(lang_path('/a/' . (int) $prev['id'])) ?>"><span><?= e(lang('上一篇', '上一篇')) ?></span><?= e(ml($prev, 'title')) ?></a>
        <?php else: ?>
          <span class="xb-disabled"><span><?= e(lang('上一篇', '上一篇')) ?></span><?= e(lang('没有了', '没有了')) ?></span>
        <?php endif; ?>
        <?php if ($next): ?>
          <a class="xb-next" href="<?= e(lang_path('/a/' . (int) $next['id'])) ?>"><span><?= e(lang('下一篇', '下一篇')) ?></span><?= e(ml($next, 'title')) ?></a>
        <?php else: ?>
          <span class="xb-disabled xb-next"><span><?= e(lang('下一篇', '下一篇')) ?></span><?= e(lang('没有了', '没有了')) ?></span>
        <?php endif; ?>
      </div>
    </article>

    <aside class="xb-article-side">
      <div class="xb-side-card">
        <span class="xb-eyebrow"><?= e(lang('相关内容', '相关内容')) ?></span>
        <h3><?= e(lang('继续阅读', '继续阅读')) ?></h3>
        <?php if ($related): ?>
          <?php foreach ($related as $r): ?>
            <a class="xb-side-link" href="<?= e(xb_article_url($r)) ?>">
              <span><?= e(xb_date($r)) ?></span>
              <strong><?= e(xb_title($r)) ?></strong>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="xb-side-empty"><?= e(lang('暂无更多内容。', '暂无更多内容。')) ?></p>
        <?php endif; ?>
      </div>
      <div class="xb-side-contact">
        <span class="xb-eyebrow xb-eyebrow-light"><?= e(lang('需要建议？', '需要建议？')) ?></span>
        <h3><?= e(lang('把场景告诉我们', '把场景告诉我们')) ?></h3>
        <p><?= e(lang('产品选型、方案配置与交付周期，都可以先聊清楚。', '产品选型、方案配置与交付周期，都可以先聊清楚。')) ?></p>
        <a class="xb-btn xb-btn-lime" href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('联系我们', '联系我们')) ?> <span>→</span></a>
      </div>
    </aside>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
