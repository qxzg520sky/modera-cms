<?php include __DIR__ . '/_top.php'; ?>
<?php
$catBySlug = [];
foreach ($nav as $c) { $catBySlug[$c['slug']] = $c; }
$catName = static function (int $id) use ($nav): string {
    foreach ($nav as $c) {
        if ((int) $c['id'] === $id) { return (string) $c['name']; }
        foreach (($c['children'] ?? []) as $ch) {
            if ((int) $ch['id'] === $id) { return (string) $ch['name']; }
        }
    }
    return '产品';
};
$rowsOf = static function (string $slug, int $limit) use ($catBySlug): array {
    if (!isset($catBySlug[$slug])) { return []; }
    $ids = [(int) $catBySlug[$slug]['id']];
    foreach (($catBySlug[$slug]['children'] ?? []) as $ch) { $ids[] = (int) $ch['id']; }
    return \think\facade\Db::name('article')->whereIn('category_id', $ids)->where('status', 1)
        ->order('is_top desc, id desc')->limit($limit)->select()->toArray();
};
$products = $rowsOf('product', 4);
$solutions = $rowsOf('solution', 3);
$news = $rowsOf('news', 4);
$cover = static function (array $a, string $fallback = '/theme/product.jpg'): string {
    $c = trim((string) ($a['cover'] ?? ''));
    return $c !== '' ? $c : $fallback;
};
$titleOf = static fn (array $a): string => (string) ml($a, 'title');
$summaryOf = static function (array $a, int $len = 72): string {
    $s = trim((string) ml($a, 'summary'));
    if ($s === '') { $s = trim(strip_tags((string) ml($a, 'content'))); }
    return mb_substr($s, 0, $len, 'UTF-8');
};
$dateOf = static function (array $a): array {
    $t = strtotime((string) ($a['create_time'] ?? ''));
    return $t ? ['d' => date('Y.m.d', $t), 'y' => date('Y', $t)] : ['d' => '—', 'y' => ''];
};
$productCat = $catBySlug['product'] ?? null;
$newsCat = $catBySlug['news'] ?? null;
$solutionCat = $catBySlug['solution'] ?? null;
$productUrl = lang_path('/c/' . ($productCat['slug'] ?? 'product'));
$newsUrl = lang_path('/c/' . ($newsCat['slug'] ?? 'news'));
$solutionUrl = lang_path('/c/' . ($solutionCat['slug'] ?? 'solution'));
$aboutPage = null;
foreach ($pages as $p) { if (($p['slug'] ?? '') === 'about') { $aboutPage = $p; } }
$aboutUrl = lang_path('/p/' . ($aboutPage['slug'] ?? 'about'));
$aboutParas = [];
if ($aboutPage && !empty($aboutPage['content'])) {
    preg_match_all('/<p[^>]*>(.*?)<\/p>/iu', (string) $aboutPage['content'], $m);
    foreach ($m[1] as $para) {
        $clean = trim(strip_tags($para));
        if ($clean !== '') { $aboutParas[] = $clean; }
        if (count($aboutParas) >= 2) { break; }
    }
}
$heroImg = '/theme/hero.jpg';
?>
<section class="hero">
  <div class="wrap hero-in">
    <div class="hero-copy">
      <span class="eyebrow">Industrial Automation · Since 2009</span>
      <h1>让制造现场<br><em>清晰可见</em></h1>
      <p>从单台设备到整线交付，从工艺确认到稳定量产。我们以 15 年工程积累，为制造企业提供稳定、精密、可扩展的自动化解决方案。</p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="<?= e($productUrl) ?>">探索产品 <span class="arrow">↗</span></a>
        <a class="btn btn-ghost" href="<?= e($solutionUrl) ?>">行业解决方案</a>
      </div>
      <div class="hero-note"><i></i><span>SINCE 2009 / 智能制造 · 整线交付 · 全程可追溯</span></div>
    </div>
    <div class="hero-visual">
      <div class="hero-specs"><span>整线交付</span><span>精密装配</span><span>全程可追溯</span></div>
      <div class="hero-plate"><img src="<?= e($heroImg) ?>" alt="<?= e($siteName) ?>"></div>
      <div class="hero-badge"><b>15 YEARS</b><span>工程积累 · 持续进化</span></div>
    </div>
  </div>
  <div class="hero-scroll">SCROLL TO EXPLORE</div>
</section>

<section class="stat-strip">
  <div class="wrap stat-grid">
    <div class="stat"><b>15<em>+</em></b><span>年行业深耕</span></div>
    <div class="stat"><b>31</b><span>省市服务覆盖</span></div>
    <div class="stat"><b>200<em>+</em></b><span>一线客户单位</span></div>
    <div class="stat"><b>7×24</b><span>技术响应</span></div>
  </div>
</section>

<section class="sec-pad">
  <div class="wrap">
    <div class="sec-head">
      <div><div class="idx">01 / PRODUCTS</div><h2>为现场而生<br>四类核心装备</h2></div>
      <p>不做参数堆砌，只做产线真正用得上、靠得住的装备。每一款产品都围绕“稳定运行、精度一致、维护简单”展开。</p>
    </div>
    <div class="product-grid">
      <?php foreach ($products as $i => $p): ?>
      <article class="product-card">
        <div class="p-copy">
          <div>
            <div class="p-no">0<?= $i + 1 ?> / <?= e($catName((int) $p['category_id'])) ?></div>
            <h3><?= e($titleOf($p)) ?></h3>
            <div class="en">MODERA · PRODUCT / 0<?= $i + 1 ?></div>
            <p><?= e($summaryOf($p, 88)) ?></p>
          </div>
          <a class="p-link" href="<?= e(lang_path('/a/' . $p['id'])) ?>">查看产品 <span class="arrow">↗</span></a>
        </div>
        <div class="p-img"><a href="<?= e(lang_path('/a/' . $p['id'])) ?>"><img src="<?= e($cover($p)) ?>" alt="<?= e($titleOf($p)) ?>" loading="lazy"></a></div>
      </article>
      <?php endforeach; ?>
    </div>
    <div class="product-foot"><span>产品矩阵：自动化装备 / 精密加工 / 整线集成 / 技术服务</span><span class="line"></span><a href="<?= e($productUrl) ?>">全部产品 <span class="arrow">↗</span></a></div>
  </div>
</section>

<section class="sec-dark sec-pad">
  <div class="wrap">
    <div class="sec-head">
      <div><div class="idx">02 / SOLUTIONS</div><h2>深入场景<br>而不止于设备</h2></div>
      <p>从工艺梳理到整线联调，我们把设备、节拍、精度与现场流程一起考虑，让自动化真正落到每一道工序里。</p>
    </div>
    <div class="solution-grid<?= count($solutions) === 3 ? ' is-three' : '' ?>">
      <?php foreach ($solutions as $i => $s): ?>
      <a class="solution-card" href="<?= e(lang_path('/a/' . $s['id'])) ?>">
        <img src="<?= e($cover($s, '/theme/factory.jpg')) ?>" alt="<?= e($titleOf($s)) ?>" loading="lazy">
        <span class="s-arrow">↗</span>
        <div class="s-copy"><span class="s-tag">0<?= $i + 1 ?> / SOLUTION</span><h3><?= e($titleOf($s)) ?></h3><p><?= e($summaryOf($s, 74)) ?></p></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sec-dark" style="padding-bottom:96px">
  <div class="wrap about-block">
    <div class="about-media">
      <img src="/theme/factory.jpg" alt="<?= e($siteName) ?>厂区" loading="lazy">
      <div class="stamp"><div><b>2009</b><span>始于制造</span></div></div>
    </div>
    <div class="about-copy">
      <span class="eyebrow light">About Us</span>
      <div class="big">以匠心制造，为工业赋能。<br>把复杂的技术，做成稳定的日常。</div>
      <?php if ($aboutParas): foreach ($aboutParas as $para): ?>
        <p><?= e($para) ?></p>
      <?php endforeach; else: ?>
        <p>从单台设备到整线交付，我们始终围绕“稳定、精密、可扩展”打磨产品与服务，让自动化真正成为客户产能的一部分。</p>
      <?php endif; ?>
      <div class="about-points">
        <div><b>研发</b><span>自主软硬件团队</span></div>
        <div><b>制造</b><span>ISO 9001 体系</span></div>
        <div><b>服务</b><span>全国巡检网络</span></div>
      </div>
      <div style="margin-top:32px"><a class="btn btn-ghost" href="<?= e($aboutUrl) ?>">了解我们 <span class="arrow">↗</span></a></div>
    </div>
  </div>
</section>

<section class="sec-pad">
  <div class="wrap">
    <div class="sec-head">
      <div><div class="idx">03 / JOURNAL</div><h2>公司动态<br>与行业观察</h2></div>
      <p>产品交付、客户培训、行业趋势。记录我们正在做的事，也记录智能制造正在发生的变化。</p>
    </div>
    <div class="news-grid">
      <?php foreach ($news as $n): $d = $dateOf($n); ?>
      <a class="news-item" href="<?= e(lang_path('/a/' . $n['id'])) ?>">
        <div class="n-date"><?= e($d['d']) ?><span><?= e($catName((int) $n['category_id'])) ?></span></div>
        <div><h3><?= e($titleOf($n)) ?></h3><p><?= e($summaryOf($n, 86)) ?></p><span class="n-arrow">阅读全文 ↗</span></div>
      </a>
      <?php endforeach; ?>
    </div>
    <div style="margin-top:34px"><a class="btn btn-line" href="<?= e($newsUrl) ?>">全部资讯 <span class="arrow">↗</span></a></div>
  </div>
</section>
<?php include __DIR__ . '/_bottom.php'; ?>



