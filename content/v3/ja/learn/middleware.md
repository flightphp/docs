# ミドルウェア

## 概要

Flight はルートおよびグループルートのミドルウェアをサポートします。ミドルウェアは、アプリケーションの一部で、ルートコールバックの前（または後）にコードが実行される場所です。これは、コードに API 認証チェックを追加する優れた方法です。また、ユーザーがルートにアクセスする権限があるかを検証することもできます。

## 理解

ミドルウェアはアプリを大幅に簡素化できます。複雑な抽象クラス継承やメソッドオーバーライドの代わりに、ミドルウェアを使用することで、カスタムのアプリロジックをルートに割り当ててルートを制御できます。ミドルウェアはサンドイッチのようなものだと考えられます。外側にパンがあり、その中にレタス、トマト、肉、チーズなどの層があります。そして、各リクエストがサンドイッチを一口かじるようなもので、外側の層から食べてコアに向かっていくイメージです。

ミドルウェアの動作の視覚的な例を以下に示します。その後、この機能の実践的な例を示します。

```text
ユーザー リクエストが URL /api に到達 ----> 
	Middleware->before() が実行 ----->
		/api にアタッチされたコールバック/メソッドが実行され、レスポンスが生成 ------>
	Middleware->after() が実行 ----->
ユーザーがサーバーからレスポンスを受信
```

そして、実践的な例はこちらです：

```text
ユーザーが URL /dashboard に移動
	LoggedInMiddleware->before() が実行
		before() が有効なログインモッションをチェック
			有効な場合、何もしないで実行を続行
			無効な場合、ユーザーを /login にリダイレクト
				/api にアタッチされたコールバック/メソッドが実行され、レスポンスが生成
	LoggedInMiddleware->after() に何も定義されていないため、実行を続行
ユーザーがサーバーからダッシュボードの HTML を受信
```

### 実行順序

ミドルウェア関数は、ルートに追加された順序で実行されます。この実行は、[Slim Framework がこれを扱う方法](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work) に似ています。

`before()` メソッドは追加された順序で実行され、`after()` メソッドは逆順で実行されます。

例: Middleware1->before()、Middleware2->before()、Middleware2->after()、Middleware1->after()。

## 基本的な使用方法

ミドルウェアは、匿名関数やクラス（推奨）を含む任意のコールバックメソッドとして使用できます。

### 匿名関数

簡単な例を以下に示します：

```php
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// これは "Middleware first! Here I am!" を出力します
```

> **注意:** 匿名関数を使用する場合、解釈されるのは `before()` メソッドのみです。匿名クラスで `after()` 動作を**定義できません**。

### クラスの使用

ミドルウェアはクラスとして登録できます（推奨されます）。「after」機能が必要な場合は、**クラスを使用する必要があります**。

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
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); 
// また ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]) も可能です

Flight::start();

// これは "Middleware first! Here I am! Middleware last!" を表示します
```

ミドルウェアのクラス名のみを定義し、クラスをインスタンス化することもできます。

```php
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware(MyMiddleware::class); 
```

> **注意:** ミドルウェアの名前のみを渡す場合、[依存性注入コンテナ](dependency-injection-container) によって自動的に実行され、ミドルウェアは必要なパラメータで実行されます。依存性注入コンテナが登録されていない場合、デフォルトで `__construct(Engine $app)` に `flight\Engine` インスタンスが渡されます。

### パラメータ付きルートの使用

ルートからパラメータが必要な場合、それらはミドルウェア関数に単一の配列として渡されます。（`function($params) { ... }` または `public function before($params) { ... }`）。その理由は、パラメータをグループ化し、一部のグループでパラメータの順序が異なり、誤ったパラメータを参照してミドルウェア関数を壊す可能性があるためです。この方法では、位置ではなく名前でアクセスできます。

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];

		// jobId は渡される場合とされない場合があります
		$jobId = $params['jobId'] ?? 0;

		// job ID がない場合、何も検索する必要がないかもしれません
		if($jobId === 0) {
			return;
		}

		// データベースで何らかの検索を実行
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {

	// 下記のグループも親のミドルウェアを受け取ります
	// ただし、パラメータはミドルウェアに単一の配列として渡されます
	$router->group('/job/@jobId', function(Router $router) {
		$router->get('', [ JobController::class, 'view' ]);
		$router->put('', [ JobController::class, 'update' ]);
		$router->delete('', [ JobController::class, 'delete' ]);
		// さらにルート...
	});
}, [ RouteSecurityMiddleware::class ]);
```

### ミドルウェア付きのルートグループ化

ルートグループを追加し、そのグループ内のすべてのルートに同じミドルウェアを適用できます。これは、ヘッダーの API キーをチェックする Auth ミドルウェアなどでルートをグループ化する必要がある場合に便利です。

```php

// グループメソッドの最後に追加
Flight::group('/api', function() {

	// この「空」のルートは実際には /api に一致します
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// これは /api/users に一致します
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// これは /api/users/1234 に一致します
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

すべてのルートにグローバルなミドルウェアを適用したい場合、「空」のグループを追加できます：

```php

// グループメソッドの最後に追加
Flight::group('', function() {

	// これは依然として /users です
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// これは依然として /users/1234 です
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // または [ new ApiAuthMiddleware() ]、同じです
```

### 一般的な使用例

#### API キー検証
`/api` ルートを保護するために API キーが正しいかを検証したい場合、ミドルウェアで簡単に処理できます。

```php
use flight\Engine;

class ApiMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}
	
	public function before(array $params) {
		$authorizationHeader = $this->app->request()->getHeader('Authorization');
		$apiKey = str_replace('Bearer ', '', $authorizationHeader);

		// データベースで API キーを検索
		$apiKeyHash = hash('sha256', $apiKey);
		$hasValidApiKey = !!$this->db()->fetchField("SELECT 1 FROM api_keys WHERE hash = ? AND valid_date >= NOW()", [ $apiKeyHash ]);

		if($hasValidApiKey !== true) {
			$this->app->jsonHalt(['error' => 'Invalid API Key']);
		}
	}
}

// routes.php
$router->group('/api', function(Router $router) {
	$router->get('/users', [ ApiController::class, 'getUsers' ]);
	$router->get('/companies', [ ApiController::class, 'getCompanies' ]);
	// さらにルート...
}, [ ApiMiddleware::class ]);
```

これで、設定した API キー検証ミドルウェアによってすべての API ルートが保護されます！ルータグループにさらにルートを追加すると、即座に同じ保護が適用されます！

#### ログインバリデーション

ログインユーザーのみが利用可能なルートを保護したいですか？ミドルウェアで簡単に実現できます！

```php
use flight\Engine;

class LoggedInMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$session = $this->app->session();
		if($session->get('logged_in') !== true) {
			$this->app->redirect('/login');
			exit;
		}
	}
}

// routes.php
$router->group('/admin', function(Router $router) {
	$router->get('/dashboard', [ DashboardController::class, 'index' ]);
	$router->get('/clients', [ ClientController::class, 'index' ]);
	// さらにルート...
}, [ LoggedInMiddleware::class ]);
```

#### ルートパラメータ検証

ユーザーが URL の値を変更してアクセスすべきでないデータにアクセスするのを防ぎたいですか？ミドルウェアで解決できます！

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];
		$jobId = $params['jobId'];

		// データベースで何らかの検索を実行
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {
	$router->get('', [ JobController::class, 'view' ]);
	$router->put('', [ JobController::class, 'update' ]);
	$router->delete('', [ JobController::class, 'delete' ]);
	// さらにルート...
}, [ RouteSecurityMiddleware::class ]);
```

## ミドルウェア実行の処理

認証ミドルウェアがあり、認証されていない場合にユーザーをログインページにリダイレクトしたいとします。いくつかのオプションがあります：

1. ミドルウェア関数から false を返し、Flight が自動的に 403 Forbidden エラーを返しますが、カスタマイズはできません。
1. `Flight::redirect()` を使用してユーザーをログインページにリダイレクトできます。
1. ミドルウェア内でカスタムエラーを作成し、ルートの実行を停止できます。

### シンプルでストレート

簡単な `return false;` の例を以下に示します：

```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			return false;
		}

		// true の場合、すべて続行されます
	}
}
```

### リダイレクトの例

ユーザーをログインページにリダイレクトする例を以下に示します：
```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### カスタムエラーの例

API を構築していて JSON エラーをスローする必要があるとします。以下のようにできます：
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->getHeader('Authorization');
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

## 関連項目
- [ルーティング](/learn/routing) - ルートをコントローラーにマッピングし、ビューをレンダリングする方法。
- [リクエスト](/learn/requests) - 受信リクエストの処理方法の理解。
- [レスポンス](/learn/responses) - HTTP レスポンスのカスタマイズ方法。
- [依存性注入](/learn/dependency-injection-container) - ルートでのオブジェクト作成と管理の簡素化。
- [なぜフレームワークか？](/learn/why-frameworks) - Flight のようなフレームワークを使用する利点の理解。
- [ミドルウェア実行戦略の例](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)

## トラブルシューティング
- ミドルウェアにリダイレクトがあるのにアプリがリダイレクトされない場合、ミドルウェアに `exit;` 文を追加してください。

## 変更履歴
- v3.1: ミドルウェアのサポートを追加。