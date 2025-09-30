# Flight 与 Fat-Free

## 什么是 Fat-Free？
[Fat-Free](https://fatfreeframework.com)（亲切地称为 **F3**）是一个强大且易于使用的 PHP 微框架，旨在帮助您快速构建动态且健壮的
Web 应用程序！

Flight 在许多方面与 Fat-Free 相似，并且在功能和简单性方面可能是最接近的亲戚。Fat-Free 拥有
许多 Flight 没有的功能，但它也拥有许多 Flight 拥有的功能。Fat-Free 开始显示出它的年龄
，并且不像曾经那样流行。

更新变得不那么频繁，社区也不像曾经那样活跃。代码足够简单，但有时缺乏
语法规范会使其难以阅读和理解。它确实支持 PHP 8.3，但代码本身仍然看起来像是生活在
PHP 5.3 中。

## 与 Flight 相比的优点

- Fat-Free 在 GitHub 上比 Flight 多一些星标。
- Fat-Free 拥有一些不错的文档，但某些领域缺乏清晰度。
- Fat-Free 拥有一些稀疏的资源，如 YouTube 教程和在线文章，可用于学习该框架。
- Fat-Free 内置了一些有时有用的[有帮助的插件](https://fatfreeframework.com/3.8/api-reference)。
- Fat-Free 内置了一个称为 Mapper 的 ORM，可用于与数据库交互。Flight 拥有 [active-record](/awesome-plugins/active-record)。
- Fat-Free 内置了 Sessions、Caching 和本地化。Flight 要求您使用第三方库，但已在 [documentation](/awesome-plugins) 中覆盖。
- Fat-Free 拥有一个小型的[社区创建插件](https://fatfreeframework.com/3.8/development#Community) 组，可用于扩展框架。Flight 在 [documentation](/awesome-plugins) 和 [examples](/examples) 页面中覆盖了一些。
- Fat-Free 与 Flight 一样没有依赖项。
- Fat-Free 与 Flight 一样旨在赋予开发者对应用程序的控制权，并提供简单的开发者体验。
- Fat-Free 与 Flight 一样维护向后兼容性（部分原因是更新变得[不那么频繁](https://github.com/bcosca/fatfree/releases)）。
- Fat-Free 与 Flight 一样适合首次涉足框架领域的开发者。
- Fat-Free 内置了一个比 Flight 的模板引擎更健壮的模板引擎。Flight 推荐使用 [Latte](/awesome-plugins/latte) 来实现此功能。
- Fat-Free 有一个独特的 CLI 类型“route”命令，您可以在 Fat-Free 内部构建 CLI 应用程序，并将其视为类似于 `GET` 请求。Flight 使用 [runway](/awesome-plugins/runway) 来实现此功能。

## 与 Flight 相比的缺点

- Fat-Free 拥有一些实现测试，甚至有一个自己的非常基本的 [test](https://fatfreeframework.com/3.8/test) 类。然而，
  它不像 Flight 那样 100% 单元测试。
- 您必须使用像 Google 这样的搜索引擎来实际搜索文档站点。
- Flight 的文档站点具有深色模式。（mic drop）
- Fat-Free 拥有一些严重未维护的模块。
- Flight 拥有一个简单的 [PdoWrapper](/learn/pdo-wrapper)，它比 Fat-Free 的内置 `DB\SQL` 类稍简单一些。
- Flight 拥有一个 [permissions plugin](/awesome-plugins/permissions)，可用于保护您的应用程序。Fat Free 要求您使用
  第三方库。
- Flight 拥有一个称为 [active-record](/awesome-plugins/active-record) 的 ORM，它感觉更像是一个 ORM，而不是 Fat-Free 的 Mapper。
  `active-record` 的额外好处是您可以定义记录之间的关系以进行自动连接，而 Fat-Free 的 Mapper
  要求您创建 [SQL views](https://fatfreeframework.com/3.8/databases#ProsandCons)。
- 令人惊讶的是，Fat-Free 没有根命名空间。Flight 完全命名空间化以避免与您自己的代码冲突。
  `Cache` 类是这里最大的违规者。
- Fat-Free 没有中间件。相反，有 `beforeroute` 和 `afterroute` 钩子，可用于在控制器中过滤请求和响应。
- Fat-Free 无法分组路由。
- Fat-Free 有一个依赖注入容器处理程序，但文档关于如何使用它的内容极其稀疏。
- 调试可能有点棘手，因为基本上所有内容都存储在所谓的 [`HIVE`](https://fatfreeframework.com/3.8/quick-reference) 中。