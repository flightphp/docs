# 视图

Flight默认提供一些基本的模板功能。要显示视图模板，请调用`render`方法并提供模板文件名称以及可选的模板数据：

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

您传递的模板数据将自动注入到模板中，并且可以像本地变量一样引用。模板文件只是简单的PHP文件。如果`hello.php`模板文件的内容是：

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

现在名为`name`的变量可以在所有视图中使用。因此，您只需简单地执行：

```php
Flight::render('hello');
```

请注意，在`render`方法中指定模板名称时，可以省略`.php`扩展名。

默认情况下，Flight将在`views`目录中查找模板文件。您可以通过设置以下配置来为您的模板设置替代路径：

```php
Flight::set('flight.views.path', '/path/to/views');
```

## 布局

网站通常具有一个带有可互换内容的单个布局模板文件。要呈现要在布局中使用的内容，您可以向`render`方法传递一个可选参数。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

然后，您的视图将保存名为`headerContent`和`bodyContent`的变量。然后，您可以通过执行以下操作来呈现您的布局：

```php
Flight::render('layout', ['title' => 'Home Page']);
```

如果模板文件如下所示：

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

## 自定义视图

Flight允许您通过注册自己的视图类简单地更换默认视图引擎。以下是如何为视图使用[Smarty](http://www.smarty.net/)模板引擎的示例：

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

// 设置模板数据
Flight::view()->assign('name', 'Bob');

// 显示模板
Flight::view()->display('hello.tpl');
```

为了完整起见，您还应该覆盖Flight的默认`render`方法：

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```