# 很棒的插件

Flight 具有令人难以置信的可扩展性。可以使用许多插件来为您的 Flight 应用程序添加功能。有些插件由 Flight 团队正式支持，其他是一些微型/轻量级库，帮助您入门。

## API 文档

API 文档对任何 API 都至关重要。它有助于开发人员了解如何与您的 API 交互，以及预期返回什么。有一些工具可帮助您为 Flight 项目生成 API 文档。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - 由 Daniel Schreiber 撰写的博客文章，介绍如何使用 OpenAPI 规范与 FlightPHP 构建 API，采用 API 先方法。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI 是一个伟大的工具，可帮助您为 Flight 项目生成 API 文档。它非常易用，并且可以自定义以满足您的需求。这是用于生成 Swagger 文档的 PHP 库。

## 应用程序性能监控 (APM)

应用程序性能监控 (APM) 对任何应用程序都至关重要。它有助于您了解应用程序的性能以及瓶颈所在。有许多 APM 工具可与 Flight 一起使用。
- <span class="badge bg-info">测试版</span>[flightphp/apm](/awesome-plugins/apm) - Flight APM 是一个简单的 APM 库，可用于监控您的 Flight 应用程序。它可用于监控应用程序的性能并帮助您识别瓶颈。

## 身份验证/授权

身份验证和授权对任何需要控制谁可以访问什么的应用程序都至关重要。

- <span class="badge bg-primary">官方</span> [flightphp/permissions](/awesome-plugins/permissions) - 官方 Flight Permissions 库。这是一个简单的方法，用于为您的应用程序添加用户和应用程序级权限。

## 缓存

缓存是加速应用程序的绝佳方式。有许多缓存库可与 Flight 一起使用。

- <span class="badge bg-primary">官方</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 轻量、简单且独立的 PHP 文件内缓存类

## CLI

CLI 应用程序是与您的应用程序交互的绝佳方式。您可以使用它们来生成控制器、显示所有路由等。

- <span class="badge bg-primary">官方</span> [flightphp/runway](/awesome-plugins/runway) - Runway 是一个 CLI 应用程序，可帮助您管理 Flight 应用程序。

## Cookies

Cookies 是存储客户端小数据片段的绝佳方式。它们可用于存储用户偏好、应用程序设置等。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie 是一个 PHP 库，提供简单且有效的方式来管理 cookies。

## 调试

调试在本地开发环境中至关重要。有一些插件可以提升您的调试体验。

- [tracy/tracy](/awesome-plugins/tracy) - 这是一个功能齐全的错误处理程序，可与 Flight 一起使用。它具有许多面板，可帮助您调试应用程序。它也很容易扩展并添加自己的面板。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - 与 [Tracy](/awesome-plugins/tracy) 错误处理程序一起使用，此插件为 Flight 项目添加了一些额外面板，以帮助调试。

## 数据库

数据库是大多数应用程序的核心。这是存储和检索数据的途径。有些数据库库只是用于编写查询的包装器，有些是功能齐全的 ORM。

- <span class="badge bg-primary">官方</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 官方 Flight PDO Wrapper，这是核心的一部分。这是一个简单的包装器，用于简化编写查询和执行查询的过程。它不是 ORM。
- <span class="badge bg-primary">官方</span> [flightphp/active-record](/awesome-plugins/active-record) - 官方 Flight ActiveRecord ORM/映射器。一个伟大的小库，用于轻松检索和存储数据库中的数据。
- [byjg/php-migration](/awesome-plugins/migrations) - 用于跟踪项目中所有数据库更改的插件。

## 加密

加密对任何存储敏感数据的应用程序都至关重要。加密和解密数据并不太难，但正确存储加密密钥 [可以](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [be](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [difficult](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key)。最重要的是，绝不要将您的加密密钥存储在公共目录中或提交到代码仓库。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 这是一个库，可用于加密和解密数据。入门并开始加密和解密数据相当简单。

## 作业队列

作业队列非常有助于异步处理任务。这可以是发送电子邮件、处理图像或任何不需要实时完成的事情。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue 是一个库，可用于异步处理作业。它可与 beanstalkd、MySQL/MariaDB、SQLite 和 PostgreSQL 一起使用。

## 会话

会话对 API 来说不是很有用，但对于构建 Web 应用程序，会话对于维护状态和登录信息至关重要。

- <span class="badge bg-primary">官方</span> [flightphp/session](/awesome-plugins/session) - 官方 Flight Session 库。这是一个简单的会话库，用于存储和检索会话数据。它使用 PHP 的内置会话处理。
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP 会话管理器（非阻塞、闪存、段落、会话加密）。使用 PHP open_ssl 进行可选的会话数据加密/解密。

## 模板引擎

模板引擎是任何具有 UI 的 Web 应用程序的核心。有许多模板引擎可与 Flight 一起使用。

- <span class="badge bg-warning">已弃用</span> [flightphp/core View](/learn#views) - 这是核心部分的一个非常基本的模板引擎。如果您的项目有超过几个页面，则不推荐使用。
- [latte/latte](/awesome-plugins/latte) - Latte 是一个功能齐全的模板引擎，非常易用，并且感觉更接近 PHP 语法，而不是 Twig 或 Smarty。它也很容易扩展并添加自己的过滤器和函数。

## WordPress 集成

想在您的 WordPress 项目中使用 Flight？有一个方便的插件！

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - 这个 WordPress 插件让您可以在 WordPress 旁边运行 Flight。它非常适合为您的 WordPress 站点添加自定义 API、微服务，甚至是完整的应用程序，使用 Flight 框架。如果您想要两种世界的优点，这超级有用！

## 贡献

有插件想分享？提交拉取请求将其添加到列表中！