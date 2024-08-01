```zh
# Latte

Latte 是一个功能齐全的模板引擎，非常易于使用，并且与 Twig 或 Smarty 相比更接近 PHP 语法。它也非常容易扩展，并且可以添加您自己的过滤器和函数。

## 安装

使用 composer 安装。

```bash
composer require latte/latte
```

## 基本配置

有一些基本配置选项可供开始使用。您可以在[Latte文档](https://latte.nette.org/en/guide)中了解更多信息。

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// 这是 Latte 将缓存您的模板以加快速度的地方
	// 有关Latte的一个很酷的功能是，当您对模板进行更改时，它会自动刷新您的缓存！
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// 告诉Latte您的视图的根目录在哪里。
	// $app->get('flight.views.path') 在 config.php 文件中设置
	//   您也可以这样做 `__DIR__ . '/../views/'`
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## 简单布局示例

这是一个布局文件的简单示例。这个文件将用来包装所有其他视图。

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
				<!-- 在这里放置导航元素 -->
			</nav>
		</header>
		<div id="content">
			<!-- 这就是魔法所在 -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; 版权所有
		</div>
	</body>
</html>
```

现在我们有一个将在内容块内呈现的文件：

```html
<!-- app/views/home.latte -->
<!-- 这告诉Latte此文件位于layout.latte文件内部 -->
{extends layout.latte}

<!-- 这是将在布局内部内容块内呈现的内容 -->
{block content}
	<h1>主页</h1>
	<p>欢迎来到我的应用!</p>
{/block}
```

然后当您想要在函数或控制器内呈现此内容时，您可以这样做：

```php
// 简单路由
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => '主页'
	]);
});

// 或者如果您正在使用控制器
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::latte()->render('home.latte', [
			'title' => '主页'
		]);
	}
}
```

请参阅[Latte文档](https://latte.nette.org/en/guide)了解如何充分利用Latte！
```