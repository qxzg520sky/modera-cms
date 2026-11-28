<?php include __DIR__ . '/_head.php'; ?>
<div class="sp-section">
  <h2 style="margin:0 0 16px;font-size:20px">确认订单</h2>
  <?php if ($err): ?>
    <div class="sp-note err"><?= e($err) ?> <a href="/shop/cart">返回购物车</a></div>
  <?php else: ?>
  <div class="sp-row">
    <div style="flex:1;min-width:0">
      <div class="sp-card" style="padding:16px 18px">
        <h3 style="margin:0 0 12px;font-size:15px">收货地址</h3>
        <?php if (!$preview['addresses']): ?>
          <div class="sp-note warn">还没有收货地址，请先去 <a href="/member/address">会员中心添加地址</a>。</div>
        <?php else: foreach ($preview['addresses'] as $a): ?>
          <a class="sp-address <?= (int) $preview['address']['id'] === (int) $a['id'] ? 'on' : '' ?>" href="/shop/checkout?address_id=<?= (int) $a['id'] ?>&coupon_code=<?= urlencode($couponCode) ?>&use_points=<?= $usePoints ? 1 : 0 ?>">
            <div>
              <b><?= e($a['receiver']) ?></b> <?= e($a['mobile']) ?> <?php if ((int) ($a['is_default'] ?? 0) === 1): ?><span class="sp-tag">默认</span><?php endif; ?>
              <div style="font-size:12.5px;color:#64748b;margin-top:3px"><?= e($a['province'] . $a['city'] . $a['district'] . ($a['detail'] ?? $a['address'] ?? '')) ?></div>
            </div>
          </a>
        <?php endforeach; endif; ?>
        <div style="margin-top:8px"><a class="sp-btn ghost" href="/member/address">管理地址</a></div>
      </div>

      <div class="sp-card" style="padding:16px 18px;margin-top:14px">
        <h3 style="margin:0 0 12px;font-size:15px">优惠与积分</h3>
        <form method="get" action="/shop/checkout" style="display:grid;gap:10px">
          <input type="hidden" name="address_id" value="<?= (int) $preview['address']['id'] ?>">
          <div class="sp-form-line" style="margin:0">
            <input name="coupon_code" value="<?= e($couponCode) ?>" placeholder="优惠券码" style="border:1px solid #d8e1ee;border-radius:9px;padding:9px 12px;flex:1;min-width:180px">
            <label style="display:flex;gap:6px;align-items:center;font-size:12.5px"><input type="checkbox" name="use_points" value="1" <?= $usePoints ? 'checked' : '' ?>> 用积分抵扣</label>
            <button class="sp-btn ghost">重新计算</button>
          </div>
        </form>
        <div style="font-size:12px;color:#94a3b8;margin-top:8px">可用积分 <?= (int) $preview['member']['points'] ?>，本单最多可抵 <?= (int) $preview['points_max'] ?> 分（<?= (int) $shopCfg['points_rate'] ?> 积分 = 1 元，上限 <?= (int) $shopCfg['points_max_percent'] ?>%）。</div>
      </div>

      <div class="sp-card" style="padding:16px 18px;margin-top:14px">
        <h3 style="margin:0 0 12px;font-size:15px">商品清单</h3>
        <table class="sp-table">
          <thead><tr><th>商品</th><th>单价</th><th>数量</th><th>小计</th></tr></thead>
          <tbody>
          <?php foreach ($preview['items'] as $it): ?>
            <tr>
              <td><b><?= e($it['product_title']) ?></b><div style="font-size:12px;color:#94a3b8"><?= e($it['spec_text']) ?: '单规格' ?></div></td>
              <td>￥<?= number_format((float) $it['price'], 2) ?></td>
              <td><?= (int) $it['qty'] ?></td>
              <td>￥<?= number_format((float) $it['subtotal'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <form method="post" action="/shop/order/create" id="orderForm" style="margin-top:14px">
        <input type="hidden" name="biz_no" value="<?= e($bizNo) ?>">
        <input type="hidden" name="address_id" value="<?= (int) $preview['address']['id'] ?>">
        <input type="hidden" name="coupon_code" value="<?= e($couponCode) ?>">
        <input type="hidden" name="use_points" value="<?= $usePoints ? 1 : 0 ?>">
        <div class="sp-card" style="padding:14px 18px">
          <div class="sp-form-line" style="margin:0">
            <span style="font-size:12.5px;color:#64748b">买家备注</span>
            <input name="buyer_remark" placeholder="选填" style="flex:1;border:1px solid #d8e1ee;border-radius:9px;padding:9px 12px">
          </div>
        </div>
      </form>
    </div>

    <aside style="width:330px;flex:none">
      <div class="sp-card" style="position:sticky;top:80px">
        <div class="sp-summary">
          <div class="line"><span>会员</span><b><?= e($preview['member']['level_name']) ?>（<?= number_format((float) $preview['discount_rate'], 2) ?> 折）</b></div>
          <div class="line"><span>商品金额</span><b>￥<?= number_format((float) $preview['goods_amount'], 2) ?></b></div>
          <div class="line"><span>会员折扣</span><b>-￥<?= number_format((float) $preview['member_discount'], 2) ?></b></div>
          <div class="line"><span>优惠券 <?= e($preview['coupon']['code'] ?? '') ?></span><b>-￥<?= number_format((float) $preview['coupon_amount'], 2) ?></b></div>
          <div class="line"><span>积分抵扣（<?= (int) $preview['points_used'] ?> 分）</span><b>-￥<?= number_format((float) $preview['points_amount'], 2) ?></b></div>
          <div class="line"><span>当前余额</span><b>￥<?= number_format((float) $preview['member']['balance'], 2) ?></b></div>
          <div class="total"><span>应付</span><span>￥<?= number_format((float) $preview['payable_amount'], 2) ?></span></div>
          <button class="sp-btn" type="submit" form="orderForm" style="width:100%;margin-top:8px" <?= empty($preview['addresses']) ? 'disabled' : '' ?>>提交订单</button>
          <div style="font-size:11.5px;color:#94a3b8;text-align:center">提交后进入订单详情，再用余额支付。余额不足会提示，不会重复扣款。</div>
        </div>
      </div>
    </aside>
  </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/_foot.php'; ?>
