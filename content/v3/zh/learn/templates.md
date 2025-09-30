# HTML 视图和模板

## 概述

Flight 默认提供了一些基本的 HTML 模板功能。模板化是一种非常有效的方式，可以将您的应用逻辑与表示层分离。

## 理解

当您构建应用时，您很可能会有希望传递回最终用户的 HTML。PHP 本身是一种模板语言，但很容易将业务逻辑如数据库调用、API 调用等包装到您的 HTML 文件中，从而使测试和解耦变得非常困难。通过将数据推送到模板并让模板渲染自身，解耦和单元测试您的代码变得容易得多。如果您使用模板，您会感谢我们！

## 基本用法

Flight 允许您通过注册自己的视图类来简单地替换默认的视图引擎。向下滚动查看如何使用 Smarty、Latte、Blade 等示例！

### Latte

<span class="badge bg-info">推荐</span>

以下是如何使用 [Latte](https://latte.nette.org/) 模板引擎来处理您的视图。

#### 安装

```bash
composer require latte/latte
```

#### 基本配置

主要思想是重写 `render` 方法，使用 Latte 而不是默认的 PHP 渲染器。

```php
// overwrite the render method to use latte instead of the default PHP renderer
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Where latte specifically stores its cache
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### 在 Flight 中使用 Latte

现在您可以使用 Latte 渲染，您可以这样做：

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

使用布局的更复杂 Latte 示例在本文档的 [awesome plugins](/awesome-plugins/latte) 部分中展示。

您可以通过阅读 [官方文档](https://latte.nette.org/en/) 来了解 Latte 的完整功能，包括翻译和语言功能。

### 内置视图引擎

<span class="badge bg-warning">已弃用</span>

> **注意：** 虽然这仍然是默认功能，并且在技术上仍然有效。

要显示视图模板，请使用模板文件名称和可选的模板数据调用 `render` 方法：

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

您传入的模板数据会自动注入到模板中，并可以像本地变量一样引用。模板文件只是 PHP 文件。如果 `hello.php` 模板文件的内容是：

```php
Hello, <?= $name ?>!
```

输出将是：

```text
Hello, Bob!
```

您也可以使用 set 方法手动设置视图变量：

```php
Flight::view()->set('name', 'Bob');
```

变量 `name` 现在在所有视图中可用。所以您可以简单地做：

```php
Flight::render('hello');
```

请注意，在 render 方法中指定模板名称时，您可以省略 `.php` 扩展名。

默认情况下，Flight 将在 `views` 目录中查找模板文件。您可以通过设置以下配置来为您的模板设置备用路径：

```php
Flight::set('flight.views.path', '/path/to/views');
```

#### 布局

网站通常有一个单一的布局模板文件，其中包含可互换的内容。要渲染用于布局的内容，您可以向 `render` 方法传递一个可选参数。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

您的视图将保存名为 `headerContent` 和 `bodyContent` 的变量。然后您可以通过这样做来渲染您的布局：

```php
Flight::render('layout', ['title' => 'Home Page']);
```

如果模板文件看起来像这样：

`header.php`：

```php
<h1><?= $heading ?></h1>
```

`body.php`：

```php
<div><?= $body ?></div>
```

`layout.php`：

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

以下是如何使用 [Smarty](http://www.smarty.net/) 模板引擎来处理您的视图：

```php
// Load Smarty library
require './Smarty/libs/Smarty.class.php';

// Register Smarty as the view class
// Also pass a callback function to configure Smarty on load
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Assign template data
Flight::view()->assign('name', 'Bob');

// Display the template
Flight::view()->display('hello.tpl');
```

为了完整性，您还应该重写 Flight 的默认 render 方法：

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

以下是如何使用 [Blade](https://laravel.com/docs/8.x/blade) 模板引擎来处理您的视图：

首先，您需要通过 Composer 安装 BladeOne 库：

```bash
composer require eftec/bladeone
```

然后，您可以在 Flight 中将 BladeOne 配置为视图类：

```php
<?php
// Load BladeOne library
use eftec\bladeone\BladeOne;

// Register BladeOne as the view class
// Also pass a callback function to configure BladeOne on load
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Assign template data
Flight::view()->share('name', 'Bob');

// Display the template
echo Flight::view()->run('hello', []);
```

为了完整性，您还应该重写 Flight 的默认 render 方法：

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

在此示例中，hello.blade.php 模板文件可能看起来像这样：

```php
<?php
Hello, {{ $name }}!
```

输出将是：

```
Hello, Bob!
```

## 另请参阅
- [扩展](/learn/extending) - 如何重写 `render` 方法以使用不同的模板引擎。
- [路由](/learn/routing) - 如何将路由映射到控制器并渲染视图。
- [响应](/learn/responses) - 如何自定义 HTTP 响应。
- [为什么使用框架？](/learn/why-frameworks) - 模板如何融入大局。

## 故障排除
- 如果您的中间件中有重定向，但您的应用似乎没有重定向，请确保在您的中间件中添加 `exit;` 语句。

## 更新日志
- v2.0 - 初始发布。