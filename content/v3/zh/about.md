# 什么是 Flight？

Flight 是一个快速、简单、可扩展的 PHP 框架。它相当灵活，可以用于构建任何类型的 web 应用程序。它的设计简单易懂。

Flight 是初学者学习 PHP 的绝佳框架，适合那些想要学习如何构建 web 应用程序的人。对于希望对其 web 应用程序有更多控制的经验丰富的开发者来说，它也是一个很好的框架。它的设计可以轻松构建 RESTful API、简单的 web 应用程序或复杂的 web 应用程序。

## 快速入门

首先通过 Composer 安装

```bash
composer require flightphp/core
```

或者你可以在 [这里](https://github.com/flightphp/core) 下载 repo 的 zip 文件。然后你会有一个基本的 `index.php` 文件，如下所示：

```php
<?php

// 如果通过 composer 安装
require 'vendor/autoload.php';
// 或者如果通过 zip 文件手动安装
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo '你好，世界！';
});

Flight::route('/json', function() {
  Flight::json(['hello' => '世界']);
});

Flight::start();
```

就这样！你拥有了一个基本的 Flight 应用程序。你可以用 `php -S localhost:8000` 运行这个文件，并在浏览器中访问 `http://localhost:8000` 查看输出。

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube 视频播放器" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">简单吧，对吧？</span>
      <br>
      <a href="https://docs.flightphp.com/learn">在文档中了解更多有关 Flight 的信息！</a>

    </div>
  </div>
</div>

## 它快吗？

是的！Flight 是快速的。它是可用的最快的 PHP 框架之一。你可以在 [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks) 查看所有的基准测试。

请查看下面的基准测试，与一些其他流行的 PHP 框架进行比较。

| 框架      | 明文请求/秒 | JSON 请求/秒 |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238	   | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen	      | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |

## 骨架/样板应用程序

有一个示例应用程序可以帮助你开始使用 Flight 框架。请访问 [flightphp/skeleton](https://github.com/flightphp/skeleton) 获取入门说明！你还可以访问 [examples](examples) 页面，获得一些你可以使用 Flight 完成的事情的灵感。

# 社区

我们在 Matrix 聊天

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

以及 Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# 贡献

有两种方式可以为 Flight 做出贡献：

1. 你可以通过访问 [core repository](https://github.com/flightphp/core) 为核心框架做出贡献。
1. 你可以为文档做出贡献。该文档网站托管在 [Github](https://github.com/flightphp/docs)。如果你发现错误或者想更好地阐述某个内容，欢迎你修改并提交拉取请求！我们努力跟上这些事情，但欢迎更新和语言翻译。

# 要求

Flight 需要 PHP 7.4 或更高版本。

**注意：** PHP 7.4 是被支持的，因为在写作时（2024 年）PHP 7.4 是一些 LTS Linux 发行版的默认版本。强制转向 PHP >8 将给那些用户带来很多麻烦。该框架也支持 PHP >8。

# 许可证

Flight 在 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 许可证下发布。