# 拡張

## 概要

Flight は拡張可能なフレームワークとして設計されています。フレームワークにはデフォルトのメソッドとコンポーネントのセットが付属していますが、ご自身のメソッドをマップしたり、ご自身のクラスを登録したり、既存のクラスやメソッドをオーバーライドしたりすることが可能です。

## 理解

Flight の機能を拡張する方法は 2 つあります：

1. メソッドのマッピング - アプリケーション内のどこからでも呼び出せるシンプルなカスタムメソッドを作成するために使用されます。これらは、コード内のどこからでも呼び出したいユーティリティ関数に通常使用されます。
2. クラスの登録 - Flight にご自身のクラスを登録するために使用されます。これは、依存関係があるクラスや設定を必要とするクラスに通常使用されます。

プロジェクトのニーズに合わせてデフォルトの動作を変更するために、既存のフレームワークメソッドをオーバーライドすることも可能です。

> DIC（Dependency Injection Container）をお探しの場合、[Dependency Injection Container](/learn/dependency-injection-container) ページに移動してください。

## 基本的な使用方法

### フレームワークメソッドのオーバーライド

Flight は、コードを変更せずにご自身のニーズに合わせてデフォルトの機能をオーバーライドすることを許可します。オーバーライド可能なすべてのメソッドは [以下](#mappable-framework-methods) をご覧ください。

たとえば、Flight が URL をルートにマッチングできない場合、`notFound` メソッドを呼び出して一般的な `HTTP 404` レスポンスを送信します。この動作を `map` メソッドを使用してオーバーライドできます：

```php
Flight::map('notFound', function() {
  // カスタム 404 ページを表示
  include 'errors/404.html';
});
```

Flight はフレームワークのコアコンポーネントを置き換えることも許可します。
たとえば、デフォルトの Router クラスを独自のカスタムクラスに置き換えることができます：

```php
// カスタム Router クラスを作成
class MyRouter extends \flight\net\Router {
	// ここでメソッドをオーバーライド
	// たとえば、GET リクエストのショートカットで
	// pass route 機能を削除
	public function get($pattern, $callback, $alias = '') {
		return parent::get($pattern, $callback, false, $alias);
	}
}

// カスタムクラスを登録
Flight::register('router', MyRouter::class);

// Flight が Router インスタンスをロードするとき、ご自身のクラスがロードされます
$myRouter = Flight::router();
$myRouter->get('/hello', function() {
  echo "Hello World!";
}, 'hello_alias');
```

ただし、`map` や `register` などのフレームワークメソッドはオーバーライドできません。これを試みるとエラーが発生します（リストについては [以下](#mappable-framework-methods) をご覧ください）。

### マッピング可能なフレームワークメソッド

以下はフレームワークの完全なメソッドセットです。コアメソッド（通常の静的メソッド）と拡張可能メソッド（フィルタリングやオーバーライドが可能なマップされたメソッド）で構成されています。

#### コアメソッド

これらのメソッドはフレームワークのコアであり、オーバーライドできません。

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // カスタムフレームワークメソッドを作成。
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // フレームワークメソッドにクラスを登録。
Flight::unregister(string $name) // フレームワークメソッドからクラスを登録解除。
Flight::before(string $name, callable $callback) // フレームワークメソッドの前にフィルタを追加。
Flight::after(string $name, callable $callback) // フレームワークメソッドの後にフィルタを追加。
Flight::path(string $path) // クラスの自動ロードのためのパスを追加。
Flight::get(string $key) // Flight::set() で設定された変数を取得。
Flight::set(string $key, mixed $value) // Flight エンジン内で変数を設定。
Flight::has(string $key) // 変数が設定されているかをチェック。
Flight::clear(array|string $key = []) // 変数をクリア。
Flight::init() // フレームワークをデフォルト設定に初期化。
Flight::app() // アプリケーションオブジェクトインスタンスを取得
Flight::request() // リクエストオブジェクトインスタンスを取得
Flight::response() // レスポンスオブジェクトインスタンスを取得
Flight::router() // ルーターオブジェクトインスタンスを取得
Flight::view() // ビューオブジェクトインスタンスを取得
```

#### 拡張可能メソッド

```php
Flight::start() // フレームワークを開始。
Flight::stop() // フレームワークを停止し、レスポンスを送信。
Flight::halt(int $code = 200, string $message = '') // オプションのステータスコードとメッセージでフレームワークを停止。
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // URL パターンをコールバックにマップ。
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // POST リクエスト URL パターンをコールバックにマップ。
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // PUT リクエスト URL パターンをコールバックにマップ。
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // PATCH リクエスト URL パターンをコールバックにマップ。
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // DELETE リクエスト URL パターンをコールバックにマップ。
Flight::group(string $pattern, callable $callback) // URL のグループを作成、パターンは文字列である必要があります。
Flight::getUrl(string $name, array $params = []) // ルートエイリアスに基づいて URL を生成。
Flight::redirect(string $url, int $code) // 別の URL にリダイレクト。
Flight::download(string $filePath) // ファイルをダウンロード。
Flight::render(string $file, array $data, ?string $key = null) // テンプレートファイルをレンダリング。
Flight::error(Throwable $error) // HTTP 500 レスポンスを送信。
Flight::notFound() // HTTP 404 レスポンスを送信。
Flight::etag(string $id, string $type = 'string') // ETag HTTP キャッシングを実行。
Flight::lastModified(int $time) // 最終更新日時 HTTP キャッシングを実行。
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSON レスポンスを送信。
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSONP レスポンスを送信。
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // JSON レスポンスを送信し、フレームワークを停止。
Flight::onEvent(string $event, callable $callback) // イベントリスナーを登録。
Flight::triggerEvent(string $event, ...$args) // イベントをトリガー。
```

`map` や `register` で追加したカスタムメソッドもフィルタリング可能です。これらのメソッドをフィルタリングする方法の例については、[Filtering Methods](/learn/filtering) ガイドを参照してください。

#### 拡張可能なフレームワーククラス

拡張してご自身のクラスを登録することで、機能のオーバーライドが可能なクラスがいくつかあります。これらのクラスは：

```php
Flight::app() // アプリケーションクラス - flight\Engine クラスを拡張
Flight::request() // リクエストクラス - flight\net\Request クラスを拡張
Flight::response() // レスポンスクラス - flight\net\Response クラスを拡張
Flight::router() // ルータークラス - flight\net\Router クラスを拡張
Flight::view() // ビュークラス - flight\template\View クラスを拡張
Flight::eventDispatcher() // イベントディスパッチャークラス - flight\core\Dispatcher クラスを拡張
```

### カスタムメソッドのマッピング

シンプルなカスタムメソッドをマップするには、`map` 関数を使用します：

```php
// メソッドをマップ
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// カスタムメソッドを呼び出し
Flight::hello('Bob');
```

シンプルなカスタムメソッドを作成することは可能ですが、PHP で標準関数を作成することを推奨します。これにより IDE でオートコンプリートが可能で、読みやすくなります。上記のコードの同等例は：

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

これは、メソッドに変数を渡して期待される値を取得する必要がある場合に使用されます。以下のように `register()` メソッドを使用するのは、設定を渡して事前設定されたクラスを呼び出す場合に適しています。

### カスタムクラスの登録

ご自身のクラスを登録して設定するには、`register` 関数を使用します。`map()` よりも利点は、この関数を呼び出すたびに同じクラスを再利用できることです（`Flight::db()` で同じインスタンスを共有するのに役立ちます）。

```php
// クラスを登録
Flight::register('user', User::class);

// クラスのインスタンスを取得
$user = Flight::user();
```

`register` メソッドは、クラスコンストラクタにパラメータを渡すことも許可します。カスタムクラスをロードすると、事前初期化された状態になります。コンストラクタパラメータは追加の配列を渡すことで定義できます。データベース接続をロードする例：

```php
// コンストラクタパラメータでクラスを登録
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// クラスのインスタンスを取得
// これは定義されたパラメータでオブジェクトを作成します
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// コードの後で必要になった場合、同じメソッドを再度呼び出すだけです
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

追加のコールバックパラメータを渡すと、クラス構築直後に実行されます。これにより、新しいオブジェクトのセットアップ手順を実行できます。コールバック関数は、新しいオブジェクトのインスタンスを 1 つのパラメータとして受け取ります。

```php
// コールバックは構築されたオブジェクトが渡されます
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

デフォルトでは、クラスをロードするたびに共有インスタンスが取得されます。クラスの新しいインスタンスを取得するには、単に `false` をパラメータとして渡します：

```php
// クラスの共有インスタンス
$shared = Flight::db();

// クラスの新しいインスタンス
$new = Flight::db(false);
```

> **注意:** マップされたメソッドは登録されたクラスよりも優先されます。同じ名前で両方を宣言した場合、マップされたメソッドのみが呼び出されます。

### 例

コアに組み込まれていない機能で Flight を拡張する方法の例をいくつか示します。

#### ログ

Flight には組み込みのログシステムはありませんが、Flight でログライブラリを使用するのは非常に簡単です。Monolog ライブラリを使用した例：

```php
// services.php

// Flight にロガーを登録
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

登録したら、アプリケーションで使用できます：

```php
// コントローラーやルート内で
Flight::log()->warning('This is a warning message');
```

これにより、指定したログファイルにメッセージがログされます。エラーが発生したときに何かをログしたい場合、`error` メソッドを使用できます：

```php
// コントローラーやルート内で
Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// カスタムエラーページを表示
	include 'errors/500.html';
});
```

`before` と `after` メソッドを使用して基本的な APM（Application Performance Monitoring）システムを作成することもできます：

```php
// services.php ファイル内で

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Request '.Flight::request()->url.' took ' . round($end - $start, 4) . ' seconds');

	// リクエストやレスポンスヘッダーを追加してログすることも可能
	// （リクエストが多い場合、データ量が多いので注意）
	Flight::log()->info('Request Headers: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Response Headers: ' . json_encode(Flight::response()->headers));
});
```

#### キャッシング

Flight には組み込みのキャッシングシステムはありませんが、Flight でキャッシングライブラリを使用するのは非常に簡単です。[PHP File Cache](/awesome-plugins/php_file_cache) ライブラリを使用した例：

```php
// services.php

// Flight にキャッシュを登録
Flight::register('cache', \flight\Cache::class, [ __DIR__ . '/../cache/' ], function(\flight\Cache $cache) {
    $cache->setDevMode(ENVIRONMENT === 'development');
});
```

登録したら、アプリケーションで使用できます：

```php
// コントローラーやルート内で
$data = Flight::cache()->get('my_cache_key');
if (empty($data)) {
	// データ取得のための処理を実行
	$data = [ 'some' => 'data' ];
	Flight::cache()->set('my_cache_key', $data, 3600); // 1 時間キャッシュ
}
```

#### 簡単な DIC オブジェクトインスタンス化

アプリケーションで DIC（Dependency Injection Container）を使用している場合、Flight を使用してオブジェクトをインスタンス化できます。[Dice](https://github.com/level-2/Dice) ライブラリを使用した例：

```php
// services.php

// 新しいコンテナを作成
$container = new \Dice\Dice;
// 以下のように自身に再割り当てすることを忘れずに！
$container = $container->addRule('PDO', [
	// shared は同じオブジェクトが毎回返されることを意味
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// 任意のオブジェクトを作成するためのマッピング可能メソッドを作成
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// これはコントローラー/ミドルウェアで使用するためのコンテナハンドラを登録
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// コンストラクタで PDO オブジェクトを受け取るサンプルクラスがあると仮定
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// メール送信コード
	}
}

// 最後に、依存注入を使用してオブジェクトを作成
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

かっこいいでしょう？

## 関連項目
- [Dependency Injection Container](/learn/dependency-injection-container) - Flight で DIC を使用する方法。
- [File Cache](/awesome-plugins/php_file_cache) - Flight でキャッシングライブラリを使用する例。

## トラブルシューティング
- マップされたメソッドは登録されたクラスよりも優先されます。同じ名前で両方を宣言した場合、マップされたメソッドのみが呼び出されます。

## 変更履歴
- v2.0 - 初回リリース。