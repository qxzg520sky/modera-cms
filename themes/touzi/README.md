# 投资集团模板（touzi）

仿 `temp-touzi`（清谷科技版式）的响应式主题，已适配 `tempqiyecms` 的栏目、文章、单页与多语言。

## 文件结构

```
themes/touzi/
├── _top.php       页头、主导航、下拉菜单、语言切换、移动端菜单
├── _bottom.php    深色页脚、全站交互脚本（首屏/业务/风采/学院/内页 MENU）
├── index.php      首页：首屏、业务介绍、集团视角、集团风采、集团学院、关于集团
├── list.php       栏目列表：产品网格、方案双栏、新闻卡片、服务/招聘列表
├── article.php    文章详情：产品双栏详情、新闻/方案正文、上一篇下一篇
├── page.php       单页：关于我们、企业文化、联系我们三卡
├── style.css      全站样式与 1199 / 991 / 767 / 479 断点
├── theme.json     主题元信息
├── screenshot.png 后台主题缩略图（1440×1000）
└── README.md
```

## 数据映射

| 页面区块 | 数据来源 |
| --- | --- |
| 首屏 3 组轮播 | 首页 `index.php` 内固定数组，图片取 `/uploads/line.jpg`、`factory.jpg`、`qc.jpg` |
| 业务介绍 6 卡（2 页） | `index.php` 的 `$bizCards`，链接分别指向产品/加工栏目与方案/服务/定制文章 |
| 集团视角 | `news` 栏目最新 3 篇文章，左侧大图取第一篇封面 |
| 集团风采 | 固定 4 张图 + 文案，可在 `$presence` 中替换 |
| 集团学院 | 固定 3 组培训内容，可在 `$institute` 中替换 |
| 关于集团 | 自动读取 `about` 单页正文的前 4 段 |
| 列表/详情 | 后台栏目与文章；产品、方案、新闻分别使用不同列表版式 |
| 联系我们 | `contact` 单页 + 站点设置中的电话、邮箱、地址 |

## 使用说明

- 启用：后台切换主题，或执行 `UPDATE tp_setting SET value='touzi' WHERE name='theme';`
- 改样式后如页面未更新：清空 `runtime/pagecache/*.html`，并递增 `_top.php` 中 `style.css?v=` 的版本号。
- 主题内图片只使用 `/uploads` 下的现有素材，未新增外部依赖；业务图标为内联 SVG。
- 移动端 `≤767px` 切换为汉堡菜单；内页右上角 `MENU` 为栏目/单页下拉导航。
