<?php $pageTitle = $pageTitle ?? ($isNew ? '新增会员' : '编辑会员'); include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/member/asset/admin.css?v=1.0.0">
<?php
  $val = function (string $k, $default = '') use ($m) { return $m[$k] ?? $default; };
  $defaultLevel = \plugins\member\service\Member::defaultLevelId();
?>

<div class="card">
  <div class="card-head">
    <span><?= $isNew ? '新增会员' : '编辑会员 · ' . e($m['member_no']) ?></span>
    <a class="btn btn-sm" href="/admin/plugin/member/members">← 返回列表</a>
  </div>
  <form method="post" action="/admin/plugin/member/member/save" style="padding:18px">
    <input type="hidden" name="id" value="<?= (int) $val('id', 0) ?>">
    <div class="mb-admin-form">
      <div><label>手机号 <span style="color:#dc2626">*</span></label><input name="mobile" value="<?= e((string) $val('mobile')) ?>" required placeholder="11 位手机号"></div>
      <div><label>用户名</label><input name="username" value="<?= e((string) $val('username')) ?>" placeholder="不填自动生成"></div>
      <div><label>姓名</label><input name="real_name" value="<?= e((string) $val('real_name')) ?>"></div>
      <div><label>公司</label><input name="company" value="<?= e((string) $val('company')) ?>"></div>
      <div><label>邮箱</label><input name="email" type="email" value="<?= e((string) $val('email')) ?>"></div>
      <div><label>性别</label>
        <select name="gender">
          <option value="0" <?= (int) $val('gender', 0) === 0 ? 'selected' : '' ?>>保密</option>
          <option value="1" <?= (int) $val('gender', 0) === 1 ? 'selected' : '' ?>>男</option>
          <option value="2" <?= (int) $val('gender', 0) === 2 ? 'selected' : '' ?>>女</option>
        </select>
      </div>
      <div><label>生日</label><input name="birthday" type="date" value="<?= e((string) $val('birthday')) ?>"></div>
      <div><label>会员等级</label>
        <select name="level_id">
          <?php $curLevel = (int) $val('level_id', $isNew ? $defaultLevel : 0); foreach ($levels as $lv): ?>
            <option value="<?= (int) $lv['id'] ?>" <?= $curLevel === (int) $lv['id'] ? 'selected' : '' ?>><?= e($lv['name']) ?>（<?= e(\plugins\member\service\Member::discountText((float) $lv['discount'])) ?> / 满 <?= number_format((float) $lv['min_spent'], 0) ?> 元）</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div><label>归属业务员</label>
        <select name="staff_id">
          <option value="0">不分配</option>
          <?php foreach ($staffs as $s): ?>
            <option value="<?= (int) $s['id'] ?>" <?= (int) $val('staff_id', 0) === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['real_name'] ?: $s['username']) ?>（<?= e($s['username']) ?>）</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div><label>状态</label>
        <select name="status">
          <option value="1" <?= (int) $val('status', 1) === 1 ? 'selected' : '' ?>>正常</option>
          <option value="0" <?= (int) $val('status', 1) === 0 ? 'selected' : '' ?>>停用</option>
        </select>
      </div>
      <div><label>累计消费（元）</label><input name="total_spent" value="<?= e((string) $val('total_spent', 0)) ?>"><div class="hint">修改累计消费后保存，会按等级门槛自动升级（不降级）</div></div>
      <div><label>登录密码</label><input name="password" type="password" placeholder="<?= $isNew ? '留空使用默认密码' : '留空表示不修改' ?>"><div class="hint">默认密码见会员设置</div></div>
      <?php if ($isNew): ?>
        <div><label>初始余额（元）</label><input name="balance" value="0"><div class="hint">保存后会写一条初始化余额日志</div></div>
        <div><label>初始积分</label><input name="points" value="0"><div class="hint">保存后会写一条初始化积分日志</div></div>
      <?php else: ?>
        <div class="full"><div class="hint">余额 / 积分请在会员详情页的“资产调整”中操作，保存资料不会改动资产。</div></div>
      <?php endif; ?>
      <div class="full"><label>备注</label><textarea name="remark" placeholder="内部备注，仅后台可见"><?= e((string) $val('remark')) ?></textarea></div>
    </div>
    <div style="margin-top:18px;display:flex;gap:10px">
      <button class="btn btn-primary" type="submit"><?= $isNew ? '创建会员' : '保存修改' ?></button>
      <a class="btn" href="/admin/plugin/member/members">取消</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>

