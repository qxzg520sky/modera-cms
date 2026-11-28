<?php $shopCfg = $shopCfg ?? []; ?>
</main>
<footer class="sp-foot">
  <?= e($shopCfg['shop_name'] ?? '企业商城') ?> · 会员资产由会员插件统一管理 · <a href="/member/center">会员中心</a> · <a href="/shop">返回商城</a>
</footer>
</body>
</html>
