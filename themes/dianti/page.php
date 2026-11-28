<?php include __DIR__ . '/_top.php'; ?>
<?php
$slug = (string) ($row['slug'] ?? '');
$title = (string) ml($row, 'title');
$content = (string) ml($row, 'content');
$desc = mb_substr(trim(strip_tags($content)), 0, 120, 'UTF-8');
if ($slug === 'contact') { $desc = '告诉我们现场情况，获取设备选型与产线配置建议。'; }
if ($slug === 'about') { $desc = '从单台设备到整线交付，我们把工艺理解写进每一次设计。'; }
?>
<section class="page-hero" data-en="<?= e(strtoupper($slug ?: 'PAGE')) ?>">
  <div class="wrap">
    <div class="breadcrumb"><a href="<?= e(lang_path('/')) ?>">首页</a> / <?= e($title) ?></div>
    <div class="hero-line"></div>
    <h1><?= e($title) ?></h1>
    <p><?= e($desc !== '' ? $desc : '了解更多关于' . $siteName . '的信息。') ?></p>
  </div>
</section>

<?php if ($slug === 'contact'): ?>
<section class="sec-pad-sm">
  <div class="wrap contact-grid">
    <div class="contact-card">
      <h2>直接找到我们</h2>
      <p>工作日 1 小时内响应，支持现场勘测与方案沟通。</p>
      <?php if ($phone): ?><div class="contact-item"><span>服务热线</span><b><?= e($phone) ?></b></div><?php endif; ?>
      <?php if ($email): ?><div class="contact-item"><span>邮箱</span><b><?= e($email) ?></b></div><?php endif; ?>
      <?php if ($address): ?><div class="contact-item"><span>地址</span><b><?= e($address) ?></b></div><?php endif; ?>
      <div class="contact-item"><span>时间</span><b>周一至周日 08:30–18:00</b></div>
    </div>
    <div class="contact-form">
      <h3>留下你的需求</h3>
      <p>填写后，我们会尽快与你联系。带 * 为必填项。</p>
      <form action="#" method="post">
        <div class="field-row">
          <div class="field"><label>称呼 *</label><input type="text" name="name" placeholder="怎么称呼你" required></div>
          <div class="field"><label>单位</label><input type="text" name="company" placeholder="单位 / 部门"></div>
        </div>
        <div class="field-row">
          <div class="field"><label>电话 *</label><input type="tel" name="phone" placeholder="手机或座机" required></div>
          <div class="field"><label>需求方向</label><input type="text" name="topic" placeholder="如：整线改造 / 单机设备"></div>
        </div>
        <div class="field"><label>具体情况</label><textarea name="message" placeholder="工艺、节拍、产能目标，或你希望解决的问题"></textarea></div>
        <button class="btn btn-dark" type="submit" style="width:100%">提交需求 <span class="arrow">↗</span></button>
        <div class="form-note">演示站点表单不会真实提交。正式使用时请接入企业邮箱或 CRM。</div>
      </form>
    </div>
  </div>
</section>
<?php elseif ($slug === 'about'): ?>
<section class="sec-pad-sm">
  <div class="wrap about-intro">
    <img src="/theme/about.jpg" alt="<?= e($siteName) ?>">
    <div class="copy">
      <span class="eyebrow">Since 2009</span>
      <h2>以匠心制造<br>为工业赋能</h2>
      <p class="about-lead">从工艺理解到整线交付，我们把设备、节拍、数据与现场流程放在一起考虑，让自动化真正落到每一道工序。</p>
      <div class="values">
        <div class="value-card"><b>01</b><h3>现场优先</h3><p>所有设计决策，回到产线真实的使用场景。</p></div>
        <div class="value-card"><b>02</b><h3>稳定可靠</h3><p>在高强度、多品种生产中，仍然稳定运行。</p></div>
        <div class="value-card"><b>03</b><h3>长期陪伴</h3><p>从交付到巡检，用持续服务替代一次性买卖。</p></div>
      </div>
    </div>
  </div>
  <div class="wrap"><div class="about-detail prose"><?= preg_replace('/<img[^>]*>/i', '', $content, 1) ?></div></div>
  <div class="wrap">
    <div class="timeline">
      <div class="tl-item"><time>2009</time><div><h3>公司成立</h3><p>聚焦工业自动化装备与非标产线集成。</p></div></div>
      <div class="tl-item"><time>2014</time><div><h3>整线交付</h3><p>完成首条机器人上下料产线整线交付。</p></div></div>
      <div class="tl-item"><time>2019</time><div><h3>全国服务网络</h3><p>服务覆盖 31 个省市，建立区域备件与巡检机制。</p></div></div>
      <div class="tl-item"><time>2026</time><div><h3>柔性制造升级</h3><p>视觉引导、数字孪生与整线数据能力全面升级。</p></div></div>
    </div>
  </div>
</section>
<section class="sec-dark sec-pad-sm">
  <div class="wrap" style="text-align:center;max-width:820px">
    <span class="eyebrow light">Our Belief</span>
    <div style="font-size:clamp(24px,3.2vw,38px);line-height:1.6;letter-spacing:-.03em;margin:22px 0 18px">“好的装备，应该让一线人员<br>更专注于任务本身。”</div>
    <p style="color:rgba(255,255,255,.5);font-size:13px;line-height:2">这就是我们做产品的起点，也是持续迭代的理由。</p>
  </div>
</section>
<?php else: ?>
<section class="sec-pad-sm">
  <div class="wrap-sm prose" style="background:var(--card);border:1px solid var(--line);padding:42px">
    <?= $content ?>
  </div>
</section>
<?php endif; ?>
<?php include __DIR__ . '/_bottom.php'; ?>





