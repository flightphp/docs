# Latte

[Latte](https://latte.nette.org/en/guide) 是一个功能齐全的模板引擎，使用起来非常简单，其语法比 Twig 或 Smarty 更接近 PHP。它也非常容易扩展，可以添加自己的过滤器和函数。

## 安装

使用 Composer 安装。

```bash
composer require latte/latte
```

## 基本配置

有一些基本的配置选项来开始使用。您可以在 [Latte 文档](https://latte.nette.org/en/guide) 中阅读更多相关信息。

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Where latte specifically stores its cache
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

## 简单布局示例

这是一个布局文件的简单示例。这个文件将用于包装您的所有其他视图。

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="en">
	<head>
		<title>{$title ? $title . ' - '}我的应用</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- 您的导航元素放在这里 -->
			</nav>
		</header>
		<div id="content">
			<!-- 这就是魔力所在 -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; 版权所有
		</div>
	</body>
</html>
```

现在，我们有一个文件将在那个内容块中渲染：

```html
<!-- app/views/home.latte -->
<!-- 这告诉 Latte 该文件“在” layout.latte 文件内部 -->
{extends layout.latte}

<!-- 这是在布局中内容块内渲染的内容 -->
{block content}
	<h1>首页</h1>
	<p>欢迎来到我的应用！</p>
{/block}
```

然后，当您在函数或控制器中渲染这个文件时，您会这样做：

```php
// 简单路由
Flight::route('/', function () {
	Flight::render('home.latte', [
		'title' => '首页'
	]);
});

// 或者如果您使用控制器
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::render('home.latte', [
			'title' => '首页'
		]);
	}
}
```

请参阅 [Latte 文档](https://latte.nette.org/en/guide)，了解如何充分利用 Latte 的更多信息！

## 使用 Tracy 进行调试

_本节需要 PHP 8.1+。_

您还可以使用 [Tracy](https://tracy.nette.org/en/) 来帮助调试您的 Latte 模板文件，开箱即用！如果您已经安装了 Tracy，则需要将 Latte 扩展添加到 Tracy 中。

```php
// services.php
use Tracy\Debugger;

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Where latte specifically stores its cache
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	// This will only add the extension if the Tracy Debug Bar is enabled
	if (Debugger::$showBar === true) {
		// this is where you add the Latte Panel to Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}
	$latte->render($finalPath, $data, $block);
});
```