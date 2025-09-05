# リクエスト

Flight は HTTP リクエストを単一のオブジェクトにカプセル化し、以下のようにアクセスできます：

```php
$request = Flight::request();
```

## 典型的な使用例

Web アプリケーションでリクエストを扱う場合、通常はヘッダーを取得したり、`$_GET` または `$_POST` パラメータを抽出したり、または生のリクエストボディを取得したりします。Flight はこれらの操作を簡単に行うインターフェースを提供します。

クエリ文字列パラメータを取得する例です：

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "You are searching for: $keyword";
	// データベースをクエリしたり、$keyword で他の処理を行ったりします
});
```

POST メソッドのフォームの例です：

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "You submitted: $name, $email";
	// データベースに保存したり、$name と $email で他の処理を行ったりします
});
```

## リクエストオブジェクトのプロパティ

リクエストオブジェクトは以下のプロパティを提供します：

- **body** - 生の HTTP リクエストボディ
- **url** - リクエストされている URL
- **base** - URL の親サブディレクトリ
- **method** - リクエストメソッド (GET, POST, PUT, DELETE)
- **referrer** - リファラ URL
- **ip** - クライアントの IP アドレス
- **ajax** - リクエストが AJAX リクエストかどうかを示す
- **scheme** - サーバープロトコル (http, https)
- **user_agent** - ブラウザ情報
- **type** - コンテンツタイプ
- **length** - コンテンツの長さ
- **query** - クエリ文字列パラメータ
- **data** - POST データまたは JSON データ
- **cookies** - クッキーデータ
- **files** - アップロードされたファイル
- **secure** - 接続がセキュアかどうかを示す
- **accept** - HTTP アクセプトパラメータ
- **proxy_ip** - クライアントのプロキシ IP アドレス。`$_SERVER` 配列を `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` の順でスキャンします。
- **host** - リクエストホスト名
- **servername** - `$_SERVER` からの SERVER_NAME

`query`、`data`、`cookies`、および `files` プロパティは、配列またはオブジェクトとしてアクセスできます。

クエリ文字列パラメータを取得するには、以下のようにできます：

```php
$id = Flight::request()->query['id'];
```

または、以下のようにできます：

```php
$id = Flight::request()->query->id;
```

## 生のリクエストボディ

PUT リクエストなどの場合に生の HTTP リクエストボディを取得するには、以下のようにします：

```php
$body = Flight::request()->getBody();
```

## JSON 入力

`application/json` タイプのデータ `{"id": 123}` を送信した場合、`data` プロパティから利用できます：

```php
$id = Flight::request()->data->id;
```

## `$_GET`

`$_GET` 配列は、`query` プロパティ経由でアクセスできます：

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

`$_POST` 配列は、`data` プロパティ経由でアクセスできます：

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

`$_COOKIE` 配列は、`cookies` プロパティ経由でアクセスできます：

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

`$_SERVER` 配列にアクセスするためのショートカットとして、`getVar()` メソッドがあります：

```php
$host = Flight::request()->getVar('HTTP_HOST');
```

## アップロードされたファイルを `$_FILES` 経由でアクセス

アップロードされたファイルは、`files` プロパティ経由でアクセスできます：

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## ファイルアップロードの処理 (v3.12.0)

フレームワークを使ってファイルアップロードを処理するためのヘルパーメソッドがあります。これは基本的に、リクエストからファイルデータを取得し、新しい場所に移動するものです。

```php
Flight::route('POST /upload', function(){
	// 入力フィールドが <input type="file" name="myFile"> の場合
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

複数のファイルをアップロードした場合、ループで処理できます：

```php
Flight::route('POST /upload', function(){
	// 入力フィールドが <input type="file" name="myFiles[]"> の場合
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **セキュリティの注意:** ユーザー入力、特にファイルアップロードを扱う場合は、常に検証とサニタイズを行ってください。許可する拡張子のタイプを検証するだけでなく、ファイルの「マジックバイト」を検証して、ユーザーが主張するファイルの種類であることを確認してください。これを助けるための [articles](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [and](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [libraries](https://github.com/RikudouSage/MimeTypeDetector) があります。

## リクエストヘッダー

リクエストヘッダーは、`getHeader()` または `getHeaders()` メソッドを使ってアクセスできます：

```php
// Authorization ヘッダーが必要な場合
$host = Flight::request()->getHeader('Authorization');
// または
$host = Flight::request()->header('Authorization');

// すべてのヘッダーを取得する場合
$headers = Flight::request()->getHeaders();
// または
$headers = Flight::request()->headers();
```

## リクエストボディ

生のリクエストボディは、`getBody()` メソッドを使ってアクセスできます：

```php
$body = Flight::request()->getBody();
```

## リクエストメソッド

リクエストメソッドは、`method` プロパティまたは `getMethod()` メソッドを使ってアクセスできます：

```php
$method = Flight::request()->method; // 実際には getMethod() を呼びます
$method = Flight::request()->getMethod();
```

**注意:** `getMethod()` メソッドはまず `$_SERVER['REQUEST_METHOD']` からメソッドを引き、`$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` が存在する場合または `$_REQUEST['_method']` が存在する場合に上書きされます。

## リクエスト URL

URL の一部を組み合わせるためのいくつかのヘルパーメソッドがあります。

### 完全な URL

完全なリクエスト URL は、`getFullUrl()` メソッドを使ってアクセスできます：

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### ベース URL

ベース URL は、`getBaseUrl()` メソッドを使ってアクセスできます：

```php
$url = Flight::request()->getBaseUrl();
// 注意: 末尾にスラッシュはありません。
// https://example.com
```

## クエリのパース

URL を `parseQuery()` メソッドに渡すと、クエリ文字列を連想配列にパースできます：

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```