# ルーティング

> **Note:** ルーティングについてさらに理解したい場合は、["なぜフレームワークか？"](/learn/why-frameworks) ページをチェックして詳細な説明をご覧ください。

Flightにおける基本的なルーティングは、URLパターンとコールバック関数、またはクラスとメソッドの配列を一致させることで行われます。

```php
Flight::route('/', function(){
    echo 'こんにちは、世界！';
});
```

> ルートは定義された順番に一致します。要求に最初に一致したルートが呼び出されます。

### コールバック/関数
コールバックは呼び出し可能な任意のオブジェクトです。そのため、通常の関数を使用できます:

```php
function hello(){
    echo 'こんにちは、世界！';
}

Flight::route('/', 'hello');
```

### クラス
静的メソッドを使用することもできます:

```php
class Greeting {
    public static function hello() {
        echo 'こんにちは、世界！';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

また、最初にオブジェクトを作成してからメソッドを呼び出すこともできます:

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = '山田 太郎';
    }

    public function hello() {
        echo "こんにちは、{$this->name}さん！";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// オブジェクトを作成せずに行うこともできます
// 注意: コンストラクタに引数は渡されません
Flight::route('/', [ 'Greeting', 'hello' ]);
```

#### DIC（Dependency Injection Container）を介した依存性の注入
依存性の注入をコンテナ（PSR-11、PHP-DI、Diceなど）を介して使用したい場合、
オブジェクトを自分で作成してコンテナを使用してオブジェクトを作成するルートのタイプのみ、
またはクラスとメソッドを呼び出すために文字列を使用できます。詳細については、
[依存性の注入](/learn/extending)ページをご覧ください。

以下に簡単な例を示します:

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
		// $this->pdoWrapperを使って何かを行う
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "こんにちは、世界！私の名前は{$name}です！";
	}
}

// index.php

// 必要なパラメータを使用してコンテナを設定します
// PSR-11に関する詳細は、依存性の注入ページをご覧ください
$dice = new \Dice\Dice();

// !! '$dice = 'で変数を再代入するのを忘れないでください!!!!
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

// 通常のようにルートを設定します
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// または
Flight::route('/hello/@id', 'Greeting->hello');
// or
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## メソッドルーティング

デフォルトでは、ルートパターンはすべてのリクエストメソッドと一致します。あるメソッドに
応答するようにするためには、URLの前に識別子を配置します。

```php
Flight::route('GET /', function () {
  echo 'GETリクエストを受信しました。';
});

Flight::route('POST /', function () {
  echo 'POSTリクエストを受信しました。';
});

// Flight::get()は変数を取得するメソッドであり、ルートを設定するためのメソッドではありません。
// Flight::post('/', function() { /* コード */ });
// Flight::patch('/', function() { /* コード */ });
// Flight::put('/', function() { /* コード */ });
// Flight::delete('/', function() { /* コード */ });
```

`|` デリミタを使用して、1つのコールバックに複数のメソッドをマップすることもできます:

```php
Flight::route('GET|POST /', function () {
  echo 'GETまたはPOSTリクエストのいずれかを受信しました。';
});
```

さらに、利用可能なヘルパーメソッドを持つ`Router`オブジェクトを取得することもできます:

```php

$router = Flight::router();

// すべてのメソッドにマップします
$router->map('/', function() {
	echo 'こんにちは、世界！';
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

ルートに正規表現を使用することができます:

```php
Flight::route('/user/[0-9]+', function () {
  // これは /user/1234 に一致します
});
```

この方法は利用できますが、名前付きパラメータや名前付きパラメータと正規表現を使用することが推奨されます。
可読性が高く、メンテナンスがしやすいです。

## 名前付きパラメータ

コールバック関数に渡される名前付きパラメータを指定することができます。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "こんにちは、$nameさん（$id）！";
});
```

`:` デリミタを使用して名前付きパラメータに正規表現を含めることもできます:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // /bob/123 に一致します
  // ただし、/bob/12345 には一致しません
});
```

> **Note:** 名前付きパラメータと一致する正規表現グループ `()` はサポートされていません。 :'\(

## オプションパラメータ

マッチングがオプションであることを明示するために、セグメントをカッコで括り、名前付きパラメータを指定できます。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 次のURLに一致します:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

一致しなかったオプションパラメータは `NULL` として渡されます。

## ワイルドカード

マッチングは個々のURLセグメントでのみ行われます。複数のセグメントにマッチさせたい場合は `*` ワイルドカードを使用できます。

```php
Flight::route('/blog/*', function () {
  // これは /blog/2000/02/01 に一致します
});
```

すべてのリクエストを単一のコールバックにルーティングするには、次のようにします:

```php
Flight::route('*', function () {
  // 何かを実行します
});
```

## パススルー

コールバック関数から `true` を返すことで、次の一致するルートに実行を移すことができます。

```php
Flight::route('/user/@name', function (string $name) {
  // ある条件をチェック
  if ($name !== "太郎") {
    // 次のルートに続行
    return true;
  }
});

Flight::route('/user/*', function () {
  // これが呼び出されます
});
```

## ルートのエイリアス

ルートにエイリアスを割り当てて、後でコード内で動的にURLを生成できるようにすることができます（例: テンプレートなど）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// あとでコードのある場所で
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します
```

URLが変更される可能性がある場合に特に便利です。上記の例では、ユーザーが `/admin/users/@id` に移動した場合でも、
エイリアスを参照している場所を変更する必要がないため、エイリアスは便利です。

グループ内でもルートエイリアスは動作します:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// あとでコードのある場所で
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します
```

## ルート情報

一致するルート情報を調査したい場合、ルートメソッドの第3引数として `true` を渡すことで、
ルートオブジェクトをコールバックに渡すように要求できます。ルートオブジェクトは、常にコールバック関数に渡される最後の引数です。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 一致したHTTPメソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // 一致する正規表現
  $route->regex;

  // URLパターンで '*' が使用されているコンテンツ
  $route->splat;

  // サイトのパスを表示します....本当に必要があれば
  $route->pattern;

  // このルートに割り当てられたミドルウェアを表示します
  $route->middleware;

  // このルートに割り当てられたエイリアスを表示します
  $route->alias;
}, true);
```

## ルートグループ化

関連するルートをまとめたい場合（たとえば `/api/v1` など）、`group` メソッドを使用できます:

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

ネストされたグループを作成することもできます:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()は変数を取得するメソッドであり、ルートを設定するメソッドではありません。オブジェクトコンテキストを参照してください
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

	// Flight::get()は変数を取得するメソッドであり、ルートを設定するメソッドではありません。オブジェクトコンテキストを参照してください
	Flight::route('GET /users', function () {
	  // GET /api/v2/users に一致します
	});
  });
});
```

### オブジェクトコンテキストを使用したグループ化

以下のように `Engine` オブジェクトでルートグループ化を使用することもできます:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router変数を使用します
  $router->get('/users', function () {
	// GET /api/v1/users に一致します
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts に一致します
  });
});
```

## ストリーミング

`streamWithHeaders()` メソッドを使用して、クライアントに対して応答をストリーミングできます。
大きなファイルの送信、長時間実行されるプロセス、または大きな応答の生成に役立ちます。
ルートのストリーミングは、通常のルートとは少し異なる方法で処理されます。

> **注意:** [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) が false に設定されている場合に限り、ストリーミング応答を使用できます。

### 手動ヘッダー付きのストリーム

コールバック関数で `stream()` メソッドを使用してクライアントに応答をストリーミングできます。 
これを行う場合、クライアントに何かを出力する前にすべてのメソッドを手動で設定する必要があります。
これは、`header()` php 関数または `Flight::response()->setRealHeader()` メソッドで行います。

```php
Flight::route('/@filename', function($filename) {

	// パスなどを適切に検証します。
	$fileNameSafe = basename($filename);

	// ルートが実行された後に追加のヘッダーを設定する場合、
	// エコーされる前にそれらを定義する必要があります。
	// すべて、'header()' 関数の生の呼び出しまたは
	// 'Flight::response()->setRealHeader()' の呼び出しで行わなければなりません。
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// または
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData```markdown
# ルーティング

> **注意:** ルーティングについて詳しく知りたい場合は、より詳細な説明のために["フレームワークの必要性は？"](/learn/why-frameworks)ページをご覧ください。

Flightでの基本的なルーティングは、URLパターンをコールバック関数またはクラスとメソッドの配列と一致させることによって行われます。

```php
Flight::route('/', function(){
    echo 'こんにちは、世界！';
});
```

> ルートは定義された順に一致します。リクエストに最初に一致したルートが呼び出されます。

### コールバック/関数
コールバックは呼び出し可能な任意のオブジェクトです。通常の関数を使用できます:

```php
function hello(){
    echo 'こんにちは、世界！';
}

Flight::route('/', 'hello');
```

### クラス
静的メソッドを使用することもできます:

```php
class Greeting {
    public static function hello() {
        echo 'こんにちは、世界！';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

または、最初にオブジェクトを作成してからメソッドを呼び出すこともできます:

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = '山田 太郎';
    }

    public function hello() {
        echo "こんにちは、{$this->name}さん！";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// オブジェクトを作成せずに行うこともできます
// 注意: 引数はコンストラクタにインジェクトされません
Flight::route('/', [ 'Greeting', 'hello' ]);
```

#### DIC（Dependency Injection Container）を介した依存関係の注入
コンテナ（PSR-11、PHP-DI、Diceなど）を介した依存性の注入を使用する場合、
オブジェクトを直接作成してコンテナを使用してオブジェクトを作成するタイプのルートだけで使用できます
または、クラスとメソッドを呼び出すために文字列を使用することができます。詳細については、
[依存性の注入](/learn/extending)ページをご覧ください。

以下に簡単な例を示します:

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
		// $this->pdoWrapperを使用して何かをする
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "こんにちは、世界！私の名前は{$name}です！";
	}
}

// index.php

// 必要なパラメータを使用してコンテナを設定します
// PSR-11に関する詳細は、依存性の注入ページをご覧ください
$dice = new \Dice\Dice();

// !! '$dice = 'で変数を再代入するのを忘れないでください!!!!
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

// 通常のようにルートを設定します
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// または
Flight::route('/hello/@id', 'Greeting->hello');
// or
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## メソッドルーティング

デフォルトでは、ルートパターンはすべてのリクエストメソッドと一致します。特定のメソッドに応答するためには、
URLの前に識別子を配置します。

```php
Flight::route('GET /', function () {
  echo 'GETリクエストを受信しました。';
});

Flight::route('POST /', function () {
  echo 'POSTリクエストを受信しました。';
});

// Flight::get()は変数を取得するメソッドであり、ルートを設定するメソッドではありません。
// Flight::post('/', function() { /* コード */ });
// Flight::patch('/', function() { /* コード */ });
// Flight::put('/', function() { /* コード */ });
// Flight::delete('/', function() { /* コード */ });
```

`|` デリミタを使用して、1つのコールバックに複数のメソッドをマップすることもできます:

```php
Flight::route('GET|POST /', function () {
  echo 'GETまたはPOSTリクエストのいずれかを受信しました。';
});
```

さらに、ヘルパーメソッドを使用するためのいくつかのヘルパーメソッドがある`Router`オブジェクトにアクセスすることもできます:

```php

$router = Flight::router();

// すべてのメソッドにマップします
$router->map('/', function() {
	echo 'こんにちは、世界！';
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

ルートに正規表現を使用することができます:

```php
Flight::route('/user/[0-9]+', function () {
  // これは /user/1234 に一致します
});
```

この方法は利用できますが、名前付きパラメータや名前付きパラメータと正規表現を使用することが推奨されます。
可読性が高く、メンテナンスがしやすいです。

## 名前付きパラメータ

コールバック関数に渡される名前付きパラメータを指定できます。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "こんにちは、$name ($id)!";
});
```

`:` デリミタを使用して名前付きパラメータに正規表現を含めることもできます:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // /bob/123 に一致します
  // ただし、/bob/12345 には一致しません
});
```

> **注意:** 名前付きパラメータと一致する正規表現グループ `()`はサポートされていません。 :'\(

## オプションのパラメータ

オプションでマッチする名前付きパラメータを指定できます。
セグメントを括弧で囲むことでオプションのパラメータを指定します。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 次のURLに一致します:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

一致しなかったオプションのパラメータは `NULL` として渡されます。

## ワイルドカード

マッチングは個々のURLセグメントでのみ行われます。複数のセグメントにマッチさせたい場合は、`*` ワイルドカードを使用できます。

```php
Flight::route('/blog/*', function () {
  // これは /blog/2000/02/01 に一致します
});
```

すべてのリクエストを単一のコールバックにルーティングするには、次のようにします:

```php
Flight::route('*', function () {
  // 何かを実行します
});
```

## パススルー

コールバック関数から `true` を返すことで、次の一致するルートに実行を移すことができます。

```php
Flight::route('/user/@name', function (string $name) {
  // ある条件をチェック
  if ($name !== "Bob") {
    // 次のルートに続行
    return true;
  }
});

Flight::route('/user/*', function () {
  // これが呼び出されます
});
```

## ルートのエイリアス

ルートにエイリアスを割り当てて、後でコード内で動的にURLを生成できるようにすることができます（例: テンプレートなど）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// 後でコードのある場所で
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します
```

URLが変更される可能性がある場合に特に便利です。上記の例では、ユーザーが `/admin/users/@id` に移動した場合でも、
エイリアスを参照している場所を変更する必要がないため、エイリアスは便利です。

ルートエイリアスはグループ内でも機能します:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// 後でコードのある場所で
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します
```

## ルート情報

マッチするルート情報を調査したい場合、ルートメソッドの第3引数として `true` を渡すことで、
ルートオブジェクトをコールバックに渡すように要求できます。ルートオブジェクトは、常にコールバック関数に渡される最後の引数です。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 一致したHTTPメソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // 一致する正規表現
  $route->regex;

  // URLパターンで '*' が使用されているコンテンツ
  $route->splat;

  // サイトのパスを表示します....本当に必要があれば
  $route->pattern;

  // このルートに割り当てられたミドルウェアを表示します
  $route->middleware;

  // このルートに割り当てられたエイリアスを表示します
  $route->alias;
}, true);
```

## ルートグループ化

関連するルートをまとめたい場合（たとえば `/api/v1` など）、`group` メソッドを使用できます:

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

ネストされたグループを作成することもできます:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()は変数を取得するメソッドであり、ルートを設定するメソッドではありません。オブジェクトコンテキストを参照してください
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

	// Flight::get()は変数を取得するメソッドであり、ルートを設定するメソッドではありません。オブジェクトコンテキストを参照してください
	Flight::route('GET /users', function () {
	  // GET /api/v2/users に一致します
	});
  });
});
```

### オブジェクトコンテキストを使用したグループ化

`Engine` オブジェクト内の `Router` 変数を使用して、`group` メソッドを使用することもできます:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router変数を使用します
  $router->get('/users', function () {
	// GET /api/v1/users に一致します
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts に一致します
  });
});
```

## ストリーミング

`streamWithHeaders()` メソッドを使用して、クライアントへ応答をストリーミングすることができます。
大きなファイル、長時間実行プロセス、または大きな応答を送信する際に有効です。
ルートのストリーミングは通常のルートとは少し異なる方法で処理されます。

> **注意:** [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) が false に設定されている場合のみ、ストリーミング応答が可能です。

### ヘッダー付きストリーミング

コールバック関数内で、`stream()` メソッドを使用してクライアントに応答をストリーミングすることができます。 
この場合、クライアントに何かを出力する前にすべてのヘッダーを手動で設定する必要があります。
これは、`header()` php 関数または `Flight::response()->setRealHeader()` メソッドを使用して行います。

```php
Flight::route('/@filename', function($filename) {

	// パスなどを適切に検証します。
	$fileNameSafe = basename($filename);

	// ルートが実行された後に追加のヘッダーを設定する場合、
	// エコーされる前にそれらを定義する必要があります。
	// すべて、'header()' 関数の生の呼び出しまたは 
	// 'Flight::response()->setRealHeader()' の呼び出しで行わなければなりません。
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'");
```