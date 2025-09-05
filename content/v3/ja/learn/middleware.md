# ルートミドルウェア

Flightはルートとグループルートのミドルウェアをサポートします。ミドルウェアはルートコールバックの前（または後）に実行される関数です。これは、コードにAPI認証チェックを追加したり、ユーザーがルートにアクセスする権限を持っていることを検証したりするのに最適な方法です。

## 基本的なミドルウェア

以下は基本的な例です：

```php
// 匿名の関数だけを供給した場合、ルートコールバックの前に実行されます。
// 「after」のミドルウェア関数はありません。クラスを使用した場合のみ利用可能です（以下を参照）。
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// これは「Middleware first! Here I am!」を出力します。
```

ミドルウェアに関するいくつかの重要な注意点があります。使用する前に把握しておくべきです：
- ミドルウェア関数は、ルートに追加された順序で実行されます。実行方法は[Slim Framework](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)が扱うものと似ています。
   - Beforeは追加された順序で実行され、Afterは逆順で実行されます。
- ミドルウェア関数がfalseを返した場合、すべての実行が停止され、403 Forbiddenエラーがスローされます。おそらくFlight::redirect()などの方法でより柔軟に処理したいでしょう。
- ルートのパラメータが必要な場合、それらはミドルウェア関数に単一の配列として渡されます。（`function($params) { ... }` または `public function before($params) {}`）。これを行う理由は、パラメータをグループ化し、そのグループ内でパラメータの順序が変わる可能性があるため、ミドルウェア関数が壊れないようにするためです。この方法で、位置ではなく名前でアクセスできます。
- ミドルウェアの名前だけを渡した場合、[dependency injection container](dependency-injection-container)によって自動的に実行され、必要なパラメータでミドルウェアが実行されます。依存注入コンテナが登録されていない場合、`flight\Engine`インスタンスが`__construct()`に渡されます。

## ミドルウェアクラス

ミドルウェアはクラスとして登録することもできます。「after」の機能が必要な場合、**クラスを使用しなければなりません**。

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
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // または ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// これは「Middleware first! Here I am! Middleware last!」を表示します。
```

## ミドルウェアエラーの処理

認証ミドルウェアがあり、ユーザーが認証されていない場合にログイン画面にリダイレクトしたいとします。いくつかのオプションがあります：

1. ミドルウェア関数からfalseを返し、Flightが自動的に403 Forbiddenエラーを返す方法ですが、カスタマイズできません。
1. `Flight::redirect()`を使用してユーザーをログイン画面にリダイレクトします。
1. ミドルウェア内でカスタムエラーを作成し、ルートの実行を停止します。

### 基本的な例

以下はシンプルなreturn false; の例です：
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
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

APIを構築していて、JSONエラーをスローする必要がある場合、以下のようにできます：
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

## グループ化されたミドルウェア

ルートグループを追加し、そのグループ内のすべてのルートに同じミドルウェアを適用できます。これは、ヘッダーのAPIキーをチェックするAuthミドルウェアで一連のルートをグループ化する必要がある場合に便利です。

```php
// groupメソッドの最後に追加
Flight::group('/api', function() {

	// これは/apiに一致します
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// これは/api/usersに一致します
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// これは/api/users/1234に一致します
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

すべてのルートにグローバルミドルウェアを適用したい場合、空のグループを追加できます：

```php
// groupメソッドの最後に追加
Flight::group('', function() {

	// これはまだ/usersです
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// これはまだ/users/1234です
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // または [ new ApiAuthMiddleware() ]、同じです
```