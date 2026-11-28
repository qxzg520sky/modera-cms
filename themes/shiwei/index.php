<?php include __DIR__ . '/_top.php'; ?>
<?php
$rows = static function (array $catIds, int $limit): array {
    if (!$catIds) {
        return [];
    }
    return \think\facade\Db::name('article')
        ->whereIn('category_id', $catIds)
        ->where('status', 1)
        ->order('is_top desc, id desc')
        ->limit($limit)
        ->select()
        ->toArray();
};

$catBySlug = [];
foreach ($nav as $c) {
    $catBySlug[$c['slug']] = $c;
}
$catIds = static function (?array $c): array {
    if (!$c) {
        return [];
    }
    $ids = [(int) $c['id']];
    foreach (($c['children'] ?? []) as $ch) {
        $ids[] = (int) $ch['id'];
    }
    return $ids;
};

$productCat  = $catBySlug['product'] ?? null;
$solutionCat = $catBySlug['solution'] ?? null;
$serviceCat  = $catBySlug['service'] ?? null;

// 首屏：优先使用轮播插件数据，未启用时用企业默认文案
$heroDefaults = [
    [
        'image'     => '/uploads/factory.jpg',
        'eyebrow'   => 'INTELLIGENT MANUFACTURING',
        'title'     => '以匠心制造，为工业赋能',
        'summary'   => '从方案设计、机械加工、电气成套到现场调试，为制造企业交付稳定可靠的智能装备与自动化整线。',
        'link'      => lang_path('/c/product'),
        'link_text' => lang('了解产品'),
        'link2'     => lang_path('/p/about'),
        'link2_text'=> lang('走进企业'),
        'target'    => '_self',
    ],
    [
        'image'     => '/uploads/line.jpg',
        'eyebrow'   => 'TURNKEY AUTOMATION',
        'title'     => '整线交付，让产线稳定跑起来',
        'summary'   => '围绕节拍、合格率与可维护性做设计，把承诺写进合同，用数据验收每一条产线。',
        'link'      => lang_path('/c/solution'),
        'link_text' => lang('查看方案'),
        'link2'     => '',
        'link2_text'=> '',
        'target'    => '_self',
    ],
    [
        'image'     => '/uploads/team.jpg',
        'eyebrow'   => 'ENGINEERING TEAM',
        'title'     => '把每一台设备做扎实',
        'summary'   => '46 人技术团队，五轴加工、车铣复合与大型龙门铣配套齐全，从图纸会签到终身维保全程负责。',
        'link'      => lang_path('/p/about'),
        'link_text' => lang('了解我们'),
        'link2'     => '',
        'link2_text'=> '',
        'target'    => '_self',
    ],
];
$hero = plugin_filter('home_slides', $heroDefaults);
if (!is_array($hero) || !$hero) {
    $hero = $heroDefaults;
}
$hero = array_values(array_filter($hero, static fn($s) => !empty($s['image'])));
if (!$hero) {
    $hero = $heroDefaults;
}

// 解决方案四卡
$solCards = $rows($catIds($solutionCat), 4);
if (count($solCards) < 4) {
    $solCards[] = [
        'title'   => lang('非标定制'),
        'summary' => lang('按产品工艺、节拍与场地条件做非标设计与改造，先做方案验证，再落地交付。'),
        'cover'   => '/uploads/workshop.jpg',
        'id'      => 0,
    ];
}
$solCards = array_slice($solCards, 0, 4);

// 服务四卡
$serviceItems = [
    ['t' => lang('系统集成'), 'e' => 'SYSTEM INTEGRATION', 'd' => lang('PLC、机器人、视觉与上位机统一组网，打通设备层到管理层的数据链路。'), 'i' => 1],
    ['t' => lang('网络部署'), 'e' => 'NETWORK DEPLOYMENT', 'd' => lang('工业以太网、无线覆盖与网络安全分区，按现场拓扑一次规划到位。'), 'i' => 2],
    ['t' => lang('产线运维'), 'e' => 'LINE MAINTENANCE', 'd' => lang('预防性巡检、备件管理与远程诊断，把停机时间压到最低。'), 'i' => 3],
    ['t' => lang('技术支持'), 'e' => 'TECHNICAL SUPPORT', 'd' => lang('7×12 小时响应，程序备份、操作培训与二次改造随叫随到。'), 'i' => 4],
];

// 产品横滑
$productCards = $rows($catIds($productCat), 6);
$productsUrl  = $productCat ? lang_path('/c/' . $productCat['slug']) : lang_path('/');
?>

<section class="sw-hero" aria-label="<?= e(lang('企业宣传')) ?>">
  <?php foreach ($hero as $i => $s): ?>
    <div class="sw-slide <?= $i === 0 ? 'on' : '' ?>" style="background-image:url('<?= e($s['image']) ?>')">
      <div class="sw-hero-in">
        <span class="sw-hero-eyebrow"><?= e($s['eyebrow'] ?? '') ?></span>
        <h1><?= e($s['title'] ?? '') ?></h1>
        <p><?= e($s['summary'] ?? '') ?></p>
        <div class="sw-hero-btns">
          <?php if (!empty($s['link'])): ?>
            <a class="sw-btn sw-btn-primary" href="<?= e($s['link']) ?>" target="<?= e($s['target'] ?? '_self') ?>"><?= e($s['link_text'] ?: '查看详情') ?></a>
          <?php endif; ?>
          <?php if (!empty($s['link2'])): ?>
            <a class="sw-btn sw-btn-ghost" href="<?= e($s['link2']) ?>" target="<?= e($s['target'] ?? '_self') ?>"><?= e($s['link2_text'] ?: '了解更多') ?></a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (count($hero) > 1): ?>
    <div class="sw-hero-dots" aria-label="<?= e(lang('轮播切换')) ?>">
      <?php foreach ($hero as $i => $s): ?>
        <button type="button" class="<?= $i === 0 ? 'on' : '' ?>" data-i="<?= $i ?>" aria-label="<?= e(sprintf('%s %d', lang('第'), $i + 1)) ?>"></button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <svg class="sw-cloud" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true">
    <path fill="#ffffff" d="M0 66c76-19 132-32 199-30 87 2 123 26 198 26 72 0 105-9 160-20-43-7-86-4-126 0-57 5-87 13-131 22-53 11-82 12-115 7-61-10-83-22-118-18-34 4-50 12-67 13z" opacity=".96"/>
    <path fill="#ffffff" d="M0 72V58c72-15 126-24 184-22 84 2 128 20 203 21 77 1 112-8 164-17-46-7-91-3-135 1-57 4-90 14-137 23-42 8-69 9-101 4-59-8-80-19-113-14-35 5-48 11-65 15z"/>
  </svg>
</section>

<section class="sw-sec" id="solution">
  <div class="sw-container">
    <div class="sw-sec-head">
      <div class="sw-head-row"><i></i><h2><?= e(lang('解决方案')) ?></h2><i></i></div>
      <em>SOLUTIONS</em>
    </div>
    <div class="sw-sol-grid">
      <?php foreach ($solCards as $i => $a): ?>
        <?php
          $aTitle = trim((string) ml($a, 'title'));
          $aSum   = trim(strip_tags((string) ml($a, 'summary')));
          if ($aSum === '') { $aSum = mb_substr(trim(strip_tags((string) ml($a, 'content')), '　 '), 0, 80) . '…'; }
          $aUrl   = !empty($a['id']) ? lang_path('/a/' . (int) $a['id']) : $productsUrl;
        ?>
        <a class="sw-sol-card" href="<?= e($aUrl) ?>">
          <div class="sw-sol-ico">
            <?php if ($i % 4 === 0): ?>
              <svg viewBox="0 0 100 100"><rect x="18" y="18" width="64" height="64" rx="4"/><path d="M50 18v64M18 50h64"/><circle cx="50" cy="50" r="12"/></svg>
            <?php elseif ($i % 4 === 1): ?>
              <svg viewBox="0 0 100 100"><path d="M50 12 87 78H13z"/><circle cx="50" cy="58" r="13"/><path d="M50 12v33"/></svg>
            <?php elseif ($i % 4 === 2): ?>
              <svg viewBox="0 0 100 100"><circle cx="50" cy="50" r="34"/><path d="M50 16v10M50 74v10M16 50h10M74 50h10"/><rect x="37" y="37" width="26" height="26" rx="2"/><path d="M50 28v12M50 60v12"/></svg>
            <?php else: ?>
              <svg viewBox="0 0 100 100"><path d="M22 30h56v44H22z"/><path d="M32 30v-9h36v9"/><path d="M38 44h9v9h-9zM53 44h9v9h-9zM38 58h9v9h-9zM53 58h9v9h-9z"/></svg>
            <?php endif; ?>
          </div>
          <h3><?= e($aTitle) ?></h3>
          <p><?= e($aSum) ?></p>
          <span class="sw-sol-more"><?= e(lang('了解更多')) ?> →</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sw-service" id="service" style="background-image:url('/uploads/line.jpg')">
  <div class="sw-container">
    <div class="sw-sec-head">
      <div class="sw-head-row on-dark"><i></i><h2><?= e(lang('服务支持')) ?></h2><i></i></div>
      <em>SERVICES</em>
    </div>
    <div class="sw-serv-grid">
      <?php foreach ($serviceItems as $it): ?>
        <a class="sw-serv-card" href="<?= e($serviceCat ? lang_path('/c/' . $serviceCat['slug']) : $contactUrl) ?>">
          <div class="sw-serv-ring">
            <?php if ($it['i'] === 1): ?>
              <svg viewBox="0 0 100 100"><circle cx="50" cy="50" r="31"/><path d="M50 19v12M50 69v12M19 50h12M69 50h12"/><rect x="38" y="38" width="24" height="24" rx="2"/></svg>
            <?php elseif ($it['i'] === 2): ?>
              <svg viewBox="0 0 100 100"><circle cx="50" cy="50" r="31"/><path d="M50 50 72 35M50 50l-2 22"/><circle cx="50" cy="50" r="4"/><path d="M30 28a28 28 0 0 1 40-4"/></svg>
            <?php elseif ($it['i'] === 3): ?>
              <svg viewBox="0 0 100 100"><circle cx="50" cy="50" r="31"/><path d="M50 32v20l14 9"/><path d="M30 23a28 28 0 0 1 40-3"/></svg>
            <?php else: ?>
              <svg viewBox="0 0 100 100"><path d="M25 36h50v34H25z"/><path d="M36 70v9h28v-9"/><path d="M34 47h32M34 57h20"/></svg>
            <?php endif; ?>
          </div>
          <h3><?= e($it['t']) ?></h3>
          <em><?= e($it['e']) ?></em>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="sw-sec" id="products">
  <div class="sw-container">
    <div class="sw-pro-wrap">
      <div class="sw-sec-head" style="margin-bottom:26px">
        <div class="sw-head-row"><i></i><h2><?= e(lang('产品中心')) ?></h2><i></i></div>
        <em>PRODUCTS</em>
      </div>
      <div class="sw-pro-arrows">
        <button class="sw-pro-prev" type="button" aria-label="<?= e(lang('上一组')) ?>">‹</button>
        <button class="sw-pro-next" type="button" aria-label="<?= e(lang('下一组')) ?>">›</button>
      </div>
    </div>
    <?php if ($productCards): ?>
      <div class="sw-pro-track">
        <?php foreach ($productCards as $a): ?>
          <?php $cover = trim((string) ml($a, 'cover')) ?: '/uploads/workshop.jpg'; ?>
          <a class="sw-pro-card" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
            <span class="sw-pro-img"><img src="<?= e($cover) ?>" alt="<?= e(ml($a, 'title')) ?>" loading="lazy"></span>
            <h3><?= e(ml($a, 'title')) ?></h3>
            <p><?= e(mb_substr(trim(strip_tags((string) ml($a, 'summary')), '　 '), 0, 40)) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="sw-pro-dots" aria-hidden="true"></div>
    <?php else: ?>
      <div class="sw-empty"><?= e(lang('暂无产品')) ?></div>
    <?php endif; ?>
  </div>
</section>

<section class="sw-join" style="background-image:url('/uploads/factory.jpg')">
  <div class="sw-join-in">
    <span>LET'S BUILD IT TOGETHER</span>
    <h2><?= e(lang('把产线难题，交给一支能落地的团队')) ?></h2>
    <p><?= e(lang('一次现场勘测、一份可验证的方案、一支跟到验收的工程队。')) ?></p>
    <a class="sw-btn sw-btn-white" href="<?= e($contactUrl) ?>"><?= e(lang('获取方案与报价')) ?></a>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
