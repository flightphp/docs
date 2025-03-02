# 超级插件

Flight 是一个极其可扩展的框架。有许多插件可以用来为您的 Flight 应用程序添加功能。一些插件得到了 Flight 团队的正式支持，而其他插件则是小型/轻量级库，帮助您快速入门。

## API 文档

API 文档对任何 API 都至关重要。它帮助开发者了解如何与您的 API 进行交互以及期望的返回效果。有一些工具可以帮助您为您的 Flight 项目生成 API 文档。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Daniel Schreiber 撰写的博文，介绍如何使用 OpenAPI 规范与 FlightPHP 一起构建 API，采用 API 优先的方法。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI 是一个很棒的工具，可以帮助您为您的 Flight 项目生成 API 文档。它非常易于使用，并且可以根据您的需要进行定制。这是一个帮助您生成 Swagger 文档的 PHP 库。

## 身份验证/授权

身份验证和授权对任何需要控制访问权限的应用程序至关重要。

- [flightphp/permissions](/awesome-plugins/permissions) - 官方 Flight 权限库。这个库是为您的应用程序添加用户和应用层级权限的简单方式。

## 缓存

缓存是加速应用程序的好方法。有许多缓存库可以与 Flight 一起使用。

- [flightphp/cache](/awesome-plugins/php-file-cache) - 轻量、简单且独立的 PHP 文件内缓存类

## CLI

CLI 应用程序是与您的应用程序交互的好方法。您可以使用它们来生成控制器、显示所有路由等。

- [flightphp/runway](/awesome-plugins/runway) - Runway 是一个 CLI 应用程序，帮助您管理您的 Flight 应用程序。

## Cookies

Cookies 是在客户端存储小数据块的好方法。它们可以用来存储用户偏好、应用设置等。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie 是一个提供简单有效方式来管理 cookies 的 PHP 库。

## 调试

在本地开发环境中，调试至关重要。有一些插件可以提升您的调试体验。

- [tracy/tracy](/awesome-plugins/tracy) - 这是一个功能齐全的错误处理程序，可以与 Flight 一起使用。它有许多面板可以帮助您调试您的应用程序。它也非常容易扩展并添加自己的面板。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - 与 [Tracy](/awesome-plugins/tracy) 错误处理程序一起使用，此插件添加了一些额外的面板以特别帮助 Flight 项目的调试。

## 数据库

数据库是大多数应用程序的核心。这是如何存储和检索数据的。一些数据库库只是写查询的封装，而一些则是完整的 ORM。

- [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 官方 Flight PDO Wrapper，属于核心的一部分。这是一个简单的封装，帮助简化编写和执行查询的过程。它不是 ORM。
- [flightphp/active-record](/awesome-plugins/active-record) - 官方 Flight ActiveRecord ORM/Mapper。一个很棒的小库，方便在数据库中检索和存储数据。
- [byjg/php-migration](/awesome-plugins/migrations) - 插件用于跟踪您的项目中的所有数据库更改。

## 加密

加密对存储敏感数据的任何应用程序都至关重要。加密和解密数据并不特别困难，但正确存储加密密钥 [可能](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [比较](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [困难](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key)。最重要的是永远不要将您的加密密钥存储在公共目录中或提交到代码库中。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 这是一个可以用来加密和解密数据的库。开始加密和解密数据相对简单。

## 任务队列

任务队列对于异步处理任务非常有帮助。这可以是发送电子邮件、处理图像或任何不需要实时完成的任务。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue 是一个可以用来异步处理工作的库。它可以与 beanstalkd、MySQL/MariaDB、SQLite 和 PostgreSQL 一起使用。

## 会话

会话对 API 来说并不是特别有用，但对于构建 Web 应用程序来说，会话对于维护状态和登录信息至关重要。

- [Ghostff/Session](/awesome-plugins/session) - PHP 会话管理器（非阻塞、闪存、段、会话加密）。使用 PHP open_ssl 用于可选的会话数据加密/解密。

## 模板

模板是任何具有用户界面的 Web 应用程序的核心。有许多模板引擎可以与 Flight 一起使用。

- [flightphp/core View](/learn#views) - 这是一个很基础的模板引擎，是核心的一部分。如果您的项目有超过几页，不推荐使用它。
- [latte/latte](/awesome-plugins/latte) - Latte 是一个功能齐全的模板引擎，使用起来非常简单，语法更接近 PHP，而不是 Twig 或 Smarty。它也非常容易扩展并添加自己的过滤器和函数。

## 贡献

有插件想分享吗？提交一个拉取请求将其添加到列表中！