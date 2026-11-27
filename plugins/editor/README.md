# 编辑器切换（editor）

Modera CMS 后台编辑器切换插件，内置两种编辑器：

- **WangEditor**：默认富文本模式，适合日常文章、单页排版。
- **Vditor**：Markdown 模式，支持即时渲染、分屏预览，提交时自动转换为 HTML。

## 使用

1. 在「插件扩展」中启用本插件，安装脚本会把静态资源部署到 `static/editor`；如果站点使用标准 `public` 作为文档根，会同时写入 `public/static/editor`。
2. 进入「插件设置 → 编辑器切换」，选择默认编辑器并保存。
3. 打开文章编辑或单页编辑页面，刷新即可看到对应编辑器。

> 本插件依赖核心 `rich_editor()` 的插件分发能力。若后台编辑器没有变化，请确认使用的是包含该分发逻辑的最新 Modera CMS 主程序；旧版本可在 `app/common.php` 的 `rich_editor()` 开头加入插件调用。

## 兼容性

- 内容字段仍保存 HTML，前台主题、文章详情页和单页模板无需改动。
- 从 WangEditor 切到 Vditor 时，编辑页会用 Turndown 将已有 HTML 转成 Markdown 展示；保存时用 marked 转回 HTML。
- 插件停用后，后台自动回落到内置 WangEditor；重新启用即可继续使用。

## 文件

- `service/Editor.php`：编辑器配置读取与两种编辑器渲染。
- `assets/wangeditor/`：WangEditor 运行资源。
- `assets/vditor/`：Vditor 核心资源（Lute、图标、语言包、代码高亮）。
- `assets/vendor/`：Turndown 与 marked，用于 HTML/Markdown 互转。
- `install.php` / `uninstall.php`：部署和清理 `static/editor`（兼容 `public/static/editor`）。

## 打包

将整个 `plugins/editor` 目录压缩为 `theme-...` 不适用，本项目插件市场包命名为：

```text
plugins/plugin-editor.zip
```

压缩包内保持 `editor/` 目录结构即可。
