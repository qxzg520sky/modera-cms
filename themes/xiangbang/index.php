<?php include __DIR__ . '/_top.php'; ?>
<?php
$db = \think\facade\Db::name('article');
$catBySlug = [];
foreach ($nav as $c) {
    $catBySlug[(string) $c['slug']] = $c;
}

$idsOf = static function (string $slug) use ($catBySlug): array {
    if (!isset($catBySlug[$slug])) {
        return [];
    }
    $ids = [(int) $catBySlug[$slug]['id']];
    foreach (($catBySlug[$slug]['children'] ?? []) as $ch) {
        $ids[] = (int) $ch['id'];
    }
    return $ids;
};
$rowsOf = static function (string $slug, int $limit) use ($idsOf): array {
    $ids = $idsOf($slug);
    if (!$ids) {
        return [];
    }
    return \think\facade\Db::name('article')->whereIn('category_id', $ids)->where('status', 1)
        ->order('is_top desc, id desc')->limit($limit)->select()->toArray();
};
$countOf = static function (string $slug) use ($idsOf): int {
    $ids = $idsOf($slug);
    if (!$ids) {
        return 0;
    }
    return (int) \think\facade\Db::name('article')->whereIn('category_id', $ids)->where('status', 1)->count();
};

$products  = $rowsOf('product', 6);
$solutions = $rowsOf('solution', 3);
$services  = $rowsOf('service', 4);
$news      = $rowsOf('news', 3);
if (!$products && !empty($articles)) {
    $products = array_slice($articles, 0, 6);
}
if (!$news && !empty($articles)) {
    $news = array_slice($articles, 0, 3);
}

$productCat = $catBySlug['product'] ?? null;
$solutionCat = $catBySlug['solution'] ?? null;
$newsCat = $catBySlug['news'] ?? null;
$serviceCat = $catBySlug['service'] ?? null;
$productUrl = xb_cat_url((string) ($productCat['slug'] ?? 'product'));
$solutionUrl = xb_cat_url((string) ($solutionCat['slug'] ?? 'solution'));
$newsUrl = xb_cat_url((string) ($newsCat['slug'] ?? 'news'));
$serviceUrl = xb_cat_url((string) ($serviceCat['slug'] ?? 'service'));

$heroTitle = xb_split_slogan($slogan !== '' ? $slogan : '让每一面墙 · 自由呼吸');
$heroDesc = trim((string) setting('site_description', ''));
if (mb_strlen($heroDesc, 'UTF-8') > 110) {
    $heroDesc = mb_substr($heroDesc, 0, 110, 'UTF-8') . '…';
}
$heroDesc = $heroDesc !== '' ? $heroDesc : '从材料、工艺到交付，把稳定的品质和克制的设计，带进每一个正在发生的空间。';
$productCount = $countOf('product');
$solutionCount = $countOf('solution');
$newsCount = $countOf('news');

$aboutPage = null;
foreach ($pages as $p) {
    if (($p['slug'] ?? '') === 'about') { $aboutPage = $p; }
}
$aboutHtml = (string) ($aboutPage['content'] ?? '');
$aboutParas = xb_paragraphs($aboutHtml, 2);
$aboutImg = xb_first_image($aboutHtml, '/theme/hero.jpg');
$aboutUrl = $aboutPage ? xb_page_url((string) $aboutPage['slug']) : lang_path('/');

$contactPage = null;
foreach ($pages as $p) {
    if (($p['slug'] ?? '') === 'contact') { $contactPage = $p; }
}
$contactUrl = $contactPage ? xb_page_url((string) $contactPage['slug']) : lang_path('/');

$supportCards = [];
foreach ($services as $s) {
    $supportCards[] = ['title' => xb_title($s), 'desc' => xb_summary($s, 60), 'url' => xb_article_url($s)];
}
$supportFallback = [
    ['title' => '从需求到落地', 'desc' => '先理解场景，再谈产品与配置，避免过度设计。', 'url' => $serviceUrl],
    ['title' => '稳定交付', 'desc' => '节点清晰、责任到人，把不确定性留在工厂里。', 'url' => $serviceUrl],
    ['title' => '长期服务', 'desc' => '交付不是结束，而是后续维护与升级的开始。', 'url' => $serviceUrl],
    ['title' => '持续迭代', 'desc' => '把每个项目的经验，沉淀成下一套更好的方案。', 'url' => $serviceUrl],
];
// 服务文章不足 4 条时补足，避免“为什么选择我们”出现整块空位
foreach ($supportFallback as $fallback) {
    if (count($supportCards) >= 4) {
        break;
    }
    $exists = false;
    foreach ($supportCards as $card) {
        if (($card['title'] ?? '') === $fallback['title']) {
            $exists = true;
            break;
        }
    }
    if (!$exists) {
        $supportCards[] = $fallback;
    }
}
?>

<section class="xb-hero">
  <div class="xb-hero-bg" style="background-image:url('/theme/hero.jpg')"></div>
  <div class="xb-hero-mask"></div>
  <div class="xb-container xb-hero-inner">
    <div class="xb-hero-copy">
      <span class="xb-hero-eyebrow"><i></i><?= e($siteName) ?> · <?= e(lang('自然质感与长期主义', '自然质感与长期主义')) ?></span>
      <h1><?= e($heroTitle[0]) ?><?php if (!empty($heroTitle[1])): ?><br><em><?= e($heroTitle[1]) ?></em><?php endif; ?></h1>
      <p><?= e($heroDesc) ?></p>
      <div class="xb-hero-actions">
        <a class="xb-btn xb-btn-lime" href="<?= e($productUrl) ?>"><?= e(lang('浏览产品与方案', '浏览产品与方案')) ?> <span>↗</span></a>
        <a class="xb-btn xb-btn-ghost" href="<?= e($contactUrl) ?>"><?= e(lang('预约沟通', '预约沟通')) ?> <span>→</span></a>
      </div>
    </div>

    <div class="xb-hero-stats">
      <div><b><?= (int) $productCount ?><span><?= e(lang('款', '款')) ?></span></b><small><?= e(lang('产品与方案', '产品与方案')) ?></small></div>
      <div><b><?= (int) $solutionCount ?><span><?= e(lang('套', '套')) ?></span></b><small><?= e(lang('行业解决方案', '行业解决方案')) ?></small></div>
      <div><b><?= (int) $newsCount ?><span><?= e(lang('条', '条')) ?></span></b><small><?= e(lang('动态与洞察', '动态与洞察')) ?></small></div>
    </div>
  </div>
  <div class="xb-hero-rail"><span>SCROLL TO EXPLORE</span></div>
</section>

<section class="xb-section xb-intro">
  <div class="xb-container xb-intro-grid">
    <div class="xb-intro-media" data-reveal>
      <img src="<?= e($aboutImg) ?>" alt="<?= e($aboutPage ? ml($aboutPage, 'title') : $siteName) ?>" loading="lazy">
      <div class="xb-intro-badge"><strong><?= e(lang('自然', '自然')) ?> · <?= e(lang('健康', '健康')) ?> · <?= e(lang('质感', '质感')) ?></strong><span><?= e(lang('把材料本来的样子，带进日常空间', '把材料本来的样子，带进日常空间')) ?></span></div>
    </div>
    <div class="xb-intro-copy" data-reveal data-delay="1">
      <span class="xb-eyebrow">01 / <?= e($aboutPage ? ml($aboutPage, 'title') : lang('关于我们', '关于我们')) ?></span>
      <h2 class="xb-section-title"><?= e(lang('好产品，源自好材料与好工艺', '好产品，源自好材料与好工艺')) ?></h2>
      <?php if ($aboutParas): ?>
        <?php foreach ($aboutParas as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
      <?php else: ?>
        <p><?= e($heroDesc) ?></p>
      <?php endif; ?>
      <div class="xb-intro-points">
        <div><b>01</b><span><?= e(lang('材料可追溯', '材料可追溯')) ?></span></div>
        <div><b>02</b><span><?= e(lang('工艺可验证', '工艺可验证')) ?></span></div>
        <div><b>03</b><span><?= e(lang('服务有响应', '服务有响应')) ?></span></div>
      </div>
      <a class="xb-text-link" href="<?= e($aboutUrl) ?>"><?= e(lang('了解更多', '了解更多')) ?> <span>→</span></a>
    </div>
  </div>
</section>

<section class="xb-section xb-products">
  <div class="xb-container">
    <?php xb_section_head('02 / ' . ($productCat ? ml($productCat, 'name') : lang('产品中心', '产品中心')), lang('把复杂工艺，<br>做成稳定的日常', '把复杂工艺，<br>做成稳定的日常'), lang('从单个产品到整线方案，用可验证的细节，回应用户真正关心的问题。', '从单个产品到整线方案，用可验证的细节，回应用户真正关心的问题。')); ?>
    <div class="xb-product-grid">
      <?php foreach ($products as $i => $a): ?>
        <article class="xb-product-card" data-reveal data-delay="<?= $i % 3 ?>">
          <a href="<?= e(xb_article_url($a)) ?>">
            <div class="xb-product-thumb"><img src="<?= e(xb_cover($a)) ?>" alt="<?= e(xb_title($a)) ?>" loading="lazy"></div>
            <div class="xb-product-body">
              <span class="xb-product-tag"><?= e($catBySlug['product']['name'] ?? lang('产品', '产品')) ?></span>
              <h3><?= e(xb_title($a)) ?></h3>
              <p><?= e(xb_summary($a, 48)) ?></p>
              <span class="xb-card-arrow">↗</span>
            </div>
          </a>
        </article>
      <?php endforeach; ?>
      <?php if (!$products): ?>
        <div class="xb-empty"><?= e(lang('暂无产品内容，请在后台添加。', '暂无产品内容，请在后台添加。')) ?></div>
      <?php endif; ?>
    </div>
    <div class="xb-section-actions"><a class="xb-btn xb-btn-dark" href="<?= e($productUrl) ?>"><?= e(lang('查看全部产品', '查看全部产品')) ?> <span>→</span></a></div>
  </div>
</section>

<section class="xb-dark-band">
  <div class="xb-container">
    <div class="xb-dark-head" data-reveal>
      <div>
        <span class="xb-eyebrow xb-eyebrow-light">03 / <?= e(lang('为什么选择我们', '为什么选择我们')) ?></span>
        <h2><?= lang('选择一个品牌，<br>也是选择一套交付系统', '选择一个品牌，也是选择一套交付系统') ?></h2>
      </div>
      <p><?= e(lang('市场在变，真正稀缺的是稳定的交付和长期的响应。把复杂留给自己，把确定留给客户。', '市场在变，真正稀缺的是稳定的交付和长期的响应。把复杂留给自己，把确定留给客户。')) ?></p>
    </div>
    <div class="xb-support-grid">
      <?php foreach (array_slice($supportCards, 0, 4) as $i => $c): ?>
        <article class="xb-support-card" data-reveal data-delay="<?= $i ?>">
          <span class="xb-support-num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <h3><?= e($c['title']) ?></h3>
          <p><?= e($c['desc']) ?></p>
          <a href="<?= e($c['url']) ?>"><?= e(lang('了解详情', '了解详情')) ?> →</a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="xb-section xb-news">
  <div class="xb-container">
    <?php xb_section_head('04 / ' . ($newsCat ? ml($newsCat, 'name') : lang('新闻中心', '新闻中心')), lang('正在发生，<br>与值得分享的', '正在发生，<br>与值得分享的'), lang('从项目交付到行业观察，用更短的时间，了解更有用的信息。', '从项目交付到行业观察，用更短的时间，了解更有用的信息。')); ?>
    <div class="xb-news-grid">
      <?php foreach ($news as $i => $a): ?>
        <article class="xb-news-card" data-reveal data-delay="<?= $i ?>">
          <a href="<?= e(xb_article_url($a)) ?>">
            <div class="xb-news-thumb"><img src="<?= e(xb_cover($a)) ?>" alt="<?= e(xb_title($a)) ?>" loading="lazy"></div>
            <div class="xb-news-body">
              <div class="xb-news-meta"><em><?= e($catBySlug['news']['name'] ?? lang('动态', '动态')) ?></em><span><?= e(xb_date($a, 'Y.m.d')) ?></span></div>
              <h3><?= e(xb_title($a)) ?></h3>
              <p><?= e(xb_summary($a, 56)) ?></p>
              <span class="xb-news-more"><?= e(lang('阅读全文', '阅读全文')) ?> →</span>
            </div>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="xb-section-actions"><a class="xb-btn xb-btn-outline" href="<?= e($newsUrl) ?>"><?= e(lang('查看全部动态', '查看全部动态')) ?> <span>→</span></a></div>
  </div>
</section>

<section class="xb-cta">
  <div class="xb-container xb-cta-inner">
    <div>
      <span class="xb-eyebrow xb-eyebrow-dark">05 / <?= e(lang('开始沟通', '开始沟通')) ?></span>
      <h2><?= lang('说说你的场景，<br>我们一起把方案落地', '说说你的场景，我们一起把方案落地') ?></h2>
      <p><?= e(lang('告诉我们空间、产能或工艺目标，我们会给出更具体的产品与交付建议。', '告诉我们空间、产能或工艺目标，我们会给出更具体的产品与交付建议。')) ?></p>
    </div>
    <div class="xb-cta-actions">
      <a class="xb-btn xb-btn-dark" href="<?= e($contactUrl) ?>"><?= e(lang('联系我们', '联系我们')) ?> <span>→</span></a>
      <?php if ($phone !== ''): ?><a class="xb-cta-phone" href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', $phone)) ?>"><?= e($phone) ?></a><?php endif; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
