<?php /** @var array $row, $codes, $names, $fields, $type */ ?>
<?php include __DIR__ . '/../../../app/view/admin/_header.php'; ?>
<style>
.ml-block{background:#fff;border:1px solid var(--line);border-radius:8px;padding:16px 18px;margin-bottom:16px}
.ml-block h4{margin:0 0 12px;font-size:14px;display:flex;align-items:center;gap:8px}
.ml-block h4 code{background:#eef2ff;color:#3730a3;padding:2px 8px;border-radius:4px;font-size:12px}
.ml-row{display:flex;gap:14px;flex-wrap:wrap}
.ml-row > .form-item{flex:1 1 320px}
</style>
  <div class="card">
    <div class="card-head">
      <span>翻译：<?= e($row['title'] ?? $row['name'] ?? '') ?>（中文原文）</span>
      <a class="btn" href="/admin/plugin/multilang">← 返回多语言管理</a>
    </div>
    <div class="card-body">
      <form method="post" action="/admin/plugin/multilang/save-content">
        <input type="hidden" name="type" value="<?= e($type) ?>">
        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">

        <?php foreach ($codes as $c): ?>
          <div class="ml-block">
            <h4><?= e($names[$c] ?? strtoupper($c)) ?> <code><?= e($c) ?></code>
              <span class="hint" style="font-weight:400">留空则前台显示中文原文</span>
            </h4>

            <?php foreach ($fields as $field => $label):
              $col   = $field . '_' . $c;
              $value = (string) ($row[$col] ?? '');
            ?>
              <?php if (in_array($field, ['content'], true)): ?>
                <div class="form-item">
                  <label><?= e($label) ?></label>
                  <?= rich_editor($col, $value, '320px') ?>
                </div>
              <?php else: ?>
                <div class="ml-row">
                  <div class="form-item">
                    <label><?= e($label) ?></label>
                    <input type="text" name="<?= e($col) ?>" value="<?= e($value) ?>"
                      placeholder="<?= e((string) ($row[$field] ?? '')) ?>">
                  </div>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>

        <?php if (!$codes): ?>
          <div class="alert alert-err">还没有启用任何外语，请先在多语言管理页添加语言。</div>
        <?php endif; ?>

        <button class="btn btn-primary" type="submit">保存</button>
        <a class="btn" href="/admin/plugin/multilang">取消</a>
      </form>
    </div>
  </div>
<?php include __DIR__ . '/../../../app/view/admin/_footer.php'; ?>
