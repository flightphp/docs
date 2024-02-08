# 応答

Flightは、一部のレスポンスヘッダを生成するのに役立ちますが、ユーザーに送り返す内容の大部分を制御できます。時には`Response`オブジェクトに直接アクセスできますが、ほとんどの場合は`Flight`インスタンスを使用してレスポンスを送信します。

## 基本的な応答を送信する

Flightはob_start()を使用して出力をバッファリングしています。これは、`echo`や`print`を使用してユーザーに応答を送信し、Flightがそれをキャプチャして適切なヘッダと共にユーザーに送り返すことができることを意味します。

```php
// これはユーザーのブラウザに"Hello, World!"を送信します
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

代わりに、`write()`メソッドを呼び出して本文に追加することもできます。

```php
// これはユーザーのブラウザに"Hello, World!"を送信します
Flight::route('/', function() {
	// 高根の果より時には、仕事が必要なときには過剰に、しかし‹‹
	Flight::response()->write("Hello, World!");

	// この時点で設定したボディを取得したい場合、次のようにすることができます‹‹
	$body = Flight::response()->getBody();
});
```

## ステータスコード

`status`メソッドを使用してレスポンスのステータスコードを設定できます。

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

現在のステータスコードを取得したい場合は、引数なしで`status`メソッドを使用できます：

```php
Flight::response()->status(); // 200
```

## レスポンスヘッダの設定

`header`メソッドを使用して、レスポンスのコンテンツタイプなどのヘッダを設定できます。

```php
// これはプレーンテキストで"Hello, World!"をユーザーのブラウザに送信します
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

FlightはJSONやJSONPレスポンスの送信をサポートしています。JSONレスポンスを送信するには、JSONにエンコードするデータを渡します：

```php
Flight::json(['id' => 123]);
```

### JSONP

JSONPリクエストでは、コールバック関数を定義するために使用するクエリパラメータ名をオプションで渡すことができます：

```php
Flight::jsonp(['id' => 123], 'q');
```

したがって、`?q=my_func`を使用してGETリクエストを行うと、次の出力を受け取るはずです：

```javascript
my_func({"id":123});
```

クエリパラメータ名を指定しない場合、デフォルトで`jsonp`になります。

## 別のURLにリダイレクトする

`redirect()`メソッドを使用して、現在のリクエストをリダイレクトできます。新しいURLを渡します：

```php
Flight::redirect('/new/location');
```

FlightはデフォルトでHTTP 303 ("See Other")ステータスコードを送信します。オプションでカスタムコードを設定できます：

```php
Flight::redirect('/new/location', 401);
```

## 停止

`halt`メソッドを呼び出すことで、いつでもフレームワークを停止できます：

```php
Flight::halt();
```

オプションの`HTTP`ステータスコードとメッセージを指定することもできます：

```php
Flight::halt(200, 'Be right back...');
```

`halt`を呼び出すと、それまでの応答コンテンツが破棄されます。フレームワークを停止して現在の応答を出力したい場合は、`stop`メソッドを使用します：

```php
Flight::stop();
```

## HTTPキャッシュ

Flightには、HTTPレベルのキャッシングをサポートする組み込み機能が提供されています。キャッシング条件が満たされると、FlightはHTTP `304 Not Modified`応答を返します。クライアントが同じリソースをリクエストするたびに、ローカルにキャッシュされたバージョンを使用するよう促されます。

### ルートレベルのキャッシュ

全体の応答をキャッシュしたい場合は、`cache()`メソッドを使用して時間を渡すことができます。

```php
// これは応答を5分間キャッシュします
Flight::route('/news', function () {
  Flight::cache(time() + 300);
  echo 'この内容はキャッシュされます。';
});

// 代替として、`strtotime()`メソッドに渡す文字列を使用できます
Flight::route('/news', function () {
  Flight::cache('+5 minutes');
  echo 'この内容はキャッシュされます。';
});
```

### Last-Modified

`lastModified`メソッドを使用して、ページが最後に修正された日付と時刻を設定できます。クライアントは最終修正値が変更されるまでキャッシュを引き続き使用します。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'この内容はキャッシュされます。';
});
```

### ETag

`ETag`キャッシングは`Last-Modified`に似ていますが、任意のリソースIDを指定できます：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'この内容はキャッシュされます。';
});
```

`lastModified`または`etag`のいずれかを呼び出すと、キャッシュ値が設定およびチェックされます。リクエスト間でキャッシュ値が同じ場合、Flightは直ちに`HTTP 304`応答を送信し、処理を停止します。