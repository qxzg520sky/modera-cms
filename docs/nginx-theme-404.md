# 前台样式丢失 / 主题 CSS 404：Nginx 部署说明

## 结论

**这不是程序缺陷，也不是主题文件损坏。**

这是宝塔 Nginx 默认静态规则与 CMS 动态主题资源路由之间的优先级冲突。程序本身的主题输出逻辑是正常的，补一条 Nginx 伪静态即可解决。

## 现象

- 网站首页能打开，但页面没有样式，像“没穿衣服”。
- 浏览器 F12 的“网络”里，`/theme/style.css`、`/theme/site.js` 等返回 `404`。
- 直接访问 `/theme/style.css` 是 404，但访问 `/index.php?s=/theme/style.css` 能正常返回 CSS 内容。
- 后台通常正常，说明 PHP、数据库和程序本身没有整体故障。

## 根因

宝塔 Nginx 默认配置里通常有类似规则：

```nginx
location ~ .*\.(js|css)?$ {
    expires 12h;
    # ...
}
```

这是**正则 location**，优先级高于普通的 `location /`。

请求 `/theme/style.css` 时，Nginx 先用这条规则把它当成普通静态文件，只去磁盘找：

```text
public/theme/style.css
```

但主题文件实际在：

```text
themes/<当前主题>/style.css
```

并且由程序中的 `/theme/:file` 路由动态读取输出。磁盘上并不存在 `public/theme/style.css`，所以 Nginx 直接返回 404，请求根本没有进入 PHP。

`/index.php?s=/theme/style.css` 能打开，是因为它绕过了静态规则，直接进入 ThinkPHP 路由，这也反证了主题文件本身没有丢失。

## 修复：宝塔面板

进入：

```text
宝塔面板 → 网站 → 找到站点 → 设置 → 伪静态
```

在原有规则中加入以下内容，保存即可，不需要重启 Nginx：

```nginx
# 主题资源走程序动态路由
location ^~ /theme/ {
    try_files $uri /index.php?s=$uri&$args;
}

# 插件资源目录，按实际插件路径增加
location ^~ /shop/asset/ {
    try_files $uri /index.php?s=$uri&$args;
}

location ^~ /kefu/asset/ {
    try_files $uri /index.php?s=$uri&$args;
}

location ^~ /member/asset/ {
    try_files $uri /index.php?s=$uri&$args;
}

location / {
    try_files $uri $uri/ /index.php?s=$uri&$args;
}
```

关键点是 `^~`：Nginx 一旦匹配到这种前缀 location，就不再继续检查后面的正则 location，因此 `.css` 的默认静态规则不会再把请求截走。

`try_files` 会先尝试磁盘上的真实文件；文件不存在时，再回退给 `index.php` 处理。因此主题目录里真实存在的图片、JS 仍然会被 Nginx 直接返回，只有动态资源才进入 PHP。

## 验证

浏览器或命令行访问：

```text
http://你的域名/theme/style.css
```

正常应返回：

```text
200
Content-Type: text/css
```

也可以执行：

```bash
curl -I http://你的域名/theme/style.css
```

前台再按 `Ctrl+F5` 强制刷新一次，样式即可恢复。

## 适用范围与注意事项

- 适用于使用宝塔 Nginx、且主题/插件资源通过 `/theme/`、`/shop/asset/` 等路径动态输出的部署方式。
- Apache 用户通常不需要改 Nginx，项目自带 `.htaccess`。
- 本地 phpStudy 正常、宝塔服务器 404 很常见，因为两边 Web 服务的静态规则不同，不是程序“认环境”。
- 不要直接删除 `location ~ .*\.(js|css)?$`，真实静态文件仍需要它来设置缓存。
- 不要给 `/static/` 这类真实静态目录随意增加 `try_files ... /index.php` 回退；文件缺失时会返回 HTML 的 200，反而掩盖 404，增加排查难度。
- 以后安装的新插件如果新增了动态资源目录，例如 `/mall/asset/`，按同样方式补一条 `^~ /插件目录/` 即可。

## 一句话判断

**前台没样式 + `/theme/style.css` 404 + `/index.php?s=/theme/style.css` 能出 CSS = Nginx 静态规则截胡，不是程序问题。**
