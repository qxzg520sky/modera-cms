# weike 主题（为科工业模板）

仿制 `http://temp-weike/`（源目录 `D:\phpstudy_pro\WWW\temp-huanbao`）的蓝白工业风企业模板，已改造成 `tempqiyecms` 可切换主题。页面里的栏目、文章、单页、搜索、多语言全部走后台数据，不保留静态 HTML 的锚点跳转。

## 文件结构

```
themes/weike/
├── theme.json           # 主题清单
├── _top.php             # 头部：logo + 主导航 + 会员/语言/搜索
├── _bottom.php          # 页脚、SEO 之外的脚本、商城/会员钩子
├── index.php            # 首页：首屏 + 产品 + 解决方案 + 案例 + 技术支持 + 新闻 + 关于 + 联系
├── list.php             # 栏目列表页（/c/{slug}、/search）
├── article.php          # 文章详情（/a/{id}）
├── page.php             # 单页（/p/{slug}，关于/联系/企业文化共用）
├── style.css            # 原模板 reset + thems + responsive，外加 CMS 适配层
├── screenshot.png       # 后台主题预览图
├── jquery.js            # 头部/首屏/表单交互
├── logo.png、bg_*.jpg/png、banner_*.jpg、banner_b_clean.jpg、pic1—pic8.jpg、icon*.png、s_btn.png
└── README.md
```

资源全部放在主题根目录，是为了适配 `Index::asset()` 的现有规则：`/theme/{file}` 只接受主题根目录下的单文件，路径会做 `basename()`，不能直接输出 `Assets/css/...` 这种多级目录。所以原模板的 `Assets/css`、`Assets/js`、`Assets/images`、`Assets/upload` 已扁平化到主题根目录，CSS 里的 `url(../images/x)` 也批量改成了 `url(/theme/x)`。

## 启用

1. 后台「模板主题」选择 **为科工业模板**；
2. 或直接改设置表：

```sql
UPDATE tp_setting SET value='weike' WHERE name='theme';
```

3. 切换后清一下整页缓存：

```powershell
Remove-Item D:\phpstudy_pro\WWW\tempqiyecms\runtime\pagecache\* -Recurse -Force
```

## 数据映射

| 模板区块 | 数据来源 |
| --- | --- |
| 首屏轮播 | 产品/解决方案里带封面的最新文章；启用轮播广告插件时由 `plugin_filter('home_slides')` 接管 |
| 产品中心 | 栏目 `product` 下前 3 篇，圆形裁切封面 |
| 解决方案 | 栏目 `solution` 下前 3 篇 |
| 应用案例 | 栏目 `product` 第 4—6 篇，不足用 `solution` 补 |
| 技术支持 | 左侧固定 3 条通用售后问答；右侧栏目 `service` 最新 5 篇 |
| 新闻中心 | 栏目 `news` 最新 4 篇 |
| 关于我们 | 单页 `about` 正文前 4 个 `<p>` |
| 联系我们 | `site_phone`、`site_email`、`site_address` + 本机邮件留言表单 |
| 主导航 | 首页 + 除 `jobs` 外的顶栏栏目 + 单页 `about`/`contact`，招聘放页脚 |
| 列表页 | `$cat`、`$subNav`、`$articles`、`$total`、`$pager` |
| 详情页 | `$row`、`$cat`、`$prev`、`$next` |

首页首屏、产品、解决方案、案例都做了空数据兜底：没有文章时用 `/theme/banner_b.jpg` 和 `pic1—pic8.jpg` 占位，不会出现空白区块。

## 与商城、会员插件的联动

- 主导航 `<ul class="nav clearfix gt-menu">` 保留了 `gt-menu` 类，`plugin-shop` 的 `front_footer` 钩子会自动把「商城」插到首页之后，窄屏收成购物袋图标；
- `_bottom.php` 保留了 `<?= plugin_hook('front_footer') ?>`，会员插件会在桌面右上角生成登录/注册入口，窄屏生成左下角会员按钮；
- 商城、会员都不写死在主题里，插件停用后入口自动消失。

## 二次修改

子页横幅使用去字版 `banner_b_clean.jpg`（列表/详情）和 `banner_c.jpg`（联系页），不再显示原模板自带的“致力于成为行业内先锋企业”“EQUIPMENT”等固定文案；子页标题改成横幅下方的 52px 深蓝条，避免原来 50px 大字压住图片。

主色在 `style.css` 里搜 `#00a4df` / `#19b1ec`，整站强调色、按钮、页脚都跟这两个值走。首屏高度在 `.banner`、`.wk-hero` 的 `560px`，移动端断点里分别改成 420/360/300。

如果要把首屏换成纯静态图，把 `index.php` 里 `$heroItems` 的循环替换成一张 `<div class="wk-hero-slide on" style="background-image:url('/theme/banner_b.jpg')">` 即可，`$heroItems` 的过滤逻辑和轮播脚本都可以删掉。

## 已验证

- 首页、`/c/product`、`/a/1`、`/p/about`、`/p/contact`、`/c/news`、`/c/service`、`/search?kw=机` 全部 HTTP 200，无 PHP 警告；
- 1440 / 1024 / 768 / 500 宽度的头部导航、首屏、三列卡片均已截图核对；
- 详情页正文已含封面时不再重复输出封面图；
- 商城钩子、会员钩子在该主题下可正常挂载。
