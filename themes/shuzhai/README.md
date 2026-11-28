# 书斋博客模板（shuzhai）

一套刻意“反自适应”的左右分栏博客模板：桌面端固定 1180px 版心，主栏 760px、侧栏 320px；手机端不放媒体查询，直接使用 `mobile/` 下另一套模板。

## 文件

- `theme.json` 模板清单
- `_helpers.php` 封面、日期、摘要、阅读时长等公共方法
- `_top.php` / `_bottom.php` 桌面端页头页脚
- `index.php` 首页（主文 + 最新文章 + 右栏）
- `list.php` 分类 / 搜索列表
- `article.php` 文章详情
- `page.php` 单页
- `style.css` 桌面端样式（无任何响应式断点）
- `theme.js` 回到顶部与图片失败回退
- `cover1.jpg` ~ `cover6.jpg` 内置自动封面
- `mobile/` 独立手机模板，同名文件 + 独立 `style.css`

## 封面规则

`sz_cover_src()` 的优先级：

1. 文章后台上传的 `cover` 字段；
2. `cover` 为空时，从正文 HTML 中提取第一张 `<img src>`（跳过 base64 与 svg/gif）；
3. 正文也没有图片时，按文章 `id % 6` 分配 `cover1.jpg` ~ `cover6.jpg`。

文章详情页如果封面来自正文首图，则不再额外显示头图，避免同一张图出现两次。

## 双端预览

- 电脑版：`http://你的域名/` 或 `?mobile=0`
- 手机版：`?mobile=1`，会写入 30 天 cookie
- 手机模板不存在时会自动回落桌面模板，本模板已提供完整 `mobile/`。

## 注意

- 桌面端 `body min-width: 1180px`，窗口小于 1180 会出现横向滚动，这是设计选择，不是 bug。
- 手机端是独立模板，不要试图把桌面 `style.css` 的宽度规则搬到 `mobile/style.css`。
- 修改含中文的模板文件请使用 UTF-8 编辑器，不要用 ANSI 编码保存。
