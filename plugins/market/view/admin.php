<?php
$pageTitle = '应用市场';
$flash = $msg ?? '';
$marketItems = $items;
include __DIR__ . '/../../../app/view/admin/_header.php';
$marketLabels = ['theme' => '模板主题', 'plugin' => '插件扩展'];
$marketGroups = ['theme' => [], 'plugin' => []];
foreach ($marketItems as $marketItem) {
    $marketGroups[$marketItem['type']][] = $marketItem;
}
?>

<div class="card">
  <div class="card-head">
    <span>Modera 应用市场</span>
    <span class="muted" style="font-weight:400;font-size:12px">按需下载安装，不随主程序打包</span>
  </div>
  <div class="card-body" style="font-size:13px;color:#374151;line-height:1.9">
    <div>主程序默认只带 <b>default</b> 模板和 <b>example</b> 插件。其余插件和模板都放在独立的下载分支里，需要哪个装哪个。</div>
    <div class="muted" style="font-size:12px;margin-top:8px">
      目录：<?= e((string) ($catalog['source'] ?? '—')) ?>
      <?php if (!empty($catalog['updated_at'])): ?> · 更新：<?= e((string) $catalog['updated_at']) ?><?php endif; ?>
      · 共 <?= count($marketItems) ?> 个模块
    </div>
    <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap">
      <a class="btn btn-sm" href="/admin/module">返回模块中心</a>
      <a class="btn btn-sm" href="/admin/plugin/market/refresh">刷新目录</a>
      <a class="btn btn-sm" href="/admin/plugin/config/market">市场设置</a>
    </div>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-err"><?= e($error) ?></div>
<?php endif; ?>

<?php foreach ($marketLabels as $type => $label): ?>
  <div class="card">
    <div class="card-head">
      <span><?= e($label) ?>（<?= count($marketGroups[$type]) ?>）</span>
      <span class="muted" style="font-weight:400;font-size:12px">下载后由应用市场自动解压安装</span>
    </div>
    <div class="card-body">
      <?php if (!$marketGroups[$type]): ?>
        <div class="empty">目录里暂时没有<?= e($label) ?></div>
      <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px">
          <?php foreach ($marketGroups[$type] as $offer): ?>
            <div style="border:1px solid #e5e7eb;border-radius:10px;padding:16px;background:#fff">
              <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
                <strong><?= e((string) $offer['title']) ?></strong>
                <?php if (!empty($offer['core'])): ?>
                  <span class="badge badge-gray">内置</span>
                <?php elseif (!empty($offer['enabled'])): ?>
                  <span class="badge badge-ok">已启用</span>
                <?php elseif (!empty($offer['installed'])): ?>
                  <span class="badge badge-ok">已安装</span>
                <?php else: ?>
                  <span class="badge badge-gray">未安装</span>
                <?php endif; ?>
              </div>
              <div class="muted" style="font-size:12px;margin:6px 0">
                <?= e((string) $offer['name']) ?>
                <?php if (!empty($offer['version'])): ?> · v<?= e((string) $offer['version']) ?><?php endif; ?>
                <?php if (!empty($offer['author'])): ?> · <?= e((string) $offer['author']) ?><?php endif; ?>
                <?php if (!empty($offer['size'])): ?> · <?= number_format(((int) $offer['size']) / 1024, 1) ?> KB<?php endif; ?>
              </div>
              <div style="min-height:46px;color:#4b5563;font-size:13px;line-height:1.7">
                <?= e(mb_substr((string) $offer['description'], 0, 120)) ?><?= mb_strlen((string) $offer['description']) > 120 ? '…' : '' ?>
              </div>
              <?php if (!empty($offer['requires'])): ?>
                <div class="muted" style="font-size:12px;margin-top:8px">依赖：<?= e(implode('、', (array) $offer['requires'])) ?></div>
              <?php endif; ?>
              <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
                <?php if (empty($offer['core']) && empty($offer['installed'])): ?>
                  <form method="post" action="/admin/plugin/market/install" style="display:inline">
                    <input type="hidden" name="type" value="<?= e((string) $offer['type']) ?>">
                    <input type="hidden" name="name" value="<?= e((string) $offer['name']) ?>">
                    <input type="hidden" name="enable" value="0">
                    <button class="btn btn-sm btn-primary" type="submit">下载并安装</button>
                  </form>
                  <?php if ($offer['type'] === 'plugin'): ?>
                    <form method="post" action="/admin/plugin/market/install" style="display:inline">
                      <input type="hidden" name="type" value="<?= e((string) $offer['type']) ?>">
                      <input type="hidden" name="name" value="<?= e((string) $offer['name']) ?>">
                      <input type="hidden" name="enable" value="1">
                      <button class="btn btn-sm" type="submit">安装并启用</button>
                    </form>
                  <?php endif; ?>
                <?php elseif (!empty($offer['installed']) && empty($offer['enabled'])): ?>
                  <span class="muted" style="font-size:12px;line-height:28px">去<?= $offer['type'] === 'theme' ? '模板主题' : '插件扩展' ?>启用</span>
                  <a class="btn btn-sm" href="<?= $offer['type'] === 'theme' ? '/admin/theme' : '/admin/plugin' ?>">去启用</a>
                <?php endif; ?>
                <a class="btn btn-sm" href="<?= e((string) $offer['url']) ?>" target="_blank" rel="noopener">单独下载</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>

<div class="card">
  <div class="card-head"><span>使用说明</span></div>
  <div class="card-body" style="font-size:13px;color:#374151;line-height:1.9">
    <div>· 应用市场是独立插件。主程序默认不带它，需要先到模块中心上传 <code>plugin-market.zip</code> 安装。</div>
    <div>· 安装其他插件时，若有“依赖”，请先安装依赖插件再启用，例如商城依赖会员插件。</div>
    <div>· 模板安装后不会自动切换；确认要启用时，请到“模板主题”里操作。</div>
    <div>· 下载地址和目录地址可在“市场设置”里更换，方便部署自己的私有模块源。</div>
  </div>
</div>

<?php include __DIR__ . '/../../../app/view/admin/_footer.php'; ?>
