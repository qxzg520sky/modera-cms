<?php
/**
 * 企业商城插件：安装钩子
 * 创建 10 张 shop_* 表、生成对外 API 密钥、写入演示商品。
 * 不会创建或修改任何 member_* 表。
 */

return \plugins\shop\service\Shop::install();
