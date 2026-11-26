<?php $pageTitle = $pageTitle ?? '商城设置'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/shop/asset/admin.css?v=1.0.0">
<?php if ($flash): ?><div class="sp-alert ok"><?= e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="sp-alert err"><?= e($error) ?></div><?php endif; ?>
<?php if (!$memberOk): ?><div class="sp-alert err">会员插件未启用，商城前台无法登录和结算。</div><?php endif; ?>

<form method="post" action="/admin/plugin/shop/settings/save">
  <div class="card">
    <div class="card-head"><span>基础设置</span></div>
    <div style="padding:16px 18px" class="sp-form-row">
      <div class="sp-field"><label>商城名称</label><input name="shop_name" value="<?= e($cfg['shop_name']) ?>"></div>
      <div class="sp-field"><label>每页商品数</label><input name="page_size" type="number" min="6" max="48" value="<?= (int) $cfg['page_size'] ?>"></div>
      <div class="sp-field"><label>库存预警默认值</label><input name="warn_stock" type="number" min="0" value="<?= (int) $cfg['warn_stock'] ?>"></div>
      <div class="sp-field"><label>待付款自动关闭（分钟，0=不自动）</label><input name="auto_close_minutes" type="number" min="0" value="<?= (int) $cfg['auto_close_minutes'] ?>"><span class="sp-muted">当前版本未挂定时任务，仅保留配置位</span></div>
      <div class="sp-field" style="grid-column:1/-1"><label>商城公告</label><textarea name="shop_notice"><?= e($cfg['shop_notice']) ?></textarea></div>
    </div>
  </div>

  <div class="card" style="margin-top:16px">
    <div class="card-head"><span>支付与抵扣</span></div>
    <div style="padding:16px 18px" class="sp-form-row">
      <div class="sp-field"><label>允许余额支付</label><select name="balance_pay"><option value="1" <?= $cfg['balance_pay'] === '1' ? 'selected' : '' ?>>开启</option><option value="0" <?= $cfg['balance_pay'] !== '1' ? 'selected' : '' ?>>关闭</option></select></div>
      <div class="sp-field"><label>允许积分抵扣</label><select name="points_deduction"><option value="1" <?= $cfg['points_deduction'] === '1' ? 'selected' : '' ?>>开启</option><option value="0" <?= $cfg['points_deduction'] !== '1' ? 'selected' : '' ?>>关闭</option></select></div>
      <div class="sp-field"><label>多少积分抵 1 元</label><input name="points_rate" type="number" step="1" min="1" value="<?= (int) $cfg['points_rate'] ?>"></div>
      <div class="sp-field"><label>积分最多抵扣订单金额百分比</label><input name="points_max_percent" type="number" min="0" max="100" value="<?= (int) $cfg['points_max_percent'] ?>"></div>
      <div class="sp-field"><label>会员折扣与优惠券</label><select name="discount_stack"><option value="1" <?= $cfg['discount_stack'] === '1' ? 'selected' : '' ?>>叠加（先会员折扣再算券）</option><option value="0" <?= $cfg['discount_stack'] !== '1' ? 'selected' : '' ?>>不叠加（用券时不再享会员折扣）</option></select></div>
    </div>
  </div>

  <div class="card" style="margin-top:16px">
    <div class="card-head"><span>会员插件对接</span></div>
    <div style="padding:16px 18px" class="sp-form-row">
      <div class="sp-field"><label>调用模式</label><select name="member_mode"><option value="service" <?= $cfg['member_mode'] === 'service' ? 'selected' : '' ?>>service：直接调用 Member 服务类（推荐，同站零配置）</option><option value="api" <?= $cfg['member_mode'] === 'api' ? 'selected' : '' ?>>api：调用 /member/api/* 签名接口</option></select></div>
      <div class="sp-field"><label>会员 API Base URL</label><input name="member_api_base" value="<?= e($cfg['member_api_base']) ?>" placeholder="http://tempqiyecms"></div>
      <div class="sp-field"><label>会员 API AppID</label><input name="member_appid" value="<?= e($cfg['member_appid']) ?>"></div>
      <div class="sp-field"><label>会员 API Secret（留空自动读会员插件配置）</label><input name="member_secret" value="<?= e($cfg['member_secret']) ?>"></div>
    </div>
    <div class="sp-muted" style="padding:0 18px 16px">商城不会读取任何 member_* 表。service 模式只调用公开服务方法；api 模式需要会员设置里的 AppID/Secret。</div>
  </div>

  <div class="card" style="margin-top:16px">
    <div class="card-head"><span>商城对外 API 密钥（给小程序/前端服务端）</span></div>
    <div style="padding:16px 18px" class="sp-form-row">
      <div class="sp-field"><label>AppID</label><input name="api_appid" value="<?= e($cfg['api_appid']) ?>"></div>
      <div class="sp-field"><label>Secret</label><input name="api_secret" value="<?= e($cfg['api_secret']) ?>"></div>
      <div class="sp-field"><label>IP 白名单（逗号分隔，空=不限）</label><input name="api_ip_whitelist" value="<?= e($cfg['api_ip_whitelist']) ?>"></div>
    </div>
    <div class="sp-muted" style="padding:0 18px 16px">签名串：appid + "\\n" + timestamp + "\\n" + nonce + "\\n" + rawBody，HMAC-SHA256。Secret 只能放服务端。</div>
  </div>

  <div class="sp-actions" style="margin:16px 4px"><button class="btn">保存设置</button></div>
</form>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>
