# Flight PHP 框架

Flight 是一个快速、简单、可扩展的 PHP 框架——专为那些希望快速完成任务且不希望大费周章的开发人员而构建。不管您是在构建经典的网络应用、极速的 API，还是在试验最新的 AI 驱动工具，Flight 的低占用和直观设计使其成为完美选择。Flight 旨在保持精简，但也能满足企业架构需求。

## 为什么选择 Flight？

- **适合初学者：** Flight 是新 PHP 开发人员的一个伟大起点。其清晰的结构和简单语法能帮助您学习网络开发，而不会迷失在样板代码中。
- **专业人士喜爱：** 经验丰富的开发人员喜爱 Flight 的灵活性和控制性。您可以从小型原型扩展到功能齐全的应用，而无需切换框架。
- **AI 友好：** Flight 的最小开销和干净架构使其非常适合集成 AI 工具和 API。不管您是在构建智能聊天机器人、AI 驱动的仪表板，还是只是想试验，Flight 会让您专注于重要事项。该 [skeleton app](https://github.com/flightphp/skeleton) 附带了主要 AI 编码助手的预构建说明文件！[了解更多关于使用 AI 与 Flight](/learn/ai)

## 视频概述

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video">够简单，对吧？</span>
      <br>
      <a href="https://docs.flightphp.com/learn">了解更多</a> 关于 Flight 的文档！
    </div>
  </div>
</div>

## 快速入门

要进行快速的基本安装，请使用 Composer 安装：

```bash
composer require flightphp/core
```

或者您可以从 [这里](https://github.com/flightphp/core) 下载仓库的 zip 文件。然后您将有一个基本的 `index.php` 文件，如下所示：

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
  Flight::json([
	'hello' => 'world'
  ]);
});

Flight::start();
```

就是这样！您有一个基本的 Flight 应用。现在您可以使用 `php -S localhost:8000` 运行此文件，然后在浏览器中访问 `http://localhost:8000` 来查看输出。

## 骨架/样板应用

有一个示例应用可以帮助您使用 Flight 开始您的项目。它具有结构化的布局、基本配置以及开箱即用的 Composer 脚本！查看 [flightphp/skeleton](https://github.com/flightphp/skeleton) 以获取一个随时可用的项目，或者访问 [examples](examples) 页面获取灵感。想要了解 AI 如何融入？[探索 AI 驱动的示例](/learn/ai)。

## 安装骨架应用

非常简单！

```bash
# 创建新项目
composer create-project flightphp/skeleton my-project/
# 进入您的新项目目录
cd my-project/
# 启动本地开发服务器立即开始！
composer start
```

它将创建项目结构，设置您需要的所有文件，您就可以开始了！

## 高性能

Flight 是最快的 PHP 框架之一。其轻量级核心意味着更少的开销和更高的速度——完美适用于传统应用和现代 AI 驱动项目。您可以在 [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks) 查看所有基准测试。

查看下面的基准测试，与其他一些流行的 PHP 框架比较。

| 框架       | Plaintext Reqs/sec | JSON Reqs/sec |
| ---------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238    | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen       | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |

## Flight 和 AI

好奇它如何处理 AI？[发现](/learn/ai) Flight 如何让您轻松使用您最喜欢的编码 LLM！

# 社区

我们使用 Matrix 聊天

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

以及 Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# 贡献

您可以通过两种方式为 Flight 贡献：

1. 贡献于核心框架，通过访问 [core repository](https://github.com/flightphp/core)。
2. 帮助改进文档！这个文档网站托管在 [Github](https://github.com/flightphp/docs) 上。如果您发现错误或想改进某些内容，请随时提交拉取请求。我们喜欢更新和新想法——尤其是围绕 AI 和新技术！

# 要求

Flight 需要 PHP 7.4 或更高版本。

**注意：** PHP 7.4 得到支持，因为在撰写本文时（2024 年），PHP 7.4 是一些 LTS Linux 发行版中的默认版本。强制迁移到 PHP >8 会给那些用户带来很多麻烦。该框架也支持 PHP >8。

# 许可

Flight 发布 under the [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 许可。