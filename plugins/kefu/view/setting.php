<?php $pageTitle = $pageTitle ?? '客服设置'; include __DIR__ . '/../../../app/view/admin/_header.php'; ?>
<style>
.kf-week{display:flex;gap:6px;flex-wrap:wrap}
.kf-week label{display:flex;align-items:center;gap:6px;padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;font-size:13px;cursor:pointer}
.kf-week input{-webkit-appearance:checkbox;appearance:auto;width:auto}
.kf-hint{font-size:12px;color:#9aa3af;margin-top:6px}
.kf-state{font-size:12px;color:#6b7280;margin-left:8px}
.kf-state.on{color:#15803d}
</style>

<form method="post" action="/admin/plugin/kefu/setting/save">
<input type="hidden" name="_k" value="1">

<div class="card">
  <div class="card-head"><span>开关与外观</span><span class="muted" style="font-weight:400;font-size:12px">卸载插件后前台浮窗和这些数据一起消失</span></div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-item">
        <label>前台浮窗</label>
        <select name="enabled">
          <option value="1" <?= ($cfg['enabled'] ?? '1') === '1' ? 'selected' : '' ?>>开启</option>
          <option value="0" <?= ($cfg['enabled'] ?? '1') === '0' ? 'selected' : '' ?>>关闭</option>
        </select>
        <div class="kf-hint">关闭后前台完全不显示客服入口，后台数据保留</div>
      </div>
      <div class="form-item">
        <label>按钮文字</label>
        <input type="text" name="btn_text" value="<?= e($cfg['btn_text']) ?>" placeholder="在线客服">
      </div>
      <div class="form-item">
        <label>按钮图标</label>
        <input type="text" name="btn_icon" value="<?= e($cfg['btn_icon']) ?>" placeholder="💬">
        <div class="kf-hint">可以填 Emoji，也可以留空只显示文字</div>
      </div>
      <div class="form-item">
        <label>浮窗位置</label>
        <select name="position">
          <option value="right" <?= $cfg['position'] === 'right' ? 'selected' : '' ?>>右下角</option>
          <option value="left" <?= $cfg['position'] === 'left' ? 'selected' : '' ?>>左下角</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-item">
        <label>主题色</label>
        <input type="text" name="theme" value="<?= e($cfg['theme']) ?>" placeholder="#2563eb">
        <div class="kf-hint">十六进制颜色，按钮和面板头部都用它</div>
      </div>
      <div class="form-item">
        <label>离屏幕边缘距离（px）</label>
        <input type="number" name="float_gap" value="<?= (int) $cfg['float_gap'] ?>">
      </div>
      <div class="form-item">
        <label>面板标题</label>
        <input type="text" name="title" value="<?= e($cfg['title']) ?>">
      </div>
      <div class="form-item">
        <label>标题下的小字</label>
        <input type="text" name="subtitle" value="<?= e($cfg['subtitle']) ?>">
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head"><span>工作时间与在线状态</span><span class="kf-state <?= $online ? 'on' : '' ?>">当前：<?= $online ? '在线' : '离线' ?>（服务器时间 <?= date('Y-m-d H:i') ?>）</span></div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-item" style="flex:2">
        <label>工作日</label>
        <div class="kf-week">
          <?php
          $days = array_filter(array_map('intval', explode(',', (string) $cfg['weekdays'])));
          $names = [1 => '周一', 2 => '周二', 3 => '周三', 4 => '周四', 5 => '周五', 6 => '周六', 7 => '周日'];
          foreach ($names as $n => $label): ?>
            <label><input type="checkbox" name="weekdays[]" value="<?= $n ?>" <?= in_array($n, $days, true) ? 'checked' : '' ?>><?= $label ?></label>
          <?php endforeach; ?>
        </div>
        <div class="kf-hint">一个都不勾 = 每天都算工作日</div>
      </div>
      <div class="form-item">
        <label>上班时间</label>
        <input type="time" name="work_start" value="<?= e($cfg['work_start']) ?>">
      </div>
      <div class="form-item">
        <label>下班时间</label>
        <input type="time" name="work_end" value="<?= e($cfg['work_end']) ?>">
        <div class="kf-hint">下班时间小于上班时间表示跨零点</div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head"><span>话术</span></div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-item">
        <label>欢迎语（面板里最先看到的一段话）</label>
        <textarea name="welcome" style="min-height:90px"><?= e($cfg['welcome']) ?></textarea>
      </div>
    </div>
    <div class="form-row">
      <div class="form-item">
        <label>离线提示（非工作时间显示）</label>
        <textarea name="offline_tip" style="min-height:70px"><?= e($cfg['offline_tip']) ?></textarea>
      </div>
    </div>
    <div class="form-row">
      <div class="form-item">
        <label>收到留言后的自动回复</label>
        <textarea name="auto_reply" style="min-height:70px"><?= e($cfg['auto_reply']) ?></textarea>
        <div class="kf-hint">访客第一次提交时自动发给他，留空则不发</div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head"><span>留言与对话规则</span></div>
  <div class="card-body">
    <div class="form-row">
      <div class="form-item">
        <label>在线对话</label>
        <select name="allow_chat">
          <option value="1" <?= $cfg['allow_chat'] === '1' ? 'selected' : '' ?>>开启（访客可发消息，客服后台回复）</option>
          <option value="0" <?= $cfg['allow_chat'] === '0' ? 'selected' : '' ?>>关闭（只显示坐席名片）</option>
        </select>
      </div>
      <div class="form-item">
        <label>必须留联系方式才能发送</label>
        <select name="require_contact">
          <option value="1" <?= $cfg['require_contact'] === '1' ? 'selected' : '' ?>>是（推荐，方便回访）</option>
          <option value="0" <?= $cfg['require_contact'] === '0' ? 'selected' : '' ?>>否（匿名也能发）</option>
        </select>
      </div>
      <div class="form-item">
        <label>新消息轮询间隔（秒）</label>
        <input type="number" name="poll" value="<?= (int) $cfg['poll'] ?>" min="3" max="60">
        <div class="kf-hint">越小越即时，越大越省服务器</div>
      </div>
    </div>

    <div class="form-row">
      <div class="form-item">
        <label>单条消息字数上限</label>
        <input type="number" name="max_len" value="<?= (int) $cfg['max_len'] ?>" min="80" max="2000">
      </div>
      <div class="form-item">
        <label>匿名访客默认称呼</label>
        <input type="text" name="guest_default" value="<?= e($cfg['guest_default']) ?>">
      </div>
      <div class="form-item">
        <label>显示坐席名片</label>
        <select name="show_staff">
          <option value="1" <?= $cfg['show_staff'] === '1' ? 'selected' : '' ?>>显示</option>
          <option value="0" <?= $cfg['show_staff'] === '0' ? 'selected' : '' ?>>不显示（只留对话）</option>
        </select>
      </div>
      <div class="form-item">
        <label>显示二维码按钮</label>
        <select name="show_qr">
          <option value="1" <?= $cfg['show_qr'] === '1' ? 'selected' : '' ?>>显示</option>
          <option value="0" <?= $cfg['show_qr'] === '0' ? 'selected' : '' ?>>不显示</option>
        </select>
      </div>
    </div>
  </div>
</div>

<div style="display:flex;gap:10px">
  <button type="submit" class="btn btn-primary">保存设置</button>
  <a class="btn" href="/admin/plugin/kefu">返回坐席</a>
  <a class="btn" href="/admin/plugin" style="margin-left:auto">插件列表</a>
</div>
</form>

<?php include __DIR__ . '/../../../app/view/admin/_footer.php'; ?>
