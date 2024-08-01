# Flight vs Laravel

## 什么是 Laravel？
[Laravel](https://laravel.com) 是一个全功能的框架，拥有一切必需的功能，以及一个令人惊叹的开发者专注生态系统，
但在性能和复杂性方面有一定代价。 Laravel 的目标是使开发者能够拥有最高水平的生产力，并使常见任务变得简单。
对于那些希望构建一个全功能企业 Web 应用程序的开发者来说，Laravel 是一个很好的选择。
但在性能和复杂性方面存在一些折衷。学习 Laravel 的基础知识可能很容易，但要熟练使用该框架可能需要一些时间。

开发者也经常感觉到，有许多 Laravel 模块，实际上可以通过使用另一个库或编写自己的代码来解决问题。

## 与 Flight 相比的优点

- Laravel 拥有 **庞大的生态系统**，其中包括开发者和模块，可用于解决常见问题。
- Laravel 拥有一套全功能的 ORM，可用于与数据库交互。
- Laravel 拥有大量文档和教程，可用于学习该框架。
- Laravel 拥有内置的身份验证系统，可用于保护应用程序。
- Laravel 拥有播客、会议、见面会、视频和其他资源，可用于学习该框架。
- Laravel 面向富有经验的开发者，他们希望构建一个全功能的企业 Web 应用程序。

## 与 Flight 相比的缺点

- Laravel 的内部复杂度远远超过了 Flight。这导致性能开销**巨大**。参见 [TechEmpower benchmarks](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 
  了解更多信息。
- Flight 面向寻求构建轻量级、快速、易于使用的 Web 应用程序的开发者。
- Flight 专注于简单性和易用性。
- Flight 的核心特点之一是它尽最大努力保持向后兼容性。Laravel 导致了[大量的困扰](https://www.google.com/search?q=laravel+breaking+changes+major+version+complaints&sca_esv=6862a9c407df8d4e&sca_upv=1&ei=t72pZvDeI4ivptQP1qPMwQY&ved=0ahUKEwiwlurYuNCHAxWIl4kEHdYRM2gQ4dUDCBA&uact=5&oq=laravel+breaking+changes+major+version+complaints&gs_lp=Egxnd3Mtd2l6LXNlcnAiMWxhcmF2ZWwgYnJlYWtpbmcgY2hhbmdlcyBtYWpvciB2ZXJzaW9uIGNvbXBsYWludHMyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEdIjAJQAFgAcAF4AZABAJgBAKABAKoBALgBA8gBAJgCAaACB5gDAIgGAZAGCJIHATGgBwA&sclient=gws-wiz-serp) 在主要版本之间发生了变化。
- Flight 面向第一次涉足框架领域的开发者。
- Flight 没有依赖性，而 [Laravel 有大量依赖](https://github.com/laravel/framework/blob/11.x/composer.json)。
- Flight 也可以处理企业级应用程序，但比 Laravel 的样板代码要少。
  这也将需要开发者更多的纪律来保持组织良好和结构完整。
- Flight 为开发者提供了更多对应用程序的控制权，而 Laravel 在幕后处理了大量的魔术，可能会令人沮丧。