# レスポンス

Flightは、レスポンスのヘッダーの一部を生成するのを助けますが、ユーザーに返す内容の大部分はあなたがコントロールします。時には`Response`オブジェクトに直接アクセスすることもできますが、ほとんどの場合、`Flight`インスタンスを使用してレスポンスを送信します。

## 基本的なレスポンスを送信する

Flightはob_start（）を使用して出力をバッファリングします。つまり、`echo`または`print`を使用してユーザーにレスポンスを送信し、Flightがそれをキャプチャして適切なヘッダーと共にユーザーに送信します。

```php

// これにより、「Hello、World！」がユーザーのブラウザに送信されます
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

代替として、`write()`メソッドを呼び出して本文に追加することもできます。

```php

// これにより、「Hello、World！」がユーザーのブラウザに送信されます
Flight::route('/', function() {
	// 細かいですが、必要なときに仕事をする場合があります
	Flight::response()->write("Hello, World!");

	// この時点で設定した本文を取得したい場合
	// 次のようにします
	$body = Flight::response()->getBody();
});
```

## ステータスコード

`status`メソッドを使用して、レスポンスのステータスコードを設定できます：

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

## レスポンスヘッダーの設定

`header`メソッドを使用して、レスポンスのコンテンツタイプなどのヘッダーを設定できます：

```php

// これにより、「Hello、World！」がユーザーのブラウザにプレーンテキストで送信されます
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flightは、JSONおよびJSONPレスポンスを送信するためのサポートを提供します。JSONレスポンスを送信するには、JSONにエンコードするデータを渡します：

```php
Flight::json(['id' => 123]);
```

### JSONP

JSONPリクエストの場合、コールバック関数を定義するために使用するクエリパラメーター名をオプションで渡すことができます：

```php
Flight::jsonp(['id' => 123], 'q');
```

そのため、`?q=my_func`を使用してGETリクエストを行うと、次の出力が受信されるはずです：

```javascript
my_func({"id":123});
```

クエリパラメーター名を渡さない場合、デフォルトで`jsonp`になります。

## 別のURLにリダイレクトする

`redirect()`メソッドを使用して、新しいURLを渡すことで現在のリクエストをリダイレクトできます：

```php
Flight::redirect('/new/location');
```

デフォルトでは、FlightはHTTP 303（"他を参照"）ステータスコードを送信します。オプションでカスタムコードを設定できます：

```php
Flight::redirect('/new/location', 401);
```

## 中止

いつでも`halt`メソッドを呼び出すことで、フレームワークを停止できます：

```php
Flight::halt();
```

オプションで`HTTP`ステータスコードとメッセージを指定することもできます：

```php
Flight::halt(200, 'Be right back...');
```

`halt`を呼び出すと、それまでのレスポンス内容が破棄されます。フレームワークを停止して現在のレスポンスを出力したい場合は、`stop`メソッドを使用します：

```php
Flight::stop();
```

## HTTPキャッシュ

Flightは、HTTPレベルのキャッシングのための組込みサポートを提供します。キャッシングの条件が満たされると、FlightはHTTP `304 Not Modified`レスポンスを返します。クライアントが同じリソースを再リクエストするとき、ローカルにキャッシュされたバージョンを使用するよう促されます。

### ルートレベルのキャッシング

全体のレスポンスをキャッシュしたい場合は、`cache()`メソッドを使用して時間を渡すことができます。

```php

// これにより、レスポンスが5分間キャッシュされます
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'このコンテンツはキャッシュされます。';
});

// 代わりに、strtotime（）メソッドに渡す文字列を使用することもできます
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'このコンテンツはキャッシュされます。';
});
```

### 最終変更

`lastModified`メソッドを使用して、ページの最終変更日時を設定するためにUNIXタイムスタンプを渡すことができます。クライアントは、最終変更値が変更されるまでキャッシュを継続します。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'このコンテンツはキャッシュされます。';
});
```

### ETag

`ETag`キャッシングは`Last-Modified`と似ており、リソースに任意のIDを指定できます：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'このコンテンツはキャッシュされます。';
});
```

`lastModified`または`etag`のいずれかを呼び出すと、キャッシュ値が設定およびチェックされます。リクエスト間でキャッシュ値が同じ場合、Flightは即座に`HTTP 304`レスポンスを送信して処理を停止します。