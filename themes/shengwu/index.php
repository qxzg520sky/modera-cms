<?php include __DIR__ . '/_top.php'; ?>
<?php
$catBySlug = [];
foreach ($nav as $c) { $catBySlug[$c['slug']] = $c; }
$catName = static function (int $id) use ($nav): string {
    foreach ($nav as $c) {
        if ((int) $c['id'] === $id) { return (string) $c['name']; }
        foreach (($c['children'] ?? []) as $ch) { if ((int) $ch['id'] === $id) { return (string) $ch['name']; } }
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
$products = $rowsOf('product', 6);
$solutions = $rowsOf('solution', 3);
$news = $rowsOf('news', 5);
$cover = static function (array $a, string $fallback = '/theme/factory.jpg'): string {
    $c = trim((string) ($a['cover'] ?? '')); return $c !== '' ? $c : $fallback;
};
$titleOf = static fn (array $a): string => (string) ml($a, 'title');
$summaryOf = static function (array $a, int $len = 86): string {
    $s = trim((string) ml($a, 'summary'));
    if ($s === '') { $s = trim(strip_tags((string) ml($a, 'content'))); }
    return mb_substr($s, 0, $len, 'UTF-8');
};
$dateOf = static function (array $a): array {
    $t = strtotime((string) ($a['create_time'] ?? ''));
    return $t ? ['d' => date('d', $t), 'm' => date('Y.m', $t), 'y' => date('Y', $t)] : ['d' => '—', 'm' => '', 'y' => ''];
};
$productCat = $catBySlug['product'] ?? null;
$newsCat = $catBySlug['news'] ?? null;
$solutionCat = $catBySlug['solution'] ?? null;
$productUrl = lang_path('/c/' . ($productCat['slug'] ?? 'product'));
$newsUrl = lang_path('/c/' . ($newsCat['slug'] ?? 'news'));
$solutionUrl = lang_path('/c/' . ($solutionCat['slug'] ?? 'solution'));
$aboutPage = null; foreach ($pages as $p) { if (($p['slug'] ?? '') === 'about') { $aboutPage = $p; } }
$aboutUrl = lang_path('/p/' . ($aboutPage['slug'] ?? 'about'));
?>
<section class="hero">
  <div class="wrap hero-grid">
    <div class="hero-copy">
      <div class="hero-key"><span>OUR VISION</span><em>医疗信息化 · 临床数据 · 智慧医院</em></div>
      <h1>提供最优秀的<br><em>医院信息化解决方案</em></h1>
      <p>从临床业务到运营管理，从数据底座到患者服务。我们用可落地的软件产品，帮助医疗机构把复杂流程变成清晰、可靠、可持续的日常。</p>
      <div class="hero-actions"><a class="btn btn-red" href="<?= e($productUrl) ?>">了解产品 <span class="arrow">↗</span></a><a class="btn btn-line-dark" href="<?= e($contactUrl) ?>">预约演示</a></div>
      <div class="hero-stats"><div><b>20<em>+</em></b><span>年医疗信息化经验</span></div><div><b>300<em>+</em></b><span>医疗机构客户</span></div><div><b>7×24</b><span>运维响应</span></div></div>
    </div>
    <div class="hero-media">
      <img src="/theme/hero1.jpg" alt="<?= e($siteName) ?>">
      <div class="media-line"></div>
      <div class="media-cap"><span>KANGSOFT / 01</span><p>让医疗软件<br>更懂临床。</p></div>
      <div class="media-tag">SINCE 2004</div>
    </div>
  </div>
  <div class="hero-foot"><div class="wrap"><span>SCROLL TO EXPLORE</span><span>医疗科技 / 生命科学 / 数字健康</span><b>01</b></div></div>
</section>

<section class="ticker"><div class="wrap ticker-in"><span>KANGSOFT NEWS</span><em>临床为本 · 数据可信 · 服务可持续</em><a href="<?= e($newsUrl) ?>">查看最新动态 ↗</a></div></section>

<section class="intro sec">
  <div class="wrap intro-grid">
    <div class="intro-title"><div class="kicker"><span>01</span><em>WHY KANGSOFT</em></div><h2>不是把系统做完，<br><em>而是让临床愿意用。</em></h2></div>
    <div class="intro-copy">
      <p>医疗软件的价值，不在于功能列表有多长，而在于医生、护士、管理者是否愿意每天打开它。康软科技从临床流程出发，把接口、数据、权限与体验一起设计。</p>
      <div class="intro-points"><div><b>临床为本</b><span>围绕真实工作流设计产品，不让系统成为额外负担。</span></div><div><b>数据可信</b><span>统一数据口径与接口标准，让每一次决策有据可依。</span></div><div><b>长期陪伴</b><span>从上线到运维，持续迭代而不是一次性交付。</span></div></div>
    </div>
  </div>
</section>

<section class="solutions sec">
  <div class="wrap">
    <div class="sec-head"><div><div class="kicker"><span>02</span><em>SOLUTIONS</em></div><h2>三条产品线<br><em>覆盖医疗数字化主线</em></h2></div><div class="sec-side"><p>从医院核心业务到区域健康平台，按场景组合，而不是把一套系统硬塞进所有机构。</p><a class="btn btn-text" href="<?= e($solutionUrl) ?>">查看解决方案 <span class="arrow">↗</span></a></div></div>
    <div class="solution-grid">
      <a class="solution-card" href="<?= e($solutionUrl) ?>"><div class="s-no">01</div><div class="s-icon"><i></i><i></i><i></i></div><h3>智慧医院</h3><p>覆盖门急诊、住院、护理与医技流程，让业务串联、数据互通。</p><span class="s-more">了解更多 ↗</span></a>
      <a class="solution-card" href="<?= e($solutionUrl) ?>"><div class="s-no">02</div><div class="s-icon"><i></i><i></i><i></i></div><h3>临床数据平台</h3><p>统一数据标准与接口，支撑临床决策、质控与科研数据利用。</p><span class="s-more">了解更多 ↗</span></a>
      <a class="solution-card" href="<?= e($solutionUrl) ?>"><div class="s-no">03</div><div class="s-icon"><i></i><i></i><i></i></div><h3>运营管理</h3><p>从人财物到绩效分析，让管理者看见真实运行状态。</p><span class="s-more">了解更多 ↗</span></a>
    </div>
  </div>
</section>

<section class="products sec">
  <div class="wrap">
    <div class="sec-head"><div><div class="kicker"><span>03</span><em>PRODUCTS</em></div><h2>产品介绍<br><em>为不同规模的机构而设计</em></h2></div><div class="sec-side"><p>模块化部署、标准化接口、可配置流程，既适合单院区，也能支撑集团化与区域协同。</p><a class="btn btn-text" href="<?= e($productUrl) ?>">全部产品 <span class="arrow">↗</span></a></div></div>
    <div class="product-grid">
      <?php foreach ($products as $i => $p): ?>
      <article class="product-card">
        <a class="p-img" href="<?= e(lang_path('/a/' . $p['id'])) ?>"><img src="<?= e($cover($p, '/theme/factory.jpg')) ?>" alt="<?= e($titleOf($p)) ?>" loading="lazy"><span>0<?= $i + 1 ?></span></a>
        <div class="p-body"><span class="p-cat"><?= e($catName((int) $p['category_id'])) ?> / KANGSOFT</span><h3><a href="<?= e(lang_path('/a/' . $p['id'])) ?>"><?= e($titleOf($p)) ?></a></h3><p><?= e($summaryOf($p, 70)) ?></p><a class="p-link" href="<?= e(lang_path('/a/' . $p['id'])) ?>">查看详情 <span>↗</span></a></div>
      </article>
      <?php endforeach; ?>
      <?php if (empty($products)): ?><div class="empty">产品内容正在整理中。</div><?php endif; ?>
    </div>
  </div>
</section>

<section class="case sec">
  <div class="wrap case-grid">
    <div class="case-media"><img src="/theme/case.jpg" alt="<?= e($siteName) ?>"><div class="case-label">CASE / 01</div></div>
    <div class="case-copy"><div class="kicker light"><span>04</span><em>CASE STUDY</em></div><h2>从上线到真正用起来，<br><em>中间隔着一次深度陪跑。</em></h2><p>医院信息化的难点，往往不在开发，而在流程梳理、数据迁移和使用习惯。我们用项目制交付，把每一步都拆清楚。</p><div class="case-points"><div><b>6</b><span>周完成核心模块上线</span></div><div><b>300<em>+</em></b><span>机构持续使用</span></div><div><b>99.9<em>%</em></b><span>系统可用性目标</span></div></div><a class="btn btn-outline-red" href="<?= e($solutionUrl) ?>">查看案例 <span class="arrow">↗</span></a></div>
  </div>
</section>

<section class="news sec">
  <div class="wrap">
    <div class="sec-head"><div><div class="kicker"><span>05</span><em>NEWS</em></div><h2>新闻动态<br><em>医疗科技正在发生</em></h2></div><div class="sec-side"><p>产品更新、行业观察与客户实践。我们记录医疗数字化正在发生的变化。</p><a class="btn btn-text" href="<?= e($newsUrl) ?>">全部资讯 <span class="arrow">↗</span></a></div></div>
    <div class="news-grid">
      <?php foreach ($news as $i => $n): $d = $dateOf($n); ?>
      <a class="news-card<?= $i === 0 ? ' featured' : '' ?>" href="<?= e(lang_path('/a/' . $n['id'])) ?>">
        <div class="nc-img"><img src="<?= e($cover($n, '/theme/news.jpg')) ?>" alt="<?= e($titleOf($n)) ?>" loading="lazy"></div>
        <div class="nc-body"><div class="nc-date"><b><?= e($d['d']) ?></b><span><?= e($d['m']) ?></span><i><?= e($catName((int) $n['category_id'])) ?></i></div><h3><?= e($titleOf($n)) ?></h3><p><?= e($summaryOf($n, 72)) ?></p><span class="nc-link">阅读全文 ↗</span></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="about sec">
  <div class="wrap about-grid">
    <div class="about-media"><img src="/theme/about.jpg" alt="<?= e($siteName) ?>"><div class="about-frame"></div></div>
    <div class="about-copy"><div class="kicker"><span>06</span><em>ABOUT KANGSOFT</em></div><h2>把复杂留给自己，<br><em>把简单交给临床。</em></h2><p>康软科技长期专注医疗信息化，从医院核心业务系统到数据平台与运营工具，服务对象覆盖不同规模的医疗机构。</p><p>我们相信，好的医疗软件应该像一套顺手的工作流：不打断诊疗，不增加录入，不让数据停留在孤岛里。</p><div class="about-sign"><b>KANGSOFT</b><span>康软科技 · 医疗信息化</span></div><a class="btn btn-red" href="<?= e($aboutUrl) ?>">了解我们 <span class="arrow">↗</span></a></div>
  </div>
</section>
<?php include __DIR__ . '/_bottom.php'; ?>
