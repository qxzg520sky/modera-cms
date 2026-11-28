<?php include __DIR__ . '/_head.php'; ?>
<section class="mb-hero" style="padding-bottom:10px">
  <div class="mb-kicker">PROFILE</div>
  <h1 class="mb-title">会员资料</h1>
  <p class="mb-sub">会员编号 <?= e($member['member_no']) ?>，注册时间 <?= $member['created_at'] ? date('Y-m-d H:i', (int) $member['created_at']) : '-' ?>。</p>
</section>

<section class="mb-section mb-split">
  <div class="mb-card mb-card-pad">
    <div class="mb-section-head" style="margin-bottom:14px"><h2>基本资料</h2><p>手机号如需变更请联系管理员</p></div>
    <form class="mb-form" method="post" action="/member/profile/save">
      <div class="mb-profile-grid">
        <div class="mb-field"><label>手机号</label><input class="mb-input" value="<?= e($member['mobile']) ?>" disabled></div>
        <div class="mb-field"><label>用户名</label><input class="mb-input" value="<?= e($member['username']) ?>" disabled></div>
        <div class="mb-field"><label>姓名</label><input class="mb-input" name="real_name" value="<?= e($member['real_name']) ?>"></div>
        <div class="mb-field"><label>公司</label><input class="mb-input" name="company" value="<?= e($member['company']) ?>"></div>
        <div class="mb-field"><label>邮箱</label><input class="mb-input" name="email" value="<?= e($member['email']) ?>"></div>
        <div class="mb-field"><label>性别</label>
          <select class="mb-select" name="gender">
            <option value="0" <?= (int) $member['gender'] === 0 ? 'selected' : '' ?>>保密</option>
            <option value="1" <?= (int) $member['gender'] === 1 ? 'selected' : '' ?>>男</option>
            <option value="2" <?= (int) $member['gender'] === 2 ? 'selected' : '' ?>>女</option>
          </select>
        </div>
        <div class="mb-field"><label>生日</label><input class="mb-input" type="date" name="birthday" value="<?= e($member['birthday'] ?: '') ?>"></div>
        <div class="mb-field"><label>会员等级</label><input class="mb-input" value="<?= e($member['level_name']) ?>" disabled></div>
      </div>
      <button class="mb-btn" type="submit">保存资料</button>
    </form>
  </div>

  <div>
    <div class="mb-card mb-card-pad">
      <div class="mb-section-head" style="margin-bottom:14px"><h2>修改密码</h2><p>密码修改后需要重新登录</p></div>
      <form class="mb-form" method="post" action="/member/password">
        <div class="mb-field"><label>原密码</label><input class="mb-input" type="password" name="old_password" required></div>
        <div class="mb-field"><label>新密码</label><input class="mb-input" type="password" name="password" placeholder="至少 6 位" required></div>
        <div class="mb-field"><label>确认新密码</label><input class="mb-input" type="password" name="password2" required></div>
        <button class="mb-btn" type="submit">更新密码</button>
      </form>
    </div>
    <div class="mb-note" style="margin-top:16px">
      <strong>安全提示</strong><br>
      会员中心不会以任何理由索取短信验证码或支付密码。如发现余额异常，请通过资产明细核对业务单号，并联系业务员处理。
    </div>
  </div>
</section>
<?php include __DIR__ . '/_foot.php'; ?>
