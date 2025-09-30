# リクエスト

## 概要

Flight は HTTP リクエストを単一のオブジェクトにカプセル化し、以下の方法でアクセスできます：

```php
$request = Flight::request();
```

## 理解

HTTP リクエストは、HTTP ライフサイクルの理解に不可欠なコア要素の一つです。ユーザーがウェブブラウザや HTTP クライアントでアクションを実行すると、ヘッダー、本文、URL などをプロジェクトに送信します。これらのヘッダー（ブラウザの言語、対応する圧縮タイプ、ユーザーエージェントなど）をキャプチャし、Flight アプリケーションに送信された本文と URL をキャプチャできます。これらのリクエストは、アプリが次に何をするかを理解するために不可欠です。

## 基本的な使用方法

PHP には、`$_GET`、`$_POST`、`$_REQUEST`、`$_SERVER`、`$_FILES`、`$_COOKIE` などのスーパーグローバルがあります。Flight はこれらを便利な [Collections](/learn/collections) に抽象化します。`query`、`data`、`cookies`、`files` プロパティを配列またはオブジェクトとしてアクセスできます。

> **注意:** プロジェクトでこれらのスーパーグローバルを使用することは**強く**推奨されず、`request()` オブジェクト経由で参照する必要があります。

> **注意:** `$_ENV` の抽象化は利用できません。

### `$_GET`

`$_GET` 配列は `query` プロパティ経由でアクセスできます：

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// または
	$keyword = Flight::request()->query->keyword;
	echo "You are searching for: $keyword";
	// $keyword でデータベースをクエリするか他の処理
});
```

### `$_POST`

`$_POST` 配列は `data` プロパティ経由でアクセスできます：

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// または
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "You submitted: $name, $email";
	// $name と $email でデータベースに保存するか他の処理
});
```

### `$_COOKIE`

`$_COOKIE` 配列は `cookies` プロパティ経由でアクセスできます：

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// または
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// 本当に保存されているかをチェックし、保存されていれば自動ログイン
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

新しいクッキー値の設定に関するヘルプについては、[overclokk/cookie](/awesome-plugins/php-cookie) を参照してください。

### `$_SERVER`

`$_SERVER` 配列は `getVar()` メソッド経由でアクセスするためのショートカットがあります：

```php
$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

アップロードされたファイルは `files` プロパティ経由でアクセスできます：

```php
// $_FILES プロパティへの生のアクセス。推奨アプローチは以下を参照
$uploadedFile = Flight::request()->files['myFile']; 
// または
$uploadedFile = Flight::request()->files->myFile;
```

詳細は [Uploaded File Handler](/learn/uploaded-file) を参照してください。

#### ファイルアップロードの処理

_v3.12.0_

フレームワークを使用して、ヘルパーメソッドでファイルアップロードを処理できます。基本的に、リクエストからファイルデータを取得し、新しい場所に移動するだけです。

```php
Flight::route('POST /upload', function(){
	// 入力フィールドが <input type="file" name="myFile"> の場合
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

複数のファイルがアップロードされた場合、ループで処理できます：

```php
Flight::route('POST /upload', function(){
	// 入力フィールドが <input type="file" name="myFiles[]"> の場合
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **セキュリティ注意:** ユーザー入力の検証とサニタイズを常に実行してください。特にファイルアップロード時には、許可する拡張子のタイプを検証し、ファイルの「マジックバイト」を検証して、ユーザーが主張するファイルタイプが本物かを確認してください。このための [記事](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [や](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [ライブラリ](https://github.com/RikudouSage/MimeTypeDetector) が利用可能です。

### リクエスト本文

POST/PUT リクエストなどの生の HTTP リクエスト本文を取得するには、以下を実行できます：

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// 送信された XML で何か処理。
});
```

### JSON 本文

コンテンツタイプ `application/json` のリクエストを受け取り、例として `{"id": 123}` のデータの場合、`data` プロパティから利用可能です：

```php
$id = Flight::request()->data->id;
```

### リクエストヘッダー

`getHeader()` または `getHeaders()` メソッドを使用してリクエストヘッダーにアクセスできます：

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

### リクエストメソッド

`method` プロパティまたは `getMethod()` メソッドを使用してリクエストメソッドにアクセスできます：

```php
$method = Flight::request()->method; // 実際には getMethod() で設定
$method = Flight::request()->getMethod();
```

**注意:** `getMethod()` メソッドはまず `$_SERVER['REQUEST_METHOD']` からメソッドを取得し、存在する場合 `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` または `$_REQUEST['_method']` で上書きされます。

## リクエストオブジェクトのプロパティ

リクエストオブジェクトは以下のプロパティを提供します：

- **body** - 生の HTTP リクエスト本文
- **url** - リクエストされる URL
- **base** - URL の親サブディレクトリ
- **method** - リクエストメソッド (GET, POST, PUT, DELETE)
- **referrer** - リファラー URL
- **ip** - クライアントの IP アドレス
- **ajax** - リクエストが AJAX かどうかのフラグ
- **scheme** - サーバープロトコル (http, https)
- **user_agent** - ブラウザ情報
- **type** - コンテンツタイプ
- **length** - コンテンツ長
- **query** - クエリ文字列パラメータ
- **data** - 投稿データまたは JSON データ
- **cookies** - クッキーデータ
- **files** - アップロードされたファイル
- **secure** - 接続がセキュアかどうかのフラグ
- **accept** - HTTP accept パラメータ
- **proxy_ip** - クライアントのプロキシ IP アドレス。`$_SERVER` 配列を `HTTP_CLIENT_IP`、`HTTP_X_FORWARDED_FOR`、`HTTP_X_FORWARDED`、`HTTP_X_CLUSTER_CLIENT_IP`、`HTTP_FORWARDED_FOR`、`HTTP_FORWARDED` の順でスキャンします。
- **host** - リクエストホスト名
- **servername** - `$_SERVER` からの SERVER_NAME

## URL ヘルパーメソッド

URL の一部を便利に組み合わせるためのヘルパーメソッドがいくつかあります。

### 完全な URL

`getFullUrl()` メソッドを使用して完全なリクエスト URL にアクセスできます：

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### ベース URL

`getBaseUrl()` メソッドを使用してベース URL にアクセスできます：

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// 注意: 末尾のスラッシュなし。
```

## クエリ解析

`parseQuery()` メソッドに URL を渡すと、クエリ文字列を連想配列に解析できます：

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## 関連項目
- [Routing](/learn/routing) - ルートをコントローラーにマッピングし、ビューをレンダリングする方法。
- [Responses](/learn/responses) - HTTP レスポンスのカスタマイズ方法。
- [Why a Framework?](/learn/why-frameworks) - リクエストが全体像にどのように適合するか。
- [Collections](/learn/collections) - データのコレクションの操作。
- [Uploaded File Handler](/learn/uploaded-file) - ファイルアップロードの処理。

## トラブルシューティング
- ウェブサーバーがプロキシ、ロードバランサーなどで後ろにある場合、`request()->ip` と `request()->proxy_ip` が異なる可能性があります。

## 変更履歴
- v3.12.0 - リクエストオブジェクト経由でファイルアップロードを処理する機能を追加。
- v1.0 - 初回リリース。