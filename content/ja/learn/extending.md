# 拡張

Flightは拡張可能なフレームワークとして設計されています。フレームワークにはデフォルトのメソッドやコンポーネントが付属していますが、独自のメソッドをマップしたり、独自のクラスを登録したり、既存のクラスやメソッドをオーバーライドすることができます。

DIC（Dependency Injection Container）をお探しの場合は、[Dependency Injection Container](dependency-injection-container) ページをご覧ください。

## メソッドのマッピング

独自のシンプルなカスタムメソッドをマップするには、`map` 関数を使用します：

```php
// メソッドをマップ
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// カスタムメソッドを呼び出す
Flight::hello('Bob');
```

シンプルなカスタムメソッドを作成することは可能ですが、PHPでは標準の関数を作成することを推奨します。これにはIDEでの自動補完があり、読みやすくなります。
上記のコードの相当するものは次の通りです：

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

これは、メソッドに変数を渡して予想される値を取得する必要がある場合により使用されます。以下のように `register()` メソッドを使用すると、構成を渡してから事前に構成されたクラスを呼び出すためにより使用されます。

## クラスの登録

独自のクラスを登録して構成するには、`register` 関数を使用します：

```php
// クラスを登録
Flight::register('user', User::class);

// クラスのインスタンスを取得する
$user = Flight::user();
```

`register` メソッドでは、クラスのコンストラクタにパラメータを渡すこともできます。
したがって、カスタムクラスをロードするとき、事前に初期化された状態で取得されます。
追加の配列を渡すことでコンストラクタパラメータを定義することができます。
以下はデータベース接続をロードする例です：

```php
// コンストラクタパラメータを持つクラスを登録
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// クラスのインスタンスを取得する
// これにより、指定されたパラメータでオブジェクトが作成されます
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// 後でコード内で必要になった場合は、同じメソッドを再度呼び出すだけです
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

追加のコールバックパラメータを渡すと、クラスの構築直後に即座に実行されます。
これにより、新しいオブジェクトのセットアップ手順を実行できます。
コールバック関数は、新しいオブジェクトのインスタンスを表すパラメータを1つ受け取ります。

```php
// 構築されたオブジェクトがコールバックに渡されます
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

デフォルトでは、クラスをロードするたびに共有インスタンスが取得されます。
クラスの新しいインスタンスを取得するには、単にパラメータとして `false` を渡します：

```php
// クラスの共有インスタンス
$shared = Flight::db();

// クラスの新しいインスタンス
$new = Flight::db(false);
```

マップされたメソッドはクラスの登録よりも優先されます。同じ名前で両方を宣言する場合、マップされたメソッドのみが呼び出されます。

## フレームワークメソッドのオーバーライド

Flightを使用すると、コードを変更せずに独自のニーズに合わせてデフォルトの機能をオーバーライドすることができます。
オーバーライドできるすべてのメソッドを確認するには、[こちら](/learn/api) をご覧ください。

たとえば、FlightがURLをルートに一致させられない場合、`notFound` メソッドが呼び出され、一般的な `HTTP 404` 応答が送信されます。この動作をオーバーライドするには、`map` メソッドを使用します：

```php
Flight::map('notFound', function() {
  // カスタム404ページを表示
  include 'errors/404.html';
});
```

Flightは、フレームワークのコアコンポーネントを置換することもできます。
たとえば、デフォルトのルータークラスを独自のカスタムクラスで置き換えることができます：

```php
// カスタムクラスを登録
Flight::register('router', MyRouter::class);

// FlightがRouterインスタンスをロードするとき、あなたのクラスがロードされます
$myrouter = Flight::router();
```

ただし、`map` や `register` のようなフレームワークメソッドはオーバーライドできません。これをしようとするとエラーが発生します。