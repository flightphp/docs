# Flight vs Laravel

## 什么是 Laravel？
[Laravel](https://laravel.com) 是一个功能齐全的框架，具有所有花里胡哨的功能和一个令人惊叹的开发者导向生态系统，
但在性能和复杂性方面付出了代价。Laravel 的目标是让开发者拥有最高水平的生产力，并使常见任务变得容易。Laravel 是那些希望构建功能齐全的企业级 Web 应用程序的开发者的绝佳选择。这会带来一些权衡，特别是性能和复杂性方面的权衡。学习 Laravel 的基础可能很容易，但熟练掌握该框架需要一些时间。

Laravel 还有如此多的模块，以至于开发者常常觉得解决问题的唯一方法是通过这些模块，而实际上你只需使用另一个库或编写自己的代码即可。

## 与 Flight 相比的优点

- Laravel 拥有一个**庞大的生态系统**，包括开发者和模块，可用于解决常见问题。
- Laravel 具有功能齐全的 ORM，可用于与数据库交互。
- Laravel 有海量的文档和教程，可用于学习框架。这对于深入探讨细节可能很好，但也可能不好，因为内容太多需要梳理。
- Laravel 具有内置的认证系统，可用于保护您的应用程序。
- Laravel 有播客、会议、聚会、视频和其他资源，可用于学习框架。
- Laravel 针对经验丰富的开发者，他们希望构建功能齐全的企业级 Web 应用程序。

## 与 Flight 相比的缺点

- Laravel 在底层比 Flight 复杂得多。这在性能方面带来了**剧烈**的代价。请参阅 [TechEmpower 基准测试](https://www.techempower.com/benchmarks/#hw=ph&test=fortune&section=data-r22&l=zik073-cn3) 以获取更多信息。
- Flight 针对那些希望构建轻量级、快速且易于使用的 Web 应用程序的开发者。
- Flight 注重简单性和易用性。
- Flight 的核心特性之一是它尽力保持向后兼容性。Laravel 在主要版本之间会引起[大量挫败感](https://www.google.com/search?q=laravel+breaking+changes+major+version+complaints&sca_esv=6862a9c407df8d4e&sca_upv=1&ei=t72pZvDeI4ivptQP1qPMwQY&ved=0ahUKEwiwlurYuNCHAxWIl4kEHdYRM2gQ4dUDCBA&uact=5&oq=laravel+breaking+changes+major+version+complaints&gs_lp=Egxnd3Mtd2l6LXNlcnAiMWxhcmF2ZWwgYnJlYWtpbmcgY2hhbmdlcyBtYWpvciB2ZXJzaW9uIGNvbXBsYWludHMyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEcyChAAGLADGNYEGEdIjAJQAFgAcAF4AZABAJgBAKABAKoBALgBA8gBAJgCAaACB5gDAIgGAZAGCJIHATGgBwA&sclient=gws-wiz-serp)。
- Flight 适合那些首次涉足框架领域的开发者。
- Flight 没有依赖项，而 [Laravel 有可怕数量的依赖项](https://github.com/laravel/framework/blob/12.x/composer.json)。
- Flight 也可以处理企业级应用程序，但它不像 Laravel 那样有大量样板代码。
  它还需要开发者在组织和结构化方面保持更多纪律。
- Flight 赋予开发者对应用程序的更多控制权，而 Laravel 在幕后有大量魔法，这可能会令人沮丧。