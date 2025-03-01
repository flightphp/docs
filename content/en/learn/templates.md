# HTML Views and Templates

Flight provides some basic templating functionality by default. 

Flight allows you to swap out the default view engine simply by registering your
own view class. Scroll down to see examples of how to use Smarty, Latte, Blade, and more!

## Built-in View Engine

To display a view template call the `render` method with the name 
of the template file and optional template data:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

The template data you pass in is automatically injected into the template and can
be reference like a local variable. Template files are simply PHP files. If the
content of the `hello.php` template file is:

```php
Hello, <?= $name ?>!
```

The output would be:

```
Hello, Bob!
```

You can also manually set view variables by using the set method:

```php
Flight::view()->set('name', 'Bob');
```

The variable `name` is now available across all your views. So you can simply do:

```php
Flight::render('hello');
```

Note that when specifying the name of the template in the render method, you can
leave out the `.php` extension.

By default Flight will look for a `views` directory for template files. You can
set an alternate path for your templates by setting the following config:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### Layouts

It is common for websites to have a single layout template file with interchanging
content. To render content to be used in a layout, you can pass in an optional
parameter to the `render` method.

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

Your view will then have saved variables called `headerContent` and `bodyContent`.
You can then render your layout by doing:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

If the template files looks like this:

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

The output would be:
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

Here's how you would use the [Smarty](http://www.smarty.net/)
template engine for your views:

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

For completeness, you should also override Flight's default render method:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

Here's how you would use the [Latte](https://latte.nette.org/)
template engine for your views:

```php

// Register Latte as the view class
// Also pass a callback function to configure Latte on load
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // This is where Latte will cache your templates to speed things up
	// One neat thing about Latte is that it automatically refreshes your
	// cache when you make changes to your templates!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Tell Latte where the root directory for your views will be at.
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// And wrap it up so you can use Flight::render() correctly
Flight::map('render', function(string $template, array $data): void {
  // This is like $latte_engine->render($template, $data);
  echo Flight::view()->render($template, $data);
});
```

## Blade

Here's how you would use the [Blade](https://laravel.com/docs/8.x/blade) template engine for your views:

First, you need to install the BladeOne library via Composer:

```bash
composer require eftec/bladeone
```

Then, you can configure BladeOne as the view class in Flight:

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

For completeness, you should also override Flight's default render method:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

In this example, the hello.blade.php template file might look like this:

```php
<?php
Hello, {{ $name }}!
```

The output would be:

```
Hello, Bob!
```

By following these steps, you can integrate the Blade template engine with Flight and use it to render your views. 