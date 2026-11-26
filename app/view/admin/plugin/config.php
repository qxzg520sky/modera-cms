<?php $pageTitle = $pageTitle ?? '插件设置'; include __DIR__ . '/../_header.php'; ?>
<?php $fields = (array) ($meta['config'] ?? []); ?>

<form method="post" action="/admin/plugin/save">
<input type="hidden" name="name" value="<?= e($name) ?>">
<div class="card">
  <div class="card-head"><span><?= e($meta['title'] ?? $name) ?> 配置</span><span class="muted" style="font-size:12px;font-weight:400"><?= e($meta['description'] ?? '') ?></span></div>
  <div class="card-body">
    <?php if (empty($fields)): ?>
      <div class="empty">该插件没有可配置项</div>
    <?php else: ?>
      <?php foreach ($fields as $f): ?>
        <div class="form-row">
          <div class="form-item">
            <label><?= e($f['label'] ?? $f['name']) ?></label>
            <?php $type = $f['type'] ?? 'text'; ?>
            <?php if ($type === 'textarea'): ?>
              <textarea name="<?= e($f['name']) ?>"><?= e($config[$f['name']] ?? '') ?></textarea>
            <?php elseif ($type === 'select'): ?>
              <select name="<?= e($f['name']) ?>">
                <?php foreach ((array) ($f['options'] ?? []) as $val => $label): ?>
                  <option value="<?= e($val) ?>" <?= (string) ($config[$f['name']] ?? '') === (string) $val ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            <?php else: ?>
              <input type="text" name="<?= e($f['name']) ?>" value="<?= e($config[$f['name']] ?? '') ?>" placeholder="<?= e($f['default'] ?? '') ?>">
            <?php endif; ?>
            <?php if (!empty($f['hint'])): ?><div class="hint"><?= e($f['hint']) ?></div><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<div style="display:flex;gap:10px">
  <button class="btn btn-primary" type="submit">保存配置</button>
  <a class="btn" href="/admin/plugin">返回插件列表</a>
</div>
</form>

<?php include __DIR__ . '/../_footer.php'; ?>
