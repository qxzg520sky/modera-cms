<?php include __DIR__ . '/_head.php'; ?>
<section class="mb-hero" style="padding-bottom:10px">
  <div class="mb-kicker">ASSET LEDGER</div>
  <h1 class="mb-title">资产明细</h1>
  <p class="mb-sub">余额和积分每一次变动都有独立流水，可用于对账和售后核查。</p>
</section>

<div class="mb-tabs">
  <a class="<?= $kind === 'balance' ? 'on' : '' ?>" href="/member/logs?kind=balance">余额流水</a>
  <a class="<?= $kind === 'points' ? 'on' : '' ?>" href="/member/logs?kind=points">积分流水</a>
</div>

<section class="mb-section">
  <div class="mb-card">
    <?php if (!$res['list']): ?>
      <div class="mb-empty">暂无<?= $kind === 'balance' ? '余额' : '积分' ?>变动记录</div>
    <?php else: ?>
    <div class="mb-table-wrap">
      <table class="mb-table">
        <thead>
          <tr>
            <th>时间</th>
            <th>类型</th>
            <th class="right">变动</th>
            <th class="right">变动前</th>
            <th class="right">变动后</th>
            <th>业务单号</th>
            <th>备注</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($res['list'] as $l): ?>
          <?php
            $n = $kind === 'balance' ? (float) $l['change_amount'] : (int) $l['change_points'];
            $before = $kind === 'balance' ? (float) $l['before_balance'] : (int) $l['before_points'];
            $after = $kind === 'balance' ? (float) $l['after_balance'] : (int) $l['after_points'];
          ?>
          <tr>
            <td><?= date('Y-m-d H:i:s', (int) $l['created_at']) ?></td>
            <td><span class="mb-badge gray"><?= e($l['type_text']) ?></span></td>
            <td class="right" style="font-weight:800;color:<?= $n >= 0 ? '#0f7d4e' : '#b42318' ?>"><?= $n >= 0 ? '+' : '' ?><?= $kind === 'balance' ? number_format($n, 2) : $n ?></td>
            <td class="right"><?= $kind === 'balance' ? number_format($before, 2) : $before ?></td>
            <td class="right"><?= $kind === 'balance' ? number_format($after, 2) : $after ?></td>
            <td style="font-size:12px;color:#64748b"><?= e($l['biz_no'] ?: '-') ?></td>
            <td style="font-size:12px;color:#64748b"><?= e($l['remark'] ?: '-') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
    <div class="mb-pager">
      <?php $base = '/member/logs?kind=' . $kind . '&'; echo \plugins\member\service\Member::pager($res['page'], $res['pages'], $base); ?>
    </div>
  </div>
</section>
<?php include __DIR__ . '/_foot.php'; ?>
