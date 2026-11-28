<?php $pageTitle = $pageTitle ?? '商品分类'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/shop/asset/admin.css?v=1.0.0">
<?php if ($flash): ?><div class="sp-alert ok"><?= e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="sp-alert err"><?= e($error) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr .7fr;gap:16px">
  <div class="card">
    <div class="card-head"><span>分类列表</span></div>
    <table class="table">
      <thead><tr><th>ID</th><th>名称</th><th>别名</th><th>排序</th><th>状态</th><th>操作</th></tr></thead>
      <tbody>
      <?php if (!$list): ?><tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:26px">暂无分类</td></tr><?php endif; ?>
      <?php foreach ($list as $c): ?>
        <tr>
          <td><?= (int) $c['id'] ?></td>
          <td><b><?= e($c['name']) ?></b></td>
          <td><?= e($c['slug'] ?: '-') ?></td>
          <td><?= (int) $c['sort'] ?></td>
          <td><span class="sp-badge <?= (int) $c['status'] === 1 ? 'ok' : '' ?>"><?= (int) $c['status'] === 1 ? '启用' : '停用' ?></span></td>
          <td>
            <form method="post" action="/admin/plugin/shop/category/delete" style="display:inline" onsubmit="return confirm('删除分类？')">
              <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
              <button class="btn btn-sm sp-danger">删除</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <div class="card-head"><span>新增 / 修改分类</span></div>
    <form method="post" action="/admin/plugin/shop/category/save" style="padding:16px 18px">
      <div class="sp-field"><label>分类 ID（新增留空/0）</label><input name="id" type="number" value="0"></div>
      <div class="sp-field"><label>名称 *</label><input name="name" required></div>
      <div class="sp-field"><label>别名</label><input name="slug" placeholder="gift"></div>
      <div class="sp-field"><label>排序</label><input name="sort" type="number" value="0"></div>
      <div class="sp-field"><label>状态</label><select name="status"><option value="1">启用</option><option value="0">停用</option></select></div>
      <div class="sp-actions"><button class="btn">保存分类</button></div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>
