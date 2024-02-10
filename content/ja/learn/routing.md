# ルーティング

> **ノート:** ルーティングについてさらに理解したいですか？詳細な説明については [フレームワークの理由](/learn/why-frameworks) ページをご覧ください。

Flight での基本的なルーティングは、URL パターンをコールバック関数またはクラスとメソッドの配列と一致させることによって行われます。

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

コールバックは callable なオブジェクトであることができます。通常の関数を使用することができます:

```php
function hello(){
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

またはクラスメソッド:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

またはオブジェクトメソッド:

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

デフォルトでは、ルートパターンはすべてのリクエストメソッドに一致します。特定のメソッドに応答するには、URL の前に識別子を配置してください。

```php
Flight::route('GET /', function () {
  echo 'GET リクエストを受信しました。';
});

Flight::route('POST /', function () {
  echo 'POST リクエストを受信しました。';
});
```

`|` デリミタを使用して、複数のメソッドを単一のコールバックにマップすることもできます:

```php
Flight::route('GET|POST /', function () {
  echo 'GET または POST リクエストを受信しました。';
});
```

さらに、いくつかのヘルパーメソッドを持つ Router オブジェクトを取得することもできます:

```php

$router = Flight::router();

// すべてのメソッドをマップします
$router->map('/', function() {
	echo 'hello world!';
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

ルートで正規表現を使用することができます:

```php
Flight::route('/user/[0-9]+', function () {
  // これは /user/1234 に一致します
});
```

このメソッドは利用可能ですが、名前付きパラメータや名前付きパラメータと組み合わせた正規表現を使用することが推奨されています。読みやすく、よりメンテナンスしやすいためです。

## 名前付きパラメータ

コールバック関数に渡される名前付きパラメータを指定することができます。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "$name ($id) さん、こんにちは！";
});
```

`:delimitor` を使用して、名前付きパラメータとともに正規表現を含めることもできます:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // これは /bob/123 に一致します
  // ただし /bob/12345 には一致しません
});
```

> **ノート:** 名前付きパラメータと一致する正規表現グループ `()` はサポートされていません。 :'\(

## オプションパラメータ

オプションで一致する名前付きパラメータを指定できます。
セグメントをかっこで囲むことで、オプションパラメータを指定します。

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

一致しなかったオプションパラメータは `NULL` として渡されます。

## ワイルドカード

一致は個々の URL セグメントでのみ行われます。複数のセグメントに一致させたい場合は、`*` ワイルドカードを使用できます。

```php
Flight::route('/blog/*', function () {
  // /blog/2000/02/01 に一致します
});
```

すべてのリクエストを単一のコールバックにルーティングするには、次のようにします:

```php
Flight::route('*', function () {
  // 何かを行います
});
```

## パッシング

コールバック関数から `true` を返すことで、次の一致するルートに実行を渡すことができます。

```php
Flight::route('/user/@name', function (string $name) {
  // ある条件をチェック
  if ($name !== "Bob") {
    // 次のルートに続きます
    return true;
  }
});

Flight::route('/user/*', function () {
  // これが呼び出されます
});
```

## ルートエイリアス

ルートにエイリアスを割り当てると、後でコード内で URL を動的に生成できます (テンプレートなどで)。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// あとでコードのどこかで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' が返されます
```

URL が変更された場合でも、エイリアスが参照されている場所を変更する必要はありません。上記の例では、ユーザーが`/admin/users/@id` に移動したとします。
エイリアスが設定されている場合、エイリアスが`/admin/users/5` のように返されるため、エイリアスを参照するすべての場所を変更する必要はありません。 

ルートのエイリアスはグループ内でも機能します:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// あとでコードのどこかで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' が返されます
```

## ルート情報

一致するルート情報を検査したい場合は、第三パラメータに `true` を渡して、ルートオブジェクトをコールバック関数に渡すようにリクエストできます。ルートオブジェクトは常にコールバック関数に渡される最後のパラメータとなります。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 一致した HTTP メソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // 一致する正規表現
  $route->regex;

  // URL パターンで使用された '*' の内容
  $route->splat;

  // URL パスを表示します...本当に必要な場合に
  $route->pattern;

  // このルートに割り当てられたミドルウェアを表示します
  $route->middleware;

  // このルートに割り当てられたエイリアスを表示します
  $route->alias;
}, true);
```

## ルートのグループ化

関連するルートをグループ化したい場合 (例: `/api/v1` など)、`group` メソッドを使用できます:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// `/api/v1/users` に一致します
  });

  Flight::route('/posts', function () {
	// `/api/v1/posts` に一致します
  });
});
```

さらに、ネストされたグループを使用することもできます:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() は変数を取得しますが、ルートは設定されません！オブジェクトコンテキストを参照してください
	Flight::route('GET /users', function () {
	  // GET `/api/v1/users` に一致します
	});

	Flight::post('/posts', function () {
	  // POST `/api/v1/posts` に一致します
	});

	Flight::put('/posts/1', function () {
	  // PUT `/api/v1/posts` に一致します
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() は変数を取得しますが、ルートは設定されません！オブジェクトコンテキストを参照してください
	Flight::route('GET /users', function () {
	  // GET `/api/v2/users` に一致します
	});
  });
});
```

### オブジェクトコンテキストを使用したグループ化

以下のように `Engine` オブジェクトを使用してルートグループ化を行うことができます:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // $router 変数を使用します
  $router->get('/users', function () {
	// GET `/api/v1/users` に一致します
  });

  $router->post('/posts', function () {
	// POST `/api/v1/posts` に一致します
  });
});
```