<?php
$siteName = $siteName ?? '企业官网';
$title    = $title ?? '会员中心';
$nav      = $nav ?? '';
$member   = $member ?? [];
$flash    = $flash ?? '';
$name     = (string) ($member['real_name'] ?? '');
if ($name === '') $name = (string) ($member['username'] ?? '会员');
$initial  = mb_substr($name, 0, 1);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> - <?= e($siteName) ?></title>
<link rel="stylesheet" href="/member/asset/member.css?v=1.0.0">
</head>
<body class="mb-body">
<header class="mb-top">
  <div class="mb-wrap mb-top-inner">
    <a class="mb-brand" href="/member/center">
      <span class="mb-brand-mark">M</span>
      <span><?= e($siteName) ?><span style="display:block;font-size:10.5px;color:#94a3b8;font-weight:600;letter-spacing:1.4px">MEMBER CENTER</span></span>
    </a>
    <?php if ($member): ?>
    <nav class="mb-nav">
      <a class="<?= $nav === 'center' ? 'on' : '' ?>" href="/member/center">会员中心</a>
      <a class="<?= $nav === 'orders' ? 'on' : '' ?>" href="/member/orders">我的订单</a>
      <a class="<?= $nav === 'address' ? 'on' : '' ?>" href="/member/address">收货地址</a>
      <a class="<?= $nav === 'logs' ? 'on' : '' ?>" href="/member/logs">资产明细</a>
      <a class="<?= $nav === 'profile' ? 'on' : '' ?>" href="/member/profile">会员资料</a>
    </nav>
    <?php endif; ?>
    <div class="mb-top-spacer"></div>
    <?php if ($member): ?>
      <div class="mb-user">
        <span class="mb-avatar"><?= e($initial) ?></span>
        <span><?= e($name) ?><span style="display:block;color:#94a3b8;font-size:11px"><?= e($member['level_name'] ?? '') ?></span></span>
        <a class="mb-link" href="/member/logout" onclick="return confirm('确定退出会员中心？')">退出</a>
      </div>
    <?php else: ?>
      <div class="mb-user">
        <a class="mb-btn ghost sm" href="/member/login">登录</a>
        <a class="mb-btn sm" href="/member/register">注册</a>
      </div>
    <?php endif; ?>
  </div>
</header>
<main class="mb-wrap">
<?php if ($flash !== ''): ?><div class="mb-flash" style="margin-top:18px"><?= e($flash) ?></div><?php endif; ?>
