# ルーティング

> **注意:** ルーティングについてさらに理解したいですか？より詳しい説明については、["なぜフレームワーク？"](/learn/why-frameworks) ページをご覧ください。

Flight における基本的なルーティングは、URL パターンをコールバック関数またはクラスとメソッドの配列でマッチングすることで行われます。

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

コールバックは callable なオブジェクトであればどんなものでも機能します。したがって、通常の関数を使用することができます：

```php
function hello(){
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

またはクラスのメソッドを使用することもできます：

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

またはオブジェクトのメソッドを使用することもできます：

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

Flight::route('/', array($greeting, 'hello'));
```

ルートは定義された順番でマッチングされます。リクエストに一致した最初のルートが呼び出されます。

## メソッドのルーティング

デフォルトでは、ルートパターンはすべてのリクエストメソッドと一致します。特定のメソッドに応答するために、URL の前に識別子を配置することで指定できます。

```php
Flight::route('GET /', function () {
  echo 'GET リクエストを受信しました。';
});

Flight::route('POST /', function () {
  echo 'POST リクエストを受信しました。';
});
```

`|` デリミタを使用して単一のコールバックに複数のメソッドをマッピングすることもできます：

```php
Flight::route('GET|POST /', function () {
  echo 'GET または POST リクエストを受信しました。';
});
```

さらに、いくつかのヘルパーメソッドを持つ Router オブジェクトを取得することもできます：

```php

$router = Flight::router();

// すべてのメソッドをマップ
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

ルートで正規表現を使用することができます：

```php
Flight::route('/user/[0-9]+', function () {
  // /user/1234 に一致します
});
```

この方法は利用可能ですが、名前付きパラメータや名前付きパラメータに正規表現を使用することをお勧めします。それらはより読みやすく、保守しやすいからです。

## 名前付きパラメータ

コールバック関数に渡される名前付きパラメータを指定できます。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

`:` デリミタを使用して名前付きパラメータに正規表現を含めることもできます：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // /bob/123 に一致します
  // ただし /bob/12345 には一致しません
});
```

> **注意:** 名前付きパラメータと正規表現のグループ `()` の一致はサポートされていません。 :'\(

## オプションのパラメータ

マッチング用に省略可能な名前付きパラメータを指定することができます。セグメントをかっこで囲むことで省略可能なパラメータを指定します。

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

一致しなかった省略可能なパラメータは `NULL` として渡されます。

## ワイルドカード

一致は個々の URL セグメントでのみ行われます。複数のセグメントに一致させたい場合は、`*` ワイルドカードを使用できます。

```php
Flight::route('/blog/*', function () {
  // /blog/2000/02/01 に一致します
});
```

すべてのリクエストを単一のコールバックにルーティングするには、次のようにします：

```php
Flight::route('*', function () {
  // 何かを行う
});
```

## 渡す

コールバック関数から `true` を返すことで、次の一致したルートに実行を渡すことができます。

```php
Flight::route('/user/@name', function (string $name) {
  // ある条件をチェック
  if ($name !== "Bob") {
    // 次のルートに続く
    return true;
  }
});

Flight::route('/user/*', function () {
  // これが呼び出されます
});
```

## ルートにエイリアスを設定

ルートにエイリアスを割り当てることで、後でコード内で動的に URL を生成できます（たとえば、テンプレートなどで）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// あとでコードのどこかで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' が返されます
```

URL が変更される可能性がある場合に特に便利です。上記の例では、例えば、users が `/admin/users/@id` に移動した場合でも、エイリアスを使用すると、エイリアスを参照する場所全てを変更する必要はなくなります。なぜなら、エイリアスが `/admin/users/5` として返されるからです。

ルートエイリアスはグループ内でも動作します：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// あとでコードのどこかで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' が返されます
```

## ルート情報

一致したルート情報を調べたい場合は、true を第三引数として、ルートメソッドに渡すことで、コールバックにルートオブジェクトが渡されるようにできます。ルートオブジェクトは、常にコールバック関数に渡される最後のパラメータです。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 一致した HTTP メソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // 一致した正規表現
  $route->regex;

  // URL パターンで使用されている '*' の内容
  $route->splat;

  // URL パスを示します... 本当に必要がある場合にのみ
  $route->pattern;

  // このルートに割り当てられたミドルウェアを表示します。
  $route->middleware;

  // このルートに割り当てられたエイリアスを表示します。
  $route->alias;
}, true);
```

## ルートのグループ化

関連するルートをまとめて管理したい場合（たとえば `/api/v1` など）、`group` メソッドを使用してグループ化できます：

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

ネストされたグループを作成することもできます：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() は変数を取得します。ルートを設定していません！オブジェクトコンテキストを参照してください
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

	// Flight::get() は変数を取得します。ルートを設定していません！オブジェクトコンテキストを参照してください
	Flight::route('GET /users', function () {
	  // GET /api/v2/users に一致します
	});
  });
});
```

### オブジェクトコンテキストを使用したグループ化

次のように `Engine` オブジェクトを使用して、`Router` 変数を使ったルートグループ化を行うことができます：

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

`streamWithHeaders()` メソッドを使用して、クライアントに対してレスポンスをストリーミングできます。大きなファイルの送信、長時間実行されるプロセス、または大量のレスポンスの生成に役立ちます。ルートをストリーミングする場合は、通常のルートとは少し異なる方法で処理されます。

> **注意:** レスポンスのストリーミングは、[`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) が false に設定されている場合にのみ利用できます。

```php
Flight::route('/stream-users', function() {

	// データを取得する方法は何でもかまいません。例として...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// データをクライアントに送信するにはこれが必要です
		ob_flush();
	}
	echo '}';

// ストリーミングを開始する前にヘッダーを設定する方法です
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// オプションのステータスコード、デフォルトは 200
	'status' => 200
]);
```