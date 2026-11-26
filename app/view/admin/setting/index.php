<?php $pageTitle = '系统设置'; include __DIR__ . '/../_header.php'; ?>

<form method="post" action="/admin/setting/save">
<div class="card">
  <div class="card-head"><span>站点信息</span></div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-item"><label>站点名称</label><input type="text" name="site_name" value="<?= e($s['site_name'] ?? '') ?>"></div>
      <div class="form-item"><label>站点标语</label><input type="text" name="site_slogan" value="<?= e($s['site_slogan'] ?? '') ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-item"><label>LOGO 地址</label><input type="text" name="site_logo" value="<?= e($s['site_logo'] ?? '') ?>" placeholder="/uploads/logo.png"></div>
      <div class="form-item"><label>备案号</label><input type="text" name="site_icp" value="<?= e($s['site_icp'] ?? '') ?>" placeholder="陇ICP备xxxxxxxx号"></div>
    </div>
    <div class="form-row">
      <div class="form-item"><label>SEO 关键词</label><input type="text" name="site_keywords" value="<?= e($s['site_keywords'] ?? '') ?>"></div>
      <div class="form-item"><label>SEO 描述</label><input type="text" name="site_description" value="<?= e($s['site_description'] ?? '') ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-item"><label>联系电话</label><input type="text" name="site_phone" value="<?= e($s['site_phone'] ?? '') ?>"></div>
      <div class="form-item"><label>邮箱</label><input type="text" name="site_email" value="<?= e($s['site_email'] ?? '') ?>"></div>
    </div>
    <div class="form-row">
      <div class="form-item"><label>公司地址</label><input type="text" name="site_address" value="<?= e($s['site_address'] ?? '') ?>"></div>
      <div class="form-item"><label>版权信息</label><input type="text" name="site_copyright" value="<?= e($s['site_copyright'] ?? '') ?>"></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head"><span>内容与多语言</span></div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-item"><label>列表每页条数</label><input type="number" name="article_page_size" value="<?= e($s['article_page_size'] ?? '10') ?>"></div>
      <div class="form-item">
        <label>手机端模板</label>
        <select name="mobile_enable">
          <option value="1" <?= ($s['mobile_enable'] ?? '1') === '1' ? 'selected' : '' ?>>开启（自动识别手机 UA）</option>
          <option value="0" <?= ($s['mobile_enable'] ?? '1') === '0' ? 'selected' : '' ?>>关闭（手机也显示 PC 模板）</option>
        </select>
        <div class="hint">手机模板放在 themes/当前主题/mobile/，没写就自动回落 PC 模板；PC 上预览用 ?mobile=1，回到电脑版用 ?mobile=0</div>
      </div>
      <?php
        $mlCfg   = multilang_config();
        $mlLabel = ['中文'];
        foreach ($mlCfg['langs'] as $__c) {
            $mlLabel[] = $mlCfg['names'][$__c] ?? strtoupper($__c);
        }
      ?>
      <div class="form-item">
        <label>前台语言</label>
        <input type="text" value="<?= e(implode('、', $mlLabel)) ?>" disabled>
        <div class="hint">
          <?php if (!$mlCfg['enabled']): ?>
            多语言插件未启用，前台只有中文。到「插件扩展 → 多语言」启用后即可添加语言。
          <?php elseif (!$mlCfg['langs']): ?>
            插件里只配了中文，前台不显示切换器。在插件里再加语言，前台会自动出现。
          <?php else: ?>
            语言由「插件扩展 → 多语言」管理：插件里配几种，前台就显示几个切换项；只留一种时切换器自动隐藏。
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div style="display:flex;gap:10px">
  <button class="btn btn-primary" type="submit">保存设置</button>
  <a class="btn" href="/" target="_blank">查看前台</a>
</div>
</form>

<?php include __DIR__ . '/../_footer.php'; ?>

