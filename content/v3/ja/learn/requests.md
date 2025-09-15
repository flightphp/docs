# リクエスト

Flight は HTTP リクエストを単一のオブジェクトにカプセル化し、次のようにアクセスできます：

```php
$request = Flight::request();
```

## 典型的な使用例

ウェブアプリケーションでリクエストを扱う場合、通常はヘッダー、または `$_GET` または `$_POST` パラメータ、または生のリクエストボディを引き出したいと思うでしょう。Flight はこれらの操作を簡単に行うインターフェースを提供します。

クエリ文字列パラメータを取得する例です：

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "You are searching for: $keyword";
	// データベースをクエリしたり、$keywordを使って他のことを行う
});
```

POST メソッドのフォームの例です：

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "You submitted: $name, $email";
	// データベースに保存したり、$name と $email を使って他のことを行う
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
- **proxy_ip** - クライアントのプロキシ IP アドレス。`$_SERVER` 配列を `HTTP_CLIENT_IP`、`HTTP_X_FORWARDED_FOR`、`HTTP_X_FORWARDED`、`HTTP_X_CLUSTER_CLIENT_IP`、`HTTP_FORWARDED_FOR`、`HTTP_FORWARDED` の順でスキャンします。
- **host** - リクエストホスト名
- **servername** - `$_SERVER` からの SERVER_NAME

`query`、`data`、`cookies`、および `files` プロパティは配列またはオブジェクトとしてアクセスできます。

クエリ文字列パラメータを取得するには：

```php
$id = Flight::request()->query['id'];
```

または：

```php
$id = Flight::request()->query->id;
```

## 生のリクエストボディ

PUT リクエストなどの場合に生の HTTP リクエストボディを取得するには：

```php
$body = Flight::request()->getBody();
```

## JSON 入力

`application/json` タイプで `{"id": 123}` のデータを送信した場合、`data` プロパティから利用できます：

```php
$id = Flight::request()->data->id;
```

## `$_GET`

`$_GET` 配列は `query` プロパティ経由でアクセスできます：

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

`$_POST` 配列は `data` プロパティ経由でアクセスできます：

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

`$_COOKIE` 配列は `cookies` プロパティ経由でアクセスできます：

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

`$_SERVER` 配列にアクセスするためのショートカットとして `getVar()` メソッドがあります：

```php
$host = Flight::request()->getVar('HTTP_HOST');
```

## アップロードされたファイルを `$_FILES` 経由でアクセス

アップロードされたファイルは `files` プロパティ経由でアクセスできます：

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## ファイルアップロードの処理 (v3.12.0)

フレームワークを使ってファイルアップロードを処理するためのヘルパーメソッドがあります。基本的に、リクエストからファイルデータを引き出し、新しい場所に移動します。

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

> **セキュリティ注意:** ユーザー入力、特にファイルアップロードを扱う場合は常に検証とサニタイズを行ってください。許可する拡張子の種類を検証するだけでなく、ファイルの "マジックバイト" を検証して、ユーザーが主張するファイルタイプであることを確認してください。これを助けるための [記事](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [と](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [ライブラリ](https://github.com/RikudouSage/MimeTypeDetector) があります。

## リクエストヘッダー

リクエストヘッダーは `getHeader()` または `getHeaders()` メソッドを使ってアクセスできます：

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

生のリクエストボディは `getBody()` メソッドを使ってアクセスできます：

```php
$body = Flight::request()->getBody();
```

## リクエストメソッド

リクエストメソッドは `method` プロパティまたは `getMethod()` メソッドを使ってアクセスできます：

```php
$method = Flight::request()->method; // 実際には getMethod() を呼び出す
$method = Flight::request()->getMethod();
```

**注意:** `getMethod()` メソッドはまず `$_SERVER['REQUEST_METHOD']` からメソッドを引き出し、存在する場合に `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` または `$_REQUEST['_method']` で上書きできます。

## リクエスト URL

URL の一部を組み合わせるためのヘルパーメソッドがいくつかあります。

### 完全 URL

完全なリクエスト URL は `getFullUrl()` メソッドを使ってアクセスできます：

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### ベース URL

ベース URL は `getBaseUrl()` メソッドを使ってアクセスできます：

```php
$url = Flight::request()->getBaseUrl();
// 注意: 末尾のスラッシュなし。
// https://example.com
```

## クエリ解析

URL を `parseQuery()` メソッドに渡すと、クエリ文字列を連想配列に解析できます：

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```