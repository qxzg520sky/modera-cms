<?php include __DIR__ . '/_staff_head.php'; ?>
<section class="mb-hero" style="padding-bottom:14px">
  <div class="mb-kicker">MY MEMBERS</div>
  <h1 class="mb-title">我的会员</h1>
  <p class="mb-sub">你只能查看和维护自己名下的会员；如需要接收新会员，请联系管理员分配。</p>
</section>

<div class="mb-grid mb-grid-4">
  <div class="mb-card mb-stat"><div class="l">名下会员</div><div class="n"><?= (int) $stats['members'] ?></div><div class="h">当前分配总数</div></div>
  <div class="mb-card mb-stat"><div class="l">会员余额合计</div><div class="n" style="font-size:24px">￥<?= number_format($stats['balance'], 2) ?></div><div class="h">仅统计自己名下</div></div>
  <div class="mb-card mb-stat"><div class="l">积分合计</div><div class="n"><?= (int) $stats['points'] ?></div><div class="h">不含已扣减</div></div>
  <div class="mb-card mb-stat"><div class="l">今日新增</div><div class="n"><?= (int) $stats['today'] ?></div><div class="h">今天注册的会员</div></div>
</div>

<section class="mb-section">
  <div class="mb-card">
    <div class="mb-section-head" style="padding:16px 18px 0;margin-bottom:0">
      <h2>会员列表</h2>
      <form method="get" action="/member/staff" style="display:flex;gap:8px">
        <input class="mb-input" style="height:34px;width:220px" name="keyword" value="<?= e($kw) ?>" placeholder="姓名 / 手机号 / 会员编号">
        <button class="mb-btn sm" type="submit">搜索</button>
      </form>
    </div>
    <?php if (!$res['list']): ?>
      <div class="mb-empty">没有找到会员</div>
    <?php else: ?>
    <div class="mb-table-wrap" style="padding:6px 4px 0">
      <table class="mb-table">
        <thead><tr><th>会员</th><th>等级</th><th class="right">余额</th><th class="right">积分</th><th class="right">累计消费</th><th>注册时间</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($res['list'] as $m): ?>
          <tr>
            <td><strong><?= e($m['real_name'] ?: $m['username']) ?></strong><div style="font-size:11.5px;color:#94a3b8"><?= e($m['member_no']) ?> · <?= e($m['mobile']) ?></div></td>
            <td><span class="mb-badge"><?= e($m['level_name']) ?></span></td>
            <td class="right">￥<?= number_format((float) $m['balance'], 2) ?></td>
            <td class="right"><?= (int) $m['points'] ?></td>
            <td class="right">￥<?= number_format((float) $m['total_spent'], 2) ?></td>
            <td style="font-size:12px;color:#64748b"><?= $m['created_at'] ? date('Y-m-d', (int) $m['created_at']) : '-' ?></td>
            <td class="right"><a class="mb-btn ghost sm" href="/member/staff/member/<?= (int) $m['id'] ?>">查看</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
    <div class="mb-pager"><?php echo \plugins\member\service\Member::pager($res['page'], $res['pages'], '/member/staff?keyword=' . urlencode($kw) . '&'); ?></div>
  </div>
</section>
<?php include __DIR__ . '/_staff_foot.php'; ?>
