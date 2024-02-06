# ラッテ

ラッテは非常に使いやすく、TwigやSmartyよりもPHPの構文に近い感じがするフル機能のテンプレートエンジンです。拡張や独自のフィルタや関数を追加することも非常に簡単です。

## インストール

Composerを使用してインストールします。

```bash
composer require latte/latte
```

## 基本的な設定

開始するための基本的な設定オプションがあります。詳細については、[ラッテドキュメント](https://latte.nette.org/en/guide)を参照してください。

```php
use Latte\Engine as LatteEngine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', LatteEngine::class, [], function(LatteEngine $latte) use ($app) {

	// ここがラッテがテンプレートをキャッシュして処理を高速化する場所です
	// ラッテの素晴らしい機能の1つは、テンプレートを変更すると自動的にキャッシュをリフレッシュすることです！
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// ラッテにビューのルートディレクトリがある場所を教えてください。
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
				<!-- ここにナビゲーション要素を記述します -->
			</nav>
		</header>
		<div id="content">
			<!-- ここが魔法の場所です -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; 著作権
		</div>
	</body>
</html>
```

そして、このコンテンツブロック内にレンダリングされるファイルがあります:

```html
<!-- app/views/home.latte -->
<!-- これでラッテにこのファイルがlayout.latteファイルの「内側」にあることを示します -->
{extends layout.latte}

<!-- これは、レイアウトの内部のコンテンツブロックにレンダリングされるコンテンツです -->
{block content}
	<h1>ホームページ</h1>
	<p>アプリへようこそ！</p>
{/block}
```
その後、このファイルを関数またはコントローラ内でレンダリングする場合、次のようにします:

```php
// シンプルなルート
Flight::route('/', function () {
	Flight::latte()->render('home.latte', [
		'title' => 'ホームページ'
	]);
});

// または、コントローラを使用している場合
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

ラッテを最大限に活用する方法についての詳細は、[ラッテドキュメント](https://latte.nette.org/en/guide)を参照してください！