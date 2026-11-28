<?php
$pageTitle = $pageTitle ?? '访问统计';
include __DIR__ . '/../../../app/view/admin/_header.php';

$d = $data['data'];
$pct = function (int $cur, int $prev): array {
    if ($prev <= 0) {
        return [$cur > 0 ? 100 : 0, $cur > 0 ? 'up' : 'flat'];
    }
    $v = (int) round(($cur - $prev) / $prev * 100);
    return [$v, $v > 0 ? 'up' : ($v < 0 ? 'down' : 'flat')];
};
[$todayPvPct, $todayPvDir] = $pct((int) $d['todayPv'], (int) $d['yPv']);
[$todayUvPct, $todayUvDir] = $pct((int) $d['todayUv'], (int) $d['yUv']);
[$rangePvPct, $rangePvDir] = $pct((int) $d['rangePv'], (int) $d['prevPv']);
[$rangeUvPct, $rangeUvDir] = $pct((int) $d['rangeUv'], (int) $d['prevUv']);
$days = (int) $d['days'];
$maxPv = max(1, (int) $d['maxPv']);
$maxUv = max(1, (int) $d['maxUv']);
$typeLabel = ['home' => '首页', 'article' => '文章', 'category' => '栏目', 'page' => '单页', 'search' => '搜索', 'other' => '其他'];
?>
<style>
.st-top{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:16px}
.st-top h2{font-size:18px;font-weight:700;color:#111827;margin:0}
.st-top .muted{font-size:12px}
.st-tabs{margin-left:auto;display:flex;gap:6px}
.st-tabs a{padding:6px 12px;border-radius:6px;font-size:13px;background:#fff;border:1px solid #e5e7eb;color:#374151;text-decoration:none}
.st-tabs a.on{background:#2563eb;border-color:#2563eb;color:#fff}
.st-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:18px}
.st-card{background:#fff;border-radius:10px;padding:16px 18px;box-shadow:0 1px 3px rgba(16,24,40,.07);border:1px solid #f1f5f9}
.st-card .l{font-size:12px;color:#6b7280;display:flex;align-items:center;gap:6px}
.st-card .n{font-size:28px;font-weight:800;color:#111827;line-height:1.25;margin-top:6px;font-variant-numeric:tabular-nums}
.st-card .n small{font-size:12px;font-weight:500;color:#9ca3af;margin-left:4px}
.st-card .c{font-size:12px;margin-top:4px;color:#9ca3af}
.st-card .c b{font-weight:600}
.st-card .c.up b{color:#059669}.st-card .c.down b{color:#dc2626}
.st-card .ico{width:26px;height:26px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;font-size:14px;background:#eff6ff}
.st-chart-card{background:#fff;border-radius:10px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(16,24,40,.07);margin-bottom:18px}
.st-chart-head{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f3f4f6}
.st-chart-head .t{font-size:14px;font-weight:700;color:#111827}
.st-legend{display:flex;gap:14px;font-size:12px;color:#6b7280}
.st-legend i{display:inline-block;width:8px;height:8px;border-radius:2px;margin-right:5px}
.st-legend .pv i{background:#3b82f6}.st-legend .uv i{background:#10b981}
.st-chart-scroll{overflow-x:auto;padding:16px 18px 10px}
.st-chart{display:flex;align-items:flex-end;gap:4px;min-width:<?= max(420, $days * 26) ?>px;height:170px;border-bottom:1px solid #eef2f7;padding-bottom:0}
.st-col{flex:1;min-width:18px;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end}
.st-bars{display:flex;align-items:flex-end;gap:2px;width:100%;height:calc(100% - 22px);justify-content:center}
.st-bar{width:42%;max-width:10px;border-radius:3px 3px 0 0;min-height:2px;transition:height .2s}
.st-bar.pv{background:linear-gradient(180deg,#60a5fa,#3b82f6)}
.st-bar.uv{background:linear-gradient(180deg,#34d399,#10b981)}
.st-col .lbl{font-size:10px;color:#9ca3af;height:18px;line-height:18px;white-space:nowrap;transform:rotate(0)}
.st-col:hover .st-bars{filter:brightness(1.05)}
.st-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:18px;margin-bottom:18px}
.st-panel{background:#fff;border-radius:10px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(16,24,40,.07);overflow:hidden}
.st-panel-head{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f3f4f6}
.st-panel-head .t{font-size:14px;font-weight:700;color:#111827}
.st-panel-head .s{font-size:12px;color:#9ca3af}
.st-panel-body{padding:0 0 4px}
.st-table{width:100%;border-collapse:collapse;font-size:13px}
.st-table th{text-align:left;font-size:12px;font-weight:600;color:#6b7280;padding:10px 16px;background:#fafbfc;border-bottom:1px solid #f1f5f9;white-space:nowrap}
.st-table td{padding:10px 16px;border-bottom:1px solid #f8fafc;color:#374151;vertical-align:middle}
.st-table tr:last-child td{border-bottom:0}
.st-table td.num{text-align:right;font-variant-numeric:tabular-nums;color:#111827;font-weight:600;white-space:nowrap}
.st-table th.num{text-align:right}
.st-rank{display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:5px;background:#f3f4f6;color:#6b7280;font-size:11px;font-weight:700;margin-right:8px}
.st-rank.top1{background:#fef3c7;color:#b45309}.st-rank.top2{background:#e0e7ff;color:#3730a3}.st-rank.top3{background:#d1fae5;color:#047857}
.st-tt{display:block;max-width:420px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#111827;text-decoration:none}
.st-tt:hover{color:#2563eb}
.st-url{display:block;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:11px;color:#9ca3af;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:420px}
.st-tag{display:inline-block;padding:1px 7px;border-radius:99px;font-size:11px;background:#f3f4f6;color:#6b7280;white-space:nowrap}
.st-tag.article{background:#eff6ff;color:#1d4ed8}.st-tag.home{background:#ecfdf5;color:#047857}
.st-bar-cell{position:relative;min-width:90px}
.st-bar-bg{height:6px;background:#f1f5f9;border-radius:99px;overflow:hidden}
.st-bar-fg{height:6px;background:#93c5fd;border-radius:99px}
.st-empty{padding:32px 18px;text-align:center;color:#9ca3af;font-size:13px;line-height:1.9}
.st-src{display:flex;align-items:center;gap:8px}
.st-src .dot{width:22px;height:22px;border-radius:6px;background:#f3f4f6;display:inline-flex;align-items:center;justify-content:center;font-size:11px;color:#6b7280}
.st-foot{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:8px}
.st-note{font-size:12px;color:#6b7280;line-height:1.9}
.st-note code{background:#f3f4f6;padding:1px 5px;border-radius:4px}
.st-form{display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end}
.st-field label{display:block;font-size:12px;color:#374151;margin-bottom:6px}
.st-field input[type=number]{width:110px;padding:8px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px}
.st-check{display:flex;align-items:center;gap:7px;font-size:13px;color:#374151;padding-bottom:9px}
.st-check input{width:16px;height:16px}
.st-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}
.st-danger{background:#fff;border:1px solid #fecaca;color:#b91c1c;padding:9px 14px;border-radius:6px;font-size:13px;cursor:pointer}
.st-danger:hover{background:#fef2f2}
@media(max-width:980px){.st-grid,.st-foot{grid-template-columns:1fr}.st-tt,.st-url{max-width:240px}}
</style>

<div class="st-top">
  <h2>📈 访问统计</h2>
  <span class="muted">区间 <?= e($d['start']) ?> ~ <?= e($d['end']) ?>，共 <?= (int) $d['totalRows'] ?> 条聚合记录</span>
  <div class="st-tabs">
    <?php foreach ([7, 30, 90] as $n): ?>
      <a href="/admin/plugin/stats?days=<?= $n ?>" class="<?= $days === $n ? 'on' : '' ?>">近 <?= $n ?> 天</a>
    <?php endforeach; ?>
  </div>
</div>

<div class="st-cards">
  <div class="st-card">
    <div class="l"><span class="ico">👁</span>今日 PV</div>
    <div class="n"><?= (int) $d['todayPv'] ?></div>
    <div class="c <?= $todayPvDir ?>">较昨日 <b><?= $todayPvPct > 0 ? '+' : '' ?><?= $todayPvPct ?>%</b>（昨日 <?= (int) $d['yPv'] ?>）</div>
  </div>
  <div class="st-card">
    <div class="l"><span class="ico" style="background:#ecfdf5">👤</span>今日 UV</div>
    <div class="n"><?= (int) $d['todayUv'] ?></div>
    <div class="c <?= $todayUvDir ?>">较昨日 <b><?= $todayUvPct > 0 ? '+' : '' ?><?= $todayUvPct ?>%</b>（昨日 <?= (int) $d['yUv'] ?>）</div>
  </div>
  <div class="st-card">
    <div class="l"><span class="ico" style="background:#fff7ed">📊</span>区间 PV</div>
    <div class="n"><?= (int) $d['rangePv'] ?><small>次</small></div>
    <div class="c <?= $rangePvDir ?>">上一周期 <b><?= $rangePvPct > 0 ? '+' : '' ?><?= $rangePvPct ?>%</b>（<?= (int) $d['prevPv'] ?>）</div>
  </div>
  <div class="st-card">
    <div class="l"><span class="ico" style="background:#f5f3ff">🧑‍🤝‍🧑</span>区间 UV</div>
    <div class="n"><?= (int) $d['rangeUv'] ?><small>人</small></div>
    <div class="c <?= $rangeUvDir ?>">上一周期 <b><?= $rangeUvPct > 0 ? '+' : '' ?><?= $rangeUvPct ?>%</b>（<?= (int) $d['prevUv'] ?>）</div>
  </div>
</div>

<div class="st-chart-card">
  <div class="st-chart-head">
    <span class="t">每日趋势</span>
    <span class="st-legend"><span class="pv"><i></i>PV</span><span class="uv"><i></i>UV</span></span>
  </div>
  <div class="st-chart-scroll">
    <div class="st-chart">
      <?php foreach ($d['trend'] as $i => $t): ?>
        <?php
          $pvH = (int) $t['pv'] > 0 ? max(3, round((int) $t['pv'] / $maxPv * 100)) : 0;
          $uvH = (int) $t['uv'] > 0 ? max(3, round((int) $t['uv'] / $maxUv * 100)) : 0;
          $showLabel = $days <= 7 || $i % 3 === 0 || $i === $days - 1;
        ?>
        <div class="st-col" title="<?= e($t['day']) ?>：PV <?= (int) $t['pv'] ?> / UV <?= (int) $t['uv'] ?>">
          <div class="st-bars">
            <span class="st-bar pv" style="height:<?= $pvH ?>%"></span>
            <span class="st-bar uv" style="height:<?= $uvH ?>%"></span>
          </div>
          <span class="lbl"><?= $showLabel ? e($t['label']) : '' ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="st-grid">
  <div class="st-panel">
    <div class="st-panel-head"><span class="t">🔥 文章流量 TOP 8</span><span class="s">按 PV 排序 · 标题取数据库最新值</span></div>
    <?php if (!$d['articles']): ?>
      <div class="st-empty">区间内还没有文章访问记录。<br>前台文章页被访问后，这里会自动出现排行。</div>
    <?php else: ?>
      <table class="st-table">
        <thead><tr><th style="width:40px">#</th><th>文章</th><th class="num" style="width:60px">PV</th><th class="num" style="width:60px">UV</th></tr></thead>
        <tbody>
        <?php foreach ($d['articles'] as $i => $a): ?>
          <tr>
            <td><span class="st-rank <?= $i < 3 ? 'top' . ($i + 1) : '' ?>"><?= $i + 1 ?></span></td>
            <td>
              <a class="st-tt" href="<?= e($a['page_key']) ?>" target="_blank" title="<?= e($a['title']) ?>"><?= e($a['title']) ?></a>
              <span class="st-url"><?= e($a['page_key']) ?></span>
            </td>
            <td class="num"><?= (int) $a['pv'] ?></td>
            <td class="num"><?= (int) $a['uv'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="st-panel">
    <div class="st-panel-head"><span class="t">🔗 来路 TOP</span><span class="s">访客首次落地来源</span></div>
    <?php if (!$d['sources']): ?>
      <div class="st-empty">还没有来路数据。<br>从搜索引擎、外链点进来时会记录来源域名。</div>
    <?php else: ?>
      <table class="st-table">
        <thead><tr><th>来源</th><th class="num">访客</th><th class="num">点击</th></tr></thead>
        <tbody>
        <?php foreach ($d['sources'] as $s): ?>
          <tr>
            <td><span class="st-src"><span class="dot"><?= (string) $s['referrer_host'] === '直接访问' ? '⌂' : '↗' ?></span><span class="st-tt" style="max-width:180px" title="<?= e($s['referrer_host']) ?>"><?= e($s['referrer_host'] ?: '直接访问') ?></span></span></td>
            <td class="num"><?= (int) $s['uv'] ?></td>
            <td class="num"><?= (int) $s['pv'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<div class="st-panel" style="margin-bottom:18px">
  <div class="st-panel-head"><span class="t">热门页面 TOP 20</span><span class="s">首页 / 栏目 / 单页 / 搜索都在内</span></div>
  <?php if (!$d['pages']): ?>
    <div class="st-empty">区间内还没有访问。打开前台页面几秒后刷新这里即可看到。</div>
  <?php else: ?>
    <div style="overflow-x:auto">
    <table class="st-table">
      <thead><tr><th style="width:40px">#</th><th>页面</th><th style="width:70px">类型</th><th class="num">PV</th><th class="num">UV</th><th style="width:130px">PV 占比</th><th class="num" style="width:90px">最后访问</th></tr></thead>
      <tbody>
      <?php $topPagePv = max(1, (int) $d['pages'][0]['pv']); foreach ($d['pages'] as $i => $p): ?>
        <tr>
          <td><span class="st-rank <?= $i < 3 ? 'top' . ($i + 1) : '' ?>"><?= $i + 1 ?></span></td>
          <td>
            <a class="st-tt" href="<?= e($p['page_key']) ?>" target="_blank" title="<?= e($p['title']) ?>"><?= e($p['title'] ?: $p['page_key']) ?></a>
            <span class="st-url"><?= e($p['page_key']) ?></span>
          </td>
          <td><span class="st-tag <?= e($p['page_type']) ?>"><?= e($typeLabel[$p['page_type']] ?? '其他') ?></span></td>
          <td class="num"><?= (int) $p['pv'] ?></td>
          <td class="num"><?= (int) $p['uv'] ?></td>
          <td class="st-bar-cell"><div class="st-bar-bg"><div class="st-bar-fg" style="width:<?= max(2, round((int) $p['pv'] / $topPagePv * 100)) ?>%"></div></div></td>
          <td class="num" style="font-weight:400;color:#9ca3af"><?= !empty($p['last_at']) ? e(date('m-d H:i', (int) $p['last_at'])) : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<div class="st-grid">
  <div class="st-panel">
    <div class="st-panel-head"><span class="t">最近访客</span><span class="s">匿名 ID，不存 IP</span></div>
    <?php if (!$d['recent']): ?>
      <div class="st-empty">暂无访客记录。</div>
    <?php else: ?>
      <table class="st-table">
        <thead><tr><th>访客</th><th>落地页</th><th>来路</th><th class="num">点击</th><th class="num">最后活跃</th></tr></thead>
        <tbody>
        <?php foreach ($d['recent'] as $r): ?>
          <tr>
            <td><span class="st-url" style="font-size:12px;color:#374151">#<?= e(substr((string) $r['visitor'], 0, 8)) ?></span></td>
            <td><a class="st-tt" style="font-size:12px;max-width:180px" href="<?= e($r['landing']) ?>" target="_blank" title="<?= e($r['landing']) ?>"><?= e($r['landing']) ?></a></td>
            <td><span class="st-tag"><?= e($r['referrer_host'] ?: '直接访问') ?></span></td>
            <td class="num"><?= (int) $r['hits'] ?></td>
            <td class="num" style="font-weight:400;color:#9ca3af"><?= e(date('m-d H:i', (int) $r['last_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="st-panel">
    <div class="st-panel-head"><span class="t">⚙ 统计设置</span><span class="s">默认保留 <?= (int) $d['retention'] ?> 天</span></div>
    <div style="padding:16px 18px">
      <form method="post" action="/admin/plugin/stats/save">
        <div class="st-form">
          <div class="st-field">
            <label>数据保留天数</label>
            <input type="number" name="retention" min="7" max="365" value="<?= (int) $d['retention'] ?>">
          </div>
          <div class="st-check">
            <input type="checkbox" name="dnt" value="1" id="stDnt" <?= (string) $cfg['dnt'] === '1' ? 'checked' : '' ?>>
            <label for="stDnt" style="margin:0;font-size:13px">尊重 Do Not Track</label>
          </div>
          <button class="btn btn-primary" type="submit">保存</button>
        </div>
      </form>
      <div class="st-note" style="margin-top:14px">
        · 只写三张按天聚合表，不落逐条日志；每次访问最多 3 条 upsert。<br>
        · UV 用第一方 cookie 的匿名 ID 去重，不保存原始 IP 和完整 UA。<br>
        · 页面被缓存插件命中时，beacon 仍会执行，PV 不会漏。
      </div>
      <div class="st-actions">
        <form method="post" action="/admin/plugin/stats/purge" onsubmit="return confirm('清理超过保留期的聚合记录？');">
          <button class="btn" type="submit">清理过期数据</button>
        </form>
        <form method="post" action="/admin/plugin/stats/clear" onsubmit="return confirm('清空全部统计？历史 PV/UV 与访客记录都会删除，且不可恢复。');">
          <button class="st-danger" type="submit">清空全部统计</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="st-note" style="padding:0 2px">
  统计口径：PV = 页面被打开次数（含刷新）；UV = 当天独立匿名访客数（跨天不去重）；后台、安装页、静态资源不计入。
  采集端点为 <code>/stats/collect</code>，停用插件后路由与 beacon 立即消失。
</div>

<?php include __DIR__ . '/../../../app/view/admin/_footer.php'; ?>
