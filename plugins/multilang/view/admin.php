<?php /** @var array $cfg, $codes, $status, $fields, $cats, $arts, $pages, $logs, $flash, $enabled */ ?>
<?php include __DIR__ . '/../../../app/view/admin/_header.php'; ?>
<style>
.ml-head{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.ml-col{overflow-x:auto}
.ml-col table{min-width:640px}
.ml-ok{color:#1a7f37;font-weight:600}
.ml-no{color:#c62828;font-weight:600}
.ml-tip{background:#f6f8fa;border-left:3px solid #2f6fed;padding:10px 14px;border-radius:4px;color:#555;font-size:13px;line-height:1.7}
.ml-tabs{display:flex;gap:8px;margin-bottom:12px}
.ml-tabs a{font-size:13px}
</style>
  <div class="card">
    <div class="card-head ml-head">
      <h3>多语言插件</h3>
      <a class="btn" href="/admin/plugin">← 返回插件列表</a>
      <span class="muted">状态：<?= $enabled ? '已启用' : '已停用' ?></span>
    </div>
    <div class="card-body">
      <?php if ($logs): ?>
        <div class="alert"><?php foreach ($logs as $l): ?><div><?= e($l) ?></div><?php endforeach; ?></div>
      <?php endif; ?>

      <div class="ml-tip">
        原理：每种外语在数据库里新增一组字段（如 <code>name_en</code>、<code>title_en</code>、<code>content_en</code>），
        前台访问 <code>/en/</code> 时自动读取这些字段；留空则自动显示中文。卸载插件会把这些字段和数据一起删掉，站点回到纯中文状态。
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><h3>① 语言设置</h3></div>
    <div class="card-body">
      <form method="post" action="/admin/plugin/multilang/save">
        <div class="form-item">
          <label>启用的外语（每行一个，格式 <code>语言代码 显示名</code>）</label>
          <textarea name="langs_text" rows="6" placeholder="en English&#10;ja 日本語"><?php
            $lines = [];
            foreach ($codes as $c) {
                $lines[] = $c . ' ' . ($cfg['names'][$c] ?? strtoupper($c));
            }
            echo e(implode("\n", $lines));
          ?></textarea>
          <div class="hint">默认语言为中文（zh-cn，不带前缀）。删掉某一行保存后，该语言的字段会被自动删除。保存即自动建字段，无需手动操作。</div>
        </div>
        <button class="btn btn-primary" type="submit">保存并同步字段</button>
      </form>

      <div class="ml-col" style="margin-top:18px">
        <table class="table">
          <thead><tr><th>语言</th><th>显示名</th><th>地址前缀</th><th>字段状态</th><th>操作</th></tr></thead>
          <tbody>
          <tr><td>zh-cn（默认）</td><td>中文</td><td>无前缀 /</td><td class="ml-ok">原生字段</td><td class="muted">不需要翻译</td></tr>
          <?php foreach ($codes as $c): ?>
          <tr>
            <td><code><?= e($c) ?></code></td>
            <td><?= e($cfg['names'][$c] ?? strtoupper($c)) ?></td>
            <td>/<?= e($c) ?>/</td>
            <td class="<?= !empty($status[$c]) ? 'ml-ok' : 'ml-no' ?>">
              <?= !empty($status[$c]) ? '字段已创建' : '字段缺失' ?>
            </td>
            <td>
              <?php if (!empty($status[$c])): ?>
                <span class="muted">已就绪</span>
              <?php else: ?>
                <form method="post" action="/admin/plugin/multilang/install" style="display:inline">
                  <button class="btn btn-sm" type="submit">补建字段</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="hint" style="margin-top:14px">
        字段清单：栏目 3 个（名称/副标题/SEO描述）、文章 5 个（标题/内容/摘要/SEO标题/SEO描述）、单页 4 个（标题/内容/SEO标题/SEO描述）、站点设置 1 个（站点名）。
      </div>

      <form method="post" action="/admin/plugin/multilang/drop" onsubmit="return confirm('会删除所有外语字段及其内容，且不可恢复。确定？');" style="margin-top:16px">
        <button class="btn btn-danger" type="submit">删除全部多语言字段</button>
        <span class="muted">（内容清空，回到纯中文；插件保持启用）</span>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><h3>② 内容翻译</h3></div>
    <div class="card-body">
      <div class="ml-tabs">
        <a class="btn btn-sm" href="#tab-cat">栏目 (<?= count($cats) ?>)</a>
        <a class="btn btn-sm" href="#tab-art">文章 (<?= count($arts) ?>)</a>
        <a class="btn btn-sm" href="#tab-page">单页 (<?= count($pages) ?>)</a>
      </div>

      <?php
      $blocks = [
          'tab-cat'  => ['栏目', 'category', $cats, 'name', '/admin/plugin/multilang/edit/category/'],
          'tab-art'  => ['文章', 'article', $arts, 'title', '/admin/plugin/multilang/edit/article/'],
          'tab-page' => ['单页', 'page', $pages, 'title', '/admin/plugin/multilang/edit/page/'],
      ];
      foreach ($blocks as $tid => [$label, $type, $rows, $nameField, $editUrl]):
      ?>
      <div id="<?= $tid ?>" class="ml-col" style="margin-bottom:26px">
        <table class="table">
          <thead>
            <tr>
              <th style="width:60px">ID</th>
              <th><?= e($label) ?>名称（中文）</th>
              <?php foreach ($codes as $c): ?><th><?= e($cfg['names'][$c] ?? strtoupper($c)) ?></th><?php endforeach; ?>
              <th style="width:110px">操作</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= (int) $r['id'] ?></td>
              <td><?= e($r[$nameField] ?? '') ?></td>
              <?php foreach ($codes as $c):
                $n = \plugins\multilang\service\Lang::filled($r, $type, $c);
                $all = count($fields[$type] ?? []);
              ?>
                <td>
                  <?php if ($n === 0): ?><span class="muted">未填写</span>
                  <?php elseif ($n >= $all): ?><span class="ml-ok">完整 <?= $n ?>/<?= $all ?></span>
                  <?php else: ?><span><?= $n ?>/<?= $all ?></span><?php endif; ?>
                </td>
              <?php endforeach; ?>
              <td><a class="btn btn-sm" href="<?= $editUrl . (int) $r['id'] ?>">翻译</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?><tr><td colspan="10" class="muted">暂无数据</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php include __DIR__ . '/../../../app/view/admin/_footer.php'; ?>
