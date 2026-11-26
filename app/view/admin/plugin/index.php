<?php $pageTitle = '插件扩展'; include __DIR__ . '/../_header.php'; ?>

<div class="card">
  <div class="card-head">
    <span>已发现插件（plugins/ 目录）</span>
    <span class="muted" style="font-weight:400;font-size:12px">把插件文件夹放进 plugins/，后台自动出现</span>
  </div>
  <div class="card-body">
    <?php if (empty($list)): ?>
      <div class="empty">plugins/ 下还没有插件</div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
      <?php foreach ($list as $p): ?>
        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:16px">
          <div style="display:flex;align-items:center;justify-content:space-between">
            <strong><?= e($p['title']) ?></strong>
            <?= (int) $p['enabled'] === 1 ? '<span class="badge badge-ok">已启用</span>' : '<span class="badge badge-gray">未启用</span>' ?>
          </div>
          <div class="muted" style="font-size:12px;margin:6px 0"><?= e($p['name']) ?> · v<?= e($p['version']) ?><?= $p['author'] ? ' · ' . e($p['author']) : '' ?></div>
          <div style="min-height:42px;color:#4b5563;font-size:13px"><?= e($p['description']) ?></div>
          <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
            <form method="post" action="/admin/plugin/toggle" style="display:inline">
              <input type="hidden" name="name" value="<?= e($p['name']) ?>">
              <input type="hidden" name="enabled" value="<?= (int) $p['enabled'] === 1 ? 0 : 1 ?>">
              <button class="btn btn-sm <?= (int) $p['enabled'] === 1 ? '' : 'btn-primary' ?>" type="submit"><?= (int) $p['enabled'] === 1 ? '停用' : '启用' ?></button>
            </form>
            <?php if (!empty($p['meta']['config'])): ?>
              <a class="btn btn-sm" href="/admin/plugin/config/<?= e($p['name']) ?>">设置</a>
            <?php endif; ?>
            <?php if (!empty($p['meta']['admin_url'])): ?>
              <a class="btn btn-sm" href="<?= e($p['meta']['admin_url']) ?>">管理</a>
            <?php endif; ?>
            <?php if ((int) $p['enabled'] === 1 || !empty($p['meta']['uninstall'])): ?>
              <form method="post" action="/admin/plugin/uninstall" style="display:inline"
                onsubmit="return confirm('卸载会执行插件的 uninstall.php，删除它新增的表/字段和配置，且不可恢复。确定卸载「<?= e($p['title']) ?>」？');">
                <input type="hidden" name="name" value="<?= e($p['name']) ?>">
                <button class="btn btn-sm btn-danger" type="submit">卸载</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <div class="card-head"><span>插件开发约定</span></div>
  <div class="card-body" style="font-size:13px;color:#374151;line-height:1.9">
    <div><code>plugins/{插件名}/plugin.json</code> 描述插件：title / version / author / description，可选 <code>config</code>（后台设置项）与 <code>admin_menu</code>（后台侧栏菜单）。</div>
    <div>插件可自带控制器与模板，通过 <code>route.php</code> 注册自己的路由，启用后即生效、停用即摘除，不影响主程序。</div>
    <div>示例插件 <code>plugins/example/</code> 已随系统提供，可作为多语言、商城、支付等插件开发的脚手架。</div>
  </div>
</div>

<?php include __DIR__ . '/../_footer.php'; ?>
