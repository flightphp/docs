# Flight PHP 框架

Flight 是一个快速、简单、可扩展的 PHP 框架——专为那些希望快速完成任务、零麻烦的开发者而构建。无论您是在构建经典 Web 应用、闪电般的 API，还是实验最新的 AI 驱动工具，Flight 的低资源占用和直观设计使其成为完美选择。Flight 旨在保持精简，但也能处理企业级架构需求。

## 为什么选择 Flight？

- **适合初学者：** Flight 是新 PHP 开发者的绝佳起点。其清晰的结构和简单的语法帮助您学习 Web 开发，而不会迷失在样板代码中。
- **专业开发者喜爱：** 经验丰富的开发者喜爱 Flight 的灵活性和控制力。您可以从小型原型扩展到功能齐全的应用，而无需切换框架。
- **向后兼容：** 我们重视您的时间。Flight v3 是 v2 的增强版本，保留了几乎所有相同的 API。我们相信进化而非革命——不再每次主要版本发布时“打破世界”。
- **零依赖：** Flight 的核心完全无依赖——没有 polyfill，没有外部包，甚至没有 PSR 接口。这意味着更少的攻击向量、更小的资源占用，以及上游依赖带来的意外破坏性变更。可选插件可能包含依赖，但核心始终保持精简和安全。
- **AI 导向：** Flight 的最小开销和干净架构使其非常适合集成 AI 工具和 API。无论您是在构建智能聊天机器人、AI 驱动的仪表板，还是只是想实验，Flight 不会妨碍您，让您专注于重要事项。[骨架应用](https://github.com/flightphp/skeleton) 开箱即用，带有主要 AI 编码助手的预构建指令文件！[了解更多关于使用 AI 与 Flight](/learn/ai)

## 视频概述

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video">足够简单，对吧？</span>
      <br>
      <a href="https://docs.flightphp.com/learn">了解更多</a> 关于 Flight 的文档！
    </div>
  </div>
</div>

## 快速开始

要进行快速的裸机安装，请使用 Composer 安装：

```bash
composer require flightphp/core
```

或者您可以从 [这里](https://github.com/flightphp/core) 下载仓库的 zip 文件。然后您将有一个基本的 `index.php` 文件，如下所示：

```php
<?php

// 如果使用 composer 安装
require 'vendor/autoload.php';
// 或者如果手动通过 zip 文件安装
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

就是这样！您现在有一个基本的 Flight 应用。您可以使用 `php -S localhost:8000` 运行此文件，然后在浏览器中访问 `http://localhost:8000` 查看输出。

## 骨架/样板应用

有一个示例应用可以帮助您使用 Flight 开始项目。它具有结构化的布局、基本的配置全部设置好，并直接处理 composer 脚本！查看 [flightphp/skeleton](https://github.com/flightphp/skeleton) 获取一个开箱即用的项目，或者访问 [examples](examples) 页面获取灵感。想看看 AI 如何融入？[探索 AI 驱动的示例](/learn/ai)。

## 安装骨架应用

足够简单！

```bash
# 创建新项目
composer create-project flightphp/skeleton my-project/
# 进入您的新项目目录
cd my-project/
# 启动本地开发服务器，立即开始！
composer start
```

它将创建项目结构，设置您需要的文件，您就可以开始了！

## 高性能

Flight 是最快的 PHP 框架之一。其轻量级核心意味着更少的开销和更高的速度——完美适用于传统应用和现代 AI 驱动项目。您可以在 [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks) 查看所有基准测试。

下面是与其他一些流行 PHP 框架的基准测试。

| 框架         | 纯文本请求/秒 | JSON 请求/秒 |
| ------------ | ------------- | ------------ |
| Flight       | 190,421       | 182,491      |
| Yii          | 145,749       | 131,434      |
| Fat-Free     | 139,238       | 133,952      |
| Slim         | 89,588        | 87,348       |
| Phalcon      | 95,911        | 87,675       |
| Symfony      | 65,053        | 63,237       |
| Lumen        | 40,572        | 39,700       |
| Laravel      | 26,657        | 26,901       |
| CodeIgniter  | 20,628        | 19,901       |

## Flight 和 AI

好奇它如何处理 AI？[发现](/learn/ai) Flight 如何让使用您喜欢的编码 LLM 变得容易！

## 稳定性和向后兼容

我们重视您的时间。我们都见过那些每隔几年完全重塑自己的框架，让开发者面临破损代码和昂贵的迁移。Flight 不同。Flight v3 被设计为 v2 的增强版本，这意味着您熟悉和喜爱的 API 没有被剥离。事实上，大多数 v2 项目在 v3 中无需任何更改即可工作。

我们致力于保持 Flight 的稳定性，让您专注于构建应用，而不是修复框架。

# 社区

我们在 Matrix Chat 上

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

以及 Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# 贡献

您可以通过两种方式为 Flight 贡献：

1. 通过访问 [核心仓库](https://github.com/flightphp/core) 为核心框架贡献。
2. 帮助改进文档！这个文档网站托管在 [Github](https://github.com/flightphp/docs) 上。如果您发现错误或想改进某些内容，请随时提交拉取请求。我们热爱更新和新想法——尤其是关于 AI 和新技术！

# 要求

Flight 需要 PHP 7.4 或更高版本。

**注意：** 支持 PHP 7.4 是因为在撰写本文时（2024 年），PHP 7.4 是某些 LTS Linux 发行版的默认版本。强制升级到 PHP >8 会为那些用户带来很多麻烦。该框架也支持 PHP >8。

# 许可证

Flight 根据 [MIT](https://github.com/flightphp/core/blob/master/LICENSE) 许可证发布。