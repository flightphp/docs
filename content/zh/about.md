# 什么是 Flight？

Flight 是一个快速、简单、可扩展的 PHP 框架。它非常多功能，可以用于构建任何类型的 web 应用程序。它的设计考虑了简洁性，并以易于理解和使用的方式编写。

Flight 是一个很好的初学者框架，适合那些刚接触 PHP 并希望学习如何构建 web 应用的人。对于那些希望对其 web 应用程序有更多控制的经验丰富的开发人员来说，它也是一个很好的框架。它旨在轻松构建 RESTful API、简单的 web 应用程序或复杂的 web 应用程序。

## 快速入门

```php
<?php

// 如果通过 composer 安装
require 'vendor/autoload.php';
// 或者如果通过 zip 文件手动安装
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
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">简单得足够对吧？</span>
      <br>
      <a href="https://docs.flightphp.com/learn">在文档中了解更多关于 Flight 的信息！</a>

    </div>
  </div>
</div>

### 骨架/样板应用

有一个示例应用可以帮助你开始使用 Flight 框架。前往 [flightphp/skeleton](https://github.com/flightphp/skeleton) 获取入门说明！你也可以访问 [examples](examples) 页面，获取一些你可以用 Flight 做的事情的灵感。

# 社区

我们在 Matrix 上与我们进行聊天，地址为 [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)。

# 贡献

你可以通过两种方式为 Flight 做出贡献：

1. 你可以访问 [核心仓库](https://github.com/flightphp/core) 为核心框架做贡献。
1. 你可以为文档做贡献。该文档网站托管在 [Github](https://github.com/flightphp/docs) 上。如果你发现错误或希望改善某些内容，请随时进行修正并提交拉取请求！我们尽量跟上，但更新和语言翻译都欢迎。

# 需求

Flight 需要 PHP 7.4 或更高版本。

**注意：** 支持 PHP 7.4，因为在当前撰写时间（2024 年）PHP 7.4 是某些 LTS Linux 发行版的默认版本。强制迁移到 PHP >8 会给这些用户带来很多不便。该框架也支持 PHP >8。

# 许可

Flight 在 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 许可下发布。