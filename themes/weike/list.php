<?php include __DIR__ . '/_top.php'; ?>
<?php
$cat      = $cat ?? ['name' => lang('栏目'), 'slug' => ''];
$articles = $articles ?? [];
$subNav   = $subNav ?? [];
$total    = $total ?? count($articles);
$pager    = $pager ?? '';

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
<div class="banner_s"><img src="/theme/banner_b_clean.jpg" alt="<?= e($catName) ?>"></div>
<div class="s_bg">
  <div class="scd clearfix">
    <ul class="scd_l">
      <?php foreach ($sideItems as $s): ?>
        <li class="<?= (!$isSearch && (int) $s['id'] === $sideNow) ? 'now' : '' ?>">
          <a href="<?= e(lang_path('/c/' . $s['slug'])) ?>"><?= e($s['name']) ?></a>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="scd_r">
      <div class="scd_rm">
        <div class="wk-crumb"><a href="<?= e(lang_path('/')) ?>"><?= e(lang('首页')) ?></a> / <?= e($catName) ?>　<span><?= e(lang('共')) ?> <?= (int) $total ?> <?= e(lang('条')) ?></span></div>

        <?php if ($articles): ?>
        <ul class="wk-list">
          <?php foreach ($articles as $i => $a): $t = strtotime((string) ($a['create_time'] ?? '')); ?>
          <li>
            <a class="thumb" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
              <img src="<?= e(($a['cover'] ?? '') !== '' ? $a['cover'] : '/theme/pic' . (($i % 8) + 1) . '.jpg') ?>" alt="<?= e((string) $a['title']) ?>" loading="lazy">
            </a>
            <div class="txt">
              <?php if ($t): ?><div class="date"><?= e(date('Y-m-d', $t)) ?></div><?php endif; ?>
              <div class="title"><a href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>"><?= e((string) $a['title']) ?></a></div>
              <div class="desc"><?= e(mb_substr(trim(strip_tags((string) ($a['summary'] ?: $a['content']))), 0, 110, 'UTF-8')) ?></div>
              <a class="more-link" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>"><?= e(lang('查看详情')) ?> &gt;</a>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php else: ?>
          <div class="wk-empty"><?= e(lang('暂无内容')) ?></div>
        <?php endif; ?>

        <?php if ($pager): ?>
          <div class="wk-pager"><?= $pager ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/_bottom.php'; ?>


