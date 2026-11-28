<?php include __DIR__ . '/_head.php'; ?>
<div class="mb-auth-wrap">
  <div class="mb-card mb-auth-card">
    <div class="mb-auth-head">
      <span class="mark">M</span>
      <h1>会员登录</h1>
      <p>登录后可查看会员等级、余额、积分、订单与收货地址</p>
    </div>
    <form class="mb-form" method="post" action="/member/login">
      <div class="mb-field">
        <label>手机号 / 用户名 / 邮箱</label>
        <input class="mb-input" type="text" name="account" placeholder="请输入登录账号" autocomplete="username" required>
      </div>
      <div class="mb-field">
        <label>密码</label>
        <input class="mb-input" type="password" name="password" placeholder="请输入密码" autocomplete="current-password" required>
      </div>
      <button class="mb-btn block" type="submit">登录会员中心</button>
    </form>
    <div class="mb-auth-foot">还没有账号？<a href="/member/register">立即注册</a>　|　<a href="/member/staff/login">业务员入口</a></div>
  </div>
</div>
<?php include __DIR__ . '/_foot.php'; ?>
