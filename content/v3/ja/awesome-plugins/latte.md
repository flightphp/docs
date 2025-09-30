# Latte

[Latte](https://latte.nette.org/en/guide) は、非常に使いやすく、Twig や Smarty よりも PHP 構文に近いフル機能のテンプレートエンジンです。また、拡張して独自のフィルターや関数を追加することも非常に簡単です。

## インストール

Composer でインストールします。

```bash
composer require latte/latte
```

## 基本設定

開始するための基本的な設定オプションがあります。これらについての詳細は、[Latte ドキュメント](https://latte.nette.org/en/guide) を参照してください。

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Latte がキャッシュを格納する場所
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

## シンプルなレイアウト例

ここにレイアウトファイルのシンプルな例を示します。これは、他のすべてのビューをラップするために使用されるファイルです。

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
				<!-- ここにナビゲーション要素を配置 -->
			</nav>
		</header>
		<div id="content">
			<!-- ここが魔法の部分です -->
			{block content}{/block}
		</div>
		<div id="footer">
			&copy; Copyright
		</div>
	</body>
</html>
```

そして、content ブロック内にレンダリングされるファイルです：

```html
<!-- app/views/home.latte -->
<!-- これにより、Latte にこのファイルが layout.latte ファイルの「内部」であることを伝えます -->
{extends layout.latte}

<!-- レイアウト内の content ブロック内にレンダリングされるコンテンツです -->
{block content}
	<h1>ホームページ</h1>
	<p>私のアプリへようこそ！</p>
{/block}
```

次に、関数やコントローラー内でこれをレンダリングする場合、以下のようにします：

```php
// シンプルなルート
Flight::route('/', function () {
	Flight::render('home.latte', [
		'title' => 'Home Page'
	]);
});

// またはコントローラーを使用する場合
Flight::route('/', [HomeController::class, 'index']);

// HomeController.php
class HomeController
{
	public function index()
	{
		Flight::render('home.latte', [
			'title' => 'Home Page'
		]);
	}
}
```

Latte を最大限に活用する方法の詳細については、[Latte ドキュメント](https://latte.nette.org/en/guide) を参照してください！

## Tracy を使用したデバッグ

_このセクションには PHP 8.1+ が必要です。_

[Tracy](https://tracy.nette.org/en/) を使用して、Latte テンプレートファイルをすぐにデバッグすることもできます！ すでに Tracy をインストールしている場合、Tracy に Latte 拡張を追加する必要があります。

```php
// services.php
use Tracy\Debugger;

$app->map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Latte がキャッシュを格納する場所
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	// Tracy デバッグバーが有効な場合のみ拡張を追加します
	if (Debugger::$showBar === true) {
		// ここで Tracy に Latte パネルを追加します
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}
	$latte->render($finalPath, $data, $block);
});
```