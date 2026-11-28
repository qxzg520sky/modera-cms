<?php include __DIR__ . '/_top.php'; ?>
<?php
$catBySlug = [];
foreach ($nav as $c) { $catBySlug[$c['slug']] = $c; }

$rowsOf = static function (string $slug, int $limit) use ($catBySlug): array {
    if (!isset($catBySlug[$slug])) { return []; }
    $ids = [(int) $catBySlug[$slug]['id']];
    foreach (($catBySlug[$slug]['children'] ?? []) as $ch) { $ids[] = (int) $ch['id']; }
    return \think\facade\Db::name('article')->whereIn('category_id', $ids)->where('status', 1)
        ->order('is_top desc, id desc')->limit($limit)->select()->toArray();
};

$products = $rowsOf('product', 6);
$news = \think\facade\Db::name('article')->where('category_id', 5)->where('status', 1)
    ->order('id desc')->limit(3)->select()->toArray();
$newsItems = is_array($news) ? array_slice($news, 0, 3) : [];
if (!$newsItems) { $newsItems = array_slice($products, 0, 3); }
$solutions = $rowsOf('solution', 3);

$coverOf = static function (array $a, string $fallback = '/theme/catbuilding.jpg'): string {
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
$productUrl = lang_path('/c/' . ($productCat['slug'] ?? 'product'));
$newsUrl = lang_path('/c/' . ($newsCat['slug'] ?? 'news'));

$aboutPage = null;
foreach ($pages as $p) { if (($p['slug'] ?? '') === 'about') { $aboutPage = $p; } }
$aboutParas = [];
if ($aboutPage && !empty($aboutPage['content'])) {
    preg_match_all('/<p[^>]*>(.*?)<\/p>/iu', (string) $aboutPage['content'], $m);
    foreach ($m[1] as $para) {
        $clean = trim(strip_tags($para));
        if ($clean !== '') { $aboutParas[] = $clean; }
        if (count($aboutParas) >= 2) { break; }
    }
}
$aboutUrl = lang_path('/p/' . ($aboutPage['slug'] ?? 'about'));

$cats = [
    ['name' => '建筑与铺地', 'en' => 'ARCHITECTURE', 'image' => '/theme/catbuilding.jpg', 'url' => $productUrl],
    ['name' => '水处理材料', 'en' => 'WATER TREATMENT', 'image' => '/theme/catwater.jpg', 'url' => $productUrl],
    ['name' => '保温隔热', 'en' => 'INSULATION', 'image' => '/theme/catinsulation.jpg', 'url' => $productUrl],
    ['name' => '电子压合', 'en' => 'ELECTRONICS', 'image' => '/theme/catsilicone.jpg', 'url' => $productUrl],
];
?>

<section class="wx-hero">
  <div class="wx-hero-bg" style="background-image:url('/theme/heroindustry.jpg')"></div>
  <div class="wx-inner wx-hero-in">
    <span class="wx-eyebrow">TRUELAND · INDUSTRIAL MATERIALS</span>
    <h1>材料，让制造<br>更稳定</h1>
    <p>橡胶 · 塑料 · 复合材料<br>从配方研发到精密成型，为工业客户提供批次稳定的材料解决方案。</p>
    <div class="wx-hero-btns">
      <a class="wx-btn-main" href="<?= e($productUrl) ?>"><?= e(lang('查看产品')) ?></a>
      <a class="wx-btn-line" href="<?= e($aboutUrl) ?>"><?= e(lang('了解我们')) ?> <em>→</em></a>
    </div>
  </div>
  <div class="wx-hero-side">
    <span>01</span>
    <p>MATERIAL<br>ENGINEERING</p>
  </div>
</section>

<section class="wx-cats">
  <div class="wx-inner wx-cat-grid">
    <?php foreach ($cats as $c): ?>
      <a class="wx-cat" href="<?= e($c['url']) ?>">
        <img src="<?= e($c['image']) ?>" alt="<?= e($c['name']) ?>">
        <div class="wx-cat-mask"></div>
        <div class="wx-cat-txt"><small><?= e($c['en']) ?></small><h3><?= e($c['name']) ?></h3></div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="wx-section wx-products">
  <div class="wx-inner">
    <div class="wx-head">
      <small>PRODUCT CENTER</small>
      <h2><?= e($productCat['name'] ?? lang('产品中心')) ?></h2>
      <p>从基础材料到精密制品，围绕橡胶、塑料与复合材料建立稳定的配方、成型与检测能力。</p>
      <a class="wx-more" href="<?= e($productUrl) ?>"><?= e(lang('查看全部')) ?> <em>→</em></a>
    </div>
    <?php if ($products): ?>
    <div class="wx-product-grid">
      <?php foreach (array_slice($products, 0, 4) as $i => $a): ?>
      <a class="wx-product" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
        <div class="wx-product-img"><img src="<?= e($coverOf($a)) ?>" alt="<?= e($titleOf($a)) ?>" loading="<?= $i > 1 ? 'lazy' : 'eager' ?>"></div>
        <div class="wx-product-txt">
          <span>0<?= $i + 1 ?></span>
          <h3><?= e($titleOf($a)) ?></h3>
          <p><?= e($summaryOf($a, 52)) ?></p>
        </div>
        <i>→</i>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <div class="wx-empty"><?= e(lang('暂无产品，请在后台添加')) ?></div>
    <?php endif; ?>
  </div>
</section>

<?php if ($solutions): ?>
<section class="wx-section wx-solutions">
  <div class="wx-inner">
    <div class="wx-head">
      <small>APPLICATIONS</small>
      <h2><?= e(lang('应用与方案')) ?></h2>
      <p>把材料性能放进真实工况里验证，再交付可复用的配方与工艺。</p>
    </div>
    <div class="wx-solution-grid">
      <?php foreach (array_slice($solutions, 0, 3) as $i => $a): ?>
      <a class="wx-solution" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
        <img src="<?= e($coverOf($a)) ?>" alt="<?= e($titleOf($a)) ?>" loading="lazy">
        <div><small>0<?= $i + 1 ?> / APPLICATION</small><h3><?= e($titleOf($a)) ?></h3><p><?= e($summaryOf($a, 46)) ?></p></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="wx-section wx-news">
  <div class="wx-inner">
    <div class="wx-head wx-head-light">
      <small>NEWS</small>
      <h2><?= e($newsCat['name'] ?? lang('新闻中心')) ?></h2>
      <p>记录材料研发、工艺改进与交付现场的最新进展。</p>
      <a class="wx-more wx-more-light" href="<?= e($newsUrl) ?>"><?= e(lang('查看全部')) ?> <em>→</em></a>
    </div>
    <div class="wx-news-grid">
      <?php foreach ($newsItems as $i => $a): $d = $dateOf($a); ?>
      <a class="wx-news-card" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
        <div class="wx-news-img"><img src="<?= e($coverOf($a, '/theme/catbuilding.jpg')) ?>" alt="<?= e($titleOf($a)) ?>" loading="lazy"></div>
        <div class="wx-news-txt">
          <span class="wx-date"><?= e($d['d']) ?></span>
          <h3><?= e($titleOf($a)) ?></h3>
          <p><?= e($summaryOf($a, 62)) ?></p>
          <em>READ MORE →</em>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="wx-about">
  <div class="wx-inner wx-about-in">
    <div class="wx-about-copy">
      <small>ABOUT TRUELAND</small>
      <h2><?= e($aboutPage['title'] ?? lang('关于我们')) ?></h2>
      <?php if ($aboutParas): ?>
        <?php foreach ($aboutParas as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
      <?php else: ?>
        <p><?= e($siteName) ?>长期专注于橡胶、塑料及复合材料的配方研发与精密成型，产品覆盖建筑铺地、离子交换、保温隔热和电子压合等应用场景。</p>
      <?php endif; ?>
      <a class="wx-btn-dark" href="<?= e($aboutUrl) ?>"><?= e(lang('了解更多')) ?> <em>→</em></a>
    </div>
    <div class="wx-about-media">
      <img src="/theme/heroindustry.jpg" alt="<?= e($siteName) ?>" loading="lazy">
      <span>EST. 2005</span>
    </div>
  </div>
  <div class="wx-inner wx-stats">
    <div><b>20<small>年</small></b><span><?= e(lang('材料研发经验')) ?></span></div>
    <div><b>6<small>条</small></b><span><?= e(lang('核心生产线')) ?></span></div>
    <div><b>300<small>+</small></b><span><?= e(lang('长期合作客户')) ?></span></div>
    <div><b>ISO<small>9001</small></b><span><?= e(lang('质量管理体系')) ?></span></div>
  </div>
</section>

<section class="wx-contact">
  <div class="wx-inner wx-contact-in">
    <div>
      <small>CONTACT</small>
      <h2><?= e(lang('把材料问题，交给我们')) ?></h2>
      <p><?= e(lang('告诉我们应用场景、性能要求与预计用量，我们会给出可执行的选型与工艺建议。')) ?></p>
    </div>
    <div class="wx-contact-ways">
      <a href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', $phone)) ?>"><span><?= e(lang('销售热线')) ?></span><b><?= e($phone) ?></b></a>
      <a href="mailto:<?= e($email) ?>"><span><?= e(lang('邮箱')) ?></span><b><?= e($email) ?></b></a>
      <p><span><?= e(lang('厂区地址')) ?></span><b><?= e($address) ?></b></p>
    </div>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
