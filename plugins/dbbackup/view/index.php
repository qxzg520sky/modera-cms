<?php $pageTitle = $pageTitle ?? '数据库备份'; include __DIR__ . '/../../../app/view/admin/_header.php'; ?>
<style>
.bk-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:18px}
.bk-stat{background:#fff;border-radius:8px;padding:16px 18px;box-shadow:0 1px 2px rgba(16,24,40,.06)}
.bk-stat .n{font-size:24px;font-weight:700;color:#111827;word-break:break-all}
.bk-stat .l{font-size:12px;color:#6b7280;margin-top:4px;white-space:nowrap}
.bk-ops{display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.bk-tag{display:inline-block;padding:2px 8px;border-radius:99px;font-size:12px;white-space:nowrap}
.bk-tag.manual{background:#eef2ff;color:#3730a3}
.bk-tag.auto{background:#ecfdf5;color:#047857}
.bk-tag.pre{background:#fff7ed;color:#b45309}
.bk-tag.upload{background:#f3f4f6;color:#4b5563}
.bk-file{font-family:ui-monospace,Menlo,Consolas,monospace;font-size:12px;white-space:nowrap}
.bk-time,.bk-nowrap{white-space:nowrap}
.bk-table-wrap{overflow-x:auto}
.bk-del{color:#b91c1c}
.bk-note{font-size:12px;color:#6b7280;line-height:1.8}
</style>

<?php if (!empty($flash)): ?>
<div class="card" style="border-left:4px solid #16a34a;margin-bottom:16px">
  <div class="card-body" style="padding:14px 18px;font-size:13px;color:#065f46"><?= e($flash) ?></div>
</div>
<?php endif; ?>

<div class="bk-stats">
  <div class="bk-stat"><div class="n"><?= e($info['name']) ?></div><div class="l">当前数据库</div></div>
  <div class="bk-stat"><div class="n"><?= (int) $info['tables'] ?></div><div class="l">数据表数量</div></div>
  <div class="bk-stat"><div class="n"><?= e((string) $info['size']) ?> MB</div><div class="l">数据库大小</div></div>
  <div class="bk-stat"><div class="n"><?= count($files) ?></div><div class="l">备份文件数</div></div>
</div>

<div class="card">
  <div class="card-head"><span>备份与还原</span></div>
  <div class="card-body">
    <div class="bk-ops" style="margin-bottom:16px">
      <form method="post" action="/admin/plugin/dbbackup/run" style="display:inline"
            onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='正在备份…';">
        <button class="btn btn-primary" type="submit">立即备份整个数据库</button>
      </form>
      <form method="post" action="/admin/plugin/dbbackup/upload" enctype="multipart/form-data" style="display:inline-flex;gap:8px;align-items:center">
        <input type="file" name="sql_file" accept=".sql" required>
        <button class="btn" type="submit">上传 SQL</button>
      </form>
    </div>
    <div class="bk-note">
      备份是把整个库导出成一份 .sql（表结构 + 全部数据，含 DROP/CREATE，可直接在 phpMyAdmin 里导入）。<br>
      还原会先给当前库自动拍一份「预还原快照」（文件名带 <code>_pre</code>），再执行所选备份；还原过程中请勿操作其他页面。<br>
      目录：<code><?= e($dir) ?></code>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head"><span>备份文件（<?= count($files) ?>）</span></div>
  <?php if (!$files): ?>
    <div class="card-body"><div class="muted">还没有备份，点上面「立即备份」生成第一份。</div></div>
  <?php else: ?>
  <div class="bk-table-wrap">
  <table class="table">
    <thead>
      <tr>
        <th>文件</th><th>来源</th><th>大小</th><th>表数</th><th>时间</th><th style="width:200px">操作</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($files as $f): ?>
      <tr>
        <td class="bk-file"><?= e($f['file']) ?></td>
        <td>
          <?php
            $labels = ['manual' => '手动', 'auto' => '定时', 'pre' => '还原前', 'upload' => '上传'];
            $txt = $labels[$f['reason']] ?? $f['reason'];
          ?>
          <span class="bk-tag <?= e($f['reason']) ?>"><?= e($txt) ?></span>
        </td>
        <td class="bk-nowrap"><?= e(\plugins\dbbackup\service\Backup::humanSize((int) $f['size'])) ?></td>
        <td class="bk-nowrap"><?= (int) $f['tables'] ?: '—' ?></td>
        <td class="muted bk-time"><?= date('Y-m-d H:i:s', (int) $f['time']) ?></td>
        <td class="actions" style="white-space:nowrap">
          <a class="btn btn-sm" href="/admin/plugin/dbbackup/download?file=<?= urlencode($f['file']) ?>">下载</a>
          <form method="post" action="/admin/plugin/dbbackup/restore" style="display:inline"
                onsubmit="return confirm('确定用这份备份覆盖当前数据库吗？\n\n<?= e(addslashes($f['file'])) ?>\n\n还原前会自动备份当前库。');">
            <input type="hidden" name="file" value="<?= e($f['file']) ?>">
            <button class="btn btn-sm btn-primary" type="submit">还原</button>
          </form>
          <form method="post" action="/admin/plugin/dbbackup/delete" style="display:inline"
                onsubmit="return confirm('删除备份文件 <?= e(addslashes($f['file'])) ?> ？删了就恢复不了。');">
            <input type="hidden" name="file" value="<?= e($f['file']) ?>">
            <button class="btn btn-sm bk-del" type="submit">删除</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-head"><span>定时自动备份</span></div>
  <div class="card-body">
    <form method="post" action="/admin/plugin/dbbackup/setting/save">
      <div class="form-row">
        <div class="form-item">
          <label>自动备份</label>
          <select name="auto">
            <option value="1" <?= (string) $cfg['auto'] === '1' ? 'selected' : '' ?>>开启</option>
            <option value="0" <?= (string) $cfg['auto'] === '0' ? 'selected' : '' ?>>关闭</option>
          </select>
          <div class="hint">开启后，有访客访问前台或你打开后台时会检查一次，到点就备一份（不依赖服务器计划任务）</div>
        </div>
        <div class="form-item">
          <label>间隔（小时）</label>
          <input type="number" name="interval" min="1" max="720" value="<?= (int) $cfg['interval'] ?>">
          <div class="hint">例如 24 = 每天一次，168 = 每周一次</div>
        </div>
        <div class="form-item">
          <label>自动备份保留份数</label>
          <input type="number" name="keep" min="1" max="60" value="<?= (int) $cfg['keep'] ?>">
          <div class="hint">超出会自动删最旧的；手动备份和还原前快照不会被自动清理</div>
        </div>
        <div class="form-item">
          <label>文件名前缀</label>
          <input type="text" name="prefix" maxlength="12" value="<?= e((string) $cfg['prefix']) ?>">
          <div class="hint">生成的文件名形如 <?= e((string) $cfg['prefix']) ?>_20261125_140000_auto.sql</div>
        </div>
      </div>
      <div style="margin-top:14px">
        <button class="btn btn-primary" type="submit">保存设置</button>
        <span class="muted" style="margin-left:12px">
          上次自动备份：<?= (int) ($state['last'] ?? 0) > 0 ? date('Y-m-d H:i:s', (int) $state['last']) : '还没有' ?>
          <?php if (!empty($state['error'])): ?>
            <span style="color:#b91c1c">（上次失败：<?= e((string) $state['error']) ?>）</span>
          <?php endif; ?>
        </span>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../../app/view/admin/_footer.php'; ?>


