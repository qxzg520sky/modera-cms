<?php $pageTitle = $pageTitle ?? '会员等级'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/member/asset/admin.css?v=1.0.0">

<div class="card">
  <div class="card-head">
    <span>会员等级</span>
    <span class="muted">按累计消费自动升级；折扣用于商城结算；积分倍率可按等级差异化</span>
  </div>
  <div style="padding:18px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px">
    <?php foreach ($levels as $lv): ?>
    <div class="card" style="box-shadow:none">
      <div class="card-head" style="padding:12px 16px">
        <span><?= e($lv['name']) ?> <?php if ((int) $lv['is_default'] === 1): ?><span class="mb-admin-tag ok">默认</span><?php endif; ?></span>
        <span class="muted">ID <?= (int) $lv['id'] ?> · <?= e($lv['code']) ?></span>
      </div>
      <form method="post" action="/admin/plugin/member/level/save" style="padding:14px 16px">
        <input type="hidden" name="id" value="<?= (int) $lv['id'] ?>">
        <div class="mb-admin-form">
          <div><label>名称</label><input name="name" value="<?= e($lv['name']) ?>" required></div>
          <div><label>标识</label><input name="code" value="<?= e($lv['code']) ?>" required></div>
          <div><label>消费门槛（元）</label><input name="min_spent" value="<?= e((string) $lv['min_spent']) ?>"></div>
          <div><label>折扣（0.01-1）</label><input name="discount" value="<?= e((string) $lv['discount']) ?>"></div>
          <div><label>积分倍率</label><input name="points_rate" value="<?= e((string) $lv['points_rate']) ?>"></div>
          <div><label>排序</label><input name="sort" value="<?= (int) $lv['sort'] ?>"></div>
          <div><label>状态</label><select name="status"><option value="1" <?= (int) $lv['status'] === 1 ? 'selected' : '' ?>>启用</option><option value="0" <?= (int) $lv['status'] === 0 ? 'selected' : '' ?>>停用</option></select></div>
          <div><label>默认等级</label><select name="is_default"><option value="0">否</option><option value="1" <?= (int) $lv['is_default'] === 1 ? 'selected' : '' ?>>是</option></select></div>
          <div class="full"><label>说明</label><input name="description" value="<?= e($lv['description']) ?>"></div>
        </div>
        <div style="display:flex;gap:8px;margin-top:12px">
          <button class="btn btn-primary btn-sm" type="submit">保存</button>
          <?php if ((int) $lv['is_default'] !== 1): ?>
            <button class="btn btn-sm" type="submit" form="del-<?= (int) $lv['id'] ?>" data-confirm="确定删除等级“<?= e($lv['name']) ?>”？">删除</button>
          <?php endif; ?>
        </div>
      </form>
      <?php if ((int) $lv['is_default'] !== 1): ?>
        <form id="del-<?= (int) $lv['id'] ?>" method="post" action="/admin/plugin/member/level/delete"><input type="hidden" name="id" value="<?= (int) $lv['id'] ?>"></form>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <div class="card" style="box-shadow:none;border-style:dashed">
      <div class="card-head" style="padding:12px 16px"><span>+ 新增等级</span></div>
      <form method="post" action="/admin/plugin/member/level/save" style="padding:14px 16px">
        <div class="mb-admin-form">
          <div><label>名称</label><input name="name" placeholder="例如：钻石会员" required></div>
          <div><label>标识</label><input name="code" placeholder="例如：diamond" required></div>
          <div><label>消费门槛（元）</label><input name="min_spent" value="0"></div>
          <div><label>折扣（0.01-1）</label><input name="discount" value="1"></div>
          <div><label>积分倍率</label><input name="points_rate" value="1"></div>
          <div><label>排序</label><input name="sort" value="0"></div>
          <div><label>状态</label><select name="status"><option value="1">启用</option><option value="0">停用</option></select></div>
          <div><label>默认等级</label><select name="is_default"><option value="0">否</option><option value="1">是</option></select></div>
          <div class="full"><label>说明</label><input name="description" placeholder="展示给会员的等级权益说明"></div>
        </div>
        <div style="margin-top:12px"><button class="btn btn-primary btn-sm" type="submit">添加等级</button></div>
      </form>
    </div>
  </div>
</div>

<div class="mb-admin-note" style="margin-top:16px">
  等级自动升级只升不降；如需降级请直接编辑会员的等级字段。默认等级用于新注册会员，不能删除。
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>

