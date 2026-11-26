<?php $pageTitle = '模块中心'; include __DIR__ . '/../_header.php'; ?>

<div class="card">
  <div class="card-head">
    <span>按需安装说明</span>
    <span class="muted" style="font-weight:400;font-size:12px">主程序默认只带 default 模板与 example 插件</span>
  </div>
  <div class="card-body" style="font-size:13px;color:#374151;line-height:1.9">
    <div>为了避免下载主程序时被迫带走全部插件和模板，其余模块已经不放在 <code>modules/store</code> 里，而是在独立的 <code>market</code> 下载分支中按需获取。</div>
    <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
      <?php if (!empty($marketEnabled)): ?>
        <a class="btn btn-sm btn-primary" href="/admin/plugin/market">打开应用市场</a>
        <span class="muted" style="font-size:12px">浏览远程插件和模板，按需下载安装。</span>
      <?php elseif (!empty($marketInstalled)): ?>
        <a class="btn btn-sm btn-primary" href="/admin/plugin">去启用应用市场插件</a>
        <span class="muted" style="font-size:12px">应用市场已安装但未启用。</span>
      <?php else: ?>
        <a class="btn btn-sm btn-primary" href="https://gitee.com/51iqx/modera-cms/raw/market/plugins/plugin-market.zip">下载应用市场插件</a>
        <a class="btn btn-sm" href="https://github.com/qxzg520sky/modera-cms/blob/market/README.md" target="_blank" rel="noopener">查看模块下载说明</a>
        <span class="muted" style="font-size:12px">应用市场是独立插件，默认不安装；上传 zip 安装后即可在后台打开。</span>
      <?php endif; ?>
    </div>
  </div>
</div>

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
                <?php if ($m['core']): ?>
                  <span class="muted" style="font-size:12px;line-height:28px">内置，不可卸载</span>
                <?php elseif ($m['enabled']): ?>
                  <span class="muted" style="font-size:12px;line-height:28px">启用中不可卸载</span>
                <?php else: ?>
                  <form method="post" action="/admin/module/uninstall" style="display:inline" onsubmit="return confirm('确定卸载模板「<?= e($m['title']) ?>」？将删除 themes/<?= e($m['name']) ?> 整个目录，且不可恢复。卸载后可在应用市场重新下载。');">
                    <input type="hidden" name="name" value="<?= e($m['name']) ?>">
                    <button class="btn btn-sm" type="submit" style="color:#b42318;border-color:#f0c9c4">卸载</button>
                  </form>
                <?php endif; ?>
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
                <?php if ($m['core']): ?>
                  <span class="muted" style="font-size:12px;line-height:28px">内置，不可卸载</span>
                <?php else: ?>
                  <form method="post" action="/admin/plugin/uninstall" style="display:inline"
                    onsubmit="return confirm('确定卸载插件「<?= e($m['title']) ?>」？将执行 uninstall.php，并删除 plugins/<?= e($m['name']) ?> 整个目录；卸载后可在应用市场重新下载。不可恢复。');">
                    <input type="hidden" name="name" value="<?= e($m['name']) ?>">
                    <button class="btn btn-sm" type="submit" style="color:#b42318;border-color:#f0c9c4">卸载</button>
                  </form>
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
      <div>· 首次安装只带 <code>default</code> 模板与 <code>example</code> 演示插件。其余插件/模板请从应用市场下载，或上传单独下载的 zip。</div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../_footer.php'; ?>
