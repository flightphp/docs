# HTML 视图与模板

## 概述

Flight 默认提供了一些基本的 HTML 模板功能。模板化是将应用程序逻辑与表现层解耦的一种非常有效的方式。专用引擎（Twig、Latte 等）还为 [AI 编码工具](/learn/ai) 提供了熟悉且受约束的语法，因此它们不太可能将业务逻辑直接塞进你的 HTML 中。

## 理解

在构建应用程序时，你很可能会有想要返回给最终用户的 HTML。PHP 本身是一种模板语言，但将数据库调用、API 调用等业务逻辑包装到 HTML 文件中并让测试和解耦变得非常困难是 _非常_ 容易的。通过将数据推入模板并让模板自行渲染，解耦和单元测试你的代码会变得容易得多。如果你使用模板，你会感谢我们的！

## 基本用法

Flight 允许你通过映射 `render`（或注册视图类）来简单替换默认视图引擎。向下滚动查看 Twig、Latte、Smarty、Blade 等更多内容。

> **骨架默认：** 官方的 [flightphp/skeleton](https://github.com/flightphp/skeleton) 在 `app/views/`（`*.twig`）下 **仅使用 Twig**。控制器调用 `$this->app->render('welcome', $data)`（扩展名可选）。这是新项目的应用选择——而不是 Flight 核心的要求。Latte 和其他引擎仍然完全受支持。

### Twig

<span class="badge bg-info">骨架默认</span>

[Twig](https://twig.symfony.com/) 是一个灵活、快速且安全的模板引擎，被 Symfony 和许多其他 PHP 项目使用。AI 编码工具往往特别了解 Twig，并且它默认自动转义输出，有助于防止 XSS。

#### 安装

```bash
composer require twig/twig
```

（当你运行 `composer create-project flightphp/skeleton` 时已包含。）

#### 基本配置

覆盖 `render` 方法以使用 Twig 而不是默认的 PHP 渲染器：

```php
// 覆盖 render 方法以使用 Twig 而不是默认的 PHP 渲染器
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

在骨架中，这种连接位于 `app/config/services.php`（共享的 Twig 环境、缓存路径、像 `base_url` / CSP nonce 这样的全局变量）。优先注入 `Engine` 并从控制器调用 `$app->render()`，这样代码就能保持 [对 AI 和测试友好](/learn/ai)。

#### 在 Flight 中使用 Twig

现在你可以用 Twig 渲染了，可以这样做：

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

当你在浏览器中访问 `/Bob` 时，输出将是：

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

本文档的 [优秀插件](/awesome-plugins/twig) 部分展示了一个使用 Twig 和布局的更完整示例。关于 Tracy 栏上的渲染时指标，请参阅 [Tracy 扩展中的 Twig 面板](/awesome-plugins/tracy-extensions#twig-panel-optional)。

你可以阅读 [官方文档](https://twig.symfony.com/doc/3.x/) 来了解更多关于 Twig 完整功能的信息。

### Latte

<span class="badge bg-secondary">绝佳替代方案</span>

[Latte](https://latte.nette.org/) 是一个功能齐全、语法类似 PHP 的引擎。它仍然是 Flight 应用的绝佳选择；骨架只是将 Twig 标准化为一个共享默认（尤其当 AI 工具生成模板时很有帮助）。

#### 安装

```bash
composer require latte/latte
```

#### 基本配置

主要思想是覆盖 `render` 方法以使用 Latte 而不是默认的 PHP 渲染器。

```php
// 覆盖 render 方法以使用 Latte 而不是默认的 PHP 渲染器
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Latte 专门存储其缓存的位置
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### 在 Flight 中使用 Latte

现在你可以用 Latte 渲染了，可以这样做：

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

当你在浏览器中访问 `/Bob` 时，输出将是：

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

本文档的 [优秀插件](/awesome-plugins/latte) 部分展示了一个使用 Latte 和布局的更复杂示例。

你可以阅读 [官方文档](https://latte.nette.org/en/) 来了解更多关于 Latte 完整功能的信息，包括翻译和语言功能。

### 内置视图引擎

<span class="badge bg-warning">已弃用</span>

> **注意：** 虽然这仍然是默认功能，并且在技术上仍然有效。

要显示视图模板，请调用 `render` 方法并传入模板文件名和可选的模板数据：

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

你传入的模板数据会自动注入到模板中，并且可以像局部变量一样引用。模板文件就是简单的 PHP 文件。如果 `hello.php` 模板文件的内容是：

```php
Hello, <?= $name ?>!
```

输出将是：

```text
Hello, Bob!
```

你还可以使用 set 方法手动设置视图变量：

```php
Flight::view()->set('name', 'Bob');
```

变量 `name` 现在可在所有视图中使用。因此你可以简单地这样做：

```php
Flight::render('hello');
```

请注意，在 render 方法中指定模板名称时，可以省略 `.php` 扩展名。

默认情况下，Flight 会在 `views` 目录中查找模板文件。你可以通过设置以下配置为模板设置备用路径：

```php
Flight::set('flight.views.path', '/path/to/views');
```

默认情况下，Flight 内置的 `View` 还会接受绝对模板路径，或从该目录向上爬升的名称。对于大多数应用，你应该锁定这一点：

```php
Flight::set('flight.views.restrict_to_path', true);
```

这将使 `render()`、`fetch()` 和 `exists()` 保持在 `flight.views.path` 内。为了向后兼容，它默认关闭。请参阅 [安全](/learn/security#flightviewsrestrict_to_path)。

#### 布局

网站通常有一个包含可互换内容的单一布局模板文件。要渲染供布局使用的内容，你可以向 `render` 方法传入一个可选参数。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

然后你的视图将拥有名为 `headerContent` 和 `bodyContent` 的已保存变量。然后你可以通过以下方式渲染布局：

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

以下是如何为你的视图使用 [Smarty](http://www.smarty.net/) 模板引擎：

```php
// 加载 Smarty 库
require './Smarty/libs/Smarty.class.php';

// 将 Smarty 注册为视图类
// 同时传入一个回调函数以在加载时配置 Smarty
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

为了完整性，你还应该覆盖 Flight 的默认 render 方法：

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

以下是如何为你的视图使用 [Blade](https://laravel.com/docs/8.x/blade) 模板引擎：

首先，你需要通过 Composer 安装 BladeOne 库：

```bash
composer require eftec/bladeone
```

然后，你可以在 Flight 中将 BladeOne 配置为视图类：

```php
<?php
// 加载 BladeOne 库
use eftec\bladeone\BladeOne;

// 将 BladeOne 注册为视图类
// 同时传入一个回调函数以在加载时配置 BladeOne
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

为了完整性，你还应该覆盖 Flight 的默认 render 方法：

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

在这个例子中，hello.blade.php 模板文件可能如下所示：

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
- [扩展](/learn/extending) - 如何覆盖 `render` 方法以使用不同的模板引擎。
- [路由](/learn/routing) - 如何将路由映射到控制器并渲染视图。
- [响应](/learn/responses) - 如何自定义 HTTP 响应。
- [安全](/learn/security) - 自动转义、XSS 和 `flight.views.restrict_to_path`。
- [AI 与开发者体验](/learn/ai) - 为什么一个视图引擎默认有助于编码代理。
- [为什么要使用框架？](/learn/why-frameworks) - 模板如何融入大局。

## 故障排除
- 如果你的中间件中有重定向，但你的应用似乎没有重定向，请确保在中间件中添加 `exit;` 语句。
- 如果 Twig 找不到模板，请检查 `flight.views.path` 以及该文件是否存在于该路径下并具有预期的扩展名（骨架：`app/views/`）。

## 变更日志
- 文档 – 为原生 PHP 视图记录了 `flight.views.restrict_to_path`。
- 文档 – Twig 被记录为官方骨架默认；Latte 仍是一流的替代方案。
- v2.0 - 初始版本。