<?php include __DIR__ . '/_top.php'; ?>
<?php
$cover = trim((string) ($row['cover'] ?? ''));
$content = (string) ml($row, 'content');
$hasImg = (bool) preg_match('/<img/i', $content);
if ($cover === '') { $cover = '/theme/about.jpg'; }
$t = strtotime((string) ($row['create_time'] ?? ''));
$dateText = $t ? date('Y.m.d', $t) : '—';
$catName = (string) ($cat['name'] ?? '文章');
$summary = trim((string) ml($row, 'summary'));
if ($summary === '') { $summary = mb_substr(trim(strip_tags((string) ml($row, 'content'))), 0, 100, 'UTF-8'); }
$related = \think\facade\Db::name('article')->where('category_id', (int) $row['category_id'])->where('status', 1)->where('id', '<>', (int) $row['id'])->order('id desc')->limit(3)->select()->toArray();
$coverOf = static function (array $a): string {
    $c = trim((string) ($a['cover'] ?? ''));
    return $c !== '' ? $c : '/theme/about.jpg';
};
?>
<section class="page-hero" data-en="ARTICLE">
  <div class="wrap">
    <div class="breadcrumb"><a href="<?= e(lang_path('/')) ?>">首页</a> / <a href="<?= e(lang_path('/c/' . ($cat['slug'] ?? 'news'))) ?>"><?= e($catName) ?></a> / 正文</div>
    <div class="hero-line"></div>
    <h1><?= e((string) ml($row, 'title')) ?></h1>
    <p>发布时间：<?= e($dateText) ?>　分类：<?= e($catName) ?>　阅读：<?= (int) ($row['views'] ?? 0) ?></p>
  </div>
</section>

<section class="sec-pad-sm">
  <div class="wrap detail-layout">
    <div class="detail-main">
      <?php if (!$hasImg): ?><div class="detail-cover"><img src="<?= e($cover) ?>" alt="<?= e((string) ml($row, 'title')) ?>"></div><?php endif; ?>
      <div class="prose">
        <?= $content ?>
      </div>
      <div class="detail-nav">
        <?php if (!empty($prev)): ?>
          <a href="<?= e(lang_path('/a/' . $prev['id'])) ?>"><span>PREV</span><?= e((string) ml($prev, 'title')) ?></a>
        <?php else: ?>
          <a href="<?= e(lang_path('/c/' . ($cat['slug'] ?? 'news'))) ?>"><span>BACK</span>返回列表</a>
        <?php endif; ?>
        <?php if (!empty($next)): ?>
          <a href="<?= e(lang_path('/a/' . $next['id'])) ?>"><span>NEXT</span><?= e((string) ml($next, 'title')) ?> ↗</a>
        <?php endif; ?>
      </div>
    </div>
    <aside class="sidebar">
      <div class="side-card">
        <h4>相关阅读</h4>
        <?php foreach ($related as $r): ?>
          <a href="<?= e(lang_path('/a/' . $r['id'])) ?>"><?= e((string) ml($r, 'title')) ?></a>
        <?php endforeach; ?>
        <?php if (!$related): ?><a href="<?= e(lang_path('/c/' . ($cat['slug'] ?? 'news'))) ?>">更多<?= e($catName) ?> →</a><?php endif; ?>
      </div>
      <div class="side-card side-contact">
        <h4>项目咨询</h4>
        <?php if ($phone): ?><b><?= e($phone) ?></b><?php endif; ?>
        <p>获取产品资料、产线方案与项目报价。</p>
        <a class="btn btn-primary" href="<?= e($contactUrl) ?>" style="width:100%">联系我们 <span class="arrow">↗</span></a>
      </div>
    </aside>
  </div>
</section>
<?php include __DIR__ . '/_bottom.php'; ?>




