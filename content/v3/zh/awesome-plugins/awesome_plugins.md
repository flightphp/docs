# 精彩的插件

Flight 拥有强大的扩展性。有许多插件可以用来为你的 Flight 应用程序添加功能。其中一些是 Flight 团队正式支持的，其他的是轻量级库，可以帮助你入门。

## API 文档

API 文档对于任何 API 都至关重要。它帮助开发者理解如何与你的 API 交互以及期望返回什么。有几种工具可供你为你的 Flight 项目生成 API 文档。

- [FlightPHP OpenAPI 生成器](https://dev.to/danielsc/define-generate-and-implement-an-api-first-approach-with-openapi-generator-and-flightphp-1fb3) - Daniel Schreiber 撰写的博客文章，介绍如何将 OpenAPI 规范与 FlightPHP 一起使用，以 API 优先的方法构建你的 API。
- [SwaggerUI](https://github.com/zircote/swagger-php) - Swagger UI 是一种很好的工具，可以帮助你为你的 Flight 项目生成 API 文档。它非常易于使用，并且可以根据你的需求进行自定义。这是一个帮助你生成 Swagger 文档的 PHP 库。

## 身份验证/授权

身份验证和授权对于任何需要控制访问的应用程序至关重要。

- <span class="badge bg-primary">official</span> [flightphp/permissions](/awesome-plugins/permissions) - 官方 Flight 权限库。这个库提供了一种简单的方式来为你的应用程序添加用户和应用级别的权限。

## 缓存

缓存是加速你的应用程序的一个很好的方式。有许多缓存库可以与 Flight 一起使用。

- <span class="badge bg-primary">official</span> [flightphp/cache](/awesome-plugins/php-file-cache) - 轻量、简单且独立的 PHP 文件内缓存类

## CLI

CLI 应用程序是与您的应用程序交互的一种很好的方式。你可以用它们来生成控制器、显示所有路由等。

- <span class="badge bg-primary">official</span> [flightphp/runway](/awesome-plugins/runway) - Runway 是一个 CLI 应用程序，帮助你管理你的 Flight 应用程序。

## Cookies

Cookies 是在客户端存储小数据块的很好方式。它们可以用来存储用户偏好、应用程序设置等。

- [overclokk/cookie](/awesome-plugins/php-cookie) - PHP Cookie 是一个 PHP 库，为管理 cookies 提供了一种简单而有效的方法。

## 调试

调试在你开发本地环境时至关重要。有一些插件可以提升你的调试体验。

- [tracy/tracy](/awesome-plugins/tracy) - 这是一个功能齐全的错误处理程序，可以与 Flight 一起使用。它有许多面板可以帮助你调试应用程序。它也非常容易扩展，并添加自己的面板。
- [flightphp/tracy-extensions](/awesome-plugins/tracy-extensions) - 与 [Tracy](/awesome-plugins/tracy) 错误处理程序一起使用，这个插件添加了一些额外的面板，专门帮助调试 Flight 项目。

## 数据库

数据库是大多数应用程序的核心。这是你存储和检索数据的方式。一些数据库库只是包裹器，方便编写查询，而一些则是完整的 ORM。

- <span class="badge bg-primary">official</span> [flightphp/core PdoWrapper](/awesome-plugins/pdo-wrapper) - 官方 Flight PDO 包装器，属于核心的一部分。这是一个简单的包装器，帮助简化编写和执行查询的过程。它不是 ORM。
- <span class="badge bg-primary">official</span> [flightphp/active-record](/awesome-plugins/active-record) - 官方 Flight ActiveRecord ORM/映射器。是一个轻量级库，用于轻松检索和存储数据库中的数据。
- [byjg/php-migration](/awesome-plugins/migrations) - 插件，用于跟踪项目的所有数据库更改。

## 加密

加密对任何存储敏感数据的应用程序至关重要。加密和解密数据并不难，但正确存储加密密钥 [可以](https://stackoverflow.com/questions/6767839/where-should-i-store-an-encryption-key-for-php#:~:text=Write%20a%20php%20config%20file%20and%20store%20it,folder%20is%20not%20accessible%20to%20the%20end%20user.) [很](https://www.reddit.com/r/PHP/comments/luqsn/the_encryption_key_where_do_you_store_it/) [困难](https://security.stackexchange.com/questions/48047/location-to-store-an-encryption-key)。最重要的是绝不要将你的加密密钥存储在公共目录中或将其提交到代码库中。

- [defuse/php-encryption](/awesome-plugins/php-encryption) - 这是一个可以用来加密和解密数据的库。启动并运行加密和解密数据相对简单。

## 工作队列

工作队列对于异步处理任务非常有帮助。这可以是发送电子邮件、处理图像，或者任何不需要实时完成的任务。

- [n0nag0n/simple-job-queue](/awesome-plugins/simple-job-queue) - 简单的工作队列是一个可以用于异步处理工作的库。它可以与 beanstalkd、MySQL/MariaDB、SQLite 和 PostgreSQL 一起使用。

## 会话

会话对于 API 来说并不是特别有用，但对于构建 Web 应用程序，保持状态和登录信息的会话非常关键。

- <span class="badge bg-primary">official</span> [flightphp/session](/awesome-plugins/session) - 官方 Flight 会话库。这是一个简单的会话库，可用于存储和检索会话数据。它使用 PHP 内置的会话处理。
- [Ghostff/Session](/awesome-plugins/ghost-session) - PHP 会话管理器（非阻塞、闪存、段、会话加密）。使用 PHP open_ssl 可选地加密/解密会话数据。

## 模板

模板是任何具有 UI 的 Web 应用程序的核心。有许多模板引擎可以与 Flight 一起使用。

- <span class="badge bg-warning">deprecated</span> [flightphp/core View](/learn#views) - 这是核心的一部分，非常基本的模板引擎。如果你的项目中有超过几页，不推荐使用。
- [latte/latte](/awesome-plugins/latte) - Latte 是一个功能齐全的模板引擎，使用起来非常简单，并且比 Twig 或 Smarty 更接近 PHP 语法。它也非常容易扩展并添加自己的过滤器和函数。

## 贡献

有插件想要分享吗？提交一个拉取请求，将其添加到列表中！