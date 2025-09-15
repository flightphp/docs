# ルーティング

> **注記:** ルーティングについてさらに理解したいですか？ ["why a framework?"](/learn/why-frameworks) ページをチェックして、より詳細な説明を確認してください。

Flight の基本的なルーティングは、URL パターンとコールバック関数、またはクラスとメソッドの配列をマッチさせることで行われます。

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> ルートは定義された順序でマッチされます。リクエストにマッチした最初のルートが呼び出されます。

### コールバック/関数
コールバックは、呼び出し可能な任意のオブジェクトです。したがって、通常の関数を使用できます：

```php
function hello() {
    echo 'hello world!';  // これはコメントです
}

Flight::route('/', 'hello');
```

### クラス
クラスの静的メソッドも使用できます：

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';  // これはコメントです
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

または、まずオブジェクトを作成して次にメソッドを呼び出す：

```php
// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';  // これはコメントです
    }

    public function hello() {
        echo "Hello, {$this->name}!";  // これはコメントです
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// オブジェクトを先に作成せずにこれを行うこともできます
// 注記: コンストラクタに引数は注入されません
Flight::route('/', [ 'Greeting', 'hello' ]);
// さらに短い構文も使用できます
Flight::route('/', 'Greeting->hello');
// または
Flight::route('/', Greeting::class.'->hello');
```

#### DIC を使用した依存性注入 (Dependency Injection Container)
コンテナ経由の依存性注入 (PSR-11、PHP-DI、Dice など) を使用したい場合、利用可能なルートのタイプは、オブジェクトを自分で作成してコンテナで作成するか、クラスとメソッドを定義するための文字列を使用するだけです。詳細については [Dependency Injection](/learn/extending) ページを参照してください。

簡単な例です：

```php
use flight\database\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;  // これはコメントです
	}

	public function hello(int $id) {
		// $this->pdoWrapper を使用して何かを行う
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";  // これはコメントです
	}
}

// index.php

// 必要なパラメータでコンテナを設定
// PSR-11 に関する詳細は Dependency Injection ページを参照
$dice = new \Dice\Dice();

// 変数を再割り当てすることを忘れないでください！ '$dice = ' を使用
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
	return $dice->create($class, $params);  // これはコメントです
});

// 通常どおりのルート
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// または
Flight::route('/hello/@id', 'Greeting->hello');
// または
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## メソッド ルーティング

デフォルトでは、ルート パターンはすべてのリクエスト メソッドに対してマッチされます。URL の前に識別子を置くことで、特定のメソッドに応答できます。

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';  // これはコメントです
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';  // これはコメントです
});

// Flight::get() はルートを作成するためのメソッドではなく、変数を取得するためのものです
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

単一のコールバックに複数のメソッドをマップするには、`|` 区切り文字を使用できます：

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';  // これはコメントです
});
```

さらに、Router オブジェクトを取得して、いくつかのヘルパー メソッドを使用できます：

```php
$router = Flight::router();

// すべてのメソッドをマップ
$router->map('/', function() {
	echo 'hello world!';  // これはコメントです
});

// GET リクエスト
$router->get('/users', function() {
	echo 'users';  // これはコメントです
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
  // これは /user/1234 にマッチします  // これはコメントです
});
```

この方法は利用可能ですが、名前付きパラメータ、または正規表現付きの名前付きパラメータを使用することを推奨します。これらはより読みやすく、メンテナンスが簡単です。

## 名前付きパラメータ

ルートで名前付きパラメータを指定すると、コールバック関数に渡されます。**これはルートの読みやすさのためです。他の重要な注意点については以下のセクションを参照してください。**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";  // これはコメントです
});
```

名前付きパラメータに正規表現を追加するには、`:` 区切り文字を使用します：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // これは /bob/123 にマッチします
  // しかし /bob/12345 にはマッチしません  // これはコメントです
});
```

> **注記:** マッチング正規表現グループ `()` を位置パラメータで使用することはサポートされていません。 :'\(

### 重要な注意点

上記の例では、`@name` が変数 `$name` に直接関連付けられているように見えますが、そうではありません。コールバック関数のパラメータの順序が何を渡すかを決定します。したがって、コールバック関数のパラメータの順序を切り替えると、変数も切り替わります。例：

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";  // これはコメントです
});
```

次の URL: `/bob/123` にアクセスすると、出力は `hello, 123 (bob)!` になります。ルートとコールバック関数の設定には注意してください。

## オプションのパラメータ

マッチングにオプションの名前付きパラメータを指定するには、セグメントを括弧で囲みます。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 次の URL にマッチします：
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog  // これはコメントです
  }
);
```

マッチしなかったオプションのパラメータは `NULL` として渡されます。

## ワイルドカード

マッチングは個々の URL セグメントでのみ行われます。複数のセグメントにマッチしたい場合、` * ` ワイルドカードを使用できます。

```php
Flight::route('/blog/*', function () {
  // これは /blog/2000/02/01 にマッチします  // これはコメントです
});
```

すべてのリクエストを単一のコールバックにルーティングするには：

```php
Flight::route('*', function () {
  // 何かを行う  // これはコメントです
});
```

## Passing

コールバック関数から `true` を返すことで、次のマッチング ルートに実行を渡せます。

```php
Flight::route('/user/@name', function (string $name) {
  // いくつかの条件をチェック
  if ($name !== "Bob") {
    // 次のルートに継続
    return true;  // これはコメントです
  }
});

Flight::route('/user/*', function () {
  // これは呼び出されます  // これはコメントです
});
```

## ルートエイリアス

ルートにエイリアスを割り当てると、コードの後で URL を動的に生成できます (例: テンプレート)。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');  // これはコメントです

// コードのどこかで後
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します  // これはコメントです
```

URL が変更された場合に特に便利です。上記の例で、users が `/admin/users/@id` に移動したとします。エイリアスを使用していれば、参照箇所を変更する必要はありません。エイリアスは `/admin/users/5` を返します。

グループ内のルートエイリアスも動作します：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');  // これはコメントです
});


// コードのどこかで後
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します  // これはコメントです
```

## ルート情報

マッチング ルート情報を検査したい場合、2 つの方法があります。`executedRoute` プロパティを使用するか、ルート メソッドの 3 番目のパラメータに `true` を渡してコールバックにルート オブジェクトを要求します。ルート オブジェクトは、コールバック関数に渡される最後のパラメータになります。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // マッチした HTTP メソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // マッチング正規表現
  $route->regex;

  // URL パターンで使用された '*' の内容を含む
  $route->splat;

  // URL パスを表示....本当に必要なら  // これはコメントです
  $route->pattern;

  // このルートに割り当てられたミドルウェアを表示
  $route->middleware;

  // このルートに割り当てられたエイリアスを表示
  $route->alias;
}, true);
```

または、最後に実行されたルートを検査したい場合：

```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // $route で何かを行う
  // マッチした HTTP メソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // マッチング正規表現
  $route->regex;

  // URL パターンで使用された '*' の内容を含む
  $route->splat;

  // URL パスを表示....本当に必要なら  // これはコメントです
  $route->pattern;

  // このルートに割り当てられたミドルウェアを表示
  $route->middleware;

  // このルートに割り当てられたエイリアスを表示
  $route->alias;
});
```

> **注記:** `executedRoute` プロパティは、ルートが実行された後にのみ設定されます。ルートが実行される前にアクセスしようとすると、`NULL` になります。ミドルウェアでも使用できます！

## ルート グループ化

関連するルートをグループ化したい場合 (例: `/api/v1`)、`group` メソッドを使用できます：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/users にマッチ  // これはコメントです
  });

  Flight::route('/posts', function () {
	// /api/v1/posts にマッチ  // これはコメントです
  });
});
```

グループをネストすることもできます：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() は変数を取得します、ルートを設定しません！ オブジェクト コンテキストを参照  // これはコメントです
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

	// Flight::get() は変数を取得します、ルートを設定しません！ オブジェクト コンテキストを参照  // これはコメントです
	Flight::route('GET /users', function () {
	  // GET /api/v2/users にマッチ
	});
  });
});
```

### オブジェクト コンテキストでのグループ化

`Engine` オブジェクトと一緒にルート グループ化を使用するには、以下の方法で：

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 変数を使用
  $router->get('/users', function () {
	// GET /api/v1/users にマッチ  // これはコメントです
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts にマッチ  // これはコメントです
  });
});
```

### ミドルウェア付きのグループ化

ルートのグループにミドルウェアを割り当てられます：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/users にマッチ  // これはコメントです
  });
}, [ MyAuthMiddleware::class ]); // または [ new MyAuthMiddleware() ] を使用してインスタンスを使用
```

詳細は [group middleware](/learn/middleware#grouping-middleware) ページを参照してください。

## リソース ルーティング

`resource` メソッドを使用して、リソースのルート セットを作成できます。これにより、RESTful 規約に従うリソースのルート セットが作成されます。

リソースを作成するには、以下のように：

```php
Flight::resource('/users', UsersController::class);
```

背景で何が起こるかというと、以下のルートが作成されます：

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

コントローラは以下のように見えます：

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

> **注記**: 新しく追加されたルートは `runway` で確認できます。`php runway routes` を実行してください。

### リソース ルートのカスタマイズ

リソース ルートを構成するためのいくつかのオプションがあります。

#### エイリアス ベース

`aliasBase` を構成できます。デフォルトでは、エイリアスは指定された URL の最後の部分です。例えば `/users/` は `aliasBase` を `users` にします。これらのルートが作成されると、エイリアスは `users.index`、`users.create` などになります。エイリアスを変更したい場合、`aliasBase` を望みの値に設定します。

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Only and Except

`only` と `except` オプションを使用して、作成するルートを指定できます。

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

これらはホワイトリストとブラックリストのオプションなので、作成するルートを指定できます。

#### ミドルウェア

`resource` メソッドで作成された各ルートで実行されるミドルウェアを指定できます。

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## ストリーミング

`streamWithHeaders()` メソッドを使用して、クライアントにレスポンスをストリーミングできます。これは、大容量のファイル、長時間実行のプロセス、または大容量のレスポンスを送信するのに便利です。ルートのストリーミングは、通常のルートとは少し異なります。

> **注記:** ストリーミング レスポンスは、[`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) が false に設定されている場合にのみ利用可能です。

### 手動ヘッダー付きのストリーム

`stream()` メソッドを使用して、クライアントにレスポンスをストリーミングできます。これを行う場合、クライアントに出力する前にすべてのメソッドを手動で設定する必要があります。これは `header()` PHP 関数または `Flight::response()->setRealHeader()` メソッドで行います。

```php
Flight::route('/@filename', function($filename) {

	// 明らかにパスを sanitizing などを行います。
	$fileNameSafe = basename($filename);  // これはコメントです

	// ルートが実行された後に追加のヘッダーを設定する必要がある場合
	// 何も出力される前に定義する必要があります。
	// すべて header() 関数の raw 呼び出しまたは Flight::response()->setRealHeader() の呼び出しでなければなりません
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// または
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');  // これはコメントです
	}

	// 必要に応じてコンテンツ長を手動で設定
	header('Content-Length: '.filesize($filePath));

	// ファイルが読み込まれると同時にクライアントにストリーミング
	readfile($filePath);

// これは魔法の行です  // これはコメントです
})->stream();
```

### ヘッダー付きのストリーム

`streamWithHeaders()` メソッドを使用して、ストリーミングを開始する前にヘッダーを設定できます。

```php
Flight::route('/stream-users', function() {

	// 追加のヘッダーをここに追加できます
	// header() または Flight::response()->setRealHeader() を使用する必要があります

	// データの取得方法はどのようにでも...例として...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// データをクライアントに送信するために必要
		ob_flush();  // これはコメントです
	}
	echo '}';

// ストリーミングを開始する前にヘッダーを設定します。
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// オプションのステータス コード、デフォルトは 200
	'status' => 200
]);
```