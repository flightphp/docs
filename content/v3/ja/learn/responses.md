# レスポンス

Flight はレスポンスヘッダーの一部を生成するのを助けてくれますが、ユーザーに返送する内容のほとんどを制御するのはあなたです。時折、直接 `Response` オブジェクトにアクセスできますが、ほとんどの場合、`Flight` インスタンスを使ってレスポンスを送信します。

## 基本レスポンスの送信

Flight は ob_start() を使用して出力をバッファリングします。これにより、`echo` または `print` を使用してユーザーにレスポンスを送信でき、Flight がそれをキャプチャして適切なヘッダーと一緒に返送します。

```php
// これはユーザーのブラウザに "Hello, World!" を送信します
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
// これはユーザーのブラウザに "Hello, World!" を送信します
Flight::route('/', function() {
	// 冗長ですが、必要な場合に役立つ
	Flight::response()->write("Hello, World!");

	// この時点で設定したボディを取得したい場合
	// 以下のようにできます
	$body = Flight::response()->getBody();
});
```

## ステータスコード

レスポンスのステータスコードを設定するには、`status` メソッドを使用します：

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

現在のステータスコードを取得するには、引数なしで `status` メソッドを使用します：

```php
Flight::response()->status(); // 200
```

## レスポンスボディの設定

レスポンスボディを設定するには、`write` メソッドを使用しますが、`echo` または `print` を使用すると、それはキャプチャされて出力バッファリング経由でレスポンスボディとして送信されます。

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// これは以下と同等です

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### レスポンスボディのクリア

レスポンスボディをクリアしたい場合、`clearBody` メソッドを使用します：

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### レスポンスボディに対するコールバックの実行

レスポンスボディでコールバックを実行するには、`addResponseBodyCallback` メソッドを使用します：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// これはすべてのルートのレスポンスをgzipします
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

複数のコールバックを追加でき、それらは追加された順序で実行されます。これは [callable](https://www.php.net/manual/en/language.types.callable.php) を受け入れるため、クラス配列 `[ $class, 'method' ]`、クロージャ `$strReplace = function($body) { str_replace('hi', 'there', $body); };`、または例えば HTML コードを最小化する関数名 `'minify'` を受け入れます。

**注:** ルートコールバックは、`flight.v2.output_buffering` 構成オプションを使用している場合に動作しません。

### 特定のルートに対するコールバック

これを特定のルートにのみ適用したい場合、ルート内でコールバックを追加できます：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// これはこのルートのレスポンスのみgzipします
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
		// ここで response() オブジェクトにコールバックを適用します。
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

レスポンスのヘッダー、例えばコンテンツタイプを設定するには、`header` メソッドを使用します：

```php
// これはユーザーのブラウザにプレーンテキストで "Hello, World!" を送信します
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// または
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight は JSON および JSONP レスポンスの送信をサポートします。JSON レスポンスを送信するには、JSON エンコードするデータを渡します：

```php
Flight::json(['id' => 123]);
```

> **注:** デフォルトで、Flight はレスポンスに `Content-Type: application/json` ヘッダーを送信します。また、JSON をエンコードする際に `JSON_THROW_ON_ERROR` と `JSON_UNESCAPED_SLASHES` 定数を使用します。

### JSON とステータスコード

ステータスコードを第2引数として渡すこともできます：

```php
Flight::json(['id' => 123], 201);
```

### JSON の整形出力

整形出力を有効にするために、最後の位置に引数を渡すこともできます：

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

`Flight::json()` に渡されるオプションを変更し、よりシンプルな構文を望む場合、JSON メソッドを再マップできます：

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// そして今、これのように使用できます
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON と実行停止 (v3.10.0)

JSON レスポンスを送信して実行を停止したい場合、`jsonHalt()` メソッドを使用します。これは、例えば承認チェックを行い、ユーザーが承認されていない場合にすぐに JSON レスポンスを送信し、既存のボディコンテンツをクリアして実行を停止するのに便利です。

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// ユーザーが承認されているか確認
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// ルートの残りを続行
});
```

v3.10.0 以前では、以下のようにする必要がありました：

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// ユーザーが承認されているか確認
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// ルートの残りを続行
});
```

### JSONP

JSONP リクエストの場合、オプションでコールバック関数を定義するためのクエリパラメーター名を渡せます：

```php
Flight::jsonp(['id' => 123], 'q');
```

例えば、`?q=my_func` を使用して GET リクエストを行うと、出力は以下のようになります：

```javascript
my_func({"id":123});
```

クエリパラメーター名を渡さない場合、デフォルトで `jsonp` になります。

## 別の URL へのリダイレクト

現在のリクエストをリダイレクトするには、`redirect()` メソッドを使用して新しい URL を渡します：

```php
Flight::redirect('/new/location');
```

デフォルトで Flight は HTTP 303 ("See Other") ステータスコードを送信します。オプションでカスタムコードを設定できます：

```php
Flight::redirect('/new/location', 401);
```

## 停止

フレームワークを任意の時点で停止するには、`halt` メソッドを呼びます：

```php
Flight::halt();
```

オプションで HTTP ステータスコードとメッセージを指定できます：

```php
Flight::halt(200, 'Be right back...');
```

`halt` を呼び出すと、それまでのレスポンスコンテンツは破棄されます。フレームワークを停止して現在のレスポンスを出力したい場合、`stop` メソッドを使用します：

```php
Flight::stop($httpStatusCode = null);
```

> **注:** `Flight::stop()` は、レスポンスを出力するがスクリプトの実行を継続するなどの奇妙な動作をします。実行を防ぐために `exit` または `return` を `Flight::stop()` の後に使用できますが、一般的に `Flight::halt()` を使用することを推奨します。

## レスポンスデータのクリア

レスポンスボディとヘッダーをクリアするには、`clear()` メソッドを使用します。これにより、レスポンスに割り当てられたヘッダーをクリアし、レスポンスボディをクリアし、ステータスコードを `200` に設定します。

```php
Flight::response()->clear();
```

### レスポンスボディのみのクリア

レスポンスボディのみをクリアしたい場合、`clearBody()` メソッドを使用します：

```php
// これは response() オブジェクトに設定されたヘッダーを保持します。
Flight::response()->clearBody();
```

## HTTP キャッシング

Flight は HTTP レベルでのキャッシングを組み込みでサポートします。キャッシング条件が満たされると、Flight は HTTP `304 Not Modified` レスポンスを返します。次にクライアントが同じリソースをリクエストすると、ローカルにキャッシュされたバージョンの使用が促されます。

### ルートレベルのキャッシング

レスポンス全体をキャッシングしたい場合、`cache()` メソッドを使用してキャッシング時間を渡します。

```php
// これはレスポンスを5分間キャッシングします
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// または、strtotime() メソッドに渡す文字列を使用
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

ページが最後に変更された日時を設定するには、`lastModified` メソッドに UNIX タイムスタンプを渡します。クライアントは last modified 値が変更されるまでキャッシュを継続して使用します。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

`ETag` キャッシングは `Last-Modified` に似ていますが、リソースの任意の ID を指定できます：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

`lastModified` または `etag` を呼び出すと、キャッシュ値を設定およびチェックします。リクエスト間でキャッシュ値が同じ場合、Flight はすぐに HTTP 304 レスポンスを送信して処理を停止します。

## ファイルのダウンロード (v3.12.0)

ファイルをダウンロードするためのヘルパーメソッドがあります。`download` メソッドにパスを渡します。

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```