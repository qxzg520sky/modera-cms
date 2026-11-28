<?php
$pageTitle = $pageTitle ?? ($shopCfg['shop_name'] ?? '企业商城');
if ($pageTitle === ($shopCfg['shop_name'] ?? '企业商城')) {
    $pageTitle = '商城';
}
$me = $me ?? [];
$cartCount = 0;
if (!empty($me['id'])) {
    try { $cartCount = (int) \think\facade\Db::name('shop_cart')->where('member_id', (int) $me['id'])->sum('qty'); } catch (\Throwable $e) {}
}
$flash = session('shop_flash');
if ($flash !== null && $flash !== '') session('shop_flash', null);
$cur = '/' . ltrim((string) request()->pathinfo(), '/');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($pageTitle) ?> - <?= e($shopCfg['shop_name'] ?? '企业商城') ?></title>
<link rel="stylesheet" href="/shop/asset/shop.css?v=1.0.2">
<?php
$shopSkin = root_path() . 'themes/' . \app\service\ThemeManager::current() . '/shop.css';
if (is_file($shopSkin)): ?>
<link rel="stylesheet" href="/theme/shop.css?v=1.0.0">
<?php endif; ?>
</head>
<body class="xb-shop">
<header class="sp-nav">
  <div class="in">
    <a class="sp-logo" href="/shop"><?= e($shopCfg['shop_name'] ?? '企业商城') ?><span> · STORE</span></a>
    <div class="links">
      <a href="/">官网首页</a>
      <a href="/shop">全部商品</a>
      <?php if ($me): ?>
        <a href="/shop/cart">购物车<?php if ($cartCount > 0): ?><span class="sp-badge-count"><?= $cartCount ?></span><?php endif; ?></a>
        <a href="/shop/orders">我的订单</a>
        <a href="/shop/after-sales">售后</a>
        <a href="/member/center">会员中心</a>
        <span class="user"><?= e($me['real_name'] ?: $me['username']) ?> · <?= e($me['level_name']) ?> · 余额 ￥<?= number_format((float) $me['balance'], 2) ?> · <?= (int) $me['points'] ?> 分</span>
        <a href="/member/logout">退出</a>
      <?php else: ?>
        <a href="/member/login?redirect=<?= urlencode('/shop') ?>">登录</a>
        <a href="/member/register">注册</a>
      <?php endif; ?>
    </div>
  </div>
</header>
<main class="sp-wrap">
<?php if ($flash): ?><div class="sp-alert"><?= e($flash) ?></div><?php endif; ?>
