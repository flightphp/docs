# 令人敬畏的插件

Flight 非常具有可扩展性。有许多插件可用于为您的 Flight 应用程序增加功能。一些是由 FlightPHP 团队官方支持的，另一些是微型/轻量级库，可帮助您入门。

## 缓存

缓存是加快应用程序速度的绝佳方式。有许多缓存库可与 Flight 一起使用。

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 轻量级、简单且独立的 PHP 文件缓存类

## 调试

在本地环境开发时，调试至关重要。有一些插件可以提升您的调试体验。

- [tracy/tracy](/awesome-plugins/tracy) - 这是一个功能齐全的错误处理程序，可与 Flight 一起使用。它拥有许多面板可帮助您调试应用程序。还可以很容易地扩展并添加自己的面板。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - 与 [Tracy](/awesome-plugins/tracy) 错误处理程序一起使用，此插件添加了一些额外的面板，专门用于 Flight 项目的调试。

## 数据库

数据库是大多数应用程序的核心。这是您存储和检索数据的方式。一些数据库库只是用于编写查询的包装器，而一些是完整的 ORM。

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 官方 Flight PDO 包装器，是核心的一部分。这是一个简单的包装器，帮助简化编写查询和执行查询的过程。它不是一个 ORM。
- [flightphp/active-record](/awesome-plugins/active-record) - 官方 Flight ActiveRecord ORM/Mapper。非常适合轻松检索和存储数据库中的数据的小型库。

## 会话

对于构建 Web 应用程序而言，会话对于维护状态和登录信息至关重要，但对于 API 并不太有用。

- [Ghostff/Session](/awesome-plugins/session) - PHP 会话管理器（非阻塞、闪存、段、会话加密）。使用 PHP open_ssl 可进行可选的会话数据加密/解密。

## 模板

模板对于具有用户界面的任何 Web 应用程序至关重要。有许多模板引擎可与 Flight 一起使用。

- [flightphp/core View](/learn#views) - 这是核心的一个非常基本的模板引擎。如果项目中有多个页面，不建议使用它。
- [latte/latte](/awesome-plugins/latte) - Latte 是一个功能丰富的模板引擎，非常易于使用，比 Twig 或 Smarty 更接近 PHP 语法。还可以很容易地扩展并添加自己的过滤器和函数。

## 贡献

有插件想要分享吗？提交拉取请求将其添加到列表中！