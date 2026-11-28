<?php include __DIR__ . '/_top.php'; ?>
<?php

$db = \think\facade\Db::name('article');

$catBySlug = [];
foreach ($nav as $c) {
    $catBySlug[$c['slug']] = $c;
}

$rowsOf = static function (string $slug, int $limit) use ($catBySlug, $db): array {
    if (!isset($catBySlug[$slug])) {
        return [];
    }
    $ids = [(int) $catBySlug[$slug]['id']];
    foreach (($catBySlug[$slug]['children'] ?? []) as $ch) {
        $ids[] = (int) $ch['id'];
    }
    return $db->whereIn('category_id', $ids)->where('status', 1)
        ->order('is_top desc, id desc')->limit($limit)->select()->toArray();
};
$rowsOfRaw = static function (string $slug, int $limit) use ($catBySlug): array {
    if (!isset($catBySlug[$slug])) {
        return [];
    }
    $ids = [(int) $catBySlug[$slug]['id']];
    foreach (($catBySlug[$slug]['children'] ?? []) as $ch) {
        $ids[] = (int) $ch['id'];
    }
    return \think\facade\Db::name('article')->whereIn('category_id', $ids)->where('status', 1)
        ->order('is_top desc, id desc')->limit($limit)->select()->toArray();
};

$products  = $rowsOfRaw('product', 6);
$solutions = $rowsOfRaw('solution', 3);
$news      = $rowsOfRaw('news', 4);
$service   = $rowsOfRaw('service', 5);

$coverOf = static function (array $a, string $fallback = '/theme/pic1.jpg'): string {
    $c = trim((string) ($a['cover'] ?? ''));
    return $c !== '' ? $c : $fallback;
};
$titleOf = static fn (array $a): string => (string) ml($a, 'title');
$summaryOf = static function (array $a, int $len = 70): string {
    $s = trim((string) ml($a, 'summary'));
    if ($s === '') {
        $s = trim(strip_tags((string) ml($a, 'content')));
    }
    $cut = mb_substr($s, 0, $len, 'UTF-8');
    // 英文摘要按整词截断，避免最后半截单词（中文仍按字数）
    if (mb_strlen($s, 'UTF-8') > $len && preg_match('/^[A-Za-z]/', $cut)) {
        $cut = preg_replace('/\s+\S*$/', '', $cut) . '…';
    }
    return $cut;
};
$dateOf = static function (array $a): array {
    $t = strtotime((string) ($a['create_time'] ?? ''));
    if (!$t) {
        return ['m-d' => '—', 'y' => ''];
    }
    return ['m-d' => date('m-d', $t), 'y' => date('Y', $t)];
};
// 中文标题前两个字染蓝；英文整词显示，避免把单词拆成两截
$splitTitle = static function (string $name): array {
    if (preg_match('/^\p{Han}/u', $name)) {
        return [mb_substr($name, 0, 2, 'UTF-8'), mb_substr($name, 2, null, 'UTF-8')];
    }
    return ['', $name];
};

$productCat  = $catBySlug['product'] ?? null;
$solutionCat = $catBySlug['solution'] ?? null;
$newsCat     = $catBySlug['news'] ?? null;
$serviceCat  = $catBySlug['service'] ?? null;
$productUrl  = lang_path('/c/' . ($productCat['slug'] ?? 'product'));
$solutionUrl = lang_path('/c/' . ($solutionCat['slug'] ?? 'solution'));
$newsUrl     = lang_path('/c/' . ($newsCat['slug'] ?? 'news'));
$serviceUrl  = lang_path('/c/' . ($serviceCat['slug'] ?? 'service'));

$productHead  = $splitTitle((string) ($productCat['name'] ?? lang('产品中心')));
$solutionHead = $splitTitle((string) ($solutionCat['name'] ?? lang('解决方案')));
$caseHead     = $splitTitle(lang('应用案例'));
$supportHead  = $splitTitle(lang('技术支持'));
$newsHead     = $splitTitle(lang('新闻中心'));
$aboutHead    = $splitTitle(lang('关于我们'));
$contactHead  = $splitTitle(lang('联系我们'));

// 首屏：取产品、解决方案里带封面的文章，最多 3 张
$heroItems = [];
foreach (array_merge(array_slice($products, 0, 2), array_slice($solutions, 0, 1)) as $a) {
    if (trim((string) ($a['cover'] ?? '')) === '') {
        continue;
    }
    if (isset($heroItems[$a['id']])) {
        continue;
    }
    $heroItems[$a['id']] = [
        'eyebrow' => (string) ($productCat['name'] ?? ''),
        'title'   => $titleOf($a),
        'summary' => $summaryOf($a, 90),
        'image'   => (string) $a['cover'],
        'link'    => lang_path('/a/' . (int) $a['id']),
    ];
}
$heroItems = array_values($heroItems);
if (!$heroItems) {
    $heroItems[] = [
        'eyebrow' => 'INDUSTRIAL AUTOMATION',
        'title'   => setting('site_slogan', $siteName),
        'summary' => '智能装备制造、工业自动化整线交付与精密零部件加工，从方案设计到现场调试一体化交付。',
        'image'   => '/theme/banner_b_clean.jpg',
        'link'    => $productUrl,
    ];
}
if (function_exists('plugin_filter')) {
    $heroItems = plugin_filter('home_slides', $heroItems);
}

// 应用案例：产品栏目第 4-6 篇，不足时用解决方案补齐
$cases = array_slice($products, 3, 3);
if (count($cases) < 3) {
    foreach ($solutions as $s) {
        if (count($cases) >= 3) { break; }
        $cases[] = $s;
    }
}

$aboutPage = null;
$contactPage = null;
foreach ($pages as $p) {
    if ($p['slug'] === 'about')   { $aboutPage = $p; }
    if ($p['slug'] === 'contact') { $contactPage = $p; }
}
$aboutParas = [];
if ($aboutPage && !empty($aboutPage['content'])) {
    preg_match_all('/<p[^>]*>(.*?)<\/p>/iu', (string) $aboutPage['content'], $m);
    foreach ($m[1] as $para) {
        $clean = trim(strip_tags($para));
        if ($clean !== '') { $aboutParas[] = $clean; }
        if (count($aboutParas) >= 4) { break; }
    }
}

// 常见问题：中文站用固定售后说明，英文站给英文版本
if (current_lang() === 'en') {
    $faqs = [
        ['q' => 'How long is the lead time for custom equipment?', 'a' => 'Standard single machines take 45–60 days; a complete line takes 90–120 days. Design, electrical assembly, in-house pre-acceptance and on-site commissioning are confirmed step by step.'],
        ['q' => 'Are on-site commissioning and training included?', 'a' => 'Yes. Our engineers stay on site for integration, cycle-time verification and safety tests, and train operators and maintenance staff by role.'],
        ['q' => 'How are spare parts and after-sales service handled?', 'a' => 'Fast-moving parts are kept in stock and shipped within 24 hours in the province or 48 hours elsewhere. Annual maintenance includes quarterly inspections and a response plan within 2 hours.'],
    ];
} else {
    $faqs = [
        ['q' => '非标设备的交付周期一般多久？', 'a' => '常规单机 45—60 天，整线 90—120 天。方案确认、机械设计、电气成套、厂内预验收、现场调试分节点推进，每个节点都有书面确认。'],
        ['q' => '设备到厂后是否包含现场调试和培训？', 'a' => '包含。调试工程师驻场完成联调、节拍验证与安全测试，并对操作、保养、维修人员做分岗培训，交付时提供纸质与电子版资料。'],
        ['q' => '备件供应和后续维保怎么保障？', 'a' => '常用备件厂内常备库存，省内 24 小时、省外 48 小时发出；年度维保按季度巡检，故障响应 2 小时内给出远程或到场方案。'],
    ];
}
?>
<div class="banner a" id="s_a">
  <div class="wk-hero" id="wkHero">
    <?php foreach ($heroItems as $i => $h): ?>
    <div class="wk-hero-slide<?= $i === 0 ? ' on' : '' ?>" style="background-image:url('<?= e($h['image']) ?>')">
      <div class="wk-hero-mask">
        <div class="wk-hero-in">
          <span class="wk-eyebrow"><?= e($h['eyebrow']) ?></span>
          <h1><?= e($h['title']) ?></h1>
          <p><?= e($h['summary']) ?></p>
          <div class="wk-hero-btns">
            <a class="wk-btn wk-btn-main" href="<?= e($h['link']) ?>"><?= e(lang('查看详情')) ?></a>
            <a class="wk-btn" href="<?= e($productUrl) ?>"><?= e(lang('产品中心')) ?></a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (count($heroItems) > 1): ?>
    <div class="wk-hero-dots">
      <?php foreach ($heroItems as $i => $h): ?>
        <button type="button" class="<?= $i === 0 ? 'on' : '' ?>" data-i="<?= $i ?>" aria-label="<?= e(lang('第')) ?><?= $i + 1 ?><?= e(lang('张')) ?>"></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="i_m i_ma b" id="s_b">
  <div class="i_name">
    <em><?= e($productHead[0]) ?></em><?= e($productHead[1]) ?>
    <p>PRODUCT CENTER</p>
  </div>
  <ul class="clearfix">
    <?php foreach (array_slice($products, 0, 3) as $i => $a): ?>
    <li>
      <a href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
        <div class="tu"><img src="<?= e($coverOf($a, '/theme/pic' . (($i % 3) + 1) . '.jpg')) ?>" alt="<?= e($titleOf($a)) ?>"></div>
        <div class="name"><?= e($titleOf($a)) ?></div>
        <p><?= e($summaryOf($a, 58)) ?></p>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
</div>

<div class="i_m i_mb c" id="s_c">
  <div class="i_name">
    <em><?= e($solutionHead[0]) ?></em><?= e($solutionHead[1]) ?>
    <p>SOLUTION</p>
  </div>
  <ul class="clearfix">
    <?php foreach (array_slice($solutions, 0, 3) as $i => $a): ?>
    <li>
      <a href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
        <div class="title"><?= e($titleOf($a)) ?></div>
        <img src="<?= e($coverOf($a, '/theme/pic4.jpg')) ?>" alt="<?= e($titleOf($a)) ?>">
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
  <div class="more"><a href="<?= e($solutionUrl) ?>">MORE</a></div>
</div>

<div class="i_m i_mb i_mc d" id="s_d">
  <div class="i_name">
    <em><?= e($caseHead[0]) ?></em><?= e($caseHead[1]) ?>
    <p>ENGINEERING CASE</p>
  </div>
  <ul class="clearfix">
    <?php foreach (array_slice($cases, 0, 3) as $i => $a): ?>
    <li>
      <a href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
        <div class="title"><?= e($titleOf($a)) ?></div>
        <img src="<?= e($coverOf($a, '/theme/pic5.jpg')) ?>" alt="<?= e($titleOf($a)) ?>">
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
</div>

<div class="i_m i_md e" id="s_e">
  <div class="i_name">
    <em><?= e($supportHead[0]) ?></em><?= e($supportHead[1]) ?>
    <p>TECHNICAL SUPPORT</p>
  </div>
  <div class="i_mdm clearfix">
    <div class="i_mdl">
      <div class="box_h">
        <span><?= e(lang('常见问题')) ?></span>
        <a href="<?= e($serviceUrl) ?>">MORE&gt;</a>
      </div>
      <ul>
        <?php foreach ($faqs as $f): ?>
        <li class="wen"><span><?= e(lang('问')) ?></span><div class="nr"><?= e($f['q']) ?></div></li>
        <li class="da"><span><?= e(lang('答')) ?></span><div class="nr"><p><?= e($f['a']) ?></p></div></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="i_mdr">
      <div class="box_h">
        <span><?= e($serviceCat['name'] ?? lang('资料下载')) ?></span>
        <a href="<?= e($serviceUrl) ?>">MORE&gt;</a>
      </div>
      <ul>
        <?php foreach ($service as $a): ?>
        <li>
          <a href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
            <p><?= e($titleOf($a)) ?></p>
            <em><?= e(substr((string) ($a['create_time'] ?? ''), 0, 10)) ?></em>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

<div class="i_m i_me f" id="s_f">
  <div class="i_name">
    <em><?= e($newsHead[0]) ?></em><?= e($newsHead[1]) ?>
    <p>NEWS</p>
  </div>
  <ul>
    <?php foreach ($news as $a): $d = $dateOf($a); ?>
    <li>
      <dl class="clearfix">
        <a href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
          <dt><?= e($d['m-d']) ?><p><?= e($d['y']) ?> YEAR</p></dt>
          <dd>
            <div class="title"><?= e($titleOf($a)) ?></div>
            <p><?= e($summaryOf($a, 90)) ?></p>
          </dd>
        </a>
      </dl>
    </li>
    <?php endforeach; ?>
  </ul>
  <div class="more"><a href="<?= e($newsUrl) ?>">MORE</a></div>
</div>

<div class="i_m i_mf g" id="s_g">
  <div class="i_name">
    <em><?= e($aboutHead[0]) ?></em><?= e($aboutHead[1]) ?>
    <p>ABOUT US</p>
  </div>
  <div class="i_mfm">
    <?php if ($aboutParas): ?>
      <?php foreach ($aboutParas as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
    <?php else: ?>
      <p><?= e($siteName) ?>专注于智能装备制造、工业自动化整线交付与精密零部件加工，具备方案设计、机械加工、电气成套到现场调试的完整交付能力。</p>
    <?php endif; ?>
  </div>
  <div class="more"><a href="<?= e(lang_path('/p/' . ($aboutPage['slug'] ?? 'about'))) ?>">MORE</a></div>
</div>

<div class="i_m i_mg">
  <div class="i_name">
    <em><?= e($contactHead[0]) ?></em><?= e($contactHead[1]) ?>
    <p>CONTACT US</p>
  </div>
  <div class="i_mgm">
    <div class="wk-contact-grid">
      <div class="wk-contact-card">
        <img src="/theme/icon5.png" alt="">
        <h4><?= e(lang('销售热线')) ?></h4>
        <p><a href="tel:<?= e(preg_replace('/[^0-9\-+]/', '', $phone)) ?>"><?= e($phone) ?></a></p>
      </div>
      <div class="wk-contact-card">
        <img src="/theme/icon6.png" alt="">
        <h4><?= e(lang('邮箱')) ?></h4>
        <p><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></p>
      </div>
      <div class="wk-contact-card">
        <img src="/theme/icon7.png" alt="">
        <h4><?= e(lang('厂区地址')) ?></h4>
        <p><?= e($address) ?></p>
      </div>
    </div>
    <form class="wk-contact-form" data-email="<?= e($email) ?>">
      <input type="text" name="name" placeholder="<?= e(lang('您的姓名')) ?>" required>
      <input type="text" name="phone" placeholder="<?= e(lang('联系电话')) ?>" required>
      <button type="submit"><?= e(lang('发送留言')) ?></button>
      <div class="wk-contact-tip"><?= e(lang('提交后将调用本机邮件客户端发送至')) ?> <?= e($email) ?>；<?= e(lang('也可直接致电')) ?> <?= e($phone) ?>。</div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/_bottom.php'; ?>



