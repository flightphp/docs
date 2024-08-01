# 什么是Flight？

Flight是一个快速、简单、可扩展的PHP框架。它非常多才多艺，可以用来构建任何类型的Web应用程序。它被设计得简单易懂，并且易于理解和使用。

Flight对于那些刚接触PHP并想学习如何构建Web应用程序的初学者来说是一个很棒的框架。对于希望更多控制其Web应用程序的经验丰富的开发者来说，它也是一个很好的框架。它被设计成轻松构建RESTful API、简单Web应用程序或复杂Web应用程序。

## 快速开始

```php
<?php

// 如果使用composer安装
require 'vendor/autoload.php';
// 或者如果手动通过zip文件安装
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo '你好，世界！';
});

Flight::route('/json', function() {
  Flight::json(['你好' => '世界']);
});

Flight::start();
```

<div class="video-container">
	<iframe width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube视频播放器" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>

已经足够简单了吧？[在文档中了解更多关于Flight的信息！](learn)

### 骨架/样板应用

有一个示例应用可以帮助您开始使用Flight框架。前往 [flightphp/skeleton](https://github.com/flightphp/skeleton) 获取开始的说明！您还可以访问 [examples](examples) 页面，以获取关于Flight可能实现的一些功能的灵感。

# 社区

我们在Matrix聊天，可以通过[#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)与我们交流。

# 贡献

有两种方法可以为Flight做出贡献：

1. 您可以通过访问 [core repository](https://github.com/flightphp/core) 来为核心框架做贡献。
1. 您可以为文档做出贡献。这个文档网站托管在 [Github](https://github.com/flightphp/docs) 上。如果您发现错误或希望改进某些内容，请随时进行更正并提交pull request！我们努力跟上事务，但更新和语言翻译是受欢迎的。

# 要求

Flight需要PHP 7.4或更高版本。

**注意：** PHP 7.4得到支持，因为在撰写本文时（2024年），PHP 7.4是一些LTS Linux发行版的默认版本。迫使迁移至PHP >8会为这些用户带来很多困扰。该框架也支持PHP >8。

# 授权许可

Flight根据 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 许可发布。