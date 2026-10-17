# HTML ビューとテンプレート

## 概要

Flight はデフォルトで基本的な HTML テンプレート機能を提供します。テンプレート化は、アプリケーションロジックをプレゼンテーション層から切り離すための非常に効果的な方法です。専用エンジン（Twig、Latte など）は、[AI コーディングツール](/learn/ai) に馴染みがあり制約された構文を与えるため、ビジネスロジックを HTML に書き散らしにくくなります。

## 理解

アプリケーションを構築していると、エンドユーザーに返したい HTML がおそらくあるでしょう。PHP 自体がテンプレート言語ですが、データベース呼び出しや API 呼び出しなどのビジネスロジックを HTML ファイルに簡単に詰め込めてしまい、テストと分離が非常に難しいプロセスになります。データをテンプレートに渡し、テンプレート自身にレンダリングさせることで、コードの分離とユニットテストがはるかに容易になります。テンプレートを使えば、きっと感謝されるでしょう！

## 基本的な使い方

Flight では、`render` をマッピングする（またはビュークラスを登録する）だけで、デフォルトのビューエンジンを差し替えられます。Twig、Latte、Smarty、Blade などについては以下を参照してください。

> **スケルトンのデフォルト:** 公式の [flightphp/skeleton](https://github.com/flightphp/skeleton) は、`app/views/` 配下で **Twig のみ**（`*.twig`）を使用します。コントローラは `$this->app->render('welcome', $data)` を呼び出します（拡張子は任意）。これは新しいプロジェクト向けのアプリケーション側の選択であり、Flight コアの要件ではありません。Latte やその他のエンジンも完全にサポートされています。

### Twig

<span class="badge bg-info">スケルトンのデフォルト</span>

[Twig](https://twig.symfony.com/) は、Symfony や多くの PHP プロジェクトで使われている柔軟で高速かつ安全なテンプレートエンジンです。AI コーディングツールは Twig を特によく理解している傾向があり、デフォルトで出力を自動エスケープするため XSS 対策に役立ちます。

#### インストール

```bash
composer require twig/twig
```

（`composer create-project flightphp/skeleton` を実行した場合、すでに含まれています。）

#### 基本的な設定

デフォルトの PHP レンダラーの代わりに Twig を使用するために、`render` メソッドを上書きします:

```php
// デフォルトの PHP レンダラーの代わりに Twig を使用するために render メソッドを上書きする
Flight::map('render', function(string $template, array $data): void {
	$loader = new \Twig\Loader\FilesystemLoader(Flight::get('flight.views.path'));
	$twig = new \Twig\Environment($loader, [
		// Twig がコンパイル済みテンプレートを保存する場所
		'cache' => __DIR__ . '/../cache/twig',
		'auto_reload' => true,
	]);

	// "welcome" または "welcome.twig" を許可する
	if (substr($template, -5) !== '.twig') {
		$template .= '.twig';
	}

	echo $twig->render($template, $data);
});
```

スケルトンでは、この配線は `app/config/services.php` にあります（共有 Twig 環境、キャッシュパス、`base_url` / CSP nonce などのグローバル）。コントローラから `Engine` を注入して `$app->render()` を呼び出すことを推奨します。そうすることでコードは [AI とテストに優しい](/learn/ai) 状態に保たれます。

#### Flight で Twig を使用する

Twig でレンダリングできるようになったので、次のようにできます:

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

ブラウザで `/Bob` にアクセスすると、出力は次のようになります:

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

#### さらに読む

レイアウトと Twig を併用するより完全な例は、このドキュメントの [awesome プラグイン](/awesome-plugins/twig) セクションに示されています。Tracy バーのレンダリング時メトリクスについては、[Tracy Extensions の Twig パネル](/awesome-plugins/tracy-extensions#twig-panel-optional) を参照してください。

Twig の全機能について詳しく知るには、[公式ドキュメント](https://twig.symfony.com/doc/3.x/) を読んでください。

### Latte

<span class="badge bg-secondary">優れた代替</span>

[Latte](https://latte.nette.org/) は、PHP に似た構文を持つフル機能のエンジンです。Flight アプリにとっても依然として優れた選択肢です。スケルトンは単に、共有デフォルトとして Twig に標準化しているだけです（特に AI ツールがテンプレートを生成する場合に役立ちます）。

#### インストール

```bash
composer require latte/latte
```

#### 基本的な設定

主な考え方は、デフォルトの PHP レンダラーの代わりに Latte を使用するために `render` メソッドを上書きすることです。

```php
// デフォルトの PHP レンダラーの代わりに Latte を使用するために render メソッドを上書きする
Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// Latte がキャッシュを保存する場所
	$latte->setTempDirectory(__DIR__ . '/../cache/');
	
	$finalPath = Flight::get('flight.views.path') . $template;

	$latte->render($finalPath, $data, $block);
});
```

#### Flight で Latte を使用する

Latte でレンダリングできるようになったので、次のようにできます:

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

ブラウザで `/Bob` にアクセスすると、出力は次のようになります:

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

#### さらに読む

レイアウトと Latte を併用するより複雑な例は、このドキュメントの [awesome プラグイン](/awesome-plugins/latte) セクションに示されています。

翻訳や言語機能を含む Latte の全機能について詳しく知るには、[公式ドキュメント](https://latte.nette.org/en/) を読んでください。

### 組み込みビューエンジン

<span class="badge bg-warning">非推奨</span>

> **注:** これは現在もデフォルトの機能であり、技術的にはまだ動作します。

ビューテンプレートを表示するには、テンプレートファイル名と任意のテンプレートデータを指定して `render` メソッドを呼び出します:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

渡したテンプレートデータは自動的にテンプレートに注入され、ローカル変数のように参照できます。テンプレートファイルは単なる PHP ファイルです。`hello.php` テンプレートファイルの内容が次の場合:

```php
Hello, <?= $name ?>!
```

出力は次のようになります:

```text
Hello, Bob!
```

set メソッドを使用してビュー変数を手動で設定することもできます:

```php
Flight::view()->set('name', 'Bob');
```

変数 `name` はすべてのビューで利用できるようになりました。したがって、次のように簡単に書けます:

```php
Flight::render('hello');
```

render メソッドでテンプレート名を指定するときは、`.php` 拡張子を省略できることに注意してください。

デフォルトでは Flight はテンプレートファイル用に `views` ディレクトリを探します。次の設定でテンプレートの代替パスを設定できます:

```php
Flight::set('flight.views.path', '/path/to/views');
```

デフォルトでは、Flight の組み込み `View` は絶対テンプレートパスや、そのディレクトリから抜け出す名前も受け入れます。ほとんどのアプリでは、これを制限すべきです:

```php
Flight::set('flight.views.restrict_to_path', true);
```

これにより、`render()`、`fetch()`、`exists()` が `flight.views.path` 内に保たれます。後方互換性のため、デフォルトではオフです。[セキュリティ](/learn/security#flightviewsrestrict_to_path) を参照してください。

#### レイアウト

Web サイトでは、コンテンツを差し替える単一のレイアウトテンプレートファイルを持つことが一般的です。レイアウトで使用するコンテンツをレンダリングするには、`render` メソッドに任意のパラメータを渡します。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

するとビューには `headerContent` と `bodyContent` という保存済み変数ができます。その後、次のようにしてレイアウトをレンダリングできます:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

テンプレートファイルが次のような場合:

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

出力は次のようになります:
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

ビューで [Smarty](http://www.smarty.net/) テンプレートエンジンを使用する方法は次のとおりです:

```php
// Smarty ライブラリを読み込む
require './Smarty/libs/Smarty.class.php';

// Smarty をビュークラスとして登録する
// 読み込み時に Smarty を設定するコールバック関数も渡す
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// テンプレートデータを割り当てる
Flight::view()->assign('name', 'Bob');

// テンプレートを表示する
Flight::view()->display('hello.tpl');
```

完全を期すために、Flight のデフォルトの render メソッドも上書きする必要があります:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Blade

ビューで [Blade](https://laravel.com/docs/8.x/blade) テンプレートエンジンを使用する方法は次のとおりです:

まず、Composer 経由で BladeOne ライブラリをインストールする必要があります:

```bash
composer require eftec/bladeone
```

次に、Flight で BladeOne をビュークラスとして設定できます:

```php
<?php
// BladeOne ライブラリを読み込む
use eftec\bladeone\BladeOne;

// BladeOne をビュークラスとして登録する
// 読み込み時に BladeOne を設定するコールバック関数も渡す
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// テンプレートデータを割り当てる
Flight::view()->share('name', 'Bob');

// テンプレートを表示する
echo Flight::view()->run('hello', []);
```

完全を期すために、Flight のデフォルトの render メソッドも上書きする必要があります:

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

この例では、hello.blade.php テンプレートファイルは次のようになります:

```php
<?php
Hello, {{ $name }}!
```

出力は次のようになります:

```
Hello, Bob!
```

## 関連項目
- [インストール](/install) - 新しいプロジェクト向けのスケルトンレイアウト（`app/views/*.twig`）。
- [拡張](/learn/extending) - 別のテンプレートエンジンを使用するために `render` メソッドを上書きする方法。
- [ルーティング](/learn/routing) - ルートをコントローラにマッピングし、ビューをレンダリングする方法。
- [レスポンス](/learn/responses) - HTTP レスポンスをカスタマイズする方法。
- [セキュリティ](/learn/security) - 自動エスケープ、XSS、および `flight.views.restrict_to_path`。
- [AI と開発者エクスペリエンス](/learn/ai) - 単一のビューエンジンのデフォルトがコーディングエージェントに役立つ理由。
- [なぜフレームワークなのか？](/learn/why-frameworks) - テンプレートが全体像の中でどのように位置づけられるか。

## トラブルシューティング
- ミドルウェアにリダイレクトがあるのに、アプリがリダイレクトしていないように見える場合は、ミドルウェアに `exit;` ステートメントを追加したことを確認してください。
- Twig がテンプレートを見つけられない場合は、`flight.views.path` と、期待される拡張子でそのパス配下にファイルが存在することを確認してください（スケルトン: `app/views/`）。

## 変更履歴
- ドキュメント – ネイティブ PHP ビュー向けの `flight.views.restrict_to_path` を文書化。
- ドキュメント – Twig を公式スケルトンのデフォルトとして文書化。Latte は第一級の代替として引き続き利用可能。
- v2.0 - 初期リリース。