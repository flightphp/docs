# ラッテ

ラッテは非常に使いやすく、TwigやSmartyよりもPHPの構文に近いフル機能のテンプレートエンジンです。拡張や独自のフィルタや関数を追加することも非常に簡単です。

## インストール

コンポーザーでインストールします。

```bash
composer require latte/latte
```

## 基本的な構成

はじめに始めるための基本的な構成オプションがいくつかあります。詳細については、[ラッテドキュメント](https://latte.nette.org/en/guide)を参照できます。

```php

use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// これはラッテがテンプレートをキャッシュして処理を高速化する場所です
	// ラッテの素晴らしい点の1つは、テンプレートを変更すると自動的にキャッシュを更新することです！
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// ラッテに、ビューのルートディレクトリを教えてください。
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});
```

## シンプルなレイアウト例

以下はレイアウトファイルのシンプルな例です。これは他のすべてのビューを包むために使用されるファイルです。

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
				<!-- ここにあなたのナビゲーション要素を挿入 -->
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

そして、そのコンテンツブロック内にレンダリングされるファイルがあります：

```html
<!-- app/views/home.latte -->
<!-- これはレイアウト.latteファイル内にある「中」であることをラッテに伝えます -->
{extends layout.latte}

<!-- レイアウト内のコンテンツブロック内にレンダリングされるコンテンツです -->
{block content}
	<h1>ホームページ</h1>
	<p>アプリへようこそ！</p>
{/block}
```

次に、これを関数やコントローラー内でレンダリングする場合は、次のようにします：

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

ラッテを最大限に活用するための詳細については、[ラッテドキュメント](https://latte.nette.org/en/guide)を参照してください！