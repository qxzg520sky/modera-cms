# 企业管理系统（ThinkPHP 8.1.4）

零第三方模板引擎、原生 PHP 模板，整套代码复制到任何 phpStudy 站点即可跑起来。

- 前台：http://tempqiyecms/
- 后台：http://tempqiyecms/admin
- 默认管理员：admin / admin123（登录后请立刻在「管理员」里改密码）
- 数据库：tempqiyecms，前缀 tp_，账号 root / 123456

## 一、后台功能一览

| 模块 | 说明 |
| --- | --- |
| 仪表盘 | 文章数、栏目数、单页数、最近发布 |
| 文章管理 | 新增/编辑/删除、栏目归属、置顶、摘要、封面、SEO 三要素、浏览量 |
| 栏目管理 | 无限级父子栏目、列表/单页/外链三种类型、排序、伪静态 slug |
| 单页管理 | 关于我们、联系方式等独立页面 |
| 模板主题 | 扫描 themes 目录，一键切换，前台立即生效 |
| 插件扩展 | 扫描 plugins 目录，启用/停用、插件配置项、插件自带后台菜单 |
| 系统设置 | 站点名称、LOGO、SEO、电话邮箱地址、备案号、每页条数、语言列表 |
| 管理员 | 多账号、昵称、启用禁用、改密码 |

前台路由：

- `/` 首页（轮播 + 核心业务卡片 + 最新动态）
- `/c/{栏目slug}` 栏目列表，带分页
- `/a/{文章id}` 文章详情，带上一篇/下一篇、阅读量自增
- `/p/{单页slug}` 单页
- `/search?kw=关键词` 搜索
- `/theme/style.css` 当前主题的静态资源（主题自带 CSS/JS 直接放到主题目录即可）

## 二、目录结构（只看这几个）

```
app/controller/admin/   后台控制器（每个菜单一个文件）
app/service/            View 渲染、ThemeManager、PluginManager、Installer
app/view/admin/         后台模板（原生 PHP，~/../_header.php 是公共头）
route/admin.php         后台全部路由
config/                 框架配置
public/                 入口目录（Web 站点根指向这里）
  static/admin/         后台样式
  uploads/              上传目录
themes/default/         默认企业主题（theme.json + 4 个模板 + style.css）
plugins/example/        示例插件（可当多语言/商城的脚手架）
.env                    数据库与调试开关
```

## 三、复制到另一个站点（4 步）

1. **拷代码**：把整个 `tempqiyecms` 目录复制成新目录，例如 `D:\phpstudy_pro\WWW\newcms`。
2. **改站点根**：phpStudy 里新建站点时，网站根目录一定要选到 `...\newcms\public`（不是项目根），PHP 版本选 8.0 以上，伪静态可留空（`.htaccess` 已配好 `index.php?s=/$1`）。
3. **建库**：phpMyAdmin 或命令行建一个空库 `CREATE DATABASE newcms DEFAULT CHARSET utf8mb4;`。
4. **改 .env**：把 `DB_NAME`、`DB_USER`、`DB_PASS` 改成新库的信息。
5. **访问 `http://新域名/admin`**：会自动进入安装向导，填站点名 + 管理员账号密码，点安装 → 自动建 7 张表 + 写默认栏目/单页/示例文章 → 跳到登录页。装完删掉 `.env` 里的 `APP_DEBUG = true`（改成 false），避免报错信息暴露。

> 安装器是幂等的（建表用 IF NOT EXISTS），重复点不会毁数据；但**已经有数据后不要再去访问安装页**。

## 四、换模板 / 做新模板

主题就是一个目录，最少 5 个文件：

```
themes/mytheme/
  theme.json     名称、版本、作者、说明
  _top.php       公共头（HTML head + 导航）
  _bottom.php    公共尾（页脚 + 备案号）
  index.php      首页
  list.php       栏目列表（$articles、$cat、$total、$pager）
  article.php    文章详情（$row、$cat、$prev、$next）
  page.php       单页（$row）
  style.css      样式，前台用 /theme/style.css 引用
```

做法：复制 `themes/default` 整个目录 → 改名（目录名 = 主题名）→ 改 `theme.json` 的 title → 改模板 HTML 与 `style.css`。到后台「模板主题」点启用即可，前台立刻换。

模板里可直接用的数据：

- `$nav` 所有启用栏目（数组：id/name/slug/seo_description…）
- `$pages` 所有启用单页（首页模板里有）
- `setting('site_name')` 任意系统设置项，模板里直接取
- `e($str)` 输出转义；`pager_html()` 已在控制器里算好传进来

## 五、开发插件（多语言、商城都是这条路）

插件 = `plugins/{插件名}/` 一个目录，四样东西：

```
plugins/myshop/
  plugin.json          清单：title/version/author/description + admin_menu + config
  route.php            自己的路由（启用后自动加载）
  controller/*.php     自己的控制器，命名空间 plugins\myshop\controller
  view/                自己的模板
```

`plugin.json` 示例：

```json
{
  "title": "多语言",
  "version": "1.0",
  "author": "你",
  "description": "中英双语切换",
  "admin_menu": [ { "title": "多语言", "url": "/admin/lang" } ],
  "config": [ { "name": "default_lang", "label": "默认语言", "type": "text" } ]
}
```

`route.php` 示例（后台页必须挂 AdminAuth 中间件，否则不校验登录）：

```php
use think\facade\Route;
Route::get('lang', 'plugins\myshop\controller\Lang@index');
Route::get('admin/lang', 'plugins\myshop\controller\Lang@admin')
    ->middleware(\app\middleware\AdminAuth::class);
```

- 命名空间 `plugins\xxx\...` 已在 `app/AppService.php` 里注册了自动加载，文件放对位置就能用。
- 插件配置存在 `tp_plugin.config`（JSON），控制器里 `Db::name('plugin')->where('name','myshop')->find()` 读出来 `json_decode` 即可。
- 启用/停用插件 = 加载/摘除它的 `route.php`，不影响主程序；数据表建议用 `tp_` 前缀 + 插件名，例如 `tp_shop_goods`。
- 商城的做法：插件里建自己的商品/订单表（安装时在后台执行建表 SQL），前台路由 `/shop`，后台菜单 `/admin/shop`。

## 六、常见问题

- **500 / 白屏**：把 `.env` 的 `APP_DEBUG` 改成 `true` 看详细报错；同时看 `runtime/log/日期.log`，最后一行就是真实原因。
- **访问 404 但文件存在**：`public/.htaccess` 必须是 `index.php?s=/$1` 这种写法（mod_fcgid 不支持默认的 PATH_INFO 形式）；phpStudy 里 Apache 的 `mod_rewrite` 要开启。
- **样式不生效**：确认站点根指向的是 `public` 目录，`/static/admin/admin.css` 能直接打开。
- **登录后又跳回登录页**：`runtime` 目录要可写（session 存在里面）；PHP 需开启 `session`（默认已开）。
- **改了路由不生效**：ThinkPHP 在 debug 模式下不缓存路由；`APP_DEBUG=false` 时若改了路由，删掉 `runtime/route.php` 或整个 `runtime` 缓存目录。
- **上传目录**：`public/uploads`，后台目前用「图片地址」填写（可贴外链或自己传图后填 `/uploads/xxx.png`）。

## 七、安全建议（上线前）

1. 后台密码改掉默认的 admin123。
2. `APP_DEBUG = false`。
3. 数据库账号不要用 root，新建一个只对该库有权限的账号。
4. 定期备份 `tp_*` 数据表 + `public/uploads`。

---

## 附：当前站内测试数据（甘肃鼎成实业有限公司）

`data` 全部在数据库里，替换成你自己的内容不需要改任何代码。

- 站点：甘肃鼎成实业有限公司，智能装备制造 / 工业自动化 / 精密加工，地址写的是酒泉肃州区工业园区
- 7 个栏目（产品中心下面挂了智能装备、精密加工两个子栏目）、15 篇文章、3 个单页（关于我们 / 联系我们 / 企业文化）
- 8 张图在 `public/uploads/`：logo.png、workshop.jpg、line.jpg、machining.jpg、qc.jpg、factory.jpg、team.jpg、showroom.jpg
- 图片是生成图，仅作占位，正式上线请换成实拍或客户素材
- 备案号 `陇ICP备2026001234号-1` 是假号，务必改成你自己的
- 后台「系统设置」里改名称、电话、邮箱、地址、LOGO 即可整站换皮；文章正文里的图片路径用的是 `/uploads/xxx.jpg`，换图时同名替换即可

---

## 八、手机端：独立手机模板（按 UA 自动切换）

系统不是纯响应式的，而是**PC 模板和手机模板分开写**，手机访问自动走手机模板，方便单独设计手机版。

### 判断顺序

1. URL 参数 `?mobile=1` / `?mobile=0` —— 强制指定，并写入 cookie（记住 30 天），PC 上预览手机版就用它
2. cookie `site_mobile`
3. User-Agent：iPhone / iPad / Android / Mobile / Windows Phone / 微信内置浏览器等 → 手机模板

后台「系统设置 → 手机端模板」可以一键关闭，关闭后手机也显示 PC 模板。

### 目录约定

```
themes/default/            PC 模板
  index.php / list.php / article.php / page.php / _top.php / _bottom.php / style.css
themes/default/mobile/     手机模板（同名文件）
  index.php / list.php / article.php / page.php / _top.php / _bottom.php / style.css
```

规则：手机访问时先找 `themes/{当前主题}/mobile/{模板}.php`，**找到就用，没写就自动回落 PC 的那一个**。所以你只写 `mobile/index.php` 也能跑，其余页面继续用 PC 版，逐个替换。

静态资源同理：手机模板里写 `<link rel="stylesheet" href="/theme/style.css?m=1">`，带 `?m=1` 时系统优先返回 `themes/{主题}/mobile/style.css`，不带就是 PC 的。图片、JS 也一样。

### 手机模板可用的变量（与 PC 完全一致）

- 首页：`$nav`（栏目）、`$articles`（最新 6 篇）、`$pages`（单页）
- 列表：`$cat`、`$articles`、`$total`、`$pager`（已算好的分页 HTML）
- 文章：`$row`、`$cat`、`$prev`、`$next`
- 单页：`$row`
- 任意页面：`setting('site_name')`、`is_mobile()`、`device_type()`

### 默认手机模板做了什么

- 固定顶栏 + 纯 CSS 抽屉菜单（无 JS，靠 checkbox 实现，兼容性好）
- 首页：蓝色渐变头图 + 双列栏目卡片 + 资讯列表 + 「查看全部动态」
- 列表：左日期右标题的卡片列表，分页可横向滑动
- 文章：标题、日期、阅读数、正文（图片自动 100% 宽、小标题左侧蓝线）
- 底部：固定「首页 / 电话咨询」操作条（手机上一点就拨号）、页脚含地址/备案/版权 + 「电脑版」切换

### 自己开发手机模板的步骤

1. 复制 `themes/default/mobile` 整目录 → 改里面的 HTML 和 `style.css`（目录已跟着当前主题走，不用另建主题）
2. 或者新建整套主题：`themes/mobile01/` 只写 PC，`themes/mobile01/mobile/` 写手机，后台启用即可
3. 预览：PC 浏览器打开 `http://tempqiyecms/?mobile=1`；改完要看 PC 版就 `?mobile=0`
4. 真机调试：手机和电脑连同一 WiFi 不行（这里是本地站点），用 Chrome F12 的设备模拟（Ctrl+Shift+M 选 iPhone）最省事

### 想用 m.xxx.com 独立域名？

现在是一套代码两个模板，同一域名，SEO 权重不分家，最省事。如果确实要独立域名，在 phpStudy 加一个 `m.tempqiyecms.com` 站点、根目录指同一个 `public`，然后在 `public/index.php` 里按 `$_SERVER['HTTP_HOST']` 强制 `is_mobile()` 返回 true 即可 —— 这一步目前没做，按需再加。

---

## 九、多语言插件（可插拔）

插件目录：`plugins/multilang/`，后台入口：`/admin/plugin/multilang`（侧栏「插件菜单 → 多语言」）。当前已启用，外语为 English，前台地址前缀 `/en/`。

### 9.1 原理

一种外语 = 数据库里一组字段 + 一份界面词典 + 一组 `/en/...` 路由。内容字段有值就显示译文，留空自动显示中文原文，所以「只翻了导航没翻正文」也不会出现空白页。

| 表 | 新增字段（以 en 为例） |
| --- | --- |
| `tp_category` | `name_en` `seo_title_en` `seo_description_en` |
| `tp_article` | `title_en` `summary_en` `content_en` `seo_title_en` `seo_description_en` |
| `tp_page` | `title_en` `content_en` `seo_title_en` `seo_description_en` |
| `tp_setting` | `value_en`（覆盖 site_name、site_slogan、site_address 等所有站点设置） |

界面文案不进数据库，放在 `plugins/multilang/lang/en.php`，key 就是模板里的中文原文。

### 9.2 前台地址

| 中文 | 英文 |
| --- | --- |
| `/` | `/en/` |
| `/c/product` | `/en/c/product` |
| `/a/1` | `/en/a/1` |
| `/p/about` | `/en/p/about` |
| `/search?kw=xx` | `/en/search?kw=xx` |

PC 模板右上角、手机模板抽屉菜单里都有语言切换器，切换时保持当前页面（`/en/a/1` 点「中文」会回到 `/a/1`）。手机模板与语言无关，仍然是 UA 判断进入独立手机模板。

### 9.3 怎么填译文

1. 后台 → 插件菜单 → 多语言 → ② 内容翻译，栏目 / 文章 / 单页各一张表，右侧「翻译」按钮进去按语言逐条填写。
2. 站点名称、地址、版权等信息：插件页顶部会同步 `value_en`，也可以直接在「系统设置」里保存中文，英文值在插件管理页维护。
3. 摘要、正文都支持 HTML，和中文编辑体验一致。

### 9.4 加一门新语言（例如日语）

1. 后台 → 多语言 → ① 语言设置，文本框追加一行（每行一个）：

```
en English
ja 日本語
```

2. 保存即自动建字段：`name_ja`、`title_ja`、`content_ja`、`value_ja`……
3. 复制 `plugins/multilang/lang/en.php` 为 `lang/ja.php`，把右侧译文换成日语（key 不要改）。
4. 前台立刻多出 `/ja/` 及 `/ja/c/...`，切换器自动出现「日本語」。
5. 删掉某一行再保存，该语言的字段和数据会被自动删除。

### 9.5 卸载与还原

后台「插件扩展」页该插件卡片右侧有「卸载」按钮：

- 执行 `plugins/multilang/uninstall.php` → 删除所有 `{字段}_{语言}` 列（含译文数据）；
- 插件置为停用，`/en/...` 路由与切换器立即消失，前台回到纯中文；
- 模板里的 `lang()`、`lang_path()`、`ml()` 是系统内置能力（`app/common.php`），插件不存在时原样返回中文，不会报错，也不用改模板；
- 语言配置（`tp_plugin.config`）保留，重新点「启用」会按原配置把字段和语言重建（数据需要重新填）。

只想清空译文、保留插件，用多语言页底部的「删除全部多语言字段」。

### 9.6 二次开发接口

写自定义主题时直接用这几个函数即可，无需感知插件是否安装：

```php
lang('首页');                       // 界面文案：当前语言词典，没有就用中文原文
lang_path('/c/product');            // 生成当前语言的链接（英文下自动加 /en 前缀）
ml($row, 'title');                  // 取内容字段：当前语言有值用译文，否则回落中文
lang_url('en');                     // 当前页面在指定语言下的地址（做切换器用）
lang_switcher('lang-switch');       // 输出切换器 HTML，插件未启用时返回空串
current_lang();                     // 当前语言代码，如 zh-cn / en
multilang_config();                 // ['enabled','default','langs','names']
```

主题目录里新增的模板只要把中文文案写成 `lang('原文')`、内部链接写成 `lang_path('/...')`，就自动支持所有已安装的语言。

---

## 十、子栏目与导航结构

后台「栏目」用父栏目字段（pid）建层级，前台自动分两级展示，同级栏目不会再挤在一行。

- 电脑版导航：只平铺顶级栏目，有子栏目的显示 ▾，鼠标悬停弹出下拉（第一项「查看全部」= 父栏目列表页，下面列出子栏目）。单页（关于我们/联系我们/企业文化）收进「关于公司」下拉。
- 栏目页：标题下方出现胶囊切换条（产品中心 · 智能装备 · 精密加工），当前栏目高亮。父栏目页显示聚合的子栏目文章，子栏目页只显示自己的文章，切换条让两边互相跳转。
- 手机模板：抽屉里父栏目下方缩进列出子栏目；栏目页顶部是可横向滑动的胶囊切换条。
- 首页「核心业务」卡片：标题下用小字列出该栏目的子栏目名称，作为入口提示。

加第三个层级（子栏目的子栏目）目前不显示在导航里，需要两级以上时建议把结构控制在两级，或直接改 `themes/default/_top.php` 的循环。模板里子栏目数据来自 `Index::nav()` 返回的树（每个顶级栏目带 `children`），要改样式只动 CSS 的 `.nav-sub` / `.mnav-sub` 即可。

---

## 十一、富文本编辑器（wangEditor 5）

后台所有「正文」输入统一换成富文本，写 HTML 不用再手敲标签。

- 选型：wangEditor 5.1.23，本地文件放在 `public/static/wangeditor/`（`index.js` + `style.css`），不依赖任何外网 CDN，断网也能用。
- 调用：视图里写 `<?= rich_editor('content', $r['content'] ?? '', '520px') ?>`，参数是「表单字段名 / 初始 HTML / 编辑区高度」。函数在 `app/common.php`，库只会加载一次，同一页面可以放多个编辑器（多语言页每个语言一个）。
- 已接入的三处：文章编辑正文（520px）、单页编辑正文（520px）、多语言插件里译文 content（320px，每种语言一个）。
- 图片：工具栏「图片」按钮 → 上传 → 走 `POST /admin/upload`（`app/controller/admin/Upload.php`），限制 jpg/jpeg/png/gif/webp、≤5MB，存 `public/uploads/年份月份/`，返回 wangEditor 约定的 `{errno:0,data:{url}}`。也可以直接粘贴网络图片地址。
- 提交方式不变：编辑器旁边留了一个隐藏 textarea 顶着原字段名，表单 submit 时自动把 `editor.getHtml()` 写回，所以控制器、数据库、旧数据全都不用改。
- 工具栏已去掉「全屏」按钮（在后台框架里全屏会盖住菜单）。
- 摘要字段仍是普通文本框：它是纯文本摘要，列表页直接截取，塞 HTML 会显示成乱码。

---

## 十二、在线客服插件（plugins/kefu）

一套可插拔的完整客服系统：前台浮动客服面板 + 后台坐席管理 / 会话收件箱 / 设置。

### 12.1 能力

- 前台浮窗：右下（或左下）圆角按钮，点开是一个完整面板：欢迎语、在线状态、坐席名片、对话区、留言框。
- 坐席名片：支持在线对话、微信、企业微信、QQ、电话、邮箱、自定义链接七种类型。微信/企微/QQ 可以配二维码图片，点开弹层显示大图并能复制账号；电话点一下直接拨号，邮箱直接开邮件，链接新窗口打开。
- 工作时间判断：设工作日 + 上下班时间（支持跨零点，如 20:00-02:00），上班时间自动显示「在线」，否则显示「离线 · 可先留言」并附离线提示文案。
- 在线留言 + 实时对话：访客第一次发言会自动建会话（可要求必留称呼和联系方式），后台回复后访客端按设置间隔（默认 5 秒）轮询拉取，面板关着时按钮上出现红点计数。
- 后台会话收件箱：列表按最后活动排序，分「全部 / 待回复 / 进行中 / 已处理」四个筛选，支持按名称、联系方式、来源页搜索；进入会话可看完整对话、一键插入常用话术、回复、标记已处理、删除。
- 后台设置：开关、按钮文案与图标、位置、主题色、标题副标题、欢迎语、离线提示、自动回复、工作日/上下班时间、是否必须留联系方式、轮询间隔、字数上限、匿名称呼、是否显示名片/二维码、离边距离。

### 12.2 插拔与还原

- 启用：自动建三张表并写入 3 个示例坐席和默认配置（重复启用不会重复插）。
- 停用：前台浮窗、`/kefu/asset/*`、`/kefu/api/*`、后台三个菜单一起消失，数据保留。
- 卸载：删掉 `tp_kefu_staff` / `tp_kefu_session` / `tp_kefu_message` 三张表，清空插件配置，前台连一行输出都没有，和没装过一样。
- 实测已验证「启用 → 建表 → 留言对话 → 后台回复 → 卸载删表还原 → 重新启用恢复」整条闭环。

### 12.3 文件结构

```
plugins/kefu/
├── plugin.json             清单（3 个后台菜单）
├── install.php             启用钩子：建表 + 默认坐席 + 默认配置
├── uninstall.php           卸载钩子：删表 + 清配置
├── route.php               前台接口 + 后台路由
├── hook/front_footer.php   前台浮窗（由主题模板 plugin_hook('front_footer') 调用）
├── service/Kefu.php        配置、建表/删表、在线判断、统计
├── controller/Api.php      前台接口：send / poll / asset
├── controller/Admin.php    后台：坐席、会话、设置
├── view/                   admin / staff_edit / chat / chat_detail / setting
└── asset/kefu.css, kefu.js 前台样式与脚本（走 /kefu/asset/*，卸载即 404）
```

### 12.4 前台接口

- `POST /kefu/api/send` — 参数 `content`、`token`(可空)、`name`、`contact`、`page`；返回 `{ok, token, id, online}`，需要留联系方式但没填时返回 `{ok:false, need_contact:1}`。
- `GET /kefu/api/poll` — 参数 `token`、`after`（最后一条消息 id），返回该 id 之后的全部消息（含访客自己的，便于刷新后恢复对话）、`online`、`closed`。
- `GET /kefu/asset/:file` — 只允许 `kefu.css` / `kefu.js`，其它文件名 404。

### 12.5 前台钩子机制

主题的 `_bottom.php`（PC 与手机各一份）里加了一行：

```php
<?= plugin_hook('front_footer') ?>
```

`plugin_hook()` 在 `app/common.php` 里，会遍历所有已启用插件，执行 `plugins/{插件}/hook/{钩子}.php` 的输出。以后别的插件想在页脚塞东西，建个 `hook/front_footer.php` 就行，不用改模板。插件停用/卸载后这个函数返回空字符串，模板里那一行等于不存在。

客服浮窗的 CSS 和 JS 是**内联输出**的（PHP 读 `asset/kefu.css`、`asset/kefu.js` 后塞进 `<style>` / `<script>`），不再依赖 `/kefu/asset/*` 这次请求；`/kefu/api` 前缀也按当前入口脚本路径自动算，站点挂在子目录里也不会错位。这样任何域名、端口、路径访问都不会出现「裸 HTML」式的错乱排版。

### 12.6 部署过的数据

- 表结构：`tp_kefu_staff`（坐席）、`tp_kefu_session`（会话，含 admin_unread / unread 两个计数）、`tp_kefu_message`（消息，role = visitor / admin / system）。
- 示例坐席：客服小鼎（电话 0937-2866888）、技术支持（service@dingcheng.com）、企业微信（在线对话），可在后台改。

### 12.7 后台提醒（访客留言不漏看）

访客在前台发了消息，后台不会只有一个「待回复 1」的数字，具体留言和提醒都在：

- **侧栏角标**：`客服会话` 菜单右侧红色数字 = 所有会话未读留言总数（>99 显示 `99+`）。来源是 `app/common.php` 的 `plugin_badge('/admin/plugin/kefu/chat')` → `plugins/kefu/hook/admin_badge.php`，插件卸载后角标一起消失。
- **坐席页顶部横幅**：`🔔 有 N 个访客在等您回复` 红边卡片，直接列出访客、联系方式、留言原文、时间，每行带「回复 / 已处理」，右上「全部去回复 →」「提示音：开/关」。看不到留言内容的时代结束了——打开 `/admin/plugin/kefu` 第一屏就是它。
- **实时提醒（轮询）**：坐席页 20 秒、会话收件箱 15 秒、会话详情 10 秒各拉一次 `api/unread` 或 `api/messages`；有新未读时
  1. 浏览器标题加 `(N)` 前缀（切到别的标签也能看到）；
  2. 播放 900Hz 提示音（Web Audio 现场合成，不依赖音频文件，可关闭，开关存 localStorage `kefu_sound`）；
  3. 坐席页右下角弹「🔔 有新的访客留言（N 条待回复）」提示条，点它直达 `chat?tab=unread`；
  4. 收件箱顶部弹「🔔 新留言 +N 条，点此刷新列表」；
  5. 会话详情页自动把访客新消息插进气泡区并滚到底。
- **已读规则**：打开某个会话详情即视为看过 → 该会话未读数清零、访客消息标为已读；点「已处理」同时清未读并把会话状态转「已处理」，会话立刻从待回复卡片消失。
- **前台交互修复**：访客点坐席名片进入对话后，欢迎语和坐席列表自动收起，顶部出现「← 返回坐席列表」，聊天记录不再被名片挤到看不见。

新增的后台接口：`GET /admin/plugin/kefu/api/unread`（返回 `{ok,unread,sessions,today,time}`）、`GET /admin/plugin/kefu/api/messages?id=&after=`（返回该 id 之后的消息并顺带置为已读）。

## 十三、数据库备份插件（plugins/dbbackup）

后台「插件扩展 → 数据库备份」（`/admin/plugin/dbbackup`）就能整库备份、还原、下载、上传 SQL，还能设定时自动备份。**不需要 mysqldump，也不需要服务器计划任务**，纯 PHP 读写。

### 13.1 能做什么

- **立即备份**：把整库导出成一份 `.sql`（每张表 `DROP TABLE IF EXISTS` + `CREATE TABLE` + 分批 `INSERT`），文件名 `db_20261125_141955_manual.sql`；文本是标准 SQL，直接拿到 phpMyAdmin / Navicat 里导入也认。
- **按文件还原**：点列表里的「还原」，会**先给当前库自动拍一份快照**（文件名带 `_pre`），再执行所选备份。万一还原错了，用那份 `_pre` 再还原回去就行。
- **上传 SQL**：本地已有的 `.sql`（≤64MB）可以上传进备份目录，然后在列表里点还原。
- **下载**：备份文件随时下载到本机存一份，避免全压在服务器上。
- **删除**：只删单份文件；手动备份、`_pre` 快照不会被自动清理逻辑碰到。
- **定时自动备份**：开启后按「间隔小时」触发，到点就备一份；**触发点是有人访问前台页面或你打开后台**，所以不需要 root 权限去装 crontab（虚拟主机也能用）。

### 13.2 定时备份是怎么触发的

没有计划任务权限的站点，靠「访问触发」：

- `plugins/dbbackup/hook/front_footer.php` 挂在 `plugin_hook('front_footer')` 上，前台每个页面底部调用一次 `Backup::maybeAutoRun()`；后台打开本插件页也会调一次。
- 平时只读 `runtime/dbbackup/state.json` 里的 `last` 时间戳，**没到间隔就直接 return，不碰数据库**，对页面速度几乎没有影响。
- 到点了先抢 `runtime/dbbackup/.lock` 文件锁（`flock(LOCK_EX|LOCK_NB)`），抢不到的请求直接放弃，避免几个访客同时触发把库 dump 好几遍；抢到后再确认一次时间，才真正备份并写回 `last`。
- 备份完执行 `prune()`：只清理 `auto` 类型的旧文件，保留「保留份数」份；`manual` / `pre` / `uploaded` 不参与清理。

### 13.3 参数与默认值

| 设置项 | 默认 | 说明 |
| --- | --- | --- |
| 自动备份 | 开启 | 关掉后前台/后台都不再触发 |
| 间隔（小时） | 24 | 1–720，24 = 每天、168 = 每周 |
| 自动备份保留份数 | 7 | 1–60，只作用于 `auto` 文件 |
| 文件名前缀 | db | 仅字母数字下划线连字符，生成 `前缀_年月日_时分秒_来源.sql` |

配置存在 `tp_plugin` 表 `name=dbbackup` 那行的 `config` 字段（JSON），备份文件在 `runtime/dbbackup/`。

### 13.4 插拔与还原

- 卸载（后台插件页点卸载，或 `PluginManager::uninstall('dbbackup')`）**只清插件配置和 `state.json` / `.lock`，已生成的 `.sql` 备份文件会原样保留**在 `runtime/dbbackup`，避免手一抖把历史备份删了。要彻底删目录得自己动手。
- 停用只是不再加载路由和前台钩子，备份文件、配置都不动。
- 重新启用后，列表里原来的备份还在，直接能还原。

### 13.5 文件结构

```
plugins/dbbackup/
├── plugin.json              # 清单：标题、后台地址、菜单项
├── install.php              # 建 runtime/dbbackup、初始化 state.json
├── uninstall.php            # 清配置与状态，保留 .sql
├── route.php                # 后台 6 条路由（都过 AdminAuth）
├── service/Backup.php       # dump / restore / 定时 / 清理 / 文件管理
├── controller/Admin.php     # index、run、restore、upload、delete、download、setting/save
├── hook/front_footer.php    # 前台访问时触发定时备份
└── view/index.php           # 后台页面
```

### 13.6 已验证过的流程

备份 → 用 `mysql` 客户端把 `.sql` 导入一个新库，10 张表 / 行数与源库一致；插件内「还原」执行 33 条语句 0 报错，还原后站点与后台访问正常；定时触发、旧文件清理、上传、下载、删除、卸载（备份保留）逐项跑通。

### 13.7 手动兜底（图形界面挂了怎么办）

万一后台进不去，直接命令行导：

```bat
"D:\phpstudy_pro\Extensions\MySQL5.7.26\bin\mysqldump.exe" -uroot -p123456 --default-character-set=utf8mb4 tempqiyecms > D:\backup_manual.sql
```

恢复：

```bat
"D:\phpstudy_pro\Extensions\MySQL5.7.26\bin\mysql.exe" -uroot -p123456 tempqiyecms < D:\backup_manual.sql
```


---

## 十四、前台主题（themes/gangtie 钢铁风企业主题）

这一章记录新增的「钢铁风企业主题」：它是仿 `temp-gangtie1` 那套钢铁贸易站做的响应式主题，一套模板同时管 PC 和手机，不需要 `mobile/` 目录。

### 14.1 怎么启用 / 怎么停用
1. 后台 → 「模板主题」页（`/admin/theme`），能看到两张卡片：默认企业主题、钢铁风企业主题，卡片顶部是主题缩略图。
2. 想换哪个，点该卡片上的 **启用此模板**（表单 `POST /admin/theme/enable`，参数 `name`）。
3. 停用 = 启用另一个。系统里永远是「一个启用中」，切回 `default` 就还原成原来的默认企业站。
4. 命令行等价写法（服务器上执行）：
   ```php
   \app\service\ThemeManager::scan();          // 重新扫描 themes/ 目录
   \app\service\ThemeManager::enable('gangtie'); // 或 'default'
   ```
   启用值写在 `tp_setting` 的 `theme` 键，同时同步 `tp_theme` 表里的启用位。

### 14.2 文件结构（themes/gangtie/）
| 文件 | 作用 |
| --- | --- |
| `theme.json` | 主题元信息：name / title / version / description（后台卡片读它） |
| `_top.php` | 头部：`<head>`、logo 行 + 热线、蓝色导航（含子栏目下拉、单页下拉、搜索、语言切换） |
| `index.php` | 首页：轮播 → 产品墙（栏目 tab）→ 关于我们 → 优势三卡 → 解决方案案例 → 联系方式条 → 新闻区 |
| `list.php` | 栏目列表页：蓝色页头 + 面包屑 + 侧栏 + 列表 + 分页 |
| `article.php` | 文章详情：页头 + 侧栏 + 正文 + 上下篇 |
| `page.php` | 单页（关于我们 / 联系我们等） |
| `_aside.php` | 内页侧栏：栏目树 + 联系方式卡 + 关于我们页面列表 |
| `_bottom.php` | 页脚四栏 + 版权条 + `plugin_hook('front_footer')` + 内联 JS（轮播 / tab / 手机菜单） |
| `style.css` | 全部样式，CSS 变量 + grid/flex，约 560 行，类名统一 `gt-` 前缀 |
| `screenshot.png` | 后台主题卡片的缩略图（678×520） |

> 注意：主题静态资源只能走 `GET /theme/:file` 这个路由，**只允许单层扁平文件名**（`style.css`、`logo.png` 这类），不能建 `css/`、`images/` 子目录。所以本主题没搬 bootstrap/jQuery，图标全部内联 SVG，占位图用 CSS 渐变，照片统一引用站点 `/uploads/*` 的文章封面。

### 14.3 首页数据是怎么取的
全部走后台数据，不写死文案：
- 轮播：每个业务栏目（排除 `news`/`jobs`）各取 1 篇有封面的文章，最多 3 张；都不够就回落全站最新 3 篇。
- 产品墙 tab：取前 5 个资讯类以外的栏目，每个 tab 显示该栏目最新 4 篇。
- 关于我们：`page` 表里 `slug=about` 的正文截 420 字。
- 解决方案：`solution` 栏目 → 深色 4 格，不足 4 格补「更多案例」虚线占位。
- 新闻：`news` 栏目，前 2 篇图文卡 + 5 条日期列表。
- 页脚四栏分别绑：联系我们 / 新闻中心（列 news 子栏目）/ 关于我们（列单页）/ 产品中心（列 product 子栏目）。

### 14.4 响应式断点
三档：`1100px`（产品墙 4 列 → 2 列）、`980px`（内页侧栏换序到正文下方、隐藏栏目树保留联系卡、导航折叠成「网站栏目」按钮）、`640px`（单列、字号收窄）。
手机访问时系统仍会先找 `themes/gangtie/mobile/`，目录不存在就回落这套 PC 模板，所以手机端效果就是同一套 CSS 的窄屏表现。

### 14.5 想再做一个同款主题
1. 复制整个目录：`themes/gangtie` → `themes/xxx`。
2. 改 `theme.json` 的 `name`（必须与目录名一致）和 `title`、`description`。
3. 后台「模板主题」页刷新即可看到新卡片并启用；`name` 不一致会导致后台读不到。

### 14.6 已验收过的页面
首页 `/`、栏目 `/c/product`、`/c/equipment`、`/c/news`、文章 `/a/1`、`/a/6`、单页 `/p/about`、搜索 `/search?kw=…`、英文 `/en/`、`/en/c/product`、`/en/a/6` 全部 200；PC 与 686px 手机宽度逐屏看过；栏目 tab 切换、轮播点切换、手机菜单展开均实测正常。

---

## 十五、第二套主题：工业橙企业主题（themes/gangtie2）

仿 `temp-gangtie2`（Induslio 工业模板）做的第二套响应式主题，黑 + 橙（#ff5e14）+ 深蓝，和「钢铁风」并存，后台可随时切换任意一套。

### 15.1 启用 / 停用
后台 → 「模板主题」页，现在有三张卡片：默认企业主题、钢铁风企业主题、工业橙企业主题（各带缩略图）。点「启用此模板」即可切换，切换后前台立刻生效，不需要重启。
命令行等价：`\app\service\ThemeManager::enable('gangtie'|'gangtie2'|'default')`。

### 15.2 页面结构（themes/gangtie2/）
`theme.json` + `_top.php`（黑色联系条 + 白色主导航 + 橙色「获取报价」）/ `index.php` / `list.php` / `article.php` / `page.php` / `_bottom.php`（橙色呼叫条 + 黑色四栏页脚 + 内联 JS）/ `style.css` / `screenshot.png`。

首页顺序：全屏轮播（栏目标签 + 标题 + 摘要 + 两个按钮，5.2 秒自动切）→ 欢迎/关于（左文右图 + 橙色播放圆标）→ 四宫格能力卡（智能装备/精密加工/工业自动化/服务支持）→ 深色「技术解决方案」左文右图（要点取 solution 栏目文章）→ 最新项目 4 格（装备+加工栏目文章）→ 在线询价（左图右表单）→ 深色「整套交付包」→ 新闻 3 卡 → 橙色呼叫条 → 黑色页脚。

### 15.3 询价表单怎么用
表单三项（姓名 / 电话 / 需求）点「提交询价」后**不会**发到没做的接口，而是自动打开在线客服浮窗，并把「称呼、电话、需求」拼成一条消息预填进输入框，访客确认后直接发出去，后台客服中心就能看到——这样表单不会变成死链。客服插件没启用时自动退回 `/p/contact`。

### 15.4 数据绑定
轮播：各业务栏目各取 1 篇有封面文章（最多 3 张），没有则全站最新 3 篇；
关于：单页 `about` 正文截 300 字，配图取最新一篇有封面的文章图；
能力卡：标题取 `equipment` / `machining` / `solution` / `service` 四个栏目名，文案为内置短句；
技术方案要点、项目墙、新闻：分别取 solution / equipment+machining / news 栏目文章；
页脚：公司列单页、产品与方案列顶级栏目 + 子栏目、联系方式读 `site_phone` / `site_email` / `site_address`。

### 15.5 响应式断点
1200（技术方案内边距收窄）/ 991（导航折叠成汉堡菜单、能力卡 2 列、技术方案与询价改单列、新闻 2 列、页脚 2 列）/ 767（顶部条隐藏邮箱）/ 575（能力卡 1 列、项目 1 列、新闻 1 列、页脚 1 列、隐藏顶部 CTA）。
已实测：1440 / 768 / 390 三种宽度均无横向溢出；手机端汉堡菜单、子栏目展开、轮播点切换、询价唤起客服都正常。

### 15.6 三次切换验证
`gangtie → gangtie2 → gangtie` 来回切换均正常（检查首页标记 `gt-nav` / `g2-nav` 出现关系）。当前启用：gangtie2。

---

## 十六、轮播广告插件（plugins/slider）

后台自己维护首页大图轮播：上传图片、写标题和小标题、配链接和按钮文字、拖不了就用上下箭头排序、单条启停。**启用插件，首页就显示你配的广告；停用插件，首页自动回到模板自带的那套轮播**，不用改任何代码。

### 16.1 启用 / 停用
后台 → 插件扩展 → 找到「轮播广告」→ 启用/停用。启用会顺带建表，并（首次）用站内 3 篇有封面的文章生成示例广告，方便立刻看效果。
卸载插件会删掉广告表（广告数据属于插件，删前如需保留请先截图/另存），站点回到原样。

### 16.2 后台怎么操作（/admin/plugin/slider）
- **新增广告**：标题（必填）、小标题（图片上方橙色小字）、摘要、图片（点「上传图片」走 `/admin/upload`，也可直接粘贴 `/uploads/xxx.jpg`）、主链接 + 主按钮文字、次链接 + 次按钮文字、打开方式、排序、状态。
- **列表**：缩略图、标题、链接、状态一目了然；左侧 ↑↓ 调整播放顺序，「停用」只隐藏这一条，「删除」直接移除。
- **图片建议** 1920×1080 横图，主体居中（手机端会裁成竖屏），单张 ≤5MB，支持 jpg/png/gif/webp。
- **链接**：站内写 `/a/14`、`/c/product`、`/p/about`，会自动补语言前缀（英文站变 `/en/a/14`）；外链直接写 `https://`。
- 前台最多显示 12 条，按排序从小到大播放，间隔沿用主题的 5 秒左右。

### 16.3 它是怎么接管模板轮播的
不劫持 DOM、不藏模板区块，走的是数据层接管：

1. `app/common.php` 新增通用过滤钩子 `plugin_filter($point, $value)`：遍历已启用插件的 `hook/filter_{point}.php`，钩子 return 数组就替换数据，return 空就保留原值。
2. 主题 `index.php` 先把「按栏目取文章封面」得到的数据统一成标准结构（`eyebrow/title/summary/image/link/link_text/link2/link2_text/target`），再 `plugin_filter('home_slides', $heroSlides)`。
3. 插件 `hook/filter_home_slides.php` 读广告表（`status=1`，按 sort 排），有条目就返回条目，没有就返回空 → 主题用自带数据。

所以：**停用插件 = 钩子不执行 = 模板原样**；换主题也不影响插件（两套钢铁风/工业橙主题都改成了标准结构，实测都能显示广告）。默认主题首页是纯色文字横幅、没有大图轮播位，插件对它不生效（后台会提示）。

### 16.4 数据表
`tp_plugin_slider`：`title/subtitle/summary/image/link/link_text/link2/link2_text/target/sort/status`，另有 `title_en/subtitle_en/summary_en/link_text_en/link2_text_en` 五个英文字段（走 `ml()`，英文站留空则显示中文）。安装建表，卸载删表。

### 16.5 实测记录
- 启用：首页首屏 3 张广告按序播放，圆点、5 秒自动切换、手机单列都正常。
- 停用：首页回到模板自带轮播（首张「产品中心 · 精密零件表面处理…」），插件路由 404，前台无残留。
- 后台：新增 / 编辑 / 停用单条 / 上移下移 / 删除 全部通过（302 回列表 + flash 提示）；上传接口走 `/admin/upload`。
- 兼容：gangtie、gangtie2 两套主题 + 英文站 `/en/` 均正常；卸载后重新启用数据重建正常。

---

## 十七、缓存优化插件（plugins/cache）

站点加速三件事：首页/文章页 HTML 静态缓存、后台清理缓存按钮、静态资源长过期头。三个开关都在后台，插件停用后所有效果立刻消失。

### 17.1 启用 / 停用
后台 → 插件扩展 → 「缓存优化」→ 启用。启用时会建好 `runtime/pagecache` 目录，并把静态资源过期头写进 `public/.htaccess` 的独立区块（`# BEGIN cache-plugin` … `# END cache-plugin`）。
停用时自动清空页面缓存、摘掉 `.htaccess` 里的区块，前台回到原生 TP8 行为；配置保留，重新启用直接接上。

### 17.2 后台（/admin/plugin/cache）
- 三个开关：**首页 HTML 静态缓存**、**文章页 HTML 静态缓存**、**静态资源过期头**。
- 页面缓存有效期（分钟，1–1440，默认 30）；静态资源缓存天数（1–3650，默认 365）。
- 「立即清理全部缓存」按钮：显示当前文件数/占用，一键清空，下一位访客触发重新生成。
- 缓存条目表：路径、类型（首页/文章页）、大小、生成时间，一眼看出缓存了什么。

### 17.3 页面缓存怎么工作
1. `app/middleware/PageCache.php` 注册进全局中间件（`app/middleware.php`）；插件没启用时它只 `return $next()`，什么都不做。
2. 插件启用后，中间件判断：GET + 无用户参数 + 路径命中 `/`、`/en/`、`/a/{id}`、`/en/a/{id}`，才走缓存逻辑。
3. 命中：直接 `response(缓存 HTML)`，不查库、不渲染模板，响应头带 `X-Page-Cache: HIT` 和 `Age`；未命中：正常渲染后落盘，响应头 `X-Page-Cache: MISS`。
4. 缓存文件名是路径 md5，旁边存一份 `.json` 记录路径/类型/时间，方便后台列表与清理。
5. 后台任何写操作（保存文章/栏目/单页/设置/主题/轮播广告…）都会自动清空页面缓存，改完内容访客立刻看到新页面；带查询参数的 URL 一律不缓存（避免 `?v=` 钉死旧页）。
6. 文章页命中缓存时不会执行 `views +1`，想精确统计浏览量就把文章页缓存关掉。

### 17.4 静态资源过期头
`public/.htaccess` 里按 `REQUEST_URI` 只对 `/uploads` 与 `/static` 生效：

```apache
<If "%{REQUEST_URI} =~ m#^/(uploads|static)/#">
  Header set Cache-Control "public, max-age=31536000, immutable"
  Header set Expires "Wed, 31 Dec 2037 23:55:55 GMT"
</If>
```

实测 `/uploads/qc.jpg` 响应头为 `Cache-Control: public, max-age=31536000, immutable`。上传图片是带时间戳的新文件名，不会因为长缓存导致旧图；`/theme/*.css` 走 PHP 路由（`Index::asset`），不受这段影响，改主题样式后访客刷新即可。

### 17.5 实测记录
- 首页：第一次 `MISS`，第二次 `HIT`（`Age: 0`，`Cache-Control: public, max-age=1800`）；把缓存文件时间改成 1 小时前再访问 → 自动 `MISS` 重生成。
- 文章页：`/a/14`、`/a/10`、`/en/a/14` 各自 `MISS → HIT`；`/c/product` 列表页、`/search` 不缓存（响应头无 `X-Page-Cache`）。
- 后台保存文章/登录等任意 POST：页面缓存文件立即归零（自动失效）。
- 清理按钮：4 个文件 / 151.8 KB 一键清空。
- 停用插件：`.htaccess` 区块消失、缓存文件清空、前台无 `X-Page-Cache`、`/uploads` 不再带长缓存头；重新启用后区块与缓存恢复正常。
- 缓存命中页面里客服浮窗、轮播、导航都正常（客服走独立接口，不受 HTML 缓存影响）。

### 17.6 框架层改动（两处）
- `app/middleware.php`：全局注册 `\app\middleware\PageCache::class`。
- `app/service/PluginManager.php`：插件停用时多执行一个 `disable.php` 钩子（和 install/uninstall 同机制），让插件能自己收拾运行痕迹。不装这个钩子的老插件完全不受影响。

---

## 十八、安装向导 /install 与模块中心

### 18.1 首次安装流程
文件刚上传（没有 `.env` 或数据库里没有 `tp_admin` 表、也没有 `runtime/install.lock`）时，全站任何请求都会被 `app/middleware/NeedInstall.php` 302 到 `/install`；后台 `/admin/*` 也统一跳到 `/install`（旧入口 `/admin/install` 保留，仅做跳转）。

安装页 `app/controller/Install.php` + `app/view/install.php` 一页完成三步：
1. **环境检测**（9 项）：PHP ≥ 8.0、pdo_mysql、mbstring、Zip、runtime / themes / plugins / modules / 站点根目录可写。有红色项时安装按钮禁用。
2. **数据库 + 站点 + 管理员**：地址/端口/库名/账号/密码/表前缀，可勾选「库不存在时自动创建」；站点名称、口号；管理员用户名（3-20 位）与密码（≥6 位，两次确认）。
3. **执行安装**：`Installer::prepareDatabase()` 建库/连通性测试 → `Installer::writeEnv()` 写 `.env`（保留其它键，只覆盖 DB_* 与基础项）→ `Installer::useDatabase()` 让当前请求切到新库 → `Installer::install()` 建 7 张表 + 写入初始数据 → 写 `runtime/install.lock`。

安装完成后访问 `/install` 显示「系统已安装」，不再允许重复安装；重装需先备份并删除 `runtime/install.lock`。已有站点也已补写该锁文件，避免每次请求都 `SHOW TABLES` 判断安装状态（缓存命中时不再多查一次库）。

### 18.2 首装只给「默认模板 + 演示插件」
`Installer::install()` 只做两件事：
- `ThemeManager::scan()` / `PluginManager::scan()` **只注册磁盘上真实存在的模块**；
- 启用 `default` 模板、启用 `example` 演示插件。

新站点发布包里，`themes/` 只有 `default`、`plugins/` 只有 `example`，其余主题/插件全部以 zip 放在 `modules/store/`，所以首装天然就是极简的，不存在“装完一堆插件”的情况。

### 18.3 模块中心（/admin/module）
后台「扩展 → 模块中心」，三块内容：
1. **已安装模块**：扫描 `themes/`、`plugins/`，显示类型、版本、启用状态；主题可直接「启用」，插件可「停用/启用/设置」。`default` 与 `example` 标记为「内置」。
2. **模块仓库**：扫描 `modules/store/*.zip`，读取包内 `theme.json` / `plugin.json` 得到标题、版本、说明，状态分「已安装 / 未安装 / 无效包」。未安装的提供 **安装**、**安装并启用**、**删除包** 三个动作。
3. **上传模块包**：直接选 zip 上传，保存到 `modules/store/` 后立即解压安装（也支持把别处下载的模块包直接拖进来）。

安装实现 `app/service/ModuleMarket.php`：
- 打开 zip 校验条目（拒绝 `..` 与绝对路径，防目录穿越）；
- 解压到 `runtime/module_tmp_*`，找到以模块名命名的目录，`rename` 到 `themes/{name}` 或 `plugins/{name}`（跨盘则递归复制）；
- 主题：`ThemeManager::scan()` 注册，可选立即启用；插件：`PluginManager::scan()` + 执行插件自己的 `install.php`，可选立即启用；
- 已安装的同名模块直接拒绝，避免覆盖。

### 18.4 模块包规范与打包
zip 第一层必须是「模块名」目录，目录里放清单：

```
gangtie2/theme.json + 模板文件 + screenshot.png   → themes/gangtie2/
slider/plugin.json + 控制器/模板/install.php        → plugins/slider/
```

PowerShell 打包：

```powershell
Compress-Archive -Path themes\gangtie2 -DestinationPath modules\store\theme-gangtie2.zip -Force
Compress-Archive -Path plugins\slider -DestinationPath modules\store\plugin-slider.zip -Force
```

仓库现有 7 个包：`theme-gangtie.zip`、`theme-gangtie2.zip`、`plugin-slider.zip`、`plugin-cache.zip`、`plugin-kefu.zip`、`plugin-dbbackup.zip`、`plugin-multilang.zip`；说明见 `modules/README.md`。

### 18.5 实测记录（独立测试站 + PHP 内置服务器）
在站点副本（只保留 `themes/default`、`plugins/example` 与 `modules/store`，无 `.env`）用 `php -S 127.0.0.1:8899` 跑完整流程：
- 访问 `/` → 302 `/install`；安装页 9/9 项检测通过；
- 提交安装（库 `tempqiyecms_fresh`、前缀 `tp_`、admin/admin123）→ 建 7 张表，`tp_setting.theme=default`，`tp_plugin` 只有 `example(enabled=1)`，`tp_theme` 只有 `default(enabled=1)`，首页 200；
- 后台登录 → 模块中心 → 安装 `theme-gangtie.zip`（安装并启用）→ `themes/gangtie` 出现、`theme=gangtie`、首页切到钢铁风（`gt-top` 结构）；
- 再安装 `plugin-slider.zip`（安装并启用）→ 自动建 `tp_plugin_slider` 表、插件 enabled=1、首页出现轮播；
- 测试完已删除测试库 `tempqiyecms_fresh`、测试目录与内置服务器进程，当前站点数据未改动。

### 18.6 本轮新增/改动文件
- 新增：`app/middleware/NeedInstall.php`、`app/controller/Install.php`、`app/view/install.php`、`app/view/install_done.php`、`app/service/ModuleMarket.php`、`app/controller/admin/Module.php`、`app/view/admin/module/index.php`、`modules/README.md`、`.env.example`、`modules/store/*.zip`（7 个）
- 改动：`app/service/Installer.php`（安装锁、环境检测、建库/写 .env/运行时切库、最小化首装）、`app/middleware.php`（注册 NeedInstall）、`app/middleware/AdminAuth.php`（未安装跳 `/install`）、`app/controller/admin/Install.php`（旧入口跳转）、`route/app.php`（/install 路由）、`route/admin.php`（模块中心路由）、`app/view/admin/_header.php`（侧栏「模块中心」）
