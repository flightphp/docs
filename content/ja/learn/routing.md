# ルーティング

> **注:** ルーティングについてもっと理解したいですか？詳細な説明については、[なぜフレームワーク](/learn/why-frameworks) ページをチェックしてください。

Flight における基本的なルーティングは、URL パターンをコールバック関数またはクラスとメソッドの配列と一致させることで行います。

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

コールバックはコール可能な任意のオブジェクトであることができます。そのため、通常の関数を使用できます:

```php
function hello(){
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

またはクラスメソッドを使用できます:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

あるいはオブジェクトメソッドを使用できます:

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

ルートは定義された順に一致します。リクエストに一致する最初のルートが呼び出されます。

## メソッドルーティング

デフォルトでは、ルートパターンはすべてのリクエストメソッドに一致します。特定のメソッドに応答することができます
URL の前に識別子を配置することで。

```php
Flight::route('GET /', function () {
  echo 'GET リクエストを受け取りました。';
});

Flight::route('POST /', function () {
  echo 'POST リクエストを受け取りました。';
});
```

`|` デリミタを使用して単一のコールバックに複数のメソッドをマッピングすることもできます:

```php
Flight::route('GET|POST /', function () {
  echo 'GET または POST リクエストを受け取りました。';
});
```

さらに、いくつかのヘルパーメソッドを持つ `Router` オブジェクトを取得することもできます:

```php

$router = Flight::router();

// すべてのメソッドをマッピング
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

ルートで正規表現を使用することができます:

```php
Flight::route('/user/[0-9]+', function () {
  // これは /user/1234 に一致します
});
```

この方法は利用可能ですが、名前付きパラメータ、または
より読みやすく、メンテナンスしやすい正規表現と共に名前付きパラメータを使用することをお勧めします。

## 名前付きパラメータ

コールバック関数に送信される名前付きパラメータを指定できます。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "こんにちは、$name ($id)!";
});
```

 `:` デリミタを使用して、名前付きパラメータと正規表現を組み合わせることもできます:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // これは /bob/123 に一致します
  // ただし /bob/12345 には一致しません
});
```

> **注:** 名前付きパラメータと正規表現のグループ `()` を一致させることはサポートされていません。 :'\(

## オプションパラメータ

一致のためにオプションの名前付きパラメータを指定できます。
セグメントを括弧で囲むことで、一致しないオプションのパラメータを指定できます。

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

一致しなかったオプションのパラメータは `NULL` で渡されます。

## ワイルドカード

一致は個々のURLセグメントでのみ行われます。複数のセグメントに一致させたい場合は、 `*` ワイルドカードを使用できます。

```php
Flight::route('/blog/*', function () {
  // これは /blog/2000/02/01 に一致します
});
```

すべてのリクエストを単一のコールバックにルーティングするには、次のようにします:

```php
Flight::route('*', function () {
  // 何かを実行
});
```

## パス

コールバック関数から `true` を返すことで、次の一致するルートに実行を進めることができます。

```php
Flight::route('/user/@name', function (string $name) {
  // ある条件を確認
  if ($name !== "Bob") {
    // 次のルートに続行
    return true;
  }
});

Flight::route('/user/*', function () {
  // これが呼び出されます
});
```

## ルートエイリアス

ルートにエイリアスを割り当てることができるため、URL を動的に生成できます（たとえば、テンプレートの場合など）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// あとでコードのどこかで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します
```

URL が変更された場合、特に役立ちます。上記の例では、users が `/admin/users/@id` に移動したとします。
エイリアスを使用すると、エイリアスを参照するすべての場所を変更する必要がないため、上記のようにエイリアスが `/admin/users/5` を返すことができます。

ルートエイリアスはグループでも機能します:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// あとでコードのどこかで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します
```

## ルート情報

一致するルート情報を検査したい場合は、ルートメソッドの第三引数として `true` を渡すことで、ルートオブジェクトをコールバックに渡すことができます。ルートオブジェクトは、常にコールバック関数に渡される最後のパラメータです。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 一致した HTTP メソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // 一致する正規表現
  $route->regex;

  // URL パターンで使用される '*' の内容
  $route->splat;

  // URL パスを表示します...実際に必要な場合は
  $route->pattern;

  // このルートに割り当てられたミドルウェアを示します
  $route->middleware;

  // このルートに割り当てられたエイリアスを示します
  $route->alias;
}, true);
```

## ルートグループ

関連するルートをまとめてグループ化したいときがあります（たとえば `/api/v1` のように）。これは `group` メソッドを使用して行うことができます:

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

さらに、グループのグループをネストすることもできます:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() は変数を取得しますが、ルートを設定しません！以下のオブジェクトコンテキストを参照してください
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

	// Flight::get() は変数を取得しますが、ルートを設定しません！以下のオブジェクトコンテキストを参照してください
	Flight::route('GET /users', function () {
	  // GET /api/v2/users に一致します
	});
  });
});
```

### オブジェクトコンテキストでグループ化

次のように `Engine` オブジェクトを使用したルートグループ化を行うことができます:

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