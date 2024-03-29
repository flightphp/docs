# 老司机

老司机是一个功能齐全的模板引擎，非常易于使用，比Twig或Smarty更贴近PHP语法。它也非常容易扩展和添加自己的过滤器和函数。

## 安装

使用composer安装。

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

	// 这是Latte将缓存您的模板以加快速度的地方
	// 关于Latte的一个很棒的功能是，当您对模板进行更改时，它会自动刷新您的缓存！
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// 告诉Latte您的视图的根目录将在哪里。
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## 简单布局示例

以下是一个简单的布局文件示例。这个文件将用于包装所有其他视图。

```html
<!-- app/views/layout.latte -->
<!doctype html>
<html lang="zh-CN">
	<head>
		<title>{$title ? $title . ' - '}My App</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<header>
			<nav>
				<!-- 在这里放置您的导航元素 -->
			</nav>
		</header>
		<div id="content">
			<!-- 这就是神奇的所在 -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; 版权所有
		</div>
	</body>
</html>
```

现在我们有一个文件，将在内容块中呈现：

```html
<!-- app/views/home.latte -->
<!-- 这告诉Latte这个文件是“内部”layout.latte文件 -->
{extends layout.latte}

<!-- 这是将在布局内渲染的内容 -->
{block content}
	<h1>主页</h1>
	<p>欢迎来到我的应用！</p>
{/block}
```

然后当您要在函数或控制器中呈现它时，您可以这样做：

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

请参阅[Latte文档](https://latte.nette.org/en/guide)获取有关如何充分利用Latte的更多信息！