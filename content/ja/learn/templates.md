# ビュー

Flight はデフォルトでいくつかの基本的なテンプレート機能を提供します。

より複雑なテンプレートのニーズがある場合は、[カスタムビュー](#custom-views)セクションの Smarty と Latte の例を参照してください。

ビューテンプレートを表示するには、`render` メソッドをテンプレートファイルの名前とオプションのテンプレートデータと共に呼び出します:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

渡すテンプレートデータは自動的にテンプレートに注入され、ローカル変数のように参照できます。テンプレートファイルは単純に PHP ファイルです。`hello.php` テンプレートファイルの内容が次のとおりである場合:

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

変数 `name` は今やすべてのビューで利用可能です。したがって、次のように簡単に行うことができます:

```php
Flight::render('hello');
```

`render` メソッドでテンプレートの名前を指定する際に、`.php` 拡張子を省略することができることに注意してください。

Flight はデフォルトでテンプレートファイルを探すために `views` ディレクトリを見に行きます。テンプレートの代替パスを設定するには、次の設定を行います:

```php
Flight::set('flight.views.path', '/path/to/views');
```

## レイアウト

ウェブサイトで単一のレイアウトテンプレートファイルがあり、その内容が交代することは一般的です。レイアウトに使用されるコンテンツをレンダリングするには、`render` メソッドにオプションのパラメータを渡すことができます。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

このようにすると、`headerContent` と `bodyContent` という保存された変数がビューにあります。次に、次のようにしてレイアウトをレンダリングすることができます:

```php
Flight::render('layout', ['title' => 'Home Page']);
```

テンプレートファイルが次のようになっていれば:

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

Flight では、デフォルトのビューエンジンを簡単に入れ替えることができます。それは自分自身のビュークラスを登録することによってです。

### Smarty

ここに、ビューに [Smarty](http://www.smarty.net/) を使用する方法があります:

```php
// Smarty ライブラリをロード
require './Smarty/libs/Smarty.class.php';

// ビュークラスとして Smarty を登録
// また、Smarty のロードを設定するためのコールバック関数を渡します
Flight::register('view', Smarty::class, [], function (Smarty $smarty) {
  $smarty->setTemplateDir('./templates/');
  $smarty->setCompileDir('./templates_c/');
  $smarty->setConfigDir('./config/');
  $smarty->setCacheDir('./cache/');
});

// テンプレートデータを指定
Flight::view()->assign('name', 'Bob');

// テンプレートを表示
Flight::view()->display('hello.tpl');
```

完全性のために、Flight のデフォルトの render メソッドをオーバーライドする必要があります:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```

### Latte

ここに、ビューに [Latte](https://latte.nette.org/) を使用する方法があります:

```php

// ビュークラスとして Latte を登録
// また、Latte のロードを設定するためのコールバック関数を渡します
Flight::register('view', Latte\Engine::class, [], function (Latte\Engine $latte) {
  // これは Latte がテンプレートをキャッシュする場所です
	// Latte の素晴らしいところは、テンプレートを変更すると自動的に
	// キャッシュを更新してくれることです！
	$latte->setTempDirectory(__DIR__ . '/../cache/');

	// Latte に、ビューのルートディレクトリがどこにあるかを教えてください。
	$latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../views/'));
});

// そして、Flight::render() を正しく使用できるようにします
Flight::map('render', function(string $template, array $data): void {
  // これは $latte_engine->render($template, $data); のようなものです
  echo Flight::view()->render($template, $data);
});
```