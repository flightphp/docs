# 超级插件

Flight 非常可扩展。 有许多插件可以用于为您的 Flight 应用程序添加功能。 一些得到 Flight 团队的官方支持，而另一些则是微型/轻量库，以帮助您入门。

## API 文档

API 文档对任何 API 都至关重要。它帮助开发人员理解如何与您的 API 交互以及可以期待什么回报。 有一些工具可帮助您为您的 Flight 项目生成 API 文档。

- [FlightPHP OpenAPI 生成器](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - 由 Daniel Schreiber 编写的博客文章，介绍了如何使用 OpenAPI 规范与 FlightPHP 一起构建 API，采用 API 优先的方法。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI 是一个出色的工具，可以帮助您为 Flight 项目生成 API 文档。 它非常易于使用，并且可以根据您的需要进行自定义。 这是用于生成 Swagger 文档的 PHP 库。

## 应用性能监控 (APM)

应用性能监控 (APM) 对任何应用程序都至关重要。它帮助您了解您的应用程序的性能以及瓶颈所在。 有许多 APM 工具可以与 Flight 一起使用。
- <span class="badge bg-info">beta</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM 是一个简单的 APM 库，可用于监控您的 Flight 应用程序。 它可以用于监控您的应用程序的性能并帮助您识别瓶颈。

## 身份验证/授权

身份验证和授权对任何需要控制谁可以访问什么的应用程序至关重要。

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - 官方 Flight 权限库。 这个库是向您的应用程序添加用户和应用程序级权限的一种简单方式。

## 缓存

缓存是加速应用程序的好方法。 有许多缓存库可以与 Flight 一起使用。

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 轻量、简单和独立的 PHP 内部文件缓存类

## CLI

CLI 应用程序是与应用程序交互的好方法。 您可以使用它们生成控制器、显示所有路由等。

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway 是一个 CLI 应用程序，可以帮助您管理您的 Flight 应用程序。

## Cookies

Cookies 是在客户端存储小块数据的好方法。 它们可以用于存储用户首选项、应用程序设置等。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie 是一个 PHP 库，提供了一种简单有效的方式来管理 cookies。

## 调试

调试在您开发本地环境时至关重要。 有一些插件可以提升您的调试体验。

- [tracy/tracy](/awesome-plugins/tracy) - 这是一个功能齐全的错误处理程序，可与 Flight 一起使用。 它有许多面板，可以帮助您调试应用程序。 扩展和添加您自己的面板也非常简单。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - 与 [Tracy](/awesome-plugins/tracy) 错误处理程序一起使用，此插件添加了一些额外面板以帮助专门为 Flight 项目调试。

## 数据库

数据库是大多数应用程序的核心。这是您存储和检索数据的方式。部分数据库库仅是编写查询的包装器，而部分则是完整的 ORM。

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 官方 Flight PDO 包装器，属于核心部分。这是一个简单的包装器，帮助简化编写查询和执行它们的过程。它不是 ORM。
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - 官方 Flight ActiveRecord ORM/Mapper。这是一个很好的小型库，可以轻松地在数据库中检索和存储数据。
- [byjg/php-migration](/awesome-plugins/migrations) - 用于跟踪项目所有数据库更改的插件。

## 加密

加密对存储敏感数据的任何应用程序至关重要。加密和解密数据并不困难，但正确存储加密密钥 [可以](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [是](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [困难的](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key)。最重要的是永远不要将加密密钥存储在公共目录中或将其提交到代码库中。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 这是一个可以用于加密和解密数据的库。获取并运行相对简单，可以开始加密和解密数据。

## 作业队列

作业队列对于异步处理任务非常有用。 这可以是发送电子邮件、处理图像或任何不需要实时完成的操作。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue 是一个可以用于异步处理作业的库。它可以与 beanstalkd、MySQL/MariaDB、SQLite 和 PostgreSQL 一起使用。

## 会话

会话对于 API 并不是非常有用，但在构建 Web 应用程序时，会话对于维护状态和登录信息可能至关重要。

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - 官方 Flight 会话库。这是一个简单的会话库，可以用于存储和检索会话数据。 它使用 PHP 内置的会话处理。
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP 会话管理器（非阻塞、闪存、分段、会话加密）。使用 PHP open_ssl 可选地对会话数据进行加密/解密。

## 模板

模板是任何具有用户界面的 Web 应用程序的核心。有许多模板引擎可以与 Flight 一起使用。

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - 这是一个非常基本的模板引擎，是核心的一部分。如果您的项目中有多个页面，建议不要使用该引擎。
- [latte/latte](/awesome-plugins/latte) - Latte 是一个功能齐全的模板引擎，使用非常简单，语法比 Twig 或 Smarty 更贴近 PHP。 扩展和添加您自己的过滤器和函数也非常简单。

## 贡献

您是否有想要分享的插件？ 提交拉取请求以将其添加到列表中！