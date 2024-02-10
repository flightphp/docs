# 视图

默认情况下，Flight提供一些基本的模板功能。

如果需要更复杂的模板需求，请参阅[自定义视图](#custom-views)部分中的Smarty和Latte示例。

要显示一个视图模板，请使用`render`方法，指定模板文件的名称和可选的模板数据：

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

您传递的模板数据将自动注入到模板中，可以像本地变量一样引用。模板文件简单地是PHP文件。如果`hello.php`模板文件的内容是：

```php
Hello, <?= $name ?>!
```

输出将是：

```
Hello, Bob!
```

您也可以通过使用`set`方法手动设置视图变量：

```php
Flight::view()->set('name', 'Bob');
```

现在变量`name`可以在所有视图中使用。因此，您可以简单地这样做：

```php
Flight::render('hello');
```

请注意，在`render`方法中指定模板名称时，可以省略`.php`扩展名。

默认情况下，Flight将查找`views`目录中的模板文件。您可以通过设置以下配置来为模板设置备用路径：

```php
Flight::set('flight.views.path', '/path/to/views');
```

## 布局

网站通常有一个使用单个布局模板文件的常见模式。要呈现用于布局的内容，可以向`render`方法传递一个可选参数。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

您的视图将保存名为`headerContent`和`bodyContent`的变量。然后，您可以通过执行以下操作来呈现布局：

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

## 自定义视图

Flight允许您简单注册自己的视图类来替换默认视图引擎。

### Smarty

这是如何为视图使用[Smarty](http://www.smarty.net/)模板引擎的示例：

```php
// 加载Smarty库
require './Smarty/libs/Smarty.class.php';

// 将Smarty注册为视图类
// 还要传递回调函数以在加载时配置Smarty
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

为完整起见，您还应该覆盖Flight默认的`render`方法：

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

这是如何为视图使用[Latte](https://latte.nette.org/)模板引擎的示例：

```php

// 将Latte注册为视图类
// 还要传递回调函数以在加载时配置Latte
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // 这是Latte将缓存模板以加快速度的位置
	// Latte的一个很棒之处在于，当您对模板进行更改时，它会自动刷新您的缓存！
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// 告诉Latte您的视图根目录将在哪里
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// 并包装一下，以便您可以正确使用Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // 这就像$latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```  