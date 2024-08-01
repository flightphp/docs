# ルーティング

> **Note:** ルーティングについてさらに理解したいですか？ より詳しい説明については、["なぜフレームワークを使うのか？"](learn/why-frameworks) ページを参照してください。

Flight における基本的なルーティングは、URL パターンとコールバック関数またはクラスとメソッドの配列との一致によって行われます。

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> ルートは定義された順番で一致します。リクエストに一致する最初のルートが実行されます。

### コールバック/関数
コールバックは呼び出し可能な任意のオブジェクトを使用できます。よって、通常の関数を使用できます：

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### クラス
クラスの静的メソッドを使用することもできます：

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
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
        echo "Hello, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// オブジェクトを作成せずにこれを行うこともできます
// 注意：引数はコンストラクタに注入されません
Flight::route('/', [ 'Greeting', 'hello' ]);
// さらに、この短縮構文を使用することもできます
Flight::route('/', 'Greeting->hello');
// または
Flight::route('/', Greeting::class.'->hello');
```

#### DIC（Dependency Injection Container）を介した依存性の注入
コンテナ（PSR-11、PHP-DI、Dice など）を使った依存性の注入を行いたい場合、
依存性の注入を使用できる唯一のタイプのルートは、オブジェクトを直接作成して自分自身でオブジェクトを作成するコンテナを使用するか、クラスとメソッドを呼び出すために文字列を使用するルートです。詳細については[Dependency Injection](/learn/extending) ページを参照してください。

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
		echo "こんにちは、世界！ 私の名前は {$name} です！";
	}
}

// index.php

// 必要なパラメータを使用してコンテナを設定します
// PSR-11 の詳細については、Dependency Injection ページを参照してください
$dice = new \Dice\Dice();

// 忘れずに変数を `$dice= ` で再割り当てしてください！！！！
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

// 通常通りルートを設定します
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// または
Flight::route('/hello/@id', 'Greeting->hello');
// または
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## メソッドルーティング

デフォルトでは、ルートパターンはすべてのリクエストメソッドと一致します。特定のメソッドに応答することができます
リクエストを URL の前に識別子を置くことで。

```php
Flight::route('GET /', function () {
  echo 'GET リクエストを受け取りました。';
});

Flight::route('POST /', function () {
  echo 'POST リクエストを受け取りました。';
});

// ルートを作成するために Flight::get() を使用することはできません
//    これはルートを作成するのではなく、変数を取得するためのメソッドです。
// Flight::post('/', function() { /* コード */ });
// Flight::patch('/', function() { /* コード */ });
// Flight::put('/', function() { /* コード */ });
// Flight::delete('/', function() { /* コード */ });
```

`|` デリミタを使用して複数のメソッドを単一のコールバックにマップすることもできます：

```php
Flight::route('GET|POST /', function () {
  echo 'GET または POST リクエストを受け取りました。';
});
```

さらに、使用するヘルパーメソッドがいくつか含まれている Router オブジェクトを取得できます：

```php

$router = Flight::router();

// すべてのメソッドにマップします
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

このメソッドは利用可能ですが、名前付きパラメータまたは正規表現付きの名前付きパラメータを使用することが推奨されています。それらはより読みやすく維持しやすいです。

## 名前付きパラメータ

コールバック関数で使用する名前付きパラメータを指定できます。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "$name ($id) さん、こんにちは！";
});
```

`:` デリミタを使用して名前付きパラメータに正規表現を含めることもできます：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // これは /bob/123 に一致します
  // ただし /bob/12345 には一致しません
});
```

> **Note:** 名前付きパラメータで正規表現のグループ `()` に一致させることはサポートされていません。 :'(

## オプションのパラメータ

一致させるためにオプションの名前付きパラメータを指定できます。一致しないオプションのパラメータは `NULL` として渡されます。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 以下の URL に一致します:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

## ワイルドカード

一致は個々の URL セグメントでのみ行われます。複数のセグメントに一致させたい場合は、`*` ワイルドカードを使用できます。

```php
Flight::route('/blog/*', function () {
  // これは /blog/2000/02/01 に一致します
});
```

すべてのリクエストを単一のコールバックにルーティングする場合は、次のようにします：

```php
Flight::route('*', function () {
  // 何かをします
});
```

## パッシング

コールバック関数から `true` を返すことで、次に一致するルートに実行を渡すことができます。

```php
Flight::route('/user/@name', function (string $name) {
  // ある条件を確認
  if ($name !== "Bob") {
    // 次のルートに続行
    return true;
  }
});

Flight::route('/user/*', function () {
  // これが呼ばれます
});
```

## ルートのエイリアス

ルートにエイリアスを割り当てることで、URL を後で動的に生成できます（たとえば、テンプレートなど）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// あとでどこかのコードで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します
```

あなたの URL が変更された場合、ルートエイリアスを使用すると、エイリアスを参照している場所全てを変更する必要がありません。
上記の例のように、ユーザーが `/admin/users/@id` に移動した場合、エイリアスを使用している場所全てを変更する必要はないため、エイリアスが非常に役立ちます。

ルートのエイリアスはグループ内でも機能します：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// あとでどこかのコードで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します
```

## ルート情報

一致するルート情報を検査したい場合は、ルートメソッドの第三引数として `true` を渡すことで、ルートオブジェクトをコールバック関数に渡すように要求することができます。 ルートオブジェクトはいつもコールバック関数に渡される最後のパラメータになります。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 一致した HTTP メソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // 一致した正規表現
  $route->regex;

  // URL パターン内で使用されている '*' の内容
  $route->splat;

  // URL パターンを表示します... 必要な場合は...
  $route->pattern;

  // このルートに割り当てられたミドルウェアを表示します
  $route->middleware;

  // このルートに割り当てられたエイリアスを表示します
  $route->alias;
}, true);
```

## ルートグループ化

関連するルートをまとめたい場合（たとえば、`/api/v1` など）、`group` メソッドを使用できます：

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

グループのグループをネストさせることもできます：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() は変数を取得しますが、ルートを設定するわけではありません！ オブジェクトコンテキストを参照してください
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

	// Flight::get() は変数を取得しますが、ルートを設定するわけではありません！ オブジェクトコンテキストを参照してください
	Flight::route('GET /users', function () {
	  // GET /api/v2/users に一致
	});
  });
});
```

### オブジェクトコンテキストを使用したグループ化

次のように、`Engine` オブジェクトを使用してルートグループ化を行うことができます：

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 変数を使用してください
  $router->get('/users', function () {
	// GET /api/v1/users に一致
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts に一致
  });
});
```

## ストリーミング

`streamWithHeaders()` メソッドを使用して、クライアントに対して応答をストリーミングで送信することができます。
これは大きなファイル、長時間実行されるプロセス、または大規模な応答を生成する場合に役立ちます。
ルートをストリーミングする場合、通常のルートよりもやや異なる方法で処理されます。

> **注意:** [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) が false に設定されている場合のみ、ストリーミング応答を使用できます。

### マニュアルヘッダー付きストリーミング

ルートで `stream()` メソッドを使用することで、クライアントに対して応答をストリーミングすることができます。これを行う場合は、クライアントに何かを出力する前に全てのメソッドを手動で設定する必要があります。 これは `header()` php 関数または `Flight::response()->setRealHeader()` メソッドで行います。

```php
Flight::route('/@filename', function($filename) {

	// パスをサニタイズする必要があります
	$fileNameSafe = basename($filename);

	// ルートの実行後にここに追加する追加ヘッダがある場合
	// 何かをエコーする前にそれ```md
# ルーティング

> **注意:** ルーティングについてさらに理解したいですか？ より詳しい説明については、["なぜフレームワークを使うのか？"](learn/why-frameworks) ページを参照してください。

Flight における基本的なルーティングは、URL パターンとコールバック関数またはクラスとメソッドの配列との一致によって行われます。

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> ルートは定義された順番で一致します。リクエストに一致する最初のルートが実行されます。

### コールバック/関数
コールバックは呼び出し可能な任意のオブジェクトを使用できます。よって、通常の関数を使用できます：

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### クラス
クラスの静的メソッドを使用することもできます：

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
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
// 注意：引数はコンストラクタに注入されません
Flight::route('/', [ 'Greeting', 'hello' ]);
// さらに、この短縮構文を使用することもできます
Flight::route('/', 'Greeting->hello');
// または
Flight::route('/', Greeting::class.'->hello');
```

#### DIC（Dependency Injection Container）を介した依存性の注入
コンテナ（PSR-11、PHP-DI、Dice など）を使った依存性の注入を行いたい場合、
依存性の注入を使用できる唯一のタイプのルートは、オブジェクトを直接作成して自分自身でオブジェクトを作成するコンテナを使用するか、クラスとメソッドを呼び出すために文字列を使用するルートです。詳細については[Dependency Injection](/learn/extending) ページを参照してください。

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
		echo "こんにちは、世界！ 私の名前は {$name} です！";
	}
}

// index.php

// 必要なパラメータを使用してコンテナを設定します
// PSR-11 の詳細については、Dependency Injection ページを参照してください
$dice = new \Dice\Dice();

// 忘れずに変数を `$dice= ` で再割り当てしてください！！！！
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

// 通常通りルートを設定します
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// または
Flight::route('/hello/@id', 'Greeting->hello');
// または
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## メソッドルーティング

デフォルトでは、ルートパターンはすべてのリクエストメソッドと一致します。特定のメソッドに応答することができます
リクエストを URL の前に識別子を置くことで。

```php
Flight::route('GET /', function () {
  echo 'GET リクエストを受け取りました。';
});

Flight::route('POST /', function () {
  echo 'POST リクエストを受け取りました。';
});

// ルートを作成するために Flight::get() を使用することはできません
//    これはルートを作成するのではなく、変数を取得するためのメソッドです。
// Flight::post('/', function() { /* コード */ });
// Flight::patch('/', function() { /* コード */ });
// Flight::put('/', function() { /* コード */ });
// Flight::delete('/', function() { /* コード */ });
```

`|` デリミタを使用して複数のメソッドを単一のコールバックにマップすることもできます：

```php
Flight::route('GET|POST /', function () {
  echo 'GET または POST リクエストを受け取りました。';
});
```

さらに、使用するヘルパーメソッドがいくつか含まれている Router オブジェクトを取得できます：

```php

$router = Flight::router();

// すべてのメソッドにマップします
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

このメソッドは利用可能ですが、名前付きパラメータまたは正規表現付きの名前付きパラメータを使用することが推奨されています。それらはより読みやすく維持しやすいです。

## 名前付きパラメータ

コールバック関数で使用する名前付きパラメータを指定できます。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "$name ($id) さん、こんにちは！";
});
```

`:` デリミタを使用して名前付きパラメータに正規表現を含めることもできます：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // これは /bob/123 に一致します
  // ただし /bob/12345 には一致しません
});
```

> **注意:** 名前付きパラメータで正規表現のグループ `()` に一致させることはサポートされていません。 :'(

## オプションのパラメータ

一致させるためにオプションの名前付きパラメータを指定できます。一致しないオプションのパラメータは `NULL` として渡されます。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 以下の URL に一致します:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

## ワイルドカード

一致は個々の URL セグメントでのみ行われます。複数のセグメントに一致させたい場合は、`*` ワイルドカードを使用できます。

```php
Flight::route('/blog/*', function () {
  // これは /blog/2000/02/01 に一致します
});
```

すべてのリクエストを単一のコールバックにルーティングする場合は、次のようにします：

```php
Flight::route('*', function () {
  // 何かをします
});
```

## パッシング

コールバック関数から `true` を返すことで、次に一致するルートに実行を渡すことができます。

```php
Flight::route('/user/@name', function (string $name) {
  // ある条件を確認
  if ($name !== "Bob") {
    // 次のルートに続行
    return true;
  }
});

Flight::route('/user/*', function () {
  // これが呼ばれます
});
```

## ルートのエイリアス

ルートにエイリアスを割り当てることで、URL を後で動的に生成できます（たとえば、テンプレートなど）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// あとでどこかのコードで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します
```

あなたの URL が変更された場合、ルートエイリアスを使用すると、エイリアスを参照している場所全てを変更する必要がありません。
上記の例のように、ユーザーが `/admin/users/@id` に移動した場合、エイリアスを使用している場所全てを変更する必要はないため、エイリアスが非常に役立ちます。

ルートエイリアスはグループ内でも機能します：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// あとでどこかのコードで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します
```

## ルート情報

一致するルート情報を検査したい場合は、ルートメソッドの第三引数として `true` を渡すことで、ルートオブジェクトをコールバック関数に渡すように要求することができます。 ルートオブジェクトはいつもコールバック関数に渡される最後のパラメータになります。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 一致した HTTP メソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // 一致した正規表現
  $route->regex;

  // URL パターン内で使用されている '*' の内容
  $route->splat;

  // URL パターンを表示します... 必要な場合は...
  $route->pattern;

  // このルートに割り当てられたミドルウェアを表示します
  $route->middleware;

  // このルートに割り当てられたエイリアスを表示します
  $route->alias;
}, true);
```

## ルートグループ化

関連するルートをまとめたい場合（たとえば、`/api/v1` など）、`group` メソッドを使用できます：

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

グループのグループをネストさせることもできます：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() は変数を取得しますが、ルートを設定するわけではありません！ オブジェクトコンテキストを参照してください
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

	// Flight::get() は変数を取得しますが、ルートを設定するわけではありません！ オブジェクトコンテキストを参照してください
	Flight::route('GET /users', function () {
	  // GET /api/v2/users に一致
	});
  });
});
```

### オブジェクトコンテキストを使用したグループ化

次のように、`Engine` オブジェクトを使用してルートグループ化を行うことができます：

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 変数を使用してください
  $router->get('/users', function () {
	// GET /api/v1/users に一致
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts に一致
  });
});
```

## ストリーミング

`streamWithHeaders()` メソッドを使用して、クライアントに対して応答をストリーミングで送信することができます。
これは大きなファイル、長時間実行されるプロセス、または大規模な応答を生成する場合に役立ちます。
ルートをストリーミングする場合、通常のルートよりもやや異なる方法で処理されます。

> **注意:** [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) が false に設定されている場合のみ、ストリーミング応答を使用できます。

### マニュアルヘッダー付きストリーミング

ルートで `stream()` メソッドを使用することで、クライアントに対して応答をストリーミングすることができます。これを行う場合は、クライアントに何かを出力する前に全てのメソッドを手動で設定する必要があります。 これは `header()` php 関数または `Flight::response()->setRealHeader()` メソッドで行います。

```php
Flight::route('/@filename', function($filename) {

	// パスをサニタイズする必要があります
	$fileNameSafe = basename($filename);

	// ルートの実行後にここに追加する追加ヘッダがある場合
	// 何かをエコーする必要があります。

   ファイルのデータを取得します
   if(empty($fileData)) {
       Flight::halt(404, 'ファイルが見つかりません');
   }

   // 必要に応じてコンテンツの長さを手動で設定します
   header('Content-Length: '.filesize($filename));

   // データをクライアントにストリーミングします
   echo $fileData;

// こちらが魔法の行です
})->stream();
```

### ヘッダー付きストリーミング

`streamWithHeaders()` メソッドを使用することで、ストリーミングを開始する前にヘッダーを設定することができます。

```php
Flight::route('/stream-users', function() {

	// ここで好きな追加ヘッダを追加できます
	// header() または Flight::response()->setRealHeader() を使用する必要があります

	// データの取得方法は問いません、とりあえずの例...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// クライアントにデータを送信するために必要です
		ob_flush();
	}
	echo '}';

// ストリーミングを開始する前にヘッダーを設定する方法
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// オプショナルなステータスコード、デフォルトは 200
	'status' => 200
]);
```