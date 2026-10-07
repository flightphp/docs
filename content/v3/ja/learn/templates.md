# HTMLビューとテンプレート

## 概要

Flightはデフォルトで基本的なHTMLテンプレート機能を提供します。テンプレートは、アプリケーションのロジックをプレゼンテーション層から切り離すための非常に効果的な方法です。専用エンジン（Twig、Latteなど）を使用すると、[AIコーディングツール](/learn/ai)にとって馴染みのある制約付きの構文が提供され、ビジネスロジックをHTMLに埋め込んでしまう可能性が低くなります。

## 理解

アプリケーションを構築するとき、エンドユーザーに返すHTMLが必要になるでしょう。PHP自体もテンプレート言語ですが、データベース呼び出しやAPI呼び出しなどのビジネスロジックをHTMLファイルに埋め込んでしまい、テストや分離が非常に困難になることが_とても_簡単に起こります。データをテンプレートに渡し、テンプレート自体にレンダリングさせることで、コードの分離と単体テストがはるかに簡単になります。テンプレートを使えば、きっと私たちに感謝することでしょう！

## 基本的な使い方

Flightでは、`render`をマップ（またはビュークラスの登録）するだけで、デフォルトのビューエンジンを別のものに交換できます。Twig、Latte、Smarty、Bladeなどの詳細は下にスクロールしてください。

> **スケルトンのデフォルト:** 公式の[flightphp/skeleton](https://github.com/flightphp/skeleton)は、`app/views/`（`*.twig`）配下で**Twigのみ**を使用します。コントローラーは`$this->app->render('welcome', $data)`を呼び出します（拡張子は省略可能）。これは新規プロジェクトに対するアプリケーション側の選択であり、Flightコアの要件ではありません。Latteや他のエンジンも引き続き完全にサポートされています。

### Twig

<span class="badge bg-info">スケルトンのデフォルト</span>

[Twig](https://twig.symfony.com/)は、Symfonyや多くのPHPプロジェクトで使用されている、柔軟で高速かつ安全なテンプレートエンジンです。AIコーディングツールは特にTwigをよく知っており、またデフォルトで出力を自動エスケープするためXSSの防止に役立ちます。

#### インストール

```bash
composer require twig/twig
```

（`composer create-project flightphp/skeleton`を実行すると、すでに含まれています。）

#### 基本設定

デフォルトのPHPレンダラーの代わりにTwigを使用するように`render`メソッドを上書きします：

```php
// デフォルトのPHPレンダラーの代わりにTwigを使用するようにrenderメソッドを上書き
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// Twigがコンパイルしたテンプレートを保存する場所
		'cache' => __DIR__ . '/../cache/twig',
		'auto_reload' => true,
	]);

	// "welcome"または"welcome.twig"の両方を許可
	if (substr($template, -5) !== '.twig') {
		$template .= '.twig';
	}

	echo $twig->render($template, $data);
});
```

スケルトンでは、この配線は`app/config/services.php`にあります（共有Twig環境、キャッシュパス、`base_url` / CSPナンスなどのグローバル変数）。コードが[AIおよびテストに適したもの](/learn/ai)になるよう、`Engine`を注入してコントローラーから`$app->render()`を呼び出すことをお勧めします。

#### FlightでTwigを使用する

Twigでレンダリングできるようになったので、次のように使用できます：

```html
{# app/views/home.twig #}
<html>
  <head>
	<title>{% if title %}{{ title }} - {% endif %}My App</title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body>
	<h1>Hello, {{ name }}!</h1>
  </body>
</html>
```

```php
// routes.php
Flight::route('/@name', function ($name) {
	Flight::render('home.twig', [
		'title' => 'Home Page',
		'name' => $name
	]);
});
```

ブラウザで`/Bob`にアクセスすると、出力は次のようになります：

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

#### 詳細情報

Twigをレイアウトとともに使用するより完全な例は、このドキュメントの[awesome plugins](/awesome-plugins/twig)セクションにあります。Tracyバーでのレンダリング時間メトリクスについては、[Tracy ExtensionsのTwigパネル](/awesome-plugins/tracy-extensions#twig-panel-optional)を参照してください。

Twigの全機能については、[公式ドキュメント](https://twig.symfony.com/doc/3.x/)をご覧ください。

### Latte

<span class="badge bg-secondary">優れた代替案</span>

[Latte](https://latte.nette.org/)は、PHPに似た構文を持つフル機能のエンジンです。Flightアプリケーションにとって今でも優れた選択肢です。スケルトンでは単に共通のデフォルトとしてTwigを標準化しているだけです（特にAIツールがテンプレートを生成する場合に便利です）。

#### インストール

```bash
composer require latte/latte
```

#### 基本設定

主なアイデアは、デフォルトのPHPレンダラーの代わりにLatteを使用するように`render`メソッドを上書きすることです。

```php
// デフォルトのPHPレンダラーの代わりにlatteを使用するようにrenderメソッドを上書き
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// latteがキャッシュを具体的に保存する場所
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### FlightでLatteを使用する

Latteでレンダリングできるようになったので、次のように使用できます：

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

ブラウザで`/Bob`にアクセスすると、出力は次のようになります：

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

#### 詳細情報

Latteをレイアウトとともに使用するより複雑な例は、このドキュメントの[awesome plugins](/awesome-plugins/latte)セクションにあります。

翻訳や言語機能を含むLatteの全機能については、[公式ドキュメント](https://latte.nette.org/en/)をご覧ください。

### 組み込みビューエンジン

<span class="badge bg-warning">非推奨</span>

> **注:** これは今でもデフォルトの機能であり、技術的にはまだ動作します。

ビューテンプレートを表示するには、テンプレートファイルの名前とオプションのテンプレートデータを指定して`render`メソッドを呼び出します：

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

渡したテンプレートデータは自動的にテンプレートに注入され、ローカル変数のように参照できます。テンプレートファイルは単なるPHPファイルです。`hello.php`テンプレートファイルの内容が次の場合：

```php
Hello, <?= $name ?>!
```

出力は次のようになります：

```text
Hello, Bob!
```

`set`メソッドを使用して、ビュー変数を手動で設定することもできます：

```php
Flight::view()->set('name', 'Bob');
```

`name`変数はすべてのビューで使用できるようになります。したがって、次のようにするだけです：

```php
Flight::render('hello');
```

renderメソッドでテンプレートの名前を指定する際、`.php`拡張子は省略できることに注意してください。

デフォルトでは、Flightはテンプレートファイル用の`views`ディレクトリを探します。次の設定を行うことで、テンプレートの代替パスを設定できます：

```php
Flight::set('flight.views.path', '/path/to/views');
```

デフォルトでは、Flightの組み込み`View`は絶対テンプレートパス、またはそのディレクトリの外に上がる名前も受け入れます。ほとんどのアプリケーションでは、これを制限することをお勧めします：

```php
Flight::set('flight.views.restrict_to_path', true);
```

これにより、`render()`、`fetch()`、`exists()`が`flight.views.path`内に制限されます。後方互換性のため、デフォルトではオフになっています。[セキュリティ](/learn/security#flightviewsrestrict_to_path)を参照してください。

#### レイアウト

ウェブサイトでは、コンテンツを差し替えられる単一のレイアウトテンプレートファイルを持つことが一般的です。レイアウトで使用するコンテンツをレンダリングするには、`render`メソッドにオプションのパラメータを渡します。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

ビューには`headerContent`と`bodyContent`という変数が保存されます。次に、レイアウトをレンダリングします：

```php
Flight::render('layout', ['title' => 'Home Page']);
```

テンプレートファイルが次のようになっている場合：

`header.php`:

```php
<h1><?= $heading ?></h1>
```

`body.php`:

```php
<div><?= $body ?></div>
```

`layout.php`:

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

ビューに[Smarty](http://www.smarty.net/)テンプレートエンジンを使用する方法は次のとおりです：

```php
// Smartyライブラリを読み込む
require './Smarty/libs/Smarty.class.php';

// Smartyをビュークラスとして登録
// また、読み込み時にSmartyを設定するコールバック関数を渡す
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// テンプレートデータを割り当てる
Flight::view()->assign('name', 'Bob');

// テンプレートを表示
Flight::view()->display('hello.tpl');
```

完全を期すために、Flightのデフォルトのrenderメソッドも上書きする必要があります：

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

ビューに[Blade](https://laravel.com/docs/8.x/blade)テンプレートエンジンを使用する方法は次のとおりです：

まず、Composerを通じてBladeOneライブラリをインストールする必要があります：

```bash
composer require eftec/bladeone
```

次に、FlightでBladeOneをビュークラスとして設定できます：

```php
<?php
// BladeOneライブラリを読み込む
use eftec\bladeone\BladeOne;

// BladeOneをビュークラスとして登録
// また、読み込み時にBladeOneを設定するコールバック関数を渡す
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// テンプレートデータを割り当てる
Flight::view()->share('name', 'Bob');

// テンプレートを表示
echo Flight::view()->run('hello', []);
```

完全を期すために、Flightのデフォルトのrenderメソッドも上書きする必要があります：

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

この例では、hello.blade.phpテンプレートファイルは次のようになります：

```php
<?php
Hello, {{ $name }}!
```

出力は次のようになります：

```
Hello, Bob!
```

## 関連情報
- [インストール](/install) - 新規プロジェクト用のスケルトンレイアウト（`app/views/*.twig`）。
- [拡張](/learn/extending) - 別のテンプレートエンジンを使用するために`render`メソッドを上書きする方法。
- [ルーティング](/learn/routing) - ルートをコントローラーにマップしてビューをレンダリングする方法。
- [レスポンス](/learn/responses) - HTTPレスポンスをカスタマイズする方法。
- [セキュリティ](/learn/security) - 自動エスケープ、XSS、`flight.views.restrict_to_path`。
- [AIと開発者体験](/learn/ai) - 単一のビューエンジンのデフォルトがコーディングエージェントに役立つ理由。
- [フレームワークを使う理由](/learn/why-frameworks) - テンプレートが全体像にどのように適合するか。

## トラブルシューティング
- ミドルウェアにリダイレクトがあるのに、アプリがリダイレクトしていないように見える場合は、ミドルウェアに`exit;`ステートメントを追加してください。
- Twigがテンプレートを見つけられない場合は、`flight.views.path`を確認し、そのパス配下に期待される拡張子（スケルトン：`app/views/`）でファイルが存在することを確認してください。

## 変更履歴
- ドキュメント – ネイティブPHPビューのための`flight.views.restrict_to_path`を文書化。
- ドキュメント – Twigが公式スケルトンのデフォルトとして文書化。Latteは引き続き第一級の代替エンジン。
- v2.0 - 初回リリース。