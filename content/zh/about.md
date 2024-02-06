# 什么是Flight？

Flight是用于PHP的快速、简单、可扩展的框架。它非常多才多艺，可以用于构建任何类型的Web应用程序。它的设计理念是简单易懂、易于理解和使用。

Flight对于那些刚接触PHP并希望学习如何构建Web应用程序的新手来说是一个很好的框架。对于那些想要快速、轻松地构建Web应用程序的经验丰富的开发人员来说，Flight也是一个很好的框架。它被设计用于轻松构建RESTful API、简单的Web应用程序或复杂的Web应用程序。

```php
<?php

// 如果使用composer安装
要求'vendor/autoload.php';
// 或者如果手动通过zip文件安装
// 要求'flight/Flight.php';

Flight::route('/', function() {
  echo '你好，世界！';
});

Flight::start();
```

很简单，对吧？[了解更多关于Flight！](learn)

## 快速开始
有一个示例应用程序可以帮助您快速上手Flight框架。转到[flightphp/skeleton](https://github.com/flightphp/skeleton)获取有关如何入门的说明！您还可以访问[examples](examples)页面，获取一些使用Flight可以实现的灵感。

# 社区

我们在Matrix上！在[#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)与我们交谈。

# 贡献

您可以通过以下两种方式为Flight做贡献：

1. 通过访问[core repository](https://github.com/flightphp/core)为核心框架做贡献。 
1. 通过贡献文档。此文档网站托管在[Github](https://github.com/flightphp/docs)上。如果您发现错误或想更好地阐述某些内容，请随时更正并提交拉取请求！我们会尽量跟进，但更新和语言翻译都是受欢迎的。

# 要求

Flight 需要 PHP 7.4 或更高版本。

**注意：** PHP 7.4 得到支持，是因为在撰写当前时间（2024年）PHP 7.4 是某些LTS Linux发行版的默认版本。强制升级到PHP >8会给这些用户带来很多麻烦。该框架也支持PHP >8。

# 许可

Flight以[MIT](https://github.com/flightphp/core/blob/master/LICENSE)许可发布。