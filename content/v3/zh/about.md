# 什么是 Flight？

Flight 是一个快速、简单、可扩展的 PHP 框架。它非常灵活，可以用于构建任何类型的 web 应用程序。它的设计关注简单性，写法易于理解和使用。

Flight 是一个非常适合 PHP 新手的框架，适合想学习如何构建 web 应用程序的人。对有经验的开发者而言，它也是一个很好的框架，因为他们想要对自己的 web 应用程序拥有更多控制。它的设计能轻松构建 RESTful API、简单的 web 应用程序或复杂的 web 应用程序。

## 快速开始

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
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube 视频播放器" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">简单得足够对吗？</span>
      <br>
      <a href="https://docs.flightphp.com/learn">在文档中了解更多关于 Flight 的信息！</a>

    </div>
  </div>
</div>

### 骨架/样板应用

有一个示例应用程序可以帮助你开始使用 Flight 框架。前往 [flightphp/skeleton](https://github.com/flightphp/skeleton) 获取如何开始的说明！你也可以访问 [examples](examples) 页面，获取一些你可以用 Flight 实现的灵感。

# 社区

我们在 Matrix 聊天，和我们聊天 [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org)。

# 贡献

你可以通过两种方式为 Flight 做出贡献：

1. 你可以通过访问 [core repository](https://github.com/flightphp/core) 为核心框架贡献代码。
1. 你可以为文档贡献。这份文档网站托管在 [Github](https://github.com/flightphp/docs) 上。如果你发现错误或希望改善某些内容，欢迎纠正并提交拉取请求！我们尽量跟上更新，但欢迎更新和语言翻译。

# 需求

Flight 需要 PHP 7.4 或更高版本。

**注意：** PHP 7.4 得到支持，因为在写作时（2024 年），PHP 7.4 是一些 LTS Linux 发行版的默认版本。强制用户迁移到 PHP >8 会给他们带来很多麻烦。该框架还支持 PHP >8。

# 许可证

Flight 在 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 许可证下发布。