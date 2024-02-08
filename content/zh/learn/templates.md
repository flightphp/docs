# 视图

Flight 默认提供了一些基本的模板功能。

如果您需要更复杂的模板需求，请参阅[自定义视图](#custom-views)部分中的 Smarty 和 Latte 示例。

要显示视图模板，请调用`渲染`方法并传入模板文件名称和可选的模板数据：

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

您传入的模板数据会自动注入到模板中，可以像本地变量一样引用。模板文件只是简单的 PHP 文件。如果`hello.php`模板文件的内容是：

```php
Hello, <?= $name ?>!
```

输出结果将会是：

```
Hello, Bob!
```

您也可以通过`set`方法手动设置视图变量：

```php
Flight::view()->set('name', 'Bob');
```

现在，变量`name`在所有视图中都可用。因此您可以简单地执行：

```php
Flight::render('hello');
```

请注意，在`渲染`方法中指定模板名称时，可以忽略`.php`扩展名。

默认情况下，Flight 会在`views`目录中查找模板文件。您可以通过设置以下配置来为模板设置一个替代路径：

```php
Flight::set('flight.views.path', '/path/to/views');
```

## 布局

网站通常有一个包含交替内容的单个布局模板文件。要渲染要在布局中使用的内容，可以向`渲染`方法传入一个可选参数。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

您的视图将保存名为`headerContent`和`bodyContent`的变量。然后，您可以通过以下方式渲染您的布局：

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

输出将会是：

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

## 自定义视图

Flight 允许您通过注册自己的视图类来简单替换默认的视图引擎。

### Smarty

以下是如何在您的视图中使用 [Smarty](http://www.smarty.net/) 模板引擎：

```php
// 加载 Smarty 库
require './Smarty/libs/Smarty.class.php';

// 注册 Smarty 作为视图类
// 也传递一个回调函数来在加载时配置 Smarty
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
});

// 分配模板数据
Flight::view()->assign('name', 'Bob');

// 显示模板
Flight::view()->display('hello.tpl');
```

为了完整性，您还应该覆盖 Flight 的默认渲染方法：

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

以下是如何在您的视图中使用 [Latte](https://latte.nette.org/) 模板引擎：

```php

// 注册 Latte 作为视图类
// 也传递一个回调函数来在加载时配置 Latte
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // 这里是 Latte 将缓存模板以加快速度的位置
	// Latte 的一个很棒的功能是，当您更改模板时，它会自动刷新您的缓存！
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// 告诉 Latte 您的视图根目录在哪里。
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// 并包装起来，这样您就可以正确使用 Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // 这就像 $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```