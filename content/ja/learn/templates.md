# HTML ビューとテンプレート

Flight はデフォルトでいくつかの基本的なテンプレート機能を提供します。

より複雑なテンプレートが必要な場合は、[カスタムビュー](#custom-views) セクションの Smarty と Latte の例を参照してください。

## デフォルトのビューエンジン

ビューテンプレートを表示するには、テンプレートファイルの名前とオプションのテンプレートデータを使用して `render` メソッドを呼び出します:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

渡すテンプレートデータは自動的にテンプレートに注入され、ローカル変数のように参照できます。テンプレートファイルは単純に PHP ファイルです。`hello.php` テンプレートファイルの内容が次のような場合:

```php
Hello, <?= $name ?>!
```

出力は次のようになります:

```
Hello, Bob!
```

また、`set` メソッドを使用してビュー変数を手動で設定することもできます:

```php
Flight::view()->set('name', 'Bob');
```

変数 `name` はこれ以降すべてのビューで使用できます。そのため、単に次のように行うことができます:

```php
Flight::render('hello');
```

`render` メソッドでテンプレートの名前を指定する際に、`.php` 拡張子を省略することができる点に注意してください。

Flight はデフォルトでテンプレートファイルのための `views` ディレクトリを探します。テンプレートの代替パスを設定するには、次の構成を設定してください:

```php
Flight::set('flight.views.path', '/path/to/views');
```

### レイアウト

ウェブサイトには交換可能なコンテンツを持つ単一のレイアウトテンプレートファイルがあることが一般的です。使用するコンテンツをレイアウトにレンダリングするには、`render` メソッドにオプションのパラメータを渡すことができます。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

その後、`headerContent` と `bodyContent` と呼ばれる保存された変数を持つビューがあります。そうすることで、次のようにレイアウトをレンダリングできます:

```php
Flight::render('layout', ['title' => 'ホームページ']);
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
    <title>ホームページ</title>
  </head>
  <body>
    <h1>Hello</h1>
    <div>World</div>
  </body>
</html>
```

## カスタムビューエンジン

Flight では、独自のビュークラスを登録することでデフォルトのビューエンジンを簡単に入れ替えることができます。

### Smarty

ビューに [Smarty](http://www.smarty.net/) テンプレートエンジンを使用する方法は次のとおりです:

```php
// Smarty ライブラリをロードします
require './Smarty/libs/Smarty.class.php';

// ビュークラスとして Smarty を登録します
// また、Smarty のロード時に設定するためのコールバック関数を渡します
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

完全性のために、Flight のデフォルトの render メソッドを上書きする必要があります:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

ビューに [Latte](https://latte.nette.org/) テンプレートエンジンを使用する方法は次のとおりです:

```php
// ビュークラスとして Latte を登録します
// また、Latte のロード時に設定するためのコールバック関数を渡します
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // ここが Latte がテンプレートをキャッシュする場所です
  // Latte の素晴らしいところの1つは、テンプレートを変更すると自動的にキャッシュを更新することです!
  $latte->setTempDirectory(__DIR__ . '/../cache/');

  // Latte にとってビューのルートディレクトリがどこにあるかを教えてください。
  $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// ラップして Flight::render() を正しく使用できるようにします
Flight::map('render', function(string $template, array $data): void {
  // これは $latte_engine->render($template, $data); のようなものです
  echo Flight::view()->render($template, $data);
});
```