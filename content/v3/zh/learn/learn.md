# 了解 Flight

Flight 是一个快速、简单、可扩展的 PHP 框架。它非常通用，可用于构建任何类型的 Web 应用程序。
它以简单性为设计理念，并以易于理解和使用的方式编写。

> **注意：** 您将看到一些示例使用 `Flight::` 作为静态变量，而另一些使用 `$app->` 引擎对象。两者可以互换使用。在控制器/中间件中，`$app` 和 `$this->app` 是 Flight 团队推荐的方法。

## 核心组件

### [路由](/learn/routing)

了解如何管理 Web 应用程序的路由。这还包括路由分组、路由参数和中间件。

### [中间件](/learn/middleware)

了解如何使用中间件来过滤应用程序中的请求和响应。

### [自动加载](/learn/autoloading)

了解如何在应用程序中自动加载您自己的类。

### [请求](/learn/requests)

了解如何在应用程序中处理请求和响应。

### [响应](/learn/responses)

了解如何向用户发送响应。

### [HTML 模板](/learn/templates)

了解如何使用内置视图引擎渲染 HTML 模板。

### [安全](/learn/security)

了解如何保护应用程序免受常见安全威胁。

### [配置](/learn/configuration)

了解如何为您的应用程序配置框架。

### [事件管理器](/learn/events)

了解如何使用事件系统向应用程序添加自定义事件。

### [扩展 Flight](/learn/extending)

了解如何通过添加您自己的方法和类来扩展框架。

### [方法钩子和过滤](/learn/filtering)

了解如何向方法和内部框架方法添加事件钩子。

### [依赖注入容器 (DIC)](/learn/dependency-injection-container)

了解如何使用依赖注入容器 (DIC) 来管理应用程序的依赖项。

## 实用类

### [集合](/learn/collections)

集合用于存储数据，并可以作为数组或对象访问，以方便使用。

### [JSON 包装器](/learn/json)

这提供了几个简单函数，使 JSON 的编码和解码保持一致。

### [PDO 包装器](/learn/pdo-wrapper)

PDO 有时会带来不必要的麻烦。这个简单的包装类可以显著简化与数据库的交互。

### [上传文件处理程序](/learn/uploaded-file)

一个简单的类，帮助管理上传的文件并将其移动到永久位置。

## 重要概念

### [为什么使用框架？](/learn/why-frameworks)

这是一篇简短的文章，解释为什么您应该使用框架。在开始使用框架之前，了解其好处是个好主意。

此外，[@lubiana](https://git.php.fail/lubiana) 创建了一个优秀的教程。虽然它没有详细介绍 Flight 的具体内容，
但这个指南将帮助您理解围绕框架的一些主要概念，以及为什么使用它们有益。
您可以在 [这里](https://git.php.fail/lubiana/no-framework-tutorial/src/branch/master/README.md) 找到该教程。

### [Flight 与其他框架的比较](/learn/flight-vs-another-framework)

如果您从其他框架（如 Laravel、Slim、Fat-Free 或 Symfony）迁移到 Flight，此页面将帮助您了解两者之间的差异。

## 其他主题

### [单元测试](/learn/unit-testing)

按照此指南学习如何对 Flight 代码进行单元测试，使其坚如磐石。

### [AI 与开发者体验](/learn/ai)

了解 Flight 如何与 AI 工具和现代开发者工作流程配合，帮助您更快、更智能地编码。

### [从 v2 迁移到 v3](/learn/migrating-to-v3)

向后兼容性在大多数情况下已保持，但从 v2 迁移到 v3 时，您应该注意一些更改。