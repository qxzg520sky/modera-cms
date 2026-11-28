# 企业会员管理插件（member）

面向自研企业管理系统 / 商城系统的独立会员中台。插件不直接管理商品和订单，只负责会员身份、等级、余额、积分、地址、业务员归属与资产流水。

> 设计原则：**商城只调用会员接口，不直接操作会员表。**  
> 会员插件与商城解耦，商城可以随时替换，会员资产数据仍独立、可对账。

---

## 1. 安装与访问

| 项目 | 值 |
|---|---|
| 插件目录 | `plugins/member` |
| 后台入口 | `/admin/plugin/member` |
| 会员中心 | `/member` |
| 会员登录 | `/member/login` |
| 业务员登录 | `/member/staff/login` |
| API 根路径 | `/member/api` |

安装方式：

1. 后台「模块中心 → 插件扩展」启用「企业会员管理」，启用时自动执行 `install.php`；
2. 或命令行调用 `PluginManager::scan()` + `PluginManager::toggle('member', 1)`；
3. 安装会创建 6 张表、写入 4 个默认等级、生成 API Secret。

安装后清一次页面缓存：`runtime/pagecache/*.html`。

---

## 2. 数据表（MySQL）

表前缀跟随系统配置，当前为 `tp_`。

### 2.1 `tp_member` 会员主表

| 字段 | 类型 | 说明 |
|---|---|---|
| id | int unsigned PK | 主键 |
| member_no | varchar(32) unique | 会员编号，自动生成 |
| username | varchar(50) unique | 登录用户名 |
| mobile | varchar(20) unique | 手机号，登录主账号 |
| email | varchar(100) | 邮箱 |
| password_hash | varchar(255) | `password_hash()` 加密，绝不存明文 |
| real_name / company | varchar | 姓名 / 公司 |
| gender / birthday / avatar | tinyint / date / varchar | 资料 |
| level_id | int | 会员等级 |
| staff_id | int | 归属业务员，0=未分配 |
| balance | decimal(12,2) | 余额 |
| points | int | 积分 |
| total_spent | decimal(12,2) | 累计消费，用于自动升级 |
| order_count | int | 订单数，由商城回写 |
| status | tinyint | 1 正常 / 0 停用 |
| source | varchar(20) | admin / register / import / api |
| remark | varchar(255) | 后台内部备注 |
| register_ip / last_login_at / last_login_ip | | 安全审计 |
| created_at / updated_at | int | 时间戳 |

索引：`uk_member_no`、`uk_username`、`uk_mobile`、`idx_level`、`idx_staff`、`idx_status`、`idx_spent`。

### 2.2 `tp_member_level` 会员等级

| 字段 | 说明 |
|---|---|
| name / code | 等级名称 / 唯一标识 |
| min_spent | 升级门槛（累计消费） |
| discount | 折扣，0.01–1.00；0.95 表示 9.5 折 |
| points_rate | 积分倍率，供商城计算积分 |
| is_default | 默认等级，新会员使用；只能有一个 |
| sort / status / description | 排序 / 启停 / 权益说明 |

默认等级：普通会员（0，1.00）、银卡（1000，0.98）、金卡（5000，0.95）、黑卡（20000，0.90）。

### 2.3 `tp_member_balance_log` 余额流水

| 字段 | 说明 |
|---|---|
| member_id | 会员 |
| change_amount | 本次变动，正增负减 |
| before_balance / after_balance | 变动前后余额 |
| type | init / recharge / consume / refund / withdraw / adjust |
| biz_type | 业务来源，如 mall / admin / create |
| biz_no | 业务单号，**唯一索引**，用于幂等 |
| operator_type / operator_id | system / admin / staff / member / api |
| remark / ip / created_at | 备注、IP、时间 |

### 2.4 `tp_member_points_log` 积分流水

字段与余额流水对应：`change_points`、`before_points`、`after_points`；`biz_no` 同样唯一。类型：init / gift / consume / deduct / refund / adjust。

### 2.5 `tp_member_address` 收货地址

`member_id, receiver, mobile, province, city, district, detail, postcode, is_default, created_at, updated_at`。  
索引：`idx_member`、`idx_default(member_id,is_default)`。

### 2.6 `tp_member_staff` 业务员

`username unique, password_hash, real_name, mobile, role, status, remark, created_at, updated_at`。  
业务员是插件自己的账号体系，不复用后台管理员。

> 完整建表 SQL 在 `service/Member.php::install()`，字段与索引以代码为准。

---

## 3. 权限模型

| 角色 | 登录入口 | 数据范围 | 能做什么 |
|---|---|---|---|
| 管理员 | `/admin` 现有后台 | 全部会员 | 新增/编辑会员、调余额积分、等级、业务员、日志、导入导出、设置 |
| 业务员 | `/member/staff/login` | `staff_id = 自己` | 查看名下会员、看资产流水、写跟进备注；**不能改余额积分** |
| 会员客户 | `/member/login` | `session.member_id` | 看自己的资产、订单、地址、资料；改密码；不能看其他会员 |

三套 session 键：`admin_id`、`member_staff_id`、`member_id`，互不混用。

业务员越权访问 `/member/staff/member/{id}` 时，只要 `staff_id` 不匹配就直接拒绝并回工作台，不暴露会员是否存在。

---

## 4. 后台功能清单

- **会员总览**：会员数、今日新增、余额/积分合计、未分配数、等级分布、消费 TOP、最近资产流水。
- **会员列表**：关键词、等级、业务员、状态、是否分配筛选；分页；导出全部 Excel。
- **新增/编辑会员**：手机号、用户名、姓名、公司、邮箱、性别、生日、等级、业务员、状态、累计消费、密码、备注；新增时可填初始余额/积分。
- **会员详情**：资产总览、资料、地址、余额调整、积分调整、强制按累计消费重算等级、余额/积分双流水表。
- **会员等级**：增删改、折扣、门槛、积分倍率、默认等级；默认等级不可删，有会员占用的等级不可删。
- **业务员**：增删改、启停、名下会员数；删除后会员自动变为未分配。
- **资产日志**：余额/积分切换，按会员关键词、类型筛选，分页。
- **导入导出**：下载 xlsx 模板、上传 xlsx/csv 导入（最多 500 行）、导出全部会员。
- **会员设置**：功能开关、注册开关、自动升级、页脚入口、注册赠分、分页、积分汇率、默认密码、API AppID/Secret、IP 白名单。

---

## 5. 前台会员中心逻辑

路由与页面：

| 页面 | 路径 | 逻辑 |
|---|---|---|
| 登录 | `/member/login` | 手机号/用户名/邮箱 + 密码，`password_verify` |
| 注册 | `/member/register` | 受 `allow_register` 控制；注册赠送积分写 `gift` 流水 |
| 会员中心 | `/member/center` | 等级、折扣、余额、积分、累计消费、最近流水、最近订单 |
| 我的订单 | `/member/orders` | 通过订单桥接类读取商城订单，不查会员表以外的订单表 |
| 收货地址 | `/member/address` | 增删改、默认地址；第一条自动默认 |
| 资产明细 | `/member/logs?kind=balance\|points` | 分页流水，含变动前后、业务单号、操作人 |
| 会员资料 | `/member/profile` | 改资料、改密码；手机号只读，需管理员修改 |

所有页面只使用当前 session 的 `member_id`，不接受 URL 传入其他会员 id。

页脚入口：`hook/front_footer.php` 统一输出前台会员入口，受 `footer_link` 开关控制；主题模板已有 `plugin_hook('front_footer')` 即自动生效。插件停用后 `plugin_hook()` 不再加载该文件，前台不会残留任何会员入口。

入口形态：

- 桌面端：登录后显示「头像 + 姓名 + 等级」下拉菜单，含会员中心、订单、地址、资产、资料、退出；游客显示「登录 / 注册」。
- 导航较满的模板（如工业橙 `gangtie2`）：会员入口自动放到顶部黑色联系条右侧、语言切换前面，不再塞进主菜单，避免把「产品中心、解决方案」等导航文字挤成竖排。
- 移动端：自动插入汉堡菜单（`touzi` 的 `.tz-mobile-in`、`gangtie2` 的 `#g2Menu`），并显示左下角浮动快捷入口；游客为「登录」，会员为姓名入口。
- 其它主题：没有 `.tz-nav` / `.g2-nav-act` 时降级为右上角浮动菜单。

**关键点：入口不把登录态写进 HTML。** 页面本身有整页缓存，若服务端直接按 session 输出菜单，会把“游客状态”缓存给已登录会员。因此钩子只输出空挂载点，由 JS 请求 `GET /member/status`（同源、no-store）拿到当前登录态后再渲染；该接口只返回姓名、等级、余额、积分，不返回敏感字段，也不需要 API 签名。

---

## 6. 对外 API

### 6.1 签名

请求头（大小写不敏感，建议小写）：

```
X-Member-Appid: modera-mall
X-Member-Timestamp: 1760000000
X-Member-Nonce: abc12345xyz
X-Member-Sign: <hex hmac>
```

待签名串：

```
appid + "\n" + timestamp + "\n" + nonce + "\n" + rawBody
```

签名：

```php
$sign = hash_hmac('sha256', $signStr, $secret);
```

规则：时间窗 300 秒；nonce 8–64 位字母数字且不可重放；默认 120 次/分钟；`GET` 请求 rawBody 为空；`POST` 必须用原始 body 计算，不能先 decode 再 encode。

成功返回：

```json
{"code":0,"msg":"ok","data":{...}}
```

签名失败 HTTP 401；业务校验失败 HTTP 400/403/404，`code` 与 HTTP 对应。

### 6.2 接口清单

| 方法 | 路径 | 关键参数 | 说明 |
|---|---|---|---|
| GET | `/member/api/health` | 无 | 探活、检查表是否就绪 |
| POST | `/member/api/member/get` | `member_id` 或 `mobile` | 查会员公开资料 |
| POST | `/member/api/member/register` | mobile, username, password, real_name, email, company | 注册会员（受注册开关限制） |
| POST | `/member/api/member/verify` | account, password | 校验账号密码，返回会员资料 |
| POST | `/member/api/member/update` | member_id + 资料字段 | 更新资料（不含手机号/资产） |
| POST | `/member/api/assets` | member_id | 查余额、积分、等级、折扣 |
| POST | `/member/api/discount` | member_id, amount | 返回等级折扣与应付金额 |
| POST | `/member/api/balance/change` | member_id, amount, direction(in/out), type, biz_no, remark | 余额增减 |
| POST | `/member/api/points/change` | member_id, points（或 change）, type, biz_no, remark | 积分增减 |
| POST | `/member/api/spend/record` | member_id, amount, order_no | 累计消费入账并触发自动升级 |
| POST | `/member/api/address/list` | member_id | 地址列表 |
| POST | `/member/api/address/save` | member_id + 地址字段 | 新增/编辑地址 |
| POST | `/member/api/address/delete` | member_id, address_id | 删除地址 |
| POST | `/member/api/orders` | member_id, page, size | 订单桥接列表 |

业务类型（余额）：`init/recharge/consume/refund/withdraw/adjust`。  
业务类型（积分）：`init/gift/consume/deduct/refund/adjust`。

**强制 biz_no**：余额 `consume/refund/withdraw`，积分 `consume/deduct/refund` 必须传业务单号，否则拒绝。同一个 `biz_no` 只能成功一次，重复请求返回「业务单号已处理」。

### 6.3 商城接入示例（PHP 服务端）

```php
function memberApi(string $path, array $payload): array
{
    $appid  = 'modera-mall';
    $secret = '从会员设置读取，不能写前端';
    $body   = json_encode($payload, JSON_UNESCAPED_UNICODE);
    $ts     = (string) time();
    $nonce  = bin2hex(random_bytes(8));
    $sign   = hash_hmac('sha256', $appid . "\n" . $ts . "\n" . $nonce . "\n" . $body, $secret);

    $ch = curl_init('http://你的域名/member/api/' . $path);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Member-Appid: ' . $appid,
            'X-Member-Timestamp: ' . $ts,
            'X-Member-Nonce: ' . $nonce,
            'X-Member-Sign: ' . $sign,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
    ]);
    $ret = json_decode((string) curl_exec($ch), true);
    curl_close($ch);
    return $ret ?: [];
}
```

### 6.4 内部服务调用（同进程，推荐给商城插件）

```php
use plugins\member\service\Member;

$m = Member::findByMobile('13800138000');
Member::applyBalance($m['id'], -99.00, 'consume', [
    'operator_type' => 'api',
    'biz_type'      => 'mall',
    'biz_no'        => 'ORDER-20261126-0001',
    'remark'        => '订单支付',
]);

Member::applyPoints($m['id'], -500, 'consume', [
    'operator_type' => 'api',
    'biz_type'      => 'mall',
    'biz_no'        => 'ORDER-20261126-0001-PTS',
]);

Member::addSpend($m['id'], 99.00, 'ORDER-20261126-0001');
$d = Member::discountFor($m['id'], 199.00); // 返回 discount / payable
```

**不要**在商城代码里直接 `Db::name('member')->update(...)`，否则会绕过行锁、流水和升级逻辑。

---

## 7. 订单桥接契约

会员插件不建订单表。会员中心「我的订单」按以下约定查找：

```php
plugins\mall\service\OrderBridge::listByMember(int $memberId, int $page, int $size): array
```

返回：

```php
[
    'list' => [
        [
            'order_no' => '...',
            'title' => '...',
            'summary' => '...',
            'amount' => 199.00,
            'item_count' => 2,
            'status_text' => '已发货',
            'created_at' => '2026-11-26 10:00:00',
        ],
    ],
    'total' => 12,
    'page' => 1,
    'pages' => 2,
    'connected' => true,
]
```

类名可替换为 `plugins\shop\service\OrderBridge`；未安装商城时返回空列表并标记 `connected=false`。

---

## 8. 导入导出

- 导出：真 `.xlsx`（ZipArchive + OOXML inlineStr），手机号按文本写入，不丢前导 0。
- 导入：支持 `.xlsx` 与 UTF-8/GBK `.csv`；第一行表头；单次 500 行。
- 表头：`手机号*、姓名、用户名、密码、邮箱、公司、等级、业务员、余额、积分、累计消费、备注`。
- 手机号已存在：只更新姓名/邮箱/公司/备注/等级/业务员，**不覆盖余额积分**。
- 新会员：可写入初始余额/积分并生成 `INIT-BAL-*` / `INIT-PTS-*` 流水。
- 导入不依赖 PhpSpreadsheet，避免虚拟主机缺扩展。

---

## 9. 开发注意事项与坑点

1. **所有资产变动必须走 `applyBalance` / `applyPoints`**：内部使用事务 + `lock(true)` 行锁，先读后改再写流水；直接 update 会并发超扣。
2. **biz_no 是幂等键**：余额/积分日志表对 `biz_no` 建唯一索引。调用方必须传稳定业务单号；重试同一单号会失败而不是重复扣款。空字符串会存 NULL，不参与唯一约束。
3. **消费/退款/提现、积分抵扣/扣减/退回必须传 biz_no**，否则服务层直接拒绝。这是故意的，避免出现无法对账的资产变动。
4. **累计消费 `addSpend` 目前没有独立幂等表**：调用方必须保证同一订单只调用一次；若要强幂等，建议商城侧以订单号做唯一锁，或后续增加 `member_spend_log`。不要在网络超时后盲目重试。
5. **自动升级只升不降**：按 `total_spent` 找满足门槛的最高等级；管理员可在会员详情点「按累计消费重算等级」。降级需手工改等级字段。
6. **默认等级只能有一个**：设置某等级为默认会清掉其他默认标记；默认等级不可删除。
7. **业务员只读资产**：需求只要求「只能看自己名下会员」，所以业务员不能调余额/积分；需要放权时再加独立权限，不要直接复用管理员接口。
8. **会员手机号是唯一登录账号**：改手机号必须走管理员；API 的 `member/update` 不接收 mobile。
9. **三套 session 分离**：不要把管理员 `admin_id` 当成会员 id；前台页面只信 `member_id`。
10. **API Secret 只能放服务端**：前端 JS 不能持有；生产环境配置 IP 白名单；修改 Secret 后商城必须同步。
11. **页面缓存**：会员中心页面不在 PageCache 白名单内；首页等整页缓存页面的会员入口已改为 JS 读取 `/member/status`，因此缓存不会把登录态串给其他会员。若自行加缓存，仍不要缓存会员中心等带 session 的业务页面。
12. **金额精度**：数据库 `decimal(12,2)`，PHP 侧统一 `round(...,2)`；积分是整数，不要传小数。
13. **地址默认逻辑**：第一条自动默认；删除默认地址会自动选一条新的默认；编辑时取消默认但全表无默认，会自动把当前地址设回默认。
14. **Excel 导入的等级/业务员按名称或 code/账号匹配**，匹配不到会落默认等级/不分配，不会报错；导入前先确认名称一致。
15. **订单桥接类不存在时不要报错**：会员中心会显示「商城未接入」，这是正常降级。
16. **卸载会删表**：`uninstall.php` 会 DROP 6 张会员表并清空配置；生产环境卸载前必须备份。

---

## 10. 测试账号（演示数据，上线前删除）

| 角色 | 账号 | 密码 |
|---|---|---|
| 后台管理员 | admin | admin123 |
| 业务员 | sales01 | sales123456 |
| 会员 | demo003 / 13800138003 | demo123456 |
| 会员 | admintest1 / 13800138004 | test123456 |

上线前务必修改后台管理员密码、业务员密码、会员测试账号，并重置 API Secret。

---

## 11. 文件结构

```
plugins/member/
├── plugin.json               # 插件清单 + 7 个后台菜单
├── install.php               # 建表/种子/密钥
├── uninstall.php             # 删表/清配置
├── route.php                 # 前台/API/业务员/后台路由
├── controller/
│   ├── Admin.php             # 后台 CRUD
│   ├── Api.php               # HMAC API
│   ├── Front.php             # 会员中心
│   └── Staff.php             # 业务员工作台
├── service/
│   ├── Member.php            # 会员/等级/资产/日志/地址/业务员/订单桥接
│   └── Excel.php             # xlsx 导出 + xlsx/csv 导入
├── view/admin/               # 后台页面
├── view/front/               # 会员中心 + 业务员页面
├── asset/
│   ├── member.css            # 会员中心样式
│   └── admin.css             # 后台补充样式
└── hook/front_footer.php     # 前台会员入口（桌面导航/移动菜单/浮动入口，动态登录态）
```
