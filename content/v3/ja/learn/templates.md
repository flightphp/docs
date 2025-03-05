# HTML ビューとテンプレート

Flight はデフォルトで基本的なテンプレーティング機能を提供します。

Flight を使用すると、独自のビュークラスを登録するだけでデフォルトのビューエンジンを切り替えることができます。Smarty、Latte、Blade などの使用例を以下で確認してください！

## 組み込みビューエンジン

ビュー テンプレートを表示するには、テンプレートファイルの名前とオプションのテンプレートデータを使って `render` メソッドを呼び出します：

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

渡されたテンプレートデータは自動的にテンプレートに注入され、ローカル変数のように参照できます。テンプレートファイルは単純な PHP ファイルです。`hello.php` テンプレートファイルの内容が次のようである場合：

```php
Hello, <?= $name ?>!
```

出力は次のようになります：

```text
Hello, Bob!
```

また、set メソッドを使用してビュー変数を手動で設定することもできます：

```php
Flight::view()->set('name', 'Bob');
```

変数 `name` はすべてのビューで利用可能になりました。ですので、単純に次のようにできます：

```php
Flight::render('hello');
```

render メソッドでテンプレートの名前を指定する際には、`.php` 拡張子を省略することもできます。

デフォルトでは、Flight はテンプレートファイル用に `views` ディレクトリを探します。次の設定を行うことで、テンプレート用の別のパスを設定できます：

```php
Flight::set('flight.views.path', '/path/to/views');
```

### レイアウト

ウェブサイトには、入れ替え可能なコンテンツを持つ単一のレイアウトテンプレートファイルが一般的です。レイアウトで使用するコンテンツをレンダリングするには、`render` メソッドにオプションのパラメータを渡すことができます。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

これにより、`headerContent` と `bodyContent` という名前の保存された変数を持つことができます。そして、次のようにしてレイアウトをレンダリングできます：

```php
Flight::render('layout', ['title' => 'Home Page']);
```

テンプレートファイルが次のようである場合：

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

ビュー用の [Smarty](http://www.smarty.net/) テンプレートエンジンを使用する方法は以下の通りです：

```php
// Smarty ライブラリを読み込みます
require './Smarty/libs/Smarty.class.php';

// Smarty をビュークラスとして登録します
// Smarty をロード時に設定するためのコールバック関数も渡します
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// テンプレートデータを割り当てます
Flight::view()->assign('name', 'Bob');

// テンプレートを表示します
Flight::view()->display('hello.tpl');
```

完全性のために、Flight のデフォルトの render メソッドをオーバーライドする必要があります：

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

## Latte

ビュー用の [Latte](https://latte.nette.org/) テンプレートエンジンを使用する方法は以下の通りです：

```php
// Latte をビュークラスとして登録します
// Latte をロード時に設定するためのコールバック関数も渡します
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // ここが Latte がテンプレートをキャッシュして速度を向上させる場所です
	// Latte の一つの素晴らしい点は、テンプレートに変更を加えると自動的にキャッシュを更新することです！
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// ビューのルートディレクトリがどこになるかを Latte に教えます
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// Flight::render() を正しく使用できるようにラップします
Flight::map('render', function(string $template, array $data): void {
  // これは $latte_engine->render($template, $data)のようなものです
  echo Flight::view()->render($template, $data);
});
```

## Blade

ビュー用の [Blade](https://laravel.com/docs/8.x/blade) テンプレートエンジンを使用する方法は以下の通りです：

まず、Composer を使用して BladeOne ライブラリをインストールする必要があります：

```bash
composer require eftec/bladeone
```

次に、Flight で BladeOne をビュークラスとして設定できます：

```php
<?php
// BladeOne ライブラリを読み込みます
use eftec\bladeone\BladeOne;

// BladeOne をビュークラスとして登録します
// BladeOne をロード時に設定するためのコールバック関数も渡します
Flight::register('view', BladeOne::class, [], function (BladeOne $blade) {
  $views = __DIR__ . '/../views';
  $cache = __DIR__ . '/../cache';

  $blade->setPath($views);
  $blade->setCompiledPath($cache);
});

// テンプレートデータを割り当てます
Flight::view()->share('name', 'Bob');

// テンプレートを表示します
echo Flight::view()->run('hello', []);
```

完全性のために、Flight のデフォルトの render メソッドもオーバーライドする必要があります：

```php
<?php
Flight::map('render', function(string $template, array $data): void {
  echo Flight::view()->run($template, $data);
});
```

この例では、hello.blade.php テンプレートファイルは次のようになります：

```php
<?php
Hello, {{ $name }}!
```

出力は次のようになります：

```
Hello, Bob!
```

これらの手順に従うことで、Blade テンプレートエンジンを Flight に統合し、ビューをレンダリングすることができます。