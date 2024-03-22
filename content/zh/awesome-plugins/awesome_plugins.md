# 优秀插件

Flight是非常可扩展的。有许多插件可用于为您的Flight应用程序添加功能。有些是由Flight团队官方支持的，而其他一些是微型/轻量级库，可帮助您入门。

## 缓存

缓存是加快应用程序速度的好方法。有许多缓存库可以与Flight一起使用。

- [Wruczek/PHP-File-Cache](/awesome-plugins/php-file-cache) - 轻量级、简单且独立的PHP文件缓存类

## Cookies

Cookie是在客户端存储小数据片段的好方法。它们可用于存储用户偏好、应用程序设置等。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie是一个提供简单有效管理Cookie的PHP库。

## 调试

在本地开发时，调试至关重要。有一些插件可以提升您的调试体验。

- [tracy/tracy](/awesome-plugins/tracy) - 这是一个功能齐全的错误处理程序，可与Flight一起使用。它有许多面板可帮助您调试应用程序。而且非常容易扩展和添加您自己的面板。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - 与[Tracy](/awesome-plugins/tracy)错误处理程序一起使用，此插件添加了一些额外面板，以帮助专门针对Flight项目进行调试。

## 数据库

数据库是大多数应用程序的核心。这是您存储和检索数据的方式。一些数据库库只是用来编写查询的包装器，而另一些是完整的ORM。

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 官方Flight PDO包装器，是核心的一部分。这是一个简单的包装器，可帮助简化编写查询和执行查询的过程。它不是ORM。
- [flightphp/active-record](/awesome-plugins/active-record) - 官方Flight ActiveRecord ORM/Mapper。一个很棒的小库，可轻松地检索和存储数据库中的数据。

## 加密

加密对于存储敏感数据的任何应用程序都至关重要。加密和解密数据并不是非常困难，但正确存储加密密钥可能会有难度。最重要的是，绝不要将加密密钥存储在公共目录中或将其提交到代码存储库中。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 这是一个可用于加密和解密数据的库。快速开始加密和解密数据相当简单。

## 会话

对于API来说，会话并不是很有用，但对于构建Web应用程序，会话对于保持状态和登录信息至关重要。

- [Ghostff/Session](/awesome-plugins/session) - PHP会话管理器（非阻塞、闪存、分段、会话加密）。使用PHP open_ssl进行可选的会话数据加密/解密。

## 模板

模板是任何具有UI的Web应用程序的核心。有许多模板引擎可与Flight一起使用。

- [flightphp/core View](/learn#views) - 这是核心的一个非常基本的模板引擎。如果您的项目有多个页面，则不建议使用。
- [latte/latte](/awesome-plugins/latte) - Latte是一个功能齐全的模板引擎，非常易于使用，比Twig或Smarty更接近PHP语法。而且非常容易扩展和添加您自己的过滤器和函数。

## 贡献

有要分享的插件吗？提交拉取请求将其添加到列表中！