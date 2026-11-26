<?php $pageTitle = $pageTitle ?? '会员导入导出'; include __DIR__ . '/../../../../app/view/admin/_header.php'; ?>
<link rel="stylesheet" href="/member/asset/admin.css?v=1.0.0">

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
  <div class="card">
    <div class="card-head"><span>Excel 批量导入</span><a class="btn btn-sm" href="/admin/plugin/member/template">下载导入模板</a></div>
    <form method="post" action="/admin/plugin/member/import" enctype="multipart/form-data" style="padding:18px">
      <div style="border:1px dashed #cfe0f7;border-radius:10px;background:#f8fbff;padding:22px;text-align:center">
        <div style="font-size:34px;line-height:1">📥</div>
        <div style="font-weight:700;margin:8px 0 4px">选择 .xlsx 或 .csv 文件</div>
        <div class="muted" style="font-size:12px;margin-bottom:12px">第一行必须是表头；单次最多 500 行；手机号重复会更新已有会员。</div>
        <input type="file" name="file" accept=".xlsx,.csv" required>
      </div>
      <div style="margin-top:14px;display:flex;gap:8px">
        <button class="btn btn-primary" type="submit">开始导入</button>
        <a class="btn" href="/admin/plugin/member/members">返回会员列表</a>
      </div>
    </form>
  </div>

  <div class="card">
    <div class="card-head"><span>导出会员</span></div>
    <div style="padding:18px;line-height:1.9;font-size:13px;color:#475569">
      导出全部会员基础资料、等级、业务员、余额、积分、累计消费，格式为真正的 <code>.xlsx</code>，手机号按文本写入，不会丢前导 0。
      <div style="margin-top:14px"><a class="btn btn-primary" href="/admin/plugin/member/export">导出全部会员 Excel</a></div>
      <div class="mb-admin-note" style="margin-top:16px">
        导入只更新基础资料、等级、业务员，不会覆盖已有会员的余额和积分；需要调资产请到会员详情页执行“资产调整”，以便留下流水。
      </div>
    </div>
  </div>
</div>

<?php if ($import): ?>
<div class="card" style="margin-top:16px">
  <div class="card-head"><span>导入结果</span></div>
  <div style="padding:18px">
    <div class="mb-admin-stats" style="grid-template-columns:repeat(3,1fr);margin-bottom:12px">
      <div class="mb-admin-stat"><div class="l">新增</div><div class="n green"><?= (int) $import['created'] ?></div></div>
      <div class="mb-admin-stat"><div class="l">更新</div><div class="n blue"><?= (int) $import['updated'] ?></div></div>
      <div class="mb-admin-stat"><div class="l">跳过</div><div class="n warn"><?= (int) $import['skipped'] ?></div></div>
    </div>
    <?php if (!empty($import['errors'])): ?>
      <div class="mb-admin-note" style="border-color:#fecdd3;background:#fff7f7;color:#9f1239">
        <strong>失败行：</strong>
        <ul class="mb-admin-log" style="margin-top:8px"><?php foreach ($import['errors'] as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
      </div>
    <?php else: ?>
      <div class="muted">全部行处理完成，没有失败记录。</div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="card" style="margin-top:16px">
  <div class="card-head"><span>模板字段说明</span></div>
  <div style="padding:18px;overflow:auto">
    <table class="table" style="min-width:760px">
      <thead><tr><th>字段</th><th>是否必填</th><th>说明</th></tr></thead>
      <tbody>
        <tr><td>手机号*</td><td><span class="mb-admin-tag warn">必填</span></td><td>唯一标识；已存在则更新基础资料</td></tr>
        <tr><td>姓名</td><td>选填</td><td>真实姓名，用于后台和业务员识别</td></tr>
        <tr><td>用户名</td><td>选填</td><td>不填自动生成，需全站唯一</td></tr>
        <tr><td>密码</td><td>选填</td><td>留空使用会员设置里的默认密码</td></tr>
        <tr><td>邮箱</td><td>选填</td><td>格式错误会导致该行失败</td></tr>
        <tr><td>公司</td><td>选填</td><td>企业客户名称</td></tr>
        <tr><td>等级</td><td>选填</td><td>填等级名称或 code，例如“金卡”或“gold”</td></tr>
        <tr><td>业务员</td><td>选填</td><td>填业务员账号或姓名</td></tr>
        <tr><td>余额</td><td>选填</td><td>仅新会员生效，并写入初始化余额日志</td></tr>
        <tr><td>积分</td><td>选填</td><td>仅新会员生效，并写入初始化积分日志</td></tr>
        <tr><td>累计消费</td><td>选填</td><td>新会员会按该金额尝试自动升级</td></tr>
        <tr><td>备注</td><td>选填</td><td>后台内部备注</td></tr>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../../../../app/view/admin/_footer.php'; ?>

