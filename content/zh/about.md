# 什么是Flight？

Flight是PHP的一个快速、简单、可扩展的框架。它非常多才多艺，可以用于构建任何类型的Web应用程序。它以简单为设计目标，编写方式易于理解和使用。

Flight对于那些刚接触PHP并想学习如何构建Web应用程序的初学者来说是一个很棒的框架。对于有经验的开发人员来说，Flight也是一个很棒的框架，可以更好地控制他们的Web应用程序。它被设计用于轻松构建RESTful API、简单的Web应用程序或复杂的Web应用程序。

## 快速入门

```php
<?php

// 如果使用Composer安装
require 'vendor/autoload.php';
// 或者如果通过zip文件手动安装
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo '你好，世界！';
});

Flight::start();
```

足够简单，对吧？[在文档中了解更多关于Flight的信息！](learn)

### 骨架/样板应用

有一个示例应用程序可以帮助您开始使用Flight框架。访问 [flightphp/skeleton](https://github.com/flightphp/skeleton) 了解如何开始！您还可以访问 [examples](examples) 页面，以获得一些您可以使用Flight做的事情上的灵感。

# 社区

我们在Matrix上！在 [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org) 与我们交流。

# 贡献

有两种方式可以为Flight做贡献：

1. 您可以通过访问 [core repository](https://github.com/flightphp/core) 为核心框架做贡献。
1. 您可以为文档做贡献。这个文档网站托管在 [Github](https://github.com/flightphp/docs) 上。如果您发现了错误或希望改进某个地方，欢迎纠正并提交请求！我们会尽量跟进，但对于更新和语言翻译，我们欢迎贡献。

# 要求

Flight要求PHP 7.4或更高版本。

**注意:** PHP 7.4 被支持，因为在撰写时（2024年），PHP 7.4 是某些 LTS Linux 发行版的默认版本。强制升级到 PHP >8 会给这些用户带来很多困扰。该框架也支持 PHP >8。

# 许可

Flight根据 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 许可发布。