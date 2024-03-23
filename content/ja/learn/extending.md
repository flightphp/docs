# 拡張

Flight は拡張可能なフレームワークとして設計されています。このフレームワークにはデフォルトのメソッドやコンポーネントが付属していますが、独自のメソッドをマップしたり、独自のクラスを登録したり、既存のクラスやメソッドを上書きしたりすることが可能です。

DIC（Dependency Injection Container）をお探しの場合は、[Dependency Injection Container](dependency-injection-container) ページをご覧ください。

## メソッドのマッピング

独自のシンプルなカスタムメソッドをマッピングするには、`map` 関数を使用します：

```php
// メソッドをマップ
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// カスタムメソッドを呼び出す
Flight::hello('Bob');
```

これは、メソッドに変数を渡して予期される値を取得する必要がある場合により使用されます。以下のように `register()` メソッドを使用することは、構成を渡し、それから事前に構成されたクラスを呼び出すためのものです。

## クラスの登録

独自のクラスを登録して構成するには、`register` 関数を使用します：

```php
// クラスを登録
Flight::register('user', User::class);

// クラスのインスタンスを取得する
$user = Flight::user();
```

登録メソッドでは、クラスのコンストラクタにパラメータを渡すこともできます。したがって、カスタムクラスを読み込むときに事前に初期化されます。追加の配列を渡すことでコンストラクタパラメータを定義できます。以下はデータベース接続の読み込み例です：

```php
// コンストラクタパラメータ付きのクラスを登録
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// クラスのインスタンスを取得する
// これにより、定義されたパラメータを持つオブジェクトが作成されます
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// そして後でそれが必要な場合、同じメソッドを再度呼び出すだけです
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

追加のコールバックパラメータを渡すと、クラス構築後すぐにそれが実行されます。これにより、新しいオブジェクトのセットアップ手順を実行できます。コールバック関数は、新しいオブジェクトのインスタンスを表すパラメーターを1つ受け取ります。

```php
// 構築されたオブジェクトが渡されます
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

デフォルトでは、クラスをロードするたびに共有インスタンスが取得されます。クラスの新しいインスタンスを取得するには、単純にパラメータとして `false` を渡します：

```php
// クラスの共有インスタンス
$shared = Flight::db();

// クラスの新しいインスタンス
$new = Flight::db(false);
```

マップされたメソッドは登録されたクラスよりも優先されます。同じ名前で両方を宣言すると、マップされたメソッドのみが呼び出されます。

## フレームワークメソッドの上書き

Flight では、コードの変更をせずに独自のニーズに合わせてデフォルトの機能を上書きすることができます。

例えば、Flight が URL をルートにマッチングできない場合、`notFound` メソッドが呼び出され、一般的な `HTTP 404` 応答が送信されます。これを上書きするには、`map` メソッドを使用します：

```php
Flight::map('notFound', function() {
  // カスタム 404 ページを表示
  include 'errors/404.html';
});
```

Flight はまた、フレームワークのコアコンポーネントを置き換えることも可能です。例えば、デフォルトのルータークラスを独自のカスタムクラスで置き換えることができます：

```php
// カスタムクラスを登録
Flight::register('router', MyRouter::class);

// Flight がルーターインスタンスをロードするとき、あなたのクラスがロードされます
$myrouter = Flight::router();
```

ただし、`map` や `register` のようなフレームワークメソッドは上書きできません。それを試みるとエラーが発生します。