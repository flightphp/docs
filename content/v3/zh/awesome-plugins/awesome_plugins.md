# 很棒的插件

Flight 具有极强的可扩展性。有许多插件可用于为您的 Flight 应用添加功能。其中一些由 Flight 团队官方支持，另一些则是帮助您入门的微/轻量级库。

## AI 工具

Flight 可以通过 AI 驱动的插件变得更加酷炫。

- [Flight MCP](/awesome-plugins/mcp) - 一个用于将 MCP（模型控制协议）与 Flight 集成的插件，实现无缝的 AI 驱动功能。主要用于文档页面，通过提供有关您的 Flight 项目的最新信息来帮助降低令牌成本。
- [stribus/mcp-flightphp-server-skeleton](https://github.com/stribus/mcp-flightphp-server-skeleton) - 一个带有 HTTP 和 stdio 的 FlightPHP MCP 服务器骨架，并支持工具、提示和资源的自动发现。

## API 文档

API 文档对任何 API 都至关重要。它帮助开发者了解如何与您的 API 交互以及会得到什么响应。有一些工具可以帮助您为 Flight 项目生成 API 文档。

- [FlightPHP OpenAPI Generator](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - 由 Daniel Schreiber 撰写的博客文章，介绍如何使用 OpenAPI 规范与 FlightPHP 以 API 优先的方法构建您的 API。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI 是帮助您为 Flight 项目生成 API 文档的优秀工具。它非常易于使用，并且可以根据您的需求进行自定义。这是一个 PHP 库，用于帮助您生成 Swagger 文档。

## 应用性能监控（APM）

应用性能监控（APM）对任何应用都至关重要。它帮助您了解应用的性能表现以及瓶颈所在。有许多 APM 工具可以与 Flight 一起使用。
- <span class="badge bg-primary">官方</span> [flightphp/apm](/awesome-plugins/apm) - Flight APM 是一个简单的 APM 库，可用于监控您的 Flight 应用。它可以用来监控您的应用性能并帮助您识别瓶颈。

## 异步

Flight 已经是一个快速的框架，但为它加上涡轮引擎会让一切变得更有趣（也更具挑战性）！

- [flightphp/async](/awesome-plugins/async) - Flight 官方异步库。这个库是一种为您应用添加异步处理的简单方式。它在底层使用 Swoole/Openswoole，提供简单有效的方法来异步运行任务。

## 授权/权限

对于需要控制谁能访问什么的应用来说，授权和权限至关重要。

- <span class="badge bg-primary">官方</span> [flightphp/permissions](/awesome-plugins/permissions) - Flight 官方权限库。这个库是向您的应用添加用户级和应用级权限的简单方法。

## 认证

认证对于需要验证用户身份和保护 API 端点的应用至关重要。

- [firebase/php-jwt](/awesome-plugins/jwt) - 用于 PHP 的 JSON Web Token（JWT）库。在您的 Flight 应用中实现基于令牌的认证的一种简单而安全的方式。非常适合无状态 API 认证、使用中间件保护路由以及实现 OAuth 风格的授权流程。

## 缓存

缓存是加速应用的好方法。有许多缓存库可以与 Flight 一起使用。

- <span class="badge bg-primary">官方</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 轻量、简单且独立的 PHP 文件内缓存类

## CLI

CLI 应用是与您的应用交互的好方法。您可以使用它们来生成控制器、显示所有路由等等。

- <span class="badge bg-primary">官方</span> [flightphp/runway](/awesome-plugins/runway) - Runway 是一个 CLI 应用，可帮助您管理您的 Flight 应用。

## Cookies

Cookie 是在客户端存储少量数据的好方法。它们可用于存储用户偏好、应用设置等等。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie 是一个 PHP 库，提供了一种简单有效的方式来管理 Cookie。

## 调试

在本地环境开发时，调试至关重要。有几个插件可以提升您的调试体验。

- [tracy/tracy](/awesome-plugins/tracy) - 这是一个功能齐全的错误处理器，可与 Flight 一起使用。它有许多面板可以帮助您调试应用。它还非常易于扩展和添加自己的面板。
- <span class="badge bg-primary">官方</span> [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - 与 [Tracy](/awesome-plugins/tracy) 错误处理器配合使用，这个插件添加了一些额外的面板，专门帮助调试 Flight 项目。

## 数据库

数据库是大多数应用的核心。这是您存储和检索数据的方式。一些数据库库只是用于编写查询的封装，而另一些则是成熟的 ORM。

- <span class="badge bg-primary">官方</span> [flightphp/core SimplePdo](/learn/simple-pdo) - 属于核心部分的 Flight 官方 PDO 助手。这是一个现代封装，提供了如 `insert()`、`update()`、`delete()` 和 `transaction()` 等便捷的辅助方法，以简化数据库操作。所有结果都以 Collections（集合）形式返回，便于灵活地以数组/对象方式访问。它不是 ORM，而是一种更好的使用 PDO 的方式。
- <span class="badge bg-warning">已弃用</span> [flightphp/core PdoWrapper](/learn/pdo-wrapper) - 属于核心部分的 Flight 官方 PDO 封装（自 v3.18.0 起已弃用）。请改用 SimplePdo。
- <span class="badge bg-primary">官方</span> [flightphp/active-record](/awesome-plugins/active-record) - Flight 官方 ActiveRecord ORM/映射器。一个很棒的小型库，用于轻松地在数据库中检索和存储数据。
- [byjg/php-migration](/awesome-plugins/migrations) - 用于跟踪项目所有数据库更改的插件。
- [knifelemon/easy-query](/awesome-plugins/easy-query) - 轻量级、流畅的 SQL 查询构建器，可生成 SQL 和预处理语句的参数。与 [SimplePdo](/learn/simple-pdo) 配合效果很好。

## 加密

加密对于任何存储敏感数据的应用都至关重要。加密和解密数据并不十分困难，但正确存储加密密钥[可能](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [很](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [困难](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key)。最重要的是绝不要把加密密钥存储在公共目录中，也不要将其提交到代码仓库。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 这是一个可用于加密和解密数据的库。上手非常简单，可以很快开始加密和解密数据。

## 电子邮件

发送电子邮件是大多数 Web 应用的核心需求——欢迎消息、密码重置、通知。这些库让这一切变得轻松，同时保持良好的送达率。

- [ryanstubbs/flightmail](/awesome-plugins/flightmail) - FlightMail 使用流畅的、对 Flight 友好的 API 封装了 Symfony Mailer。通过简单的 DSN 字符串使用 SMTP 或任何主流提供商发送邮件，按消息路由不同的提供商，并使用 Twig 或 Latte 模板渲染正文。这是 Flight 的非官方插件，不由 Flight 团队维护。

## 任务队列

任务队列对于异步处理任务非常有用。这可以是发送电子邮件、处理图像，或任何不需要实时完成的事情。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - Simple Job Queue 是一个可用于异步处理任务的库。它可以与 beanstalkd、MySQL/MariaDB、SQLite 和 PostgreSQL 一起使用。

## 会话

会话对 API 来说并不那么有用，但在构建 Web 应用时，会话对于维护状态和登录信息可能至关重要。

- <span class="badge bg-primary">官方</span> [flightphp/session](/awesome-plugins/session) - Flight 官方会话库。这是一个简单的会话库，可用于存储和检索会话数据。它使用 PHP 内置的会话处理。
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP 会话管理器（非阻塞、flash、分段、会话加密）。使用 PHP open_ssl 对会话数据进行可选加密/解密。

## 模板

模板对于任何有 UI 的 Web 应用都是核心。有许多模板引擎可以与 Flight 一起使用。

- <span class="badge bg-warning">已弃用</span> [flightphp/core View](/learn#views) - 这是一个非常基础的模板引擎，属于核心的一部分。如果您的项目超过几个页面，不建议使用它。
- [latte/latte](/awesome-plugins/latte) - Latte 是一个功能齐全的模板引擎，非常易于使用，并且比 Twig 或 Smarty 更接近 PHP 语法。它还非常易于扩展并添加自己的过滤器和函数。
- [twig/twig](/awesome-plugins/twig) - Twig 是一个灵活、快速且安全的模板引擎（与 Symfony 使用的是同一个）。AI 工具和许多 PHP 开发人员都非常熟悉它，它默认会自动转义输出，并且拥有庞大的扩展生态系统。
- [knifelemon/comment-template](/awesome-plugins/comment-template) - CommentTemplate 是一个功能强大的 PHP 模板引擎，支持资源编译、模板继承和变量处理。具有自动 CSS/JS 压缩、缓存、Base64 编码以及可选的 Flight PHP 框架集成等特性。

## WordPress 集成

想在 WordPress 项目中使用 Flight 吗？有一个方便的插件可以做到这一点！

- [n0nag0n/wordpress-integration-for-flight-framework](/awesome-plugins/n0nag0n_wordpress) - 这个 WordPress 插件让您可以在 WordPress 旁边直接运行 Flight。它非常适合使用 Flight 框架为您的 WordPress 站点添加自定义 API、微服务，甚至完整的应用。如果您想两全其美，这非常有用！

## 参与贡献

有想要分享的插件吗？提交一个拉取请求把它添加到列表中！