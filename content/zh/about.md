# 什么是Flight?

Flight 是一个快速、简单、可扩展的 PHP 框架。它非常灵活，可用于构建任何类型的 Web 应用程序。它专注于简单性，并且采用易于理解和使用的方式编写。

对于那些刚接触 PHP 并想要学习如何构建 Web 应用程序的初学者来说，Flight 是一个很好的入门框架。对于有经验的开发人员来说，Flight 也是一个很好的框架，可以更好地控制他们的 Web 应用程序。它设计用于轻松构建 RESTful API、简单的 Web 应用程序或复杂的 Web 应用程序。

## 快速开始

```php
<?php

// 如果使用 Composer 安装
require 'vendor/autoload.php';
// 或者如果手动通过 zip 文件安装
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="video-container">
	<iframe width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>

看起来很简单是吧？[在文档中了解更多关于Flight的信息！](learn)

### 骨架/样板应用

有一个示例应用程序可以帮助您开始使用Flight框架。访问 [flightphp/skeleton](https://github.com/flightphp/skeleton) 获取开始说明！您还可以访问 [examples](examples) 页面，以获取关于Flight能做什么的一些灵感。

# 社区

我们在Matrix上！在[#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)与我们交流。

# 贡献

有两种方式可以贡献给Flight：

1. 您可以通过访问 [核心仓库](https://github.com/flightphp/core) 贡献到核心框架。
1. 您可以贡献到文档。此文档网站托管在 [Github](https://github.com/flightphp/docs) 上。如果您发现错误或想要改进某些内容，请随时更正并提交拉取请求！我们尽量跟进事务，但是更新和语言翻译是受欢迎的。

# 要求

Flight 需要 PHP 7.4 或更高版本。

**注意：** PHP 7.4 受支持，因为在撰写本文时（2024年），PHP 7.4 是一些 LTS Linux 发行版的默认版本。强制迁移到 PHP >8 将会给这些用户带来许多烦恼。该框架也支持 PHP >8。

# 许可证

Flight根据 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 许可发布。