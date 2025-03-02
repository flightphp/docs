# HTMLビューとテンプレート

Flightは、標準でいくつかの基本的なテンプレーティング機能を提供します。

Flightでは、自分のビュークラスを登録することで、デフォルトのビューエンジンを簡単に切り替えることができます。Smarty、Latte、Bladeなどの使用例を確認するには、下にスクロールしてください！

## 組み込みビューエンジン

ビューテンプレートを表示するには、テンプレートファイルの名前とオプションのテンプレートデータを指定して`render`メソッドを呼び出します：

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

渡されたテンプレートデータは自動的にテンプレートに注入され、ローカル変数のように参照できます。テンプレートファイルは単純なPHPファイルです。`hello.php`テンプレートファイルの内容が以下のようである場合：

```php
Hello, <?= $name ?>!
```

出力は次のようになります：

```
Hello, Bob!
```

`set`メソッドを使用して手動でビュー変数を設定することもできます：

```php
Flight::view()->set('name', 'Bob');
```

変数`name`はすべてのビューで利用可能です。したがって、単純に次のように記述できます：

```php
Flight::render('hello');
```

`render`メソッドでテンプレートの名前を指定する際、`.php`拡張子を省略することができます。

デフォルトでは、Flightはテンプレートファイルのために`views`ディレクトリを探します。以下の設定を行うことで、テンプレートの代替パスを設定できます：

```php
Flight::set('flight.views.path', '/path/to/views');
```

### レイアウト

ウェブサイトには、入れ替え可能なコンテンツを持つ単一のレイアウトテンプレートファイルが一般的です。レイアウトで使用される内容をレンダリングするには、`render`メソッドにオプションのパラメータを渡すことができます。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

これにより、あなたのビューは`headerContent`と`bodyContent`という保存された変数を持つことになります。次のようにしてレイアウトをレンダリングできます：

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

## Smarty

以下は、[Smarty](http://www.smarty.net/)テンプレートエンジンをビューに使用する方法です：

```php
// Smartyライブラリをロード
require './Smarty/libs/Smarty.class.php';

// Smartyをビュークラスとして登録
// また、Smartyをロード時に設定するためのコールバック関数も渡します
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// テンプレートデータを割り当て
Flight::view()->assign('name', 'Bob');

// テンプレートを表示
Flight::view()->display('hello.tpl');
```

完全を期すために、Flightのデフォルトのrenderメソッドをオーバーライドすることも推奨します：

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

以下は、[Latte](https://latte.nette.org/)テンプレートエンジンをビューに使用する方法です：

```php

// Latteをビュークラスとして登録
// また、Latteをロード時に設定するためのコールバック関数も渡します
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // ここがLatteがテンプレートをキャッシュして速度を向上させる場所です
	// Latteの素晴らしい点は、テンプレートを変更すると自動的にキャッシュを更新することです！
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// ビューのルートディレクトリの場所をLatteに教えます
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Flight::render()を正しく使用できるようにラップします
Flight::map('render', function(string $template, array $data): void {
  // これは$latte_engine->render($template, $data)のようなものです
  echo Flight::view()->render($template, $data);
});
```

## Blade

以下は、[Blade](https://laravel.com/docs/8.x/blade)テンプレートエンジンをビューに使用する方法です：

まず、Composerを介してBladeOneライブラリをインストールする必要があります：

```bash
composer require eftec/bladeone
```

その後、FlightでBladeOneをビュークラスとして設定できます：

```php
<?php
// BladeOneライブラリをロード
use eftec\bladeone\BladeOne;

// BladeOneをビュークラスとして登録
// また、BladeOneをロード時に設定するためのコールバック関数も渡します
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// テンプレートデータを割り当て
Flight::view()->share('name', 'Bob');

// テンプレートを表示
echo Flight::view()->run('hello', []);
```

完全を期すために、Flightのデフォルトのrenderメソッドをオーバーライドすることも推奨します：

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

これらのステップに従うことで、BladeテンプレートエンジンをFlightに統合し、ビューをレンダリングするために使用できます。