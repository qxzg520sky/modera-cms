<?php
$nav     = $nav ?? [];
$cat     = $cat ?? null;
$pages   = $pages ?? \think\facade\Db::name('page')->where('status', 1)->order('id asc')->select()->toArray();
$curSlug = (string) ($cat['slug'] ?? '');
$root    = null;
if ($cat) {
    foreach ($nav as $c) {
        if ((int) $c['id'] === (int) ($cat['id'] ?? 0)) {
            $root = $c;
            break;
        }
        foreach ($c['children'] as $ch) {
            if ((int) $ch['id'] === (int) ($cat['id'] ?? 0)) {
                $root = $c;
                break 2;
            }
        }
    }
}
?>
<aside class="gt-aside">
  <div class="gt-anav">
    <?php if ($root): ?>
      <h3><?= e($root['name']) ?></h3>
      <ul>
        <li class="<?= $curSlug === $root['slug'] ? 'on' : '' ?>">
          <a href="<?= e(lang_path('/c/' . $root['slug'])) ?>"><?= e($root['name']) ?><span><?= e('全部') ?></span></a>
        </li>
        <?php foreach ($root['children'] as $ch): ?>
          <li class="<?= $curSlug === $ch['slug'] ? 'on' : '' ?>">
            <a href="<?= e(lang_path('/c/' . $ch['slug'])) ?>"><?= e($ch['name']) ?><span>›</span></a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <h3><?= e(lang('栏目导航')) ?></h3>
      <ul>
        <?php foreach ($nav as $c): ?>
          <li class="<?= $curSlug === $c['slug'] ? 'on' : '' ?>">
            <a href="<?= e(lang_path('/c/' . $c['slug'])) ?>"><?= e($c['name']) ?><span>›</span></a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="gt-acontact">
    <h3><?= e(lang('联系方式')) ?></h3>
    <p class="gt-ac-tel"><?= e(setting('site_phone', '')) ?></p>
    <p><?= e(setting('site_address', '')) ?></p>
    <p><?= e(setting('site_email', '')) ?></p>
    <a class="gt-btn gt-btn-sm" href="<?= e(lang_path('/p/contact')) ?>"><?= e(lang('在线咨询')) ?></a>
  </div>

  <?php if ($pages): ?>
    <div class="gt-anav gt-anav-page">
      <h3><?= e(lang('关于我们')) ?></h3>
      <ul>
        <?php foreach ($pages as $p): ?>
          <li><a href="<?= e(lang_path('/p/' . $p['slug'])) ?>"><?= e($p['title']) ?><span>›</span></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
</aside>
