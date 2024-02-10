# ビュー

Flight はデフォルトでいくつかの基本的なテンプレート機能を提供します。

より複雑なテンプレートが必要な場合は、[カスタムビュー](#custom-views) セクションの Smarty および Latte の例を参照してください。

ビューテンプレートを表示するには、 `render` メソッドを呼び出して、テンプレートファイルの名前とオプションのテンプレートデータを指定します。

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

渡すテンプレートデータは自動的にテンプレートに挿入され、ローカル変数のように参照することができます。テンプレートファイルは単純に PHP ファイルです。 `hello.php` テンプレートファイルの内容が次のような場合:

```php
Hello, <?= $name ?>!
```

出力は次のようになります:

```
Hello, Bob!
```

`set` メソッドを使用してビュー変数を手動で設定することもできます:

```php
Flight::view()->set('name', 'Bob');
```

変数 `name` はこれ以降すべてのビューで使用可能になります。したがって、次のように単に行うことができます:

```php
Flight::render('hello');
```

`render` メソッドの中でテンプレートの名前を指定する際に、`.php` 拡張子を省略することができることに注意してください。

Flight はデフォルトでテンプレートファイルのために `views` ディレクトリを探します。テンプレートの代替パスを設定するには、次の設定を行います:

```php
Flight::set('flight.views.path', '/path/to/views');
```

## レイアウト

ウェブサイトでは、入れ替わるコンテンツを持つ単一のレイアウトテンプレートファイルを持つことが一般的です。 レイアウトに使用されるコンテンツをレンダリングするには、`render` メソッドにオプションのパラメータを渡します。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

これにより、ビューに `headerContent` と `bodyContent` という保存された変数があります。その後、次のようにしてレイアウトをレンダリングできます:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

テンプレートファイルが次のようになっている場合:

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

## カスタムビュー

Flight では、独自のビュークラスを登録することで、デフォルトのビューエンジンを簡単に切り替えることができます。

### Smarty

次のように、[Smarty](http://www.smarty.net/) テンプレートエンジンをビューに使用します:

```php
// Smarty ライブラリの読み込み
require './Smarty/libs/Smarty.class.php';

// ビュークラスとして Smarty を登録
// また、Smarty をロード時に設定するためのコールバック関数も渡します
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// テンプレートデータの割り当て
Flight::view()->assign('name', 'Bob');

// テンプレートを表示
Flight::view()->display('hello.tpl');
```

完全性のために、Flight のデフォルトのレンダリングメソッドもオーバーライドする必要があります:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

次のように、[Latte](https://latte.nette.org/) テンプレートエンジンをビューに使用します:

```php

// ビュークラスとして Latte を登録
// また、Latte をロード時に設定するためのコールバック関数も渡します
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // ここは Latte がテンプレートを高速化するためにキャッシュする場所です
	// Latte の素晴らしい点の1つは、テンプレートを変更すると自動的にキャッシュを更新することです!
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Latte にビューのルートディレクトリがある場所を伝えます
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// そして、Flight::render() を正しく使用できるようにラップします
Flight::map('render', function(string $template, array $data): void {
  // これは $latte_engine->render($template, $data); と同等です
  echo Flight::view()->render($template, $data);
});
```