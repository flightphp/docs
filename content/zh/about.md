# 什么是 Flight?

Flight 是 PHP 的一个快速、简单、可扩展的框架。它非常通用，可用于构建任何类型的 web 应用程序。它旨在简单易懂且易于使用。

Flight 是新手学习 PHP 并想要学习如何构建 web 应用程序的绝佳入门框架。对于希望快速、轻松地构建 Web 应用程序的经验丰富的开发人员来说，它也是一个很棒的框架。它被设计用于轻松构建 RESTful API、简单的 Web 应用程序或复杂的 Web 应用程序。

```php
<?php

// 如果使用Composer安装
require 'vendor/autoload.php';
// 或者如果手动通过zip文件安装
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo '你好，世界！';
});

Flight::start();
```

足够简单吧？[了解更多关于 Flight！](learn)

## 快速开始
有一个示例应用程序可以帮助您开始使用 Flight 框架。访问 [flightphp/skeleton](https://github.com/flightphp/skeleton) 获取如何开始的说明！您也可以访问 [examples](examples) 页面，以获取有关使用 Flight 可以做的一些事情的灵感。

# 社区

我们在 Matrix 上！在 [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org) 与我们交流。

# 贡献

有两种方式可以为 Flight 做出贡献：

1. 您可以通过访问 [core repository](https://github.com/flightphp/core) 来为核心框架做贡献。
1. 您可以为文档做贡献。此文档网站托管在 [Github](https://github.com/flightphp/docs) 上。如果您发现错误或想改进某些内容，请随时更正并提交拉取请求！我们尽量跟进事务，但更新和语言翻译都是受欢迎的。

# 要求

Flight 要求 PHP 7.4 或更高版本。

**注意：** PHP 7.4 受支持，因为在撰写当前时间（2024 年）时，PHP 7.4 是某些 LTS Linux 发行版的默认版本。强制切换到 PHP >8 将对这些用户造成很大困扰。该框架也支持 PHP >8。

# 许可证

Flight 根据 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 许可发布。