# 精选插件

Flight 极具可扩展性。有许多插件可用于为您的 Flight 应用程序添加功能。其中一些由 Flight 团队官方支持，其他则是一些微型/轻量级库，帮助您快速上手。

## API 文档

API 文档对于任何 API 都至关重要。它帮助开发者理解如何与您的 API 交互以及预期返回什么。有几个工具可用于帮助您为 Flight 项目生成 API 文档。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - 由 Daniel Schreiber 撰写的博客文章，介绍如何使用 OpenAPI 规范与 FlightPHP 结合，采用 API 优先方法构建您的 API。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI 是一个优秀的工具，可帮助您为 Flight 项目生成 API 文档。它非常易用，并可自定义以满足您的需求。这是用于生成 Swagger 文档的 PHP 库。

## 应用程序性能监控 (APM)

应用程序性能监控 (APM) 对于任何应用程序都至关重要。它帮助您了解应用程序的性能表现以及瓶颈所在。有许多 APM 工具可与 Flight 一起使用。
- <span class="badge bg-primary">official</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM 是一个简单的 APM 库，可用于监控您的 Flight 应用程序。它可用于监控应用程序性能并帮助您识别瓶颈。

## 异步

Flight 已经是一个快速的框架，但为其添加涡轮引擎会让一切变得更有趣（且更具挑战性）！

- [flightphp/async](/awesome-plugins/async) - 官方 Flight Async 库。此库是一种简单的方式，为您的应用程序添加异步处理。它在底层使用 Swoole/Openswoole，提供简单有效的任务异步运行方式。

## 授权/权限

授权和权限对于需要控制谁可以访问什么的任何应用程序都至关重要。

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - 官方 Flight Permissions 库。此库是一种简单的方式，为您的应用程序添加用户和应用程序级别的权限。

## 认证

认证对于需要验证用户身份并保护 API 端点的应用程序至关重要。

- [firebase/php-jwt](/awesome-plugins/jwt) - PHP 的 JSON Web Token (JWT) 库。一种简单安全的实现基于令牌认证的方式，适用于您的 Flight 应用程序。非常适合无状态 API 认证、使用中间件保护路由以及实现 OAuth 风格的授权流程。

## 缓存

缓存是加速应用程序的绝佳方式。有许多缓存库可与 Flight 一起使用。

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 轻量、简单且独立的 PHP 文件内缓存类

## CLI

CLI 应用程序是与您的应用程序交互的绝佳方式。您可以使用它们生成控制器、显示所有路由等。

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway 是一个 CLI 应用程序，帮助您管理 Flight 应用程序。

## Cookie

Cookie 是存储客户端小量数据的绝佳方式。它们可用于存储用户偏好、应用程序设置等。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie 是一个 PHP 库，提供简单有效的 cookie 管理方式。

## 调试

在本地环境中开发时，调试至关重要。有几个插件可以提升您的调试体验。

- [tracy/tracy](/awesome-plugins/tracy) - 这是一个功能齐全的错误处理器，可与 Flight 一起使用。它有多个面板可帮助您调试应用程序。它也非常易于扩展并添加您自己的面板。
- <span class="badge bg-primary">official</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - 与 [Tracy](/awesome-plugins/tracy) 错误处理器一起使用，此插件添加了一些额外的面板，专门帮助调试 Flight 项目。

## 数据库

数据库是大多数应用程序的核心。这是存储和检索数据的方式。有些数据库库只是用于编写查询的包装器，有些则是功能齐全的 ORM。

- <span class="badge bg-primary">official</span> [flightphp/core SimplePdo](/learn/simple-pdo) - 官方 Flight PDO 助手，是核心的一部分。这是一个现代包装器，带有便捷的辅助方法，如 `insert()`、`update()`、`delete()` 和 `transaction()`，以简化数据库操作。所有结果均返回为集合，支持灵活的数组/对象访问。不是 ORM，而是一种更好地使用 PDO 的方式。
- <span class="badge bg-warning">deprecated</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - 官方 Flight PDO 包装器，是核心的一部分（自 v3.18.0 起已弃用）。请改用 SimplePdo。
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - 官方 Flight ActiveRecord ORM/映射器。一个优秀的库，便于轻松检索和存储数据库中的数据。
- [byjg/php-migration](/awesome-plugins/migrations) - 用于跟踪项目所有数据库变更的插件。

## 加密

加密对于存储敏感数据的任何应用程序都至关重要。加密和解密数据并不难，但正确存储加密密钥 [可能](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [会](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [困难](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key)。最重要的是，切勿将加密密钥存储在公共目录中或提交到您的代码仓库。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 这是一个可用于加密和解密数据的库。启动并运行非常简单，即可开始加密和解密数据。

## 作业队列

作业队列对于异步处理任务非常有用。这可以是发送电子邮件、处理图像或任何不需要实时完成的任务。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue 是一个可用于异步处理作业的库。它可与 beanstalkd、MySQL/MariaDB、SQLite 和 PostgreSQL 一起使用。

## 会话

会话对于 API 并不太有用，但对于构建 Web 应用程序，会话对于维护状态和登录信息至关重要。

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - 官方 Flight Session 库。这是一个简单的会话库，可用于存储和检索会话数据。它使用 PHP 内置的会话处理。
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP 会话管理器（非阻塞、闪现、段、会话加密）。使用 PHP open_ssl 进行可选的会话数据加密/解密。

## 模板

模板是任何带 UI 的 Web 应用程序的核心。有许多模板引擎可与 Flight 一起使用。

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - 这是一个非常基本的模板引擎，是核心的一部分。如果您的项目超过几个页面，不推荐使用。
- [latte/latte](/awesome-plugins/latte) - Latte 是一个功能齐全的模板引擎，非常易用，其语法更接近 PHP 而非 Twig 或 Smarty。它也非常易于扩展并添加您自己的过滤器和函数。
- [knifelemon/comment-template](/awesome-plugins/comment-template) - CommentTemplate 是一个强大的 PHP 模板引擎，支持资产生成、模板继承和变量处理。特性包括自动 CSS/JS 压缩、缓存、Base64 编码，以及可选的 Flight PHP 框架集成。

## WordPress 集成

想在您的 WordPress 项目中使用 Flight？有一个方便的插件！

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - 此 WordPress 插件让您可以与 WordPress 并行运行 Flight。它非常适合使用 Flight 框架为您的 WordPress 站点添加自定义 API、微服务，甚至完整应用程序。如果您想兼得两者的优点，这超级有用！

## 贡献

有一个您想分享的插件？提交拉取请求将其添加到列表中！