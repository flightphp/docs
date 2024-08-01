# ラッテ

ラッテは非常に使いやすく、TwigやSmartyよりもPHPの構文に近いフル機能のテンプレーティングエンジンです。また、独自のフィルタや関数を追加することも非常に簡単です。

## インストール

Composerを使用してインストールしてください。

```bash
composer require latte/latte
```

## 基本的な設定

始めるためのいくつかの基本的な設定オプションがあります。詳細については、[ラッテのドキュメント](https://latte.nette.org/en/guide)をご覧ください。

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// ここは、Latteがテンプレートをキャッシュして処理を高速化する場所です
	// Latteの素晴らしい点の1つは、テンプレートを変更したときに
	// キャッシュを自動的にリフレッシュするということです！
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Latteに、ビューのルートディレクトリがどこにあるかを伝えます。
	// $app->get('flight.views.path')はconfig.phpファイルで設定されています
	//   または単純に次のようにすることもできます `__DIR__ . '/../views/'`
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## シンプルなレイアウト例

こちらはレイアウトファイルの簡単な例です。これは他のすべてのビューを包むために使用されるファイルです。

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
				<!-- ここにあなたのナビゲーション要素を追加 -->
			</nav>
		</header>
		<div id="content">
			<!-- これが魔法の部分です -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; 著作権
		</div>
	</body>
</html>
```

そして、このコンテンツブロック内にレンダリングされるファイルがあるとします:

```html
<!-- app/views/home.latte -->
<!-- これはこのファイルが layout.latte ファイルの "内部"であることをラッテに伝えます -->
{extends layout.latte}

<!-- これは、レイアウト内のコンテンツブロック内にレンダリングされるコンテンツです -->
{block content}
	<h1>ホームページ</h1>
	<p>私のアプリへようこそ！</p>
{/block}
```

その後、この機能やコントローラー内でこれをレンダリングする場合、次のように行います:

```php
// シンプルなルート
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'ホームページ'
	]);
});

// またはコントローラーを使用している場合
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::latte()->render('home.latte', [
			'title' => 'ホームページ'
		]);
	}
}
```

ラッテを最大限に活用する方法の詳細については、[ラッテのドキュメント](https://latte.nette.org/en/guide)をご覧ください!