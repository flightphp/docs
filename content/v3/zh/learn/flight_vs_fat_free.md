# Flight vs Fat-Free

## 什么是Fat-Free？
[Fat-Free](https://fatfreeframework.com)（亲切地被称为 **F3**）是一个强大而易于使用的PHP微框架，旨在帮助您快速构建动态和健壮的Web应用程序！

Flight在许多方面与Fat-Free进行比较，可能是功能和简单性方面最接近的近亲。 Fat-Free具有许多Flight没有的功能，但也具有Flight具有的许多功能。 Fat-Free开始显露岁月的痕迹，不再像以前那样受欢迎。

更新频率正在变低，社区也不再像以前那样活跃。代码足够简单，但有时缺乏语法纪律可能会使阅读和理解变得困难。它可以用于PHP 8.3，但代码本身看起来仍然像生存在PHP 5.3中。

## 与Flight相比的优势

- Fat-Free在GitHub上比Flight多一些星星。
- Fat-Free有一些体面的文档，但在某些清晰度方面有所不足。
- Fat-Free有一些稀疏的资源，如YouTube教程和在线文章，可用于学习框架。
- Fat-Free内置了一些有时有用的[有用插件](https://fatfreeframework.com/3.8/api-reference)。
- Fat-Free具有称为Mapper的内置ORM，可用于与您的数据库交互。Flight具有[active-record](/awesome-plugins/active-record)。
- Fat-Free具有内置的会话、缓存和本地化。Flight需要您使用第三方库，但在[文档](/awesome-plugins)中有介绍。
- Fat-Free有一群小众创建的[社区插件](https://fatfreeframework.com/3.8/development#Community)，可用于扩展框架。Flight在[文档](/awesome-plugins)和[示例](/examples)页面中有涵盖一些。
- Fat-Free和Flight一样没有依赖关系。
- Fat-Free和Flight一样旨在让开发人员控制其应用程序并获得简单的开发体验。
- Fat-Free像Flight一样保持向后兼容（部分原因是更新变得[不那么频繁](https://github.com/bcosca/fatfree/releases)）。
- Fat-Free像Flight一样适用于首次涉足框架领域的开发人员。
- Fat-Free有一个内置模板引擎，比Flight的模板引擎更强大。Flight推荐使用[Latte](/awesome-plugins/latte)来实现这一点。
- Fat-Free有一个独特的CLI类型的“route”命令，在其中您可以在Fat-Free内部构建CLI应用程序，并将其视为`GET`请求。Flight使用[runway](/awesome-plugins/runway)来实现这一点。

## 与Flight相比的缺点

- Fat-Free有一些实现测试，甚至有自己的[测试](https://fatfreeframework.com/3.8/test)类，非常基础。然而，它不像Flight那样完全进行单元测试。
- 您必须使用像Google这样的搜索引擎来实际搜索文档网站。
- Flight在其文档网站上有深色模式。（mic drop）
- Fat-Free有一些模块是令人遗憾地未维护。
- Flight有一个简单的[PdoWrapper](/awesome-plugins/pdo-wrapper)，比Fat-Free内置的`DB\SQL`类简单一些。
- Flight有一个[权限插件](/awesome-plugins/permissions)，可以用来保护应用程序。Slim要求您使用第三方库。
- Flight有一个名为[active-record](/awesome-plugins/active-record)的ORM，感觉更像ORM，而不是Fat-Free的Mapper。
  `active-record`的附加好处是，您可以定义记录之间的关系，以实现自动连接，而Fat-Free的Mapper则要求您创建[SQL视图](https://fatfreeframework.com/3.8/databases#ProsandCons)。
- 令人惊讶的是，Fat-Free没有根命名空间。Flight从头到尾都有命名空间，以避免与您自己的代码冲突。
  `Cache`类在这里是最大的问题。
- Fat-Free没有中间件。相反，可以使用`beforeroute`和`afterroute`钩子来过滤控制器中的请求和响应。
- Fat-Free无法分组路由。
- Fat-Free有一个依赖注入容器处理程序，但文档非常稀少，不清楚如何使用它。
- 调试可能会有些棘手，因为基本上一切都存储在所谓的[`HIVE`](https://fatfreeframework.com/3.8/quick-reference)中。