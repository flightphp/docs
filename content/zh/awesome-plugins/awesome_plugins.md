# 令人敬畏的插件

Flight 非常可扩展。有许多插件可用于为您的 Flight 应用程序添加功能。一些得到了 Flight 团队的官方支持，而另一些是微型/精简库，可帮助您入门。

## 身份验证/授权

身份验证和授权对于任何需要对访问进行控制的应用程序至关重要。

- [flightphp/permissions](/awesome-plugins/permissions) - 官方 Flight 权限库。此库是向您的应用程序添加用户和应用程序级权限的简单方法。

## 缓存

缓存是加速应用程序的好方法。有多个缓存库可与 Flight 一起使用。

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 轻巧、简单且独立的 PHP 文件缓存类

## CLI

CLI 应用程序是与您的应用程序交互的好方法。您可以使用它们生成控制器，显示所有路由等。

- [flightphp/runway](/awesome-plugins/runway) - Runway 是一个 CLI 应用程序，可帮助您管理 Flight 应用程序。

## Cookies

Cookie 是在客户端存储小数据块的好方法。可以用于存储用户喜好、应用程序设置等。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie 是一个提供简单有效的管理 Cookie 方式的 PHP 库。

## 调试

在本地环境开发时，调试至关重要。有一些插件可以提升您的调试体验。

- [tracy/tracy](/awesome-plugins/tracy) - 这是一个功能齐全的错误处理程序，可与 Flight 一起使用。它有许多面板可以帮助您调试应用程序。而且很容易扩展和添加自己的面板。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - 与 [Tracy](/awesome-plugins/tracy) 错误处理程序一起使用，此插件添加了一些额外面板，以帮助调试专门针对 Flight 项目。

## 数据库

数据库是大多数应用程序的核心。这是您存储和检索数据的方式。一些数据库库只是用来编写查询的包装器，而另一些是完整的对象关系映射（ORM）。

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 官方 Flight PDO 包装器，是核心的一部分。这是一个简单的包装器，可帮助简化编写查询和执行查询的过程。它不是 ORM。
- [flightphp/active-record](/awesome-plugins/active-record) - 官方 Flight ActiveRecord ORM/Mapper。非常适合轻松检索和存储数据库中的数据的小型库。

## 加密

加密对于存储敏感数据的任何应用程序至关重要。加密和解密数据并不是非常困难，但正确存储加密密钥可能会有些困难。最重要的是永远不要将加密密钥存储在公共目录中或将其提交到代码存储库中。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 这是一个可用于加密和解密数据的库。开始加密和解密数据非常简单。

## 会话

对于构建 Web 应用程序而言，会话对于维护状态和登录信息至关重要，但对于 API 来说并不是特别有用。

- [Ghostff/Session](/awesome-plugins/session) - PHP 会话管理器（非阻塞、闪存、分段、会话加密）。使用 PHP open_ssl 可进行可选的会话数据加密/解密。

## 模板

模板是任何带有用户界面的 Web 应用程序的核心。有许多模板引擎可与 Flight 一起使用。

- [flightphp/core View](/learn#views) - 这是核心的一个非常基本的模板引擎。如果您的项目中有多个页面，则不建议使用它。
- [latte/latte](/awesome-plugins/latte) - Latte 是一个功能齐全的模板引擎，非常易于使用，更接近 PHP 语法，而不是 Twig 或 Smarty。而且很容易扩展和添加自己的过滤器和函数。

## 贡献

有要分享的插件吗？提交拉取请求将其添加到列表中吧！