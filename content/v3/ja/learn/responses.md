# レスポンス

## 概要

Flight は、レスポンスヘッダーの一部を自動生成するのに役立ちますが、ユーザーに返す内容の大部分はあなたが制御します。ほとんどの場合、`response()` オブジェクトに直接アクセスしますが、Flight にはいくつかのヘルパーメソッドがあり、それらを使ってレスポンスヘッダーの一部を設定できます。

## 理解

ユーザーが [request](/learn/requests) リクエストをアプリケーションに送信した後、それらに対して適切なレスポンスを生成する必要があります。彼らは、好みの言語、特定の種類の圧縮を扱えるかどうか、ユーザーエージェントなどを含む情報を送信しており、すべてを処理した後、適切なレスポンスを返します。これは、ヘッダーの設定、HTML や JSON のボディの出力、またはページへのリダイレクトを行うことです。

## 基本的な使用方法

### レスポンスボディの送信

Flight は `ob_start()` を使用して出力をバッファリングします。これにより、`echo` や `print` を使用してユーザーにレスポンスを送信でき、Flight がそれをキャプチャして適切なヘッダーとともにユーザーに返します。

```php
// これは「Hello, World!」をユーザーのブラウザに送信します
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
// これは「Hello, World!」をユーザーのブラウザに送信します
Flight::route('/', function() {
	// 冗長ですが、必要なときに役立ちます
	Flight::response()->write("Hello, World!");

	// この時点で設定したボディを取得したい場合
	// 以下のようにできます
	$body = Flight::response()->getBody();
});
```

### JSON

Flight は JSON および JSONP レスポンスの送信をサポートします。JSON レスポンスを送信するには、JSON エンコードするデータを渡します：

```php
Flight::route('/@companyId/users', function(int $companyId) {
	// 例としてデータベースからユーザーを取得する
	$users = Flight::db()->fetchAll("SELECT id, first_name, last_name FROM users WHERE company_id = ?", [ $companyId ]);

	Flight::json($users);
});
// [{"id":1,"first_name":"Bob","last_name":"Jones"}, /* 他のユーザー */ ]
```

> **注意:** デフォルトでは、Flight はレスポンスに `Content-Type: application/json` ヘッダーを送信します。また、JSON をエンコードする際に `JSON_THROW_ON_ERROR` および `JSON_UNESCAPED_SLASHES` フラグを使用します。

#### ステータスコード付きの JSON

2 番目の引数としてステータスコードを渡すこともできます：

```php
Flight::json(['id' => 123], 201);
```

#### プリティプリント付きの JSON

最後の位置に引数を渡してプリティプリントを有効にすることもできます：

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

#### JSON 引数の順序変更

`Flight::json()` は非常にレガシーメソッドですが、Flight の目標はプロジェクトの後方互換性を維持することです。引数の順序をよりシンプルな構文に変更したい場合、JSON メソッドを他の Flight メソッドと同様に [再マップ](/learn/extending) するだけで非常に簡単です：

```php
Flight::map('json', function($data, $code = 200, $options = 0) {

	// これで json() メソッドを使用する際に `true, 'utf-8'` を指定する必要がなくなります！
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// これで以下のように使用できます
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

#### JSON と実行の停止

_v3.10.0_

JSON レスポンスを送信して実行を停止したい場合、`jsonHalt()` メソッドを使用できます。これは、承認の種類をチェックしており、ユーザーが承認されていない場合に JSON レスポンスを即座に送信し、既存のボディコンテンツをクリアして実行を停止するのに便利です。

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// ユーザーが承認されているかをチェック
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
		// ここで exit; は必要ありません。
	}

	// ルートの残りを続行
});
```

v3.10.0 以前では、以下のようにする必要がありました：

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// ユーザーが承認されているかをチェック
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// ルートの残りを続行
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

上記のユースケースは一般的ではありませんが、[middleware](/learn/middleware) で使用する場合により一般的になる可能性があります。

### レスポンスボディに対するコールバックの実行

`addResponseBodyCallback` メソッドを使用して、レスポンスボディにコールバックを実行できます：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// これにより、すべてのルートのレスポンスを gzip します
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

複数のコールバックを追加でき、追加された順序で実行されます。これは任意の [callable](https://www.php.net/manual/en/language.types.callable.php) を受け入れるため、クラス配列 `[ $class, 'method' ]`、クロージャ `$strReplace = function($body) { str_replace('hi', 'there', $body); };`、または HTML コードを最小化する関数名 `'minify'` などを指定できます。

**注意:** `flight.v2.output_buffering` 設定オプションを使用している場合、ルートコールバックは動作しません。

#### 特定のルートコールバック

これを特定のルートにのみ適用したい場合、ルート自体でコールバックを追加できます：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// これにより、このルートのレスポンスのみを gzip します
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

#### ミドルウェアオプション

[middleware](/learn/middleware) を使用して、すべてのルートにコールバックを適用することもできます：

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
		// ボディを最小化する
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

現在のステータスコードを取得したい場合、引数なしで `status` メソッドを使用できます：

```php
Flight::response()->status(); // 200
```

### レスポンスヘッダーの設定

`header` メソッドを使用して、レスポンスのコンテンツタイプなどのヘッダーを設定できます：

```php
// これは「Hello, World!」をプレーンテキストでユーザーのブラウザに送信します
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// または
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
		return; // 下記の機能が実行されないように必要です
	}

	// 新しいユーザーを追加...
	Flight::db()->runQuery("INSERT INTO users ....");
	Flight::redirect('/admin/dashboard');
});
```

> **注意:** デフォルトでは、Flight は HTTP 303 ("See Other") ステータスコードを送信します。オプションでカスタムコードを設定できます：

```php
Flight::redirect('/new/location', 301); // 永久
```

### ルート実行の停止

`halt` メソッドを呼び出して、フレームワークを停止し、任意の時点で即座に終了できます：

```php
Flight::halt();
```

オプションで `HTTP` ステータスコードとメッセージを指定することもできます：

```php
Flight::halt(200, 'Be right back...');
```

`halt` を呼び出すと、その時点までのすべてのレスポンスコンテンツを破棄し、すべての実行を停止します。フレームワークを停止して現在のレスポンスを出力したい場合、`stop` メソッドを使用します：

```php
Flight::stop($httpStatusCode = null);
```

> **注意:** `Flight::stop()` には奇妙な動作があり、レスポンスを出力しますが、スクリプトの実行を続行します。これは望ましくない場合があります。`Flight::stop()` を呼び出した後に `exit` または `return` を使用してさらなる実行を防げますが、一般的に `Flight::halt()` を使用することを推奨します。

これにより、ヘッダーキーと値がレスポンスオブジェクトに保存されます。リクエストライフサイクルの終了時に、ヘッダーを構築してレスポンスを送信します。

## 高度な使用方法

### ヘッダーの即時送信

ヘッダーでカスタムなことを行い、作業中のそのコード行でヘッダーを送信する必要がある場合があります。[ストリーミングルート](/learn/routing) を設定する場合、これが必要です。これは `response()->setRealHeader()` で実現できます。

```php
Flight::route('/', function() {
	Flight::response()->setRealHeader('Content-Type: text/plain');
	echo 'Streaming response...';
	sleep(5);
	echo 'Done!';
})->stream();
```

### JSONP

JSONP リクエストの場合、コールバック関数を定義するために使用するクエリパラメータ名をオプションで渡せます：

```php
Flight::jsonp(['id' => 123], 'q');
```

したがって、`?q=my_func` を使用した GET リクエストを行うと、以下のような出力を受け取るはずです：

```javascript
my_func({"id":123});
```

クエリパラメータ名を渡さない場合、デフォルトで `jsonp` になります。

> **注意:** 2025 年以降も JSONP リクエストを使用している場合、チャットに参加して理由を教えてください！ 良い戦いやホラーストーリーを聞くのが好きです！

### レスポンスデータのクリア

`clear()` メソッドを使用してレスポンスボディとヘッダーをクリアできます。これにより、レスポンスに割り当てられたすべてのヘッダーをクリアし、レスポンスボディをクリアし、ステータスコードを `200` に設定します。

```php
Flight::response()->clear();
```

#### レスポンスボディのみのクリア

レスポンスボディのみをクリアしたい場合、`clearBody()` メソッドを使用できます：

```php
// これにより、response() オブジェクトに設定されたヘッダーは保持されます。
Flight::response()->clearBody();
```

### HTTP キャッシング

Flight は HTTP レベルのキャッシングの組み込みサポートを提供します。キャッシング条件が満たされた場合、Flight は HTTP `304 Not Modified` レスポンスを返します。次にクライアントが同じリソースをリクエストすると、ローカルにキャッシュされたバージョンを使用するよう促されます。

#### ルートレベルのキャッシング

レスポンス全体をキャッシュしたい場合、`cache()` メソッドを使用してキャッシュ時間を渡せます。

```php

// これはレスポンスを 5 分間キャッシュします
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// 代替として、strtotime() メソッドに渡す文字列を使用できます
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

`lastModified` メソッドを使用して、ページが最後に変更された日時を UNIX タイムスタンプで設定できます。クライアントは、最終変更値が変更されるまでキャッシュを使用し続けます。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

`ETag` キャッシングは `Last-Modified` に似ていますが、リソースに対して任意の ID を指定できます：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

`lastModified` または `etag` のいずれかを呼び出すと、両方ともキャッシュ値を設定し、チェックします。リクエスト間でキャッシュ値が同じ場合、Flight は即座に `HTTP 304` レスポンスを送信し、処理を停止します。

### ファイルのダウンロード

_v3.12.0_

エンドユーザーにファイルをストリーミングするヘルパーメソッドがあります。`download` メソッドを使用してパスを渡せます。

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```

## 関連項目
- [Routing](/learn/routing) - ルートをコントローラーにマップし、ビューをレンダリングする方法。
- [Requests](/learn/requests) - 受信リクエストの処理方法の理解。
- [Middleware](/learn/middleware) - 認証、ロギングなどにルートでミドルウェアを使用。
- [Why a Framework?](/learn/why-frameworks) - Flight のようなフレームワークを使用する利点の理解。
- [Extending](/learn/extending) - Flight を独自の機能で拡張する方法。

## トラブルシューティング
- リダイレクトが動作しない場合、メソッドに `return;` を追加してください。
- `stop()` と `halt()` は同じではありません。`halt()` は実行を即座に停止しますが、`stop()` は実行を続行します。

## 変更履歴
- v3.12.0 - downloadFile ヘルパーメソッドを追加。
- v3.10.0 - `jsonHalt` を追加。
- v1.0 - 初回リリース。