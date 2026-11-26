# Modera CMS

基于 **ThinkPHP 8.1.4** 的企业建站系统：原生 PHP 模板、主题一键切换、插件模块中心、内置会员与商城，适合企业官网、工厂展示、产品站和中小型内容站。

前台：`/`　后台：`/admin`　默认演示主题：**为科工业（weike）**

> 全部主题与插件的一句话功能说明见 [MODULES.md](MODULES.md)。

![Modera CMS 演示首页](themes/weike/screenshot.png)

## 为什么用它

- **不依赖第三方模板引擎**：主题就是一组 PHP 文件，复制目录、改 `theme.json` 就能开发新模板。
- **插件即模块包**：插件以 zip 包放在 `modules/store/`，后台“模块中心”识别后一键安装、启用、停用、卸载。
- **自带企业级功能**：文章、无限级栏目、单页、SEO、轮播、多语言、在线客服、访问统计、数据库备份、会员、商城。
- **前台后台同一套数据**：栏目、文章、单页、导航、设置和插件数据全部来自后台，不写死页面。
- **响应式主题**：PC、平板、手机共用一套模板，内置 7 套企业主题。
- **可离线部署**：`vendor/` 已随仓库提供，不装 Composer 也能跑；安装向导自动建表。

## 环境要求

| 项目 | 要求 |
| --- | --- |
| PHP | 8.1 及以上，推荐 8.2 / 8.3（已在 PHP 8.2.9 NTS 实测） |
| 数据库 | MySQL 5.7 / 8.0，字符集 `utf8mb4` |
| Web 服务 | Apache / Nginx / phpStudy 均可 |
| PHP 扩展 | `pdo_mysql`、`mbstring`、`fileinfo`、`zip`、`gd`（图片处理） |
| 目录权限 | `runtime/`、`public/uploads/`、`modules/store/` 可写 |

> 站点根目录必须指向 **`public/`**，不要直接指向项目根目录。

## 快速安装

### 1. 克隆仓库

```bash
git clone https://github.com/qxzg520sky/modera-cms.git
# 或
git clone https://gitee.com/51iqx/modera-cms.git
```

### 2. 配置站点

Apache 直接把网站根目录设为 `public/`，项目自带 `.htaccess`。Nginx 使用：

```nginx
root /path/to/modera-cms/public;
index index.php;

location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

### 3. 建库并配置 `.env`

```sql
CREATE DATABASE modera_cms DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

复制 `.env.example` 为 `.env`，填写数据库信息：

```ini
APP_DEBUG = false

DB_TYPE = mysql
DB_HOST = 127.0.0.1
DB_NAME = modera_cms
DB_USER = root
DB_PASS = 你的数据库密码
DB_PORT = 3306
DB_CHARSET = utf8mb4
DB_PREFIX = tp_

DEFAULT_LANG = zh-cn
DEFAULT_TIMEZONE = Asia/Shanghai
```

### 4. 打开安装向导

访问：

```text
http://你的域名/admin
```

按提示填写站点名称、管理员账号和密码。安装器会创建数据表、默认栏目、单页和示例文章，然后跳转登录页。

安装完成后建议：

1. 把 `.env` 里的 `APP_DEBUG` 改为 `false`。
2. 在后台“管理员”里修改初始密码。
3. 不要重复访问安装页；已有数据时再次安装不会清空数据，但也没有必要。
4. 生产环境关闭目录列表，定期备份数据库。

## 插件下载

9 个插件都已经打成后台模块中心可直接安装的 zip 包，放在仓库的 [`modules/store/`](modules/store/) 目录，**每个都能单独下载**：

| 插件 | 版本 | 功能 | 下载 |
| --- | --- | --- | --- |
| 缓存优化 `cache` | 1.0.0 | 首页/文章页 HTML 静态缓存、静态资源长过期头、后台一键清缓存 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/plugin-cache.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/plugin-cache.zip) |
| 数据库备份 `dbbackup` | 1.0.0 | 一键备份/下载/还原 SQL、上传还原、定时备份、旧备份清理 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/plugin-dbbackup.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/plugin-dbbackup.zip) |
| 在线客服 `kefu` | 1.0.0 | 浮动客服面板、坐席名片、工作时间判断、留言与实时会话 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/plugin-kefu.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/plugin-kefu.zip) |
| 企业会员 `member` | 1.1.1 | 会员资料、等级折扣、余额、积分、地址、业务员、Excel 导入导出 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/plugin-member.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/plugin-member.zip) |
| 多语言 `multilang` | 1.0.0 | 栏目/文章/单页/设置增加外语字段，`/en/` 前缀切换语言 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/plugin-multilang.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/plugin-multilang.zip) |
| 企业商城 `shop` | 1.0.0 | 商品、分类、SKU、购物车、订单、售后、优惠券、余额支付、积分抵扣 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/plugin-shop.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/plugin-shop.zip) |
| 首页轮播 `slider` | 1.0.0 | 后台维护首页大图、标题、按钮、链接、排序与启停 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/plugin-slider.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/plugin-slider.zip) |
| 访问统计 `stats` | 1.0.0 | UV/PV、访问趋势、来路来源、热门文章与页面排行、最近访客 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/plugin-stats.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/plugin-stats.zip) |
| 示例插件 `example` | 1.0.0 | 后台菜单、配置项、独立控制器和前台路由的开发脚手架 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/plugin-example.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/plugin-example.zip) |

### 安装插件（两种方式）

**方式一：仓库自带包**

1. 把对应 `plugin-*.zip` 放进站点的 `modules/store/` 目录（仓库里已经放好）。
2. 登录后台，打开“模块中心”。
3. 在“模块仓库”找到插件，点“安装”或“安装并启用”。
4. 到“插件扩展”里确认状态；需要配置的插件点“配置”。

**方式二：后台上传**

1. 后台打开“模块中心”。
2. 上传 `.zip` 模块包。
3. 上传后会自动解压安装，再到“插件扩展”启用。

> 安装插件时，插件自己的 `install.php` 会建表或加字段；停用/卸载时会按插件声明清理。生产环境操作前先备份数据库。

## 主题下载

仓库同时附带 7 套响应式企业主题，也是可直接安装的模块包：

| 主题 | 版本 | 风格 | 下载 |
| --- | --- | --- | --- |
| 默认企业 `default` | 1.0.0 | 简洁企业官网，轮播 + 栏目 + 资讯 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/theme-default.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/theme-default.zip) |
| 钢铁风 `gangtie` | 1.0.0 | 钢铁贸易、蓝白工业风、产品墙与案例块 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/theme-gangtie.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/theme-gangtie.zip) |
| 工业橙 `gangtie2` | 1.0.0 | 黑橙工业风、全屏轮播、方案分栏、询价表单 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/theme-gangtie2.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/theme-gangtie2.zip) |
| 视维蓝 `shiwei` | 1.0.0 | 白底蓝字、框线标题、解决方案与产品横滑 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/theme-shiwei.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/theme-shiwei.zip) |
| 投资集团 `touzi` | 1.0.0 | 金融投资集团、蓝色首屏、业务切换与集团风采 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/theme-touzi.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/theme-touzi.zip) |
| 为科工业 `weike` | 1.0.0 | 蓝白工业、建筑横幅、产品/方案/案例/新闻，兼容商城导航 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/theme-weike.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/theme-weike.zip) |
| 象邦自然 `xiangbang` | 1.0.0 | 深绿与米白的自然系企业官网，含全屏主视觉、产品/方案/服务/新闻、移动菜单与商城入口 | [GitHub](https://github.com/qxzg520sky/modera-cms/raw/main/modules/store/theme-xiangbang.zip) · [Gitee](https://gitee.com/51iqx/modera-cms/raw/main/modules/store/theme-xiangbang.zip) |

安装主题和安装插件一样：包放进 `modules/store/`，后台“模块中心”点安装，再到“模板主题”启用。

## 目录结构

```text
app/
  controller/          前台控制器
  controller/admin/    后台控制器（文章、栏目、单页、主题、插件、模块中心、会员等）
  service/             ThemeManager、PluginManager、ModuleMarket、Installer、View
  view/                后台原生 PHP 模板
config/                数据库、缓存、会话、路由等配置
public/                Web 入口，站点根目录指向这里
  static/              后台与公共静态资源
  uploads/             后台上传文件（运行数据，默认不纳入版本库）
route/                 前台与后台路由
themes/                主题源码（default、gangtie、gangtie2、shiwei、touzi、weike、xiangbang）
plugins/               插件源码（cache、dbbackup、example、kefu、member、multilang、shop、slider、stats）
modules/store/         可安装的插件/主题 zip 包
runtime/               运行缓存、日志、备份（默认不纳入版本库）
vendor/                ThinkPHP 框架与依赖
```

## 插件包格式

一个合法的插件包结构如下，顶层目录名必须和插件名一致：

```text
plugin-shop.zip
└── shop/
    ├── plugin.json      # 名称、标题、版本、作者、说明、后台菜单
    ├── install.php      # 安装/启用时执行：建表、加字段
    ├── uninstall.php    # 卸载时执行：删表、清数据
    ├── disable.php      # 停用时执行：撤销运行时改动
    ├── route.php        # 插件路由
    ├── controller/      # 前台/后台控制器
    ├── view/            # 插件模板
    └── ...
```

`plugin.json` 最少包含：

```json
{
  "name": "shop",
  "title": "企业商城",
  "version": "1.0.0",
  "author": "Modera CMS",
  "description": "独立商城插件"
}
```

主题包同理，顶层目录下放 `theme.json`、`_top.php`、`_bottom.php`、`index.php`、`list.php`、`article.php`、`page.php` 和 `style.css`。

## 已验证范围

本仓库中的 9 个插件已在 PHP 8.2 + MySQL 5.7 环境完成安装与核心流程测试，包括：

- 后台插件入口、配置项、启停与卸载；
- 文章/栏目/单页/轮播/多语言前台联动；
- 会员注册登录、等级折扣、余额、积分、地址与导入导出；
- 商城商品、SKU、购物车、下单、余额支付、积分抵扣、发货、售后与退款；
- 客服会话、留言、后台回复；
- 统计 UV/PV、来路与排行；
- 数据库备份、下载、还原与定时任务；
- 缓存命中、清理与停用还原。

商城“售后审核同意退款”已补写管理员备注，并把补丁包含在 `plugin-shop.zip` 中。

## 开发一个插件

1. 复制 `plugins/example/` 为新目录，例如 `plugins/demo/`。
2. 修改 `plugin.json` 的 `name`、`title`、`version`。
3. 在 `route.php`、`controller/`、`view/` 里写自己的页面。
4. 如需建表，在 `install.php` 里执行 SQL；在 `uninstall.php` 里清理。
5. 后台“模块中心”刷新即可看到；打包时保持“demo/plugin.json”的顶层结构。

## 安全建议

- 不要提交 `.env`、数据库密码、后台账号密码、`runtime/` 日志和 `uploads/` 用户文件。
- 上线后把 `APP_DEBUG` 设为 `false`。
- 安装完立即修改后台初始密码，并限制 `/admin` 访问来源。
- 定期使用“数据库备份”插件做异地备份。
- 主题中的演示图片仅用于版式展示，商用前请替换为自有素材。

## 许可证

本仓库的业务代码采用 **MIT License**，见 [LICENSE](LICENSE)。

`vendor/` 下的 ThinkPHP 框架及其依赖遵循各自的开源许可证（ThinkPHP 为 Apache-2.0），见 [LICENSE.txt](LICENSE.txt) 与各依赖包内的许可证文件。主题模板仅用于学习与建站参考，模板中涉及的图片、商标和版式素材版权归原权利人，不在 MIT 授权范围内；商用请自行取得授权或替换素材。

## 链接

- GitHub：<https://github.com/qxzg520sky/modera-cms>
- Gitee：<https://gitee.com/51iqx/modera-cms>
- 模块包目录：[GitHub](https://github.com/qxzg520sky/modera-cms/tree/main/modules/store) · [Gitee](https://gitee.com/51iqx/modera-cms/tree/main/modules/store)
