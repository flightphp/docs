# Latte

Latte is a full featured templating engine that is very easy to use and feels closer to a PHP syntax than Twig or Smarty. It's also very easy to extend and add your own filters and functions.

## Installation

Install with composer.

```bash
composer require latte/latte
```

## Basic Configuration

There are some basic configuration options to get started. You can read more about them in the [Latte Documentation](https://latte.nette.org/en/guide).

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// This is where Latte will cache your templates to speed things up
	// One neat thing about Latte is that it automatically refreshes your
	// cache when you make changes to your templates!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Tell Latte where the root directory for your views will be at.
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## Simple Layout Example

Here's a simple example of a layout file. This is the file that will be used to wrap all of your other views.

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}My App</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- your nav elements here -->
			</nav>
		</header>
		<div id="content">
			<!-- This is the magic right here -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Copyright
		</div>
	</body>
</html>
```

And now we have your file that's going to render inside that content block:

```html
<!-- app/views/home.latte -->
<!-- This tells Latte that this file is "inside" the layout.latte file -->
{extends layout.latte}

<!-- This is the content that will be rendered inside the layout inside the content block -->
{block content}
	<h1>Home Page</h1>
	<p>Welcome to my app!</p>
{/block}
```

Then when you go to render this inside your function or controller, you would do something like this:

```php
// simple route
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'Home Page'
	]);
});

// or if you're using a controller
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::latte()->render('home.latte', [
			'title' => 'Home Page'
		]);
	}
}
```

See the [Latte Documentation](https://latte.nette.org/en/guide) for more information on how to use Latte to it's fullest potential!