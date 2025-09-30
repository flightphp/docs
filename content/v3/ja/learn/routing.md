# ルーティング

## 概要
Flight PHP のルーティングは、URL パターンをコールバック関数やクラスメソッドにマッピングし、高速でシンプルなリクエスト処理を可能にします。最小限のオーバーヘッド、初心者向けの使用感、そして外部依存なしの拡張性を設計しています。

## 理解
ルーティングは、Flight アプリケーション内で HTTP リクエストをアプリケーションのロジックに接続するコアメカニズムです。ルートを定義することで、異なる URL が関数、クラスメソッド、またはコントローラーアクションを通じて特定のコードをトリガーする方法を指定します。Flight のルーティングシステムは柔軟で、基本パターン、名前付きパラメータ、正規表現、依存性注入やリソースフルルーティングなどの高度な機能に対応しています。このアプローチにより、コードを整理しやすくメンテナンスしやすく保ちつつ、初心者には高速でシンプルに、上級者には拡張可能に保てます。

> **注意:** ルーティングについてさらに理解したいですか？["なぜフレームワークか？"](/learn/why-frameworks) ページで詳細な説明を確認してください。

## 基本的な使用方法

### シンプルなルートの定義
Flight での基本的なルーティングは、URL パターンをコールバック関数またはクラスとメソッドの配列にマッチさせることで行われます。

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> ルートは定義された順序でマッチされます。リクエストに最初にマッチしたルートが呼び出されます。

### コールバックとしての関数の使用
コールバックは任意の呼び出し可能なオブジェクトを使用できます。したがって、通常の関数を使用できます：

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### コントローラーとしてのクラスとメソッドの使用
クラス（静的または非静的）のメソッドも使用できます：

```php
class GreetingController {
    public function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'GreetingController','hello' ]);
// または
Flight::route('/', [ GreetingController::class, 'hello' ]); // 推奨方法
// または
Flight::route('/', [ 'GreetingController::hello' ]);
// または 
Flight::route('/', [ 'GreetingController->hello' ]);
```

または、まずオブジェクトを作成してからメソッドを呼び出す方法：

```php
use flight\Engine;

// GreetingController.php
class GreetingController
{
	protected Engine $app
    public function __construct(Engine $app) {
		$this->app = $app;
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$app = Flight::app();
$greeting = new GreetingController($app);

Flight::route('/', [ $greeting, 'hello' ]);
```

> **注意:** フレームワーク内でコントローラーが呼び出される場合、デフォルトで `flight\Engine` クラスが注入されます。[依存性注入コンテナ](/learn/dependency-injection-container) を通じて指定しない限りです。

### メソッド固有のルーティング

デフォルトでは、ルートパターンはすべてのリクエストメソッドにマッチします。特定のメソッドに応答するには、URL の前に識別子を配置します。

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// Flight::get() をルートに使用できません。それは変数を取得するためのメソッドで、ルートを作成するものではありません。
Flight::post('/', function() { /* code */ });
Flight::patch('/', function() { /* code */ });
Flight::put('/', function() { /* code */ });
Flight::delete('/', function() { /* code */ });
```

複数のメソッドを単一のコールバックにマッピングするには、`|` 区切り文字を使用できます：

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

### ルーターオブジェクトの使用

さらに、ヘルパーメソッドを持つルーターオブジェクトを取得できます：

```php

$router = Flight::router();

// Flight::route() と同じくすべてのメソッドをマップします
$router->map('/', function() {
	echo 'hello world!';
});

// GET リクエスト
$router->get('/users', function() {
	echo 'users';
});
$router->post('/users', 			function() { /* code */});
$router->put('/users/update/@id', 	function() { /* code */});
$router->delete('/users/@id', 		function() { /* code */});
$router->patch('/users/@id', 		function() { /* code */});
```

### 正規表現 (Regex)
ルートで正規表現を使用できます：

```php
Flight::route('/user/[0-9]+', function () {
  // これにより /user/1234 にマッチします
});
```

この方法は利用可能ですが、名前付きパラメータ、または正規表現付きの名前付きパラメータを使用することを推奨します。これらは読みやすく、メンテナンスしやすいためです。

### 名前付きパラメータ
ルートで名前付きパラメータを指定すると、コールバック関数に渡されます。**これはルートの可読性を高めるためのもので、それ以上のものではありません。重要な注意点については以下のセクションを参照してください。**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

名前付きパラメータに正規表現を含めるには、`:` 区切り文字を使用します：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // これにより /bob/123 にマッチします
  // しかし /bob/12345 にはマッチしません
});
```

> **注意:** 位置パラメータ付きのマッチング regex グループ `()` はサポートされていません。例: `:'\(`

#### 重要な注意点

上記の例では、`@name` が直接 `$name` 変数に結びついているように見えますが、そうではありません。コールバック関数のパラメータの順序が何が渡されるかを決定します。コールバック関数のパラメータの順序を切り替えると、変数も切り替わります。例：

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

以下の URL にアクセスした場合：`/bob/123`、出力は `hello, 123 (bob)!` になります。
ルートとコールバック関数の設定時には _注意してください_！

### オプションのパラメータ
マッチングにオプションの名前付きパラメータを指定するには、セグメントを括弧で囲みます。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 以下の URL にマッチします：
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

マッチしなかったオプションのパラメータは `NULL` として渡されます。

### ワイルドカードルーティング
マッチングは個別の URL セグメントでのみ行われます。複数のセグメントにマッチするには、`*` ワイルドカードを使用できます。

```php
Flight::route('/blog/*', function () {
  // これにより /blog/2000/02/01 にマッチします
});
```

すべてのリクエストを単一のコールバックにルーティングするには：

```php
Flight::route('*', function () {
  // 何かを実行
});
```

### 404 Not Found ハンドラー

デフォルトでは、URL が見つからない場合、Flight は非常にシンプルでプレーンな `HTTP 404 Not Found` レスポンスを送信します。よりカスタマイズされた 404 レスポンスを希望する場合は、独自の `notFound` メソッドを[マップ](/learn/extending)できます：

```php
Flight::map('notFound', function() {
	$url = Flight::request()->url;

	// カスタムテンプレートで Flight::render() を使用することもできます。
    $output = <<<HTML
		<h1>My Custom 404 Not Found</h1>
		<h3>The page you have requested {$url} could not be found.</h3>
		HTML;

	$this->response()
		->clearBody()
		->status(404)
		->write($output)
		->send();
});
```

## 高度な使用方法

### ルートでの依存性注入
コンテナ（PSR-11、PHP-DI、Dice など）経由で依存性注入を使用する場合、利用可能なルートのタイプは、自身でオブジェクトを作成しコンテナでオブジェクトを作成するか、クラスとメソッドを呼び出す文字列を使用するかのいずれかです。[依存性注入](/learn/dependency-injection-container) ページで詳細を確認してください。

簡単な例：

```php

use flight\database\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hello(int $id) {
		// $this->pdoWrapper で何かを実行
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// 必要なパラメータでコンテナを設定
// PSR-11 に関する詳細は依存性注入ページを参照
$dice = new \Dice\Dice();

// '$dice = ' で変数を再割り当てすることを忘れずに!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// コンテナハンドラーを登録
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// 通常通りルート
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// または
Flight::route('/hello/@id', 'Greeting->hello');
// または
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

### 次のルートへの実行の引き渡し
<span class="badge bg-warning">非推奨</span>
コールバック関数から `true` を返すことで、次のマッチするルートに実行を引き渡せます。

```php
Flight::route('/user/@name', function (string $name) {
  // 条件をチェック
  if ($name !== "Bob") {
    // 次のルートに継続
    return true;
  }
});

Flight::route('/user/*', function () {
  // これが呼び出されます
});
```

このような複雑なユースケースには、[ミドルウェア](/learn/middleware) を使用することを推奨します。

### ルートエイリアス
ルートにエイリアスを割り当てることで、アプリケーション内で動的にそのエイリアスを呼び出し、後でコード内で生成できます（例: HTML テンプレート内のリンク、またはリダイレクト URL の生成）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
// または 
Flight::route('/users/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');

// 後でコードのどこかで
class UserController {
	public function update() {

		// ユーザーを保存するコード...
		$id = $user['id']; // 例: 5

		$redirectUrl = Flight::getUrl('user_view', [ 'id' => $id ]); // '/users/5' を返します
		Flight::redirect($redirectUrl);
	}
}

```

URL が変更された場合に特に役立ちます。上記の例で、ユーザーが `/admin/users/@id` に移動したとします。ルートにエイリアスを設定していれば、コード内のすべての古い URL を探して変更する必要がなく、エイリアスは今や `/admin/users/5` を返します。

グループ内でもルートエイリアスは動作します：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
	// または
	Flight::route('/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');
});
```

### ルート情報の検査
マッチしたルート情報を検査したい場合、2 つの方法があります：

1. `Flight::router()` オブジェクトの `executedRoute` プロパティを使用します。
2. ルートメソッドの第 3 パラメータに `true` を渡すことで、ルートオブジェクトをコールバックに渡すようリクエストします。ルートオブジェクトはコールバックに渡される最後のパラメータになります。

#### `executedRoute`
```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // $route で何かを実行
  // マッチした HTTP メソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // マッチする正規表現
  $route->regex;

  // URL パターンで使用された '*' の内容を含む
  $route->splat;

  // URL パスを表示...本当に必要なら
  $route->pattern;

  // このルートに割り当てられたミドルウェアを表示
  $route->middleware;

  // このルートに割り当てられたエイリアスを表示
  $route->alias;
});
```

> **注意:** `executedRoute` プロパティは、ルートが実行された後にのみ設定されます。ルートが実行される前にアクセスしようとすると `NULL` になります。[ミドルウェア](/learn/middleware) でも executedRoute を使用できます！

#### ルート定義に `true` を渡す
```php
Flight::route('/', function(\flight\net\Route $route) {
  // マッチした HTTP メソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // マッチする正規表現
  $route->regex;

  // URL パターンで使用された '*' の内容を含む
  $route->splat;

  // URL パスを表示...本当に必要なら
  $route->pattern;

  // このルートに割り当てられたミドルウェアを表示
  $route->middleware;

  // このルートに割り当てられたエイリアスを表示
  $route->alias;
}, true);// <-- この true パラメータがそれを実現します
```

### ルートグループとミドルウェア
関連するルートをグループ化したい場合（例: `/api/v1`）があります。`group` メソッドを使用してこれを行えます：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/users にマッチ
  });

  Flight::route('/posts', function () {
	// /api/v1/posts にマッチ
  });
});
```

グループのグループをネストすることもできます：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() は変数を取得します。ルートを設定しません！オブジェクトコンテキストを以下で参照
	Flight::route('GET /users', function () {
	  // GET /api/v1/users にマッチ
	});

	Flight::post('/posts', function () {
	  // POST /api/v1/posts にマッチ
	});

	Flight::put('/posts/1', function () {
	  // PUT /api/v1/posts にマッチ
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() は変数を取得します。ルートを設定しません！オブジェクトコンテキストを以下で参照
	Flight::route('GET /users', function () {
	  // GET /api/v2/users にマッチ
	});
  });
});
```

#### オブジェクトコンテキストでのグループ化

`Engine` オブジェクトを使用してルートグループ化を次のように使用できます：

```php
$app = Flight::app();

$app->group('/api/v1', function (Router $router) {

  // $router 変数を使用
  $router->get('/users', function () {
	// GET /api/v1/users にマッチ
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts にマッチ
  });
});
```

> **注意:** これは `$router` オブジェクトを使用してルートとグループを定義する推奨方法です。

#### ミドルウェア付きのグループ化

ルートのグループにミドルウェアを割り当てすることもできます：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/users にマッチ
  });
}, [ MyAuthMiddleware::class ]); // インスタンスを使用したい場合は [ new MyAuthMiddleware() ]
```

[group middleware](/learn/middleware#grouping-middleware) ページで詳細を確認してください。

### リソースルーティング
`resource` メソッドを使用して、リソースのためのルートのセットを作成できます。これは RESTful 規約に従ったルートのセットを作成します。

リソースを作成するには：

```php
Flight::resource('/users', UsersController::class);
```

バックグラウンドで以下のルートが作成されます：

```php
[
      'index' => 'GET /users',
      'create' => 'GET /users/create',
      'store' => 'POST /users',
      'show' => 'GET /users/@id',
      'edit' => 'GET /users/@id/edit',
      'update' => 'PUT /users/@id',
      'destroy' => 'DELETE /users/@id'
]
```

コントローラーは以下のメソッドを使用します：

```php
class UsersController
{
    public function index(): void
    {
    }

    public function show(string $id): void
    {
    }

    public function create(): void
    {
    }

    public function store(): void
    {
    }

    public function edit(string $id): void
    {
    }

    public function update(string $id): void
    {
    }

    public function destroy(string $id): void
    {
    }
}
```

> **注意**: 新しく追加されたルートは `php runway routes` を実行して `runway` で確認できます。

#### リソースルートのカスタマイズ

リソースルートを構成するためのオプションがいくつかあります。

##### エイリアスベース

`aliasBase` を構成できます。デフォルトでは、エイリアスは指定された URL の最後の部分です。
例: `/users/` は `aliasBase` を `users` にします。これらのルートが作成されると、エイリアスは `users.index`、`users.create` などになります。エイリアスを変更したい場合は、`aliasBase` を希望の値に設定します。

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

##### Only と Except

`only` と `except` オプションを使用して、作成するルートを指定できます。

```php
// これらのメソッドのみホワイトリストし、残りをブラックリスト
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
// これらのメソッドのみブラックリストし、残りをホワイトリスト
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

これらは基本的にホワイトリストとブラックリストのオプションで、作成するルートを指定できます。

##### ミドルウェア

`resource` メソッドで作成された各ルートに実行されるミドルウェアを指定できます。

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

### ストリーミングレスポンス

`stream()` または `streamWithHeaders()` を使用して、クライアントにレスポンスをストリーミングできます。
これは大きなファイル、長時間実行のプロセス、または大きなレスポンスを生成する場合に役立ちます。
ルートのストリーミングは通常のルートとは少し異なります。

> **注意:** ストリーミングレスポンスは、[`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) を `false` に設定した場合にのみ利用可能です。

#### 手動ヘッダーでのストリーム

ルートで `stream()` メソッドを使用してクライアントにレスポンスをストリーミングできます。これを行う場合、クライアントに出力する前にすべてのヘッダーを手動で設定する必要があります。
これは `header()` PHP 関数または `Flight::response()->setRealHeader()` メソッドで行います。

```php
Flight::route('/@filename', function($filename) {

	$response = Flight::response();

	// 明らかにパスをサニタイズするなどします。
	$fileNameSafe = basename($filename);

	// ルートが実行された後に追加のヘッダーを設定する場合
	// クライアントにエコーする前に定義する必要があります。
	// すべて header() 関数の生の呼び出しまたは
	// Flight::response()->setRealHeader() の呼び出しである必要があります
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// または
	$response->setRealHeader('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// 必要に応じてコンテンツ長を手動で設定
	header('Content-Length: '.filesize($filePath));
	// または
	$response->setRealHeader('Content-Length: '.filesize($filePath));

	// ファイルが読み込まれるにつれてクライアントにストリーミング
	readfile($filePath);

// これが魔法の行です
})->stream();
```

#### ヘッダー付きストリーム

ストリーミングを開始する前にヘッダーを設定するために `streamWithHeaders()` メソッドを使用できます。

```php
Flight::route('/stream-users', function() {

	// ここに追加のヘッダーを追加できます
	// header() または Flight::response()->setRealHeader() を使用するだけです

	// データの取得方法の例として...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// データをクライアントに送信するために必要
		ob_flush();
	}
	echo '}';

// ストリーミングを開始する前にヘッダーを設定する方法です。
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// オプションのステータスコード、デフォルトは 200
	'status' => 200
]);
```

## 関連トピック
- [Middleware](/learn/middleware) - ルートで認証、ロギングなどにミドルウェアを使用。
- [Dependency Injection](/learn/dependency-injection-container) - ルートでのオブジェクト作成と管理の簡素化。
- [Why a Framework?](/learn/why-frameworks) - Flight のようなフレームワークを使用する利点の理解。
- [Extending](/learn/extending) - `notFound` メソッドを含む独自機能で Flight を拡張する方法。
- [php.net: preg_match](https://www.php.net/manual/en/function.preg-match.php) - 正規表現マッチングのための PHP 関数。

## トラブルシューティング
- ルートパラメータは名前ではなく順序でマッチされます。コールバックパラメータの順序がルート定義と一致することを確認してください。
- `Flight::get()` はルートを定義しません。ルーティングには `Flight::route('GET /...')` またはグループ内のルーターオブジェクトコンテキスト（例: `$router->get(...)`）を使用してください。
- executedRoute プロパティはルート実行後にのみ設定されます。実行前は NULL です。
- ストリーミングにはレガシー Flight 出力バッファリング機能の無効化（`flight.v2.output_buffering = false`）が必要です。
- 依存性注入の場合、コンテナベースのインスタンス化をサポートするのは特定のルート定義のみです。

### 404 Not Found または予期しないルート動作

404 Not Found エラー（しかし命に誓ってそこにあり、タイポではないと確信している場合）が表示される場合、これはルートエンドポイントで値を返す代わりにエコーするだけであることが原因の可能性があります。この理由は意図的ですが、一部の開発者を驚かせる可能性があります。

```php

Flight::route('/hello', function(){
	// これにより 404 Not Found エラーが発生する可能性があります
	return 'Hello World';
});

// 恐らくこれが欲しいもの
Flight::route('/hello', function(){
	echo 'Hello World';
});

```

この理由は、ルーターに組み込まれた特別なメカニズムのためで、戻り値を出力を「次のルートに進む」シグナルとして扱います。
動作は [Routing](/learn/routing#passing) セクションで文書化されています。

## 変更履歴
- v3: リソースルーティング、ルートエイリアス、ストリーミングサポート、ルートグループ、ミドルウェアサポートを追加。
- v1: 基本機能の大部分が利用可能。