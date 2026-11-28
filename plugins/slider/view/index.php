<?php $pageTitle = $pageTitle ?? '轮播广告'; include __DIR__ . '/../../../app/view/admin/_header.php'; ?>
<style>
.sl-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:18px}
.sl-stat{background:#fff;border-radius:8px;padding:16px 18px;box-shadow:0 1px 2px rgba(16,24,40,.06)}
.sl-stat .n{font-size:24px;font-weight:700;color:#111827}
.sl-stat .l{font-size:12px;color:#6b7280;margin-top:4px}
.sl-thumb{width:150px;height:84px;border-radius:6px;object-fit:cover;background:#f3f4f6;display:block}
.sl-thumb-empty{width:150px;height:84px;border-radius:6px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:12px}
.sl-title{font-weight:600;color:#111827;line-height:1.5;max-width:320px}
.sl-sub{font-size:12px;color:#6b7280;margin-top:2px}
.sl-tag{display:inline-block;padding:2px 8px;border-radius:99px;font-size:12px;white-space:nowrap}
.sl-tag.on{background:#ecfdf5;color:#047857}
.sl-tag.off{background:#f3f4f6;color:#6b7280}
.sl-link{font-family:ui-monospace,Menlo,Consolas,monospace;font-size:12px;color:#374151;word-break:break-all;max-width:220px;display:block}
.sl-move{display:inline-flex;flex-direction:column;gap:2px}
.sl-move button{width:26px;height:20px;line-height:18px;padding:0;font-size:11px}
.sl-bar{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.sl-note{font-size:12px;color:#6b7280;line-height:1.9}
.sl-note code{background:#f3f4f6;padding:1px 5px;border-radius:4px}
.sl-table-wrap{overflow-x:auto}
.sl-table-wrap .table{min-width:940px}
</style>

<div class="sl-stats">
  <div class="sl-stat"><div class="n"><?= count($slides) ?></div><div class="l">广告总数</div></div>
  <div class="sl-stat"><div class="n" style="color:#047857"><?= (int) $enabled ?></div><div class="l">启用中（前台显示）</div></div>
  <div class="sl-stat"><div class="n" style="font-size:16px;line-height:34px"><?= e($theme ?: 'default') ?></div><div class="l">当前启用主题</div></div>
  <div class="sl-stat"><div class="n" style="font-size:16px;line-height:34px">已启用</div><div class="l">插件状态（停用即回模板自带轮播）</div></div>
</div>

<?php if ($theme === 'default'): ?>
<div class="card" style="border-left:4px solid #f59e0b">
  <div class="card-body" style="font-size:13px;color:#92400e;line-height:1.8">
    当前用的是「默认企业主题」，它的首页是纯色文字横幅、没有大图轮播位，所以这套广告不会显示。<br>
    把主题切换到「钢铁风企业主题」或「工业橙企业主题」（后台 → 模板主题），广告立刻出现在首页首屏。
  </div>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-head">
    <span>广告列表</span>
    <span class="sl-bar">
      <a class="btn btn-sm" href="/" target="_blank">前台预览 ↗</a>
      <a class="btn btn-primary btn-sm" href="/admin/plugin/slider/form">+ 新增广告</a>
    </span>
  </div>
  <div class="card-body" style="padding-bottom:8px">
    <div class="sl-note">
      启用插件时，首页首屏轮播就用这里的图片和文案；<strong>停用插件</strong>后自动回到模板自带的那套轮播（按栏目文章封面自动生成），不需要改代码。<br>
      图片建议 <code>1920×1080</code> 左右横图（手机上会被裁切为竖屏，主体尽量居中）；单张不超过 5MB。链接填 <code>/a/12</code> 这种站内路径会自动带语言前缀，外链直接写 <code>https://</code> 开头。<br>
      前台最多显示 <?= \plugins\slider\service\Slider::MAX ?> 条，按「排序」从小到大依次播放，单条停用只隐藏这一条。
    </div>
  </div>

  <?php if (!$slides): ?>
    <div class="card-body"><div class="muted">还没有广告。点右上角「新增广告」上传第一张，或者直接把插件停用，前台会回到模板自带轮播。</div></div>
  <?php else: ?>
  <div class="sl-table-wrap">
  <table class="table">
    <thead>
      <tr>
        <th style="width:60px">排序</th>
        <th style="width:170px">图片</th>
        <th>标题 / 小标题</th>
        <th style="width:220px">链接</th>
        <th style="width:110px">状态</th>
        <th style="width:190px">操作</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($slides as $i => $s): ?>
      <tr>
        <td>
          <div style="font-weight:600;margin-bottom:3px"><?= (int) $s['sort'] ?></div>
          <div class="sl-move">
            <form method="post" action="/admin/plugin/slider/move">
              <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
              <input type="hidden" name="dir" value="up">
              <button class="btn btn-sm" type="submit" title="上移" <?= $i === 0 ? 'disabled' : '' ?>>↑</button>
            </form>
            <form method="post" action="/admin/plugin/slider/move">
              <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
              <input type="hidden" name="dir" value="down">
              <button class="btn btn-sm" type="submit" title="下移" <?= $i === count($slides) - 1 ? 'disabled' : '' ?>>↓</button>
            </form>
          </div>
        </td>
        <td>
          <?php if ($s['image'] !== ''): ?>
            <img class="sl-thumb" src="<?= e($s['image']) ?>" alt="<?= e($s['title']) ?>">
          <?php else: ?>
            <div class="sl-thumb-empty">没有图片</div>
          <?php endif; ?>
        </td>
        <td>
          <div class="sl-title"><?= e($s['title']) ?></div>
          <?php if ($s['eyebrow'] !== ''): ?><div class="sl-sub">小标题：<?= e($s['eyebrow']) ?></div><?php endif; ?>
          <?php if ($s['summary'] !== ''): ?><div class="sl-sub"><?= e(mb_substr($s['summary'], 0, 40)) ?></div><?php endif; ?>
        </td>
        <td>
          <?php if ($s['raw_link'] !== ''): ?>
            <span class="sl-link"><?= e($s['raw_link']) ?></span>
            <div class="sl-sub">按钮：<?= e($s['link_text'] ?: '查看详情') ?><?= $s['target'] === '_blank' ? ' · 新窗口' : '' ?></div>
          <?php else: ?>
            <span class="muted">无链接</span>
          <?php endif; ?>
          <?php if ($s['raw_link2'] !== ''): ?>
            <span class="sl-link" style="margin-top:4px"><?= e($s['raw_link2']) ?></span>
          <?php endif; ?>
        </td>
        <td>
          <span class="sl-tag <?= $s['status'] === 1 ? 'on' : 'off' ?>"><?= $s['status'] === 1 ? '显示中' : '已停用' ?></span>
          <form method="post" action="/admin/plugin/slider/toggle" style="margin-top:6px">
            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
            <button class="btn btn-sm" type="submit"><?= $s['status'] === 1 ? '停用' : '启用' ?></button>
          </form>
        </td>
        <td class="actions">
          <a class="btn btn-sm" href="/admin/plugin/slider/form/<?= (int) $s['id'] ?>">编辑</a>
          <form method="post" action="/admin/plugin/slider/delete" style="display:inline"
                onsubmit="return confirm('删除这条广告？删除后前台不再显示。');">
            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
            <button class="btn btn-sm btn-danger" type="submit">删除</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../app/view/admin/_footer.php'; ?>
