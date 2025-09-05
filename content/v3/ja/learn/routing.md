# ルーティング

> **Note:** ルーティングについてもっと理解したいですか？ ["why a framework?"](/learn/why-frameworks) ページで詳しい説明を確認してください。

Flight の基本的なルーティングは、URL パターンとコールバック関数、またはクラスとメソッドの配列を一致させることで行われます。

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> ルートは定義された順序で一致します。リクエストに一致する最初のルートが呼び出されます。

### コールバック/関数
コールバックは、呼び出し可能な任意のオブジェクトです。したがって、通常の関数を使用できます：

```php
function hello() {
    echo 'hello world!';  // こんにちは世界！
}

Flight::route('/', 'hello');
```

### クラス
クラスの静的メソッドも使用できます：

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';  // こんにちは世界！
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

または、まずオブジェクトを作成してメソッドを呼び出す：

```php
// Greeting.php  // Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';  // John Doe
    }

    public function hello() {
        echo "Hello, {$this->name}!";  // Hello, {$this->name}!
    }
}

// index.php  // index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// オブジェクトを作成せずにこれを行うこともできます
// 注意: コンストラクタには引数は注入されません
Flight::route('/', [ 'Greeting', 'hello' ]);
// また、この短い構文も使用できます
Flight::route('/', 'Greeting->hello');
// または
Flight::route('/', Greeting::class.'->hello');
```

#### DIC を使用した依存性注入 (Dependency Injection Container)
DIC (依存性注入コンテナ、PSR-11、PHP-DI、Dice など) を使用して依存性を注入したい場合、利用可能なルートの種類は、直接オブジェクトを作成してコンテナでオブジェクトを作成するか、クラスとメソッドを定義する文字列を使用するだけです。詳しくは [Dependency Injection](/learn/extending) ページを参照してください。

簡単な例：

```php
use flight\database\PdoWrapper;

// Greeting.php  // Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;  // $pdoWrapper を設定
	}

	public function hello(int $id) {
		// $this->pdoWrapper を使用して何かを行う
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";  // Hello, world! 私の名前は {$name}!
	}
}

// index.php  // index.php

// 必要なパラメータでコンテナを設定
// PSR-11 に関する詳細は Dependency Injection ページを参照
$dice = new \Dice\Dice();

// 変数を再割り当てすることを忘れないでください！!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// コンテナハンドラを登録
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);  // $dice を使用して $class を作成
});

// 通常通りルートを設定
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// または
Flight::route('/hello/@id', 'Greeting->hello');
// または
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## メソッドルーティング

デフォルトでは、ルートパターンはすべてのリクエストメソッドに対して一致します。URL の前に識別子を置くことで、特定のメソッドに応答できます。

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';  // GET リクエストを受け取りました。
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';  // POST リクエストを受け取りました。
});

// Flight::get() は変数を取得するためのメソッドで、ルートを作成するものではありません
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

単一のコールバックに複数のメソッドをマップするには、`|` 区切り文字を使用できます：

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';  // GET または POST リクエストを受け取りました。
});
```

Router オブジェクトを取得して、ヘルパーメソッドを使用することもできます：

```php
$router = Flight::router();

// すべてのメソッドにマップ
$router->map('/', function() {
	echo 'hello world!';  // こんにちは世界！
});

// GET リクエスト
$router->get('/users', function() {
	echo 'users';  // users
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## 正規表現

ルートで正規表現を使用できます：

```php
Flight::route('/user/[0-9]+', function () {
  // これは /user/1234 に一致します  // これは /user/1234 に一致します
});
```

この方法は利用可能ですが、名前付きパラメータ、または名前付きパラメータに正規表現を組み合わせる方が、読みやすくメンテナンスしやすいため推奨されます。

## 名前付きパラメータ

ルートで名前付きパラメータを指定すると、コールバック関数に渡されます。**これはルートの読みやすさを高めるためのものです。他の重要な注意点については以下のセクションを参照してください。**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";  // hello, $name ($id)!
});
```

名前付きパラメータに正規表現を追加するには、`:` 区切り文字を使用します：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // これは /bob/123 に一致します
  // しかし /bob/12345 には一致しません
});
```

> **Note:** 一致する正規表現グループ `()` は位置パラメータでサポートされていません。:'\(

### 重要な注意点

上記の例では、`@name` が変数 `$name` に直接関連付けられているように見えますが、そうではありません。コールバック関数のパラメータの順序が何を渡すかを決定します。したがって、コールバック関数のパラメータの順序を切り替えると、変数も切り替わります。例：

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";  // hello, $name ($id)!
});
```

次の URL: `/bob/123` にアクセスすると、出力は `hello, 123 (bob)!` になります。ルートとコールバック関数の設定に注意してください。

## オプションのパラメータ

マッチングでオプションの名前付きパラメータを指定するには、セグメントを括弧で囲みます。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 次の URL に一致します：
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

一致しないオプションのパラメータは `NULL` として渡されます。

## ワイルドカード

マッチングは個々の URL セグメントでのみ行われます。複数のセグメントに一致させたい場合、` * ` ワイルドカードを使用します。

```php
Flight::route('/blog/*', function () {
  // これは /blog/2000/02/01 に一致します  // これは /blog/2000/02/01 に一致します
});
```

すべてのリクエストを単一のコールバックにルーティングするには：

```php
Flight::route('*', function () {
  // 何かを行う  // 何かを行う
});
```

## 通過

コールバック関数から `true` を返すことで、次の一致するルートに実行を渡せます。

```php
Flight::route('/user/@name', function (string $name) {
  // いくつかの条件をチェック
  if ($name !== "Bob") {
    // 次のルートに続ける
    return true;  // 次のルートに続ける
  }
});

Flight::route('/user/*', function () {
  // これは呼び出されます  // これは呼び出されます
});
```

## ルートエイリアス

ルートにエイリアスを割り当てると、コードの後で URL を動的に生成できます (例: テンプレート)。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');  // user:'.$id

// コードのどこかで後で
Flight::getUrl('user_view', [ 'id' => 5 ]);  // '/users/5' を返します
```

URL が変更された場合に特に便利です。上記の例で、users が `/admin/users/@id` に移動したとします。エイリアスを使用していれば、参照箇所を変更せずにエイリアスが `/admin/users/5` を返します。

グループ内のルートエイリアスも動作します：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');  // user:'.$id
});


// コードのどこかで後で
Flight::getUrl('user_view', [ 'id' => 5 ]);  // '/users/5' を返します
```

## ルート情報

一致するルート情報を検査するには、2 つの方法があります。`executedRoute` プロパティを使用するか、ルートメソッドの 3 番目のパラメータに `true` を渡してルートオブジェクトをコールバックに渡すことです。ルートオブジェクトは、コールバック関数に渡される最後のパラメータになります。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 一致した HTTP メソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // 一致する正規表現
  $route->regex;

  // URL パターンで使用された '*' の内容を含む
  $route->splat;

  // URL パスを表示....必要なら
  $route->pattern;

  // このルートに割り当てられたミドルウェアを表示
  $route->middleware;

  // このルートに割り当てられたエイリアスを表示
  $route->alias;
}, true);  // true を渡してルートオブジェクトを渡す
```

最後に実行されたルートを検査したい場合：

```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // $route で何かを行う
  // 一致した HTTP メソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // 一致する正規表現
  $route->regex;

  // URL パターンで使用された '*' の内容を含む
  $route->splat;

  // URL パスを表示....必要なら
  $route->pattern;

  // このルートに割り当てられたミドルウェアを表示
  $route->middleware;

  // このルートに割り当てられたエイリアスを表示
  $route->alias;
});  // executedRoute はルートが実行された後にのみ設定されます
```

> **Note:** `executedRoute` プロパティは、ルートが実行された後にのみ設定されます。ルートが実行される前にアクセスしようとすると、`NULL` になります。ミドルウェアでも executedRoute を使用できます！

## ルートグループ

関連するルートをグループ化したい場合 (例: `/api/v1`)、`group` メソッドを使用します：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/users に一致
  });

  Flight::route('/posts', function () {
	// /api/v1/posts に一致
  });
});
```

グループをネストすることもできます：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() は変数を取得し、ルートを設定しません！ オブジェクトの文脈を参照
	Flight::route('GET /users', function () {
	  // GET /api/v1/users に一致
	});

	Flight::post('/posts', function () {
	  // POST /api/v1/posts に一致
	});

	Flight::put('/posts/1', function () {
	  // PUT /api/v1/posts に一致
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() は変数を取得し、ルートを設定しません！ オブジェクトの文脈を参照
	Flight::route('GET /users', function () {
	  // GET /api/v2/users に一致
	});
  });
});
```

### オブジェクトの文脈でのグループ化

`Engine` オブジェクトでルートグループを使用するには、以下のようにします：

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 変数を使用
  $router->get('/users', function () {
	// GET /api/v1/users に一致
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts に一致
  });
});  // $router を使用
```

### ミドルウェア付きのグループ化

ルートグループにミドルウェアを割り当てられます：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/users に一致
  });
}, [ MyAuthMiddleware::class ]);  // または [ new MyAuthMiddleware() ] を使用してインスタンスを使用
```

詳細は [group middleware](/learn/middleware#grouping-middleware) ページを参照してください。

## リソースルーティング

`resource` メソッドを使用して、RESTful 規約に従うリソースのルートセットを作成できます。

リソースを作成するには：

```php
Flight::resource('/users', UsersController::class);  // /users のリソースを作成
```

背景で次のルートを作成します：

```php
[
      'index' => 'GET ',
      'create' => 'GET /create',
      'store' => 'POST ',
      'show' => 'GET /@id',
      'edit' => 'GET /@id/edit',
      'update' => 'PUT /@id',
      'destroy' => 'DELETE /@id'
]
```

コントローラは次のようになります：

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

> **Note**: 新しく追加されたルートは `php runway routes` を実行して `runway` で確認できます。

### リソースルートのカスタマイズ

リソースルートを構成するためのいくつかのオプションがあります。

#### エイリアスベース

`aliasBase` を構成できます。デフォルトでは、エイリアスは指定された URL の最後の部分です。例えば `/users/` の場合、`aliasBase` は `users` になります。これらのルートが作成されると、エイリアスは `users.index`、`users.create` などになります。エイリアスを変更したい場合、`aliasBase` を希望の値に設定します。

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);  // aliasBase を 'user' に設定
```

#### Only と Except

作成するルートを指定するには、`only` と `except` オプションを使用します。

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);  // 'index' と 'show' のみ
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);  // 'create' などを除外
```

これらはホワイトリストとブラックリストのオプションなので、作成するルートを指定できます。

#### ミドルウェア

`resource` メソッドで作成された各ルートに実行されるミドルウェアを指定できます。

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);  // MyAuthMiddleware を追加
```

## ストリーミング

`streamWithHeaders()` メソッドを使用して、クライアントにレスポンスをストリーミングできます。これは大きなファイルを送信したり、長時間実行のプロセスを実行したり、大きなレスポンスを生成したりする場合に便利です。ルートのストリーミングは通常のルートとは少し異なります。

> **Note:** ストリーミングレスポンスは、[`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) を false に設定している場合にのみ利用可能です。

### 手動ヘッダー付きストリーム

ルートで `stream()` メソッドを使用して、クライアントにレスポンスをストリーミングできます。これを行う場合、クライアントに何かを出力する前にすべてのヘッダーを手動で設定する必要があります。これは `header()` PHP 関数または `Flight::response()->setRealHeader()` メソッドで行います。

```php
Flight::route('/@filename', function($filename) {

	// 明らかにパスを sanitizing などを行います。
	$fileNameSafe = basename($filename);  // ファイル名を安全に取得

	// ルート実行後に追加のヘッダーを設定する必要がある場合
	// 何かを echo する前に定義する必要があります。
	// すべて header() 関数への生の呼び出しまたは Flight::response()->setRealHeader() への呼び出しでなければなりません
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');  // Content-Disposition ヘッダーを設定
	// または
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);  // ファイル内容を取得

	// エラー処理など
	if(empty($fileData)) {
		Flight::halt(404, 'File not found');  // ファイルが見つからない場合
	}

	// 必要に応じてコンテンツ長を手動で設定
	header('Content-Length: '.filesize($filename));  // Content-Length を設定

	// データをクライアントにストリーミング
	echo $fileData;

})->stream();  // ストリーミングを有効にする
```

### ヘッダー付きストリーム

`streamWithHeaders()` メソッドを使用して、ストリーミングを開始する前にヘッダーを設定できます。

```php
Flight::route('/stream-users', function() {

	// 追加のヘッダーを追加したい場合
	// header() または Flight::response()->setRealHeader() を使用する必要があります

	// データ取得方法など...例として
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");  // ユーザーをクエリ

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// データをクライアントに送信するために必要
		ob_flush();  // 出力バッファをフラッシュ
	}
	echo '}';

})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// オプションのステータスコード、デフォルトは 200
	'status' => 200
]);  // ヘッダーを設定してストリーミング
```