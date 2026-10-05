# HTML Views and Templates

## Overview

Flight provides some basic HTML templating functionality by default. Templating is a very effective way for you to disconnect your application logic from your presentation layer. A dedicated engine (Twig, Latte, etc.) also gives [AI coding tools](/learn/ai) a familiar, constrained syntax so they are less likely to dump business logic into your HTML.

## Understanding

When you are building an application, you'll likely have HTML that you'll want to deliver back to the end user. PHP by itself is a templating language, but it is _very_ easy to wrap up business logic like database calls, API calls, etc into your HTML file and make testing and decoupling a very difficult process. By pushing data into a template and letting the template render itself, it becomes much easier to decouple and unit test your code. You will thank us if you use templates!

## Basic Usage

Flight allows you to swap out the default view engine simply by mapping `render` (or registering a view class). Scroll down for Twig, Latte, Smarty, Blade, and more.

> **Skeleton default:** The official [flightphp/skeleton](https://github.com/flightphp/skeleton) uses **Twig only** under `app/views/` (`*.twig`). Controllers call `$this->app->render('welcome', $data)` (extension optional). That is an application choice for new projects—not a requirement of Flight core. Latte and other engines remain fully supported.

### Twig

<span class="badge bg-info">skeleton default</span>

[Twig](https://twig.symfony.com/) is a flexible, fast, and secure template engine used by Symfony and many other PHP projects. AI coding tools tend to know Twig especially well, and it auto-escapes output by default which helps protect against XSS.

#### Installation

```bash
composer require twig/twig
```

(Already included when you `composer create-project flightphp/skeleton`.)

#### Basic Configuration

Overwrite the `render` method to use Twig instead of the default PHP renderer:

```php
// overwrite the render method to use Twig instead of the default PHP renderer
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// Where Twig stores its compiled templates
		'cache' => __DIR__ . '/../cache/twig',
		'auto_reload' => true,
	]);

	// Allow "welcome" or "welcome.twig"
	if (substr($template, -5) !== '.twig') {
		$template .= '.twig';
	}

	echo $twig->render($template, $data);
});
```

In the skeleton, this wiring lives in `app/config/services.php` (shared Twig environment, cache path, globals like `base_url` / CSP nonce). Prefer injecting `Engine` and calling `$app->render()` from controllers so the code stays [AI- and test-friendly](/learn/ai).

#### Using Twig in Flight

Now that you can render with Twig, you can do something like this:

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

A more complete example of using Twig with layouts is shown in the [awesome plugins](/awesome-plugins/twig) section of this documentation. For render-time metrics on the Tracy bar, see the [Twig panel in Tracy Extensions](/awesome-plugins/tracy-extensions#twig-panel-optional).

You can learn more about Twig's full capabilities by reading the [official documentation](https://twig.symfony.com/doc/3.x/).

### Latte

<span class="badge bg-secondary">great alternative</span>

[Latte](https://latte.nette.org/) is a full-featured engine with a PHP-like syntax. It is still an excellent choice for Flight apps; the skeleton simply standardizes on Twig for one shared default (especially helpful when AI tools generate templates).

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

By default Flight's built-in `View` will also accept an absolute template path, or a name that climbs out of that directory. For most apps you should lock that down:

```php
Flight::view()->restrictToPath = true;
```

That keeps `render()`, `fetch()`, and `exists()` inside `flight.views.path`. It is off by default for backwards compatibility. See [Security](/learn/security#viewrestricttopath).

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
- [Installation](/install) - Skeleton layout (`app/views/*.twig`) for new projects.
- [Extending](/learn/extending) - How to overwrite the `render` method to use a different template engine.
- [Routing](/learn/routing) - How to map routes to controllers and render views.
- [Responses](/learn/responses) - How to customize HTTP responses.
- [Security](/learn/security) - Auto-escaping, XSS, and `View::$restrictToPath`.
- [AI & Developer Experience](/learn/ai) - Why one view engine default helps coding agents.
- [Why a Framework?](/learn/why-frameworks) - How templates fit into the big picture.

## Troubleshooting
- If you have a redirect in your middleware, but your app doesn't seem to be redirecting, make sure you add an `exit;` statement in your middleware.
- If Twig cannot find a template, check `flight.views.path` and that the file exists under that path with the expected extension (skeleton: `app/views/`).

## Changelog
- Docs – Documented `View::$restrictToPath` for native PHP views.
- Docs – Twig documented as the official skeleton default; Latte remains a first-class alternative.
- v2.0 - Initial release.