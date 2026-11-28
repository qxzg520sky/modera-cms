<?php include __DIR__ . '/_top.php'; ?>
<?php

$rows = static function (array $catIds, int $limit) : array {
    if (!$catIds) {
        return [];
    }
    return \think\facade\Db::name('article')->whereIn('category_id', $catIds)->where('status', 1)
        ->order('is_top desc, id desc')->limit($limit)->select()->toArray();
};

// 轮播：每个业务栏目各取 1 条有封面的最新内容（产品在前，招聘/资讯不上首屏）
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
        $slides[] = $one;
    }
    if (count($slides) >= 3) {
        break;
    }
}
if (!$slides) {
    $slides = \think\facade\Db::name('article')->where('status', 1)->where('cover', '<>', '')
        ->order('is_top desc, id desc')->limit(3)->select()->toArray();
}

// 统一成标准轮播结构：轮播广告插件启用时由插件接管，停用则用上面这套文章封面
$heroSlides = [];
foreach ($slides as $a) {
    $heroSlides[] = [
        'eyebrow'    => '',
        'title'      => (string) ml($a, 'title'),
        'summary'    => mb_substr(strip_tags((string) ml($a, 'summary')), 0, 90),
        'image'      => (string) $a['cover'],
        'link'       => lang_path('/a/' . (int) $a['id']),
        'link_text'  => lang('查看详情'),
        'link2'      => '',
        'link2_text' => '',
        'target'     => '_self',
    ];
}
if (function_exists('plugin_filter')) {
    $heroSlides = plugin_filter('home_slides', $heroSlides);
}

// 栏目 tab：顶级栏目（去掉新闻/招聘这类资讯栏目），每个 tab 取该栏目 + 子栏目下 4 条
$tabs = [];
foreach ($nav as $c) {
    if (in_array($c['slug'], ['news', 'jobs'], true)) {
        continue;
    }
    $ids = [(int) $c['id']];
    foreach ($c['children'] as $ch) {
        $ids[] = (int) $ch['id'];
    }
    $items = $rows($ids, 4);
    if (!$items) {
        continue;
    }
    $tabs[] = ['cat' => $c, 'items' => $items];
    if (count($tabs) >= 5) {
        break;
    }
}

// 工程案例：解决方案栏目
$caseCat = null;
foreach ($nav as $c) {
    if ($c['slug'] === 'solution') {
        $caseCat = $c;
        break;
    }
}
$cases = $caseCat ? $rows([(int) $caseCat['id']], 4) : $rows([], 0);

// 新闻
$newsCat = null;
foreach ($nav as $c) {
    if ($c['slug'] === 'news') {
        $newsCat = $c;
        break;
    }
}
$news = $newsCat ? $rows([(int) $newsCat['id']], 5) : $rows([], 0);

// 关于我们
$about = \think\facade\Db::name('page')->where('slug', 'about')->where('status', 1)->find();
?>

<?php if ($heroSlides): ?>
<section class="gt-slider" id="gtSlider">
  <?php foreach ($heroSlides as $i => $s): ?>
    <div class="gt-slide<?= $i === 0 ? ' on' : '' ?>" style="background-image:url('<?= e($s['image']) ?>')">
      <div class="gt-wrap gt-slide-in">
        <div class="gt-slide-txt">
          <?php if (!empty($s['eyebrow'])): ?><span class="gt-eyebrow"><?= e($s['eyebrow']) ?></span><?php endif; ?>
          <h2><?= e($s['title']) ?></h2>
          <?php if (!empty($s['summary'])): ?><p><?= e($s['summary']) ?></p><?php endif; ?>
          <div class="gt-slide-btns">
            <?php if (!empty($s['link'])): ?>
              <a class="gt-btn" href="<?= e($s['link']) ?>" target="<?= e($s['target'] ?: '_self') ?>"><?= e($s['link_text'] ?: lang('查看详情')) ?></a>
            <?php endif; ?>
            <?php if (!empty($s['link2'])): ?>
              <a class="gt-btn gt-btn-line" href="<?= e($s['link2']) ?>"><?= e($s['link2_text'] ?: lang('更多栏目')) ?></a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (count($heroSlides) > 1): ?>
  <div class="gt-slide-dots">
    <?php foreach ($heroSlides as $i => $s): ?>
      <button type="button" class="<?= $i === 0 ? 'on' : '' ?>" data-i="<?= $i ?>" aria-label="<?= $i + 1 ?>"></button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php else: ?>
<section class="gt-slider gt-slider-empty">
  <div class="gt-wrap gt-slide-in">
    <div class="gt-slide-txt">
      <h2><?= e($slogan ?: $siteName) ?></h2>
      <p><?= e(setting('site_description', lang('专业、可靠、值得信赖的企业服务平台'))) ?></p>
      <a class="gt-btn" href="<?= e(lang_path('/p/about')) ?>"><?= e(lang('了解更多')) ?></a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($tabs): ?>
<section class="gt-wrap gt-sec gt-products">
  <div class="gt-sec-head">
    <div>
      <h2><?= e(lang('产品与服务')) ?></h2>
      <p>PRODUCTS &amp; SERVICE</p>
    </div>
    <a class="gt-more" href="<?= e(lang_path('/c/' . $tabs[0]['cat']['slug'])) ?>"><?= e(lang('更多产品')) ?> →</a>
  </div>
  <ul class="gt-tabs">
    <?php foreach ($tabs as $i => $t): ?>
      <li class="<?= $i === 0 ? 'on' : '' ?>"><a href="javascript:;" data-pane="pane-<?= $i ?>"><?= e($t['cat']['name']) ?></a></li>
    <?php endforeach; ?>
  </ul>
  <div class="gt-panes">
    <?php foreach ($tabs as $i => $t): ?>
      <div class="gt-pane<?= $i === 0 ? ' on' : '' ?>" id="pane-<?= $i ?>">
        <?php foreach ($t['items'] as $a): ?>
          <dl class="gt-pro">
            <dt>
              <a href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
                <?php if ($a['cover']): ?><img src="<?= e($a['cover']) ?>" alt="<?= e(ml($a, 'title')) ?>" loading="lazy">
                <?php else: ?><span class="gt-ph"><?= e(mb_substr(ml($a, 'title'), 0, 2)) ?></span><?php endif; ?>
                <span class="gt-pro-mask"><em><?= e(lang('查看详情')) ?></em></span>
              </a>
            </dt>
            <dd><a href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>"><?= e(ml($a, 'title')) ?></a></dd>
          </dl>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($about): ?>
<section class="gt-about">
  <div class="gt-wrap">
    <?php
      $leadName   = trim((string) setting('site_name', ''));
      $leadSlogan = trim((string) setting('site_slogan', ''));
      $leadPhone  = trim((string) setting('site_phone', ''));
    ?>
    <?php if ($leadSlogan !== '' || $leadName !== ''): ?>
      <div class="gt-about-lead">
        <?php if ($leadSlogan !== ''): ?><strong><?= e($leadSlogan) ?></strong><?php endif; ?>
        <span>
          <?php
            $leadBits = [];
            if ($leadName !== '')  { $leadBits[] = $leadName; }
            if ($leadPhone !== '') { $leadBits[] = '服务热线 ' . $leadPhone; }
            echo e(implode(' · ', $leadBits));
          ?>
        </span>
      </div>
    <?php endif; ?>
    <div class="gt-about-card">
      <div class="gt-about-head">
        <h2><?= e(ml($about, 'title')) ?></h2>
        <p>ABOUT US</p>
      </div>
      <div class="gt-about-txt">
        <?= e(mb_substr(strip_tags((string) ml($about, 'content')), 0, 420)) ?>
      </div>
      <a class="gt-btn gt-btn-line" href="<?= e(lang_path('/p/' . $about['slug'])) ?>"><?= e(lang('查看详情')) ?></a>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="gt-wrap gt-sec gt-adv">
  <div class="gt-sec-head">
    <div><h2><?= e(lang('我们的优势')) ?></h2><p>WHY CHOOSE US</p></div>
  </div>
  <div class="gt-adv-list">
    <div class="gt-adv-item">
      <div class="gt-adv-no">01</div>
      <h3><?= e(lang('设备与产线自研')) ?></h3>
      <p><?= e(lang('机械结构、电气控制、视觉检测一体设计，非标设备从中标到交付平均 45 天，节拍与精度指标写入合同。')) ?></p>
    </div>
    <div class="gt-adv-item">
      <div class="gt-adv-no">02</div>
      <h3><?= e(lang('全流程质量管控')) ?></h3>
      <p><?= e(lang('来料检验、装配自检、出厂联调三道关卡，关键尺寸全检留档。')) ?></p>
    </div>
    <div class="gt-adv-item">
      <div class="gt-adv-no">03</div>
      <h3><?= e(lang('本地化服务响应')) ?></h3>
      <p><?= e(lang('西北区域驻点工程师，备件常备库存，故障响应不超过 24 小时。')) ?></p>
    </div>
  </div>
</section>

<?php if ($cases): ?>
<section class="gt-cases">
  <div class="gt-wrap">
    <div class="gt-sec-head">
      <div><h2><?= e($caseCat['name'] ?? lang('工程案例')) ?></h2><p>CASE SHOW</p></div>
      <?php if ($caseCat): ?><a class="gt-more light" href="<?= e(lang_path('/c/' . $caseCat['slug'])) ?>"><?= e(lang('更多案例')) ?> →</a><?php endif; ?>
    </div>
    <div class="gt-case-list">
      <?php foreach ($cases as $a): ?>
        <a class="gt-case" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
          <?php if ($a['cover']): ?><img src="<?= e($a['cover']) ?>" alt="<?= e(ml($a, 'title')) ?>" loading="lazy">
          <?php else: ?><span class="gt-ph dark"><?= e(mb_substr(ml($a, 'title'), 0, 2)) ?></span><?php endif; ?>
          <div class="gt-case-txt">
            <h3><?= e(ml($a, 'title')) ?></h3>
          </div>
        </a>
      <?php endforeach; ?>
      <?php if (count($cases) < 4 && $caseCat): ?>
        <a class="gt-case gt-case-more" href="<?= e(lang_path('/c/' . $caseCat['slug'])) ?>">
          <span class="gt-case-more-in">
            <b><?= e(lang('更多案例')) ?></b>
            <i><?= e(lang('查看全部工程案例')) ?> →</i>
          </span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="gt-contact-strip">
  <div class="gt-wrap gt-contact-in">
    <div class="gt-ci">
      <span class="gt-ci-ico">
        <svg viewBox="0 0 24 24" width="30" height="30"><path d="M6.6 10.8c1.4 2.8 3.6 5 6.4 6.4l2.2-2.2c.3-.3.7-.4 1-.2 2.3.7 4.6 1 6.9 1 .6 0 1 .4 1 1V21c0 .6-.4 1-1 1C10.4 22 2 13.6 2 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 2.3.3 4.6 1 6.9 1 .3.2.7.1 1-.2z" fill="#0887e7"/></svg>
      </span>
      <div><span><?= e(lang('全国服务热线')) ?></span><strong><?= e(setting('site_phone', '')) ?></strong></div>
    </div>
    <div class="gt-ci">
      <span class="gt-ci-ico">
        <svg viewBox="0 0 24 24" width="30" height="30"><path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11z" stroke="#0887e7" stroke-width="2" fill="none"/><circle cx="12" cy="10" r="2.6" fill="#0887e7"/></svg>
      </span>
      <div><span>ADD</span><strong><?= e(setting('site_address', '')) ?></strong></div>
    </div>
    <div class="gt-ci">
      <span class="gt-ci-ico">
        <svg viewBox="0 0 24 24" width="30" height="30"><rect x="3" y="5" width="18" height="14" rx="2" stroke="#0887e7" stroke-width="2" fill="none"/><path d="M3.5 6.5L12 13l8.5-6.5" stroke="#0887e7" stroke-width="2" fill="none"/></svg>
      </span>
      <div><span>E-mail</span><strong><?= e(setting('site_email', '')) ?></strong></div>
    </div>
  </div>
</section>

<?php if ($news): ?>
<section class="gt-wrap gt-sec gt-news">
  <div class="gt-sec-head">
    <div><h2><?= e($newsCat['name'] ?? lang('新闻动态')) ?></h2><p>NEWS</p></div>
    <?php if ($newsCat): ?><a class="gt-more" href="<?= e(lang_path('/c/' . $newsCat['slug'])) ?>"><?= e(lang('更多资讯')) ?> →</a><?php endif; ?>
  </div>
  <div class="gt-news-grid">
    <div class="gt-news-cards">
      <?php foreach (array_slice($news, 0, 2) as $a): ?>
        <a class="gt-ncard" href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>">
          <?php if ($a['cover']): ?><img src="<?= e($a['cover']) ?>" alt="<?= e(ml($a, 'title')) ?>" loading="lazy">
          <?php else: ?><span class="gt-ph"><?= e(mb_substr(ml($a, 'title'), 0, 2)) ?></span><?php endif; ?>
          <div class="gt-ncard-txt">
            <h3><?= e(ml($a, 'title')) ?></h3>
            <span><?= e(date('Y-m-d', strtotime((string) $a['create_time']))) ?></span>
            <p><?= e(mb_substr(strip_tags((string) ml($a, 'summary')), 0, 70)) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="gt-news-list">
      <?php foreach ($news as $a): ?>
        <dl>
          <dt>
            <b><?= e(date('d', strtotime((string) $a['create_time']))) ?></b>
            <em><?= e(date('Y-m', strtotime((string) $a['create_time']))) ?></em>
          </dt>
          <dd>
            <h3><a href="<?= e(lang_path('/a/' . (int) $a['id'])) ?>"><?= e(ml($a, 'title')) ?></a></h3>
            <p><?= e(mb_substr(strip_tags((string) ml($a, 'summary')), 0, 60)) ?></p>
          </dd>
        </dl>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/_bottom.php'; ?>


