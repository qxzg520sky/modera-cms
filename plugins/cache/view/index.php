<?php $pageTitle = $pageTitle ?? '缓存优化'; include __DIR__ . '/../../../app/view/admin/_header.php'; ?>
<style>
.cs-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:18px}
.cs-stat{background:#fff;border-radius:8px;padding:16px 18px;box-shadow:0 1px 2px rgba(16,24,40,.06)}
.cs-stat .n{font-size:24px;font-weight:700;color:#111827}
.cs-stat .l{font-size:12px;color:#6b7280;margin-top:4px}
.cs-on{color:#047857}
.cs-off{color:#9ca3af;font-size:20px;line-height:28px}
.cs-switch{display:flex;align-items:center;gap:10px;padding:12px 0;border-bottom:1px dashed #eee}
.cs-switch:last-of-type{border-bottom:0}
.cs-switch .t{font-size:13px;font-weight:600;color:#111827}
.cs-switch .d{font-size:12px;color:#6b7280;margin-top:2px;line-height:1.7}
.cs-switch .r{margin-left:auto;display:flex;align-items:center;gap:6px}
.cs-switch input[type=checkbox]{width:16px;height:16px}
.cs-hint{font-size:12px;color:#6b7280;line-height:1.9}
.cs-hint code{background:#f3f4f6;padding:1px 5px;border-radius:4px}
.cs-tag{display:inline-block;padding:2px 8px;border-radius:99px;font-size:12px}
.cs-tag.ok{background:#ecfdf5;color:#047857}
.cs-tag.no{background:#fff7ed;color:#b45309}
.cs-warn{background:#fffbeb;border-left:4px solid #f59e0b;padding:12px 16px;border-radius:6px;font-size:13px;color:#92400e;line-height:1.8;margin-bottom:16px}
.cs-mono{font-family:ui-monospace,Menlo,Consolas,monospace;font-size:12px;color:#374151}
button.btn,label.cs-hint{white-space:nowrap}
</style>

<div class="cs-stats">
  <div class="cs-stat"><div class="n"><?= (int) $stats['count'] ?></div><div class="l">页面缓存文件</div></div>
  <div class="cs-stat"><div class="n"><?= e(\plugins\cache\controller\Admin::humanSize((int) $stats['size'])) ?></div><div class="l">缓存占用</div></div>
  <div class="cs-stat"><div class="n <?= (string) $cfg['home'] === '1' ? 'cs-on' : 'cs-off' ?>"><?= (string) $cfg['home'] === '1' ? '开' : '关' ?></div><div class="l">首页静态缓存</div></div>
  <div class="cs-stat"><div class="n <?= (string) $cfg['article'] === '1' ? 'cs-on' : 'cs-off' ?>"><?= (string) $cfg['article'] === '1' ? '开' : '关' ?></div><div class="l">文章页静态缓存</div></div>
  <div class="cs-stat"><div class="n <?= (string) $cfg['assets'] === '1' ? 'cs-on' : 'cs-off' ?>"><?= (string) $cfg['assets'] === '1' ? '开' : '关' ?></div><div class="l">静态资源过期头</div></div>
</div>

<form method="post" action="/admin/plugin/cache/save">
<div class="card">
  <div class="card-head"><span>加速开关</span><span class="cs-hint">保存后旧缓存会自动清空</span></div>
  <div class="card-body">

    <div class="cs-switch">
      <div>
        <div class="t">首页 HTML 静态缓存</div>
        <div class="d">访客访问首页时直接输出缓存文件，不查数据库、不渲染模板；过期时间到了自动重生成。</div>
      </div>
      <div class="r">
        <input type="hidden" name="home" value="0">
        <input type="checkbox" name="home" value="1" id="csHome" <?= (string) $cfg['home'] === '1' ? 'checked' : '' ?>>
        <label for="csHome" class="cs-hint">启用</label>
      </div>
    </div>

    <div class="cs-switch">
      <div>
        <div class="t">文章页 HTML 静态缓存</div>
        <div class="d"><code>/a/12</code> 这类详情页缓存；命中时不会累计浏览量（要精确统计浏览数就别开）。</div>
      </div>
      <div class="r">
        <input type="hidden" name="article" value="0">
        <input type="checkbox" name="article" value="1" id="csArticle" <?= (string) $cfg['article'] === '1' ? 'checked' : '' ?>>
        <label for="csArticle" class="cs-hint">启用</label>
      </div>
    </div>

    <div class="cs-switch">
      <div>
        <div class="t">静态资源过期头（浏览器长缓存）</div>
        <div class="d">给 <code>/uploads</code> 与 <code>/static</code> 下的图片、字体、脚本加上 <code>Cache-Control: immutable</code> 与远期 Expires，老访客二次打开不再重复下载。</div>
      </div>
      <div class="r">
        <input type="hidden" name="assets" value="0">
        <input type="checkbox" name="assets" value="1" id="csAssets" <?= (string) $cfg['assets'] === '1' ? 'checked' : '' ?>>
        <label for="csAssets" class="cs-hint">启用</label>
      </div>
    </div>

    <div class="form-row" style="margin-top:16px">
      <div class="form-item">
        <label>页面缓存有效期（分钟）</label>
        <input type="number" name="ttl" min="1" max="1440" value="<?= (int) $cfg['ttl'] ?>">
        <div class="cs-hint" style="margin-top:6px">建议 15–60 分钟；内容更新后保存文章会自动清缓存，所以不必设太短。</div>
      </div>
      <div class="form-item">
        <label>静态资源缓存天数</label>
        <input type="number" name="asset_days" min="1" max="3650" value="<?= (int) $cfg['asset_days'] ?>">
        <div class="cs-hint" style="margin-top:6px">图片是带时间戳的新文件名，一般 365 天足够；改完主题 CSS 记得让访客强刷（CSS 走 PHP 路由，不受这里影响）。</div>
      </div>
    </div>

    <div style="display:flex;gap:10px;align-items:center;margin-top:6px">
      <button class="btn btn-primary" type="submit">保存设置</button>
      <span class="cs-hint">当前主题：<?= e($theme ?: 'default') ?>；缓存目录：<span class="cs-mono"><?= e($dir) ?></span></span>
    </div>
  </div>
</div>
</form>

<div class="card">
  <div class="card-head">
    <span>清理缓存</span>
    <span class="cs-tag <?= $hasBlock ? 'ok' : 'no' ?>">过期头配置：<?= $hasBlock ? '已写入 public/.htaccess' : '未写入' ?></span>
  </div>
  <div class="card-body">
    <div class="cs-hint" style="margin-bottom:14px">
      修改文章、栏目、单页、站点设置或切换主题时，后台会自动清空页面缓存，正常情况下不用手动点。<br>
      需要立刻看到最新页面时（比如刚改完模板、插件、轮播广告），点下面按钮即可全站重生成。
    </div>
    <form method="post" action="/admin/plugin/cache/clear" onsubmit="return confirm('清空全部页面缓存？清完后第一位访客会触发重新生成。');">
      <button class="btn btn-primary" type="submit">立即清理全部缓存（<?= (int) $stats['count'] ?> 个文件 / <?= e(\plugins\cache\controller\Admin::humanSize((int) $stats['size'])) ?>）</button>
    </form>
    <?php if (!$htWritable): ?>
      <div class="cs-warn" style="margin:14px 0 0">public/.htaccess 不可写，过期头可能写不进去，请检查文件权限。</div>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <div class="card-head"><span>缓存条目（最近 <?= count($entries) ?> 条）</span></div>
  <?php if (!$entries): ?>
    <div class="card-body"><div class="muted">还没有缓存。开启对应开关后访问首页或文章页就会生成。</div></div>
  <?php else: ?>
  <div style="overflow-x:auto">
  <table class="table">
    <thead><tr><th>页面路径</th><th style="width:100px">类型</th><th style="width:120px">大小</th><th style="width:170px">生成时间</th></tr></thead>
    <tbody>
    <?php foreach ($entries as $e): ?>
      <tr>
        <td class="cs-mono"><?= e($e['path'] ?: '—') ?></td>
        <td><span class="cs-tag <?= $e['type'] === 'home' ? 'ok' : 'no' ?>"><?= $e['type'] === 'home' ? '首页' : ($e['type'] === 'article' ? '文章页' : '页面') ?></span></td>
        <td><?= e(\plugins\cache\controller\Admin::humanSize((int) $e['size'])) ?></td>
        <td class="muted"><?= date('Y-m-d H:i:s', (int) $e['time']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-head"><span>说明</span></div>
  <div class="card-body">
    <div class="cs-hint">
      · 命中缓存时响应头会带 <code>X-Page-Cache: HIT</code>，未命中是 <code>MISS</code>，可以拿来验证。<br>
      · 带查询参数的地址一律不缓存（避免 <code>?v=xxx</code> 把旧页面钉住）；搜索结果、栏目列表页不在缓存范围。<br>
      · 文章页缓存命中时不会执行 <code>views +1</code>，浏览量统计会偏少。<br>
      · 停用插件后中间件立即不生效、页面缓存文件自动清空、<code>.htaccess</code> 里的过期头区块会被摘掉，站点回到原生行为。
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../../app/view/admin/_footer.php'; ?>
