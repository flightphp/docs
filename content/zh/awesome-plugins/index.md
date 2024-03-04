# 令人惊叹的插件

Flight非常可扩展。有许多插件可以用于向您的Flight应用程序添加功能。其中一些得到了Flight团队的官方支持，而其他一些是微型/轻量级库，可帮助您入门。

## 缓存

缓存是加速应用程序的绝佳方法。有许多缓存库可与Flight一起使用。

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 轻巧、简单且独立的PHP文件缓存类

## 调试

在本地环境中进行开发时，调试至关重要。有一些插件可以提升您的调试体验。

- [tracy/tracy](/awesome-plugins/tracy) - 这是一个功能齐全的错误处理程序，可以与Flight一起使用。它具有多个面板，可以帮助您调试应用程序。扩展和添加自定义面板也非常简单。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - 与[Tracy](/awesome-plugins/tracy)错误处理程序一起使用，此插件添加了一些额外的面板，专门用于Flight项目的调试。

## 数据库

数据库是大多数应用程序的核心。这是您存储和检索数据的方式。有些数据库库只是用来编写查询的包装器，而有些是完整的ORM。

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - Flight官方PDO包装器，是核心的一部分。这是一个简单的包装器，帮助简化编写查询和执行查询的过程。它不是ORM。
- [flightphp/active-record](/awesome-plugins/active-record) - Flight官方ActiveRecord ORM/Mapper。非常适合轻松检索和存储数据库中的数据的小型库。

## 会话

对于API来说，会话实际上并不那么有用，但对于构建Web应用程序来说，会话可以对保持状态和登录信息至关重要。

- [Ghostff/Session](/awesome-plugins/session) - PHP会话管理器（非阻塞，闪存，分段，会话加密）。使用PHP open_ssl进行可选的会话数据加密/解密。

## 模板

模板是任何具有UI的Web应用程序的核心。有许多模板引擎可与Flight一起使用。

- [flightphp/core View](/learn#views) - 这是Flight核心的一个非常基本的模板引擎。如果项目中有多个页面，则不建议使用它。
- [latte/latte](/awesome-plugins/latte) - Latte是一个功能齐全的模板引擎，非常易于使用，更接近于PHP语法而不是Twig或Smarty。扩展和添加自定义过滤器和功能也非常简单。

## 贡献

有插件想要分享吗？提交拉取请求将其添加到列表中！