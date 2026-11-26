<?php $pageTitle = $pageTitle ?? '管理员'; include __DIR__ . '/../_header.php'; ?>
<?php $r = $row ?? []; ?>

<form method="post" action="/admin/user/save">
<input type="hidden" name="id" value="<?= (int) ($r['id'] ?? 0) ?>">
<div class="card">
  <div class="card-head"><span>账号信息</span></div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-item"><label>登录账号 <span class="req">*</span></label><input type="text" name="username" value="<?= e($r['username'] ?? '') ?>" required></div>
      <div class="form-item"><label>昵称</label><input type="text" name="nickname" value="<?= e($r['nickname'] ?? '') ?>"></div>
      <div class="form-item">
        <label>状态</label>
        <select name="status">
          <option value="1" <?= (int) ($r['status'] ?? 1) === 1 ? 'selected' : '' ?>>正常</option>
          <option value="0" <?= (int) ($r['status'] ?? 1) === 0 ? 'selected' : '' ?>>禁用</option>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-item">
        <label>密码 <?= empty($r) ? '<span class="req">*</span>' : '' ?></label>
        <input type="password" name="password" placeholder="<?= empty($r) ? '至少 6 位' : '留空表示不修改' ?>">
      </div>
    </div>
  </div>
</div>

<div style="display:flex;gap:10px">
  <button class="btn btn-primary" type="submit">保存</button>
  <a class="btn" href="/admin/user">返回列表</a>
</div>
</form>

<?php include __DIR__ . '/../_footer.php'; ?>
