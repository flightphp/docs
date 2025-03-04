# HTML 视图和模板

Flight 默认提供了一些基本的模板功能。

Flight 允许您通过注册自己的视图类轻松更换默认视图引擎。向下滚动以查看如何使用 Smarty、Latte、Blade 等的示例！

## 内置视图引擎

要显示视图模板，请调用 `render` 方法，传入模板文件的名称和可选的模板数据：

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

您传入的模板数据会自动注入到模板中，可以像本地变量一样引用。模板文件只是 PHP 文件。如果 `hello.php` 模板文件的内容是：

```php
Hello, <?= $name ?>!
```

输出将是：

```
Hello, Bob!
```

您还可以通过使用 set 方法手动设置视图变量：

```php
Flight::view()->set('name', 'Bob');
```

变量 `name` 现在在您的所有视图中可用。因此您可以简单地执行：

```php
Flight::render('hello');
```

请注意，在 render 方法中指定模板名称时，您可以省略 `.php` 扩展名。

默认情况下，Flight 将在 `views` 目录中查找模板文件。您可以通过设置以下配置为模板设置备用路径：

```php
Flight::set('flight.views.path', '/path/to/views');
```

### 布局

网站常常有一个单一的布局模板文件，里面有可变内容。要渲染可用于布局的内容，您可以向 `render` 方法传递一个可选参数。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

您的视图将保存名为 `headerContent` 和 `bodyContent` 的变量。然后通过以下方式渲染您的布局：

```php
Flight::render('layout', ['title' => 'Home Page']);
```

如果模板文件看起来是这样的：

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

## Smarty

以下是如何为您的视图使用 [Smarty](http://www.smarty.net/) 模板引擎：

```php
// 加载 Smarty 库
require './Smarty/libs/Smarty.class.php';

// 注册 Smarty 作为视图类
// 还需传入一个回调函数以在加载时配置 Smarty
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

为了完整起见，您还应覆盖 Flight 的默认 render 方法：

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

以下是如何为您的视图使用 [Latte](https://latte.nette.org/) 模板引擎：

```php

// 注册 Latte 作为视图类
// 还需传入一个回调函数以在加载时配置 Latte
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // 这是 Latte 将缓存您的模板的地方，以加快速度
	// Latte 的一个有趣的地方是，当您对模板进行更改时，它会自动刷新您的缓存！
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// 告诉 Latte 您的视图根目录将位于何处。
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// 并将其封装，以便您可以正确使用 Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // 这就像是 $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```

## Blade

以下是如何为您的视图使用 [Blade](https://laravel.com/docs/8.x/blade) 模板引擎：

首先，您需要通过 Composer 安装 BladeOne 库：

```bash
composer require eftec/bladeone
```

然后，您可以将 BladeOne 配置为 Flight 中的视图类：

```php
<?php
// 加载 BladeOne 库
use eftec\bladeone\BladeOne;

// 注册 BladeOne 作为视图类
// 还需传入一个回调函数以在加载时配置 BladeOne
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

为了完整起见，您还应覆盖 Flight 的默认 render 方法：

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

在这个例子中，hello.blade.php 模板文件可能看起来像这样：

```php
<?php
Hello, {{ $name }}!
```

输出将是：

```
Hello, Bob!
```

通过遵循这些步骤，您可以将 Blade 模板引擎与 Flight 集成，并用于渲染您的视图。