# レスポンス

Flight はあなたのためにレスポンスヘッダーの一部を生成しますが、ユーザーに返すものの大部分はあなたが制御します。場合によっては、`Response` オブジェクトに直接アクセスできますが、ほとんどの場合、`Flight` インスタンスを使用してレスポンスを送信します。

## 基本的なレスポンスの送信

Flight は ob_start() を使用して出力をバッファリングします。これは、`echo` または `print` を使用してユーザーにレスポンスを送信でき、Flight がそれをキャプチャして適切なヘッダーでユーザーに返すことを意味します。

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

代わりに、`write()` メソッドを呼び出してボディに追加することも可能です。

```php

// これにより「Hello, World!」がユーザーのブラウザに送信されます
Flight::route('/', function() {
	// 冗長ですが、必要なときに仕事をすることがあります
	Flight::response()->write("Hello, World!");

	// この時点で設定したボディを取得したい場合
	// このようにして取得できます
	$body = Flight::response()->getBody();
});
```

## ステータスコード

`status` メソッドを使用してレスポンスのステータスコードを設定できます：

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

`write` メソッドを使用してレスポンスボディを設定できますが、何かを `echo` または `print` すると 
それはキャプチャされ、出力バッファリング経由でレスポンスボディとして送信されます。

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// 同じ意味です

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### レスポンスボディのクリア

レスポンスボディをクリアしたい場合は、`clearBody` メソッドを使用できます：

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### レスポンスボディでコールバックを実行

`addResponseBodyCallback` メソッドを使用してレスポンスボディでコールバックを実行できます：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// これにより、すべてのルートのレスポンスが gzip 圧縮されます
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

複数のコールバックを追加でき、それらは追加された順に実行されます。このメソッドは任意の [callable](https://www.php.net/manual/en/language.types.callable.php) を受け入れるため、クラス配列 `[ $class, 'method' ]`、クロージャ `$strReplace = function($body) { str_replace('hi', 'there', $body); };`、または例えばHTMLコードを最適化する関数名 `'minify'` を受け入れることができます。

**注意:** ルートコールバックは `flight.v2.output_buffering` 設定オプションを使用している場合には機能しません。

### 特定のルートコールバック

これを特定のルートにのみ適用したい場合は、ルート自体でコールバックを追加できます：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// これにより、このルートのレスポンスのみが gzip 圧縮されます
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### ミドルウェアオプション

ミドルウェアを使用してすべてのルートにコールバックを適用することもできます：

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
		// ボディを何らかの方法で最適化します
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

// これにより「Hello, World!」がプレーンテキストでユーザーのブラウザに送信されます
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// または
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight は JSON および JSONP レスポンスを送信するためのサポートを提供しています。JSON レスポンスを送信するには、いくつかのデータを JSON エンコードします：

```php
Flight::json(['id' => 123]);
```

> **注意:** デフォルトで、Flight はレスポンスに `Content-Type: application/json` ヘッダーを送信します。また、JSON をエンコードするときに定数 `JSON_THROW_ON_ERROR` および `JSON_UNESCAPED_SLASHES` を使用します。

### ステータスコード付きの JSON

第二引数としてステータスコードを渡すこともできます：

```php
Flight::json(['id' => 123], 201);
```

### プレティプリント付きの JSON

最後の位置に引数を渡すことで、プレティプリントを有効にすることも可能です：

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

`Flight::json()` に渡すオプションを変更して、よりシンプルな構文が必要な場合は、JSON メソッドを再マッピングすることができます：

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// これで、このように使うことができます
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON と実行の停止 (v3.10.0)

JSON レスポンスを送信し実行を停止する場合は、`jsonHalt` メソッドを使用します。
これは、何らかの認証をチェックしていて、ユーザーが認証されていない場合に、すぐに JSON レスポンスを送信し、既存のボディコンテンツをクリアし、実行を停止できる便利な方法です。

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// ユーザーが認証されているかどうかをチェック
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// ルートの残りを続行
});
```

v3.10.0以前では、このようにする必要がありました：

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// ユーザーが認証されているかどうかをチェック
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// ルートの残りを続行
});
```

### JSONP

JSONP リクエストについては、オプションでコールバック関数を定義するために使用しているクエリパラメータ名を渡すことができます：

```php
Flight::jsonp(['id' => 123], 'q');
```

したがって、`?q=my_func` を使用して GET リクエストを行うと、次の出力が得られます：

```javascript
my_func({"id":123});
```

クエリパラメータ名を渡さない場合は、デフォルトで `jsonp` に設定されます。

## 別の URL へのリダイレクト

`redirect()` メソッドを使用して新しい URL を渡すことで、現在のリクエストをリダイレクトできます：

```php
Flight::redirect('/new/location');
```

デフォルトでは Flight は HTTP 303 ("See Other") ステータスコードを送信します。オプションでカスタムコードを設定できます：

```php
Flight::redirect('/new/location', 401);
```

## 停止

いつでも `halt` メソッドを呼び出すことでフレームワークを停止できます：

```php
Flight::halt();
```

オプションの `HTTP` ステータスコードとメッセージを指定することもできます：

```php
Flight::halt(200, 'Be right back...');
```

`halt` を呼び出すと、その時点までのレスポンスコンテンツは破棄されます。フレームワークを停止し、現在のレスポンスを出力したい場合は、`stop` メソッドを使用します：

```php
Flight::stop();
```

## レスポンスデータのクリア

`clear()` メソッドを使用してレスポンスボディとヘッダーをクリアできます。これにより、レスポンスに割り当てられたヘッダーがクリアされ、レスポンスボディがクリアされ、ステータスコードが `200` に設定されます。

```php
Flight::response()->clear();
```

### レスポンスボディのみのクリア

レスポンスボディのみをクリアしたい場合は、`clearBody()` メソッドを使用できます：

```php
// これにより、レスポンス() オブジェクトに設定されたヘッダーは保持されます。
Flight::response()->clearBody();
```

## HTTP キャッシング

Flight には、HTTP レベルのキャッシングに対するビルトインのサポートがあります。キャッシング条件が満たされると、Flight は HTTP `304 Not Modified` レスポンスを返します。クライアントが同じリソースを再度リクエストする際には、ローカルにキャッシュされたバージョンを使用するように促されます。

### ルートレベルのキャッシング

レスポンス全体をキャッシュしたい場合は、`cache()` メソッドを使用し、キャッシュする時間を渡すことができます。

```php

// これにより、レスポンスが5分間キャッシュされます
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'このコンテンツはキャッシュされます。';
});

// または、strtotime() メソッドに渡す文字列を使用できます
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'このコンテンツはキャッシュされます。';
});
```

### 最終更新日時

`lastModified` メソッドを使用して、UNIX タイムスタンプを渡し、ページが最後に修正された日時を設定できます。クライアントは、最終更新値が変更されるまでキャッシュを使用し続けます。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'このコンテンツはキャッシュされます。';
});
```

### ETag

`ETag` キャッシングは `Last-Modified` と似ていますが、リソースのために望む任意の ID を指定できます：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'このコンテンツはキャッシュされます。';
});
```

`lastModified` または `etag` を呼び出すと、どちらもキャッシュ値を設定およびチェックします。要求間でキャッシュ値が同じである場合、Flight は即座に `HTTP 304` レスポンスを送信し、処理を停止します。

## ファイルのダウンロード (v3.12.0)

ファイルをダウンロードするためのヘルパーメソッドがあります。`download` メソッドを使用し、パスを渡すことができます。

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```