# HTML视图和模板

Flight默认提供一些基本的模板功能。

如果您需要更复杂的模板需求，请参阅[自定义视图](#custom-views)部分中的Smarty和Latte示例。

## 默认视图引擎

要显示视图模板，请调用`render`方法，传入模板文件名称和可选的模板数据：

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

您传入的模板数据将自动注入到模板中，可以像使用本地变量一样引用。模板文件简单地是PHP文件。如果`hello.php`模板文件的内容是：

```php
Hello, <?= $name ?>!
```

输出将是：

```
Hello, Bob!
```

您还可以通过使用`set`方法手动设置视图变量：

```php
Flight::view()->set('name', 'Bob');
```

现在变量`name`可以在所有视图中使用。因此，您可以简单地执行：

```php
Flight::render('hello');
```

请注意，在`render`方法中指定模板的名称时，可以省略`.php`扩展名。

默认情况下，Flight将在`views`目录中查找模板文件。您可以通过设置以下配置来为模板设置另一个路径：

```php
Flight::set('flight.views.path', '/path/to/views');
```

### 布局

网站通常具有一个具有可交换内容的单个布局模板文件。要呈现用于布局的内容，可以将一个可选参数传递给`render`方法。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

然后，您的视图将保存名为`headerContent`和`bodyContent`的变量。然后，您可以通过执行以下操作来呈现您的布局：

```php
Flight::render('layout', ['title' => '主页']);
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
    <title>主页</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

## 自定义视图引擎

Flight允许您通过注册自己的视图类来简单地更换默认视图引擎。

### Smarty

以下是如何在视图中使用[Smarty](http://www.smarty.net/)模板引擎：

```php
// 加载Smarty库
require './Smarty/libs/Smarty.class.php';

// 注册Smarty为视图类
// 还要传递一个回调函数来在加载时配置Smarty
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

为完整起见，您还应该覆盖Flight的默认`render`方法：

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

以下是如何在视图中使用[Latte](https://latte.nette.org/)模板引擎：

```php

// 注册Latte为视图类
// 并传递一个回调函数来在加载时配置Latte
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // 这是Latte将缓存您的模板以加快速度的地方
	// Latte的一个很好的特性是，当您对模板进行更改时，它会自动刷新您的缓存！
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// 告诉Latte您的视图的根目录在哪里
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// 并封装以便您可以正确使用Flight::render()
Flight::map('render', function(string $template, array $data): void {
  // 这就像$latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```