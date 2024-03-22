# ルーティング

> **注意:** ルーティングについて詳しく知りたいですか？より詳細な説明については、["なぜフレームワークか？"](/learn/why-frameworks) ページをチェックしてください。

Flight での基本的なルーティングは、URL パターンをコールバック関数またはクラスとメソッドの配列と一致させることで行われます。

```php
Flight::route('/', function(){
    echo 'こんにちは、世界！';
});
```

> ルートは定義された順に一致します。リクエストと一致する最初のルートが呼び出されます。

### コールバック/関数

コールバックは、呼び出し可能な任意のオブジェクトにすることができます。通常の関数を使用できます：

```php
function hello(){
    echo 'こんにちは、世界！';
}

Flight::route('/', 'hello');
```

### クラス

クラスの静的メソッドを使用することもできます：

```php
class Greeting {
    public static function hello() {
        echo 'こんにちは、世界！';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

また、最初にオブジェクトを作成してからメソッドを呼び出すこともできます：

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "こんにちは、{$this->name}さん！";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// オブジェクトを作成せずにこれを行うこともできます
// 注意：コンストラクタに引数は挿入されません
Flight::route('/', [ 'Greeting', 'hello' ]);
```

#### DIC（Dependency Injection Container）を介した依存性注入
依存性注入をコンテナ（PSR-11、PHP-DI、Dice など）を介して使用したい場合、
自分でオブジェクトを作成してコンテナを使用してオブジェクトを作成するルートタイプだけが利用可能です。または、クラスと呼び出すメソッドを指定する際に文字列を使用することもできます。詳細については [Dependency Injection](/learn/extending) ページを参照してください。

以下に簡単な例を示します：

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
		// $this->pdoWrapper を使用して何かを行う
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "こんにちは、世界！私の名前は {$name} です！";
	}
}

// index.php

// 必要なパラメーターでコンテナを設定します
// PSR-11 に関する詳細については依存性注入ページを参照してください
$dice = new \Dice\Dice();

// 変数を '$dice = ' で再割り当てするのを忘れないでください！！！
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// コンテナハンドラを登録します
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// 通常どおりにルーティングを行います
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// または
Flight::route('/hello/@id', 'Greeting->hello');
// または
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## メソッドルーティング

デフォルトでは、ルートパターンはすべてのリクエストメソッドと一致します。特定のメソッドに応答するには、URL の前に識別子を配置します。

```php
Flight::route('GET /', function () {
  echo 'GET リクエストを受け取りました。';
});

Flight::route('POST /', function () {
  echo 'POST リクエストを受け取りました。';
});

// ルートを作成するために Flight::get() を使用することはできません
//    それはルートを作成するのではなく変数を取得するためのメソッドです。
// Flight::post('/', function() { /* コード */ });
// Flight::patch('/', function() { /* コード */ });
// Flight::put('/', function() { /* コード */ });
// Flight::delete('/', function() { /* コード */ });
```

`|` デリミタを使用して、複数のメソッドを単一のコールバックにマップすることもできます：

```php
Flight::route('GET|POST /', function () {
  echo 'GET または POST リクエストを受け取りました。';
});
```

さらに、使用できるヘルパーメソッドを持つ Router オブジェクトを取得することもできます：

```php

$router = Flight::router();

// すべてのメソッドをマップします
$router->map('/', function() {
	echo 'こんにちは、世界！';
});

// GET リクエスト
$router->get('/users', function() {
	echo 'ユーザー';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## 正規表現

ルートで正規表現を使用することができます：

```php
Flight::route('/user/[0-9]+', function () {
  // これは /user/1234 に一致します
});
```

このメソッドは利用可能ですが、名前付きパラメーター、または
名前付きパラメーターと正規表現を使用することをお勧めします。それらはより読みやすく、メンテナンスが容易です。

## 名前付きパラメーター

コールバック関数に渡される名前付きパラメーターを指定できます。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "$name さん、こんにちは（$id）！";
});
```

`:` デリミタを使用して、名前付きパラメーターに正規表現を含めることもできます：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // これは /bob/123 に一致します
  // ただし /bob/12345 には一致しません
});
```

> **注意:** 名前付きパラメーターと正規表現とを組み合わせた括弧のグループ `()` はサポートされていません。 :'\(

## オプションパラメーター

マッチングをオプションにするために、セグメントを括弧で囲んで名前付きパラメーターを指定できます。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 以下の URL を一致させます：
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

一致しなかった任意のオプションパラメーターは `NULL` として渡されます。

## ワイルドカード

マッチングは個々の URL セグメントにのみ行われます。複数セグメントを一致させたい場合は、`*` ワイルドカードを使用できます。

```php
Flight::route('/blog/*', function () {
  // これは /blog/2000/02/01 に一致します
});
```

すべてのリクエストを単一のコールバックにルーティングするには、次のようにします：

```php
Flight::route('*', function () {
  // 何かを行う
});
```

## パッシング

コールバック関数から `true` を返すことで、次の一致するルートに実行を引き継ぐことができます。

```php
Flight::route('/user/@name', function (string $name) {
  // 一部の条件を確認
  if ($name !== "Bob") {
    // 次のルートに続行
    return true;
  }
});

Flight::route('/user/*', function () {
  // これが呼び出されます
});
```

## ルートにエイリアスを付ける

ルートにエイリアスを割り当てることができ、後でコード内で動的に URL を生成できます（たとえば、テンプレートなど）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// 何かのコードの後で
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' が返されます
```

特に URL が変更される場合に役立ちます。上記の例では、ユーザーは `/admin/users/@id` に移動されました。
エイリアスを使用すると、エイリアスを参照しているどこかすべてを変更する必要がないため、上記の例のようにエイリアスは `/admin/users/5` を返します。

エイリアスはグループ内でも機能します：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// 何かのコードの後で
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' が返されます
```

## ルート情報

一致するルート情報を調べたい場合は、`true` を第三引数に渡してコールバックにルートオブジェクトを要求することができます。ルートオブジェクトは、常にコールバック関数に渡される最後のパラメーターです。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 一致した HTTP メソッドの配列
  $route->methods;

  // 名前付きパラメーターの配列
  $route->params;

  // 一致した正規表現
  $route->regex;

  // URL パターン内の '*' の内容
  $route->splat;

  // URL パスを表示します... 本当に必要ですか？
  $route->pattern;

  // このルートに割り当てられたミドルウェアを表示します
  $route->middleware;

  // このルートに割り当てられたエイリアスを表示します
  $route->alias;
}, true);
```

## ルートグループ化

関連するルートをまとめて (たとえば `/api/v1`) グループ化したい場合があります。
これは `group` メソッドを使用して行うことができます：

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

さらに、グループ化されたグループも作成できます：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() は変数を取得するメソッドであり、ルートを設定するメソッドではありません！オブジェクトコンテキストを参照してください
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
	// Flight::get() は変数を取得するメソッドであり、ルートを設定するメソッドではありません！オブジェクトコンテキストを参照してください
	Flight::route('GET /users', function () {
	  // GET /api/v2/users に一致します
	});
  });
});
```

### オブジェクトコンテキストを使ったグループ化

`Engine` オブジェクトを続行することで、ルートグループ化を使用できます：

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

## ストリーミング

`streamWithHeaders()` メソッドを使用してクライアントにレスポンスをストリーミングすることができます。
これは大きなファイルの送信、長時間実行されるプロセス、または大規模な応答の生成に役立ちます。
ルートのストリーミングは、通常のルートとは少し異なる方法で処理されます。

> **注意:** ストリーミングレスポンスは、[`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) を `false` に設定している場合にのみ利用できます。

```php
Flight::route('/stream-users', function() {

	// ルートの後で追加のヘッダーを設定する場合、ルートの実行後にそれらを定義する必要があります。
	// すべてが echo される前に、header() 関数を直接呼び出すか
	// Flight::response()->setRealHeader() を呼び出す必要があります。
	header('Content-Disposition: attachment; filename="users.json"');
	// または
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="users.json"');

	// データを取得する方法にかかわらず、例として...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0# ルーティング

> **注意:** ルーティングについてさらに理解したいですか？詳細な説明は、[フレームワークの必要性](/learn/why-frameworks) ページをご覧ください。

Flight での基本的なルーティングは、URL パターンとコールバック関数またはクラスとメソッドの配列を一致させることで行われます。

```php
Flight::route('/', function(){
    echo 'こんにちは、世界！';
});
```

> ルートは定義順に一致します。リクエストと一致する最初のルートが呼び出されます。

### コールバック/関数

コールバックは、呼び出し可能な任意のオブジェクトにできます。通常の関数を使用できます：

```php
function hello(){
    echo 'こんにちは、世界！';
}

Flight::route('/', 'hello');
```

### クラス

クラスの静的メソッドを使用することもできます：

```php
class Greeting {
    public static function hello() {
        echo 'こんにちは、世界！';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

また、最初にオブジェクトを作成してからメソッドを呼び出すこともできます：

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "こんにちは、{$this->name}さん！";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// オブジェクトを作成せずにこれを行うこともできます
// 注意: 引数はコンストラクタに注入されません
Flight::route('/', [ 'Greeting', 'hello' ]);
```

#### DIC（Dependency Injection Container）を介した依存性注入
コンテナ（PSR-11、PHP-DI、Dice など）を介して依存性注入を使用する場合、
自分でオブジェクトを作成してコンテナを使用してオブジェクトを作成するルートタイプだけが利用可能です。または、クラスと呼び出すメソッドを指定する際に文字列を使用することもできます。詳細については[Dependency Injection](/learn/extending) ページをご覧ください。

以下に簡単な例を示します：

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
		// $this->pdoWrapper を使って何かをします
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "こんにちは、世界！私の名前は {$name} です！";
	}
}

// index.php

// 必要なパラメーターでコンテナを設定します
// PSR-11 については、依存性注入ページで詳細をご覧ください
$dice = new \Dice\Dice();

// 変数を '$dice = ' で再割り当てするのを忘れないでください！！！
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// コンテナハンドラを登録します
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// 通常と同じようにルーティングを行います
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// または
Flight::route('/hello/@id', 'Greeting->hello');
// または
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## メソッドルーティング

デフォルトでは、ルートパターンはすべてのリクエストメソッドに一致します。特定のメソッドに応答するには、URL の前に識別子を配置します。

```php
Flight::route('GET /', function () {
  echo 'GET リクエストを受信しました。';
});

Flight::route('POST /', function () {
  echo 'POST リクエストを受信しました。';
});

// ルートを作成するために Flight::get() を使用することはできません
//    それはルートを作成するのではなく変数を取得するためのメソッドです。
// Flight::post('/', function() { /* コード */ });
// Flight::patch('/', function() { /* コード */ });
// Flight::put('/', function() { /* コード */ });
// Flight::delete('/', function() { /* コード */ });
```

`|` デリミタを使用して、複数のメソッドを単一のコールバックにマップすることもできます：

```php
Flight::route('GET|POST /', function () {
  echo 'GET または POST リクエストを受信しました。';
});
```

さらに、使用できるヘルパーメソッドを持つ Router オブジェクトを取得することもできます：

```php

$router = Flight::router();

// すべてのメソッドをマップします
$router->map('/', function() {
	echo 'こんにちは、世界！';
});

// GET リクエスト
$router->get('/users', function() {
	echo 'ユーザー';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## 正規表現

ルートで正規表現を使用することができます：

```php
Flight::route('/user/[0-9]+', function () {
  // これは /user/1234 に一致します
});
```

このメソッドは利用可能ですが、名前付きパラメーター、または
名前付きパラメーターと正規表現を使用することをお勧めします。それらはより読みやすく、メンテナンスが容易です。

## 名前付きパラメーター

コールバック関数に渡される名前付きパラメーターを指定できます。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "$name さん、こんにちは（$id）！";
});
```

`:` デリミタを使用して、名前付きパラメーターに正規表現を含めることもできます：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // これは /bob/123 に一致します
  // ただし /bob/12345 には一致しません
});
```

> **注意:** 名前付きパラメーターと正規表現とを組み合わせた括弧のグループ `()` はサポートされていません。 :'\(

## オプションパラメーター

マッチングをオプションにするために、セグメントを括弧で囲んで名前付きパラメーターを指定できます。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 以下の URL を一致させます：
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

一致しなかった任意のオプションパラメーターは `NULL` として渡されます。

## ワイルドカード

マッチングは個々の URL セグメントにのみ行われます。複数のセグメントを一致させたい場合は、`*` ワイルドカードを使用できます。

```php
Flight::route('/blog/*', function () {
  // これは /blog/2000/02/01 に一致します
});
```

すべてのリクエストを単一のコールバックにルーティングするには、次のようにします：

```php
Flight::route('*', function () {
  // 何かを行う
});
```

## パッシング

コールバック関数から `true` を返すことで、次の一致するルートに実行を引き継ぐことができます。

```php
Flight::route('/user/@name', function (string $name) {
  // 一部の条件を確認
  if ($name !== "Bob") {
    // 次のルートへ継続
    return true;
  }
});

Flight::route('/user/*', function () {
  // これが呼び出されます
});
```

## ルートにエイリアスを付ける

ルートにエイリアスを割り当てることができ、後でコード内で動的に URL を生成できます（たとえば、テンプレートなど）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// 何かのコードの後で
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' が返されます
```

特に URL が変更される場合に役立ちます。上記の例では、ユーザーは `/admin/users/@id` に移動されました。
エイリアスを使用すると、エイリアスを参照しているどこかすべてを変更する必要がないため、上記の例のようにエイリアスは `/admin/users/5` を返します。

エイリアスはグループ内でも機能します：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// 何かのコードの後で
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' が返されます
```

## ルート情報

一致するルート情報を調べたい場合は、`true` を第三引数に渡してコールバックにルートオブジェクトを要求することができます。ルートオブジェクトは、常にコールバック関数に渡される最後のパラメーターです。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 一致した HTTP メソッドの配列
  $route->methods;

  // 名前付きパラメーターの配列
  $route->params;

  // 一致した正規表現
  $route->regex;

  // URL パターン内の '*' の内容
  $route->splat;

  // URL パスを表示... 本当に必要ですか？
  $route->pattern;

  // このルートに割り当てられたミドルウェアを表示
  $route->middleware;

  // このルートに割り当てられたエイリアスを表示
  $route->alias;
}, true);
```

## ルートグループ化

関連するルートをまとめて（たとえば `/api/v1`）グループ化したい場合があります。
これは `group` メソッドを使用して行うことができます：

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

さらに、グループ化されたグループも作成できます：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() は変数を取得するメソッドであり、ルートを設定するメソッドではありません！オブジェクトコンテキストを参照してください
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
	// Flight::get() は変数を取得するメソッドであり、ルートを設定するメソッドではありません！オブジェクトコンテキストを参照してください
	Flight::route('GET /users', function () {
	  // GET /api/v2/users に一致します
	});
  });
});
```

### オブジェクトコンテキストを使用したグループ化

`Engine` オブジェクトを続行することで、ルートグループ化を使用できます：

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

## ストリーミング

`streamWithHeaders()` メソッドを使用してクライアントにレスポンスをストリーミングすることができます。
これは大きなファイルの送信、長時間実行されるプロセス、または大規模な応答の生成に役立ちます。
ルートのストリーミングは、通常のルートとは少し異なる方法で処理されます。

> **注意:** ストリーミングレスポンスは、[`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) を `false` に設定している場合にのみ利用できます。

```php
Flight::route('/stream-users', function() {

	// ルートの後で追加のヘッダーを設定する場合、ルートの実行後にそれらを定義する必要があります。
	// すべてが echo される前に、header() 関数を直接呼び出すか
	// Flight::response()->setRealHeader() を呼び出す必要があります。
	header('Content-Disposition: attachment; filename="users.json"');
	// または
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="users.json"');

	// データを取得する方法にかかわらず、例として...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// これはクライアントにストリーミングされるデータを送信します
		ob_flush();
	}
	echo '}';

// ストリーミングを開始する前にヘッダーを設定する方法
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// オプションのステータスコード、デフォルトは 200
	'status' => 200
]);