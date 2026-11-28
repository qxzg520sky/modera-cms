<?php include __DIR__ . '/_head.php'; ?>
<?php $name = $member['real_name'] !== '' ? $member['real_name'] : $member['username']; ?>
<section class="mb-hero" style="padding-bottom:0">
  <div class="mb-card mb-hero-card">
    <div class="who"><?= e($siteName) ?> · MEMBER CENTER</div>
    <div class="name"><?= e($name) ?>，欢迎回来</div>
    <div class="tag"><?= e($member['level_name']) ?> · 会员折扣 <?= e(\plugins\member\service\Member::discountText((float) $member['discount'])) ?></div>
    <div class="mini">
      <div><b>￥<?= number_format($member['balance'], 2) ?></b><small>账户余额</small></div>
      <div><b><?= (int) $member['points'] ?></b><small>可用积分</small></div>
      <div><b>￥<?= number_format($member['total_spent'], 2) ?></b><small>累计消费</small></div>
      <div><b><?= (int) $member['order_count'] ?></b><small>累计订单</small></div>
    </div>
  </div>
</section>

<section class="mb-section">
  <div class="mb-section-head">
    <h2>快捷入口</h2>
    <p>会员编号 <?= e($member['member_no']) ?>　注册于 <?= $member['created_at'] ? date('Y-m-d', $member['created_at']) : '-' ?></p>
  </div>
  <div class="mb-quick">
    <a href="/member/orders"><span class="i">📦</span>我的订单</a>
    <a href="/member/address"><span class="i">📍</span>收货地址</a>
    <a href="/member/logs"><span class="i">🧾</span>资产明细</a>
    <a href="/member/profile"><span class="i">👤</span>会员资料</a>
  </div>
</section>

<section class="mb-section mb-split">
  <div>
    <div class="mb-card mb-card-pad">
      <div class="mb-section-head" style="margin-bottom:8px">
        <h2>最近余额变动</h2>
        <a class="mb-link" href="/member/logs?kind=balance">全部 →</a>
      </div>
      <?php if (!$balLog): ?>
        <div class="mb-empty">暂无余额变动</div>
      <?php else: ?>
        <?php foreach ($balLog as $l): $n = (float) $l['change_amount']; ?>
        <div class="mb-log">
          <div><div class="t"><?= e($l['type_text']) ?></div><div class="s"><?= date('Y-m-d H:i', (int) $l['created_at']) ?> · <?= e($l['remark'] ?: $l['operator_text']) ?></div></div>
          <div class="v <?= $n >= 0 ? 'pos' : 'neg' ?>"><?= $n >= 0 ? '+' : '' ?><?= number_format($n, 2) ?></div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
  <div>
    <div class="mb-card mb-card-pad">
      <div class="mb-section-head" style="margin-bottom:8px">
        <h2>最近订单</h2>
        <a class="mb-link" href="/member/orders">全部 →</a>
      </div>
      <?php if (!empty($orders['list'])): ?>
        <?php foreach (array_slice($orders['list'], 0, 3) as $o): ?>
          <div class="mb-list-item">
            <div><div class="n"><?= e($o['title'] ?? $o['order_no'] ?? '订单') ?></div><div class="m"><?= e($o['order_no'] ?? '') ?> · <?= e($o['created_at'] ?? '') ?></div></div>
            <div style="font-weight:800">￥<?= number_format((float) ($o['amount'] ?? 0), 2) ?></div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="mb-empty" style="padding:24px 10px">暂无订单，商城接入后会显示在这里</div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="mb-section">
  <div class="mb-section-head">
    <h2>最近积分变动</h2>
    <a class="mb-link" href="/member/logs?kind=points">全部 →</a>
  </div>
  <div class="mb-card mb-card-pad">
    <?php if (!$ptsLog): ?>
      <div class="mb-empty">暂无积分变动</div>
    <?php else: ?>
      <?php foreach ($ptsLog as $l): $n = (int) $l['change_points']; ?>
      <div class="mb-log">
        <div><div class="t"><?= e($l['type_text']) ?></div><div class="s"><?= date('Y-m-d H:i', (int) $l['created_at']) ?> · <?= e($l['remark'] ?: $l['operator_text']) ?></div></div>
        <div class="v <?= $n >= 0 ? 'pos' : 'neg' ?>"><?= $n >= 0 ? '+' : '' ?><?= $n ?> 分</div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/_foot.php'; ?>
