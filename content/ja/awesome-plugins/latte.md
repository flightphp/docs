# ラテ

ラテは非常に使いやすく、TwigやSmartyよりもPHP構文に近いテンプレートエンジンで、フル機能を備えています。また、独自のフィルタや関数を簡単に拡張および追加できます。

## インストール

Composerでインストールします。

```bash
composer require latte/latte
```

## 基本設定

始めるための基本設定オプションがいくつかあります。詳細については、[ラテのドキュメント](https://latte.nette.org/en/guide)を参照してください。

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// ここがラテがテンプレートをキャッシュして処理を高速化する場所です。
	// ラテの素晴らしい点の1つは、テンプレートを変更すると自動的にキャッシュが更新されることです！
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// ビューのルートディレクトリをラテに伝えます。
	$latte->setLoader(new \Latte\Loaders\FileLoader($app->get('flight.views.path')));
});
```

## シンプルなレイアウトの例

以下はレイアウトファイルのシンプルな例です。これは他のすべてのビューを囲むために使用されるファイルです。

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
				<!-- ここにナビゲーション要素を追加 -->
			</nav>
		</header>
		<div id="content">
			<!-- ここが魔法の部分です -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; 著作権
		</div>
	</body>
</html>
```

そして、そのコンテンツブロック内にレンダリングされるファイルを取得します：

```html
<!-- app/views/home.latte -->
<!-- これにより、このファイルがlayout.latteファイルの「内部」であることがラテに伝えられます -->
{extends layout.latte}

<!-- これはレイアウト内のコンテンツブロック内にレンダリングされるコンテンツです -->
{block content}
	<h1>ホームページ</h1>
	<p>アプリへようこそ！</p>
{/block}
```

次に、この機能またはコントローラー内でこれをレンダリングするときは、次のようにします：

```php
// シンプルなルート
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'ホームページ'
	]);
});

// コントローラーを使用している場合
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

ラテを最大限に活用する方法の詳細については、[ラテのドキュメント](https://latte.nette.org/en/guide)を参照してください！