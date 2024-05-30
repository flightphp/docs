# レスポンス

Flight はレスポンスヘッダーの一部を自動的に生成しますが、ユーザーに返す内容の大部分はあなたがコントロールします。時々 `Response` オブジェクトに直接アクセスできますが、ほとんどの場合はレスポンスを送信するために `Flight` のインスタンスを使用します。

## ベーシックなレスポンスの送信

Flight では、出力をバッファリングするために ob_start() を使用しています。つまり、`echo` や `print` を使ってユーザーにレスポンスを送信し、Flight がそれをキャプチャして適切なヘッダーと共にユーザーに送り返します。

```php

// これは "Hello, World!" をユーザーのブラウザに送ります
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

代わりに、`write()` メソッドを呼び出して本文を追加することもできます。

```php

// これは "Hello, World!" をユーザーのブラウザに送ります
Flight::route('/', function() {
	// 詳細ですが、必要な時には仕事をします
	Flight::response()->write("Hello, World!");

	// この時点で設定した本文を取得したい場合は、次のようにしてください
	$body = Flight::response()->getBody();
});
```

## ステータスコード

`status` メソッドを使用してレスポンスのステータスコードを設定できます:

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

現在のステータスコードを取得したい場合は、引数なしで `status` メソッドを使用できます:

```php
Flight::response()->status(); // 200
```

## レスポンス本体でコールバックを実行する

`addResponseBodyCallback` メソッドを使用すると、レスポンス本体でコールバックを実行できます:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// これはどのルートのレスポンスにも gzip 圧縮を適用します
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

複数のコールバックを追加でき、追加した順に実行されます。これは任意の [callable](https://www.php.net/manual/en/language.types.callable.php) を受け入れるため、「クラス配列 `[ $class, 'method' ]`」、「クロージャ `\$strReplace = function(\$body) { str_replace('hi', 'there', \$body); };`」、または関数名 `'minify'` などが使用できます。

**注意:** `flight.v2.output_buffering` 構成オプションを使用している場合、ルートコールバックは機能しません。

### 特定のルートコールバック

これを特定のルートにのみ適用したい場合、ルート自体でコールバックを追加できます:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// これはこのルートのレスポンスのみを gzip 圧縮します
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### ミドルウェアオプション

ミドルウェアを使用してすべてのルートにコールバックを適用することもできます:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		Flight::response()->addResponseBodyCallback(function($body) {
			// This is a 
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// 本文を最適化
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

`header` メソッドを使用して、レスポンスのコンテンツタイプなどのヘッダーを設定できます:

```php

// これは "Hello, World!" をプレーンテキストでユーザーのブラウザに送信します
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight は JSON と JSONP レスポンスを送信するためのサポートを提供しています。JSON レスポンスを送信するには、JSON にエンコードするデータを渡します:

```php
Flight::json(['id' => 123]);
```

### ステータスコード付きの JSON

2 番目の引数としてステータスコードを渡すこともできます:

```php
Flight::json(['id' => 123], 201);
```

### 綺麗な出力の JSON

最後の引数に引数を渡すことで、綺麗なプリントを有効にできます:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

`Flight::json()` に渡すオプションを変更し、よりシンプルな構文を使用したい場合は、JSON メソッドを再マップするだけです:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// そして、このように使用できます
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON と実行の停止

JSON レスポンスを送信して実行を停止したい場合は、`jsonHalt` メソッドを使用します。
これは、認証のチェックをしている場合など、ユーザーが認証されていない場合はすぐに JSON レスポンスを送信し、既存の本文コンテンツをクリアし、実行を停止するために便利です。

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// ユーザーが認証されているか確認
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// 残りのルートを続行
});
```

### JSONP

JSONP リクエストの場合、コールバック関数を定義するために使用しているクエリパラメータ名をオプションで渡すことができます:

```php
Flight::jsonp(['id' => 123], 'q');
```

そのため、`?q=my_func` を使用して GET リクエストを行うと、次の出力が受け取れるはずです:

```javascript
my_func({"id":123});
```

クエリパラメータ名を指定しない場合、デフォルトで `jsonp` になります。

## 別の URL にリダイレクトする

`redirect()` メソッドを使用して現在のリクエストをリダイレクトすることができます。新しい URL を渡してください:

```php
Flight::redirect('/new/location');
```

Flight はデフォルトで HTTP 303 ("See Other") ステータスコードを送信します。カスタムコードを設定することもできます:

```php
Flight::redirect('/new/location', 401);
```

## 停止

任意の時点で `halt` メソッドを呼び出すことで、フレームワークを停止できます:

```php
Flight::halt();
```

オプションで `HTTP` ステータスコードとメッセージを指定することもできます:

```php
Flight::halt(200, 'Be right back...');
```

`halt` を呼び出すと、その時点までのすべてのレスポンスコンテンツが破棄されます。フレームワークを停止して現在のレスポンスを出力する場合は、`stop` メソッドを使用してください:

```php
Flight::stop();
```

## HTTP キャッシュ

Flight は HTTP レベルのキャッシュをサポートしています。キャッシュ条件が満たされると、Flight は HTTP `304 Not Modified` レスポンスを返します。次回クライアントが同じリソースをリクエストすると、ローカルにキャッシュされたバージョンを使用するよう促されます。

### ルートレベルのキャッシュ

レスポンス全体をキャッシュしたい場合は、`cache()` メソッドを使用して時間を渡してください。

```php

// これはレスポンスを 5 分間キャッシュします
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'このコンテンツはキャッシュされます。';
});

// 代わりに、strtotime() メソッドに渡す文字列を使用することもできます
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'このコンテンツはキャッシュされます。';
});
```

### Last-Modified

`lastModified` メソッドを使用して、ページの最終更新日時を設定できます。クライアントは、最終更新日時の値が変更されるまで、キャッシュを継続します。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'このコンテンツはキャッシュされます。';
});
```

### ETag

`ETag` キャッシングは `Last-Modified` に似ていますが、リソースに任意の ID を指定できます:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'このコンテンツはキャッシュされます。';
});
```

`lastModified` または `etag` を呼び出すと、キャッシュ値が設定および確認されます。リクエスト間でキャッシュ値が同じ場合、Flight はすぐに `HTTP 304` レスポンスを送信して処理を停止します。