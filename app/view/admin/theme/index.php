<?php $pageTitle = '模板主题'; include __DIR__ . '/../_header.php'; ?>

<div class="card">
  <div class="card-head">
    <span>已安装模板（themes/ 目录）</span>
    <span class="muted" style="font-weight:400;font-size:12px">新建主题：复制 themes/default 改个名字即可</span>
  </div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px">
      <?php foreach ($list as $t): ?>
        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:16px">
          <?php
            $shot = '';
            $file = root_path() . 'themes/' . $t['name'] . '/screenshot.png';
            if (is_file($file)) {
                $shot = 'data:image/png;base64,' . base64_encode((string) file_get_contents($file));
            }
          ?>
          <?php if ($shot): ?>
            <div style="border:1px solid #eee;border-radius:6px;overflow:hidden;margin-bottom:12px;background:#f8fafc">
              <img src="<?= $shot ?>" alt="<?= e($t['title']) ?>" style="display:block;width:100%;height:auto">
            </div>
          <?php endif; ?>
          <div style="display:flex;align-items:center;justify-content:space-between">
            <strong><?= e($t['title']) ?></strong>
            <?php if ($t['name'] === $current): ?><span class="badge badge-ok">当前使用</span><?php endif; ?>
          </div>
          <div class="muted" style="font-size:12px;margin:6px 0">目录：themes/<?= e($t['name']) ?> · v<?= e($t['version']) ?> <?= $t['author'] ? '· ' . e($t['author']) : '' ?></div>
          <div style="min-height:42px;color:#4b5563;font-size:13px"><?= e($t['description']) ?></div>
          <div style="margin-top:12px">
            <?php if ($t['name'] === $current): ?>
              <button class="btn btn-sm" disabled>已启用</button>
            <?php else: ?>
              <form method="post" action="/admin/theme/enable" style="display:inline">
                <input type="hidden" name="name" value="<?= e($t['name']) ?>">
                <button class="btn btn-sm btn-primary" type="submit">启用此模板</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head"><span>模板开发约定</span></div>
  <div class="card-body" style="font-size:13px;color:#374151;line-height:1.9">
    <div>模板目录：<code>themes/{主题名}/</code>，其中 <code>theme.json</code> 描述名称、版本、作者、说明。</div>
    <div>默认模板文件：<code>index.php</code>（首页）、<code>list.php</code>（栏目列表）、<code>article.php</code>（文章详情）、<code>page.php</code>（单页）、<code>layout.php</code>（公共头尾，可选）。</div>
    <div>模板为原生 PHP，可直接使用 <code>e()</code> 输出、<code>setting()</code> 读取配置，无需任何模板标签学习成本。</div>
    <div>手机模板：在 <code>themes/{主题名}/mobile/</code> 下写同名文件（index/list/article/page/_top/_bottom + style.css），系统按手机 UA 自动切换；没写的文件会自动回落 PC 模板。PC 上预览 <code>?mobile=1</code>，回电脑版 <code>?mobile=0</code>。</div>
  </div>
</div>

<?php include __DIR__ . '/../_footer.php'; ?>

