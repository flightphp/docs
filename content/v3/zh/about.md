# 什么是 Flight？

Flight 是一个快速、简单、可扩展的 PHP 框架——专为希望快速完成任务、零麻烦的开发人员而设计。无论您是在构建经典的网络应用程序、极速 API，还是实验最新的 AI 驱动工具，Flight 的低占用空间和直观设计使其成为完美选择。

## 为什么选择 Flight？

- **适合初学者：** Flight 是新 PHP 开发人员的一个伟大起点。其清晰结构和简单语法可帮助您学习网络开发，而不会迷失在样板代码中。
- **专业人士喜爱：** 经验丰富的开发人员喜爱 Flight 的灵活性和控制力。您可以从小型原型扩展到功能齐全的应用程序，而无需切换框架。
- **AI 友好：** Flight 的最小开销和干净架构使其非常适合集成 AI 工具和 API。无论您是在构建智能聊天机器人、AI 驱动的仪表板，还是只是想实验，Flight 会让您专注于重要事项，而不会碍事。 [了解更多关于使用 AI 与 Flight](/learn/ai)

## 快速入门

首先，使用 Composer 安装：

```bash
composer require flightphp/core
```

或者您可以从 [这里](https://github.com/flightphp/core) 下载仓库的 zip 文件。然后，您将有一个基本的 `index.php` 文件，如下所示：

```php
<?php

// 如果使用 Composer 安装
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

就是这样！您有一个基本的 Flight 应用程序。现在，您可以使用 `php -S localhost:8000` 运行此文件，然后在浏览器中访问 `http://localhost:8000` 来查看输出。

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">简单够用，对吗？</span>
      <br>
      <a href="https://docs.flightphp.com/learn">在文档中了解更多关于 Flight！</a>
      <br>
      <button href="/learn/ai" class="btn btn-primary mt-3">发现 Flight 如何让 AI 变得简单</button>
    </div>
  </div>
</div>

## 它快吗？

绝对快！Flight 是最快的 PHP 框架之一。其轻量级核心意味着更少的开销和更高的速度——适合传统应用程序和现代 AI 驱动项目。您可以在 [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks) 查看所有基准测试。

查看以下基准测试，与其他一些流行的 PHP 框架比较。

| Framework | Plaintext Reqs/sec | JSON Reqs/sec |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238    | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen       | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |

## 骨架/样板应用程序

有一个示例应用程序可帮助您快速入门。查看 [flightphp/skeleton](https://github.com/flightphp/skeleton) 以获取一个随时可用的项目，或者访问 [examples](examples) 页面获取灵感。想看看 AI 如何融入？[探索 AI 驱动的示例](/learn/ai)。

# 社区

我们使用 Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

以及 Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# 贡献

您可以通过两种方式为 Flight 贡献力量：

1. 贡献核心框架，访问 [core repository](https://github.com/flightphp/core)。
2. 帮助改进文档！此文档网站托管在 [Github](https://github.com/flightphp/docs)。如果您发现错误或想改进某些内容，请随时提交拉取请求。我们喜欢更新和新想法——尤其是围绕 AI 和新技术！

# 要求

Flight 需要 PHP 7.4 或更高版本。

**注意：** PHP 7.4 得到支持，因为在撰写本文时（2024 年），PHP 7.4 是某些 LTS Linux 发行版的默认版本。强制迁移到 PHP >8 会给用户带来很多麻烦。该框架也支持 PHP >8。

# 许可证

Flight 以 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 许可证发布。