# ルーティング

> **注意：** ルーティングについてさらに理解したいですか？詳しい説明については、["なぜフレームワーク？"](/learn/why-frameworks)ページをチェックしてください。

Flightでの基本的なルーティングは、URLパターンをコールバック関数またはクラスとメソッドの配列と一致させることで行われます。

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

コールバックは、呼び出し可能な任意のオブジェクトであることができます。そのため、通常の関数を使用できます：

```php
function hello(){
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

または、クラスのメソッド：

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

またはオブジェクトのメソッド：

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

ルートは定義された順に一致します。リクエストに一致した最初のルートが呼び出されます。

## メソッドルーティング

デフォルトでは、ルートパターンはすべてのリクエストメソッドと一致します。特定のメソッドに応答するには、URLの前に識別子を配置します。

```php
Flight::route('GET /', function () {
  echo 'GETリクエストを受信しました。';
});

Flight::route('POST /', function () {
  echo 'POSTリクエストを受信しました。';
});
```

`|`デリミタを使用して、単一のコールバックに複数のメソッドをマッピングすることもできます：

```php
Flight::route('GET|POST /', function () {
  echo 'GETまたはPOSTリクエストを受信しました。';
});
```

さらに、使用できるヘルパーメソッドを持つRouterオブジェクトを取得することもできます：

```php

$router = Flight::router();

// すべてのメソッドをマッピング
$router->map('/', function() {
	echo 'hello world!';
});

// GETリクエスト
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
  // これは/user/1234に一致します。
});
```

このメソッドは利用可能ですが、可読性が向上し、メンテナンスが容易になるため、名前付きパラメータまたは名前付きパラメータと正規表現を使用することが推奨されます。

## 名前付きパラメータ

コールバック関数に渡される名前付きパラメータを指定できます。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "$name ($id)さん、こんにちは！";
});
```

名前付きパラメータには、`: `デリミタを使用して正規表現を含めることもできます：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // これは/bob/123に一致します
  // ただし/bob/12345には一致しません。
});
```

> **注意：** 名前付きパラメータと一致する正規表現グループ`()`はサポートされていません。 :'\(

## オプションパラメータ

一致するオプションの名前付きパラメータを指定できます。セグメントをかっこで囲むことで、一致するオプションのパラメータを指定できます。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 以下のURLに一致します：
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

一致しなかったオプションのパラメータは`NULL`として渡されます。

## ワイルドカード

一致は個々のURLセグメントでのみ行われます。複数のセグメントを一致させたい場合は、`*`ワイルドカードを使用できます。

```php
Flight::route('/blog/*', function () {
  // これは/blog/2000/02/01に一致します。
});
```

すべてのリクエストを個々のコールバックにマッチングするには、次のようにします：

```php
Flight::route('*', function () {
  // 何かをします
});
```

## パス

コールバック関数から`true`を返すことで、次に一致するルートに実行を渡すことができます。

```php
Flight::route('/user/@name', function (string $name) {
  // 一部の条件をチェック
  if ($name !== "Bob") {
    // 次のルートに継続
    return true;
  }
});

Flight::route('/user/*', function () {
  // これが呼び出されます
});
```

## ルートのエイリアス

ルートにエイリアスを割り当てることで、後でコード内で動的にURLを生成できます（たとえば、テンプレートなどです）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// さらに後でコードのどこかで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' が返されます
```

URLが変更された場合、特に便利です。上記の例では、usersが`/admin/users/@id`に移動したとします。
エイリアスがあると、エイリアスを参照する場所全てを変更する必要がないため、上記の例のように、
エイリアスが`/admin/users/5`を返します。

ルートにエイリアスを設定できるのはグループでも可能です：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// さらに後でコードのどこかで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' が返されます
```

## ルート情報

一致したルート情報を調査する場合は、ルートメソッドで3番目のパラメータとして`true`を渡すことで、
ルートオブジェクトをコールバックに渡すことができます。ルートオブジェクトは、常にコールバック関数に渡される
最後のパラメータとなります。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 一致したHTTPメソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // 一致する正規表現
  $route->regex;

  // URLパターンで使用される'*'の内容
  $route->splat;

  // URLパスを表示...本当に必要なら
  $route->pattern;

  // このルートに割り当てられたミドルウェアを示します
  $route->middleware;

  // このルートに割り当てられたエイリアスを示します
  $route->alias;
}, true);
```

## ルートグループ

関連するルートをまとめたい場合（たとえば、`/api/v1`など）、`group`メソッドを使用して行うことができます。

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// /api/v1/usersに一致
  });

  Flight::route('/posts', function () {
	// /api/v1/postsに一致
  });
});
```

さらに、グループ内のグループをネストすることもできます：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()が変数を取得してルートを設定するが、オブジェクトコンテキストを参照します！下記を参照
	Flight::route('GET /users', function () {
	  // GET /api/v1/usersに一致
	});

	Flight::post('/posts', function () {
	  // POST /api/v1/postsに一致
	});

	Flight::put('/posts/1', function () {
	  // PUT /api/v1/postsに一致
	});
  });
  Flight::group('/v2', function () {

	// Flight::get()が変数を取得してルートを設定するが、オブジェクトコンテキストを参照します！下記を参照
	Flight::route('GET /users', function () {
	  // GET /api/v2/usersに一致
	});
  });
});
```

### オブジェクトコンテキストを使用したグループ化

`group`メソッド内で`Engine`オブジェクトを使用することができます。

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router変数を使用します
  $router->get('/users', function () {
	// GET /api/v1/usersに一致
  });

  $router->post('/posts', function () {
	// POST /api/v1/postsに一致
  });
});
```

## ストリーミング

`streamWithHeaders()`メソッドを使用して、クライアントにレスポンスをストリーミングできます。
これは、大きなファイルの送信、長時間実行されるプロセス、または大きなレスポンスの生成に便利です。
ストリーミングルートは、通常のルートとは少し異なる方法で処理されます。

> **注意：** ストリーミングレスポンスは、[`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering)が`false`に設定されている場合にのみ利用できます。

```php
Flight::route('/stream-users', function() {

	// データを取得する方法にかかわらず、例として...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// データをクライアントに送信するには必要です
		ob_flush();
	}
	echo '}';

// ストリーミングを開始する前にヘッダーを設定する方法です。
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// オプションのステータスコード、デフォルトは200です
	'status' => 200
]);