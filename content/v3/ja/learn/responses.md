# レスポンス

## 概要

Flight はレスポンスヘッダーの一部を生成するのを手伝いますが、ユーザーに返す内容のほとんどの制御はあなたが持ちます。通常は `response()` オブジェクトに直接アクセスしますが、Flight にはレスポンスヘッダーの一部を設定するためのヘルパーメソッドもあります。

## 理解

ユーザーが [request](/learn/requests) リクエストをアプリケーションに送信した後、彼らに適切なレスポンスを生成する必要があります。彼らは好みの言語、特定の種類の圧縮を扱えるかどうか、ユーザーエージェントなどを含む情報を送信してきました。すべてを処理した後、適切なレスポンスを彼らに返します。これはヘッダーの設定、HTML や JSON のボディを出力、またはページへのリダイレクトです。

## 基本的な使用方法

### レスポンスボディの送信

Flight は出力のバッファリングに `ob_start()` を使用します。これにより、`echo` や `print` を使用してユーザーにレスポンスを送信でき、Flight がそれをキャプチャして適切なヘッダーと共にユーザーに返します。

```php
// This will send "Hello, World!" to the user's browser
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

代替として、`write()` メソッドを呼び出してボディに追加することもできます。

```php
// This will send "Hello, World!" to the user's browser
Flight::route('/', function() {
	// verbose, but gets the job sometimes when you need it
	Flight::response()->write("Hello, World!");

	// if you want to retrieve the body that you've set at this point
	// you can do so like this
	$body = Flight::response()->getBody();
});
```

### JSON

Flight は JSON および JSONP レスポンスの送信をサポートします。JSON レスポンスを送信するには、JSON エンコードされるデータを渡します：

```php
Flight::route('/@companyId/users', function(int $companyId) {
	// somehow pull out your users from a database for example
	$users = Flight::db()->fetchAll("SELECT id, first_name, last_name FROM users WHERE company_id = ?", [ $companyId ]);

	Flight::json($users);
});
// [{"id":1,"first_name":"Bob","last_name":"Jones"}, /* more users */ ]
```

> **Note:** By default, Flight will send a `Content-Type: application/json` header with the response. It will also use the flags `JSON_THROW_ON_ERROR` and `JSON_UNESCAPED_SLASHES` when encoding the JSON.

#### ステータスコード付き JSON

2 番目の引数としてステータスコードを渡すこともできます：

```php
Flight::json(['id' => 123], 201);
```

#### プリティプリント付き JSON

最後の位置に引数を渡してプリティプリントを有効にすることもできます：

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

#### JSON 引数の順序変更

`Flight::json()` は非常に古いメソッドですが、Flight の目標はプロジェクトの後方互換性を維持することです。引数の順序を変更してよりシンプルな構文を使用したい場合、JSON メソッドを他の Flight メソッドと同様に [remap](/learn/extending) するだけです：

```php
Flight::map('json', function($data, $code = 200, $options = 0) {

	// now you don't have to `true, 'utf-8'` when using the json() method!
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// And now it can be used like this
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

#### JSON と実行の停止

_v3.10.0_

JSON レスポンスを送信して実行を停止したい場合、`jsonHalt()` メソッドを使用できます。これは、承認などのチェックを行い、ユーザーが承認されていない場合に JSON レスポンスを即座に送信し、既存のボディコンテンツをクリアして実行を停止するのに便利です。

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Check if the user is authorized
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
		// no exit; needed here.
	}

	// Continue with the rest of the route
});
```

v3.10.0 以前では、以下のようにする必要がありました：

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Check if the user is authorized
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// Continue with the rest of the route
});
```

### レスポンスボディのクリア

レスポンスボディをクリアしたい場合、`clearBody` メソッドを使用できます：

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

上記の使用例は一般的ではないかもしれませんが、[middleware](/learn/middleware) で使用される場合に一般的になる可能性があります。

### レスポンスボディに対するコールバックの実行

`addResponseBodyCallback` メソッドを使用して、レスポンスボディにコールバックを実行できます：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// This will gzip all the responses for any route
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

複数のコールバックを追加でき、追加された順序で実行されます。これには任意の [callable](https://www.php.net/manual/en/language.types.callable.php) を受け入れるため、クラス配列 `[ $class, 'method' ]`、クロージャ `$strReplace = function($body) { str_replace('hi', 'there', $body); };`、または HTML コードを最小化するための関数名 `'minify'` などを渡せます。

**Note:** Route callbacks will not work if you are using the `flight.v2.output_buffering` configuration option.

#### 特定のルートコールバック

これを特定のルートにのみ適用したい場合、ルート内でコールバックを追加できます：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// This will gzip only the response for this route
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

#### ミドルウェアオプション

[middleware](/learn/middleware) を使用して、すべてのルートにコールバックをミドルウェア経由で適用することもできます：

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Apply the callback here on the response() object.
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// minify the body somehow
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

### ステータスコード

`status` メソッドを使用して、レスポンスのステータスコードを設定できます：

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hello, World!";
	} else {
		Flight::response()->status(403);
		echo "Forbidden";
	}
});
```

現在のステータスコードを取得したい場合、引数なしで `status` メソッドを使用できます：

```php
Flight::response()->status(); // 200
```

### レスポンスヘッダーの設定

`header` メソッドを使用して、レスポンスのコンテンツタイプなどのヘッダーを設定できます：

```php
// This will send "Hello, World!" to the user's browser in plain text
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// or
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

### リダイレクト

`redirect()` メソッドを使用して現在のリクエストをリダイレクトでき、新しい URL を渡します：

```php
Flight::route('/login', function() {
	$username = Flight::request()->data->username;
	$password = Flight::request()->data->password;
	$passwordConfirm = Flight::request()->data->password_confirm;

	if($password !== $passwordConfirm) {
		Flight::redirect('/new/location');
		return; // this is necessary so functionality below doesn't execute
	}

	// add the new user...
	Flight::db()->runQuery("INSERT INTO users ....");
	Flight::redirect('/admin/dashboard');
});
```

> **Note:** By default Flight sends a HTTP 303 ("See Other") status code. You can optionally set a
custom code:

```php
Flight::redirect('/new/location', 301); // permanent
```

### ルート実行の停止

`halt` メソッドを呼び出して、任意の時点でフレームワークを停止して即座に終了できます：

```php
Flight::halt();
```

オプションの `HTTP` ステータスコードとメッセージを指定することもできます：

```php
Flight::halt(200, 'Be right back...');
```

`halt` を呼び出すと、その時点までのレスポンスコンテンツを破棄し、すべての実行を停止します。フレームワークを停止して現在のレスポンスを出力したい場合、`stop` メソッドを使用します：

```php
Flight::stop($httpStatusCode = null);
```

> **Note:** `Flight::stop()` has some odd behavior such as it will output the response but continue executing your script which might not be what you are after. You can use `exit` or `return` after calling `Flight::stop()` to prevent further execution, but it is generally recommended to use `Flight::halt()`. 

This will save the header key and value to the response object. At the end of the request lifecycle
it will build the headers and send a response.

## 高度な使用方法

### ヘッダーの即時送信

ヘッダーでカスタムなことをする必要があり、作業中のそのコード行でヘッダーを送信する必要がある場合があります。[streamed route](/learn/routing) を設定する場合、これが必要です。これは `response()->setRealHeader()` で達成できます。

```php
Flight::route('/', function() {
	Flight::response()->setRealHeader('Content-Type: text/plain');
	echo 'Streaming response...';
	sleep(5);
	echo 'Done!';
})->stream();
```

### JSONP

JSONP リクエストの場合、コールバック関数を定義するためのクエリパラメータ名をオプションで渡せます：

```php
Flight::jsonp(['id' => 123], 'q');
```

したがって、`?q=my_func` を使用した GET リクエストの場合、出力は以下のようになります：

```javascript
my_func({"id":123});
```

クエリパラメータ名を渡さない場合、デフォルトで `jsonp` になります。

> **Note:** If you are still using JSONP requests in 2025 and beyond, hop in the chat and tell us why! We love hearing some good battle/horror stories!

### レスポンスデータのクリア

`clear()` メソッドを使用して、レスポンスボディとヘッダーをクリアできます。これはレスポンスに割り当てられたすべてのヘッダーをクリアし、レスポンスボディをクリアし、ステータスコードを `200` に設定します。

```php
Flight::response()->clear();
```

#### レスポンスボディのみのクリア

レスポンスボディのみをクリアしたい場合、`clearBody()` メソッドを使用できます：

```php
// This will still keep any headers set on the response() object.
Flight::response()->clearBody();
```

### HTTP キャッシング

Flight は HTTP レベルのキャッシングのビルトインサポートを提供します。キャッシング条件が満たされた場合、Flight は HTTP `304 Not Modified` レスポンスを返します。次にクライアントが同じリソースをリクエストすると、ローカルにキャッシュされたバージョンの使用が促されます。

#### ルートレベルのキャッシング

レスポンス全体をキャッシュしたい場合、`cache()` メソッドを使用してキャッシュ時間を渡せます。

```php

// This will cache the response for 5 minutes
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// Alternatively, you can use a string that you would pass
// to the strtotime() method
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

`lastModified` メソッドを使用して、ページが最後に変更された日時を UNIX タイムスタンプで設定できます。クライアントは最後に変更された値が変更されるまでキャッシュを使用し続けます。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

`ETag` キャッシングは `Last-Modified` に似ていますが、リソースに任意の ID を指定できます：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

`lastModified` または `etag` のいずれかを呼び出すと、両方ともキャッシュ値を設定してチェックします。リクエスト間でキャッシュ値が同じ場合、Flight は即座に `HTTP 304` レスポンスを送信して処理を停止します。

### ファイルのダウンロード

_v3.12.0_

エンドユーザーにファイルをストリーミングするためのヘルパーメソッドがあります。`download` メソッドを使用してパスを渡せます。

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
  // As of v3.17.1 you can specify a custom filename for the download
  Flight::download('/path/to/file.txt', 'custom_name.txt');
});
```

## 関連項目
- [Routing](/learn/routing) - How to map routes to controllers and render views.
- [Requests](/learn/requests) - Understanding how to handle incoming requests.
- [Middleware](/learn/middleware) - Using middleware with routes for authentication, logging, etc.
- [Why a Framework?](/learn/why-frameworks) - Understanding the benefits of using a framework like Flight.
- [Extending](/learn/extending) - How to extend Flight with your own functionality.

## トラブルシューティング
- If you're having trouble with redirects not working, make sure you add a `return;` to the method.
- `stop()` and `halt()` are not the same thing. `halt()` will stop execution immediately, while `stop()` will allow execution to continue.

## Changelog
- v3.17.1 - Added `$fileName` to `downloadFile()` method.
- v3.12.0 - Added downloadFile helper method.
- v3.10.0 - Added `jsonHalt`.
- v1.0 - Initial release.