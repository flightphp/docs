# 令人惊叹的插件

Flight 的扩展性极强。有很多插件可以用来为您的 Flight 应用程序添加功能。部分插件由 Flight 团队官方支持，其他则是小型/轻量级库，以帮助您入门。

## API 文档

API 文档对任何 API 都至关重要。它帮助开发者理解如何与您的 API 进行交互以及可以期待什么。您可以使用一些工具为您的 Flight 项目生成 API 文档。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - 由 Daniel Schreiber 撰写的博客文章，介绍如何使用 OpenAPI Generator 与 FlightPHP 生成 API 文档。
- [Swagger UI](https://github.com/zircote/swagger-php) - Swagger UI 是一个很好的工具，可以帮助您为 Flight 项目生成 API 文档。它非常容易使用，并且可以根据您的需求进行自定义。这是帮助您生成 Swagger 文档的 PHP 库。

## 身份验证/授权

身份验证和授权对任何需要控制访问的应用程序至关重要。

- [flightphp/permissions](/awesome-plugins/permissions) - 官方 Flight 权限库。这个库是为您的应用程序添加用户和应用级权限的一种简单方法。

## 缓存

缓存是加速您应用程序的好方法。有许多缓存库可以与 Flight 一起使用。

- [flightphp/cache](/awesome-plugins/php-file-cache) - 轻量、简单、独立的 PHP 文件内缓存类

## CLI

CLI 应用程序是与您的应用程序交互的好方法。您可以用它们来生成控制器，显示所有路由等等。

- [flightphp/runway](/awesome-plugins/runway) - Runway 是一个 CLI 应用程序，帮助您管理您的 Flight 应用程序。

## Cookies

Cookies 是在客户端存储小块数据的好方法。它们可以用于存储用户偏好、应用程序设置等。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie 是一个提供简单有效方式来管理 Cookies 的 PHP 库。

## 调试

调试在您本地环境中开发时至关重要。有一些插件可以提升您的调试体验。

- [tracy/tracy](/awesome-plugins/tracy) - 这是一个功能齐全的错误处理程序，可以与 Flight 一起使用。它有许多面板可以帮助您调试应用程序。它也很容易扩展和添加您自己的面板。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - 与 [Tracy](/awesome-plugins/tracy) 错误处理程序一起使用的这个插件增加了一些额外的面板，以帮助 Flight 项目的调试。

## 数据库

数据库是大多数应用程序的核心。这是您存储和检索数据的方式。有些数据库库仅仅是用来编写查询的包装器，而有些则是完整的 ORM。

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 官方 Flight PDO Wrapper，属于核心部分。这是一个简单的包装器，帮助简化编写查询和执行查询的过程。它不是一个 ORM。
- [flightphp/active-record](/awesome-plugins/active-record) - 官方 Flight ActiveRecord ORM/映射器。非常方便的小库，用于轻松检索和存储数据库中的数据。
- [byjg/php-migration](/awesome-plugins/migrations) - 用于跟踪您项目的所有数据库更改的插件。

## 加密

加密对于存储敏感数据的任何应用程序至关重要。加密和解密数据并不是特别困难，但正确存储加密密钥 [可能](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [是](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [困难的](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key)。最重要的是，绝不要将加密密钥存储在公共目录中或将其提交到您的代码仓库。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 这是一个可以用于加密和解密数据的库。启动并运行相对简单，可以开始加密和解密数据。

## 会话

会话对于 API 并不特别有用，但对于构建 Web 应用程序而言，会话对于维持状态和登录信息至关重要。

- [Ghostff/Session](/awesome-plugins/session) - PHP 会话管理器（非阻塞、闪存、分段、会话加密），使用 PHP open_ssl 可选地对会话数据进行加密/解密。

## 模板

模板是任何具有 UI 的 Web 应用程序的核心。有许多模板引擎可以与 Flight 一起使用。

- [flightphp/core View](/learn#views) - 这是一个非常基本的模板引擎，属于核心部分。如果您的项目中有超过几页，建议不要使用。
- [latte/latte](/awesome-plugins/latte) - Latte 是一个功能齐全的模板引擎，使用非常简单，并且感觉比 Twig 或 Smarty 更接近 PHP 语法。它也非常容易扩展和添加您自己的过滤器和函数。

## 贡献

有您想分享的插件吗？提交一个拉取请求，将其添加到列表中！