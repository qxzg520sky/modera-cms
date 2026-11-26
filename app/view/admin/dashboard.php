<?php $pageTitle = '仪表盘'; include __DIR__ . '/_header.php'; ?>

<div class="stat-grid">
  <div class="stat"><div class="num"><?= (int) $stat['article'] ?></div><div class="lab">文章总数</div></div>
  <div class="stat"><div class="num"><?= (int) $stat['category'] ?></div><div class="lab">栏目数量</div></div>
  <div class="stat"><div class="num"><?= (int) $stat['page'] ?></div><div class="lab">单页数量</div></div>
  <div class="stat"><div class="num"><?= (int) $stat['plugin'] ?></div><div class="lab">已启用插件</div></div>
</div>

<div class="card">
  <div class="card-head">
    <span>快捷入口</span>
  </div>
  <div class="card-body" style="display:flex;gap:10px;flex-wrap:wrap">
    <a class="btn btn-primary" href="/admin/article/edit">＋ 发布文章</a>
    <a class="btn" href="/admin/category/edit">＋ 新建栏目</a>
    <a class="btn" href="/admin/page/edit">＋ 新建单页</a>
    <a class="btn" href="/admin/theme">切换模板</a>
    <a class="btn" href="/admin/plugin">插件市场</a>
    <a class="btn" href="/admin/setting">站点设置</a>
  </div>
</div>

<div class="card">
  <div class="card-head"><span>最近发布</span><a href="/admin/article">全部文章 →</a></div>
  <?php if (empty($recent)): ?>
    <div class="empty">还没有文章，点上方“发布文章”开始</div>
  <?php else: ?>
  <table class="table">
    <thead><tr><th>标题</th><th>栏目</th><th>状态</th><th>发布时间</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($recent as $row): ?>
      <tr>
        <td><?= e($row['title']) ?></td>
        <td class="muted"><?= e($row['category_name']) ?></td>
        <td><?= ((int) $row['status'] === 1) ? '<span class="badge badge-ok">已发布</span>' : '<span class="badge badge-gray">草稿</span>' ?></td>
        <td class="muted"><?= e($row['create_time'] ?? '') ?></td>
        <td class="actions"><a class="btn-ghost" href="/admin/article/edit/<?= (int) $row['id'] ?>">编辑</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-head"><span>系统信息</span></div>
  <table class="table">
    <tbody>
      <tr><th style="width:160px">ThinkPHP</th><td><?= e($info['thinkphp']) ?></td></tr>
      <tr><th>PHP 版本</th><td><?= e($info['php']) ?></td></tr>
      <tr><th>MySQL</th><td><?= e($info['mysql']) ?></td></tr>
      <tr><th>Web 服务器</th><td><?= e($info['server']) ?></td></tr>
      <tr><th>操作系统</th><td><?= e($info['os']) ?></td></tr>
      <tr><th>当前模板</th><td><?= e($info['theme']) ?></td></tr>
      <tr><th>项目路径</th><td class="muted"><?= e($info['root']) ?></td></tr>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
