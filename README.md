# Modera CMS

基于 **ThinkPHP 8.1.4** 的企业建站系统：原生 PHP 模板、主题一键切换、插件模块中心、内置会员与商城，适合企业官网、工厂展示、产品站和中小型内容站。

前台：`/`　后台：`/admin`　默认演示主题：**为科工业（weike）**

> 全部主题与插件的一句话功能说明见 [MODULES.md](MODULES.md)。

## 为什么用它

- **不依赖第三方模板引擎**：主题就是一组 PHP 文件，复制目录、改 `theme.json` 就能开发新模板。
- **插件即模块包**：插件以 zip 包按需安装，后台“模块中心”识别后一键安装、启用、停用、卸载；默认包只带 `example`，其余模块从应用市场或 `market` 分支单独下载。
- **自带企业级功能**：文章、无限级栏目、单页、SEO、轮播、多语言、在线客服、访问统计、数据库备份、会员、商城。
- **前台后台同一套数据**：栏目、文章、单页、导航、设置和插件数据全部来自后台，不写死页面。
- **响应式主题**：PC、平板、手机共用一套模板，默认带 1 套企业主题，另外 6 套从应用市场按需下载。
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

# 主题资源由 PHP 动态输出，必须先于默认的 .css 正则规则
location ^~ /theme/ {
    try_files $uri /index.php?s=$uri&$args;
}

location / {
    try_files $uri $uri/ /index.php?s=$uri&$args;
}

location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

> 宝塔 Nginx 若出现“前台没样式、`/theme/style.css` 404”，这是 Nginx 默认 `.css` 静态规则截走了动态主题资源，不是程序问题。处理步骤见 [docs/nginx-theme-404.md](docs/nginx-theme-404.md)。

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

## 按需安装模块

主程序默认只带 `themes/default` 和 `plugins/example`；`modules/store/` 也只保留 `plugin-example.zip`。其余 8 个插件、6 套模板不再放进主仓库，统一放在独立的 **market 下载分支**，需要哪个单独下载，避免 clone 主程序时被迫带走全部模块。

### 应用市场（推荐）

先下载应用市场插件，上传到“模块中心”安装，再到“插件扩展”启用，后台左侧会出现“应用市场”：

- Gitee：[plugin-market.zip](https://gitee.com/51iqx/modera-cms/raw/market/plugins/plugin-market.zip)
- GitHub：[plugin-market.zip](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/plugins/plugin-market.zip)

应用市场读取远程 `catalog.json`，列出可安装插件/模板，支持单独下载或在线安装；目录地址可在“市场设置”里替换成自己的私有源。

### 手动下载

全部模块的单独下载地址、catalog 格式和自建模块源说明：

- [market 分支 README（Gitee）](https://gitee.com/51iqx/modera-cms/blob/market/README.md)
- [market 分支 README（GitHub）](https://github.com/qxzg520sky/modera-cms/blob/market/README.md)

### 安装模块

**后台上传**：模块中心 → 上传 `.zip` → 自动解压安装 → 到“插件扩展”或“模板主题”启用。

**本地仓库包**：把 zip 放进 `modules/store/`，模块中心会列出；主仓库只带 `plugin-example.zip`。

> 插件 `install.php` 会建表/加字段；生产环境操作前先备份数据库。

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
themes/                默认主题源码，仅保留 default；其他主题在 market 分支
plugins/               默认插件源码，仅保留 example；其他插件在 market 分支
modules/store/         本地模块 zip 包，主仓库仅保留 plugin-example.zip
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

示例插件与 market 分支中的 8 个插件已在 PHP 8.2 + MySQL 5.7 环境完成安装与核心流程测试，包括：

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
- 模块下载区：[GitHub market 分支](https://github.com/qxzg520sky/modera-cms/tree/market) · [Gitee market 分支](https://gitee.com/51iqx/modera-cms/tree/market)

