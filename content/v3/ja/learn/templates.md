# HTML ビューとテンプレート

## 概要

Flight はデフォルトで基本的な HTML テンプレート機能を備えています。テンプレートは、アプリケーションのロジックをプレゼンテーション層から分離する非常に効果的な方法です。

## 理解

アプリケーションを構築する際、エンドユーザーに返す HTML を準備する必要があるでしょう。PHP 自体がテンプレート言語ですが、データベース呼び出し、API 呼び出しなどのビジネスロジックを HTML ファイルに混ぜ込んでしまうと、テストや分離が非常に困難になります。データをテンプレートに押し込み、テンプレート自身にレンダリングさせることで、コードの分離と単体テストがはるかに容易になります。テンプレートを使用すれば、私たちに感謝するはずです！

## 基本的な使用方法

Flight では、デフォルトのビューエンジンを置き換えるために、独自のビュー クラスを登録するだけで簡単に切り替えられます。Smarty、Latte、Blade などの使用例を見るには、下にスクロールしてください！

### Latte

<span class="badge bg-info">推奨</span>

[Latte](https://latte.nette.org/) テンプレート エンジンをビューで使用する方法を以下に示します。

#### インストール

```bash
composer require latte/latte
```

#### 基本的な設定

主なアイデアは、`render` メソッドをオーバーライドして、デフォルトの PHP レンダラーではなく Latte を使用することです。

```php
// overwrite the render method to use latte instead of the default PHP renderer
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Where latte specifically stores its cache
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Flight での Latte の使用

Latte でレンダリングできるようになったら、以下のようにできます：

```html
<!-- app/views/home.latte -->
<html>
  <head>
	<title>{$title ? $title . ' - '}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {$name}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.latte', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

ブラウザで `/Bob` にアクセスすると、出力は次のようになります：

```html
<html>
  <head>
	<title>Home Page - My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, Bob!</h1>
  </body>
</html>
```

#### さらなる読み物

Latte をレイアウトで使用するより複雑な例は、このドキュメントの [awesome plugins](/awesome-plugins/latte) セクションに示されています。

Latte の完全な機能（翻訳や言語機能を含む）については、[公式ドキュメント](https://latte.nette.org/en/) を読んでください。

### ビルトインのビュー エンジン

<span class="badge bg-warning">非推奨</span>

> **注意:** これは依然としてデフォルトの機能であり、技術的には動作します。

ビュー テンプレートを表示するには、`render` メソッドをテンプレート ファイルの名前とオプションのテンプレート データで呼び出します：

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

渡したテンプレート データは自動的にテンプレートに注入され、ローカル変数のように参照できます。テンプレート ファイルは単なる PHP ファイルです。`hello.php` テンプレート ファイルの内容が以下のようである場合：

```php
Hello, <?= $name ?>!
```

出力は次のようになります：

```text
Hello, Bob!
```

`set` メソッドを使用して、手動でビュー変数を設定することもできます：

```php
Flight::view()->set('name', 'Bob');
```

変数 `name` はすべてのビューで利用可能になります。したがって、以下のように単純に実行できます：

```php
Flight::render('hello');
```

`render` メソッドでテンプレート名を指定する際は、`.php` 拡張子を省略できます。

デフォルトでは、Flight はテンプレート ファイルのために `views` ディレクトリを探します。テンプレートの代替パスを設定するには、以下の設定を実行します：

```php
Flight::set('flight.views.path', '/path/to/views');
```

#### レイアウト

ウェブサイトでは、交換可能なコンテンツを持つ単一のレイアウト テンプレート ファイルが一般的です。レイアウトで使用するコンテンツをレンダリングするには、`render` メソッドにオプションのパラメータを渡せます。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

ビューには、`headerContent` と `bodyContent` という名前の変数が保存されます。次に、レイアウトを以下のようにレンダリングできます：

```php
Flight::render('layout', ['title' => 'Home Page']);
```

テンプレート ファイルが以下のようである場合：

`header.php`：

```php
<h1><?= $heading ?></h1>
```

`body.php`：

```php
<div><?= $body ?></div>
```

`layout.php`：

```php
<html>
  <head>
    <title><?= $title ?></title>
  </head>
  <body>
    <?= $headerContent ?>
    <?= $bodyContent ?>
  </body>
</html>
```

出力は次のようになります：
```html
<html>
  <head>
    <title>Home Page</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

### Smarty

[Smarty](http://www.smarty.net/) テンプレート エンジンをビューで使用する方法を以下に示します：

```php
// Load Smarty library
require './Smarty/libs/Smarty.class.php';

// Register Smarty as the view class
// Also pass a callback function to configure Smarty on load
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// Assign template data
Flight::view()->assign('name', 'Bob');

// Display the template
Flight::view()->display('hello.tpl');
```

完全性を期すために、Flight のデフォルトの `render` メソッドもオーバーライドしてください：

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

[Blade](https://laravel.com/docs/8.x/blade) テンプレート エンジンをビューで使用する方法を以下に示します：

まず、Composer 経由で BladeOne ライブラリをインストールする必要があります：

```bash
composer require eftec/bladeone
```

次に、Flight で BladeOne をビュー クラスとして設定できます：

```php
<?php
// Load BladeOne library
use eftec\bladeone\BladeOne;

// Register BladeOne as the view class
// Also pass a callback function to configure BladeOne on load
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// Assign template data
Flight::view()->share('name', 'Bob');

// Display the template
echo Flight::view()->run('hello', []);
```

完全性を期すために、Flight のデフォルトの `render` メソッドもオーバーライドしてください：

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

この例では、`hello.blade.php` テンプレート ファイルは以下のように見えるかもしれません：

```php
<?php
Hello, {{ $name }}!
```

出力は次のようになります：

```
Hello, Bob!
```

## 関連項目
- [拡張](/learn/extending) - 異なるテンプレート エンジンを使用するために `render` メソッドをオーバーライドする方法。
- [ルーティング](/learn/routing) - ルートをコントローラーにマッピングし、ビューをレンダリングする方法。
- [レスポンス](/learn/responses) - HTTP レスポンスをカスタマイズする方法。
- [フレームワークとは？](/learn/why-frameworks) - テンプレートが全体像にどのように適合するか。

## トラブルシューティング
- ミドルウェアにリダイレクトがあるのに、アプリがリダイレクトされない場合は、ミドルウェアに `exit;` 文を追加してください。

## 変更履歴
- v2.0 - 初期リリース。