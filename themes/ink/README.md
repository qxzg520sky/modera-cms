# 墨白博客模板 / Ink

一套面向技术博客与建站笔记的 Modera CMS 主题。设计取向是「编辑式排版 + 低饱和视觉」：暖纸色浅色主题、墨黑绿深色主题、衬线标题、宽松留白和轻薄阴影。没有大面积渐变、没有高饱和装饰，重点放在标题、摘要和阅读节奏上。

## 功能

- 博客首页：大标题 + 本期推荐 + 分类导航 + 最新文章网格
- 分类/搜索列表：分类大标题、文章卡片、分页
- 文章详情：窄阅读栏、正文排版、作者信息、上一篇/下一篇、同栏目推荐
- 单页模板：关于、联系、说明等独立页面
- 浅色 / 深色双主题，跟随系统偏好，支持手动切换并记住选择
- PC / 平板 / 手机自适应
- 文章卡片展示：封面、分类、发布时间、阅读量、标题、摘要、阅读全文
- SEO：动态 title / description / keywords、canonical、Open Graph、Twitter Card、文章 JSON-LD
- 图片懒加载：上传封面使用 `loading="lazy"`；自动封面在接近视口时才用 Canvas 绘制

## 封面逻辑（重点）

封面由 `_helpers.php` 的 `ink_cover()` 统一输出：

1. 文章 `cover` 字段不为空：优先使用后台上传的封面。
2. `cover` 为空：输出 `<canvas width="1600" height="1000">`，由 `theme.js` 按文章标题、摘要、分类、日期和种子生成默认封面。
3. 上传封面加载失败时：自动切换为 Canvas 封面，不会出现空白卡片。

自动封面不调用任何第三方图片接口，完全在浏览器本地绘制。它使用固定视觉规则 + 可复现的伪随机：

- 画布 1600 × 1000
- 6 种几何版式：圆环、右侧色带、拱形、斜切、横向条带、左上圆弧
- 3 套低饱和调色板（浅色 / 深色各一套）
- 用 `id + slug + title` 做 FNV 哈希，同一篇文章每次生成的封面一致
- 标题最多 3 行、摘要最多 2 行，自动避头标点（不让「，。、」出现在行首）
- 分类、日期、编号作为页脚信息
- 切换深浅色主题时重绘自动封面

模板会优先保证上传封面，自动封面只是兜底，不会覆盖后台已设置的图片。

## 安装

1. 将 `ink` 目录放入 Modera CMS 的 `themes/` 下：

```text
themes/
└── ink/
    ├── theme.json
    ├── _helpers.php
    ├── _top.php
    ├── _bottom.php
    ├── index.php
    ├── list.php
    ├── article.php
    ├── page.php
    ├── style.css
    ├── theme.js
    ├── screenshot.png
    └── README.md
```

2. 后台进入「模板主题」，启用「墨白博客模板」。
3. 也可以直接写入设置：

```sql
UPDATE tp_setting SET value = 'ink' WHERE name = 'theme';
```

4. 清理页面缓存后访问首页。

## 自定义

主题颜色、字体、圆角、阴影都集中在 `style.css` 顶部的 CSS 变量：

```css
:root[data-theme="light"] {
  --bg: #f4f1ea;
  --surface: #fbfaf6;
  --ink: #1c1f1b;
  --accent: #7d8f79;
  --clay: #b18f7c;
  --radius: 18px;
}
```

自动封面的调色板在 `theme.js` 的 `PALETTES` 中，版式在 `drawGeometric()` 中。改这两个位置即可调整默认封面的气质，不需要改 PHP。

## 数据与路由

主题沿用 Modera CMS 的现有路由和字段：

- 首页：`/`
- 分类：`/c/:slug`
- 文章：`/a/:id`
- 单页：`/p/:slug`
- 搜索：`/search?keyword=...`
- 静态资源：`/theme/:file`

文章字段使用 `title / summary / content / cover / views / create_time / category_id / author`，多语言字段通过 `ml()` 读取。

## 兼容性

- Modera CMS（ThinkPHP 8）
- PHP 8.1+
- 不依赖第三方图片服务、不依赖构建工具
- 与会员、客服等前台插件共存；插件按钮的默认高饱和蓝色会被主题变量收敛为苔绿 / 墨色

## 文件说明

| 文件 | 作用 |
| --- | --- |
| `index.php` | 首页、推荐位、分类导航、最新文章 |
| `list.php` | 分类与搜索列表 |
| `article.php` | 文章详情、JSON-LD、相关推荐 |
| `page.php` | 单页 |
| `_helpers.php` | 日期、摘要、阅读量、分类、封面、卡片、分页 |
| `_top.php` | head、SEO、OG、导航、主题切换 |
| `_bottom.php` | 页脚、返回顶部、脚本引入 |
| `style.css` | 双主题变量、布局、卡片、正文排版、响应式 |
| `theme.js` | 主题切换、移动菜单、懒绘制 Canvas 封面 |

## 版本

- 1.0.0
- 作者：Modera CMS
