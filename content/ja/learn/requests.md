# リクエスト

FlightはHTTPリクエストを単一のオブジェクトにカプセル化し、
次のようにアクセスできます：

```php
$request = Flight::request();
```

## 一般的な使用例

Webアプリケーションでリクエストを処理する際は、通常、ヘッダーを
取り出したり、`$_GET`や`$_POST`のパラメータを取得したり、あるいは
生のリクエストボディを取得したいと思うことでしょう。Flightはそれを
簡単に行うためのインターフェースを提供します。

クエリ文字列パラメータを取得する例は以下の通りです：

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "あなたが検索しているのは: $keyword";
	// $keywordを使ってデータベースにクエリするか、何か他のことをする
});
```

POSTメソッドのフォームの例はこちらです：

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "あなたが送信したのは: $name, $email";
	// $nameと$emailを使ってデータベースに保存するか、何か他のことをする
});
```

## リクエストオブジェクトのプロパティ

リクエストオブジェクトは以下のプロパティを提供します：

- **body** - 生のHTTPリクエストボディ
- **url** - リクエストされているURL
- **base** - URLの親サブディレクトリ
- **method** - リクエストメソッド (GET, POST, PUT, DELETE)
- **referrer** - リファラURL
- **ip** - クライアントのIPアドレス
- **ajax** - リクエストがAJAXリクエストかどうか
- **scheme** - サーバープロトコル (http, https)
- **user_agent** - ブラウザ情報
- **type** - コンテンツタイプ
- **length** - コンテンツの長さ
- **query** - クエリ文字列パラメータ
- **data** - ポストデータまたはJSONデータ
- **cookies** - クッキーデータ
- **files** - アップロードされたファイル
- **secure** - 接続が安全かどうか
- **accept** - HTTPのacceptパラメータ
- **proxy_ip** - クライアントのプロキシIPアドレス。`HTTP_CLIENT_IP`、`HTTP_X_FORWARDED_FOR`、`HTTP_X_FORWARDED`、`HTTP_X_CLUSTER_CLIENT_IP`、`HTTP_FORWARDED_FOR`、`HTTP_FORWARDED`をその順で`$_SERVER`配列からスキャンします。
- **host** - リクエストホスト名

`query`、`data`、`cookies`、および`files`プロパティには
配列またはオブジェクトとしてアクセスできます。

したがって、クエリ文字列パラメータを取得するには、次のようにできます：

```php
$id = Flight::request()->query['id'];
```

または、次のようにできます：

```php
$id = Flight::request()->query->id;
```

## 生のリクエストボディ

例えばPUTリクエストを扱うときに生のHTTPリクエストボディを取得するには、

```php
$body = Flight::request()->getBody();
```

## JSON入力

`application/json`タイプのリクエストでデータ`{"id": 123}`を送信すると、
それは`data`プロパティから利用可能になります：

```php
$id = Flight::request()->data->id;
```

## `$_GET`

`$_GET`配列には`query`プロパティを介してアクセスできます：

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

`$_POST`配列には`data`プロパティを介してアクセスできます：

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

`$_COOKIE`配列には`cookies`プロパティを介してアクセスできます：

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

`$_SERVER`配列には`getVar()`メソッドを介してショートカットでアクセスできます：

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## `$_FILES`を介してアップロードされたファイルにアクセスする

`files`プロパティを介してアップロードされたファイルにアクセスできます：

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## ファイルアップロードの処理

フレームワークを使用してファイルアップロードを処理できます。基本的には
リクエストからファイルデータを取り出し、それを新しい場所に移動することです。

```php
Flight::route('POST /upload', function(){
	// <input type="file" name="myFile">のような入力フィールドがあった場合
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

複数のファイルがアップロードされている場合は、それらをループ処理できます：

```php
Flight::route('POST /upload', function(){
	// <input type="file" name="myFiles[]">のような入力フィールドがあった場合
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **セキュリティノート:** ユーザー入力を常に検証し、サニタイズしてください。特にファイルアップロードを扱う場合は注意が必要です。許可する拡張子のタイプを必ず検証し、ファイルが実際にユーザーが主張するファイルタイプであることを確認するために「マジックバイト」も検証してください。これに役立つ[記事](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe)、[および](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/)、[ライブラリ](https://github.com/RikudouSage/MimeTypeDetector)があります。

## リクエストヘッダー

`getHeader()`または`getHeaders()`メソッドを使用してリクエストヘッダーにアクセスできます：

```php

// おそらくAuthorizationヘッダーが必要な場合
$host = Flight::request()->getHeader('Authorization');
// または
$host = Flight::request()->header('Authorization');

// すべてのヘッダーを取得する必要がある場合
$headers = Flight::request()->getHeaders();
// または
$headers = Flight::request()->headers();
```

## リクエストボディ

`getBody()`メソッドを使用して生のリクエストボディにアクセスできます：

```php
$body = Flight::request()->getBody();
```

## リクエストメソッド

`method`プロパティまたは`getMethod()`メソッドを使用してリクエストメソッドにアクセスできます：

```php
$method = Flight::request()->method; // 実際にはgetMethod()を呼び出す
$method = Flight::request()->getMethod();
```

**注意:** `getMethod()`メソッドは最初に`$_SERVER['REQUEST_METHOD']`からメソッドを取得し、その後、存在する場合は`$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`によって上書きされるか、存在する場合は`$_REQUEST['_method']`によって上書きされることがあります。

## リクエストURL

URLの部分を組み合わせるためのいくつかのヘルパーメソッドがあります。

### 完全URL

`getFullUrl()`メソッドを使用して完全なリクエストURLにアクセスできます：

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### ベースURL

`getBaseUrl()`メソッドを使用してベースURLにアクセスできます：

```php
$url = Flight::request()->getBaseUrl();
// 注意: トレーリングスラッシュはありません。
// https://example.com
```

## クエリ解析

`parseQuery()`メソッドにURLを渡すことで、クエリ文字列を連想配列に解析できます：

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```