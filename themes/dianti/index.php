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
$products = $rowsOf('product', 6);
$solutions = $rowsOf('solution', 3);
$news = $rowsOf('news', 4);
$cover = static function (array $a, string $fallback = '/theme/product.jpg'): string {
    $c = trim((string) ($a['cover'] ?? ''));
    return $c !== '' ? $c : $fallback;
};
$titleOf = static fn (array $a): string => (string) ml($a, 'title');
$summaryOf = static function (array $a, int $len = 82): string {
    $s = trim((string) ml($a, 'summary'));
    if ($s === '') { $s = trim(strip_tags((string) ml($a, 'content'))); }
    return mb_substr($s, 0, $len, 'UTF-8');
};
$dateOf = static function (array $a): array {
    $t = strtotime((string) ($a['create_time'] ?? ''));
    return $t ? ['d' => date('d', $t), 'm' => date('Y.m', $t)] : ['d' => '—', 'm' => ''];
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
?>
<section class="hero">
  <div class="hero-bg" style="background-image:url('/theme/hero1.jpg')"></div>
  <div class="hero-shade"></div>
  <div class="wrap hero-in">
    <div class="hero-side"><span>FUJI</span><b>ELEVATOR</b><i></i><em>SINCE 2009</em></div>
    <div class="hero-copy">
      <div class="hero-kicker"><span>专业电梯定制</span><span>住宅 / 商业 / 公共建筑</span></div>
      <h1>专业电梯定制<br><em>一站式解决方案</em></h1>
      <p>集研发、设计、制造、安装与维保于一体。以中日合资的技术积累，为每一栋建筑提供安全、舒适、节能的垂直交通系统。</p>
      <div class="hero-actions">
        <a class="btn btn-solid" href="<?= e($productUrl) ?>">探索产品 <span class="arrow">↗</span></a>
        <a class="btn btn-outline" href="<?= e($contactUrl) ?>">预约勘测</a>
      </div>
      <div class="hero-tags"><span>安全第一</span><span>用户第一</span><span>服务至上</span></div>
    </div>
  </div>
  <div class="hero-bottom"><div class="wrap hero-bottom-in"><span>SCROLL TO EXPLORE</span><b>01 / 06</b></div></div>
</section>

<section class="stat-strip">
  <div class="wrap stat-grid">
    <div class="stat"><b>20<em>+</em></b><span>年行业积累</span></div>
    <div class="stat"><b>500<em>+</em></b><span>项目交付</span></div>
    <div class="stat"><b>31</b><span>省市服务网络</span></div>
    <div class="stat"><b>24<em>h</em></b><span>应急响应</span></div>
  </div>
</section>

<section class="about-split sec">
  <div class="wrap about-grid">
    <div class="about-copy">
      <div class="sec-no"><span>01</span><i></i><em>ABOUT FUJI</em></div>
      <h2>关于富士恒<br><em>让建筑向上，更从容。</em></h2>
      <p class="lede">深圳市富士恒电梯有限公司是中日合资企业，集研发、设计、制造生产销售安装、维修保养于一体，以集团技术人才与高精度设备为基础，持续打磨电梯产品的安全性与舒适度。</p>
      <div class="about-points">
        <div><b>研发</b><span>永磁同步曳引 / 矢量数字化驱动</span></div>
        <div><b>制造</b><span>模块化设计 / 全流程品控</span></div>
        <div><b>服务</b><span>安装、维保、改造一体化</span></div>
      </div>
      <a class="btn btn-text" href="<?= e($aboutUrl) ?>">了解更多 <span class="arrow">↗</span></a>
    </div>
    <div class="about-media">
      <div class="about-img"><img src="/theme/about.jpg" alt="<?= e($siteName) ?>"></div>
      <div class="about-stamp"><b>20<em>+</em></b><span>YEARS<br>OF SAFETY</span></div>
      <div class="about-cap"><span>01</span> 专业电梯 / 城市垂直交通</div>
    </div>
  </div>
</section>

<section class="products sec">
  <div class="wrap">
    <div class="sec-head">
      <div><div class="sec-no"><span>02</span><i></i><em>PRODUCTS</em></div><h2>为每一种建筑<br><em>匹配恰当的电梯</em></h2></div>
      <div class="sec-side"><p>从乘客电梯到载货、观光与医用电梯，围绕建筑尺度、使用频率与舒适体验做选型，而不是简单堆叠参数。</p><a class="btn btn-text" href="<?= e($productUrl) ?>">全部产品 <span class="arrow">↗</span></a></div>
    </div>
    <div class="product-grid">
      <?php foreach ($products as $i => $p): ?>
      <article class="product-card">
        <a class="p-img" href="<?= e(lang_path('/a/' . $p['id'])) ?>"><img src="<?= e($cover($p)) ?>" alt="<?= e($titleOf($p)) ?>" loading="lazy"><span class="p-index">0<?= $i + 1 ?></span></a>
        <div class="p-body">
          <div class="p-meta"><span><?= e($catName((int) $p['category_id'])) ?></span><span>FUJI / PRODUCT</span></div>
          <h3><a href="<?= e(lang_path('/a/' . $p['id'])) ?>"><?= e($titleOf($p)) ?></a></h3>
          <p><?= e($summaryOf($p, 72)) ?></p>
          <a class="p-link" href="<?= e(lang_path('/a/' . $p['id'])) ?>">查看详情 <span>↗</span></a>
        </div>
      </article>
      <?php endforeach; ?>
      <?php if (empty($products)): ?><div class="empty">产品内容正在整理中。</div><?php endif; ?>
    </div>
  </div>
</section>

<section class="projects sec">
  <div class="wrap">
    <div class="sec-head light">
      <div><div class="sec-no"><span>03</span><i></i><em>PROJECTS</em></div><h2>工程案例<br><em>从住宅到城市综合体</em></h2></div>
      <div class="sec-side"><p>每一个项目都从井道勘测与客流测算开始，用适配的配置把安全、静音与节能落到日常运行里。</p><a class="btn btn-ghost" href="<?= e($solutionUrl) ?>">查看全部案例 <span class="arrow">↗</span></a></div>
    </div>
    <div class="project-grid">
      <?php foreach ($solutions as $i => $s): ?>
      <a class="project-card" href="<?= e(lang_path('/a/' . $s['id'])) ?>">
        <img src="<?= e($cover($s, '/theme/project.jpg')) ?>" alt="<?= e($titleOf($s)) ?>" loading="lazy">
        <div class="project-shade"></div>
        <div class="project-copy"><span>PROJECT / 0<?= $i + 1 ?></span><h3><?= e($titleOf($s)) ?></h3><p><?= e($summaryOf($s, 54)) ?></p></div>
        <div class="project-arrow">↗</div>
      </a>
      <?php endforeach; ?>
      <?php if (empty($solutions)): ?><div class="empty light">案例内容正在整理中。</div><?php endif; ?>
    </div>
  </div>
</section>

<section class="advantages sec">
  <div class="wrap">
    <div class="sec-no"><span>04</span><i></i><em>WHY FUJI</em></div>
    <h2>从一部电梯，<br><em>看见一套完整的安全体系。</em></h2>
    <div class="adv-grid">
      <div class="adv-card"><b>01</b><h3>研发设计</h3><p>永磁同步曳引、矢量全数字化驱动与模块化设计，兼顾舒适、节能与建筑空间。</p></div>
      <div class="adv-card"><b>02</b><h3>制造品控</h3><p>关键部件全流程检验，整梯联调与安全测试前置，减少现场不可控。</p></div>
      <div class="adv-card"><b>03</b><h3>安装交付</h3><p>从井道勘测到验收交付，项目节点透明可追踪，尽量减少对现场进度的影响。</p></div>
      <div class="adv-card"><b>04</b><h3>维保服务</h3><p>区域备件与 24 小时响应机制，让每一次运行都有人兜底。</p></div>
    </div>
  </div>
</section>

<section class="news sec">
  <div class="wrap">
    <div class="sec-head">
      <div><div class="sec-no"><span>05</span><i></i><em>JOURNAL</em></div><h2>新闻中心<br><em>关于电梯，也关于城市生活</em></h2></div>
      <div class="sec-side"><p>公司动态、行业观察与电梯知识。记录正在发生的事，也回答每一次乘坐背后的为什么。</p><a class="btn btn-text" href="<?= e($newsUrl) ?>">全部资讯 <span class="arrow">↗</span></a></div>
    </div>
    <div class="news-layout">
      <a class="news-feature" href="<?= e(lang_path('/a/' . ($news[0]['id'] ?? 0))) ?>">
        <img src="<?= e($cover($news[0] ?? [], '/theme/news.jpg')) ?>" alt="<?= e($news ? $titleOf($news[0]) : '新闻中心') ?>" loading="lazy">
        <div class="nf-copy"><span>FEATURED STORY</span><h3><?= e($news ? $titleOf($news[0]) : '电梯安全，从每一次维保开始') ?></h3><p><?= e($news ? $summaryOf($news[0], 96) : '') ?></p><em>阅读全文 ↗</em></div>
      </a>
      <div class="news-list">
        <?php foreach (array_slice($news, 1) as $n): $d = $dateOf($n); ?>
        <a class="news-row" href="<?= e(lang_path('/a/' . $n['id'])) ?>">
          <div class="n-date"><b><?= e($d['d']) ?></b><span><?= e($d['m']) ?></span></div>
          <div class="n-body"><span class="n-cat"><?= e($catName((int) $n['category_id'])) ?></span><h3><?= e($titleOf($n)) ?></h3><p><?= e($summaryOf($n, 82)) ?></p></div>
          <span class="n-arrow">↗</span>
        </a>
        <?php endforeach; ?>
        <?php if (count($news) < 2): ?><div class="empty">更多资讯正在整理中。</div><?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/_bottom.php'; ?>
