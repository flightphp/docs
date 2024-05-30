# 令人惊叹的插件

Flight 非常具有可扩展性。有许多插件可用于为您的 Flight 应用程序添加功能。有些由 Flight 团队官方支持，而其他一些是微型/精简库，可帮助您入门。

## 缓存

缓存是加速应用程序的好方法。有许多缓存库可与 Flight 一起使用。

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 轻巧、简单且独立的 PHP 文件缓存类

## 命令行界面

CLI 应用程序是与应用程序进行交互的好方法。您可以使用它们生成控制器，显示所有路由等。

- [flightphp/runway](/awesome-plugins/runway) - Runway 是一个 CLI 应用程序，可帮助您管理 Flight 应用程序。

## Cookies

Cookies 是在客户端存储小数据块的好方法。它们可用于存储用户首选项、应用程序设置等。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie 是一个提供了简单有效的管理 cookie 方式的 PHP 库。

## 调试

在本地环境开发时调试至关重要。有一些插件可以提升您的调试体验。

- [tracy/tracy](/awesome-plugins/tracy) - 这是一个功能齐全的错误处理程序，可与 Flight 一起使用。它有许多面板可以帮助您调试应用程序。而且非常容易扩展和添加自己的面板。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - 与 [Tracy](/awesome-plugins/tracy) 错误处理程序一起使用，此插件添加了一些额外面板，专门帮助调试 Flight 项目。

## 数据库

数据库是大多数应用程序的核心。这是您存储和检索数据的方式。有些数据库库只是用来编写查询的包装器，而有些是完整的 ORM。

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 官方 Flight PDO 包装器，是 Flight 核心的一部分。这是一个简单的包装器，帮助简化编写查询和执行查询的过程。它不是一个 ORM。
- [flightphp/active-record](/awesome-plugins/active-record) - 官方 Flight ActiveRecord ORM/Mapper。非常适合在数据库中轻松检索和存储数据的小库。

## 加密

加密对于存储敏感数据的任何应用程序至关重要。加密和解密数据并不是很困难，但是正确存储加密密钥可能有困难。最重要的是永远不要将您的加密密钥存储在公共目录中或提交到您的代码存储库中。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 这是一个可用于加密和解密数据的库。开始加密和解密数据相当简单。

## 会话

会话对于 API 并不是特别有用，但对于构建 Web 应用程序，会话对于维护状态和登录信息至关重要。

- [Ghostff/Session](/awesome-plugins/session) - PHP 会话管理器（非阻塞、快闪、段、会话加密）。使用 PHP open_ssl 可选加密/解密会话数据。

## 模板

模板是具有 UI 的任何 Web 应用程序的核心。有许多模板引擎可与 Flight 一起使用。

- [flightphp/core View](/learn#views) - 这是 Flight 核心的一个非常基本的模板引擎。如果您的项目页面不止几页，不建议使用此模板引擎。
- [latte/latte](/awesome-plugins/latte) - Latte 是一个功能丰富的模板引擎，非常易于使用，比 Twig 或 Smarty 更接近 PHP 语法。而且非常容易扩展和添加自己的过滤器和函数。

## 贡献

有插件想要分享吗？提交拉取请求将其添加到列表中！