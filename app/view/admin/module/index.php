<?php $pageTitle = '模块中心'; include __DIR__ . '/../_header.php'; ?>

<div class="card">
  <div class="card-head">
    <span>已安装模块（磁盘 themes/ 与 plugins/）</span>
    <span class="muted" style="font-weight:400;font-size:12px">默认模板与演示插件为内置模块，不可卸载</span>
  </div>
  <div class="card-body">
    <?php if (empty($records)): ?>
      <div class="empty">还没有安装任何模块</div>
    <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px">
        <?php foreach ($records as $m): ?>
          <div style="border:1px solid #e5e7eb;border-radius:10px;padding:16px;background:#fff">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
              <strong><?= e($m['title']) ?></strong>
              <span class="badge <?= $m['enabled'] ? 'badge-ok' : 'badge-gray' ?>"><?= $m['enabled'] ? '已启用' : '未启用' ?></span>
            </div>
            <div class="muted" style="font-size:12px;margin:6px 0">
              <?= $m['type'] === 'theme' ? '模板' : '插件' ?> · <?= e($m['name']) ?> · v<?= e($m['version']) ?><?= $m['author'] ? ' · ' . e($m['author']) : '' ?>
              <?php if ($m['core']): ?> · <span style="color:#2563eb">内置</span><?php endif; ?>
            </div>
            <div style="min-height:46px;color:#4b5563;font-size:13px;line-height:1.7"><?= e(mb_substr($m['description'], 0, 120)) ?><?= mb_strlen($m['description']) > 120 ? '…' : '' ?></div>
            <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
              <?php if ($m['type'] === 'theme'): ?>
                <?php if (!$m['enabled']): ?>
                  <form method="post" action="/admin/theme/enable" style="display:inline">
                    <input type="hidden" name="name" value="<?= e($m['name']) ?>">
                    <button class="btn btn-sm btn-primary" type="submit">启用</button>
                  </form>
                <?php endif; ?>
                <a class="btn btn-sm" href="/admin/theme">模板列表</a>
              <?php else: ?>
                <form method="post" action="/admin/plugin/toggle" style="display:inline">
                  <input type="hidden" name="name" value="<?= e($m['name']) ?>">
                  <input type="hidden" name="enabled" value="<?= $m['enabled'] ? 0 : 1 ?>">
                  <button class="btn btn-sm <?= $m['enabled'] ? '' : 'btn-primary' ?>" type="submit"><?= $m['enabled'] ? '停用' : '启用' ?></button>
                </form>
                <a class="btn btn-sm" href="/admin/plugin">插件列表</a>
                <?php if (!empty($m['name'])): ?>
                  <a class="btn btn-sm" href="/admin/plugin/config/<?= e($m['name']) ?>">设置</a>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <div class="card-head">
    <span>模块仓库（<?= e($storeDir) ?>）</span>
    <span class="muted" style="font-weight:400;font-size:12px">安装 = 解压到 themes/ 或 plugins/ 并注册</span>
  </div>
  <div class="card-body">
    <?php if (empty($packages)): ?>
      <div class="empty">仓库里还没有模块包。把 zip 放进 modules/store/，或从下方上传。</div>
    <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px">
        <?php foreach ($packages as $p): ?>
          <div style="border:1px solid #e5e7eb;border-radius:10px;padding:16px;background:#fff<?= !empty($p['error']) ? ';border-color:#fecaca' : '' ?>">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
              <strong><?= e($p['title']) ?></strong>
              <?php if (!empty($p['error'])): ?>
                <span class="badge badge-gray">无效包</span>
              <?php elseif ($p['installed']): ?>
                <span class="badge badge-ok">已安装<?= $p['enabled'] ? ' · 启用中' : '' ?></span>
              <?php else: ?>
                <span class="badge badge-gray">未安装</span>
              <?php endif; ?>
            </div>
            <div class="muted" style="font-size:12px;margin:6px 0">
              <?= $p['type'] === 'theme' ? '模板' : ($p['type'] === 'plugin' ? '插件' : '未知') ?> · <?= e($p['file']) ?>
              <?= $p['version'] !== '' ? ' · v' . e($p['version']) : '' ?>
              <?= $p['size'] ? ' · ' . number_format($p['size'] / 1024, 1) . ' KB' : '' ?>
            </div>
            <div style="min-height:46px;color:#4b5563;font-size:13px;line-height:1.7"><?= e(mb_substr((string) $p['description'], 0, 120)) ?><?= mb_strlen((string) $p['description']) > 120 ? '…' : '' ?></div>
            <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
              <?php if (empty($p['error']) && !$p['installed']): ?>
                <form method="post" action="/admin/module/install" style="display:inline">
                  <input type="hidden" name="file" value="<?= e($p['file']) ?>">
                  <input type="hidden" name="enable" value="0">
                  <button class="btn btn-sm btn-primary" type="submit">安装</button>
                </form>
                <form method="post" action="/admin/module/install" style="display:inline">
                  <input type="hidden" name="file" value="<?= e($p['file']) ?>">
                  <input type="hidden" name="enable" value="1">
                  <button class="btn btn-sm" type="submit">安装并启用</button>
                </form>
              <?php endif; ?>
              <?php if (empty($p['error']) && $p['installed'] && !$p['enabled']): ?>
                <span class="muted" style="font-size:12px;line-height:28px">已安装，去<?= $p['type'] === 'theme' ? '模板主题' : '插件扩展' ?>启用</span>
              <?php endif; ?>
              <?php if (empty($p['error']) && !$p['installed']): ?>
                <form method="post" action="/admin/module/remove" style="display:inline" onsubmit="return confirm('只删除模块包（zip），已安装的模块不会受影响。继续？');">
                  <input type="hidden" name="file" value="<?= e($p['file']) ?>">
                  <button class="btn btn-sm" type="submit">删除包</button>
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
  <div class="card-head"><span>上传模块包 / 模块规范</span></div>
  <div class="card-body">
    <form method="post" action="/admin/module/upload" enctype="multipart/form-data" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:16px">
      <input type="file" name="package" accept=".zip" required style="font-size:13px">
      <button class="btn btn-primary btn-sm" type="submit">上传并安装</button>
      <span class="muted" style="font-size:12px">zip 内层目录名 = 模块名，目录里放 theme.json 或 plugin.json</span>
    </form>
    <div style="font-size:13px;color:#374151;line-height:1.9">
      <div>· 主题包：<code>包名/theme.json</code> + 模板文件 + <code>screenshot.png</code>，解压到 <code>themes/包名/</code></div>
      <div>· 插件包：<code>包名/plugin.json</code> + 控制器/模板/<code>install.php</code>，解压到 <code>plugins/包名/</code></div>
      <div>· 插件安装时会自动执行 <code>install.php</code>（建表、建字段），启用后前台与后台菜单才生效。</div>
      <div>· 首次安装只带 <code>default</code> 模板与 <code>example</code> 演示插件，其余模块一律从本页安装。</div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../_footer.php'; ?>
