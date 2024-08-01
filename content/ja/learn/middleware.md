# ルートミドルウェア

Flightはルートおよびグループルートのミドルウェアをサポートしています。ミドルウェアは、ルートコールバックの前（または後）に実行される関数です。これは、コード内にAPI認証チェックを追加したり、ユーザーがルートにアクセスする権限を持っていることを検証するのに便利な方法です。

## 基本的なミドルウェア

基本的な例を以下に示します：

```php
// 無名関数のみを指定する場合、ルートコールバックの前に実行されます。
// 「after」ミドルウェア関数はクラスを除いて存在しません（以下を参照）
Flight::route('/path', function() { echo 'Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// これにより、「Middleware first! Here I am!」と表示されます。
```

ミドルウェアについて重要な注意事項がいくつかありますので、使用する前に認識しておく必要があります：
- ミドルウェア関数はルートに追加された順に実行されます。実行は、[Slim Frameworkがこれをどのように処理するのか](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)に似ています。
   - 「before」は追加された順に実行され、「after」は逆の順で実行されます。
- ミドルウェア関数がfalseを返すと、すべての実行が停止され、403 Forbiddenエラーがスローされます。これをよりスムーズに処理したい場合は、`Flight::redirect()`などを使用すると良いでしょう。
- ルートからパラメーターが必要な場合、それらは1つの配列としてミドルウェア関数に渡されます（`function($params) { ... }`または`public function before($params) {}`）。これは、パラメーターをグループ化し、その中のいくつかのグループで、パラメーターが実際に異なる順序で表示される場合があるためです。これにより、位置ではなく名前でアクセスできます。
- ミドルウェアの名前のみを渡すと、[依存性注入コンテナ](dependency-injection-container)によって自動的に実行され、必要なパラメーターでミドルウェアが実行されます。依存性注入コンテナが登録されていない場合は、`__construct()`に`flight\Engine`インスタンスが渡されます。

## ミドルウェアクラス

ミドルウェアはクラスとしても登録できます。"after"機能が必要な場合は、**必ず**クラスを使用する必要があります。

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
Flight::route('/path', function() { echo 'Here I am! '; })->addMiddleware($MyMiddleware); // または ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// これにより、「Middleware first! Here I am! Middleware last!」が表示されます。
```

## ミドルウェアエラーの処理

認証ミドルウェアがあるとして、認証されていない場合にユーザーをログインページにリダイレクトしたいとします。その場合、次のオプションがいくつかあります：

1. ミドルウェア関数からfalseを返すと、Flightは自動的に403 Forbiddenエラーを返しますが、カスタマイズはできません。
1. `Flight::redirect()`を使用してユーザーをログインページにリダイレクトできます。
1. ミドルウェア内でカスタムエラーを作成し、ルートの実行を停止できます。

### 基本的な例

次に、単純なfalseを返す例を示します：
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// trueであるため、すべてが進行し続けます
	}
}
```

### リダイレクトの例

ユーザーをログインページにリダイレクトする例は次のとおりです：
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

### カスタムエラーの例

APIを構築しているため、JSONエラーをスローする必要があるとしましょう。これは以下のように行えます：
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
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## ミドルウェアのグループ化

ルートグループを追加し、そのグループ内のすべてのルートに同じミドルウェアを適用できます。これは、例えばヘッダーのAPIキーをチェックするために、多くのルートをグループ化する必要がある場合に便利です。

```php

// グループメソッドの最後に追加
Flight::group('/api', function() {

	// この「空」に見えるルートは実際には/apiに一致します
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// これは/api/usersに一致します
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// これは/api/users/1234に一致します
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

すべてのルートにグローバルミドルウェアを適用する場合は、次のようにして「空の」グループを追加できます：

```php

// グループメソッドの最後に追加
Flight::group('', function() {

	// これは依然として/usersです
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// そしてこれは依然として/users/1234です
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```  