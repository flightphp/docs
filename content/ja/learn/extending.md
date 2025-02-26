# 拡張

Flightは拡張可能なフレームワークとして設計されています。このフレームワークにはデフォルトのメソッドやコンポーネントのセットが付属していますが、独自のメソッドをマップしたり、独自のクラスを登録したり、既存のクラスやメソッドをオーバーライドすることも可能です。

DIC（依存性注入コンテナ）を探している場合は、[依存性注入コンテナ](dependency-injection-container) ページに移動してください。

## メソッドのマッピング

独自のシンプルなカスタムメソッドをマップするには、`map`関数を使用します：

```php
// メソッドをマップします
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// カスタムメソッドを呼び出します
Flight::hello('Bob');
```

シンプルなカスタムメソッドを作成することは可能ですが、標準的な関数をPHPで作成することをお勧めします。これにはIDEでの自動補完があり、読みやすくなります。
上記のコードの等価なものは次のようになります：

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

これは、期待される値を得るために変数をメソッドに渡す必要がある場合に使用されます。以下のように`register()`メソッドを使用するのは、構成を渡して事前に構成されたクラスを呼び出すためです。

## クラスの登録

独自のクラスを登録して構成するには、`register`関数を使用します：

```php
// クラスを登録します
Flight::register('user', User::class);

// クラスのインスタンスを取得します
$user = Flight::user();
```

registerメソッドでは、クラスのコンストラクタにパラメータを渡すこともできます。したがって、カスタムクラスをロードすると、事前に初期化された状態になります。追加の配列を渡すことでコンストラクタのパラメータを定義できます。
データベース接続をロードする例を示します：

```php
// コンストラクタパラメータ付きでクラスを登録します
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// クラスのインスタンスを取得します
// これは定義されたパラメータでオブジェクトを作成します
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// そして、コードの後で必要な場合は、再度同じメソッドを呼び出します
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

追加のコールバックパラメータを渡すと、クラスの構築後すぐに実行されます。これにより、新しいオブジェクトのセットアップ手続きを行うことができます。コールバック関数は、新しいオブジェクトのインスタンスを1つのパラメータとして受け取ります。

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

デフォルトでは、クラスをロードするたびに共有インスタンスが取得されます。クラスの新しいインスタンスを取得するには、単に`false`をパラメータとして渡します：

```php
// クラスの共有インスタンス
$shared = Flight::db();

// クラスの新しいインスタンス
$new = Flight::db(false);
```

マッピングされたメソッドは登録されたクラスよりも優先されることに注意してください。両方を同じ名前で宣言すると、マッピングされたメソッドのみが呼び出されます。

## ロギング

Flightには標準のロギングシステムがありませんが、Flightと一緒にロギングライブラリを使用するのは非常に簡単です。以下は、Monologライブラリを使用した例です：

```php
// index.phpまたはbootstrap.php

// Flightにロガーを登録します
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

登録されたので、アプリケーション内で使用できます：

```php
// コントローラーまたはルート内で
Flight::log()->warning('これは警告メッセージです');
```

これは、指定したログファイルにメッセージを記録します。エラーが発生したときに何かをログに記録したい場合は、`error`メソッドを使用できます：

```php
// コントローラーまたはルート内で

Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// カスタムエラーページを表示します
	include 'errors/500.html';
});
```

また、`before`および`after`メソッドを使用して基本的なAPM（アプリケーションパフォーマンスモニタリング）システムを作成することもできます：

```php
// ブートストラップファイル内で

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('リクエスト '.Flight::request()->url.' は ' . round($end - $start, 4) . ' 秒かかりました');

	// リクエストまたはレスポンスヘッダーをログに追加することもできます
	// もしたくさんのリクエストがあれば、たくさんのデータになるため注意してください
	Flight::log()->info('リクエストヘッダー: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('レスポンスヘッダー: ' . json_encode(Flight::response()->headers));
});
```

## フレームワークメソッドのオーバーライド

Flightは、そのデフォルトの機能を変更することなく、独自のニーズに合わせてオーバーライドすることを許可します。オーバーライドできるすべてのメソッドは[こちら](learn/api)で確認できます。

たとえば、FlightがURLをルートに一致させられない場合、`notFound`メソッドが呼び出され、一般的な`HTTP 404`レスポンスが送信されます。この動作は、`map`メソッドを使用してオーバーライドできます：

```php
Flight::map('notFound', function() {
  // カスタム404ページを表示します
  include 'errors/404.html';
});
```

Flightはまた、フレームワークのコアコンポーネントを置き換えることもできます。たとえば、デフォルトのRouterクラスを独自のカスタムクラスに置き換えることができます：

```php
// カスタムクラスを登録します
Flight::register('router', MyRouter::class);

// FlightがRouterインスタンスをロードする際に、あなたのクラスがロードされます
$myrouter = Flight::router();
```

ただし、`map`や`register`のようなフレームワークメソッドはオーバーライドできません。試みるとエラーが発生します。