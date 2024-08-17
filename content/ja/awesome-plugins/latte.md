# ラッテ

[ラッテ](https://latte.nette.org/en/guide) は、非常に使いやすく、Twig や Smarty よりも PHP 構文に近いテンプレートエンジンです。フル機能を備えており、独自のフィルタや関数を追加することも非常に簡単です。

## インストール

Composer を使用してインストールします。

```bash
composer require latte/latte
```

## 基本的な設定

始めるための基本的な設定オプションがあります。[ラッテドキュメント](https://latte.nette.org/en/guide) で詳細を確認できます。

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// ここがラッテがテンプレートをキャッシュして処理を高速化する場所です
	// ラッテの素晴らしい点の1つは、テンプレートを変更すると自動的にキャッシュを更新します！
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// ビューのルートディレクトリを示す Latte の設定
	// $app->get('flight.views.path') は config.php ファイルで設定されています
	//   または `__DIR__ . '/../views/'` のようなものも行えます
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## シンプルなレイアウトの例

以下はレイアウトファイルのシンプルな例です。このファイルは他のすべてのビューを囲むために使用されます。

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
				<!-- ここにナビゲーション要素が入ります -->
			</nav>
		</header>
		<div id="content">
			<!-- これが魔法です -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; 著作権
		</div>
	</body>
</html>
```

そして、コンテンツブロック内でレンダリングされるファイルがあります:

```html
<!-- app/views/home.latte -->
<!-- このファイルが layout.latte ファイル内にあることを Latte に伝えます -->
{extends layout.latte}

<!-- レイアウト内のコンテンツブロック内に表示されるコンテンツです -->
{block content}
	<h1>ホームページ</h1>
	<p>アプリへようこそ！</p>
{/block}
```

次に、この内容を関数またはコントローラ内でレンダリングする際には、次のように行います:

```php
// シンプルなルート
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'ホームページ'
	]);
});

// もしくはコントローラを使用している場合
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

ラッテを最大限に活用するための詳細については、[Latte Documentation](https://latte.nette.org/en/guide) を参照してください。