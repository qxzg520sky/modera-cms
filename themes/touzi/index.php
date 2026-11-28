<?php include __DIR__ . '/_top.php'; ?>
<?php
$rows = static function (array $catIds, int $limit): array {
    if (!$catIds) return [];
    return \think\facade\Db::name('article')
        ->whereIn('category_id', $catIds)
        ->where('status', 1)
        ->order('is_top desc, id desc')
        ->limit($limit)
        ->select()
        ->toArray();
};
$catIds = static function (?array $c): array {
    if (!$c) return [];
    $ids = [(int) $c['id']];
    foreach (($c['children'] ?? []) as $ch) { $ids[] = (int) $ch['id']; }
    return $ids;
};

$persItems = $rows($catIds($catBySlug['news'] ?? null), 3);
$fallbackNews = [
    ['id' => 0, 'title' => '某某实业通过 ISO9001:2015 质量体系年度审核', 'summary' => '审核组抽查了设计开发、来料检验、过程控制与售后服务四个模块，未开具不符合项。', 'cover' => '/uploads/team.jpg', 'create_time' => '2026-11-20'],
    ['id' => 0, 'title' => '公司二期厂房投产，年产能提升至 320 台套', 'summary' => '新增 8600㎡ 装配车间与 12 台加工设备，非标设备年产能由 180 台套提升到 320 台套。', 'cover' => '/uploads/factory.jpg', 'create_time' => '2026-11-02'],
    ['id' => 0, 'title' => '2026 西北工业自动化展参展回顾', 'summary' => '三天展期接待客户 160 余组，现场演示的视觉引导装配单元成为咨询热点。', 'cover' => '/uploads/showroom.jpg', 'create_time' => '2026-10-15'],
];
if (count($persItems) < 3) {
    $persItems = array_slice(array_merge($persItems, $fallbackNews), 0, 3);
}
$persImg = trim((string) ($persItems[0]['cover'] ?? '')) ?: '/uploads/factory.jpg';

$bizCards = [
    [
        't' => '智能装备制造', 'en' => 'INTELLIGENT EQUIPMENT',
        'd' => '六轴机器人工作站、视觉装配单元与非标专机，按工艺节拍做结构，按验收数据交付。',
        'u' => lang_path('/c/product'), 'icon' => 'robot',
    ],
    [
        't' => '自动化整线', 'en' => 'TURNKEY LINE',
        'd' => '从清洗、装配到检测，一条线打通 12 道工序，节拍 45 秒，预留二期扩产接口。',
        'u' => lang_path('/a/8'), 'icon' => 'line',
    ],
    [
        't' => '精密零部件加工', 'en' => 'PRECISION MACHINING',
        'd' => '五轴加工中心、车铣复合与大型龙门铣，批量一致性先控装夹与热变形。',
        'u' => lang_path('/c/machining'), 'icon' => 'gear',
    ],
    [
        't' => '产线改造升级', 'en' => 'LINE UPGRADE',
        'd' => '不推倒重来，用「局部自动化 + 数据补齐」的方式，让一条 2012 年的老线节拍提升 35%。',
        'u' => lang_path('/a/9'), 'icon' => 'upgrade',
    ],
    [
        't' => '设备维保服务', 'en' => 'MAINTENANCE',
        'd' => '四档维保套餐，省内 24 小时到场，常用备件 48 小时发货，含两次预防性巡检。',
        'u' => lang_path('/a/14'), 'icon' => 'service',
    ],
    [
        't' => '非标定制交付', 'en' => 'CUSTOM SOLUTION',
        'd' => '按产品工艺、场地与预算做非标设计，从工艺确认到验收拆成六个可交付节点。',
        'u' => lang_path('/a/3'), 'icon' => 'custom',
    ],
];

$presence = [
    ['img' => '/uploads/factory.jpg',  't' => '二期厂房投产', 'd' => '新增 8600㎡ 装配车间与 12 台加工设备，非标设备年产能由 180 台套提升到 320 台套，为整线项目留出更充裕的装配与调试周期。'],
    ['img' => '/uploads/team.jpg',     't' => '46 人技术团队', 'd' => '机械、电气、软件、工艺四条专业线协同，项目从图纸会签到现场调试由同一支团队跟到验收，问题不过夜。'],
    ['img' => '/uploads/workshop.jpg', 't' => '一项目一档案', 'd' => '工艺卡、评审记录、FAT 数据、调试日志全部归档。项目结束做复盘，问题清单进入知识库，返工率三年下降 40%。'],
    ['img' => '/uploads/showroom.jpg', 't' => '交付即培训', 'd' => '设备进厂同步交付操作、编程与保养三套课程，客户工程师能独立换型、排故，才算真正完成交付。'],
];

$institute = [
    [
        'img' => '/uploads/team.jpg',
        't' => '工艺技术学院',
        'd' => '把项目复盘变成课程：图纸会签要点、装夹与热变形控制、节拍测算与 FAT 验收。每一门课都来自做过的项目，不讲空理论。',
        'd2' => '内部讲师由项目经理与资深工艺工程师担任，新人先跟两个完整项目，再独立承担模块，师带徒不低于六个月。',
    ],
    [
        'img' => '/uploads/qc.jpg',
        't' => '质量管理学院',
        'd' => '从来料检验到过程控制，把「差不多」换成可追溯的数据。首件确认、巡检频次、不合格品处置，全部按流程走。',
        'd2' => '质量部每季度组织一次跨部门审核复盘，把客户投诉与内部返工做成案例库，问题闭环率纳入项目考核。',
    ],
    [
        'img' => '/uploads/showroom.jpg',
        't' => '客户培训中心',
        'd' => '设备交付不是终点。操作、编程、保养三套课程按客户产线定制，培训结束进行操作考核，考核通过才移交。',
        'd2' => '常用备件清单、排故手册与远程诊断通道一并交付，客户工程师遇到问题先查手册，再连现场，缩短停机时间。',
    ],
];

$aboutParas = [];
if ($aboutPage) {
    if (preg_match_all('/<p[^>]*>(.*?)<\/p>/isu', (string) ml($aboutPage, 'content'), $am)) {
        foreach ($am[1] as $ap) {
            $txt = trim(preg_replace('/\s+/u', ' ', strip_tags($ap)));
            if ($txt !== '' && mb_strlen($txt) > 20) { $aboutParas[] = $txt; }
        }
    }
}
$aboutParas = array_slice($aboutParas, 0, 4);
if (!$aboutParas) {
    $aboutParas = ['某某实业有限公司成立于 2009 年，位于某某市某某区工业园区，是一家以智能装备制造、工业自动化整线交付与精密零部件加工为主营业务的民营制造企业。'];
}
?>

<section class="tz-hero" aria-label="<?= e(lang('企业宣传')) ?>">
  <div class="tz-hero-bg" aria-hidden="true"></div>
  <div class="tz-hero-slides">
    <div class="tz-hero-slide on">
      <div class="tz-container tz-hero-in">
        <div class="tz-hero-copy">
          <span class="tz-hero-tag">INTELLIGENT MANUFACTURING</span>
          <h1>让每一条产线<br><em>稳定跑起来</em></h1>
          <p>从方案设计、机械加工、电气成套到现场调试，为制造企业交付稳定可靠的智能装备与自动化整线。</p>
          <div class="tz-hero-btns">
            <a class="tz-btn tz-btn-light" href="<?= e(lang_path('/c/solution')) ?>"><?= e(lang('查看方案')) ?></a>
            <a class="tz-hero-link" href="<?= e(lang_path('/p/about')) ?>"><?= e(lang('走进企业')) ?> →</a>
          </div>
        </div>
        <div class="tz-hero-media">
          <div class="tz-screen">
            <div class="tz-screen-bar"><span></span><span></span><span></span><b>LINE MONITOR / 产线监控</b></div>
            <img src="/uploads/line.jpg" alt="<?= e($siteName) ?> 自动化产线">
            <div class="tz-screen-data">
              <div><b>98.6%</b><span>设备在线率</span></div>
              <div><b>42s</b><span>单件节拍</span></div>
              <div><b>+18%</b><span>OEE 提升</span></div>
            </div>
          </div>
          <div class="tz-hero-badge"><b>DC-1200</b><span>六轴机器人工作站</span></div>
        </div>
      </div>
    </div>

    <div class="tz-hero-slide">
      <div class="tz-container tz-hero-in">
        <div class="tz-hero-copy">
          <span class="tz-hero-tag">TURNKEY AUTOMATION</span>
          <h1>整线交付<br><em>从方案到验收</em></h1>
          <p>围绕节拍、合格率与可维护性做设计，把承诺写进合同，用实测数据验收每一条产线。</p>
          <div class="tz-hero-btns">
            <a class="tz-btn tz-btn-light" href="<?= e(lang_path('/c/solution')) ?>"><?= e(lang('查看方案')) ?></a>
            <a class="tz-hero-link" href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('获取方案与报价')) ?> →</a>
          </div>
        </div>
        <div class="tz-hero-media">
          <div class="tz-screen">
            <div class="tz-screen-bar"><span></span><span></span><span></span><b>PLANT OVERVIEW / 厂区总览</b></div>
            <img src="/uploads/factory.jpg" alt="<?= e($siteName) ?> 厂区">
            <div class="tz-screen-data">
              <div><b>320</b><span>台套 / 年</span></div>
              <div><b>46</b><span>技术工程师</span></div>
              <div><b>40+</b><span>加工设备</span></div>
            </div>
          </div>
          <div class="tz-hero-badge"><b>3.6万㎡</b><span>厂区占地</span></div>
        </div>
      </div>
    </div>

    <div class="tz-hero-slide">
      <div class="tz-container tz-hero-in">
        <div class="tz-hero-copy">
          <span class="tz-hero-tag">ENGINEERING TEAM</span>
          <h1>把每一台设备<br><em>做扎实</em></h1>
          <p>46 人技术团队，五轴加工、车铣复合与大型龙门铣配套齐全，从图纸会签到终身维保全程负责。</p>
          <div class="tz-hero-btns">
            <a class="tz-btn tz-btn-light" href="<?= e(lang_path('/p/about')) ?>"><?= e(lang('了解我们')) ?></a>
            <a class="tz-hero-link" href="<?= e(lang_path('/c/jobs')) ?>"><?= e(lang('招贤纳士')) ?> →</a>
          </div>
        </div>
        <div class="tz-hero-media">
          <div class="tz-screen">
            <div class="tz-screen-bar"><span></span><span></span><span></span><b>QUALITY LAB / 检测中心</b></div>
            <img src="/uploads/qc.jpg" alt="<?= e($siteName) ?> 精密检测">
            <div class="tz-screen-data">
              <div><b>ISO9001</b><span>质量体系</span></div>
              <div><b>100%</b><span>首件确认</span></div>
              <div><b>24h</b><span>省内响应</span></div>
            </div>
          </div>
          <div class="tz-hero-badge"><b>+15年</b><span>制造经验</span></div>
        </div>
      </div>
    </div>
  </div>

  <div class="tz-hero-dots" aria-label="<?= e(lang('轮播切换')) ?>">
    <button class="on" type="button" aria-label="1"></button>
    <button type="button" aria-label="2"></button>
    <button type="button" aria-label="3"></button>
  </div>
</section>

<section class="tz-business" id="business">
  <div class="tz-container">
    <div class="tz-heading tz-heading-box">
      <span class="tz-en">BUSINESS INTRODUCTION</span>
      <h2>业务<b>介绍</b></h2>
      <p class="tz-heading-note">六大业务板块，从单机到整线，从制造到服务</p>
    </div>
  </div>

  <div class="tz-biz-body">
    <?php foreach (array_chunk($bizCards, 3) as $pi => $page): ?>
      <div class="tz-biz-page <?= $pi === 0 ? 'on' : '' ?>">
        <?php foreach ($page as $card): ?>
          <div class="tz-biz-card">
            <div class="tz-biz-icon">
              <?php if ($card['icon'] === 'robot'): ?>
                <svg viewBox="0 0 100 100" aria-hidden="true"><path d="M22 84h56M29 84v-26h42v26M50 58V36M50 36l22-11M72 25l5 8-20 11M35 84v-9h30v9M50 24a5 5 0 1 0 0-10 5 5 0 0 0 0 10M37 58v-8h9M54 58v-8h9"/></svg>
              <?php elseif ($card['icon'] === 'line'): ?>
                <svg viewBox="0 0 100 100" aria-hidden="true"><circle cx="24" cy="70" r="9"/><circle cx="50" cy="70" r="9"/><circle cx="76" cy="70" r="9"/><path d="M16 52h68v10H16zM22 52V36h24v16M58 52V28h20v24M24 70h44"/></svg>
              <?php elseif ($card['icon'] === 'gear'): ?>
                <svg viewBox="0 0 100 100" aria-hidden="true"><circle cx="50" cy="50" r="22"/><circle cx="50" cy="50" r="9"/><path d="M50 18v10M50 72v10M18 50h10M72 50h10M28 28l7 7M65 65l7 7M72 28l-7 7M35 65l-7 7"/></svg>
              <?php elseif ($card['icon'] === 'upgrade'): ?>
                <svg viewBox="0 0 100 100" aria-hidden="true"><path d="M20 78h60M28 78V48h32v30M60 48l15-9 8 5v34M36 78v-9h9v9M48 78v-9h9v9M22 48h38M52 38h20M52 58h24"/></svg>
              <?php elseif ($card['icon'] === 'service'): ?>
                <svg viewBox="0 0 100 100" aria-hidden="true"><path d="M50 16l28 11v22c0 17-12 28-28 35-16-7-28-18-28-35V27z"/><path d="M38 50l9 9 17-19"/></svg>
              <?php else: ?>
                <svg viewBox="0 0 100 100" aria-hidden="true"><path d="M30 26l20-10 20 10v38l-20 10-20-10zM30 26l20 10 20-10M50 36v38M36 32v38M64 32v38"/></svg>
              <?php endif; ?>
            </div>
            <span class="tz-biz-en"><?= e($card['en']) ?></span>
            <h3><?= e($card['t']) ?></h3>
            <p><?= e($card['d']) ?></p>
            <a class="tz-biz-more" href="<?= e($card['u']) ?>">MORE <span>→</span></a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <div class="tz-biz-dots" aria-label="业务切换">
      <button class="on" type="button" aria-label="1"></button>
      <button type="button" aria-label="2"></button>
    </div>
  </div>
</section>

<section class="tz-perspective" id="perspective">
  <div class="tz-container">
    <div class="tz-heading tz-heading-simple">
      <span class="tz-en">GROUP PERSPECTIVE</span>
      <h2>集团<b>视角</b></h2>
    </div>
    <div class="tz-pers-grid">
      <a class="tz-pers-pic" href="<?= e(lang_path('/c/news')) ?>">
        <img src="<?= e($persImg) ?>" alt="<?= e($siteName) ?> 集团动态" loading="lazy">
        <span class="tz-pers-pic-tag">集团动态 / NEWS</span>
      </a>
      <div class="tz-pers-list">
        <?php foreach ($persItems as $i => $a): ?>
          <?php
            $aTitle = trim((string) ml($a, 'title'));
            $aSum = trim(strip_tags((string) ml($a, 'summary')));
            if ($aSum === '') { $aSum = mb_substr(trim(strip_tags((string) ml($a, 'content'))), 0, 90) . '…'; }
            $aUrl = !empty($a['id']) ? lang_path('/a/' . (int) $a['id']) : lang_path('/c/news');
            $aDate = !empty($a['create_time']) ? date('Y-m-d', strtotime((string) $a['create_time'])) : date('Y-m-d');
          ?>
          <a class="tz-pers-item" href="<?= e($aUrl) ?>">
            <span class="tz-pers-no"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <div class="tz-pers-body">
              <h3><?= e($aTitle) ?></h3>
              <p><?= e($aSum) ?></p>
            </div>
            <time><?= e($aDate) ?></time>
          </a>
        <?php endforeach; ?>
        <div class="tz-pers-more"><a href="<?= e(lang_path('/c/news')) ?>"><?= e(lang('查看全部动态 →')) ?></a></div>
      </div>
    </div>
  </div>
</section>

<section class="tz-presence" id="presence">
  <div class="tz-container">
    <div class="tz-heading tz-heading-simple">
      <span class="tz-en">COMPANY PRESENCE</span>
      <h2>集团<b>风采</b></h2>
      <p class="tz-heading-note">专注于智能装备与自动化整线的制造企业</p>
    </div>
    <div class="tz-pres-stage">
      <div class="tz-pres-circles">
        <?php foreach ($presence as $i => $p): ?>
          <button class="tz-pres-circle <?= $i === 0 ? 'on' : '' ?>" type="button" aria-label="<?= e($p['t']) ?>">
            <img src="<?= e($p['img']) ?>" alt="<?= e($p['t']) ?>" loading="lazy">
          </button>
        <?php endforeach; ?>
      </div>
      <div class="tz-pres-descs">
        <?php foreach ($presence as $i => $p): ?>
          <div class="tz-pres-desc <?= $i === 0 ? 'on' : '' ?>">
            <h3><?= e($p['t']) ?></h3>
            <p><?= e($p['d']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="tz-institute" id="institute">
  <div class="tz-container">
    <div class="tz-heading tz-heading-simple">
      <span class="tz-en">GROUP INSTITUTE</span>
      <h2>集团<b>学院</b></h2>
    </div>
    <div class="tz-inst-panel">
      <div class="tz-inst-track">
        <?php foreach ($institute as $i => $it): ?>
          <div class="tz-inst-item <?= $i === 0 ? 'on' : '' ?>">
            <div class="tz-inst-pic"><img src="<?= e($it['img']) ?>" alt="<?= e($it['t']) ?>" loading="lazy"></div>
            <div class="tz-inst-copy">
              <span class="tz-inst-en">TRAINING PROGRAM <?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
              <h3><?= e($it['t']) ?></h3>
              <p><?= e($it['d']) ?></p>
              <p><?= e($it['d2']) ?></p>
              <a class="tz-text-more" href="<?= e(lang_path('/p/culture')) ?>"><?= e(lang('了解更多')) ?> →</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="tz-inst-nav">
        <button class="tz-inst-prev" type="button" aria-label="<?= e(lang('上一组')) ?>">‹</button>
        <button class="tz-inst-next" type="button" aria-label="<?= e(lang('下一组')) ?>">›</button>
      </div>
    </div>
  </div>
</section>

<section class="tz-about" id="about">
  <div class="tz-container">
    <div class="tz-about-frame">
      <div class="tz-about-pic">
        <img src="/uploads/factory.jpg" alt="<?= e($siteName) ?> 厂区" loading="lazy">
      </div>
      <div class="tz-about-copy">
        <span class="tz-en">ABOUT GROUP</span>
        <h2>关于集团</h2>
        <?php foreach ($aboutParas as $ap): ?>
          <p><?= e($ap) ?></p>
        <?php endforeach; ?>
        <a class="tz-btn tz-btn-black" href="<?= e($aboutUrl) ?>">MORE <span>→</span></a>
      </div>
      <div class="tz-about-vertical">关于集团<span>ABOUT GROUP</span></div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/_bottom.php'; ?>
