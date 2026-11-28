# 访问统计（stats）

轻量 UV / PV 统计插件，类似 Umami 的思路：前台只发一个几行 JS 的 beacon，后端只写按天聚合的数据，不保存逐条访问日志。

## 入口

- 后台：`/admin/plugin/stats`
- 采集：`POST /stats/collect`（只接受 POST，异常不影响前台）
- 菜单：安装并启用后自动出现在后台「插件菜单」，标题「访问统计」

## 统计口径

- **PV**：页面被打开的次数，刷新也算；后台、安装页、`/uploads`、`/static` 等路径不计。
- **UV**：当天独立匿名访客数，用第一方 cookie `modera_vid` 去重；同一天同一页面的 UV 另用 `stats_page_visitor` 去重。
- **来路**：访客当天第一次落地时的来源域名；站内跳转记为「内部跳转」，空来源记为「直接访问」。
- **文章排行**：按文章页 PV 排序，标题会用数据库里的最新中英文标题覆盖客户端传来的标题。
- **保留期**：默认 90 天，可改 7–365 天；约 1/300 次访问顺带清理一次过期聚合数据。

## 数据表

只建三张表，全部按天聚合，唯一索引负责去重：

| 表 | 唯一键 | 作用 |
|---|---|---|
| `tp_stats_page` | `day + page_key` | 每页每天 PV / UV、标题、类型、首次/最后访问 |
| `tp_stats_visitor` | `day + visitor` | 每个匿名访客每天一行：来路、落地页、点击次数 |
| `tp_stats_page_visitor` | `day + page_key + visitor` | 页面 UV 去重 |

每次访问最多 3 条 `INSERT ... ON DUPLICATE KEY UPDATE` / `INSERT IGNORE`，不存原始 IP、不存完整 UA。

## 与缓存插件共存

beacon 通过主题的 `plugin_hook('front_footer')` 输出。页面被缓存插件命中时，HTML 里的 JS 仍会在浏览器执行，因此 PV 不会漏。

启停插件时会顺带清理一次 `runtime/pagecache`，避免缓存里残留旧版 beacon。

## 安装 / 卸载

- 后台「模块中心」上传 `modules/store/plugin-stats.zip`，或直接把 `stats` 目录放到 `plugins/` 后在「插件扩展」启用。
- 首次启用会执行 `install.php` 建表并写入默认配置。
- 停用只停止采集和后台入口，保留历史数据。
- 卸载会删除三张统计表和插件配置，数据不可恢复。

## 文件结构

```
stats/
├── plugin.json
├── install.php / disable.php / uninstall.php
├── route.php
├── controller/Admin.php     # 后台概览、设置、清理
├── controller/Collect.php   # beacon 接收与清洗
├── service/Stats.php        # 建表、写入、查询、清理
├── hook/front_footer.php    # 前台 JS beacon
└── view/index.php           # 后台仪表盘
```
