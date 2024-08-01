# レスポンス

Flightは、レスポンスヘッダーの一部を生成するのに役立ちますが、ユーザーに送り返す内容の大部分を制御することができます。時々、`Response`オブジェクトに直接アクセスできることがありますが、ほとんどの場合は`Flight`インスタンスを使用してレスポンスを送信します。

## 基本的なレスポンスの送信

Flightは、出力をバッファリングするためにob_start()を使用しています。これは、`echo`や`print`を使用してユーザーにレスポンスを送信し、Flightがそれをキャプチャして適切なヘッダーとともにユーザーに送り返すことができることを意味します。

```php

// これは、「こんにちは、世界！」をユーザーのブラウザに送信します
Flight::route('/', function() {
	echo "こんにちは、世界！";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// こんにちは、世界！
```

代わりに、`write()`メソッドを呼び出して本文に追加することもできます。

```php

// これは、「こんにちは、世界！」をユーザーのブラウザに送信します
Flight::route('/', function() {
	// 煩雑ですが、必要な場合には仕事を行います
	Flight::response()->write("こんにちは、世界！");

	// この時点で設定した本文を取得したい場合
	// 以下のようにすることができます
	$body = Flight::response()->getBody();
});
```

## ステータスコード

`status`メソッドを使用してレスポンスのステータスコードを設定できます。

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "こんにちは、世界！";
	} else {
		Flight::response()->status(403);
		echo "禁止されています";
	}
});
```

現在のステータスコードを取得したい場合は、引数なしで`status`メソッドを使用できます。

```php
Flight::response()->status(); // 200
```

## レスポンスボディの設定

`write`メソッドを使用してレスポンスボディを設定できますが、`echo`または`print`すると、出力バッファリングを介してレスポンスボディとしてキャプチャされます。

```php
Flight::route('/', function() {
	Flight::response()->write("こんにちは、世界！");
});

// 以下と同じ

Flight::route('/', function() {
	echo "こんにちは、世界！";
});
```

### レスポンスボディのクリア

レスポンスボディをクリアしたい場合は、`clearBody`メソッドを使用できます。

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("こんにちは、世界！");
	} else {
		Flight::response()->clearBody();
	}
});
```

### レスポンスボディでコールバックを実行

`addResponseBodyCallback`メソッドを使用して、レスポンスボディにコールバックを実行できます。

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// これにより、どのルートに対してもすべての応答をgzip形式にします
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

複数のコールバックを追加することができ、追加された順に実行されます。これは任意の [callable](https://www.php.net/manual/en/language.types.callable.php) を受け入れるため、クラス配列 `[ $class, 'method' ]`、クロージャ `$strReplace = function($body) { str_replace('hi', 'there', $body); };`、または関数名 `'minify'` を受け入れることができます（たとえば、HTMLコードを縮小化する関数がある場合など）。

**注意:** `flight.v2.output_buffering`構成オプションを使用している場合、ルートコールバックは機能しません。

### 特定のルートコールバック

これを特定のルートにのみ適用させたい場合は、ルート自体でコールバックを追加することができます。

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// これにより、このルートへの応答だけがgzip形式になります
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### ミドルウェアオプション

すべてのルートに対してコールバックを適用するためにミドルウェアを使用することもできます。

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// ここでresponse()オブジェクトに対してコールバックを適用します
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// 何らかの方法で本文を縮小化します
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## レスポンスヘッダーの設定

`header`メソッドを使用して、レスポンスのコンテンツタイプなどのヘッダーを設定できます。

```php

// これは「こんにちは、世界！」をユーザーのブラウザにプレーンテキストとして送信します
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// または
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "こんにちは、世界！";
});
```

## JSON

Flightは、JSONおよびJSONP応答の送信をサポートしています。 JSON応答を送信するには、JSONにエンコードするデータを渡します。

```php
Flight::json(['id' => 123]);
```

### ステータスコード付きのJSON

2番目の引数としてステータスコードを渡すこともできます。

```php
Flight::json(['id' => 123], 201);
```

### クリアな印刷とJSON

最後の位置に引数を渡してクリアな印刷を有効にすることもできます。

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

`Flight::json()`に渡すオプションを変更してより簡単な構文を必要とする場合は、JSONメソッドを再マッピングすることができます。

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// これで以下のように使用できます
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSONと実行の停止（v3.10.0）

JSON応答を送信して実行を停止したい場合は、`jsonHalt`メソッドを使用できます。
これは、ある種の認証をチェックして、ユーザーが認可されていない場合、即座にJSON応答を送信し、現在の本文内容をクリアして実行を停止する場合に便利です。

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// ユーザーが認可されているかどうかをチェック
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// ルートの残りの部分を続行します
});
```

v3.10.0以前では、次のようにする必要がありました:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// ユーザーが認可されているかどうかをチェック
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// ルートの残りの部分を続行します
});
```

### JSONP

JSONPリクエストの場合、コールバック関数を定義するために使用しているクエリパラメータ名をオプションで渡すことができます。

```php
Flight::jsonp(['id' => 123], 'q');
```

そのため、`?q=my_func`を使用してGETリクエストを行うと、出力が次のようになります:

```javascript
my_func({"id":123});
```

クエリパラメータ名を渡さない場合は、デフォルトで`jsonp`になります。

## 別のURLにリダイレクト

`redirect()`メソッドを使用して現在のリクエストをリダイレクトできます
新しいURLを渡します。

```php
Flight::redirect('/new/location');
```

デフォルトでは、FlightはHTTP 303（「他を参照してください」）ステータスコードを送信します。カスタムコードを設定することもできます。

```php
Flight::redirect('/new/location', 401);
```

## 停止

`halt`メソッドを呼び出すことで、いつでもフレームワークを停止できます。

```php
Flight::halt();
```

オプションの`HTTP`ステータスコードとメッセージを指定することもできます。

```php
Flight::halt(200, 'ただいまメンテナンス中...');
```

`halt`を呼び出すことで、現在までの任意のレスポンス内容が破棄されます。フレームワークを停止して、現在のレスポンスを出力する場合は、`stop`メソッドを使用します。

```php
Flight::stop();
```

## レスポンスデータのクリア

`clear()`メソッドを使用して、レスポンスボディとヘッダーをクリアできます。 これにより、レスポンスに割り当てられた任意のヘッダーがクリアされ、レスポンス本体がクリアされ、ステータスコードが`200`に設定されます。

```php
Flight::response()->clear();
```

### レスポンスボディのみのクリア

レスポンスボディだけをクリアしたい場合は、`clearBody()`メソッドを使用できます。

```php
// これにより、response()オブジェクトに設定された任意のヘッダーが保持されます。
Flight::response()->clearBody();
```

## HTTPキャッシュ

Flightには、HTTPレベルのキャッシュを簡単に行うための組込みのサポートがあります。 キャッシュ条件が満たされると、FlightはHTTP`304 Not Modified`応答を返します。 クライアントが同じリソースを再リクエストすると、ローカルにキャッシュされたバージョンを使用するよう促されます。

### ルートレベルのキャッシュ

全体のレスポンスをキャッシュしたい場合は、`cache()`メソッドを使用し、キャッシュする時間を渡すことができます。

```php

// これにより、レスポンスが 5 分間キャッシュされます
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'このコンテンツはキャッシュされます。';
});

// 代わりに、strtotime()メソッドに渡す文字列を使用することもできます
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'このコンテンツはキャッシュされます。';
});
```

### 最終変更

`lastModified`メソッドを使用して、ページが最終に変更された日付と時刻を設定できます。 クライアントは、最終変更値が変更されるまでキャッシュを維持します。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'このコンテンツはキャッシュされます。';
});
```

### ETag

`ETag`キャッシングは、`Last-Modified`と似ていますが、リソースに対して任意のIDを指定できます。

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'このコンテンツはキャッシュされます。';
});
```

`lastModified`または`etag`を呼び出すと、キャッシュ値が設定およびチェックされます。 リクエスト間でキャッシュ値が同じ場合、Flightは即座に`HTTP 304`応答を送信して処理を停止します。

### ファイルのダウンロード

ファイルをダウンロードするためのヘルパーメソッドがあります。 `download`メソッドを使用して、パスを渡すことができます。

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```