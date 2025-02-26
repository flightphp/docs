# レスポンス

Flightはレスポンスヘッダーの一部を生成するのを助けますが、ユーザーに返す内容の大部分はあなたが制御します。時々、`Response` オブジェクトに直接アクセスできますが、ほとんどの場合、レスポンスを送信するために `Flight` インスタンスを使用します。

## 基本的なレスポンスを送信する

Flightはob_start()を使用して出力をバッファリングします。これは、`echo` や `print` を使用してユーザーにレスポンスを送信でき、Flightがそれをキャプチャして適切なヘッダーを付けてユーザーに返すことを意味します。

```php

// これにより「Hello, World!」がユーザーのブラウザに送信されます
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

代わりに、`write()` メソッドを呼び出してボディに追加することもできます。

```php

// これにより「Hello, World!」がユーザーのブラウザに送信されます
Flight::route('/', function() {
	// 詳細ですが、時々必要なときに役に立ちます
	Flight::response()->write("Hello, World!");

	// この時点で設定したボディを取得したい場合
	// そのようにすることができます
	$body = Flight::response()->getBody();
});
```

## ステータスコード

`status` メソッドを使ってレスポンスのステータスコードを設定できます：

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

現在のステータスコードを取得したい場合は、引数なしで `status` メソッドを使用できます：

```php
Flight::response()->status(); // 200
```

## レスポンスボディの設定

`write` メソッドを使用してレスポンスボディを設定できますが、`echo` や `print` を使うと、
それがキャプチャされ、出力バッファリングを通じてレスポンスボディとして送信されます。

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// 同じ内容として

Flight::route('/', function() {
	echo "Hello, World!";
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

### レスポンスボディでコールバックを実行する

`addResponseBodyCallback` メソッドを使用して、レスポンスボディでコールバックを実行できます：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// これにより、すべてのルートのレスポンスがgzipされます
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

複数のコールバックを追加できますが、それらは追加された順に実行されます。これは任意の [呼び出し可能](https://www.php.net/manual/en/language.types.callable.php) を受け入れるため、クラスの配列 `[ $class, 'method' ]`、クロージャ `$strReplace = function($body) { str_replace('hi', 'there', $body); };`、またはHTMLコードを最小化する関数がある場合の関数名 `'minify'` を受け入れます。

**注:** ルートコールバックは、`flight.v2.output_buffering` 設定オプションを使用している場合は機能しません。

### 特定のルートコールバック

これを特定のルートのみに適用したい場合は、ルート自体にコールバックを追加できます：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// これにより、このルートのレスポンスのみがgzipされます
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### ミドルウェアオプション

ミドルウェアを使用して、すべてのルートにコールバックを適用することもできます：

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// レスポンス() オブジェクトにここでコールバックを適用します。
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// ボディを何らかの方法で最小化します
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

`header` メソッドを使用してレスポンスのコンテンツタイプなどのヘッダーを設定できます：

```php

// これにより、「Hello, World!」がユーザーのブラウザにプレーンテキストで送信されます
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// または
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

FlightはJSONおよびJSONPレスポンスの送信をサポートしています。JSONレスポンスを送信するには、JSONエンコードされるデータを渡します：

```php
Flight::json(['id' => 123]);
```

> **注意:** デフォルトでは、Flightはレスポンスとともに`Content-Type: application/json`ヘッダーを送信します。また、JSONをエンコードする際に`JSON_THROW_ON_ERROR`および`JSON_UNESCAPED_SLASHES`定数も使用します。

### ステータスコード付きのJSON

第2引数としてステータスコードを渡すこともできます：

```php
Flight::json(['id' => 123], 201);
```

### プレティプリント付きのJSON

最後の位置に引数を渡すことで、プレティプリントを有効にすることもできます：

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

`Flight::json()`に渡されるオプションを簡潔な構文に変更したい場合、単にJSONメソッドを再マッピングできます：

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// そして、次のように使用できます
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSONと実行の停止 (v3.10.0)

JSONレスポンスを送信し、実行を停止したい場合は、`jsonHalt` メソッドを使用できます。これは、ユーザーが承認されていない場合などに、既存のボディ内容をクリアして実行を停止するのに便利です。

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// ユーザーが承認されているか確認します
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// ルートの残りの部分を続けます
});
```

v3.10.0以前は、次のようにする必要がありました：

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// ユーザーが承認されているか確認します
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// ルートの残りの部分を続けます
});
```

### JSONP

JSONPリクエストの場合、コールバック関数を定義するために使用するクエリパラメータ名をオプションで渡すことができます：

```php
Flight::jsonp(['id' => 123], 'q');
```

したがって、`?q=my_func`を使用してGETリクエストを行うと、次の出力を受け取るべきです：

```javascript
my_func({"id":123});
```

クエリパラメータ名を渡さない場合は、デフォルトで`jsonp`になります。

## 別のURLにリダイレクト

`redirect()` メソッドを使用して現在のリクエストをリダイレクトし、新しいURLを渡すことができます：

```php
Flight::redirect('/new/location');
```

デフォルトでは、FlightはHTTP 303 ("See Other") ステータスコードを送信します。オプションでカスタムコードを設定できます：

```php
Flight::redirect('/new/location', 401);
```

## 停止

`halt` メソッドを呼び出すことで、フレームワークを任意の時点で停止できます：

```php
Flight::halt();
```

オプションでHTTPステータスコードとメッセージを指定できます：

```php
Flight::halt(200, 'すぐに戻ります...');
```

`halt`を呼び出すと、その時点までのレスポンスコンテンツはすべて破棄されます。フレームワークを停止して現在のレスポンスを出力したい場合は、`stop` メソッドを使用します：

```php
Flight::stop();
```

## レスポンスデータのクリア

`clear()` メソッドを使用してレスポンスボディとヘッダーをクリアできます。これにより、レスポンスに割り当てられたすべてのヘッダーがクリアされ、レスポンスボディがクリアされ、ステータスコードが `200` に設定されます。

```php
Flight::response()->clear();
```

### レスポンスボディのみをクリア

レスポンスボディのみをクリアしたい場合、`clearBody()` メソッドを使用できます：

```php
// これにより、レスポンス()オブジェクトに設定されたヘッダーは保持されます。
Flight::response()->clearBody();
```

## HTTPキャッシング

FlightはHTTPレベルのキャッシングを標準でサポートしています。キャッシング条件が満たされると、FlightはHTTP `304 Not Modified` レスポンスを返します。次回クライアントが同じリソースをリクエストすると、ローカルにキャッシュされたバージョンを使用するように促されます。

### ルートレベルキャッシング

レスポンス全体をキャッシュしたい場合、`cache()` メソッドを使用し、キャッシュする時間を渡すことができます。

```php

// これにより、レスポンスが5分間キャッシュされます
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'このコンテンツはキャッシュされます。';
});

// また、strtotime()メソッドに渡す文字列を使用することもできます
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'このコンテンツはキャッシュされます。';
});
```

### 最終更新日時

`lastModified` メソッドを使用し、UNIXタイムスタンプを渡してページが最終更新された日付と時刻を設定できます。クライアントは最終更新値が変更されるまでキャッシュを使用し続けます。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'このコンテンツはキャッシュされます。';
});
```

### ETag

`ETag`キャッシングは`Last-Modified`に似ていますが、リソース用の任意のIDを指定できます：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'このコンテンツはキャッシュされます。';
});
```

`lastModified`または`etag`を呼び出すと、両方のキャッシュ値が設定され、確認されます。リクエスト間でキャッシュ値が同じ場合、Flightは即座に`HTTP 304`レスポンスを送信し、処理を停止します。

## ファイルのダウンロード (v3.12.0)

ファイルをダウンロードするためのヘルパーメソッドがあります。`download`メソッドを使用し、パスを渡すできます。

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```