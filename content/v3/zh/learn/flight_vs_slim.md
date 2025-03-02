# Flight 与 Slim

## 什么是Slim？
[Slim](https://slimframework.com) 是一个 PHP 微框架，帮助您快速编写简单而强大的 Web 应用程序和 API。

实际上，Flight 的一些 v3 特性受到了 Slim 的启发。分组路由和按特定顺序执行中间件是两个受 Slim 启发的特性。Slim v3 的出现旨在追求简单性，不过关于 v4，评价[褒贬不一](https://github.com/slimphp/Slim/issues/2770)。

## 与Flight相比的优点

- Slim 拥有更庞大的开发者社区，他们制作便捷的模块，帮助您避免重复造轮子。 
- Slim 遵循许多 PHP 社区常见的接口和规范，提高了可互操作性。
- Slim 拥有不错的文档和教程，可用于学习该框架（虽然与 Laravel 或 Symfony 相比还有差距）。
- Slim 提供各种资源，如 YouTube 教程和在线文章，可用于学习该框架。
- Slim 允许您使用任何组件来处理核心路由功能，因为它符合 PSR-7 规范。

## 与Flight相比的缺点

- 令人惊讶的是，Slim 并不像您想象的那样快速，作为一个微框架。有关更多信息，请参阅[TechEmpower基准测试](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3)。
- Flight 针对寻求构建轻量级、快速且易于使用的 Web 应用程序的开发者。
- Flight 没有依赖关系，而[Slim 有一些依赖](https://github.com/slimphp/Slim/blob/4.x/composer.json)需要您安装。
- Flight 旨在简单和易用。
- Flight 的核心特性之一是尽最大努力保持向后兼容性。 Slim v3 到 v4 是一个破坏性的改变。
- Flight 面向首次涉足框架领域的开发者。
- Flight 也可以开发企业级应用程序，但示例和教程不如 Slim 那么多。
  开发者需要更多的纪律来保持组织和结构良好。
- Flight 给开发者更多对应用程序的控制权，而 Slim 可以在幕后做些魔术。
- Flight 有一个简单的[PdoWrapper](/awesome-plugins/pdo-wrapper)，可用于与数据库交互。 Slim 要求您使用第三方库。
- Flight 有一个[permissions plugin](/awesome-plugins/permissions)，可用于保护应用程序的安全。 Slim 要求您使用第三方库。
- Flight 有一个名为[active-record](/awesome-plugins/active-record)的 ORM，可用于与数据库交互。 Slim 要求您使用第三方库。
- Flight 有一个名为[runway](/awesome-plugins/runway)的 CLI 应用程序，可用于从命令行运行您的应用程序。 Slim 则没有。