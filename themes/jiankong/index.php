<?php include __DIR__ . '/_top.php'; ?>
<?php
$catBySlug = [];
foreach ($nav as $c) { $catBySlug[$c['slug']] = $c; }
$catName = static function (int $id) use ($nav, $T): string {
    foreach ($nav as $c) { if ((int) $c['id'] === $id) { return (string) $c['name']; } foreach (($c['children'] ?? []) as $ch) { if ((int) $ch['id'] === $id) { return (string) $ch['name']; } } }
    return $T('产品', 'Products');
};
$rowsOf = static function (string $slug, int $limit) use ($catBySlug): array {
    if (!isset($catBySlug[$slug])) { return []; }
    $ids = [(int) $catBySlug[$slug]['id']];
    foreach (($catBySlug[$slug]['children'] ?? []) as $ch) { $ids[] = (int) $ch['id']; }
    return \think\facade\Db::name('article')->whereIn('category_id', $ids)->where('status', 1)->order('is_top desc, id desc')->limit($limit)->select()->toArray();
};
$products = $rowsOf('product', 3);
$solutions = $rowsOf('solution', 3);
$news = $rowsOf('news', 3);
$cover = static function (array $a, string $fallback = '/theme/factory.png'): string { $c = trim((string) ($a['cover'] ?? '')); return $c !== '' ? $c : $fallback; };
$titleOf = static fn (array $a): string => (string) ml($a, 'title');
$summaryOf = static function (array $a, int $len = 80): string { $s = trim((string) ml($a, 'summary')); if ($s === '') { $s = trim(strip_tags((string) ml($a, 'content'))); } return mb_substr($s, 0, $len, 'UTF-8'); };
$dateOf = static function (array $a): array { $t = strtotime((string) ($a['create_time'] ?? '')); if (!$t) { return ['d' => '—', 'm' => '']; } return ['d' => date('d', $t), 'm' => current_lang() === 'en' ? date('M Y', $t) : date('n月 Y', $t)]; };
$productCat = $catBySlug['product'] ?? null; $newsCat = $catBySlug['news'] ?? null; $solutionCat = $catBySlug['solution'] ?? null;
$productUrl = lang_path('/c/' . ($productCat['slug'] ?? 'product')); $newsUrl = lang_path('/c/' . ($newsCat['slug'] ?? 'news')); $solutionUrl = lang_path('/c/' . ($solutionCat['slug'] ?? 'solution'));
$aboutPage = null; foreach ($pages as $p) { if (($p['slug'] ?? '') === 'about') { $aboutPage = $p; } }
$aboutUrl = lang_path('/p/' . ($aboutPage['slug'] ?? 'about'));
?>
<section class="hero">
  <div class="hero-bg"></div>
  <div class="wrap hero-in">
    <div class="hero-copy">
      <div class="hero-label"><span></span><?= $T('智能安防系统', 'SMART SECURITY SYSTEM') ?></div>
      <h1><?= $T('让企业安全<br>更早一步<br><em>发生。</em>', 'CoSecu Helps<br>Your Business<br><em>Succeed</em>') ?></h1>
      <p><?= $T('让安全更早发生。面向家庭、商业与信息场景，提供从感知、传输到平台的一体化智能监控方案。', 'Security that starts earlier. One integrated monitoring system — from sensors and transmission to the platform — for homes, businesses and information environments.') ?></p>
      <div class="hero-actions"><a class="btn btn-blue" href="<?= e($productUrl) ?>"><?= $T('选购产品', 'BUY PRODUCT') ?></a><a class="btn btn-white-line" href="<?= e($contactUrl) ?>"><?= $T('联系我们', 'CONTACT US') ?></a></div>
      <div class="hero-scroll"><span></span> <?= $T('向下探索', 'SCROLL TO EXPLORE') ?></div>
    </div>
    <div class="hero-visual"><img src="/theme/hero.png" alt="<?= e($siteName) ?>"><div class="lens"><span></span><i></i></div><div class="hero-vtag">CoSecu / <?= $T('全天候监控', '24H MONITORING') ?></div></div>
  </div>
</section>

<section class="service-cards">
  <div class="wrap card-grid">
    <article class="security-card"><div class="sc-num">01</div><div class="sc-icon"><img src="/theme/service1.png" alt=""></div><h3><?= $T('家庭安防', 'Home Security') ?></h3><p><?= $T('家庭安防从门口开始。智能门锁、摄像头与告警联动，随时看见家里发生的事。', 'Home security starts at the door. Smart locks, cameras and alerts work together so you always know what is happening at home.') ?></p><a href="<?= e($solutionUrl) ?>"><?= $T('了解方案', 'EXPLORE') ?> <span>↗</span></a></article>
    <article class="security-card"><div class="sc-num">02</div><div class="sc-icon"><img src="/theme/service2.png" alt=""></div><h3><?= $T('商业安防', 'Business Security') ?></h3><p><?= $T('面向商铺、园区与办公场景的多点位监控与集中管理，让每一次异常都有记录。', 'Multi-site monitoring and central management for shops, campuses and offices — every anomaly leaves a record.') ?></p><a href="<?= e($solutionUrl) ?>"><?= $T('了解方案', 'EXPLORE') ?> <span>↗</span></a></article>
    <article class="security-card"><div class="sc-num">03</div><div class="sc-icon"><img src="/theme/service3.png" alt=""></div><h3><?= $T('信息安全', 'Information Security') ?></h3><p><?= $T('从设备接入到数据存储，构建可控的权限与审计体系，保护视频与业务数据。', 'From device access to data storage, we build controllable permissions and auditing to protect video and business data.') ?></p><a href="<?= e($solutionUrl) ?>"><?= $T('了解方案', 'EXPLORE') ?> <span>↗</span></a></article>
  </div>
</section>

<section class="stat-band">
  <div class="wrap stat-in">
    <div class="stat"><b>300<em>+</em></b><span><?= $T('项目交付', 'Projects delivered') ?></span></div>
    <div class="stat"><b>24<em>/7</em></b><span><?= $T('实时监控', 'Live monitoring') ?></span></div>
    <div class="stat"><b>99.9<em>%</em></b><span><?= $T('系统在线率', 'System uptime') ?></span></div>
    <div class="stat"><b>15<em>min</em></b><span><?= $T('异常响应', 'Incident response') ?></span></div>
  </div>
</section>

<section class="story sec">
  <div class="wrap story-grid">
    <div class="story-media"><img src="/theme/about.png" alt="<?= e($siteName) ?>"><div class="play"><span>▶</span></div><div class="sm-label"><?= $T('我们的故事', 'OUR STORY') ?> / 01</div></div>
    <div class="story-copy"><div class="kicker"><span>01</span><em><?= $T('我们的故事', 'OUR STORY') ?></em></div><h2><?= $T('不是装一个摄像头，<br><em>而是建立一套感知系统。</em>', 'Not just a camera.<br><em>A complete sensing system.</em>') ?></h2><p><?= $T('CoSecu 从现场勘测开始，先理解空间、动线与风险点，再决定设备、网络与平台。我们不做堆砌式方案，只做真正能被日常使用的安防系统。', 'CoSecu starts with an on-site survey: understanding the space, movement paths and risk points before choosing devices, network and platform. We build systems people actually use every day.') ?></p><div class="story-list"><div><b>01</b><span><?= $T('需求勘测与风险点位梳理', 'Site survey and risk mapping') ?></span></div><div><b>02</b><span><?= $T('设备、网络与平台一体化设计', 'Integrated device, network and platform design') ?></span></div><div><b>03</b><span><?= $T('交付、调试与持续运维', 'Delivery, commissioning and ongoing support') ?></span></div></div><a class="btn btn-dark" href="<?= e($aboutUrl) ?>"><?= $T('关于我们', 'ABOUT US') ?> <span class="arrow">↗</span></a></div>
  </div>
</section>

<section class="solutions sec">
  <div class="wrap">
    <div class="sec-head"><div><div class="kicker"><span>02</span><em><?= $T('我们提供什么', 'WHAT WE PROVIDE') ?></em></div><h2><?= $T('为每一种场景<br><em>提供安全保障。</em>', 'Security for<br><em>every scenario.</em>') ?></h2></div><div class="sec-side"><p><?= $T('家庭、商业、园区与信息场景，不同的空间需要不同的监控逻辑。', 'Homes, businesses, campuses and information environments each need their own monitoring logic.') ?></p><a class="btn btn-text" href="<?= e($solutionUrl) ?>"><?= $T('全部方案', 'ALL SOLUTIONS') ?> <span class="arrow">↗</span></a></div></div>
    <div class="solution-grid">
      <a class="sol-card" href="<?= e($solutionUrl) ?>"><img src="/theme/service1.png" alt=""><div class="sol-copy"><span>01 / <?= $T('家庭', 'HOME') ?></span><h3><?= $T('家庭安防', 'Security for Home') ?></h3><p><?= $T('门口、客厅、庭院与老人儿童看护，一套系统联动起来。', 'Doorway, living room, garden and elder or child care — one connected system.') ?></p></div><i>↗</i></a>
      <a class="sol-card" href="<?= e($solutionUrl) ?>"><img src="/theme/service2.png" alt=""><div class="sol-copy"><span>02 / <?= $T('商业', 'BUSINESS') ?></span><h3><?= $T('商业安防', 'Security for Office') ?></h3><p><?= $T('多点位画面集中查看，异常事件自动留存，管理更省心。', 'View multiple sites in one place, keep incident footage automatically and simplify management.') ?></p></div><i>↗</i></a>
      <a class="sol-card" href="<?= e($solutionUrl) ?>"><img src="/theme/service3.png" alt=""><div class="sol-copy"><span>03 / <?= $T('信息', 'INFORMATION') ?></span><h3><?= $T('信息安全', 'Security Your Business') ?></h3><p><?= $T('权限、存储与审计一起设计，保护设备与数据资产。', 'Permissions, storage and auditing designed together to protect devices and data assets.') ?></p></div><i>↗</i></a>
    </div>
  </div>
</section>

<section class="process sec">
  <div class="wrap">
    <div class="kicker"><span>03</span><em><?= $T('如何运作', 'HOW IT WORKS') ?></em></div>
    <h2><?= $T('三步，让安全<br><em>真正开始工作。</em>', 'Three steps to put<br><em>security to work.</em>') ?></h2>
    <div class="process-grid">
      <div class="step"><div class="step-no">01</div><h3><?= $T('选购产品', 'Order Product') ?></h3><p><?= $T('告诉我们空间面积、点位数量与使用场景，获取选型建议。', 'Tell us the floor area, number of points and use case, and we will recommend the right setup.') ?></p><span><?= $T('咨询 / 选型', 'CONSULT / SELECT') ?></span></div>
      <div class="step"><div class="step-no">02</div><h3><?= $T('上门安装', 'Installation') ?></h3><p><?= $T('现场勘测、布线、设备安装与平台配置，一次完成。', 'Site survey, cabling, device installation and platform configuration — completed in one visit.') ?></p><span><?= $T('安装 / 配置', 'INSTALL / CONFIGURE') ?></span></div>
      <div class="step"><div class="step-no">03</div><h3><?= $T('安心使用', 'Enjoy Your Safety') ?></h3><p><?= $T('手机随时查看，异常自动提醒，系统持续在线。', 'Check in from your phone anytime, get automatic alerts and keep the system online.') ?></p><span><?= $T('监控 / 守护', 'MONITOR / PROTECT') ?></span></div>
    </div>
  </div>
</section>

<section class="products sec">
  <div class="wrap">
    <div class="sec-head"><div><div class="kicker"><span>04</span><em><?= $T('产品', 'PRODUCTS') ?></em></div><h2><?= $T('推荐安防<br><em>产品</em>', 'Recommended<br><em>security products</em>') ?></h2></div><div class="sec-side"><p><?= $T('从室内云台到户外枪机，按场景搭配，不买多余功能。', 'From indoor pan-tilt cameras to outdoor bullets — matched to the scene, without paying for unused features.') ?></p><a class="btn btn-text" href="<?= e($productUrl) ?>"><?= $T('全部产品', 'ALL PRODUCTS') ?> <span class="arrow">↗</span></a></div></div>
    <div class="product-grid">
      <?php foreach ($products as $i => $p): ?>
      <article class="product-card"><a class="p-img" href="<?= e(lang_path('/a/' . $p['id'])) ?>"><img src="<?= e($cover($p, '/theme/blog1.png')) ?>" alt="<?= e($titleOf($p)) ?>" loading="lazy"><span>0<?= $i + 1 ?></span></a><div class="p-body"><span class="p-cat"><?= e($catName((int) $p['category_id'])) ?> / COSECU</span><h3><a href="<?= e(lang_path('/a/' . $p['id'])) ?>"><?= e($titleOf($p)) ?></a></h3><p><?= e($summaryOf($p, 68)) ?></p><a class="p-link" href="<?= e(lang_path('/a/' . $p['id'])) ?>"><?= $T('查看详情', 'VIEW DETAIL') ?> <span>↗</span></a></div></article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="quote-band">
  <div class="wrap quote-in">
    <div class="quote-mark">“</div>
    <div><p><?= $T('好的安防系统，应该在你需要的时候出现，<br>在不需要的时候安静地运行。', 'A good security system shows up when you need it,<br>and stays quiet when you don\'t.') ?></p><span>— <?= $T('CoSecu 设计原则', 'CoSecu Design Principle') ?></span></div>
    <div class="quote-orb"><span></span><i></i></div>
  </div>
</section>

<section class="news sec">
  <div class="wrap">
    <div class="sec-head"><div><div class="kicker"><span>05</span><em><?= $T('最新动态', 'LATEST NEWS') ?></em></div><h2><?= $T('洞察与<br><em>安防故事</em>', 'Insights &<br><em>security stories</em>') ?></h2></div><div class="sec-side"><p><?= $T('行业趋势、产品更新与真实项目复盘。', 'Industry trends, product updates and real project reviews.') ?></p><a class="btn btn-text" href="<?= e($newsUrl) ?>"><?= $T('全部资讯', 'ALL NEWS') ?> <span class="arrow">↗</span></a></div></div>
    <div class="news-grid">
      <?php foreach ($news as $i => $n): $d = $dateOf($n); ?>
      <a class="news-card" href="<?= e(lang_path('/a/' . $n['id'])) ?>"><div class="nc-img"><img src="/theme/blog<?= $i + 1 ?>.png" alt="<?= e($titleOf($n)) ?>" loading="lazy"></div><div class="nc-body"><div class="nc-date"><b><?= e($d['d']) ?></b><span><?= e($d['m']) ?></span><i><?= e($catName((int) $n['category_id'])) ?></i></div><h3><?= e($titleOf($n)) ?></h3><p><?= e($summaryOf($n, 66)) ?></p><span><?= $T('阅读全文', 'READ MORE') ?> ↗</span></div></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php include __DIR__ . '/_bottom.php'; ?>
