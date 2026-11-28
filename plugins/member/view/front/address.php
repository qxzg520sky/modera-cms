<?php include __DIR__ . '/_head.php'; ?>
<section class="mb-hero" style="padding-bottom:10px">
  <div class="mb-kicker">ADDRESS BOOK</div>
  <h1 class="mb-title">收货地址</h1>
  <p class="mb-sub">维护常用收货信息，下单时可直接选择默认地址。</p>
</section>

<section class="mb-section mb-split">
  <div>
    <div class="mb-section-head"><h2>我的地址</h2><p>共 <?= count($addresses) ?> 条</p></div>
    <?php if (!$addresses): ?>
      <div class="mb-card"><div class="mb-empty">还没有收货地址，请在右侧新增。</div></div>
    <?php else: ?>
      <div class="mb-grid" style="gap:12px">
        <?php foreach ($addresses as $a): ?>
        <div class="mb-address <?= $a['is_default'] ? 'default' : '' ?>">
          <div class="h">
            <div><span class="name"><?= e($a['receiver']) ?></span> <span class="mb-badge gray"><?= e($a['mobile']) ?></span></div>
            <?php if ($a['is_default']): ?><span class="mb-badge">默认</span><?php endif; ?>
          </div>
          <div class="detail"><?= e($a['province'] . $a['city'] . $a['district']) ?> <?= e($a['detail']) ?><?= $a['postcode'] ? '（' . e($a['postcode']) . '）' : '' ?></div>
          <div class="ops">
            <a class="mb-btn ghost sm" href="/member/address?edit=<?= (int) $a['id'] ?>">编辑</a>
            <form method="post" action="/member/address/delete" onsubmit="return confirm('确定删除该地址？')">
              <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
              <button class="mb-btn danger sm" type="submit">删除</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div>
    <div class="mb-section-head"><h2><?= $editing ? '编辑地址' : '新增地址' ?></h2></div>
    <div class="mb-card mb-card-pad">
      <form class="mb-form" method="post" action="/member/address/save">
        <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
        <div class="mb-profile-grid">
          <div class="mb-field"><label>收货人</label><input class="mb-input" name="receiver" value="<?= e($editing['receiver'] ?? '') ?>" required></div>
          <div class="mb-field"><label>手机号</label><input class="mb-input" name="mobile" value="<?= e($editing['mobile'] ?? '') ?>" required></div>
          <div class="mb-field"><label>省份</label><input class="mb-input" name="province" value="<?= e($editing['province'] ?? '') ?>"></div>
          <div class="mb-field"><label>城市</label><input class="mb-input" name="city" value="<?= e($editing['city'] ?? '') ?>"></div>
          <div class="mb-field"><label>区县</label><input class="mb-input" name="district" value="<?= e($editing['district'] ?? '') ?>"></div>
          <div class="mb-field"><label>邮编</label><input class="mb-input" name="postcode" value="<?= e($editing['postcode'] ?? '') ?>"></div>
        </div>
        <div class="mb-field"><label>详细地址</label><textarea class="mb-textarea" name="detail" required><?= e($editing['detail'] ?? '') ?></textarea></div>
        <label style="display:flex;gap:8px;align-items:center;font-size:12.5px;color:#475569"><input type="checkbox" name="is_default" value="1" <?= !empty($editing['is_default']) ? 'checked' : '' ?>> 设为默认收货地址</label>
        <div style="display:flex;gap:10px">
          <button class="mb-btn" type="submit"><?= $editing ? '保存修改' : '新增地址' ?></button>
          <?php if ($editing): ?><a class="mb-btn gray" href="/member/address">取消编辑</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>
</section>
<?php include __DIR__ . '/_foot.php'; ?>
