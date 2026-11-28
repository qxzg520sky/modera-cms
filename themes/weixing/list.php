<?php include __DIR__ . '/_top.php'; ?>
<?php
$cat = $cat ?? ['name' => lang('栏目'), 'slug' => ''];
$articles = $articles ?? [];
$subNav = $subNav ?? [];
$total = $total ?? count($articles);
$pager = $pager ?? '';
$catSlug = (string) ($cat['slug'] ?? '');
$catName = (string) ($cat['name'] ?? lang('栏目'));
$isSearch = $catSlug === '' && !empty($kw);

$sideItems = [];
if ($subNav) {
    $sideItems = $subNav;
} elseif ($catSlug !== '') {
    $sideItems[] = ['id' => (int) ($cat['id'] ?? 0), 'name' => $catName, 'slug' => $catSlug];
} else {
    foreach ($nav as $c) {
        $sideItems[] = ['id' => (int) $c['id'], 'name' => (string) $c['name'], 'slug' => (string) $c['slug']];
    }
}
$sideNow = (int) ($cat['id'] ?? 0);
?>
<section class="wx-page-hero">
  <img src="/theme/heroindustry.jpg" alt="<?= e($catName) ?>">
  <div class="wx-inner">
    <small><?= e(strtoupper($catSlug ?: 'SEARCH')) ?></small>
    <h1><?= e($catName) ?></h1>
    <p><?= e($isSearch ? lang('搜索结果') : lang('材料与工艺，从真实工况出发')) ?></p>
  </div>
</section>

<div class="wx-page-body wx-inner">
  <aside class="wx-side">
    <h3><?= e($catName) ?></h3>
    <ul>
      <?php foreach ($sideItems as $s): ?>
        <li class="<?= (!$isSearch && (int) $s['id'] === $sideNow) ? 'now' : '' ?>">
          <a href="<?= e(lang_path('/c/' . $s['slug'])) ?>"><?= e($s['name']) ?></a>
        </li>
      <?php endforeach; ?>
      <li class="wx-side-contact"><a href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('需要选型建议？')) ?> →</a></li>
    </ul>
  </aside>
  <div class="wx-list-main">
    <div class="wx-crumb"><a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a><span>/</span><?= e($catName) ?><em><?= e(lang('共')) ?> <?= (int) $total ?> <?= e(lang('条')) ?></em></div>
    <?php if ($articles): ?>
      <div class="wx-article-grid">
        <?php foreach ($articles as $i => $a): $t = strtotime((string) ($a['create_time'] ?? '')); ?>
        <a class="wx-article-card" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
          <div class="wx-article-img"><img src="<?= e(($a['cover'] ?? '') !== '' ? $a['cover'] : '/theme/catbuilding.jpg') ?>" alt="<?= e((string) $a['title']) ?>" loading="lazy"></div>
          <div class="wx-article-txt">
            <?php if ($t): ?><span><?= e(date('Y.m.d', $t)) ?></span><?php endif; ?>
            <h3><?= e((string) $a['title']) ?></h3>
            <p><?= e(mb_substr(trim(strip_tags((string) ($a['summary'] ?: $a['content']))), 0, 80, 'UTF-8')) ?></p>
            <em><?= e(lang('查看详情')) ?> →</em>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="wx-empty"><?= e(lang('暂无内容')) ?></div>
    <?php endif; ?>
    <?php if ($pager): ?><div class="wx-pager"><?= $pager ?></div><?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/_bottom.php'; ?>
