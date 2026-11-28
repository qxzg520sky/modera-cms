<?php
$title = $title ?? '业务员工作台';
$flash = $flash ?? '';
$staff = $staff ?? [];
$nav   = $nav ?? 'workbench';
$staffName = $staff['real_name'] ?? ($staff['username'] ?? '业务员');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> - 业务员工作台</title>
<link rel="stylesheet" href="/member/asset/member.css?v=1.0.0">
</head>
<body class="mb-body">
<header class="mb-top">
  <div class="mb-wrap mb-top-inner">
    <a class="mb-brand" href="/member/staff/workbench">
      <span class="mb-brand-mark" style="background:linear-gradient(135deg,#0f766e,#14b8a6)">S</span>
      <span>业务员工作台<span style="display:block;font-size:10.5px;color:#94a3b8;font-weight:600;letter-spacing:1.4px">STAFF DESK</span></span>
    </a>
    <nav class="mb-nav">
      <a class="<?= $nav === 'workbench' ? 'on' : '' ?>" href="/member/staff/workbench">我的会员</a>
    </nav>
    <div class="mb-top-spacer"></div>
    <div class="mb-user">
      <span class="mb-avatar"><?= e(mb_substr($staffName, 0, 1)) ?></span>
      <span><?= e($staffName) ?><span style="display:block;color:#94a3b8;font-size:11px"><?= e($staff['role'] ?? '') ?></span></span>
      <a class="mb-link" href="/member/staff/logout" onclick="return confirm('确定退出业务员工作台？')">退出</a>
    </div>
  </div>
</header>
<main class="mb-wrap">
<?php if ($flash !== ''): ?><div class="mb-flash" style="margin-top:18px"><?= e($flash) ?></div><?php endif; ?>
