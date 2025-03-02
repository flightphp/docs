# ビュー

Flightは、デフォルトでいくつかの基本的なテンプレーティング機能を提供します。ビューテンプレートを表示するには、`render`メソッドをテンプレートファイルの名前とオプションのテンプレートデータで呼び出します:

```php
Flight::render('hello.php', ['name' => 'Bob']);
```

渡すテンプレートデータは、自動的にテンプレートに注入され、ローカル変数のように参照できます。テンプレートファイルは単純なPHPファイルです。`hello.php`テンプレートファイルの内容が次のような場合:

```php
Hello, <?= $name ?>!
```

出力は次のようになります:

```
Hello, Bob!
```

また、`set`メソッドを使用してビュー変数を手動で設定することもできます:

```php
Flight::view()->set('name', 'Bob');
```

変数`name`は今やすべてのビューで利用可能です。したがって、次のように簡単にできます:

```php
Flight::render('hello');
```

`render`メソッド内でテンプレートの名前を指定する際に、`.php`拡張子を省略することができることに注意してください。

デフォルトでは、Flightはテンプレートファイルのために `views` ディレクトリを参照します。テンプレートの代替パスを設定するためには、次の設定を行います:

```php
Flight::set('flight.views.path', '/path/to/views');
```

## レイアウト

ウェブサイトには、入れ替わるコンテンツを持つ単一のレイアウトテンプレートファイルを持つことが一般的です。レイアウトにレンダリングするコンテンツを渡すには、`render`メソッドにオプションのパラメータを渡すことができます。

```php
Flight::render('header', ['heading' => 'Hello'], 'headerContent');
Flight::render('body', ['body' => 'World'], 'bodyContent');
```

その後、ビューには `headerContent` と `bodyContent` という名前の保存された変数があります。次に、次のようにしてレイアウトをレンダリングできます:

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

Flightを使用すると、独自のビュークラスを登録するだけでデフォルトのビューエンジンを切り替えることができます。ビューに[Smarty](http://www.smarty.net/)テンプレートエンジンを使用する方法は次の通りです:

```php
// Smartyライブラリの読み込み
require './Smarty/libs/Smarty.class.php';

// ビュークラスとしてSmartyを登録
// Smartyをロード時に構成するためのコールバック関数も渡す
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

完全性を期すために、Flightのデフォルトの`render`メソッドもオーバーライドする必要があります:

```php
Flight::map('render', function(string $template, array $data): void {
  Flight::view()->assign($data);
  Flight::view()->display($template);
});
```