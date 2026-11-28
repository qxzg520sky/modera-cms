<?php $pageTitle = $pageTitle ?? ($isNew ? '新增业务员' : '编辑业务员'); include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/member/asset/admin.css?v=1.0.0">
<?php $val = function (string $k, $d = '') use ($s) { return $s[$k] ?? $d; }; ?>

<div class="card">
  <div class="card-head">
    <span><?= $isNew ? '新增业务员' : '编辑业务员 · ' . e((string) $val('username')) ?></span>
    <a class="btn btn-sm" href="/admin/plugin/member/staff">← 返回列表</a>
  </div>
  <form method="post" action="/admin/plugin/member/staff/save" style="padding:18px">
    <input type="hidden" name="id" value="<?= (int) $val('id', 0) ?>">
    <div class="mb-admin-form">
      <div><label>登录账号 <span style="color:#dc2626">*</span></label><input name="username" value="<?= e((string) $val('username')) ?>" required placeholder="3-50 位字母、数字、下划线"><div class="hint">用于 /member/staff/login 登录</div></div>
      <div><label>姓名 <span style="color:#dc2626">*</span></label><input name="real_name" value="<?= e((string) $val('real_name')) ?>" required></div>
      <div><label>手机号</label><input name="mobile" value="<?= e((string) $val('mobile')) ?>"></div>
      <div><label>状态</label><select name="status"><option value="1" <?= (int) $val('status', 1) === 1 ? 'selected' : '' ?>>启用</option><option value="0" <?= (int) $val('status', 1) === 0 ? 'selected' : '' ?>>停用</option></select></div>
      <div class="full"><label>登录密码</label><input name="password" type="password" placeholder="<?= $isNew ? '至少 6 位' : '留空表示不修改' ?>"><?php if ($isNew): ?><div class="hint">新增业务员必须设置密码</div><?php endif; ?></div>
      <div class="full"><label>备注</label><textarea name="remark" placeholder="负责区域、渠道等"><?= e((string) $val('remark')) ?></textarea></div>
    </div>
    <div style="margin-top:18px;display:flex;gap:10px">
      <button class="btn btn-primary" type="submit"><?= $isNew ? '创建业务员' : '保存修改' ?></button>
      <a class="btn" href="/admin/plugin/member/staff">取消</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>

