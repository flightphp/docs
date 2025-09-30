# Flight 与 Slim

## 什么是 Slim？
[Slim](https://slimframework.com) 是一个 PHP 微框架，它帮助您快速编写简单却强大的 Web 应用程序和 API。

Flight 的一些 v3 功能的灵感实际上来自于 Slim。路由分组以及按特定顺序执行中间件是两个受 Slim 启发的功能。Slim v3 推出时以简洁为导向，但 v4 版本的评价[褒贬不一](https://github.com/slimphp/Slim/issues/2770)。

## 与 Flight 相比的优势

- Slim 拥有更大的开发者社区，这些开发者会制作实用的模块，帮助您避免重复造轮子。
- Slim 遵循 PHP 社区中常见的许多接口和标准，从而提高了互操作性。
- Slim 拥有不错的文档和教程，可用于学习框架（不过与 Laravel 或 Symfony 相比仍相形见绌）。
- Slim 有各种资源，如 YouTube 教程和在线文章，可用于学习框架。
- Slim 允许您使用任何组件来处理核心路由功能，因为它符合 PSR-7 标准。

## 与 Flight 相比的劣势

- 令人惊讶的是，作为微框架，Slim 的速度并不像您想象的那么快。请参阅
  [TechEmpower 基准测试](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3)
  以获取更多信息。
- Flight 针对那些希望构建轻量级、快速且易用的 Web 应用程序的开发者。
- Flight 无任何依赖项，而 [Slim 有一些依赖项](https://github.com/slimphp/Slim/blob/4.x/composer.json)，您必须安装它们。
- Flight 以简洁和易用性为导向。
- Flight 的核心功能之一是尽最大努力保持向后兼容性。Slim 从 v3 到 v4 是一个破坏性变更。
- Flight 适合那些首次涉足框架领域的开发者。
- Flight 也可以处理企业级应用程序，但它没有像 Slim 那样多的示例和教程。
  它还需要开发者在保持事物组织化和结构良好方面付出更多自律。
- Flight 赋予开发者对应用程序的更多控制权，而 Slim 可能会在幕后偷偷引入一些魔法。
- Flight 有一个简单的 [PdoWrapper](/learn/pdo-wrapper)，可用于与您的数据库交互。Slim 要求您使用第三方库。
- Flight 有一个 [permissions plugin](/awesome-plugins/permissions)，可用于保护您的应用程序。Slim 要求您使用第三方库。
- Flight 有一个名为 [active-record](/awesome-plugins/active-record) 的 ORM，可用于与您的数据库交互。Slim 要求您使用第三方库。
- Flight 有一个名为 [runway](/awesome-plugins/runway) 的 CLI 应用程序，可用于从命令行运行您的应用程序。Slim 没有。