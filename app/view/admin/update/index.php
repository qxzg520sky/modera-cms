<?php
$pageTitle = '在线更新';
include __DIR__ . '/../_header.php';
$cur = $current ?? [];
$s   = $settings ?? [];
$lc  = $lastCheck ?? [];
$lr  = $lastResult ?? [];
$avail = $lc['update_available'] ?? null;
$downgrade = !empty($lc['downgrade']);
$remoteVer = $lc['version']['version'] ?? '';
$sha = (string) ($lc['commit']['sha'] ?? ($lc['revision'] ?? ''));
$shortSha = $sha !== '' ? substr($sha, 0, 10) : '';
$notes = trim((string) ($lc['version']['notes'] ?? ''));
$archiveSize = (int) ($lc['archive_size'] ?? 0);
$hasCheck = !empty($lc);
$backup = (string) ($cur['backup'] ?? '');
?>
<div class="card">
  <div class="card-head">
    <span>程序版本</span>
    <span class="muted" style="font-weight:400;font-size:12px">只更新程序核心，不碰数据与模板</span>
  </div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px">
      <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;background:#fff">
        <div class="muted" style="font-size:12px">当前版本</div>
        <div style="font-size:22px;font-weight:700;margin-top:6px">v<?= e($cur['version'] ?? '未知') ?></div>
        <div class="muted" style="font-size:12px;margin-top:4px">发布日期 <?= e($cur['release'] ?? '—') ?></div>
      </div>
      <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;background:#fff">
        <div class="muted" style="font-size:12px">最近在线更新</div>
        <div style="font-size:16px;font-weight:600;margin-top:8px"><?= e($cur['updated_at'] ?: '尚未执行') ?></div>
        <div class="muted" style="font-size:12px;margin-top:4px"><?= e($cur['mirror'] ? strtoupper($cur['mirror']) . ' / ' . $cur['branch'] : '—') ?></div>
      </div>
      <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;background:#fff">
        <div class="muted" style="font-size:12px">已应用提交</div>
        <div style="font-family:ui-monospace,Consolas,monospace;font-size:15px;font-weight:600;margin-top:8px"><?= e($cur['revision'] ? substr($cur['revision'], 0, 10) : '—') ?></div>
        <div class="muted" style="font-size:12px;margin-top:4px">用于判断远端是否有新提交</div>
      </div>
      <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;background:#fff">
        <div class="muted" style="font-size:12px">最近备份</div>
        <div style="font-size:14px;font-weight:600;margin-top:8px;word-break:break-all"><?= e($backup ? basename($backup) : '暂无') ?></div>
        <div class="muted" style="font-size:12px;margin-top:4px"><?= $backup ? e(number_format((int) ($cur['backup_size'] ?? 0) / 1024, 1)) . ' KB' : '正式更新时自动生成' ?></div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head">
    <span>更新源</span>
    <span class="muted" style="font-weight:400;font-size:12px">GitHub 与 Gitee 二选一，自动走 HTTPS</span>
  </div>
  <div class="card-body">
    <form method="post" action="/admin/update/check">
      <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
        <label style="display:flex;gap:8px;align-items:center;border:1px solid #dbe2ea;border-radius:9px;padding:10px 14px;background:#fff;cursor:pointer">
          <input type="radio" name="mirror" value="github" <?= ($s['mirror'] ?? 'github') === 'github' ? 'checked' : '' ?>>
          <span><b>GitHub</b><br><span class="muted" style="font-size:12px"><?= e($s['github'] ?? '') ?></span></span>
        </label>
        <label style="display:flex;gap:8px;align-items:center;border:1px solid #dbe2ea;border-radius:9px;padding:10px 14px;background:#fff;cursor:pointer">
          <input type="radio" name="mirror" value="gitee" <?= ($s['mirror'] ?? '') === 'gitee' ? 'checked' : '' ?>>
          <span><b>Gitee</b><br><span class="muted" style="font-size:12px"><?= e($s['gitee'] ?? '') ?></span></span>
        </label>
        <label style="display:flex;gap:8px;align-items:center">
          <span class="muted" style="font-size:13px">分支</span>
          <input type="text" name="branch" value="<?= e($s['branch'] ?? 'main') ?>" style="width:110px;padding:8px 10px;border:1px solid #dbe2ea;border-radius:7px">
        </label>
        <button class="btn btn-primary" type="submit">检查更新</button>
        <button class="btn" type="submit" formaction="/admin/update/run" name="dry_run" value="1">试运行差异</button>
        <button class="btn" type="submit" formaction="/admin/update/run" style="color:#b42318;border-color:#f0c9c4;<?= $downgrade ? 'opacity:.55;cursor:not-allowed' : '' ?>" <?= $downgrade ? 'disabled title="远端落后，先推送新代码并重新检查"' : '' ?> onclick="return confirm('确定从当前选择的源更新程序核心？更新前会自动备份，可在本页回滚。');">立即更新</button>
      </div>
    </form>
    <div class="muted" style="font-size:12px;line-height:1.9;margin-top:12px">
      更新范围：<code>app</code>、<code>route</code>、<code>config</code>、<code>extend</code>、<code>error</code>、<code>public/static/admin</code>、<code>version.json</code>。
      不更新 <code>.env</code>、<code>uploads</code>、<code>themes</code>、<code>plugins</code>、<code>modules</code>、<code>vendor</code>、数据库和运行时缓存。
    </div>
    <div style="margin-top:12px;padding:11px 13px;border:1px solid #f3d38a;background:#fffbeb;border-radius:8px;color:#92400e;font-size:12px;line-height:1.8">
      注意：远端会覆盖本地二次修改过的 <code>app</code> / <code>route</code> 文件。正式更新前请先点“试运行差异”，确认变更清单；更新失败会自动回滚，成功后也可手动回滚最近一次。远端提交日期早于本地版本发布日期时，系统会拒绝降级。
    </div>
  </div>
</div>

<?php if ($hasCheck): ?>
<div class="card">
  <div class="card-head">
    <span>最近一次检查</span>
    <span class="badge <?= $avail === true ? 'badge-ok' : 'badge-gray' ?>" style="<?= $avail === true ? 'background:#ecfdf3;color:#027a48' : ($downgrade ? 'background:#fffbeb;color:#92400e' : '') ?>">
      <?= $downgrade ? '远端落后，已阻止' : ($avail === true ? '发现更新' : ($avail === false ? '已是最新' : '待确认')) ?>
    </span>
  </div>
  <div class="card-body" style="font-size:13px;line-height:1.9">
    <div style="display:grid;grid-template-columns:150px 1fr;gap:7px 12px">
      <div class="muted">检查时间</div><div><?= e($lc['checked_at'] ?? '') ?></div>
      <div class="muted">更新源</div><div><?= e(strtoupper($lc['mirror'] ?? '')) ?> · <?= e($lc['repo'] ?? '') ?> · <?= e($lc['branch'] ?? '') ?></div>
      <div class="muted">远端版本</div><div><?= $remoteVer !== '' ? 'v' . e($remoteVer) : '源仓库未提供 version.json' ?></div>
      <div class="muted">最新提交</div><div style="font-family:ui-monospace,Consolas,monospace"><?= $shortSha !== '' ? e($shortSha) : '未获取到提交号' ?>
        <?php if (!empty($lc['commit']['date'])): ?><span class="muted" style="font-family:inherit"> · <?= e($lc['commit']['date']) ?></span><?php endif; ?>
      </div>
      <?php if (!empty($lc['commit']['message'])): ?>
        <div class="muted">提交说明</div><div><?= e(mb_substr((string) $lc['commit']['message'], 0, 240)) ?></div>
      <?php endif; ?>
      <div class="muted">源码包</div><div>HTTP <?= (int) ($lc['archive_status'] ?? 0) ?><?= $archiveSize > 0 ? ' · ' . e(number_format($archiveSize / 1048576, 1)) . ' MB' : '' ?></div>
    </div>
    <?php if ($notes !== ''): ?>
      <div style="margin-top:12px;padding:12px 14px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;white-space:pre-wrap"><?= e($notes) ?></div>
    <?php endif; ?>
    <?php if ($downgrade): ?>
      <div style="margin-top:12px;padding:12px 14px;background:#fffbeb;border:1px solid #f3d38a;border-radius:8px;color:#92400e;line-height:1.8">
        远端最新提交早于本地版本发布日期，正式更新会被拒绝，避免把本地新功能“更新”回旧代码。请先把本地代码推送到当前更新源，再点检查更新。
      </div>
    <?php endif; ?>
    <?php if (!empty($lc['error'])): ?>
      <div style="margin-top:12px;color:#b42318">连接提示：<?= e($lc['error']) ?></div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($lr['log'])): ?>
<div class="card">
  <div class="card-head">
    <span>最近一次执行结果</span>
    <span class="muted" style="font-weight:400;font-size:12px"><?= !empty($lr['dry_run']) ? '试运行，未写文件' : '正式更新' ?></span>
  </div>
  <div class="card-body">
    <pre style="margin:0;max-height:360px;overflow:auto;background:#0f172a;color:#dbeafe;padding:14px 16px;border-radius:9px;font-size:12px;line-height:1.7;white-space:pre-wrap"><?= e(implode("\n", (array) $lr['log'])) ?></pre>
  </div>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-head"><span>回滚</span></div>
  <div class="card-body" style="display:flex;gap:14px;align-items:center;justify-content:space-between;flex-wrap:wrap">
    <div class="muted" style="font-size:13px;line-height:1.8">
      恢复上一次正式更新前的核心文件。回滚只处理本页“最近备份”对应的文件，不恢复数据库和上传内容。
    </div>
    <form method="post" action="/admin/update/rollback" onsubmit="return confirm('确定回滚最近一次正式更新？当前核心文件会被替换为上次更新前的备份。');">
      <button class="btn" type="submit" <?= $backup ? '' : 'disabled' ?> style="<?= $backup ? 'color:#b42318;border-color:#f0c9c4' : 'opacity:.55;cursor:not-allowed' ?>">回滚上次更新</button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../_footer.php'; ?>
