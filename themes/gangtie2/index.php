<?php include __DIR__ . '/_top.php'; ?>
<?php

$rows = static function (array $catIds, int $limit) : array {
    if (!$catIds) {
        return [];
    }
    return \think\facade\Db::name('article')->whereIn('category_id', $catIds)->where('status', 1)
        ->order('is_top desc, id desc')->limit($limit)->select()->toArray();
};

$catBySlug = [];
foreach ($nav as $c) {
    $catBySlug[$c['slug']] = $c;
}

// 轮播
$slides = [];
foreach ($nav as $c) {
    if (in_array($c['slug'], ['news', 'jobs'], true)) {
        continue;
    }
    $ids = [(int) $c['id']];
    foreach ($c['children'] as $ch) {
        $ids[] = (int) $ch['id'];
    }
    $one = \think\facade\Db::name('article')->whereIn('category_id', $ids)->where('status', 1)
        ->where('cover', '<>', '')->order('is_top desc, id desc')->find();
    if ($one) {
        $slides[] = ['a' => $one, 'cat' => $c];
    }
    if (count($slides) >= 3) {
        break;
    }
}
if (!$slides) {
    foreach (\think\facade\Db::name('article')->where('status', 1)->where('cover', '<>', '')
        ->order('is_top desc, id desc')->limit(3)->select()->toArray() as $a) {
        $slides[] = ['a' => $a, 'cat' => null];
    }
}

// 统一成标准轮播结构：轮播广告插件启用时由插件接管，停用则用上面这套文章封面
$heroSlides = [];
foreach ($slides as $s) {
    $a = $s['a'];
    $heroSlides[] = [
        'eyebrow'    => (string) ($s['cat']['name'] ?? ''),
        'title'      => (string) ml($a, 'title'),
        'summary'    => mb_substr(strip_tags((string) ml($a, 'summary')), 0, 90),
        'image'      => (string) $a['cover'],
        'link'       => lang_path('/a/' . (int) $a['id']),
        'link_text'  => lang('查看详情'),
        'link2'      => $s['cat'] ? lang_path('/c/' . ($s['cat']['slug'] ?? 'product')) : '',
        'link2_text' => lang('更多栏目'),
        'target'     => '_self',
    ];
}
if (function_exists('plugin_filter')) {
    $heroSlides = plugin_filter('home_slides', $heroSlides);
}

// 关于我们
$about = \think\facade\Db::name('page')->where('slug', 'about')->where('status', 1)->find();
$aboutImg = '/uploads/showroom.jpg';
$aboutArt = \think\facade\Db::name('article')->where('status', 1)->where('cover', '<>', '')
    ->order('is_top desc, id desc')->find();
if ($aboutArt && is_file(public_path() . ltrim($aboutArt['cover'], '/'))) {
    $aboutImg = $aboutArt['cover'];
}

// 四宫格能力
$featureDefs = [
    ['slug' => 'equipment', 'eyebrow' => 'AUTOMATION', 'title' => lang('智能装备'), 'fallback' => '非标自动化设备的设计、制造与交付，覆盖装配、检测、上下料全流程。', 'icon' => 'robot'],
    ['slug' => 'machining', 'eyebrow' => 'MACHINING', 'title' => lang('精密加工'), 'fallback' => '五轴加工中心与车铣复合，关键零件尺寸与表面质量全检留档。', 'icon' => 'gear'],
    ['slug' => 'solution', 'eyebrow' => 'SOLUTION', 'title' => lang('工业自动化'), 'fallback' => '整线节拍测算、电气控制与视觉检测一体设计，按工况出方案。', 'icon' => 'factory'],
    ['slug' => 'service', 'eyebrow' => 'SERVICE', 'title' => lang('服务支持'), 'fallback' => '省内 24 小时到场，备件常备库存，年度维保按合同执行。', 'icon' => 'support'],
];
$features = [];
foreach ($featureDefs as $fd) {
    $c = $catBySlug[$fd['slug']] ?? null;
    $features[] = [
        'title' => $c ? $c['name'] : $fd['title'],
        'eyebrow' => $fd['eyebrow'],
        'text' => $fd['fallback'],
        'icon' => $fd['icon'],
        'url' => $c ? lang_path('/c/' . $c['slug']) : lang_path('/'),
    ];
}

// 技术方案要点：解决方案栏目文章
$solIds = [];
if (!empty($catBySlug['solution'])) {
    $solIds[] = (int) $catBySlug['solution']['id'];
    foreach ($catBySlug['solution']['children'] as $ch) {
        $solIds[] = (int) $ch['id'];
    }
}
$solArts = $solIds ? \think\facade\Db::name('article')->whereIn('category_id', $solIds)->where('status', 1)
    ->order('is_top desc, id desc')->limit(6)->select()->toArray() : [];
$techImg = '/uploads/workshop.jpg';
if ($solArts && is_file(public_path() . ltrim($solArts[0]['cover'], '/'))) {
    $techImg = $solArts[0]['cover'];
}

// 项目墙：装备 + 加工
$projIds = [];
foreach (['equipment', 'machining'] as $s) {
    if (!empty($catBySlug[$s])) {
        $projIds[] = (int) $catBySlug[$s]['id'];
        foreach ($catBySlug[$s]['children'] as $ch) {
            $projIds[] = (int) $ch['id'];
        }
    }
}
$projects = $rows($projIds, 4);

// 新闻
$newsIds = !empty($catBySlug['news']) ? [(int) $catBySlug['news']['id']] : [];
$news = $rows($newsIds, 3);
$newsCat = $catBySlug['news'] ?? null;

$phone = setting('site_phone', '');
$address = setting('site_address', '');
$email = setting('site_email', '');
$packImg = '/uploads/team.jpg';
?>

<?php if ($heroSlides): ?>
<section class="g2-hero" id="g2Hero">
  <?php foreach ($heroSlides as $i => $s): ?>
    <div class="g2-slide<?= $i === 0 ? ' on' : '' ?>" style="background-image:url('<?= e($s['image']) ?>')">
      <div class="g2-wrap g2-slide-in">
        <div class="g2-slide-txt">
          <?php if (!empty($s['eyebrow'])): ?><span class="g2-eyebrow"><?= e($s['eyebrow']) ?></span><?php endif; ?>
          <h1><?= e($s['title']) ?></h1>
          <?php if (!empty($s['summary'])): ?><p><?= e($s['summary']) ?></p><?php endif; ?>
          <div class="g2-slide-btns">
            <?php if (!empty($s['link'])): ?>
              <a class="g2-btn g2-btn-white" href="<?= e($s['link']) ?>" target="<?= e($s['target'] ?: '_self') ?>"><?= e($s['link_text'] ?: lang('查看详情')) ?></a>
            <?php endif; ?>
            <?php if (!empty($s['link2'])): ?>
              <a class="g2-btn g2-btn-org" href="<?= e($s['link2']) ?>"><?= e($s['link2_text'] ?: lang('更多栏目')) ?></a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (count($heroSlides) > 1): ?>
  <div class="g2-hero-dots">
    <?php foreach ($heroSlides as $i => $s): ?>
      <button type="button" class="<?= $i === 0 ? 'on' : '' ?>" data-i="<?= $i ?>" aria-label="<?= $i + 1 ?>"></button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- 欢迎 / 关于 -->
<section class="g2-welcome">
  <div class="g2-wrap g2-welcome-in">
    <div class="g2-welcome-txt">
      <span class="g2-eyebrow">WELCOME TO</span>
      <h2><?= e($siteName) ?></h2>
      <p><?= e($about ? mb_substr(strip_tags((string) ml($about, 'content')), 0, 300) : ($slogan ?: '')) ?></p>
      <p class="g2-sign"><?= e($slogan ?: $siteName) ?></p>
      <a class="g2-btn g2-btn-dark" href="<?= e(lang_path('/p/' . ($about['slug'] ?? 'about'))) ?>"><?= e(lang('了解更多')) ?></a>
    </div>
    <div class="g2-welcome-img">
      <img src="<?= e($aboutImg) ?>" alt="<?= e($siteName) ?>">
      <span class="g2-play"><i></i></span>
    </div>
  </div>
</section>

<!-- 四宫格能力 -->
<section class="g2-features">
  <div class="g2-wrap">
    <div class="g2-feature-grid">
      <?php foreach ($features as $f): ?>
        <a class="g2-feature" href="<?= e($f['url']) ?>">
          <span class="g2-f-ico">
            <?php if ($f['icon'] === 'robot'): ?>
              <svg viewBox="0 0 48 48" width="42" height="42"><rect x="9" y="15" width="30" height="22" rx="4" fill="none" stroke="#ff5e14" stroke-width="1.6"/><circle cx="18" cy="26" r="3" fill="none" stroke="#ff5e14" stroke-width="1.6"/><circle cx="30" cy="26" r="3" fill="none" stroke="#ff5e14" stroke-width="1.6"/><path d="M24 15V7m-4 0h8M9 24H4m4 6H4m40-6h-5m5 6h-5" stroke="#ff5e14" stroke-width="1.6" fill="none"/></svg>
            <?php elseif ($f['icon'] === 'gear'): ?>
              <svg viewBox="0 0 48 48" width="42" height="42"><circle cx="24" cy="24" r="8" fill="none" stroke="#ff5e14" stroke-width="1.6"/><path d="M24 8v6m0 20v6M8 24h6m20 0h6M13 13l4 4m14 14l4 4M35 13l-4 4M17 31l-4 4" stroke="#ff5e14" stroke-width="1.6" fill="none"/></svg>
            <?php elseif ($f['icon'] === 'factory'): ?>
              <svg viewBox="0 0 48 48" width="42" height="42"><path d="M6 38V20l10 6V20l10 6V20l10 6v12z" fill="none" stroke="#ff5e14" stroke-width="1.6"/><path d="M6 38h36M36 26V12h6v14" fill="none" stroke="#ff5e14" stroke-width="1.6"/></svg>
            <?php else: ?>
              <svg viewBox="0 0 48 48" width="42" height="42"><circle cx="24" cy="24" r="16" fill="none" stroke="#ff5e14" stroke-width="1.6"/><path d="M18 20a6 6 0 1 1 12 0c0 5-6 5-6 9" fill="none" stroke="#ff5e14" stroke-width="1.6"/><circle cx="24" cy="34" r="1.8" fill="#ff5e14"/></svg>
            <?php endif; ?>
          </span>
          <em><?= e($f['eyebrow']) ?></em>
          <h3><?= e($f['title']) ?></h3>
          <p><?= e($f['text']) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 深色技术方案 -->
<section class="g2-tech">
  <div class="g2-tech-l">
    <div class="g2-tech-in">
      <span class="g2-eyebrow"><?= e(lang('我们的')) ?></span>
      <h2><?= e(lang('技术解决方案')) ?></h2>
      <p><?= e($slogan ?: lang('从工艺确认到交付验收，全流程由同一支工程师队伍负责。')) ?></p>
      <ul class="g2-tech-list">
        <?php foreach (($solArts ?: []) as $a): ?>
          <li><a href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>"><?= e(ml($a, 'title')) ?></a></li>
        <?php endforeach; ?>
        <?php if (!$solArts): ?>
          <li><span><?= e(lang('安全与工艺评估')) ?></span></li>
          <li><span><?= e(lang('电气控制设计')) ?></span></li>
          <li><span><?= e(lang('视觉检测集成')) ?></span></li>
          <li><span><?= e(lang('交付与验收')) ?></span></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
  <div class="g2-tech-r" style="background-image:url('<?= e($techImg) ?>')"></div>
</section>

<!-- 项目墙 -->
<?php if ($projects): ?>
<section class="g2-projects">
  <div class="g2-wrap">
    <div class="g2-head">
      <div>
        <span class="g2-eyebrow"><?= e(lang('我们的作品')) ?></span>
        <h2><?= e(lang('最新项目')) ?></h2>
        <p><?= e(lang('每一个项目都从工况出发，节拍、精度与可维护性写在验收单里。')) ?></p>
      </div>
      <a class="g2-btn g2-btn-org g2-btn-sm" href="<?= e(lang_path('/c/' . ($projIds ? (string) ($catBySlug['equipment']['slug'] ?? 'product') : 'product'))) ?>"><?= e(lang('查看更多')) ?></a>
    </div>
    <div class="g2-project-grid">
      <?php foreach ($projects as $p): ?>
        <a class="g2-project" href="<?= e(lang_path('/a/' . (int) $p['id'])) ?>">
          <?php if ($p['cover']): ?>
            <img src="<?= e($p['cover']) ?>" alt="<?= e(ml($p, 'title')) ?>">
          <?php else: ?>
            <span class="g2-ph"><?= e(mb_substr(ml($p, 'title'), 0, 6)) ?></span>
          <?php endif; ?>
          <span class="g2-project-mask"><b><?= e(ml($p, 'title')) ?></b><i><?= e(lang('查看项目')) ?> →</i></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- 询价表单 -->
<section class="g2-quote">
  <div class="g2-wrap g2-quote-in">
    <div class="g2-quote-img" style="background-image:url('/uploads/qc.jpg')"></div>
    <div class="g2-quote-card">
      <span class="g2-eyebrow"><?= e(lang('在线询价')) ?></span>
      <h2><?= e(lang('获取报价')) ?></h2>
      <p><?= e(lang('留下需求与联系方式，工程师会在一个工作日内给出方案与报价。')) ?></p>
      <form class="g2-form" id="g2Quote">
        <input type="text" name="name" placeholder="<?= e(lang('您的姓名')) ?>" autocomplete="name">
        <input type="tel" name="phone" placeholder="<?= e(lang('联系电话')) ?>" autocomplete="tel">
        <input type="text" name="need" placeholder="<?= e(lang('需求描述（如：装配线节拍 30s/件）')) ?>">
        <button type="submit" class="g2-btn g2-btn-org"><?= e(lang('提交询价')) ?></button>
        <span class="g2-form-hint"><?= e(lang('提交后会直接进入在线客服对话')) ?></span>
      </form>
    </div>
  </div>
</section>

<!-- 深色服务带 -->
<section class="g2-pack" style="background-image:url('<?= e($packImg) ?>')">
  <div class="g2-wrap g2-pack-in">
    <div class="g2-pack-txt">
      <span class="g2-eyebrow"><?= e(lang('方案所在')) ?></span>
      <h2><?= e(lang('我们提供整套交付包')) ?></h2>
      <p><?= e(lang('从选型、设计到安装调试与培训，一个团队跟到底。')) ?></p>
      <ul>
        <li><?= e(lang('非标设备设计制造与整线集成')) ?></li>
        <li><?= e(lang('关键零件精密加工与表面处理')) ?></li>
        <li><?= e(lang('电气控制、视觉检测与数据采集')) ?></li>
        <li><?= e(lang('安装调试、操作培训与年度维保')) ?></li>
        <li><?= e(lang('省内 24 小时到场，备件常备库存')) ?></li>
      </ul>
    </div>
  </div>
</section>

<!-- 新闻 -->
<?php if ($news): ?>
<section class="g2-news">
  <div class="g2-wrap">
    <div class="g2-head">
      <div>
        <span class="g2-eyebrow"><?= e(lang('我们的动态')) ?></span>
        <h2><?= e(lang('新闻中心')) ?></h2>
        <p><?= e(lang('展会、交付与体系认证的最新进展。')) ?></p>
      </div>
      <?php if ($newsCat): ?>
        <a class="g2-btn g2-btn-line g2-btn-sm" href="<?= e(lang_path('/c/' . $newsCat['slug'])) ?>"><?= e(lang('全部资讯')) ?></a>
      <?php endif; ?>
    </div>
    <div class="g2-news-grid">
      <?php foreach ($news as $a): ?>
        <a class="g2-news-card" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
          <span class="g2-news-img">
            <?php if ($a['cover']): ?><img src="<?= e($a['cover']) ?>" alt="<?= e(ml($a, 'title')) ?>"><?php else: ?><span class="g2-ph"><?= e(lang('新闻')) ?></span><?php endif; ?>
          </span>
          <div class="g2-news-body">
            <h3><?= e(ml($a, 'title')) ?></h3>
            <span class="g2-news-meta">
              <i><?= e(date('Y-m-d', strtotime((string) $a['create_time'])))?></i>
              <i><?= e(lang('阅读')) ?> <?= (int) $a['views'] ?></i>
            </span>
            <p><?= e(mb_substr(strip_tags((string) ml($a, 'summary')), 0, 80)) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/_bottom.php'; ?>
