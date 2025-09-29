# HTML Views and Templates

## Overview

Flight provides some basic HTML templating functionality by default. Templating is a very effective way for you to disconnect your application logic from your presentation layer.

## Understanding

When you are building an application, you'll likely have HTML that you'll want to deliver back to the end user. PHP by itself is a templating language, but it is _very_ easy to wrap up business logic like database calls, API calls, etc into your HTML file and make testing and decoupling a very difficult process. By pushing data into a template and letting the template render itself, it becomes much easier to decouple and unit test your code. You will thank us if you use templates!

## Basic Usage

Flight allows you to swap out the default view engine simply by registering your
own view class. Scroll down to see examples of how to use Smarty, Latte, Blade, and more!

### Latte

<span class="badge bg-info">recommended</span>

Here's how you would use the [Latte](https://latte.nette.org/)
template engine for your views.

#### Installation

```bash
composer require latte/latte
```

#### Basic Configuration

The main idea is that you overwrite the `render` method to use Latte instead of the default PHP renderer.

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

#### Using Latte in Flight

Now that you can render with Latte, you can do something like this:

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

When you visit `/Bob` in your browser, the output would be:

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

#### Further Reading

A more complex example of using Latte with layouts is shown in the [awesome plugins](/awesome-plugins/latte) section of this documentation.

You can learn more about Latte's full capabilities including translation and language capabilities by reading the [official documentation](https://latte.nette.org/en/).

### Built-in View Engine

<span class="badge bg-warning">deprecated</span>

> **Note:** While this is still the default functionality and still technically works.

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

```text
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

#### Layouts

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

### Smarty

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

### Blade

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

## See Also
- [Extending](/learn/extending) - How to overwrite the `render` method to use a different template engine.
- [Routing](/learn/routing) - How to map routes to controllers and render views.
- [Responses](/learn/responses) - How to customize HTTP responses.
- [Why a Framework?](/learn/why-frameworks) - How templates fit into the big picture.

## Troubleshooting
- If you have a redirect in your middleware, but your app doesn't seem to be redirecting, make sure you add an `exit;` statement in your middleware.

## Changelog
- v2.0 - Initial release.