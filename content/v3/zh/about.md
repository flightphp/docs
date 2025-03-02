# 什么是 Flight?

Flight 是一个快速、简单、可扩展的 PHP 框架。它非常多才多艺，可以用于构建任何类型的 Web 应用程序。它的设计理念是简洁，编写方式易于理解和使用。

Flight 是一个很好的入门框架，适合那些刚接触 PHP 并想学习如何构建 Web 应用程序的人。对于希望更好控制其 Web 应用程序的经验开发者来说，它也是一个很好的框架。它被设计为能够轻松构建 RESTful API、简单的 Web 应用程序或复杂的 Web 应用程序。

## 快速开始

```php
<?php

// 如果通过 composer 安装
require 'vendor/autoload.php';
// 或者如果通过压缩文件手动安装
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube 视频播放器" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">简单到足够对吧？</span>
      <br>
      <a href="https://docs.flightphp.com/learn">在文档中了解更多关于 Flight 的信息！</a>

    </div>
  </div>
</div>

### 骨架/模板应用

有一个示例应用程序可以帮助您入门 Flight 框架。请访问 [flightphp/skeleton](https://github.com/flightphp/skeleton) 获取入门指南！您还可以访问 [examples](examples) 页面，获取有关您可以使用 Flight 完成的一些事情的灵感。

# 社区

我们在 Matrix Chat 上与我们交流 [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)。

# 贡献

您可以通过两种方式为 Flight 做贡献：

1. 您可以通过访问 [core repository](https://github.com/flightphp/core) 为核心框架做贡献。
1. 您可以为文档做贡献。此文档网站托管在 [Github](https://github.com/flightphp/docs) 上。如果您发现错误或想改进某些内容，请随时进行更正并提交拉取请求！我们会努力跟上进展，但更新和语言翻译是受欢迎的。

# 要求

Flight 需要 PHP 7.4 或更高版本。

**注意：** 支持 PHP 7.4 是因为在撰写本文时（2024），PHP 7.4 是某些 LTS Linux 发行版的默认版本。强制迁移到 PHP >8 会给这些用户带来很多麻烦。该框架也支持 PHP >8。

# 许可

Flight 在 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 许可下发布。