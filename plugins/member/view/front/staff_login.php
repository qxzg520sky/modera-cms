<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>业务员登录 - 会员管理</title>
<link rel="stylesheet" href="/member/asset/member.css?v=1.0.0">
</head>
<body class="mb-body">
<div class="mb-auth-wrap">
  <div class="mb-card mb-auth-card">
    <div class="mb-auth-head">
      <span class="mark" style="background:linear-gradient(135deg,#0f766e,#14b8a6)">S</span>
      <h1>业务员登录</h1>
      <p>仅可查看和维护自己名下的会员，不能跨业务员查询</p>
    </div>
    <?php if ($flash !== ''): ?><div class="mb-flash"><?= e($flash) ?></div><?php endif; ?>
    <form class="mb-form" method="post" action="/member/staff/login">
      <div class="mb-field"><label>业务员账号</label><input class="mb-input" name="username" autocomplete="username" required></div>
      <div class="mb-field"><label>密码</label><input class="mb-input" type="password" name="password" autocomplete="current-password" required></div>
      <button class="mb-btn block" type="submit">进入工作台</button>
    </form>
    <div class="mb-auth-foot">管理员请走 <a href="/admin">后台入口</a>　|　会员请走 <a href="/member/login">会员登录</a></div>
  </div>
</div>
<footer class="mb-footer"><div class="mb-wrap">会员管理 · 业务员工作台</div></footer>
</body>
</html>
