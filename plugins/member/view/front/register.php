<?php include __DIR__ . '/_head.php'; ?>
<div class="mb-auth-wrap">
  <div class="mb-card mb-auth-card">
    <div class="mb-auth-head">
      <span class="mark">M</span>
      <h1>注册会员</h1>
      <p>注册即成为普通会员，后续消费可自动升级等级并享受会员折扣</p>
    </div>
    <?php if (!empty($closed)): ?>
      <div class="mb-flash">当前站点未开放会员注册，请联系管理员开通账号。</div>
    <?php else: ?>
    <form class="mb-form" method="post" action="/member/register">
      <div class="mb-field">
        <label>手机号 <span style="color:#dc2626">*</span></label>
        <input class="mb-input" type="text" name="mobile" placeholder="用于登录和接收通知" required>
      </div>
      <div class="mb-field">
        <label>用户名（可选）</label>
        <input class="mb-input" type="text" name="username" placeholder="字母、数字、下划线，3-50 位">
      </div>
      <div class="mb-field">
        <label>密码 <span style="color:#dc2626">*</span></label>
        <input class="mb-input" type="password" name="password" placeholder="至少 6 位" autocomplete="new-password" required>
      </div>
      <div class="mb-field">
        <label>姓名</label>
        <input class="mb-input" type="text" name="real_name" placeholder="请输入真实姓名">
      </div>
      <div class="mb-field">
        <label>公司</label>
        <input class="mb-input" type="text" name="company" placeholder="公司 / 单位名称">
      </div>
      <div class="mb-field">
        <label>邮箱</label>
        <input class="mb-input" type="email" name="email" placeholder="用于接收资产与订单通知">
      </div>
      <button class="mb-btn block" type="submit">注册并进入会员中心</button>
    </form>
    <?php endif; ?>
    <div class="mb-auth-foot">已有账号？<a href="/member/login">直接登录</a></div>
  </div>
</div>
<?php include __DIR__ . '/_foot.php'; ?>
