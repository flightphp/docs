# 令人敬畏的插件

Flight 是非常可扩展的。有许多插件可用于向您的 Flight 应用程序添加功能。有些得到了 FlightPHP 团队的官方支持，而其他一些是微型/精简库，可帮助您入门。

## 缓存

缓存是加快应用程序速度的好方法。有许多缓存库可与 Flight 一起使用。

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 轻巧、简单且独立的 PHP 文件缓存类

## 调试

在本地环境开发时，调试至关重要。有一些插件可以提升您的调试体验。

- [tracy/tracy](/awesome-plugins/tracy) - 这是一个功能齐全的错误处理程序，可与 Flight 一起使用。它具有许多面板，可帮助您调试应用程序。而且非常容易扩展并添加自己的面板。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - 与 [Tracy](/awesome-plugins/tracy) 错误处理程序一起使用，此插件添加了一些额外面板，可帮助专门用于 Flight 项目的调试。

## 数据库

数据库是大多数应用程序的核心。这是您存储和检索数据的方式。某些数据库库只是用来编写查询的包装，而另一些是完整的 ORM。

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 官方 Flight PDO 包装器，是核心的一部分。这是一个简单的包装器，有助于简化编写查询和执行查询的过程。它不是一个 ORM。
- [flightphp/active-record](/awesome-plugins/active-record) - 官方 Flight ActiveRecord ORM/Mapper。非常适合轻松在数据库中检索和存储数据的小型库。

## 会话

对于构建 Web 应用程序，API 并不是非常有用，但会话对于维护状态和登录信息至关重要。

- [Ghostff/Session](/awesome-plugins/session) - PHP 会话管理器（非阻塞、闪存、分段、会话加密）。使用 PHP open_ssl 可选加密/解密会话数据。

## 模板

模板是任何具有 UI 的 Web 应用程序的核心。有许多模板引擎可与 Flight 一起使用。

- [flightphp/core View](/learn#views) - 这是核心的一个非常基础的模板引擎。如果您的项目超过几个页面，则不建议使用它。
- [latte/latte](/awesome-plugins/latte) - Latte 是一个功能齐全的模板引擎，非常易于使用，比 Twig 或 Smarty 更接近 PHP 语法。而且非常容易扩展并添加自己的过滤器和函数。

## 贡献

有插件想要分享吗？提交拉取请求将其添加到列表中！