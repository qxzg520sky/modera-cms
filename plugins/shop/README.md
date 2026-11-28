# 企业商城插件（plugins/shop）开发文档

> 配套插件：`plugins/member`（企业会员管理）
> 适用：ThinkPHP 8.1 + MySQL 5.7/8.0，企业 CMS 插件中心安装
> 版本：1.0.0

## 1. 这套商城到底解决了什么问题

一句话：**商城是独立插件，会员是独立中台。商城只存商品、库存、订单、售后、优惠券；会员资料、等级、余额、积分、地址、累计消费全部在会员插件里。**

商城永远不执行下面这类 SQL：

```sql
-- 禁止
SELECT * FROM tp_member WHERE id = ?;
UPDATE tp_member SET balance = balance - ?;
```

所有会员能力只有两个入口：

1. `service` 模式（默认，同站推荐）：调用 `plugins\member\service\Member` 的公开方法。
2. `api` 模式：调用会员插件 `/member/api/*`，HMAC-SHA256 签名。

统一封装在 `plugins/shop/service/MemberBridge.php`。以后会员表加字段、改等级规则、换存储，商城不用跟着改，只要会员插件的方法签名不变。

### 架构

```
浏览器/小程序
   │
   ├── /shop/*            商城前台（复用会员 session）
   ├── /shop/staff/orders 业务员订单（复用业务员 session）
   ├── /admin/plugin/shop 商城后台（管理员）
   └── /shop/api/*        商城对外 API（HMAC 签名）
              │
        controller（控制器，只做参数/权限/响应）
              │
        service / logic（业务规则、事务、幂等）
              │
        MemberBridge ──► Member 服务类 或 /member/api/*
              │
        shop_* 表（商城自己的数据）
```

## 2. 目录结构

```
plugins/shop/
├── plugin.json                 插件清单（模块中心识别、后台菜单）
├── install.php                 安装：建 10 张表 + 生成 API Secret + 演示商品
├── uninstall.php               卸载：只删 shop_* 表，不碰会员数据
├── route.php                   前台/后台/业务员/API 路由
├── sql/install.sql             安装 SQL（__PREFIX__ 自动替换）
├── hook/
│   └── front_footer.php        前台导航入口：插到主导航第 2 位，窄屏自动缩为图标
├── controller/
│   ├── Admin.php               后台：商品/分类/SKU/订单/售后/优惠券/设置
│   ├── Front.php               前台：列表/详情/购物车/结算/订单/售后
│   ├── Staff.php               业务员：只看自己名下会员订单
│   └── Api.php                 对外 API + 签名校验
├── service/
│   ├── Shop.php                配置、建表、统计、日志、状态文案
│   ├── MemberBridge.php        会员插件桥接（唯一会员入口）
│   └── Excel.php               订单 XLSX 导出（不依赖 PhpSpreadsheet）
├── logic/
│   └── OrderLogic.php          下单/支付/退款/关单/累计消费同步
├── model/                      模型层（关联查询与扩展；核心写操作仍用 Db 门面）
│   ├── Product.php / Sku.php / Category.php
│   └── Order.php / OrderItem.php / OrderLog.php / AfterSale.php
├── view/
│   ├── admin/                  后台 10 个页面
│   ├── front/                  前台 9 个页面
│   └── staff/orders.php        业务员订单页
└── asset/
    ├── admin.css
    └── shop.css
```

## 3. 数据表（10 张，全部 shop_ 前缀）

> 实际表名 = 站点表前缀 + 表名，例如 `tp_shop_order`。

| 表 | 作用 | 关键字段/索引 |
|---|---|---|
| `shop_category` | 商品分类 | `pid`、`name`、`sort`、`status` |
| `shop_product` | 商品主表 | `category_id`、`price`、`stock`、`status`、`sales` |
| `shop_sku` | 多规格 SKU 与库存 | `sku_code` 唯一、`spec_json`、`price`、`stock`、`warn_stock` |
| `shop_cart` | 购物车 | `member_id + sku_id` 唯一，只存会员 ID 不存会员资料 |
| `shop_order` | 订单主表 | `order_no` 唯一、`biz_no` 唯一、`member_id`、`staff_id`、`spend_state`、`spend_before`、`stock_returned` |
| `shop_order_item` | 订单明细快照 | 商品标题/规格/单价/数量，退款不依赖商品表 |
| `shop_after_sale` | 售后申请 | `after_sale_no` 唯一、`order_id`、`status` |
| `shop_coupon` | 优惠券模板 | `code` 唯一、`type(fixed/percent)`、`value`、`min_amount` |
| `shop_coupon_member` | 券发放/使用记录 | `coupon_id + member_id` 唯一、`status`、`order_no` |
| `shop_order_log` | 订单操作日志 | `order_id`、`action`、`operator_type/id`、`content` |

### 订单关键字段说明

```text
order_no        商城订单号，唯一，展示用
biz_no          客户端幂等号，唯一；重复提交直接返回原订单
status          pending_payment / paying / paid / shipped / completed / closed / refunded
pay_status      0 未支付，1 已支付
goods_amount    商品原始金额
member_discount 会员等级优惠金额
coupon_amount   优惠券金额
points_used     抵扣积分数
points_amount   积分折算金额
payable_amount  最终应付
balance_amount  实际余额支付金额
discount_rate   下单时会员折扣快照，例如 0.95
spend_state     0 未同步 / 1 调用中 / 2 已同步 / 3 结果不确定
spend_before    调用 addSpend 前会员的 total_spent 快照，用于对账
stock_returned  库存是否已回滚，防重复回库
```

## 4. 安装与启用

1. 把 `shop` 目录放到 `plugins/shop`。
2. 后台「插件扩展 / 模块中心」启用 `shop`，会执行 `install.php`：
   - 建 10 张 `shop_*` 表；
   - 生成商城 API `api_secret`；
   - 写入演示分类、商品、两个 SKU（商品表为空时才写）。
3. 前台入口：`/shop`。
4. 后台入口：`/admin/plugin/shop`。
5. 会员插件必须启用，否则商城无法登录/结算。会员插件入口：`/admin/plugin/member`。

### 商城设置（后台 → 商城设置）

| 配置 | 默认 | 说明 |
|---|---|---|
| `member_mode` | `service` | `service` 调服务类；`api` 走 `/member/api/*` |
| `member_api_base` | 空 | API 模式下的站点地址，例如 `http://tempqiyecms`；空则用当前域名 |
| `member_appid` / `member_secret` | `modera-mall` / 空 | 空 Secret 会自动读取会员插件配置 |
| `points_rate` | 100 | 多少积分抵 1 元 |
| `points_max_percent` | 30 | 积分最多抵订单金额的百分比 |
| `discount_stack` | 1 | 1=会员折扣与券叠加；0=用券时不再享会员折扣 |
| `balance_pay` | 1 | 是否允许余额支付（当前版本只实现余额支付） |
| `api_appid` / `api_secret` | `shop-client` / 随机 | 商城对外 API 密钥 |
| `api_ip_whitelist` | 空 | 对外 API IP 白名单 |

> 安全：`api_secret` 只放服务端，绝不下发到前端或小程序客户端。

## 5. 后台功能清单

| 页面 | 路径 | 功能 |
|---|---|---|
| 商城总览 | `/admin/plugin/shop` | 商品/订单/金额/待付款/售后/库存预警、最近订单、会员插件状态 |
| 商品管理 | `/products` | 列表、筛选、上下架、删除、新增/编辑 |
| 商品编辑 | `/product/edit/:id` | 标题、价格、库存、封面、轮播图、详情、多 SKU（编码/规格/价格/库存/预警） |
| 商品分类 | `/categories` | 新增/修改/删除（有商品的分类禁止删除） |
| 库存管理 | `/skus` | SKU 列表、只看预警、+库存/设为库存 |
| 订单管理 | `/orders` | 按订单号/会员/手机/状态/业务员/累计消费状态筛选 |
| 订单详情 | `/order/:no` | 明细、金额、收货、会员实时资产、日志、改价、发货、关单、退款、累计消费对账 |
| 售后审核 | `/aftersales` | 同意（立即退款并回滚库存）/拒绝 |
| 优惠券 | `/coupons` | 新建满减/折扣券、按会员 ID 发放 |
| 商城设置 | `/settings` | 基础、支付抵扣、会员对接、对外 API 密钥 |
| 订单导出 | `/order/export` | XLSX，最多 5000 条，含订单/会员/金额/物流/累计消费状态 |

## 6. 会员前台页面

### 前台入口

商城入口由 `hook/front_footer.php` 输出，插到主导航「网站首页」之后，不修改主题模板。停用插件后入口自动消失，不会残留死链。

- 1920/1366 宽屏：显示「购物袋图标 + 商城」；
- 981–1100px：自动收起文字，只留购物袋图标（约 40px），避免挤压搜索框和语言切换；
- 980px 以下：跟随主题移动菜单，排在「网站首页」之后。

已适配 4 套前台主题：

| 主题 | 桌面导航 | 移动菜单 |
|---|---|---|
| `gangtie` 工业蓝 | `.gt-menu` 第 2 项 | `.gt-menu` 展开菜单 |
| `gangtie2` 工业橙 | `.g2-menu` 第 2 项 | `.g2-menu` 展开菜单 |
| `shiwei` 视维蓝 | `.sw-nav` 第 2 项 | `.sw-mobile-in` 第 2 项 |
| `touzi` 投资蓝 | `.tz-nav` 第 2 项 | 跟随主题现有移动菜单 |

`gangtie2` 还有一个单独处理：白色主导航右侧的「获取报价」按钮在栏目变多时会被 flex 压缩成竖排文字。钩子给它加了 `white-space:nowrap` + `flex:0 0 auto`，同时把桌面菜单间距从 30px 收到 22px；1199px 以下隐藏搜索框，保证按钮宽度稳定在 106px。实测 1440、1200、1024 三档都不换行、不溢出。

入口是静态 HTML + 一段插入脚本，不包含会员登录态，可以安全进入整页缓存。商城页仍然走自己的 `/shop/*` 路由，不依赖主题模板。

| 页面 | 路径 | 说明 |
|---|---|---|
| 商品列表 | `/shop` | 搜索、分类、销量/价格排序、分页；登录后显示等级价参考 |
| 商品详情 | `/shop/product/:id` | 轮播、SKU 选择、库存、加购 |
| 购物车 | `/shop/cart` | 勾选、改数量、删除、合计 |
| 结算页 | `/shop/checkout` | 自动读会员地址、等级折扣、余额、积分；券码、积分抵扣试算 |
| 创建订单 | `POST /shop/order/create` | 锁库存、写订单、清购物车、占券 |
| 订单详情 | `/shop/order/:no` | 金额明细、物流、日志、余额支付、售后入口 |
| 我的订单 | `/shop/orders` | 按状态筛选 |
| 申请售后 | `/shop/after-sale/:no` | 仅退款/退货退款，提交后待审核 |
| 我的售后 | `/shop/after-sales` | 售后进度 |

未登录访问购物车/结算会跳 `/member/login?redirect=...`，登录态复用会员插件 `session('member_id')`，不另建账号。

### 结算计算顺序

```
商品金额
  → 会员等级折扣（按 member.discount 快照）
  → 优惠券（满减或折扣）
  → 积分抵扣（不超过订单金额的 points_max_percent）
  → 应付金额
```

不叠加模式（`discount_stack=0`）：使用优惠券时不再计算会员折扣，避免“折上折”失控。

## 7. 下单/支付/退款完整流程

```
① 创建订单
   校验会员（MemberBridge::get）
   锁 SKU 行（lock(true)）
   校验 stock >= qty
   扣 SKU 与商品总库存
   写 shop_order / shop_order_item
   占用优惠券
   清购物车
   订单状态 pending_payment

② 支付
   订单行锁，pending_payment → paying（原子占位，防并发重复扣款）
   有积分：会员 points/change 扣积分（biz_no = order_no + '-points'）
   有余额：会员 balance/change 扣余额（biz_no = order_no + '-balance'）
   订单 → paid / pay_status=1 / 记录 spend_before
   调用会员 addSpend（spend_state 控制）

③ 累计消费同步
   spend_state 0→1 抢占调用权
   调 Member::addSpend(member_id, payable_amount, order_no)
   成功 → 2；异常/超时 → 3（不确定，禁止自动重试）

④ 关闭订单
   仅待付款
   回滚库存（stock_returned=1）
   退回优惠券
   状态 closed

⑤ 退款
   仅 paid/shipped/completed
   回滚库存（一次）
   退积分（biz_no = order_no + '-refund-points'）
   退余额（biz_no = order_no + '-refund-balance'）
   状态 refunded；累计消费不回退
```

## 8. 接口清单

### 8.1 商城对外 API（HMAC 签名）

请求头与会员插件完全一致：

```
X-Member-Appid: shop-client
X-Member-Timestamp: 1764123456
X-Member-Nonce: 随机 8~64 位字母数字
X-Member-Sign: HMAC-SHA256(secret, appid + "\n" + timestamp + "\n" + nonce + "\n" + rawBody)
```

| 方法 | 路径 | 参数 | 说明 |
|---|---|---|---|
| GET | `/shop/api/health` | - | 健康检查，返回会员插件是否可用 |
| GET | `/shop/api/products` | `keyword/category_id/page/size` | 商品列表，含 SKU |
| GET | `/shop/api/product/:id` | - | 商品详情，含 SKU |
| POST | `/shop/api/order/create` | JSON：`member_id`、`biz_no`、`cart_ids`、`address_id`、`coupon_code`、`use_points` | 创建订单 |
| POST | `/shop/api/order/pay` | JSON：`member_id`、`order_no`、`pay_type` | 余额支付 |
| GET | `/shop/api/order/:no` | `member_id` | 订单详情（校验归属） |

签名失败返回 HTTP 401；nonce 5 分钟防重放；限流 300 次/分钟/IP 维度简化实现。

#### 签名示例（PHP）

```php
$appid  = 'shop-client';
$secret = '后台商城设置里的 api_secret';
$raw    = json_encode(['member_id' => 2, 'biz_no' => 'B202611260001', 'address_id' => 2], JSON_UNESCAPED_UNICODE);
$ts     = time();
$nonce  = bin2hex(random_bytes(12));
$sign   = hash_hmac('sha256', $appid . "\n" . $ts . "\n" . $nonce . "\n" . $raw, $secret);

$ch = curl_init('http://tempqiyecms/shop/api/order/create');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $raw,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-Member-Appid: ' . $appid,
        'X-Member-Timestamp: ' . $ts,
        'X-Member-Nonce: ' . $nonce,
        'X-Member-Sign: ' . $sign,
    ],
]);
$ret = json_decode(curl_exec($ch), true);
```

### 8.2 内部服务调用（service 模式）

```php
use plugins\shop\service\MemberBridge;
use plugins\shop\logic\OrderLogic;

// 会员信息（只读，返回 publicMember 字段）
$member = MemberBridge::get($memberId);

// 等级折扣
$discount = MemberBridge::discount($memberId, 1000);

// 余额变动：正数入账、负数出账，必须带 biz_no
MemberBridge::balanceChange($memberId, -95.00, 'consume', 'S202611260001-balance', '商城余额支付');

// 积分变动：正数赠送、负数扣减
MemberBridge::pointsChange($memberId, -100, 'consume', 'S202611260001-points', '商城积分抵扣');

// 累计消费（会触发会员自动升级）
MemberBridge::addSpend($memberId, 95.00, 'S202611260001');
```

> `MemberBridge` 内部会自动选择 service 或 api 模式；业务代码不用判断。

### 8.3 会员插件本身的接口（商城可调用）

| 方法 | 路径 | 用途 |
|---|---|---|
| GET | `/member/api/health` | 健康检查 |
| POST | `/member/api/member/get` | 会员信息 |
| POST | `/member/api/discount` | 等级折扣 |
| POST | `/member/api/balance/change` | 余额变动 |
| POST | `/member/api/points/change` | 积分变动 |
| POST | `/member/api/spend/record` | 累计消费 |
| POST | `/member/api/address/list` | 收货地址 |
| POST | `/member/api/orders` | 会员在其它系统的订单（商城暂未写入） |

会员 API 的 `balance/change` 用 `amount + direction(in/out)`；`points/change` 用带符号的 `points`。

## 9. 重点风险与踩坑（必须看）

### 9.1 `addSpend` 没有幂等表 —— 最大的坑

会员插件 `Member::addSpend($memberId, $amount, $orderNo)` 只累加 `total_spent` 和 `order_count`，**不保存 order_no，也没有唯一索引**。同一个订单调用两次，累计消费就加两次，会员可能被“刷”到更高等级。

商城侧的三层防护：

1. 订单 `biz_no` 唯一，重复提交返回原订单。
2. 支付前把订单原子更新为 `paying`，防止并发重复扣款。
3. `spend_state` 闸门：
   - `0 未同步`：可以调用；
   - `1 调用中`：别人正在调，跳过；
   - `2 已同步`：不再调用；
   - `3 不确定`：**禁止自动重试**，后台人工对账。

对账逻辑（后台订单详情「累计消费对账」）：

```
目标 = spend_before + payable_amount
读取会员当前 total_spent
若 total_spent >= 目标 → 认为已到账，只把 spend_state 改成 2，不再调用
否则才允许强制调用一次
```

**这个对账是启发式的**：如果该会员在两次对账之间有其它订单也增加了累计消费，`total_spent` 可能提前达到目标，从而误判“已到账”。最稳的做法是：**同一会员同一时间只允许一个待同步订单**，或让会员插件后续补一张 `member_spend_log(order_no unique)`。本商城已在文档和后台提示，但没有替会员插件改表。

### 9.2 退款不会减少累计消费

会员插件没有“减少 total_spent”的接口，退款只能退回余额/积分，**累计消费和等级不会回退**。这是业务边界，不是 bug。若业务要求退款后降级，需要会员插件新增 `reduceSpend` 或后台手动重算等级。

### 9.3 跨插件没有共享事务

商城 `shop_*` 和会员 `member_*` 在不同插件的表里，不能用一个数据库事务包住。当前策略：

- 支付前先 `pending_payment → paying`，失败可回到待付款；
- 余额/积分扣减用会员流水 `biz_no` 唯一做幂等；
- 如果积分扣成功、余额扣失败，立即用新的 `biz_no` 退回已扣部分；
- 退款同理，先退资产再改订单状态；重复退款会被会员流水唯一索引拒绝，代码按“已处理”容错。

**任何跨插件写操作都必须带业务单号，且单号可推导、可重放。** 这是整套系统能对账的基础。

### 9.4 库存超卖

下单时：

```php
$sku = Db::name('shop_sku')->where('id', $skuId)->lock(true)->find();
if ($sku['stock'] < $qty) throw new RuntimeException('库存不足');
Db::name('shop_sku')->where('id', $skuId)->where('stock', '>=', $qty)
    ->update(['stock' => Db::raw('stock-' . $qty)]);
```

`lock(true)` 行锁 + 条件更新 + 事务，保证同一 SKU 并发下单不会扣成负数。关单/退款用 `stock_returned` 标记，防止重复回库。

仍然存在的边界：**商品总库存是冗余字段**，只用于列表展示；真实扣减以 SKU 为准。后台改商品库存不会重算 SKU，运营需要分别维护。

### 9.5 优惠券

- 发券：`shop_coupon_member(coupon_id, member_id)` 唯一，重复发放会跳过。
- 用券：下单即占用（`status=1`），关单退回（`status=0`），退款不退回。
- 折扣券 `value` 填 0~1 之间，例如 `0.9` 表示 9 折；填 10 会校验失败。
- 不叠加模式：用券时会员折扣归零，避免折上折。

### 9.6 页面缓存与登录态

商城前台不写入 CMS 首页缓存，`/shop` 不在页面缓存白名单；但会员插件首页钩子用 `/member/status` 动态渲染，商城登录态复用同一个 session。如果以后给商城加首页钩子，**不要把会员姓名/余额直接写进缓存 HTML**。

### 9.7 后台视图变量被 `_header.php` 覆盖

后台公共头部内部用了 `$items` 变量遍历菜单。任何后台视图如果再传 `$items`，include 之后会被覆盖成菜单数组（本次已踩：订单详情商品明细全空 + `Undefined array key "product_title"`）。商城订单详情已改用 `$orderItems`。后续写后台页面时，**不要用 `$items`、`$groups`、`$item` 作为业务变量名**。

### 9.8 Excel 导出

`Excel::export()` 用 ZipArchive + OOXML，单元格 `inlineStr`，订单号/手机号不会丢前导 0。PHP 必须启用 `zip` 扩展，否则导出会抛“无法创建 Excel 文件”。

### 9.9 业务员权限

业务员页面 `/shop/staff/orders` 复用会员插件 `session('member_staff_id')`，SQL 强制 `staff_id = 当前业务员`。业务员不能改价、发货、退款，这些操作只给管理员。下单时 `shop_order.staff_id` 取会员资料里的 `staff_id` 快照，之后会员改归属不会影响历史订单统计。

## 10. 已验证的测试场景

在本机 `tempqiyecms` 实测通过：

- 会员 `admintest1`（金卡，0.95 折，余额 600，积分 150）：
  - 加购 2 件 100 元商品 → 会员折扣 10 元 → 应付 190；
  - 余额支付成功：余额 600 → 410，余额流水 `consume`，累计消费 6000 → 6190，`spend_state=2`；
  - 重复支付、重复 biz_no 提交：订单数仍为 1，余额不再变化；
  - 后台退款：余额回到 600，库存回滚，订单 `refunded`，累计消费仍 6190（符合边界）。
- 优惠券 `TEST10`（满 50 减 10）+ 150 积分：
  - 商品 100 → 会员折扣 5 → 券 10 → 积分 1.5 → 应付 83.5；
  - 支付后积分 150 → 0，余额 600 → 516.5，券状态已使用；
  - 售后审核通过：积分/余额退回，库存 +1，订单 refunded。
- 待付款订单后台关闭：库存回滚，`stock_returned=1`。
- 订单 Excel 导出：生成合法 xlsx（5 个 OOXML 部件）。
- 商城 API：正确签名 200；无签名 401；订单查询按 `member_id` 校验归属。
- `member_mode=api`：结算页通过 `/member/api/*` 成功读取会员折扣与余额。
- 业务员 `sales01`：`/shop/staff/orders` 只显示自己名下会员订单。
- 前台导航：`gangtie`、`gangtie2`、`shiwei` 三套主题均实测有「商城」入口，桌面第 2 位、移动菜单第 2 位，点击跳转 `/shop` 正常；`gangtie2` 的「获取报价」在 1440/1200/1024 三档均为单行 106px，不再竖排；清除 `runtime/pagecache` 后默认首页也能直接看到入口。

## 11. 博客教程建议结构

1. 为什么要把会员和商城拆成两个插件（一张图讲清边界）。
2. 会员中台怎么设计：`member` 表 + 资产流水 + 等级规则。
3. 商城表怎么设计：为什么订单要存金额快照、折扣快照、地址快照。
4. 下单事务与库存行锁：从 SQL 到代码。
5. 跨插件一致性：余额/积分流水 biz_no 幂等。
6. **最难的一节：addSpend 没有幂等表怎么办**——biz_no、paying 占位、spend_state、人工对账四道闸。
7. 退款为什么不能回退累计消费：接口边界与业务妥协。
8. 后台视图变量被公共头覆盖的坑（真实报错记录）。
9. 业务员权限只靠一条 `staff_id` 条件够不够。
10. 联调清单：Postman 签名、Excel 校验、并发下单脚本。

## 12. 后续可扩展

- 会员插件补 `member_spend_log(order_no unique)` 后，商城可删除启发式对账，改成严格幂等。
- 增加微信/支付宝支付：`pay_type` 已留字段，`pay()` 只实现了余额。
- 订单超时自动关闭：`auto_close_minutes` 已留配置，需挂定时任务扫描 `pending_payment`。
- 商品图片上传：当前只支持 URL，可接后台文件上传组件。
- 售后部分退款/部分退货：当前只做整单退款，`shop_after_sale.amount` 已预留。
