# ルーティング

> **注記:** ルーティングについて詳しく知りたいですか？ 詳しい説明については、["why a framework?"](/learn/why-frameworks) ページを参照してください。

Flight での基本的なルーティングは、URL パターンとコールバック関数、またはクラスとメソッドの配列を一致させることで行われます。

```php
Flight::route('/', function(){
    echo 'hello world!';
});
// これはルートが定義された順序で一致します。最初の一致するリクエストが呼び出されます。
```

### コールバック/関数
コールバックは、呼び出し可能な任意のオブジェクトにできます。つまり、通常の関数を使用できます：

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### クラス
クラスの静的メソッドも使用できます：

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
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
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// また、オブジェクトを作成せずにこれを行うこともできます
// 注意: コンストラクタに引数は注入されません
Flight::route('/', [ 'Greeting', 'hello' ]);
// また、この短い構文も使用できます
Flight::route('/', 'Greeting->hello');
// または
Flight::route('/', Greeting::class.'->hello');
```

#### DIC を使用した依存性注入 (Dependency Injection Container)
コンテナを介した依存性注入 (PSR-11, PHP-DI, Dice など) を使用したい場合、利用可能なルートのタイプは、直接オブジェクトを作成してコンテナでオブジェクトを作成するか、クラスとメソッドを呼び出すための文字列を定義するものです。詳細については、[Dependency Injection](/learn/extending) ページを参照してください。

簡単な例を示します：

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
		// $this->pdoWrapper で何かをします
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// 必要なパラメータでコンテナを設定します
// PSR-11 に関する詳細については、Dependency Injection ページを参照してください
$dice = new \Dice\Dice();

// 変数を再割り当てすることを忘れないでください！ '$dice = ' を使用します
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// コンテナ ハンドラーを登録します
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
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

デフォルトでは、ルート パターンはすべてのリクエスト メソッドに対して一致します。URL の前に識別子を置くことで、特定のメソッドに応答できます。

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
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
  echo 'I received either a GET or a POST request.';
});
```

さらに、Router オブジェクトを取得して、ヘルパー メソッドを使用できます：

```php
$router = Flight::router();

// すべてのメソッドをマップします
$router->map('/', function() {
	echo 'hello world!';
});

// GET リクエスト
$router->get('/users', function() {
	echo 'users';
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
  // これは /user/1234 に一致します
});
```

この方法は利用可能ですが、名前付きパラメータ、または名前付きパラメータと正規表現を使用することを推奨します。これらは読みやすく、保守が容易です。

## 名前付きパラメータ

ルートで名前付きパラメータを指定すると、コールバック関数に渡されます。**これはルートの読みやすさのためです。以下の重要な注意点を参照してください。**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

名前付きパラメータに正規表現を含めるには、`:` 区切り文字を使用します：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // これは /bob/123 に一致します
  // しかし /bob/12345 には一致しません
});
```

> **注記:** 位置パラメータとの正規表現グループ `()` の一致はサポートされていません。 :'\(

### 重要な注意点

上記の例では、`@name` が変数 `$name` と直接結びついているように見えますが、そうではありません。コールバック関数のパラメータの順序が、渡されるものを決定します。したがって、コールバック関数のパラメータの順序を切り替えると、変数も切り替わります。例：

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

次の URL: `/bob/123` にアクセスすると、出力は `hello, 123 (bob)!` になります。ルートとコールバック関数の設定に注意してください。

## オプションのパラメータ

セグメントを括弧で囲むことで、一致のためのオプションの名前付きパラメータを指定できます。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // これは次の URL に一致します:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

一致しないオプションのパラメータは、`NULL` として渡されます。

## ワイルドカード

一致は個々の URL セグメントでのみ行われます。複数のセグメントに一致したい場合は、` * ` ワイルドカードを使用します。

```php
Flight::route('/blog/*', function () {
  // これは /blog/2000/02/01 に一致します
});
```

すべてのリクエストを単一のコールバックにルーティングするには、以下のようにします：

```php
Flight::route('*', function () {
  // 何かをします
});
```

## 通過

コールバック関数から `true` を返すことで、次の一致するルートに実行を渡せます。

```php
Flight::route('/user/@name', function (string $name) {
  // いくつかの条件をチェックします
  if ($name !== "Bob") {
    // 次のルートに続行します
    return true;
  }
});

Flight::route('/user/*', function () {
  // これは呼び出されます
});
```

## ルートエイリアス

ルートにエイリアスを割り当てると、コードの後で URL を動的に生成できます (例: テンプレート)。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// コードのどこかで後で
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します
```

これは特に、URL が変更された場合に役立ちます。上記の例で、users が `/admin/users/@id` に移動したとします。エイリアスを使用している場合、例のようにエイリアスが `/admin/users/5` を返すため、参照箇所を変更する必要はありません。

ルートエイリアスはグループでも動作します：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// コードのどこかで後で
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します
```

## ルート情報

一致するルート情報を検査したい場合、ルート メソッドの 3 番目のパラメータに `true` を渡すことで、コールバックにルート オブジェクトを渡すことができます。ルート オブジェクトは、コールバック関数に渡される最後のパラメータになります。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 一致した HTTP メソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // 一致する正規表現
  $route->regex;

  // URL パターンで使用された '*' の内容を含みます
  $route->splat;

  // URL パスを表示します...本当に必要な場合
  $route->pattern;

  // このルートに割り当てられたミドルウェアを表示します
  $route->middleware;

  // このルートに割り当てられたエイリアスを表示します
  $route->alias;
}, true);
```

## ルートグループ化

関連するルートをグループ化したい場合 (例: `/api/v1`) は、`group` メソッドを使用します：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/users に一致します
  });

  Flight::route('/posts', function () {
	// /api/v1/posts に一致します
  });
});
```

グループを入れ子にすることもできます：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() は変数を取得しますが、ルートを設定しません！ オブジェクト コンテキストを以下に参照してください
	Flight::route('GET /users', function () {
	  // GET /api/v1/users に一致します
	});

	Flight::post('/posts', function () {
	  // POST /api/v1/posts に一致します
	});

	Flight::put('/posts/1', function () {
	  // PUT /api/v1/posts に一致します
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() は変数を取得しますが、ルートを設定しません！ オブジェクト コンテキストを以下に参照してください
	Flight::route('GET /users', function () {
	  // GET /api/v2/users に一致します
	});
  });
});
```

### オブジェクト コンテキストでのグループ化

`Engine` オブジェクトと一緒にルート グループ化を使用するには、以下のようにします：

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 変数を使用します
  $router->get('/users', function () {
	// GET /api/v1/users に一致します
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts に一致します
  });
});
```

### ミドルウェア付きのグループ化

ルートのグループにミドルウェアを割り当てることができます：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/users に一致します
  });
}, [ MyAuthMiddleware::class ]); // または [ new MyAuthMiddleware() ] を使用してインスタンスを使用したい場合
```

詳細については、[group middleware](/learn/middleware#grouping-middleware) ページを参照してください。

## リソース ルーティング

`resource` メソッドを使用して、RESTful 規約に従うリソースのルート セットを作成できます。

リソースを作成するには、以下のようにします：

```php
Flight::resource('/users', UsersController::class);
```

背景で起こることは、以下のルートを作成します：

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

> **注記**: 新しく追加されたルートを `runway` で表示するには、`php runway routes` を実行します。

### リソース ルートのカスタマイズ

リソース ルートを構成するためのいくつかのオプションがあります。

#### エイリアス ベース

`aliasBase` を構成できます。デフォルトでは、エイリアスは指定された URL の最後の部分です。例えば `/users/` の場合、`aliasBase` は `users` になります。これらのルートが作成されると、エイリアスは `users.index`、`users.create` などになります。エイリアスを変更したい場合、`aliasBase` を望みの値に設定します。

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Only と Except

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

`streamWithHeaders()` メソッドを使用して、クライアントにレスポンスをストリーミングできます。これは、大型のファイルを送信したり、長時間実行のプロセスを実行したり、大型のレスポンスを生成したりする場合に便利です。ルートのストリーミングは、通常のルートとは少し異なります。

> **注記:** ストリーミング レスポンスは、[`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) を false に設定した場合にのみ利用可能です。

### 手動ヘッダー付きのストリーム

`stream()` メソッドを使用して、クライアントにレスポンスをストリーミングできます。これを行う場合、クライアントに出力する前にすべてのヘッダーを手動で設定する必要があります。これは、`header()` PHP 関数または `Flight::response()->setRealHeader()` メソッドで行います。

```php
Flight::route('/@filename', function($filename) {

	// 明らかに、パスを sanitizing するなどします。
	$fileNameSafe = basename($filename);

	// ルートが実行された後に追加のヘッダーを設定する必要がある場合
	// 何も出力される前に定義する必要があります。
	// すべて raw な header() 関数の呼び出しまたは
	// Flight::response()->setRealHeader() の呼び出しでなければなりません
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// または
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// エラー捕捉など
	if(empty($fileData)) {
		Flight::halt(404, 'File not found');
	}

	// 必要に応じて手動でコンテンツ長を設定します
	header('Content-Length: '.filesize($filename));

	// データをクライアントにストリーミングします
	echo $fileData;

// これは魔法の行です
})->stream();
```

### ヘッダー付きのストリーム

`streamWithHeaders()` メソッドを使用して、ストリーミングを開始する前にヘッダーを設定できます。

```php
Flight::route('/stream-users', function() {

	// 追加のヘッダーを追加できます
	// header() または Flight::response()->setRealHeader() を使用する必要があります

	// データの取得方法はどのようにでも...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// データをクライアントに送信するために必要です
		ob_flush();
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