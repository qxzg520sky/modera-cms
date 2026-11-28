<?php include __DIR__ . '/_staff_head.php'; ?>
<section class="mb-hero" style="padding-bottom:14px">
  <div class="mb-kicker">MEMBER DETAIL · <?= e($member['member_no']) ?></div>
  <h1 class="mb-title"><?= e($member['real_name'] ?: $member['username']) ?></h1>
  <p class="mb-sub"><?= e($member['mobile']) ?>　|　<?= e($member['level_name']) ?>　|　注册于 <?= $member['created_at'] ? date('Y-m-d', (int) $member['created_at']) : '-' ?></p>
</section>

<div class="mb-grid mb-grid-4">
  <div class="mb-card mb-stat"><div class="l">余额</div><div class="n" style="font-size:24px">￥<?= number_format($member['balance'], 2) ?></div><div class="h">资产变动由管理员操作</div></div>
  <div class="mb-card mb-stat"><div class="l">积分</div><div class="n"><?= (int) $member['points'] ?></div><div class="h">可用于商城抵扣</div></div>
  <div class="mb-card mb-stat"><div class="l">累计消费</div><div class="n" style="font-size:24px">￥<?= number_format($member['total_spent'], 2) ?></div><div class="h">决定自动升级等级</div></div>
  <div class="mb-card mb-stat"><div class="l">订单数</div><div class="n"><?= (int) $member['order_count'] ?></div><div class="h">商城回传统计</div></div>
</div>

<section class="mb-section mb-split">
  <div class="mb-card mb-card-pad">
    <div class="mb-section-head" style="margin-bottom:14px"><h2>会员资料</h2><p>只读视图</p></div>
    <div class="mb-profile-grid">
      <div><div class="mb-hint">姓名</div><strong><?= e($member['real_name'] ?: '-') ?></strong></div>
      <div><div class="mb-hint">用户名</div><strong><?= e($member['username']) ?></strong></div>
      <div><div class="mb-hint">手机号</div><strong><?= e($member['mobile']) ?></strong></div>
      <div><div class="mb-hint">邮箱</div><strong><?= e($member['email'] ?: '-') ?></strong></div>
      <div><div class="mb-hint">公司</div><strong><?= e($member['company'] ?: '-') ?></strong></div>
      <div><div class="mb-hint">状态</div><strong><?= (int) $member['status'] === 1 ? '正常' : '停用' ?></strong></div>
    </div>
  </div>
  <div class="mb-card mb-card-pad">
    <div class="mb-section-head" style="margin-bottom:14px"><h2>跟进备注</h2><p>仅自己可见</p></div>
    <form class="mb-form" method="post" action="/member/staff/remark">
      <input type="hidden" name="id" value="<?= (int) $raw['id'] ?>">
      <textarea class="mb-textarea" name="remark" placeholder="记录沟通情况、需求、售后跟进等"><?= e($raw['remark'] ?? '') ?></textarea>
      <button class="mb-btn" type="submit">保存备注</button>
    </form>
  </div>
</section>

<section class="mb-section">
  <div class="mb-tabs">
    <a class="<?= $kind === 'balance' ? 'on' : '' ?>" href="/member/staff/member/<?= (int) $raw['id'] ?>?kind=balance">余额流水</a>
    <a class="<?= $kind === 'points' ? 'on' : '' ?>" href="/member/staff/member/<?= (int) $raw['id'] ?>?kind=points">积分流水</a>
  </div>
  <div class="mb-card">
    <?php if (!$res['list']): ?>
      <div class="mb-empty">暂无流水</div>
    <?php else: ?>
    <div class="mb-table-wrap">
      <table class="mb-table">
        <thead><tr><th>时间</th><th>类型</th><th class="right">变动</th><th class="right">变动后</th><th>业务单号</th><th>备注</th></tr></thead>
        <tbody>
        <?php foreach ($res['list'] as $l): ?>
          <?php
            $n = $kind === 'balance' ? (float) $l['change_amount'] : (int) $l['change_points'];
            $after = $kind === 'balance' ? (float) $l['after_balance'] : (int) $l['after_points'];
          ?>
          <tr>
            <td><?= date('Y-m-d H:i', (int) $l['created_at']) ?></td>
            <td><span class="mb-badge gray"><?= e($l['type_text']) ?></span></td>
            <td class="right" style="font-weight:800;color:<?= $n >= 0 ? '#0f7d4e' : '#b42318' ?>"><?= $n >= 0 ? '+' : '' ?><?= $kind === 'balance' ? number_format($n, 2) : $n ?></td>
            <td class="right"><?= $kind === 'balance' ? number_format($after, 2) : $after ?></td>
            <td style="font-size:12px;color:#64748b"><?= e($l['biz_no'] ?: '-') ?></td>
            <td style="font-size:12px;color:#64748b"><?= e($l['remark'] ?: '-') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
    <div class="mb-pager"><?php echo \plugins\member\service\Member::pager($res['page'], $res['pages'], '/member/staff/member/' . (int) $raw['id'] . '?kind=' . $kind . '&'); ?></div>
  </div>
</section>
<?php include __DIR__ . '/_staff_foot.php'; ?>
