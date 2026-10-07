# HTML 视图和模板

## 概述

Flight 默认提供了一些基本的 HTML 模板功能。模板化是一种非常有效的方式，可以将应用程序逻辑与表示层分离。专用的引擎（Twig、Latte 等）也为 [AI 编码工具](/learn/ai) 提供了熟悉且受限的语法，因此它们不太可能将业务逻辑转储到 HTML 中。

## 理解

在构建应用程序时，您很可能会有想要返回给最终用户的 HTML。PHP 本身是一种模板语言，但将数据库调用、API 调用等业务逻辑包装到 HTML 文件中，会使测试和解耦变得非常困难。通过将数据推送到模板中并让模板自行渲染，代码的解耦和单元测试会变得容易得多。如果您使用模板，您会感谢我们的！

## 基本用法

Flight 允许您通过映射 `render`（或注册视图类）来简单地替换默认的视图引擎。向下滚动查看 Twig、Latte、Smarty、Blade 等更多内容。

> **骨架默认：** 官方的 [flightphp/skeleton](https://github.com/flightphp/skeleton) 在 `app/views/` 下**仅使用 Twig**（`*.twig`）。控制器调用 `$this->app->render('welcome', $data)`（扩展名可选）。这是新项目的应用程序选择，而不是 Flight 核心的要求。Latte 和其他引擎仍然完全支持。

### Twig

<span class="badge bg-info">骨架默认</span>

[Twig](https://twig.symfony.com/) 是一个灵活、快速且安全的模板引擎，被 Symfony 和许多其他 PHP 项目使用。AI 编码工具往往特别了解 Twig，并且它默认自动转义输出，有助于防止 XSS。

#### 安装

```bash
composer require twig/twig
```

（当您运行 `composer create-project flightphp/skeleton` 时已包含。）

#### 基本配置

重写 `render` 方法以使用 Twig 而不是默认的 PHP 渲染器：

```php
// 重写 render 方法以使用 Twig 而不是默认的 PHP 渲染器
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// Twig 存储其编译模板的位置
		'cache' => __DIR__ . '/../cache/twig',
		'auto_reload' => true,
	]);

	// 允许 "welcome" 或 "welcome.twig"
	if (substr($template, -5) !== '.twig') {
		$template .= '.twig';
	}

	echo $twig->render($template, $data);
});
```

在骨架中，此连接位于 `app/config/services.php`（共享的 Twig 环境、缓存路径、全局变量如 `base_url` / CSP nonce）。最好注入 `Engine` 并从控制器调用 `$app->render()`，以便代码保持 [对 AI 和测试友好](/learn/ai)。

#### 在 Flight 中使用 Twig

现在您可以使用 Twig 进行渲染，可以这样做：

```html
{# app/views/home.twig #}
<html>
  <head>
	<title>{% if title %}{{ title }} - {% endif %}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {{ name }}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.twig', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

当您在浏览器中访问 `/Bob` 时，输出将是：

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### 进一步阅读

本文档的 [awesome 插件](/awesome-plugins/twig) 部分展示了一个更完整的使用 Twig 和布局的示例。有关 Tracy 栏上的渲染时指标，请参阅 [Tracy 扩展中的 Twig 面板](/awesome-plugins/tracy-extensions#twig-panel-optional)。

您可以通过阅读[官方文档](https://twig.symfony.com/doc/3.x/)来了解有关 Twig 全部功能的更多信息。

### Latte

<span class="badge bg-secondary">很好的替代方案</span>

[Latte](https://latte.nette.org/) 是一个功能齐全的引擎，具有类似 PHP 的语法。它仍然是 Flight 应用程序的绝佳选择；骨架只是将 Twig 标准化为一个共享默认值（当 AI 工具生成模板时尤其有用）。

#### 安装

```bash
composer require latte/latte
```

#### 基本配置

主要思想是重写 `render` 方法以使用 Latte 而不是默认的 PHP 渲染器。

```php
// 重写 render 方法以使用 latte 而不是默认的 PHP 渲染器
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// latte 专门存储其缓存的位置
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### 在 Flight 中使用 Latte

现在您可以使用 Latte 进行渲染，可以这样做：

```html
<!-- app/views/home.latte -->
<html>
  <head>
	<title>{$title ? $title . ' - '}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {$name}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.latte', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

当您在浏览器中访问 `/Bob` 时，输出将是：

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### 进一步阅读

本文档的 [awesome 插件](/awesome-plugins/latte) 部分展示了一个更复杂的使用 Latte 和布局的示例。

您可以通过阅读[官方文档](https://latte.nette.org/en/)来了解有关 Latte 全部功能（包括翻译和语言功能）的更多信息。

### 内置视图引擎

<span class="badge bg-warning">已弃用</span>

> **注意：** 虽然这仍然是默认功能，并且技术上仍然有效。

要显示视图模板，请调用 `render` 方法，并传入模板文件的名称和可选的模板数据：

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

您传入的模板数据会自动注入到模板中，并且可以像局部变量一样引用。模板文件就是 PHP 文件。如果 `hello.php` 模板文件的内容是：

```php
Hello, <?= $name ?>!
```

输出将是：

```text
Hello, Bob!
```

您还可以使用 set 方法手动设置视图变量：

```php
Flight::view()->set('name', 'Bob');
```

变量 `name` 现在可在所有视图中使用。因此，您可以简单地执行：

```php
Flight::render('hello');
```

请注意，在 render 方法中指定模板名称时，可以省略 `.php` 扩展名。

默认情况下，Flight 会在 `views` 目录中查找模板文件。您可以通过设置以下配置为模板设置备用路径：

```php
Flight::set('flight.views.path', '/path/to/views');
```

默认情况下，Flight 的内置 `View` 也会接受绝对模板路径，或者从该目录爬出的名称。对于大多数应用程序，您应该锁定这一点：

```php
Flight::set('flight.views.restrict_to_path', true);
```

这使得 `render()`、`fetch()` 和 `exists()` 保持在 `flight.views.path` 内。为了向后兼容，默认情况下它是关闭的。请参阅[安全](/learn/security#flightviewsrestrict_to_path)。

#### 布局

网站通常有一个包含可互换内容的单一布局模板文件。要渲染用于布局的内容，您可以向 `render` 方法传递一个可选参数。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

然后您的视图将具有名为 `headerContent` 和 `bodyContent` 的已保存变量。然后您可以通过以下方式渲染布局：

```php
Flight::render('layout', ['title' => 'Home Page']);
```

如果模板文件如下所示：

`header.php`:

```php
<h1><?= $heading ?></h1>
```

`body.php`:

```php
<div><?= $body ?></div>
```

`layout.php`:

```php
<html>
  <head>
    <title><?= $title ?></title>
  </head>
  <body>
    <?= $headerContent ?>
    <?= $bodyContent ?>
  </body>
</html>
```

输出将是：
```html
<html>
  <head>
    <title>Home Page</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

### Smarty

以下是如何为您的视图使用 [Smarty](http://www.smarty.net/) 模板引擎：

```php
// 加载 Smarty 库
require './Smarty/libs/Smarty.class.php';

// 将 Smarty 注册为视图类
// 同时传递一个回调函数以在加载时配置 Smarty
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// 分配模板数据
Flight::view()->assign('name', 'Bob');

// 显示模板
Flight::view()->display('hello.tpl');
```

为了完整起见，您还应该重写 Flight 的默认渲染方法：

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

以下是如何为您的视图使用 [Blade](https://laravel.com/docs/8.x/blade) 模板引擎：

首先，您需要通过 Composer 安装 BladeOne 库：

```bash
composer require eftec/bladeone
```

然后，您可以在 Flight 中将 BladeOne 配置为视图类：

```php
<?php
// 加载 BladeOne 库
use eftec\bladeone\BladeOne;

// 将 BladeOne 注册为视图类
// 同时传递一个回调函数以在加载时配置 BladeOne
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// 分配模板数据
Flight::view()->share('name', 'Bob');

// 显示模板
echo Flight::view()->run('hello', []);
```

为了完整起见，您还应该重写 Flight 的默认渲染方法：

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

在此示例中，hello.blade.php 模板文件可能如下所示：

```php
<?php
Hello, {{ $name }}!
```

输出将是：

```
Hello, Bob!
```

## 另请参阅
- [安装](/install) - 新项目的骨架布局（`app/views/*.twig`）。
- [扩展](/learn/extending) - 如何重写 `render` 方法以使用不同的模板引擎。
- [路由](/learn/routing) - 如何将路由映射到控制器并渲染视图。
- [响应](/learn/responses) - 如何自定义 HTTP 响应。
- [安全](/learn/security) - 自动转义、XSS 和 `flight.views.restrict_to_path`。
- [AI 与开发者体验](/learn/ai) - 为什么一个视图引擎默认值有助于编码代理。
- [为什么使用框架？](/learn/why-frameworks) - 模板如何融入大局。

## 故障排除
- 如果您的中间件中有重定向，但您的应用似乎没有重定向，请确保在中间件中添加 `exit;` 语句。
- 如果 Twig 找不到模板，请检查 `flight.views.path`，并确保文件在该路径下存在且具有预期的扩展名（骨架：`app/views/`）。

## 变更日志
- 文档 – 为原生 PHP 视图记录了 `flight.views.restrict_to_path`。
- 文档 – Twig 被记录为官方骨架默认；Latte 仍然是一流的替代方案。
- v2.0 - 初始版本。