# ルートミドルウェア

Flightはルートとグループルートのミドルウェアをサポートします。ミドルウェアは、ルートコールバックの前（または後）に実行される関数です。これは、コードにAPI認証チェックを追加したり、ユーザーがルートにアクセスする権限があるかを検証したりするのに最適な方法です。

## 基本的なミドルウェア

以下は基本的な例です：

```php
// 匿名関数だけを供給した場合、ルートコールバックの前に実行されます。
// 「後」のミドルウェア関数はありません（クラスについては以下を参照）。
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// これは「Middleware first! Here I am!」と出力されます。
```

ミドルウェアに関するいくつか重要な注意点があります：
- ミドルウェア関数は、ルートに追加された順序で実行されます。実行方法は[Slim Frameworkが扱うもの](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)に似ています。
   - Beforeは追加された順序で実行され、Afterは逆順で実行されます。
- ミドルウェア関数がfalseを返す場合、すべての実行が停止され、403 Forbiddenエラーがスローされます。おそらく`Flight::redirect()`などの方法でより優雅に処理したいでしょう。
- ルートのパラメータが必要な場合、それらはミドルウェア関数に単一の配列として渡されます。(`function($params) { ... }` または `public function before($params) {}`)。これを行う理由は、パラメータをグループ化でき、そのグループのいくつかでパラメータの順序が異なり、ミドルウェア関数を壊す可能性があるからです。この方法で、位置ではなく名前でアクセスできます。
- ミドルウェアの名前だけを渡す場合、[dependency injection container](dependency-injection-container)によって自動的に実行され、必要なパラメータでミドルウェアが実行されます。依存注入コンテナが登録されていない場合、`flight\Engine`インスタンスを`__construct()`に渡します。

## ミドルウェアクラス

ミドルウェアはクラスとしても登録できます。「後」の機能が必要な場合、**クラスを使用する必要があります**。

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware first!';
	}

	public function after($params) {
		echo 'Middleware last!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // また ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]); も可能

Flight::start();

// これは「Middleware first! Here I am! Middleware last!」と表示されます。
```

## ミドルウェアエラーの処理

認証ミドルウェアがあり、ユーザーが認証されていない場合にログイン画面にリダイレクトしたいとします。いくつかのオプションがあります：

1. ミドルウェア関数からfalseを返し、Flightが自動的に403 Forbiddenエラーを返すが、カスタマイズはできません。
1. `Flight::redirect()`を使用してユーザーをログイン画面にリダイレクトします。
1. ミドルウェア内でカスタムエラーを作成し、ルートの実行を停止します。

### 基本的な例

以下はシンプルなreturn false; の例です：
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;  // ユーザーが設定されていない場合
		}

		// trueの場合、すべてが続行されます
	}
}
```

### リダイレクト例

以下はユーザーをログイン画面にリダイレクトする例です：
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### カスタムエラー例

APIを構築している場合、JSONエラーをスローする必要があるとします。以下のようにできます：
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// または
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// または
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']));
		}
	}
}
```

## ミドルウェアのグループ化

ルートグループを追加し、そのグループ内のすべてのルートに同じミドルウェアを適用できます。これは、ヘッダーのAPIキーをチェックするためのAuthミドルウェアで一連のルートをグループ化する必要がある場合に便利です。

```php
// groupメソッドの最後に追加
Flight::group('/api', function() {

	// この「空」のルートは /api に一致します
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// これは /api/users に一致します
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// これは /api/users/1234 に一致します
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

すべてのルートにグローバルミドルウェアを適用したい場合、「空」のグループを追加できます：

```php
// groupメソッドの最後に追加
Flight::group('', function() {

	// これはまだ /users に一致します
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// これはまだ /users/1234 に一致します
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // または [ new ApiAuthMiddleware() ]、同じです
```