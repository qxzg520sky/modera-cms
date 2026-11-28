# 模块仓库（modules/）

`modules/store/` 保存本站全部主题与插件的 zip 模块包。后台 **模块中心（/admin/module）** 会扫描这里的
`*.zip`，可以直接「安装 / 安装并启用」。即使把 `themes/`、`plugins/` 下对应的目录删掉，模块包仍在，
随时能从模块中心重新装回来。

## 目录

```
modules/
└── store/
    ├── theme-default.zip      # 默认企业主题
    ├── theme-gangtie.zip      # 钢铁风企业主题
    ├── theme-gangtie2.zip     # 工业橙企业主题
    ├── theme-shiwei.zip       # 视维蓝企业模板
    ├── theme-touzi.zip        # 投资集团模板
    ├── theme-weike.zip        # 为科工业模板（当前启用）
    ├── plugin-example.zip     # 示例插件
    ├── plugin-cache.zip       # 缓存优化
    ├── plugin-dbbackup.zip    # 数据库备份
    ├── plugin-kefu.zip        # 在线客服
    ├── plugin-member.zip      # 企业会员管理（会员/等级/余额/积分/业务员）
    ├── plugin-shop.zip        # 企业商城（商品/库存/订单/售后/优惠券，调用会员插件）
    ├── plugin-multilang.zip   # 多语言
    ├── plugin-slider.zip      # 轮播广告
    └── plugin-stats.zip       # 访问统计
```

当前共 6 个主题包、9 个插件包，均为 2026-11-26 的完整代码快照。

## 模块包结构

zip 解压后第一层必须是「模块名」目录，目录里放清单文件：

```
gangtie2/                  # 目录名 = 模块名（英文、数字、下划线、短横线）
├── theme.json             # 主题清单（name/title/version/author/description）
├── screenshot.png
├── index.php ...
└── ...

slider/                    # 插件
├── plugin.json            # 插件清单，可含 config / admin_menu / admin_url
├── install.php            # 可选：安装/启用时执行（建表、建字段）
├── uninstall.php          # 可选：卸载时清理
└── ...
```

## 删除后重装

1. 后台 →「扩展」→ 模块中心；
2. 找到对应模块包，点 **安装**（只解压注册）或 **安装并启用**；
3. 插件安装时会自动执行 `install.php`，随后可在「插件扩展」里停用/启用、配置；
4. 主题安装后到「模板主题」里切换；
5. 也可以把别处下载的模块 zip 直接在模块中心「上传并安装」。

> 删目录只影响代码，不会删除数据库里的栏目、文章、插件配置和插件表。
> 如果要连插件数据一起清理，请在后台先点「卸载」，让 `uninstall.php` 执行完再删目录。
> `default` 与 `example` 也打了包；它们属于内置模块，模块中心会显示「已安装」，一般不需要重装。

## 重新打包（Windows PowerShell）

```powershell
# 单个主题
Compress-Archive -Path themes\touzi -DestinationPath modules\store\theme-touzi.zip -Force

# 单个插件
Compress-Archive -Path plugins\stats -DestinationPath modules\store\plugin-stats.zip -Force
```

批量重打全部主题、插件：

```powershell
$root = 'D:\phpstudy_pro\WWW\tempqiyecms'
Get-ChildItem "$root\themes" -Directory | ForEach-Object {
  Compress-Archive -Path $_.FullName -DestinationPath "$root\modules\store\theme-$($_.Name).zip" -Force
}
Get-ChildItem "$root\plugins" -Directory | ForEach-Object {
  Compress-Archive -Path $_.FullName -DestinationPath "$root\modules\store\plugin-$($_.Name).zip" -Force
}
```
