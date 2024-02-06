# ルーティング

Flightにおけるルーティングは、URLパターンをコールバック関数と一致させることで行われます。

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

コールバックは呼び出し可能な任意のオブジェクトであることができます。ですので、通常の関数を使用することもできます:

```php
function hello(){
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

また、クラスメソッドを使用することもできます:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

オブジェクトメソッドを使用することもできます:

```php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

ルートは定義された順に一致します。リクエストに一致する最初のルートが呼び出されます。

## メソッドルーティング

デフォルトでは、ルートパターンはすべてのリクエストメソッドとマッチングされます。特定のメソッドに応答することができます。そのためにはURLの前に識別子を配置します。

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});
```

`|` デリミタを使用して、複数のメソッドを単一のコールバックにマップすることもできます:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

## 正規表現

ルートで正規表現を使用することができます。

```php
Flight::route('/user/[0-9]+', function () {
  // これは /user/1234 に一致します
});
```

## 名前付きパラメータ

ルートで名前付きパラメータを指定することができ、これらはコールバック関数に渡されます。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

`:` デリミタを使用して、名前付きパラメータに正規表現を含めることもできます:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // これは /bob/123 に一致します
  // しかし /bob/12345 には一致しません
});
```

正規表現グループ `()` と名前付きパラメータを一致させることはサポートされていません。

## オプションパラメータ

一致するオプションの名前付きパラメータを指定することができ、セグメントを括弧で囲むことで対応します。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 以下のURLに一致します:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

一致しなかったオプションのパラメータは NULL として渡されます。

## ワイルドカード

一致は個々のURLセグメントでのみ行われます。複数のセグメントを一致させたい場合は `*` ワイルドカードを使用できます。

```php
Flight::route('/blog/*', function () {
  // これは /blog/2000/02/01 に一致します
});
```

すべてのリクエストを単一のコールバックにルーティングする場合は、次のようにします:

```php
Flight::route('*', function () {
  // 何かを実行
});
```

## パッシング

コールバック関数から `true` を返すことで、次の一致するルートに実行を渡すことができます。

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

## ルート情報

一致するルート情報を調べたい場合は、ルートメソッドの3番目のパラメータとして `true` を渡すことで、ルートオブジェクトをコールバックに渡すことができます。ルートオブジェクトは、いつもコールバック関数に渡される最後のパラメータとなります。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 一致したHTTPメソッドの配列
  $route->methods;

  // 名前付きパラメータの配列
  $route->params;

  // 一致した正規表現
  $route->regex;

  // URLパターンで使用された '*' の内容を含む
  $route->splat;
}, true);
```

## ルートグループ

関連するルートをまとめたい場合（例: `/api/v1` のような場合）は、`group` メソッドを使用できます:

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

ネストされたグループを使用することもできます:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() が変数を取得しますが、ルートを設定しません！オブジェクトコンテキストが下にあります
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

	// Flight::get() が変数を取得しますが、ルートを設定しません！オブジェクトコンテキストが下にあります
	Flight::route('GET /users', function () {
	  // GET /api/v2/users に一致
	});
  });
});
```

### オブジェクトコンテキストでのグループ化

`Engine` オブジェクトを使ってルートグループ化を行うこともできます:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/users', function () {
	// GET /api/v1/users に一致
  });

  $router->post('/posts', function () {
	// POST /api/v1/posts に一致
  });
});
```

## ルートエイリアス

ルートにエイリアスを割り当てることで、後でコード内で動的にURLを生成することができます（テンプレートなど）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// どこかのコードで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します
```

URLが変更された場合に特に役立ちます。上記の例では、ユーザーが `/admin/users/@id` に移動したとします。エイリアスがあることで、エイリアスを参照するすべての場所を変更する必要はありません。なぜなら、エイリアスが `/admin/users/5` のように新しい値を返すからです。

ルートエイリアスはグループ内でも機能します:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});

// どこかのコードで
Flight::getUrl('user_view', [ 'id' => 5 ]); // '/users/5' を返します
```

## ルートミドルウェア
Flightはルートとグループルートのミドルウェアをサポートしています。ミドルウェアは、ルートコールバックの前（または後）に実行される関数です。これは、コード内でAPI認証チェックを追加したり、ユーザーがルートにアクセスする権限を検証したりするための素晴らしい方法です。

以下に基本的な例を示します:

```php
// 無名関数のみを提供する場合、ルートコールバックの前に実行されます。
// クラスには「後」のミドルウェア関数はありません（下で説明）。
Flight::route('/path', function() { echo 'ここにいます！'; })->addMiddleware(function() {
	echo '最初のミドルウェア！';
});

Flight::start();

// これは「最初のミドルウェア！ ここにいます！」と出力されます
```

ミドルウェアについて知っておくべきいくつかの重要なポイントがあります:
- ミドルウェア関数は、ルートに追加された順に実行されます。実行は [Slim Framework が行う方法](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work) に類似しています。
   - Befores は追加された順に実行され、Afters は逆順に実行されます。
- ミドルウェア関数が false を返すと、すべての実行が停止され、403禁止エラーが発生します。より適切に、`Flight::redirect()` や類似の方法でこれを優雅に処理する必要があります。
- ルートからパラメータが必要な場合、これらは1つの配列としてミドルウェア関数に渡されます。（`function($params) { ... }` または `public function before($params) {}`）。これは、パラメータをグループ化し、これらのグループのいくつかでは、パラメータが実際に異なる順序で表示される可能性があり、ミドルウェア関数が誤ったパラメータを参照してしまう場合があるためです。これで、名前でアクセスできるようになります。

### ミドウェアクラス

ミドルウェアはクラスとしても登録できます。"後"の機能が必要な場合は、クラスを使用する必要があります。

```php
class MyMiddleware {
	public function before($params) {
		echo '最初のミドルウェア！';
	}

	public function after($params) {
		echo '最後のミドルウェア！';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo 'ここにいます! '; })->addMiddleware($MyMiddleware); // または ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// これは "最初のミドルウェア！ここにいます! 最後のミドルウェア！" と表示されます
```

### ミドルウェアグループ

ルートグループを追加すると、そのグループ内のすべてのルートに同じミドルウェアが追加されます。これは、例えばヘッダー内のAPIキーをチェックするために認証ミドルウェアなどによって、多くのルートをグループ化する必要がある場合に便利です。

```php

// groupメソッドの最後に追加
Flight::group('/api', function() {
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);