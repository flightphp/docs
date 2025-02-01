# ルーティング

> **注:** ルーティングについてもっと理解したいですか？より詳細な説明については、["なぜフレームワーク？"](/learn/why-frameworks)ページをチェックしてください。

Flightでの基本的なルーティングは、URLパターンをコールバック関数またはクラスとメソッドの配列と照合することによって行われます。

```php
Flight::route('/', function(){
    echo 'こんにちは世界！';
});
```

> ルートは定義された順序で一致します。リクエストに一致する最初のルートが呼び出されます。

### コールバック/関数
コールバックは呼び出し可能な任意のオブジェクトである必要があります。つまり、通常の関数を使用することができます：

```php
function hello() {
    echo 'こんにちは世界！';
}

Flight::route('/', 'hello');
```

### クラス
クラスの静的メソッドを使用することもできます：

```php
class Greeting {
    public static function hello() {
        echo 'こんにちは世界！';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

または、最初にオブジェクトを作成し、メソッドを呼び出すこともできます：

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'ジョン・ドー';
    }

    public function hello() {
        echo "こんにちは、{$this->name}！";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// オブジェクトを最初に作成せずにこれを行うこともできます
// 注: 引数はコンストラクタに注入されません
Flight::route('/', [ 'Greeting', 'hello' ]);
// さらに短い構文を使用できます
Flight::route('/', 'Greeting->hello');
// または
Flight::route('/', Greeting::class.'->hello');
```

#### DIC（依存性注入コンテナ）による依存性注入
コンテナを介して依存性注入を使用したい場合（PSR-11、PHP-DI、Diceなど）、利用できるルートの種類は、オブジェクトを自分で直接作成し、コンテナを使用してオブジェクトを作成するか、呼び出すクラスとメソッドを文字列で定義する必要があります。詳細については、[依存性注入](/learn/extending)ページをご覧ください。

以下は簡単な例です：

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
		// $this->pdoWrapperで何かをする
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "こんにちは、世界！私の名前は{$name}です！";
	}
}

// index.php

// 必要なパラメータでコンテナを設定
// PSR-11に関する詳細情報は依存性注入ページを参照してください
$dice = new \Dice\Dice();

// '$dice ='で変数を再割り当てすることを忘れないでください！！！！！
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
	return $dice->create($class, $params);
});

// 通常のようにルートを設定
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// または
Flight::route('/hello/@id', 'Greeting->hello');
// または
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## メソッドルーティング

デフォルトでは、ルートパターンはすべてのリクエストメソッドに対して一致します。特定のメソッドに応答するには、URLの前に識別子を置きます。

```php
Flight::route('GET /', function () {
  echo 'GETリクエストを受け取りました。';
});

Flight::route('POST /', function () {
  echo 'POSTリクエストを受け取りました。';
});

// Flight::get()はルートには使用できません。これは変数を取得するためのメソッドであり、ルートを作成するものではありません。
// Flight::post('/', function() { /* コード */ });
// Flight::patch('/', function() { /* コード */ });
// Flight::put('/', function() { /* コード */ });
// Flight::delete('/', function() { /* コード */ });
```

複数のメソッドを単一のコールバックにマッピングすることもできます。`|`区切りを使用します：

```php
Flight::route('GET|POST /', function () {
  echo 'GETまたはPOSTリクエストのいずれかを受け取りました。';
});
```

さらに、いくつかのヘルパーメソッドを使用するためのRouterオブジェクトを取得できます：

```php

$router = Flight::router();

// すべてのメソッドをマッピングする
$router->map('/', function() {
	echo 'こんにちは世界！';
});

// GETリクエスト
$router->get('/users', function() {
	echo 'ユーザー';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## 正規表現

ルートに正規表現を使用できます：

```php
Flight::route('/user/[0-9]+', function () {
  // これは/user/1234と一致します
});
```

この方法は利用可能ですが、名前付きパラメータや正規表現を使った名前付きパラメータを使用することをお勧めします。なぜなら、それらはより可読性があり、メンテナンスが容易だからです。

## 名前付きパラメータ

ルートで名前付きパラメータを指定することができ、コールバック関数に渡されます。**これはルートの可読性のためだけのものです。それ以外に特に注意が必要です。**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "こんにちは、$name ($id)！";
});
```

名前付きパラメータに正規表現を含めることもできます。`:`区切りを使用します：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // これは/bob/123と一致します
  // しかし/bob/12345とは一致しません
});
```

> **注:** 一致する正規表現グループ`()`と位置パラメータはサポートされていません。 :'\(

### 重要な注意点

上記の例では`@name`が変数`$name`に直接結びついているように見えますが、実際にはそうではありません。コールバック関数のパラメータの順序が、それに渡されるものを決定します。したがって、コールバック関数のパラメータの順序を切り替えると、変数も切り替わります。以下はその例です：

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "こんにちは、$name ($id)！";
});
```

次のURLでアクセスした場合：`/bob/123`、出力は`こんにちは、123 (bob)!`になります。
ルートとコールバック関数を設定する際には注意してください。

## 省略可能なパラメータ

一致する省略可能な名前付きパラメータを指定するには、セグメントを括弧で囲みます。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 次のURLに一致します：
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

一致しない省略可能なパラメータは`NULL`として渡されます。

## ワイルドカード

一致は個々のURLセグメントのみに行われます。複数のセグメントを一致させたい場合は、`*`ワイルドカードを使用できます。

```php
Flight::route('/blog/*', function () {
  // これは/blog/2000/02/01に一致します
});
```

すべてのリクエストを単一のコールバックにルーティングするには、次のようにします：

```php
Flight::route('*', function () {
  // 何かをする
});
```

## パススルー

コールバック関数から`true`を返すことで、次の一致するルートに実行を渡すことができます。

```php
Flight::route('/user/@name', function (string $name) {
  // 条件を確認する
  if ($name !== "Bob") {
    // 次のルートに続行
    return true;
  }
});

Flight::route('/user/*', function () {
  // これは呼び出されます
});
```

## ルートエイリアス

ルートにエイリアスを割り当てることで、後でコード内でURLを動的に生成できるようになります（例えば、テンプレートのように）。

```php
Flight::route('/users/@id', function($id) { echo 'ユーザー:'.$id; }, false, 'user_view');

// 後でコード内のどこかで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'を返します
```

これは、URLが変更される場合に特に便利です。上記の例では、ユーザーが`/admin/users/@id`に移動したとします。
エイリアスを使用しているため、エイリアスを参照する場所すべてを変更する必要はありません。エイリアスは現在`/admin/users/5`を返します。

ルートエイリアスはグループ内でも機能します：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'ユーザー:'.$id; }, false, 'user_view');
});

// 後でコード内のどこかで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5'を返します
```

## ルート情報

一致するルート情報を調査したい場合は、ルートメソッドの第3パラメータに`true`を渡すことで、ルートオブジェクトをコールバックに渡すようリクエストできます。ルートオブジェクトは、常にコールバック関数に渡される最後のパラメータになります。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 一致したHTTPメソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // 一致する正規表現
  $route->regex;

  // URLパターンで使用された'*'の内容を含みます
  $route->splat;

  // URLパスを示します....本当に必要な場合
  $route->pattern;

  // このルートに割り当てられたミドルウェアを示します
  $route->middleware;

  // このルートに割り当てられたエイリアスを示します
  $route->alias;
}, true);
```

## ルートグループ

関連するルートをグループ化したい場合があります（例えば`/api/v1`など）。これを行うには`group`メソッドを使用します：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/usersに一致します
  });

  Flight::route('/posts', function () {
	// /api/v1/postsに一致します
  });
});
```

グループのグループを入れ子にすることもできます：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()は変数を取得し、それはルートを設定しない！下のオブジェクトコンテキストを参照してください
	Flight::route('GET /users', function () {
	  // GET /api/v1/usersに一致します
	});

	Flight::post('/posts', function () {
	  // POST /api/v1/postsに一致します
	});

	Flight::put('/posts/1', function () {
	  // PUT /api/v1/postsに一致します
	});
  });
  Flight::group('/v2', function () {

	// Flight::get()は変数を取得し、それはルートを設定しない！下のオブジェクトコンテキストを参照してください
	Flight::route('GET /users', function () {
	  // GET /api/v2/usersに一致します
	});
  });
});
```

### オブジェクトコンテキストを使用したグループ化

次のように`Engine`オブジェクトを使用してルートグループを使用することもできます：

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router変数を使用
  $router->get('/users', function () {
	// GET /api/v1/usersに一致します
  });

  $router->post('/posts', function () {
	// POST /api/v1/postsに一致します
  });
});
```

## リソースルーティング

`resource`メソッドを使用して、リソース用のルートセットを作成できます。これにより、RESTful規約に従ったリソースのためのルートセットが作成されます。

リソースを作成するには、次のようにします：

```php
Flight::resource('/users', UsersController::class);
```

裏では、次のルートが作成されます：

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

そして、あなたのコントローラーは次のようになります：

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

> **注:** 新しく追加されたルートは、`php runway routes`を実行して`runway`で表示できます。

### リソースルートのカスタマイズ

リソースルートを構成するいくつかのオプションがあります。

#### エイリアスベース

`aliasBase`を構成できます。デフォルトでは、エイリアスは指定されたURLの最後の部分です。
例えば、`/users/`は`users`の`aliasBase`になります。これらのルートが作成されると、エイリアスは`users.index`、`users.create`などとなります。エイリアスを変更する場合は、`aliasBase`を希望する値に設定してください。

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### 除外とのみ

`only`および`except`オプションを使用して、作成したいルートを指定することもできます。

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

これらは基本的にホワイトリストおよびブラックリストオプションであり、作成したいルートを指定できます。

#### ミドルウェア

`resource`メソッドで作成された各ルートで実行されるミドルウェアを指定することもできます。

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## ストリーミング

`streamWithHeaders()`メソッドを使用して、クライアントにレスポンスをストリーミングできるようになりました。これは、大きなファイルや長時間実行されるプロセス、大きなレスポンスを生成するのに便利です。
ストリーミングルートの処理は、通常のルートとは少し異なります。

> **注:** ストリーミングレスポンスは、[`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering)がfalseに設定されている場合のみ利用可能です。

### 手動ヘッダーでのストリーム

ルートで`stream()`メソッドを使用して、クライアントにレスポンスをストリーミングできます。これを行う場合、出力する前に手動ですべてのメソッドを設定する必要があります。
これは`header()` PHP関数または`Flight::response()->setRealHeader()`メソッドを使用して行います。

```php
Flight::route('/@filename', function($filename) {

	// 明らかに、パスやその他のものをサニタイズする必要があります。
	$fileNameSafe = basename($filename);

	// ルートが実行された後にここに追加のヘッダーを設定する場合、
	// 出力される前にすべて定義する必要があります。
	// それらはすべてheader()関数への生の呼び出しか、
	// Flight::response()->setRealHeader()への呼び出しである必要があります。
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// または
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// エラー処理とその他の処理
	if(empty($fileData)) {
		Flight::halt(404, 'ファイルが見つかりません');
	}

	// 必要に応じて手動でコンテンツ長を設定
	header('Content-Length: '.filesize($filename));

	// データをクライアントにストリーミング
	echo $fileData;

// これが魔法の行です
})->stream();
```

### ヘッダーでのストリーム

ストリーミングを開始する前にヘッダーを設定するために、`streamWithHeaders()`メソッドを使用することもできます。

```php
Flight::route('/stream-users', function() {

	// ここに追加する任意のヘッダーを追加できます
	// header()またはFlight::response()->setRealHeader()を使用する必要があります

	// どのようにデータを取得するかは、あくまで例として...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// これはデータをクライアントに送信するために必要です
		ob_flush();
	}
	echo '}';

// ストリーミングを開始する前にヘッダーを設定する方法です。
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// オプションのステータスコード、デフォルトは200
	'status' => 200
]);
```