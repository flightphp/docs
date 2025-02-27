# 拡張

Flightは拡張可能なフレームワークとして設計されています。このフレームワークにはデフォルトのメソッドとコンポーネントのセットが付属していますが、独自のメソッドをマッピングしたり、自分のクラスを登録したり、既存のクラスやメソッドをオーバーライドすることもできます。

DIC（依存性注入コンテナ）を探しているなら、[依存性注入コンテナ](dependency-injection-container) ページをご覧ください。

## メソッドのマッピング

独自のシンプルなカスタムメソッドをマップするには、`map` 関数を使用します：

```php
// あなたのメソッドをマップする
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// あなたのカスタムメソッドを呼び出す
Flight::hello('Bob');
```

シンプルなカスタムメソッドを作成することは可能ですが、PHPで標準関数を作成することをお勧めします。これはIDEでオートコンプリートがあり、読みやすくなります。
上記のコードの同等のものは次のようになります：

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

これは、期待される値を得るためにメソッドに変数を渡す必要があるときにもっと使われます。以下のように`register()`メソッドを使用するのは、設定を渡し、あらかじめ設定されたクラスを呼び出すためのものです。

## クラスの登録

独自のクラスを登録して設定するには、`register` 関数を使用します：

```php
// あなたのクラスを登録する
Flight::register('user', User::class);

// あなたのクラスのインスタンスを取得する
$user = Flight::user();
```

registerメソッドは、クラスのコンストラクタにパラメータを渡すことも可能です。したがって、カスタムクラスをロードするとき、それは事前に初期化されていることになります。
コンストラクタのパラメータは、追加の配列を渡すことで定義できます。
データベース接続をロードする例は次のとおりです：

```php
// コンストラクタパラメータ付きでクラスを登録する
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// あなたのクラスのインスタンスを取得する
// これは定義されたパラメータを持つオブジェクトを作成します
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// そして、もしコード内で後でそれが必要になった場合は、再度同じメソッドを呼び出すだけです
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

追加のコールバックパラメータを渡すと、クラスの構築後に直ちに実行されます。これにより、新しいオブジェクトのために設定手順を実行できます。コールバック関数は1つのパラメータ、新しいオブジェクトのインスタンスを受け取ります。

```php
// コールバックには構築されたオブジェクトが渡されます
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

デフォルトでは、クラスを読み込むたびに共有インスタンスが得られます。
クラスの新しいインスタンスを取得するには、`false`をパラメータとして渡すだけです：

```php
// クラスの共有インスタンス
$shared = Flight::db();

// クラスの新しいインスタンス
$new = Flight::db(false);
```

マッピングされたメソッドは、登録されたクラスよりも優先されることに注意してください。両方を同じ名前で宣言した場合、マッピングされたメソッドのみが呼び出されます。

## ロギング

Flightには組み込みのロギングシステムはありませんが、Flightとともにロギングライブラリを使用するのは非常に簡単です。以下はMonologライブラリを使用した例です：

```php
// index.phpまたはbootstrap.php

// Flightにロガーを登録する
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

登録されたので、アプリケーションで使用することができます：

```php
// あなたのコントローラやルートの中で
Flight::log()->warning('これは警告メッセージです');
```

これは、指定されたログファイルにメッセージを記録します。エラーが発生したときに何かをログに記録したい場合は、`error`メソッドを使用できます：

```php
// あなたのコントローラやルートの中で

Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// あなたのカスタムエラーページを表示する
	include 'errors/500.html';
});
```

また、`before`と`after`メソッドを使用して基本的なAPM（アプリケーションパフォーマンスモニタリング）システムを作成することもできます：

```php
// あなたのブートストラップファイルの中で

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('リクエスト '.Flight::request()->url.' は ' . round($end - $start, 4) . ' 秒かかりました');

	// あなたのリクエストまたはレスポンスヘッダーを追加することもできます
	// それらをログに記録するために（多くのリクエストがあるときはデータが大量になるので注意してください）
	Flight::log()->info('リクエストヘッダー: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('レスポンスヘッダー: ' . json_encode(Flight::response()->headers));
});
```

## フレームワークメソッドのオーバーライド

Flightは、コードを修正することなく、デフォルトの機能を自分のニーズに合わせてオーバーライドすることを可能にします。オーバーライドできるすべてのメソッドを[こちら](/learn/api)で確認できます。

たとえば、FlightがURLをルートに一致させることができない場合、`notFound`メソッドが呼び出され、一般的な`HTTP 404`レスポンスが送信されます。この動作をオーバーライドするには、`map`メソッドを使用します：

```php
Flight::map('notFound', function() {
  // カスタム404ページを表示する
  include 'errors/404.html';
});
```

Flightはフレームワークのコアコンポーネントを置き換えることもできます。
たとえば、デフォルトのRouterクラスを独自のカスタムクラスに置き換えることができます：

```php
// あなたのカスタムクラスを登録する
Flight::register('router', MyRouter::class);

// FlightがRouterインスタンスをロードするとき、あなたのクラスがロードされます
$myrouter = Flight::router();
```

ただし、`map`や`register`のようなフレームワークメソッドはオーバーライドできません。そうしようとするとエラーが発生します。