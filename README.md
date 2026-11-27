# Modera 模块下载分支

这个分支是 **Modera CMS 的独立模块下载区**，不是主程序分支。主程序 `main` 分支默认只带：

- `themes/default`：默认企业模板
- `plugins/example`：示例插件
- `modules/store/plugin-example.zip`：示例插件包

其余插件和模板不再放进主程序的 `modules/store`，避免克隆或下载主程序时被迫带走全部模块。需要什么，从这个分支单独下载，或者安装“应用市场”插件后在线获取。

## 应用市场插件

应用市场是独立插件，默认不安装。先下载：

- Gitee：[plugin-market.zip](https://gitee.com/51iqx/modera-cms/raw/market/plugins/plugin-market.zip)
- GitHub：[plugin-market.zip](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/plugins/plugin-market.zip)

然后在后台“模块中心”上传 zip 安装，到“插件扩展”启用，左侧会出现“应用市场”。应用市场会读取本分支的 `catalog.json`，列出可安装模块并支持下载安装。

## 模块目录

目录文件：

- Gitee：[catalog.json](https://gitee.com/51iqx/modera-cms/raw/market/catalog.json)
- GitHub：[catalog.json](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/catalog.json)

目录里的每个条目都带 `file`、`url`、`size`、`sha256`。应用市场下载后会先校验 sha256，再交给模块中心解压安装。

## 单独下载

插件：

| 模块 | 文件 | 下载 |
| --- | --- | --- |
| 缓存优化 `cache` | `plugins/plugin-cache.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/plugins/plugin-cache.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/plugins/plugin-cache.zip) |
| 数据库备份 `dbbackup` | `plugins/plugin-dbbackup.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/plugins/plugin-dbbackup.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/plugins/plugin-dbbackup.zip) |
| 在线客服 `kefu` | `plugins/plugin-kefu.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/plugins/plugin-kefu.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/plugins/plugin-kefu.zip) |
| 企业会员 `member` | `plugins/plugin-member.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/plugins/plugin-member.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/plugins/plugin-member.zip) |
| 多语言 `multilang` | `plugins/plugin-multilang.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/plugins/plugin-multilang.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/plugins/plugin-multilang.zip) |
| 企业商城 `shop` | `plugins/plugin-shop.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/plugins/plugin-shop.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/plugins/plugin-shop.zip) |
| 轮播广告 `slider` | `plugins/plugin-slider.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/plugins/plugin-slider.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/plugins/plugin-slider.zip) |
| 访问统计 `stats` | `plugins/plugin-stats.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/plugins/plugin-stats.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/plugins/plugin-stats.zip) |
| 编辑器切换 `editor` | `plugins/plugin-editor.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/plugins/plugin-editor.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/plugins/plugin-editor.zip) |

> 商城 `shop` 依赖会员 `member`，请先安装并启用会员插件。

> 编辑器切换 `editor` 依赖主程序 `rich_editor()` 的插件分发能力；最新 `main` 已内置。旧版本需先更新 `app/common.php`，否则启用插件后编辑页仍显示内置 WangEditor。

模板：

| 模板 | 文件 | 下载 |
| --- | --- | --- |
| 钢铁风 `gangtie` | `themes/theme-gangtie.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/themes/theme-gangtie.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/themes/theme-gangtie.zip) |
| 工业橙 `gangtie2` | `themes/theme-gangtie2.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/themes/theme-gangtie2.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/themes/theme-gangtie2.zip) |
| 视维蓝 `shiwei` | `themes/theme-shiwei.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/themes/theme-shiwei.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/themes/theme-shiwei.zip) |
| 投资集团 `touzi` | `themes/theme-touzi.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/themes/theme-touzi.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/themes/theme-touzi.zip) |
| 为科工业 `weike` | `themes/theme-weike.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/themes/theme-weike.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/themes/theme-weike.zip) |
| 象邦自然 `xiangbang` | `themes/theme-xiangbang.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/themes/theme-xiangbang.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/themes/theme-xiangbang.zip) |
| 唯星工业 `weixing` | `themes/theme-weixing.zip` | [Gitee](https://gitee.com/51iqx/modera-cms/raw/market/themes/theme-weixing.zip) · [GitHub](https://raw.githubusercontent.com/qxzg520sky/modera-cms/market/themes/theme-weixing.zip) |

## 自建模块源

`catalog.json` 可以放在任意 HTTP 地址，结构如下：

```json
{
  "name": "Modera Module Market",
  "base": "https://example.com/modules/",
  "items": [
    {
      "type": "plugin",
      "name": "shop",
      "title": "企业商城",
      "version": "1.0.0",
      "file": "plugins/plugin-shop.zip",
      "url": "https://example.com/modules/plugins/plugin-shop.zip",
      "sha256": "……",
      "size": 82973,
      "requires": ["member"]
    }
  ]
}
```

在后台“应用市场 → 市场设置”里把目录地址换成自己的地址即可。

## 安全说明

- 所有模块包只从 `catalog.json` 里声明的 `https` 地址下载，并校验 zip 内的 `plugin.json` / `theme.json` 与目录条目一致。
- 有 `sha256` 时会强制校验；校验失败不会安装。
- 应用市场是后台管理员工具，不会在前台暴露安装入口。
